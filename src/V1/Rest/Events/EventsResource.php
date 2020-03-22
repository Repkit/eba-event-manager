<?php
namespace MicroIceEventManager\V1\Rest\Events;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EventsResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_eventsTypes;
    private $_eventDetails;
    private $_eventDetailFields;
    private $_eventDatas;
    private $_dataFields;
    private $_dataFieldConfig;
    private $_eventTypeDataType;
    private $_eventTranslations;

    private $_settings;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $EventsTypes, TableGatewayInterface $EventsDetails, TableGatewayInterface $EventsDetailFields, TableGatewayInterface $EventsDatas, TableGatewayInterface $DataFields, TableGatewayInterface $DataFieldConfig, TableGatewayInterface $EventTypeDataType, TableGatewayInterface $EventTranslations, $Settings)
    {
        $this->_model = $Model;
        $this->_eventsTypes = $EventsTypes;
        $this->_eventDetails = $EventsDetails;
        $this->_eventDetailFields = $EventsDetailFields;
        $this->_eventDatas = $EventsDatas;
        $this->_dataFields = $DataFields;
        $this->_dataFieldConfig = $DataFieldConfig;
        $this->_eventTypeDataType = $EventTypeDataType;
        $this->_eventTranslations = $EventTranslations;

        $this->_settings = $Settings;
    }

    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        try
        {
            $this->getEventManager()->trigger('events.create.pre', $this, array());

            $id = $this->getEvent()->getRouteParam('event_id');
            $lang = $this->getEvent()->getRouteParam('language_code');
            if('*' == $lang || '*' == $id){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $default = empty($id) ? true: false;
            $rdata = (array)$data;

            // start trasaction as we need to delete also other information like price categories and other stuff
            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            $creationDate = date('Y-m-d H:i:s');
            if($default)
            {
                 // create entity
                $entityClass = $this->_model->getEntityClass();
                $entity = new $entityClass();
                $entity->exchangeArray($rdata);
                $entity->CreationDate = $creationDate;

                //validate translation
                if(!empty($rdata['Name'])){
                    $texists = $this->_eventTranslations->getByNameAndStatusAndLanguage($rdata['Name'], $entity->Status, $lang);
                    if(!empty($texists)){
                        throw new \InvalidArgumentException("An event with same name already exists!", 1);
                    }
                }

                // persist entity
                $inserted = $this->_model->insert($entity->getArrayCopy());
                if(!$inserted){
                    throw new \Exception("Could not create event", 1);
                }
                $entity->Id = $this->_model->getLastInsertValue();
            }
            else
            {
                $entity = $this->_model->getById($id);
                if( !$entity || $entity->Status == 99 ){
                    throw new \InvalidArgumentException("Please specify a valid event!", 1);
                }
            }
           
            $entityTransClass = $this->_eventTranslations->getEntityClass();
            $translation = new $entityTransClass();

            $translation->exchangeArray($rdata);

            $translation->EventId = $entity->Id;
            $translation->Language = $lang;
            $translation->CreationDate = $creationDate;

            $inserted = $this->_eventTranslations->insert($translation->getArrayCopy());
            if(!$inserted){
                throw new \Exception("Could not create event translation", 2);
            }
            
            $entity->Language = $lang;
            $entity->TranslationId = $this->_eventTranslations->getLastInsertValue();
            $entity->Identifier = $translation->Identifier;
            $entity->Name = $translation->Name;
            $this->getEventManager()->trigger('events.create.post', $this, array('entity' => $entity, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            return $entity;

        }
        catch(\InvalidArgumentException $e)
        {
            if (!empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface){
                $connection->rollback();
            }
            return new ApiProblem(400, $e->getMessage());
        }
        catch(\Exception $e)
        {
            if (!empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface){
                $connection->rollback();
            }
            return new ApiProblem(417, $e->getMessage());
        }
        // return new ApiProblem(405, 'The POST method has not been defined');
    }

    /**
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        try
        {
            $this->getEventManager()->trigger('events.delete.pre', $this, array());

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();
            
            $result = false;

            /*$event = $this->_model->getById($id);
            if($event->Status != 1){
                throw new \Exception("Please specify an active events!", 1);
            }*/

            $result = $this->_model->delete(array('Id' => $id));
            $result = (bool)$result;
            
            $this->getEventManager()->trigger('events.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            return $result;
        }
        catch(\InvalidArgumentException $e)
        {
            if (!empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface){
                $connection->rollback();
            }
            return new ApiProblem(400, $e->getMessage());
        }
        catch(\Exception $e)
        {
            if (!empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface){
                $connection->rollback();
            }
            return new ApiProblem(417, $e->getMessage());
        }
    }

    /**
     * Delete a collection, or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function deleteList($data)
    {
        return new ApiProblem(405, 'The DELETE method has not been defined for collections');
    }

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        try
        {
            if(!isset($id) || empty($id)){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $lang = $this->getEvent()->getRouteParam('language_code');

            if('*' == $lang)
            {
                $storage = array();
                if(!empty($this->_settings['event'])){
                    if(!empty($this->_settings['event']['storage'])){
                        if(!empty($this->_settings['event']['storage']['joins'])){
                            $storage = $this->_settings['event']['storage'];
                        }
                    }
                }

                /*$where = new \Zend\Db\Sql\Where();
                $where->equalTo('events.Id',$id);*/
                $events = $this->_model->getTranslationsById($id);
            }
            else
            {
                if(!empty($lang)){
                    if('*' == $id){
                        $events = $this->_model->getAllTypesByLanguage($lang);
                    }else{
                        $event = $this->_model->getByIdAndLanguage($id, $lang);
                        if(!$event){
                            // if no translation return default
                            $event = $this->_model->getById($id);
                        }
                    }
                }else{
                    $event = $this->_model->getById($id);
                }
                // var_dump($event);exit(__FILE__.'::'.__LINE__);
                if( !$event || $event->Status == 99 ){
                    if(!isset($events)){
                        throw new \InvalidArgumentException("Error Processing Request", 1);
                    }
                }
                if(!isset($events) && isset($event)){
                    $events = array($event);
                    $events = new \ArrayIterator($events);
                }
            }
            
            $collection = array();
            foreach ($events as $key => $event) 
            {
                // var_dump($event);exit(__FILE__.'::'.__LINE__);
                if( !$event || $event->Status == 99 ){
                    throw new \InvalidArgumentException("Error Processing Request", 1);
                }

                $tid = $event->TranslationId;
                if(empty($tid)){
                    continue;
                }

                $details = $this->_eventDetails->getAllByEventId($tid);
                $detailTypes = array();
                foreach($details as $idx => $field)
                {
                    $fname = $field->Field;
                    $fvalue = $field->Value;
                    $ftype = $field->TypeId;
                    if(!isset($detailTypes[$ftype])){
                        $detailTypes[$ftype] = array();
                    }
                    $detailTypes[$ftype][$fname] = $fvalue;
                }

                $eventlang = $event->Language;
                if(null == $eventlang){
                    $types = $this->_eventsTypes->getAllTypesByEventId($id);
                }else{
                    $types = $this->_eventsTypes->getAllTypesByEventIdAndLanguage($id, $eventlang);
                }
                
                $eventTypes = array();
                foreach ($types as $key => $type) {
                    $typeId = $type->Id;

                    //get detail field config for default values
                    $typeFieldConfigs = $this->_eventDetailFields->getAllByTypeId($typeId);
                    foreach ($typeFieldConfigs as $key => $typeFieldConfig) {
                        $fname = $typeFieldConfig->Field;
                        $fvalue = $typeFieldConfig->Value;
                        if(!isset($detailTypes[$typeId][$fname])){
                            $detailTypes[$typeId][$fname] = $fvalue;
                        }
                    }
                    
                    $eventDataTypesDb = $this->_eventTypeDataType->getAllEventDataTypesByEventTypeId($typeId);
                    // var_dump($eventDataTypes->toArray());exit(__FILE__.'::'.__LINE__);
                    $eventDataTypes = array();
                    foreach ($eventDataTypesDb as $key => $datatype) {
                        
                        if(!isset($eventDataTypes[$datatype->Name])){
                            $eventDataTypes[$datatype->Name] = array();    
                        }
                        
                        $datatypeId = $datatype->Id;                        
                        $eventData = $this->_eventDatas->fetchAllByEventIdAndTypeId($tid, $datatypeId);
                        // $eventData = $this->_model->fetchAllByEventId($eventid, $where);

                        // create an array for easy of use
                        $xtraFields = array();
                        // get extra fields for this type to add to entities
                        $fieldconfigs = $this->_dataFieldConfig->getAllByTypeId($datatypeId);
                        foreach ($fieldconfigs as $key => $fieldconfig) {
                            $xtraFields[$fieldconfig->Field] = $fieldconfig->Value ? $fieldconfig->Value : null;
                        }

                        // prepare for assigment extra fields
                        $eventdataIds = array();
                        $eventDatas = array();
                        foreach ($eventData as $key => $udata) {
                            $eventdataIds[] = $udata->Id;
                            foreach ($xtraFields as $xtraField => $xtraFieldValue) {
                                $udata->$xtraField = $xtraFieldValue;
                            }
                            $eventDatas[$udata->Id] = $udata;
                        }
                        // interswitch
                        $eventData = $eventDatas;
                        unset($eventDatas);

                        if(!isset($eventdataIds) || empty($eventdataIds)){
                            continue;
                        }

                        // get extra fields for all event data
                        $eventDataFields = $this->_dataFields->fetchAllByDataId($eventdataIds);

                        // assign extra fields on each event data
                        foreach ($eventDataFields as $key => $udatafield) {
                            $fname = $udatafield->Field;
                            $eventData[$udatafield->DataId]->$fname = $udatafield->Value;
                            unset($fname);
                        }
                        # code...
                        $eventDataTypes[$datatype->Name] = array_values($eventData);
                        // var_dump($eventData);exit(__FILE__.'::'.__LINE__);
                    }

                    $type->Fields = $detailTypes[$typeId];
                    $type->Data = $eventDataTypes;
                    $eventTypes[] = $type;
                }

                $event->Types = $eventTypes;
                // var_dump($types->toArray());exit(__FILE__.'::'.__LINE__);

                $collection[] = $event;
            }
            
            /*if(1 == count($events) && '*' != $lang){
                return $event;
            }*/

            $events = new \Zend\Paginator\Adapter\ArrayAdapter($collection);
            return new EventsCollection($events);

            // $events = new \ArrayIterator($collection);
            // return new EventsCollection(new \Zend\Paginator\Adapter\Iterator($events));

        }
        catch(\InvalidArgumentException $e)
        {
            return new ApiProblem(400, $e->getMessage());
        }
        catch(\Exception $e)
        {
            return new ApiProblem(417, $e->getMessage());
        }
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = array())
    {
        try
        {
            // $limit = intval($this->_settings['page_size']);
            $detailsLimit = 100;
            if(count($params))
            {
                $fetchall = false;
                // overwrite default limit
                if(!empty($params['limit']))
                {
                    $rqlimit = intval($params['limit']);
                    if($rqlimit > $detailsLimit){
                        $params['limit'] = $detailsLimit;
                    }else{
                        $fetchall = true;
                    }
                    unset($rqlimit);
                }
                else
                {
                    $params['limit'] = $detailsLimit;
                }

                if($fetchall)
                {
                    // get joins with other tables 
                    $storage = array();
                    if(!empty($this->_settings['event'])){
                        if(!empty($this->_settings['event']['storage'])){
                            if(!empty($this->_settings['event']['storage']['joins'])){
                                $storage = $this->_settings['event']['storage'];
                            }
                        }
                    }

                    $dbfilterwhere = null;
                    if(!empty($params['filter'])){
                        $dbfilterwhere = $params['filter'];
                    }

                    $dbfilterwhere[] = [
                        'name' => 'Language',
                        'type' => 'isNull',
                        'term' => '',
                    ];

                    // $postwhere = new \Zend\Db\Sql\Where();
                    // $postwhere->isNull('event_translations.Language');

                    // $events = $this->_model->getAllExtended($storage, $dbfilterwhere, $postwhere);
                    $events = $this->_model->getAllExtended($storage, $dbfilterwhere);
                }
                else
                {
                    throw new \Exception("Request Entity Too Large",413);
                    
                    \Zend\EventManager\StaticEventManager::getInstance()->attach(
                    'ZF\Rest\RestController', 'getList.post', function ($e) {

                        $halCollection = $e->getParam('collection');
                        $halCollection->setAttributes(
                            array( 
                                // '_error' => array(
                                    "type" => "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html",
                                    "title" => "Request Entity Too Large",
                                    "status" => 413,
                                    "detail" => "Please specify a valid limit parameter!"
                                // ),
                                // added here just to specify display order
                                // "_links" => null,
                                // "_embedded" => null,
                                // "page_size" => 10,
                                // "page_count" => 0,
                                // "total_items" => null,
                                // "page" => 0,
                            )
                        );

                        $e->getTarget()->getResponse()->setStatusCode(413);

                    }); 

                    /*\Zend\EventManager\StaticEventManager::getInstance()->attach(
                    'ZF\Hal\Plugin\Hal', 'renderCollection.post', function ($e) {

                        // $halCollection = $e->getParam('collection');
                        $payload = $e->getParam('payload');
                        unset($payload['_embedded']);
                        unset($payload['page_count']);
                        unset($payload['page_size']);
                        unset($payload['total_items']);
                        unset($payload['page']);

                        // var_dump($payload['_links']);exit(__FILE__.'::'.__LINE__);

                    }); */

                    $events = new \ArrayIterator(array());
                    // $events = new \ArrayIterator(array('message'=>'Please specify a valid limit with filters'));
                }    
                
            }
            else
            {
                // exclude 99
                $events = $this->_model->fetchAll(array('where'=>array('events.Status' => array(0,1))));
            }    
            
            return new EventsCollection(new \Zend\Paginator\Adapter\Iterator($events));
            
        }
        catch(\InvalidArgumentException $e)
        {
            return new ApiProblem(400, $e->getMessage());
        }
        catch(\Exception $e)
        {
            $errno = $e->getCode();
            if(500 == $errno){
                $errno = 417;
            }
            return new ApiProblem($errno, $e->getMessage());
        }
    }

    /**
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        // var_dump($data);exit(__FILE__.'::'.__LINE__);
        try
        {
            if(!isset($id) || empty($id) || '*' == $id){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $lang = $this->getEvent()->getRouteParam('language_code');
            if('*' == $lang){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $this->getEventManager()->trigger('events.patch.pre', $this, array());
            
            $rdata = (array)$data;

            // start trasaction as we need to delete also other information like price categories and other stuff
            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            if(!isset($lang) && empty($lang)){
                //update main entity
                /*$entityClass = $this->_model->getEntityClass();
                $entity = new $entityClass();*/
                $entity = $this->_model->select(array('Id' => $id))->current();
                if(empty($entity) || $entity->Id != $id || $entity->Status == 99){
                    throw new \InvalidArgumentException("Error Processing Request", 1);
                }
                $edata = $entity->getArrayCopy();

                foreach($rdata as $field => $value)
                {
                    if(in_array($field, array('Timestamp','CreationDate'))) {
                        continue;
                    }
                    if(array_key_exists($field, $edata)){
                        $entity->$field = $rdata[$field];
                        unset($rdata[$field]);
                    }
                }
                $updateData = $entity->getArrayCopy();
                /*unset($updateData['Id']);
                unset($updateData['CreationDate']);*/

                $this->_model->update($updateData, array('Id' => $id));
            }

            // update translated entity
            // if($lang && '*' != $lang)
            // {
                /*$tentityClass = $this->_eventTranslations->getEntityClass();
                $tentity = new $tentityClass();*/
                $tentity = $this->_eventTranslations->getByEventIdAndLanguage($id, $lang);
                if(empty($tentity) || $tentity->Status == 99){
                    throw new \InvalidArgumentException("Error Processing Request", 1);
                }
                $tedata = $tentity->getArrayCopy();
                foreach($rdata as $field => $value)
                {
                    if(in_array($field, array('Timestamp','CreationDate'))) {
                        continue;
                    }
                    if(array_key_exists($field, $tedata)){
                        $tentity->$field = $rdata[$field];
                        unset($rdata[$field]);
                    }
                }
                //keep parent status
                if(!isset($lang) && empty($lang)){
                    $tentity->Status = $entity->Status;
                }
                
                if(!empty($lang) && !empty($rdata)){
                    throw new \InvalidArgumentException("The folowing fields can't be translated: " . implode(', ', array_keys($rdata)), 1);
                }
                /*$tentity->EventId = $id;
                $tentity->Language = $lang;*/
                $tupdateData = $tentity->getArrayCopy();
                /*unset($tupdateData['Id']);
                unset($tupdateData['CreationDate']);*/
                
                // $this->_eventTranslations->update($tupdateData, array('EventId' => $id, 'Language' => $lang));
                $this->_eventTranslations->update($tupdateData, array('Id' => $tentity->Id));
            // }

            $this->getEventManager()->trigger('events.patch.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();
            
            return $this->fetch($id);

            // return $event;
        }
        catch(\InvalidArgumentException $e)
        {
            if (!empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface){
                $connection->rollback();
            }
            return new ApiProblem(400, $e->getMessage());
        }
        catch(\Exception $e)
        {
            if (!empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface){
                $connection->rollback();
            }
            return new ApiProblem(417, $e->getMessage());
        }
    }

    /**
     * Patch (partial in-place update) a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patchList($data)
    {
        return new ApiProblem(405, 'The PATCH method has not been defined for collections');
    }

    /**
     * Replace a collection or members of a collection
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function replaceList($data)
    {
        return new ApiProblem(405, 'The PUT method has not been defined for collections');
    }

    /**
     * Update a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function update($id, $data)
    {
        // var_dump($data);exit(__FILE__.'::'.__LINE__);
        try
        {
            if(!isset($id) || empty($id) || '*' == $id){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $lang = $this->getEvent()->getRouteParam('language_code');
            if(!empty($lang)){
                return new ApiProblem(405, 'The PUT method has not been defined for translated resources');
            }

            $this->getEventManager()->trigger('events.update.pre', $this, array());
            
            $rdata = (array)$data;

            // start trasaction as we need to delete also other information like price categories and other stuff
            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            if(!isset($lang) && empty($lang)){
                //update main entity
                /*$entityClass = $this->_model->getEntityClass();
                $entity = new $entityClass();*/
                $entity = $this->_model->select(array('Id' => $id))->current();
                if(empty($entity) || $entity->Id != $id || $entity->Status == 99){
                    throw new \InvalidArgumentException("Error Processing Request", 1);
                }
                $edata = $entity->getArrayCopy();

                foreach($rdata as $field => $value)
                {
                    if(in_array($field, array('Timestamp','CreationDate'))) {
                        continue;
                    }
                    if(array_key_exists($field, $edata)){
                        $entity->$field = $rdata[$field];
                        unset($rdata[$field]);
                    }
                }
                $updateData = $entity->getArrayCopy();
                /*unset($updateData['Id']);
                unset($updateData['CreationDate']);*/

                $this->_model->update($updateData, array('Id' => $id));
            }

            // update translated entity
            // if($lang && '*' != $lang)
            // {
                /*$tentityClass = $this->_eventTranslations->getEntityClass();
                $tentity = new $tentityClass();*/
                $tentity = $this->_eventTranslations->getByEventIdAndLanguage($id, $lang);
                if(empty($tentity) || $tentity->Status == 99){
                    throw new \InvalidArgumentException("Error Processing Request", 1);
                }
                $tedata = $tentity->getArrayCopy();
                foreach($rdata as $field => $value)
                {
                    if(in_array($field, array('Timestamp','CreationDate'))) {
                        continue;
                    }
                    if(array_key_exists($field, $tedata)){
                        $tentity->$field = $rdata[$field];
                        unset($rdata[$field]);
                    }
                }

                /*$tentity->EventId = $id;
                $tentity->Language = $lang;*/
                $tupdateData = $tentity->getArrayCopy();
                /*unset($tupdateData['Id']);
                unset($tupdateData['CreationDate']);*/
                
                // $this->_eventTranslations->update($tupdateData, array('EventId' => $id, 'Language' => $lang));
                $this->_eventTranslations->update($tupdateData, array('Id' => $tentity->Id));
            // }

            $this->getEventManager()->trigger('events.update.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            return $this->fetch($id);

            // return $event;
        }
        catch(\InvalidArgumentException $e)
        {
            if (!empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface){
                $connection->rollback();
            }
            return new ApiProblem(400, $e->getMessage());
        }
        catch(\Exception $e)
        {
            if (!empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface){
                $connection->rollback();
            }
            return new ApiProblem(417, $e->getMessage());
        }
    }
}