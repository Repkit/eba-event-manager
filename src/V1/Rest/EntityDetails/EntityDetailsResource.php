<?php
namespace MicroIceEventManager\V1\Rest\EntityDetails;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use MicroIceEventManager\V1\Rest\Entities\EntitiesEntity;
use MicroIceEventManager\V1\Rest\EntityTypes\EntityTypesEntity;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EntityDetailsResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_modelFields;
    private $_types;
    private $_entities;
    private $_entitiesTypes;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface$ModelFields, TableGatewayInterface $Types, TableGatewayInterface $Entities, TableGatewayInterface $EntitiesTypes)
    {
        $this->_model = $Model;
        $this->_modelFields = $ModelFields;
        $this->_types = $Types;
        $this->_entities = $Entities;
        $this->_entitiesTypes = $EntitiesTypes;
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

            // validate entity type
            $entitytypedid = $this->getEvent()->getRouteParam('entity_type_id');
            if(!isset($entitytypedid) || empty($entitytypedid)){
                throw new \InvalidArgumentException("Please specify a type for the entity!", 1);
            }

            $eTranslationId = $this->getEvent()->getRouteParam('entity_translation_id');
            if(!isset($eTranslationId) || empty($eTranslationId)){
                throw new \InvalidArgumentException("Please specify an entity!", 1);
            }

            $entityType = $this->_types->validateTypeAndEntityTranslationId($entitytypedid, $eTranslationId);

            if(!$entityType || $entityType->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid entity type or translation!", 1);
            }
            $entityAssigned = $entityType->Assigned;
            $entityid = $entityType->EntityId;
            unset($entityType);

            /*$entity = $this->_entities->getById($eTranslationId);

            if(!$entity || $entity->Status != 1){
                throw new \Exception("Please specify an active entity!", 1);
            }*/
            
            $this->getEventManager()->trigger('entity_details.create.pre', $this, array());

            if(is_object($data)){
                $data = get_object_vars($data);
            }

            $fields = $this->_modelFields->getAllByTypeId($entitytypedid);
            
            foreach ($fields as $key => $field) 
            {

                $fname = $field->Field;
                $fvalue = null;
                if(isset($data[$fname])){
                    $fvalue = $data[$fname];
                }
                
                // validate required
                $required = $field->Required;
                if($required){
                    if(!$fvalue){
                        throw new \InvalidArgumentException("$fname  is mandatory", 1);
                    }
                }

                // validate against pattern
                $pattern = $field->Pattern;
                if( !empty($fvalue) && !empty($pattern)){
                    if (!preg_match("/$pattern/", $fvalue)) {
                        throw new \InvalidArgumentException("Invalid $fname", 1);
                    }
                }

                if(is_null($fvalue)){
                    continue;
                }

                // create entity
                $udetails = new EntityDetailsEntity();
                $udetails->EntityId = $eTranslationId;
                $udetails->Field = $fname;
                $udetails->Value = $fvalue;
                $udetails->Category = null;
                $udetails->TypeId = $entitytypedid;
                $udetails->Status = 1;

                

                // prepare for persist
                $result = $this->_model->createBulkInsert($udetails);
                // var_dump($result);exit(__FILE__.'::'.__LINE__);

            }

            // start trasaction as we need to delete also other information like price categories and other stuff
            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            //clear old data for this entity
            $this->_model->delete(array('EntityId' => $eTranslationId, 'TypeId' => $entitytypedid));

            // persist
            $result = $this->_model->runBulkInsert(false);
            // var_dump($result);exit(__FILE__.'::'.__LINE__);

            /*if($persist){
                // set entity type if anything is ok
                $this->_model->setentitytype();
            }*/

            if(!$entityAssigned){
                $ptClass = $this->_entitiesTypes->getEntityClass();
                $pt = new $ptClass();
                $pt->TypeId = $entitytypedid;
                $pt->EntityId = $entityid;
                $pt->Status = 1;

                $inserted = $this->_entitiesTypes->insert($pt->getArrayCopy());
                if(!$inserted){
                    throw new \Exception("Could not assign entity on type", 1);
                }
            }
            
            $this->getEventManager()->trigger('entity_details.create.post', $this, array('entity' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );
            
            $connection->commit();
            
            return (bool)$result;
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
        return new ApiProblem(405, 'The DELETE method has not been defined for individual resources');
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
        // return new ApiProblem(405, 'The GET method has not been defined for individual resources');
        try
        {
            $eTranslationId = $this->getEvent()->getRouteParam('entity_translation_id');
            if(!isset($eTranslationId) || empty($eTranslationId)){
                throw new \InvalidArgumentException("Please specify an entity!", 1);
            }

            // $entitytypedid = $this->getEvent()->getRouteParam('entity_type_id');
            if(!isset($id) || empty($id)){
                throw new \InvalidArgumentException("Please specify a type for the entity!", 1);
                // $entityDetails = $this->_model->getAllByEntityId($id);
                // return new EntityDetailsCollection(new \Zend\Paginator\Adapter\Iterator($entityDetails));
            }

            $entityType = $this->_types->getById($id);

            if(!$entityType || $entityType->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid entity type!", 1);
            }
            unset($entityType);

            $entityDetails = $this->_model->getAllByEntityIdAndTypeId($eTranslationId, $id);

            return new EntityDetailsCollection(new \Zend\Paginator\Adapter\Iterator($entityDetails));
            // return $eventDetails->toArray();
        }
        catch(\InvalidArgumentException $e)
        {
            return new ApiProblem(400, $e->getMessage());
        }
        catch(\Exception $e)
        {
            return new ApiProblem(417, $e->getMessage());
        }
        // return new ApiProblem(405, 'The GET method has not been defined for individual resources');
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
            $eTranslationId = $this->getEvent()->getRouteParam('entity_translation_id');
            if(!isset($eTranslationId) || empty($eTranslationId)){
                throw new \InvalidArgumentException("Please specify an entity!", 1);
            }
            
            $entityFields = $this->_model->getAllByEntityId($eTranslationId);
            if(!$entityFields){
                $entityFields = array();
            }

            $types = array();
            foreach ($entityFields as $key => $field) {
                // var_dump($field);exit(__FILE__.'::'.__LINE__);
                  $field = $field->getArrayCopy();
                  $typeId = $field['TypeId'];
                  unset($field['TypeId']);
                  unset($field['EntityId']);
                  if(!isset($typeId) || empty($typeId)){
                    continue;
                  }
                  if(!isset($types[$typeId])){
                    $types[$typeId] = array();
                  }
                  $types[$typeId][] = $field;
            }

            $result = array('types' => $types);

            // $data = new \ArrayIterator($result);
            $data = new \Zend\Paginator\Adapter\ArrayAdapter($result);
            // var_dump($data);exit(__FILE__.'::'.__LINE__);
            // return new EntityDetailsCollection(new \Zend\Paginator\Adapter\Iterator($data));
            return new EntityDetailsCollection($data);
        }
        catch(\InvalidArgumentException $e)
        {
            return new ApiProblem(400, $e->getMessage());
        }
        catch(\Exception $e)
        {
            return new ApiProblem(417, $e->getMessage());
        }
        // return new ApiProblem(405, 'The GET method has not been defined for collections');
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
            $data = (array)$data;
            if( empty($data) ){
                throw new \InvalidArgumentException('Invalid patch data');
            }
            // validate entity type
            $entitytypedid = $this->getEvent()->getRouteParam('entity_type_id');
            if(!isset($entitytypedid) || empty($entitytypedid)){
                throw new \InvalidArgumentException("Please specify a type for the entity!", 1);
            }

            $eTranslationId = $this->getEvent()->getRouteParam('entity_translation_id');
            if(!isset($eTranslationId) || empty($eTranslationId)){
                throw new \InvalidArgumentException("Please specify an entity!", 1);
            }

            $entityType = $this->_types->getById($entitytypedid);

            if(!$entityType || $entityType->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid entity type!", 1);
            }
            unset($entityType);

            /*$entity = $this->_entities->getById($eTranslationId);

            if(!$entity || $entity->Status != 1){
                throw new \InvalidArgumentException("Please specify an active entity!", 1);
            }*/

            if(is_object($data)){
                $data = get_object_vars($data);
            }

            $fields = $this->_modelFields->getAllByTypeId($entitytypedid);
            if( !$fields->count() ){
                throw new \InvalidArgumentException('Invalid type fields');
            }

            $this->getEventManager()->trigger('entity_details.patch.pre', $this, array());

            $updatedFields = array();
            foreach ($fields as $key => $field)
            {

                $fname = $field->Field;
                if( !isset($data[$fname]) ){
                    continue;
                }
                $fvalue = $data[$fname];
                // validate required
                $required = $field->Required;
                if($required){
                    if(!$fvalue){
                        throw new \InvalidArgumentException("$fname  is mandatory", 1);
                    }
                }

                // validate against pattern
                $pattern = $field->Pattern;
                if( !empty($fvalue) && !empty($pattern)){
                    if (!preg_match("/$pattern/", $fvalue)) {
                        throw new \InvalidArgumentException("Invalid $fname", 1);
                    }
                }
                $updatedFields[] = $fname;

                // create entity
                $udetails = new EntityDetailsEntity();
                $udetails->EntityId = $eTranslationId;
                $udetails->Field = $fname;
                $udetails->Value = $fvalue;
                $udetails->Category = null;
                $udetails->TypeId = $entitytypedid;
                $udetails->Status = 1;



                // prepare for persist
                $result = $this->_model->createBulkInsert($udetails);
            }

            if( empty($updatedFields) ){
                throw new \InvalidArgumentException('Invalid update fields');
            }

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            //clear old data for this event
            $this->_model->delete(array('EntityId' => $eTranslationId, 'TypeId' => $entitytypedid,'Field' => $updatedFields));

            // persist
            $result = $this->_model->runBulkInsert(false);

            $this->getEventManager()->trigger('entity_details.patch.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            return (bool)$result;
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
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }
}
