<?php
namespace MicroIceEventManager\V1\Rest\Entities;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EntitiesResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_entitiesTypes;
    private $_entityDetails;
    private $_entityDetailFields;
    private $_entityDatas;
    private $_dataFields;
    private $_dataFieldConfig;
    private $_entityTypeDataType;
    private $_entityTranslations;

    private $_settings;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $EntitiesTypes, TableGatewayInterface $EntityDetails, TableGatewayInterface $EntityDetailFields, TableGatewayInterface $EntityDatas, TableGatewayInterface $DataFields, TableGatewayInterface $DataFieldConfig, TableGatewayInterface $EntityTypeDataType, TableGatewayInterface $EntityTranslations, $Settings)
    {
        $this->_model = $Model;
        $this->_entitiesTypes = $EntitiesTypes;
        $this->_entityDetails = $EntityDetails;
        $this->_entityDetailFields = $EntityDetailFields;
        $this->_entityDatas = $EntityDatas;
        $this->_dataFields = $DataFields;
        $this->_dataFieldConfig = $DataFieldConfig;
        $this->_entityTypeDataType = $EntityTypeDataType;
        $this->_entityTranslations = $EntityTranslations;

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
            $this->getEventManager()->trigger('entities.create.pre', $this, array());

            $id = $this->getEvent()->getRouteParam('entity_id');
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
                    $texists = $this->_entityTranslations->getByNameAndStatusAndLanguage($rdata['Name'], $entity->Status, $lang);
                    if(!empty($texists)){
                        throw new \InvalidArgumentException("An entity with same name already exists!", 1);
                    }
                }

                // persist entity
                $inserted = $this->_model->insert($entity->getArrayCopy());
                if(!$inserted){
                    throw new \Exception("Could not create entity", 1);
                }
                $entity->Id = $this->_model->getLastInsertValue();
            }
            else
            {
                $entity = $this->_model->getById($id);
                if( !$entity || $entity->Status == 99 ){
                    throw new \InvalidArgumentException("Please specify a valid entity!", 1);
                }
            }
           
            $entityTransClass = $this->_entityTranslations->getEntityClass();
            $translation = new $entityTransClass();

            $translation->exchangeArray($rdata);

            $translation->EntityId = $entity->Id;
            $translation->Language = $lang;
            $translation->CreationDate = $creationDate;

            $inserted = $this->_entityTranslations->insert($translation->getArrayCopy());
            if(!$inserted){
                throw new \Exception("Could not create entity translation", 2);
            }
            
            $entity->Language = $lang;
            $entity->TranslationId = $this->_entityTranslations->getLastInsertValue();
            $entity->Name = $translation->Name;

            $this->getEventManager()->trigger('entities.create.post', $this, array('entity' => $entity, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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
            $this->getEventManager()->trigger('entities.delete.pre', $this, array());
            
            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            $result = false;

            /*$entity = $this->_model->getById($id);
            if($entity->Status != 1){
                throw new \InvalidArgumentException("Please specify an active entities!", 1);
            }*/

            $result = $this->_model->delete(array('Id' => $id));
            $result = (bool)$result;
            
            $this->getEventManager()->trigger('entities.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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
                if(!empty($this->_settings['entity'])){
                    if(!empty($this->_settings['entity']['storage'])){
                        if(!empty($this->_settings['entity']['storage']['joins'])){
                            $storage = $this->_settings['entity']['storage'];
                        }
                    }
                }

                /*$where = new \Zend\Db\Sql\Where();
                $where->equalTo('entities.Id',$id);*/
                $entities = $this->_model->getTranslationsById($id);
            }
            else
            {
                if(!empty($lang)){
                    if('*' == $id){
                        $entities = $this->_model->getAllTypesByLanguage($lang);
                    }else{
                        $entity = $this->_model->getByIdAndLanguage($id, $lang);
                        if(!$entity){
                            // if no translation return default
                            $entity = $this->_model->getById($id);
                        }
                    }
                }else{
                    $entity = $this->_model->getById($id);
                }
                // var_dump($entity);exit(__FILE__.'::'.__LINE__);
                if( !$entity || $entity->Status == 99 ){
                    if(!isset($entities)){
                        throw new \InvalidArgumentException("Error Processing Request", 1);
                    }
                }
                if(!isset($entities) && isset($entity)){
                    $entities = array($entity);
                    $entities = new \ArrayIterator($entities);
                }
            }
            
            $collection = array();
            foreach ($entities as $key => $entity) 
            {
                // var_dump($entity);exit(__FILE__.'::'.__LINE__);
                if( !$entity || $entity->Status == 99 ){
                    throw new \InvalidArgumentException("Error Processing Request", 1);
                }

                $tid = $entity->TranslationId;
                if(empty($tid)){
                    continue;
                }

                $details = $this->_entityDetails->getAllByEntityId($tid);
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

                $entitylang = $entity->Language;
                if(null == $entitylang){
                    $types = $this->_entitiesTypes->getAllTypesByEntityId($id);
                }else{
                    $types = $this->_entitiesTypes->getAllTypesByEntityIdAndLanguage($id, $entitylang);
                }
                
                $entityTypes = array();
                foreach ($types as $key => $type) {
                    $typeId = $type->Id;

                    //get detail field config for default values
                    $typeFieldConfigs = $this->_entityDetailFields->getAllByTypeId($typeId);
                    foreach ($typeFieldConfigs as $key => $typeFieldConfig) {
                        $fname = $typeFieldConfig->Field;
                        $fvalue = $typeFieldConfig->Value;
                        if(!isset($detailTypes[$typeId][$fname])){
                            $detailTypes[$typeId][$fname] = $fvalue;
                        }
                    }
                    
                    $entityDataTypesDb = $this->_entityTypeDataType->getAllEntityDataTypesByEntityTypeId($typeId);
                    // var_dump($entityDataTypes->toArray());exit(__FILE__.'::'.__LINE__);
                    $entityDataTypes = array();
                    foreach ($entityDataTypesDb as $key => $datatype) {
                        
                        if(!isset($entityDataTypes[$datatype->Name])){
                            $entityDataTypes[$datatype->Name] = array();    
                        }
                        
                        $datatypeId = $datatype->Id;                        
                        $entityData = $this->_entityDatas->fetchAllByEntityIdAndTypeId($tid, $datatypeId);
                        // $entityData = $this->_model->fetchAllByEntityId($entityid, $where);

                        // create an array for easy of use
                        $xtraFields = array();
                        // get extra fields for this type to add to entities
                        $fieldconfigs = $this->_dataFieldConfig->getAllByTypeId($datatypeId);
                        foreach ($fieldconfigs as $key => $fieldconfig) {
                            $xtraFields[$fieldconfig->Field] = $fieldconfig->Value ? $fieldconfig->Value : null;
                        }

                        // prepare for assigment extra fields
                        $entitydataIds = array();
                        $entityDatas = array();
                        foreach ($entityData as $key => $udata) {
                            $entitydataIds[] = $udata->Id;
                            foreach ($xtraFields as $xtraField => $xtraFieldValue) {
                                $udata->$xtraField = $xtraFieldValue;
                            }
                            $entityDatas[$udata->Id] = $udata;
                        }
                        // interswitch
                        $entityData = $entityDatas;
                        unset($entityDatas);

                        if(!isset($entitydataIds) || empty($entitydataIds)){
                            continue;
                        }

                        // get extra fields for all entity data
                        $entityDataFields = $this->_dataFields->fetchAllByDataId($entitydataIds);

                        // assign extra fields on each entity data
                        foreach ($entityDataFields as $key => $udatafield) {
                            $fname = $udatafield->Field;
                            $entityData[$udatafield->DataId]->$fname = $udatafield->Value;
                            unset($fname);
                        }
                        # code...
                        $entityDataTypes[$datatype->Name] = array_values($entityData);
                        // var_dump($entityData);exit(__FILE__.'::'.__LINE__);
                    }

                    $type->Fields = $detailTypes[$typeId];
                    $type->Data = $entityDataTypes;
                    $entityTypes[] = $type;
                }

                $entity->Types = $entityTypes;
                // var_dump($types->toArray());exit(__FILE__.'::'.__LINE__);

                $collection[] = $entity;
            }
            
            /*if(1 == count($entities) && '*' != $lang){
                return $entity;
            }*/

            $entities = new \Zend\Paginator\Adapter\ArrayAdapter($collection);
            return new EntitiesCollection($entities);

            // $entities = new \ArrayIterator($collection);
            // return new EntitiesCollection(new \Zend\Paginator\Adapter\Iterator($entities));

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
                    if(!empty($this->_settings['entity'])){
                        if(!empty($this->_settings['entity']['storage'])){
                            if(!empty($this->_settings['entity']['storage']['joins'])){
                                $storage = $this->_settings['entity']['storage'];
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
                    // $postwhere->isNull('entity_translations.Language');

                    // $entities = $this->_model->getAllExtended($storage, $dbfilterwhere, $postwhere);
                    $entities = $this->_model->getAllExtended($storage, $dbfilterwhere);
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

                    $entities = new \ArrayIterator(array());
                    // $entities = new \ArrayIterator(array('message'=>'Please specify a valid limit with filters'));
                }    
                
            }
            else
            {
                // exclude 99
                $entities = $this->_model->fetchAll(array('where'=>array('entities.Status' => array(0,1))));
            }    
            
            return new EntitiesCollection(new \Zend\Paginator\Adapter\Iterator($entities));
            
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

            $this->getEventManager()->trigger('entities.patch.pre', $this, array());
            
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
                /*$tentityClass = $this->_entityTranslations->getEntityClass();
                $tentity = new $tentityClass();*/
                $tentity = $this->_entityTranslations->getByEntityIdAndLanguage($id, $lang);
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
                /*$tentity->EntityId = $id;
                $tentity->Language = $lang;*/
                $tupdateData = $tentity->getArrayCopy();
                /*unset($tupdateData['Id']);
                unset($tupdateData['CreationDate']);*/
                
                // $this->_entityTranslations->update($tupdateData, array('EntityId' => $id, 'Language' => $lang));
                 $this->_entityTranslations->update($tupdateData, array('Id' => $tentity->Id));
            // }

            $this->getEventManager()->trigger('entities.patch.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();
            
            return $this->fetch($id);
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

            $this->getEventManager()->trigger('entities.update.pre', $this, array());
            
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
                /*$tentityClass = $this->_entityTranslations->getEntityClass();
                $tentity = new $tentityClass();*/
                $tentity = $this->_entityTranslations->getByEntityIdAndLanguage($id, $lang);
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

                /*$tentity->EntityId = $id;
                $tentity->Language = $lang;*/
                $tupdateData = $tentity->getArrayCopy();
                /*unset($tupdateData['Id']);
                unset($tupdateData['CreationDate']);*/
                
                // $this->_entityTranslations->update($tupdateData, array('EntityId' => $id, 'Language' => $lang));
                 $this->_entityTranslations->update($tupdateData, array('Id' => $tentity->Id));
            // }

            $this->getEventManager()->trigger('entities.update.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();
            
            return $this->fetch($id);
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