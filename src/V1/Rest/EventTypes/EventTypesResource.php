<?php
namespace MicroIceEventManager\V1\Rest\EventTypes;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EventTypesResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_translations;
    
    private $_settings;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $Translations, $Settings)
    {
        $this->_model = $Model;
        $this->_translations = $Translations;

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
            $this->getEventManager()->trigger('event_types.create.pre', $this, array());

            $id = $this->getEvent()->getRouteParam('event_type_id');
            $lang = $this->getEvent()->getRouteParam('language_code');
            if('*' == $lang || '*' == $id){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $default = empty($id) ? true: false;
            $rdata = (array)$data;

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
                    $texists = $this->_translations->getByNameAndStatusAndLanguage($rdata['Name'], $entity->Status, $lang);
                    if(!empty($texists)){
                        throw new \InvalidArgumentException("An event type with same name already exists!", 1);
                    }
                }

                // persist entity
                $inserted = $this->_model->insert($entity->getArrayCopy());
                if(!$inserted){
                    throw new \Exception("Could not create event type", 1);
                }
                $entity->Id = $this->_model->getLastInsertValue();
            }
            else
            {
                $entity = $this->_model->getById($id);
                if( !$entity || $entity->Status == 99 ){
                    throw new \InvalidArgumentException("Please specify a valid event type!", 1);
                }
            }
            
            $entityTransClass = $this->_translations->getEntityClass();
            $translation = new $entityTransClass();
            
            $translation->exchangeArray($rdata);

            $translation->TypeId = $entity->Id;
            $translation->Language = $lang;
            $translation->CreationDate = $creationDate;

            $inserted = $this->_translations->insert($translation->getArrayCopy());
            if(!$inserted){
                throw new \Exception("Could not create event type translation", 2);
            }

            $entity->Language = $lang;
            $entity->TranslationId = $this->_translations->getLastInsertValue();
            $entity->Name = $translation->Name;
            
            $this->getEventManager()->trigger('event_types.create.post', $this, array('entity' => $entity, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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

            /*$type = $this->_model->getById($id);

            if(!$type || $type->Status != 1){
                throw new \InvalidArgumentException("Please specify an active event type!", 1);
            }
            unset($type);*/
            
            $this->getEventManager()->trigger('event_types.delete.pre', $this, array());

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            // deleting type
            $result = $this->_model->delete(array('Id' => $id));
            if(!$result){
                throw new \Exception("Error Processing Request", 1);
            }

            $this->getEventManager()->trigger('event_types.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            return (bool)$result;
        }
        catch(\Exception $e)
        {
            if (!empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface){
                $connection->rollback();
            }
            return new ApiProblem(417, $e->getMessage());
        }
        // return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
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
                if(!empty($this->_settings['event_type'])){
                    if(!empty($this->_settings['event_type']['storage'])){
                        if(!empty($this->_settings['event_type']['storage']['joins'])){
                            $storage = $this->_settings['event_type']['storage'];
                        }
                    }
                }

                /*$where = new \Zend\Db\Sql\Where();
                $where->equalTo('event_types.Id',$id);*/
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
                if(!$entity || $entity->Status == 99 ){
                    if(!isset($entities)){
                        throw new \InvalidArgumentException("Error Processing Request", 1);
                    }
                }
                if(!isset($entities) && isset($entity)){
                    $entities = array($entity);
                    $entities = new \ArrayIterator($entities);
                }
            }

            /*if(1 == count($entities) && '*' != $lang){
                // return $entity;
                return reset($entities);
            }*/

            return new EventTypesCollection(new \Zend\Paginator\Adapter\Iterator($entities));
            
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
                    if(!empty($this->_settings['event_type'])){
                        if(!empty($this->_settings['event_type']['storage'])){
                            if(!empty($this->_settings['event_type']['storage']['joins'])){
                                $storage = $this->_settings['event_type']['storage'];
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
                    // $postwhere->isNull('event_type_translations.Language');

                    // $entities = $this->_model->getAllExtended($storage, $dbfilterwhere, $postwhere);
                    $entities = $this->_model->getAllExtended($storage, $dbfilterwhere);
                }
                else
                {
                    throw new \Exception("Request Entity Too Large", 413);
                    
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
                $entities = $this->_model->fetchAll(array('where'=>array('event_types.Status' => array(0,1))));    
            }

            return new EventTypesCollection(new \Zend\Paginator\Adapter\Iterator($entities));
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
        try
        {
            if(!isset($id) || empty($id) || '*' == $id){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }
            
            $lang = $this->getEvent()->getRouteParam('language_code');
            if('*' == $lang){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $this->getEventManager()->trigger('event_types.patch.pre', $this, array());

            $rdata = (array)$data;

            // start trasaction as we need to delete also other information like price categories and other stuff
            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            if(!isset($lang) && empty($lang)){
                //update main entity
                /*$entityClass = $this->_model->getEntityClass();
                $entity = new $entityClass();*/
                $entity = $this->_model->select(array('Id' => $id))->current();
                if(!$entity){
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

            // update translated event
            // if($lang && '*' != $lang)
            // {
                /*$tentityClass = $this->_translations->getEntityClass();
                $tentity = new $tentityClass();*/
                $tentity = $this->_translations->getByTypeIdAndLanguage($id, $lang);
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
                /*$tentity->TypeId = $id;
                $tentity->Language = $lang;*/
                $tupdateData = $tentity->getArrayCopy();
                /*unset($tupdateData['Id']);
                unset($tupdateData['CreationDate']);*/
                
                // $this->_translations->update($tupdateData, array('TypeId' => $id, 'Language' => $lang));
                 $this->_translations->update($tupdateData, array('Id' => $tentity->Id));
            // }
            
            $this->getEventManager()->trigger('event_types.patch.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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
        try
        {
            if(!isset($id) || empty($id) || '*' == $id){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }
            
            $lang = $this->getEvent()->getRouteParam('language_code');
            if(!empty($lang)){
                return new ApiProblem(405, 'The PUT method has not been defined for translated resources');
            }

            $this->getEventManager()->trigger('event_types.update.pre', $this, array());

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
                if(!$entity){
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

            // update translated event
            // if($lang && '*' != $lang)
            // {
                /*$tentityClass = $this->_translations->getEntityClass();
                $tentity = new $tentityClass();*/
                $tentity = $this->_translations->getByTypeIdAndLanguage($id, $lang);
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

                /*$tentity->TypeId = $id;
                $tentity->Language = $lang;*/
                $tupdateData = $tentity->getArrayCopy();
                /*unset($tupdateData['Id']);
                unset($tupdateData['CreationDate']);*/
                
                // $this->_translations->update($tupdateData, array('TypeId' => $id, 'Language' => $lang));
                 $this->_translations->update($tupdateData, array('Id' => $tentity->Id));
            // }
            
            $this->getEventManager()->trigger('event_types.update.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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