<?php
namespace MicroIceEventManager\V1\Rest\EntityData;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use MicroIceEventManager\V1\Rest\EntityDataFields\EntityDataFieldsEntity;
use MicroIceEventManager\V1\Rest\Entities\EntitiesEntity;
use MicroIceEventManager\V1\Rest\EntitiesData\EntitiesDataEntity;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EntityDataResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_dataFields;
    private $_dataFieldConfig;
    private $_dataType;
    private $_entitiesData;
    private $_entities;
    private $_preferencesModel;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $DataFields, TableGatewayInterface $DataFieldConfig, TableGatewayInterface $DataType, TableGatewayInterface $EntitiesData, TableGatewayInterface $Entities, $DataPreferencesModel)
    {
        $this->_model = $Model;
        $this->_dataFields = $DataFields;
        $this->_dataFieldConfig = $DataFieldConfig;
        $this->_dataType = $DataType;
        $this->_entitiesData = $EntitiesData;
        $this->_entities = $Entities;
        $this->_preferencesModel = $DataPreferencesModel;
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
            // validate entity data type
            $dataType = $this->getEvent()->getRouteParam('entity_data_type_id');
            if(!isset($dataType) || empty($dataType)){
                throw new \InvalidArgumentException("Please specify a type for the entity data!", 1);
            }
            
            $eTranslationId = $this->getEvent()->getRouteParam('entity_translation_id');
            if(!isset($eTranslationId) || empty($eTranslationId)){
                throw new \InvalidArgumentException("Please specify an entity!", 1);
            }

            // $type = $this->_dataType->getByName($dataType);
            $type = $this->_dataType->getById($dataType);

            if(!$type || $type->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid entity data type!", 1);
            }
            $typeId = $type->Id;
            unset($type);

            // validate entity
            /*$entity = $this->_entities->getById($eTranslationId);

            if(!$entity || $entity->Status != 1){
                throw new \InvalidArgumentException("Please specify an active entity!", 1);
            }*/
            
            $this->getEventManager()->trigger('entity_data.create.pre', $this, array());

            // create entity
            $data = (array)$data;
            $creationDate = date('Y-m-d H:i:s');
            $entitydata = new EntityDataEntity();
            // $entitydata->exchangeArray($data);

            $entitydataArray = $entitydata->getArrayCopy();
            foreach ($entitydataArray as $key => $value) 
            {
                $entitydata[$key] = $data[$key];
                unset($data[$key]);
            }
            $entitydata->TypeId = $typeId;
            $entitydata->CreationDate = $creationDate;

            // var_dump($entitydata, $data);exit(__FILE__.'::'.__LINE__);

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();
            
            // persist entity
            $inserted = $this->_model->insert($entitydata->getArrayCopy());

            if($inserted){
                $entitydata->Id = $this->_model->getLastInsertValue();
            }

            // validate insert
            $entitydataId = $entitydata->Id;
            if(!isset($entitydataId) || empty($entitydataId)){
                throw new \Exception("Could not save entity data!", 1);
            }

            $fieldconfigs = $this->_dataFieldConfig->getAllByTypeId($typeId);
            // var_dump($fieldconfigs->toArray());exit(__FILE__.'::'.__LINE__);
            if($fieldconfigs->count() > 0)
            {
                $needpersist = false;
                foreach ($fieldconfigs as $fidx => $fieldconfig) 
                {
                    $fname = $fieldconfig->Field;
                    $fvalue = null;
                    if(isset($data[$fname])){
                        $fvalue = $data[$fname];
                    }
                    
                    // validate required
                    $required = $fieldconfig->Required;
                    if($required){
                        if(!$fvalue){
                            throw new \InvalidArgumentException("$fname is mandatory", 1);
                        }
                    }

                    // validate against pattern
                    $pattern = $fieldconfig->Pattern;
                    if( !empty($fvalue) && !empty($pattern)){
                        if (!preg_match("/$pattern/", $fvalue)) {
                            throw new \InvalidArgumentException("Invalid $fname", 1);
                        }
                    }

                    // assign to our entity to output purposes
                    $entitydata->$fname = $fvalue;

                    // if field value is null and filed is optional then don't add to persist
                    if(NULL === $fvalue){
                        continue;
                    }

                    // create entity
                    $datafield = new EntityDataFieldsEntity();
                    $datafield->Field = $fname;
                    $datafield->Value = $fvalue;
                    $datafield->Category = isset($data['Category'])?$data['Category']:null;
                    $datafield->DataId = $entitydata->Id;
                    $datafield->Status = 1;

                    

                    // prepare for persist
                    $resultdf = $this->_dataFields->createBulkInsert($datafield);
                    if(empty($resultdf)){
                        if($required){
                            throw new \InvalidArgumentException("Could not persist $fname which is mandatory", 1);    
                        }
                    }else{
                        $needpersist = true;
                    }
                    // var_dump($resultdf);exit(__FILE__.'::'.__LINE__);
                }

                // persist
                if($needpersist)
                {
                    $result = $this->_dataFields->runBulkInsert(false);
                    if(!isset($result) || empty($result)){
                        throw new \Exception("Could not persist additional fields", 1);
                    }
                }
                
            }

            // assign entity to the specified entity
            $entitiesData = new EntitiesDataEntity();
            $entitiesData->EntityId = $eTranslationId;
            $entitiesData->DataId = $entitydata->Id;
            $entitiesData->Status = $entitydata->Status;

            $assigned = $this->_entitiesData->insert($entitiesData->getArrayCopy());
            if(!isset($assigned) || empty($assigned)){
                throw new \Exception("Could not assign data to entitiesData!", 1);
            }

            $this->getEventManager()->trigger('entity_data.create.post', $this, array('entity' => $entitydata, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            return $entitydata;
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
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        try
        {
            // validate entity data type
            $dataType = $this->getEvent()->getRouteParam('entity_data_type_id');
            if(!isset($dataType) || empty($dataType)){
                throw new \InvalidArgumentException("Please specify a type for the entity data!", 1);
            }

            $eTranslationId = $this->getEvent()->getRouteParam('entity_translation_id');
            if(!isset($eTranslationId) || empty($eTranslationId)){
                throw new \InvalidArgumentException("Please specify an entity!", 1);
            }

            $typeId = $dataType;
            // $type = $this->_dataType->getByName($dataType);
           /* $type = $this->_dataType->getById($dataType);

            if($type->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid entity data type!", 1);
            }
            $typeId = $type->Id;
            unset($type);*/

            // validate entity
            /*$entity = $this->_entities->getById($eTranslationId);

            if($entity->Status != 1){
                throw new \InvalidArgumentException("Please specify an active entity!", 1);
            }*/
            
            $this->getEventManager()->trigger('entity_data.delete.pre', $this, array());

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            // delete the actual entity data
            $ok = $this->_model->delete(array('TypeId' => $typeId, 'Id' => $id));
            
            // if nothing was deleted then we must stop here because a security breach can be here if combine wrong id with type id as bellow we delete by id only
            if(!$ok){
                throw new \Exception("Error deleting entity data", 1);
            }

            // delete all entity data fields
            $this->_dataFields->delete(array('DataId' => $id));
            
            // delete all entity data preferences
            // $this->_entityDataPreferences->delete(array('DataId' => $id));

            // delete all assigment of entities on this data
            $this->_entitiesData->delete(array('DataId' => $id));
            
            $this->getEventManager()->trigger('entity_data.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            return TRUE;
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

            // validate entity data type
            $dataType = $this->getEvent()->getRouteParam('entity_data_type_id');
            if(!isset($dataType) || empty($dataType)){
                throw new \InvalidArgumentException("Please specify a type for the entity data!", 1);
            }

            $eTranslationId = $this->getEvent()->getRouteParam('entity_translation_id');
            if(!isset($eTranslationId) || empty($eTranslationId)){
                throw new \InvalidArgumentException("Please specify an entity!", 1);
            }

            // $type = $this->_dataType->getByName($dataType);
            $type = $this->_dataType->getById($dataType);

            if(!$type || $type->Status != 1 ){
                throw new \InvalidArgumentException("Please specify a valid entity data type!", 1);
            }
            $typeId = $type->Id;
            unset($type);

            // validate entity
            /*$entity = $this->_entities->getById($eTranslationId);

            if($entity->Status != 1 && $entity->Status != 0){
                throw new \InvalidArgumentException("Please specify an active entity!", 1);
            }*/

            $entityData = $this->_model->getByIdAndTypeId($id, $typeId);

            $id = $entityData->Id;
            if(!isset($id) || empty($id) || $entityData->Status == 99){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            // validate data is assigned to the required entity
            $entitiesData = $this->_entitiesData->getByEntityIdAndDataId($eTranslationId, $id);
            $entitiesdataid = $entitiesData->Id;
            if(!isset($entitiesdataid) || empty($entitiesdataid) || $entitiesData->Status == 99){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            // add extra defined fields if any
            $fieldconfigs = $this->_dataFieldConfig->getAllByTypeId($typeId);
            if($fieldconfigs->count() > 0)
            {
                foreach ($fieldconfigs as $key => $fieldconfig) {
                    $fname = $fieldconfig->Field;
                    $entityData->$fname = null;
                    unset($fname);
                }

                $xtraFields = $this->_dataFields->getAllByDataId($id);
                foreach ($xtraFields as $key => $xtra) {
                    $fname = $xtra->Field;
                    $entityData->$fname = $xtra->Value;
                    unset($fname);
                }
            }

            return $entityData;
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
            // validate entity data type
            $dataType = $this->getEvent()->getRouteParam('entity_data_type_id');
            if(!isset($dataType) || empty($dataType)){
                throw new \InvalidArgumentException("Please specify a type for the entity data!", 1);
            }

            $eTranslationId = $this->getEvent()->getRouteParam('entity_translation_id');
            if(!isset($eTranslationId) || empty($eTranslationId)){
                throw new \InvalidArgumentException("Please specify an entity!", 1);
            }

            // $type = $this->_dataType->getByName($dataType);
            $type = $this->_dataType->getById($dataType);

            if(!$type || $type->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid entity data type!", 1);
            }
            $typeId = $type->Id;
            unset($type);

            // validate entity
            /*$entity = $this->_entities->getById($eTranslationId);

            if($entity->Status != 1 && $entity->Status != 0){
                throw new \InvalidArgumentException("Please specify an active entity!", 1);
            }*/

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
                    $where = null;
                    if(!empty($params['filter'])){
                        $where = $params['filter'];
                    }

                    $entityData = $this->_model->fetchAllByEntityIdAndTypeId($eTranslationId, $typeId);
                    // $entityData = $this->_model->fetchAllByEntityId($eTranslationId, $where);

                    // create an array for easy of use
                    $xtraFields = array();
                    // get extra fields for this type to add to entities
                    $fieldconfigs = $this->_dataFieldConfig->getAllByTypeId($typeId);
                    foreach ($fieldconfigs as $key => $fieldconfig) {
                        $xtraFields[] = $fieldconfig->Field;
                    }

                    // prepare for assigment extra fields
                    $entitydataIds = array();
                    $entityDatas = array();
                    foreach ($entityData as $key => $udata) {
                        $entitydataIds[] = $udata->Id;
                        foreach ($xtraFields as $xtraField) {
                            $udata->$xtraField = null;
                        }
                        $entityDatas[$udata->Id] = $udata;
                    }
                    // interswitch
                    $entityData = $entityDatas;
                    unset($entityDatas);

                    if(!isset($entitydataIds) || empty($entitydataIds)){
                        return;
                    }

                    // get extra fields for all entity data
                    $entityDataFields = $this->_dataFields->fetchAllByDataId($entitydataIds);

                    // assign extra fields on each entity data
                    foreach ($entityDataFields as $key => $udatafield) {
                        $fname = $udatafield->Field;
                        $entityData[$udatafield->DataId]->$fname = $udatafield->Value;
                        unset($fname);
                    }

                    // convert to iterator
                    $entityData = new \ArrayIterator($entityData);
                }
                else
                {

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

                    \Zend\EventManager\StaticEventManager::getInstance()->attach(
                    'ZF\Hal\Plugin\Hal', 'renderCollection.post', function ($e) {

                        // $halCollection = $e->getParam('collection');
                        $payload = $e->getParam('payload');
                        unset($payload['_embedded']);
                        unset($payload['page_count']);
                        unset($payload['page_size']);
                        unset($payload['total_items']);
                        unset($payload['page']);

                        // var_dump($payload['_links']);exit(__FILE__.'::'.__LINE__);

                    }); 

                    $entityData = new \ArrayIterator(array());
                }    
                
            }
            else
            {
                // exclude 99
                $entityData = $this->_model->fetchAllByEntityIdAndTypeId($eTranslationId, $typeId);
            }    
            
            return new EntityDataCollection(new \Zend\Paginator\Adapter\Iterator($entityData));
            
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
     * Patch (partial in-place update) a resource
     *
     * @param  mixed $id
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function patch($id, $data)
    {
        return $this->update($id, $data);
        // return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
        
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
            // validate entity data type
            $dataType = $this->getEvent()->getRouteParam('entity_data_type_id');
            if(!isset($dataType) || empty($dataType)){
                throw new \InvalidArgumentException("Please specify a type for the entity data!", 1);
            }

            $eTranslationId = $this->getEvent()->getRouteParam('entity_translation_id');
            if(!isset($eTranslationId) || empty($eTranslationId)){
                throw new \InvalidArgumentException("Please specify an entity!", 1);
            }

            // $type = $this->_dataType->getByName($dataType);
            $type = $this->_dataType->getById($dataType);

            if(!$type || $type->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid entity data type!", 1);
            }
            $typeId = $type->Id;
            unset($type);

            // validate entity
            /*$entity = $this->_entities->getById($eTranslationId);

            if(!$entity || $entity->Status != 1){
                throw new \InvalidArgumentException("Please specify an active entity!", 1);
            }*/

            // select entity
            $entitydata = $this->_model->getByIdAndTypeId($id, $typeId);

            if(empty($entitydata)){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }
            $id = $entitydata->Id;
            if(!isset($id) || empty($id) || $entitydata->Status == 99){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }
            
            $this->getEventManager()->trigger('entity_data.update.pre', $this, array());

            // populate with patch values
            $data = (array)$data;
            $entitydataArray = $entitydata->getArrayCopy();
            // var_dump($entitydataArray);exit(__FILE__.'::'.__LINE__);

            $needupdate = false; //flag if main entity need updated
            foreach ($entitydataArray as $key => $value) 
            {
                if(in_array($key, array('Timestamp','CreationDate'))) {
                    unset($data[$key]);
                    continue;
                }
                if(isset($data[$key])){
                    $entitydata[$key] = $data[$key];
                    unset($data[$key]);
                    $needupdate = true;
                }
            }

            // make sure id and type stay the same 
            $entitydata->TypeId = $typeId;
            $entitydata->Id = $id;
            // var_dump($entitydata->getArrayCopy());exit(__FILE__.'::'.__LINE__);

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();
            
            // update entity
            if($needupdate){
                $updated = $this->_model->update($entitydata->getArrayCopy(),array('Id' => $id));
            }    
            
            $fieldconfigs = $this->_dataFieldConfig->getAllByTypeId($typeId);
            // var_dump($fieldconfigs->toArray());exit(__FILE__.'::'.__LINE__);
            if($fieldconfigs->count() > 0)
            {
                // attach extra field to the main entity
                $datafields = $this->_dataFields->fetchAllByDataId($entitydata->Id);
                $xtraFields = array();
                foreach ($datafields as $key => $datafield) {
                    $field = $datafield->Field;
                    $entitydata->$field = $datafield->Value;
                    $xtraFields[$field] = $datafield;
                    unset($field);
                }

                foreach ($fieldconfigs as $fidx => $fieldconfig) 
                {
                    $fname = $fieldconfig->Field;
                    $fvalue = null;
                    if(!isset($data[$fname])){
                        continue;
                    }

                    $fvalue = $data[$fname];

                    // select entity
                    // $datafield = $this->_dataFields->getByDataIdAndField($entitydata->Id, $fname);
                    if(isset($xtraFields[$fname])){
                        $datafield = $xtraFields[$fname];
                        unset($xtraFields[$fname]);
                    }else{
                        $datafield = new EntityDataFieldsEntity();
                    }

                    $datafieldId = $datafield->Id;
                    $insert = false;
                    if(!isset($datafieldId) || empty($datafieldId)){
                        // continue;
                        $datafield->DataId = $entitydata->Id;
                        $datafield->Field = $fname;
                        $insert = true;
                    }
                    
                    // validate required
                    $required = $fieldconfig->Required;
                    if($required){
                        if(!$fvalue){
                            throw new \InvalidArgumentException("$fname is mandatory", 1);
                        }
                    }

                    // validate against pattern
                    $pattern = $fieldconfig->Pattern;
                    if( !empty($fvalue) && !empty($pattern)){
                        if (!preg_match("/$pattern/", $fvalue)) {
                            throw new \InvalidArgumentException("Invalid $fname", 1);
                        }
                    }
                    
                    $datafield->Value = $fvalue;
                    $datafield->Status = $entitydata->Status;
                    // $datafield->save();
                    // var_dump($datafield);exit(__FILE__.'::'.__LINE__);
                    if($insert){
                        $this->_dataFields->insert($datafield->getArrayCopy());
                    }else{
                        $this->_dataFields->update($datafield->getArrayCopy(), array('Id' => $datafield->Id));
                    }

                    // add extra field to our entity
                    $entitydata->$fname = $fvalue;
                }

            }

            $this->getEventManager()->trigger('entity_data.update.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            // attach extra field to the main entity
            /*$datafields = $this->_dataFields->fetchAllByDataId($entitydata->Id);
            foreach ($datafields as $key => $datafield) {
                $field = $datafield->Field;
                $entitydata->$field = $datafield->Value;
                unset($field);
            }*/

            return $entitydata;
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
        // return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }
}
