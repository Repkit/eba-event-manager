<?php
namespace MicroIceEventManager\V1\Rest\EntityDetailFields;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use MicroIceEventManager\V1\Rest\EntityTypes\EntityTypesEntity;
use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EntityDetailFieldsResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_types;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $Types)
    {
        $this->_model = $Model;
        $this->_types = $Types;
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

            $entityType = $this->_types->getById($entitytypedid);

            if(!$entityType || $entityType->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid entity type!", 1);
            }
            unset($entityType);   
            
            $this->getEventManager()->trigger('entity_detail_fields.create.pre', $this, array());

            $creationDate = date('Y-m-d H:i:s');

            $field = new EntityDetailFieldsEntity();
            $field->hydrate($data);
            $field->TypeId = $entitytypedid;
            $field->CreationDate = $creationDate;

            //make sure wont define a field that is predefined already
            $entity = new \MicroIceEventManager\V1\Rest\Entities\EntitiesEntity();
            $predefinedFields = $entity->toArray();
            if(array_key_exists($field->Field, $predefinedFields)){
                throw new \InvalidArgumentException("{$field->Field} is a predefined mandatory field!", 1);
            }
            unset($entity);
            unset($predefinedFields);
            
            $required = filter_var($field->Required, FILTER_VALIDATE_BOOLEAN);
            $field->Required = $required;

            $multiple = filter_var($field->Multiple, FILTER_VALIDATE_BOOLEAN);
            $field->Multiple = $multiple;

            // persist entity
            $inserted = $this->_model->insert($field->getArrayCopy());

            if($inserted){
                $field->Id = $this->_model->getLastInsertValue();
            }
            
            $this->getEventManager()->trigger('entity_detail_fields.create.post', $this, array('entity' => $field, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            return $field;
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
     * Delete a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function delete($id)
    {
        try
        {
            if(!isset($id) || empty($id)){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }
            
            // validate entity type
            $entitytypedid = $this->getEvent()->getRouteParam('entity_type_id');
            if(!isset($entitytypedid) || empty($entitytypedid)){
                throw new \InvalidArgumentException("Please specify a type for the entity!", 1);
            }

            /*$entityType = $this->_types->getById($entitytypedid);
            if($entityType->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid entity type!", 1);
            }
            unset($entityType); */
            
            $this->getEventManager()->trigger('entity_detail_fields.delete.pre', $this, array());

            $result = false;

            /*$field = $this->_model->getById($id);

            if(is_null($field->Status)){
                throw new \Exception("Unable to delete entity!", 1);
            }*/

            $result = $this->_model->delete(array('Id' => $id));
            $result = (bool)$result;
            
            $this->getEventManager()->trigger('entity_detail_fields.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            return $result;
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

            // validate entity type
            $entitytypedid = $this->getEvent()->getRouteParam('entity_type_id');
            if(!isset($entitytypedid) || empty($entitytypedid)){
                throw new \InvalidArgumentException("Please specify a type for the entity!", 1);
            }

            $entityType = $this->_types->getById($entitytypedid);

            if(!$entityType || $entityType->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid entity type!", 1);
            }
            unset($entityType); 

            $field = $this->_model->getByIdAndTypeId($id, $entitytypedid);

            return $field;
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
            // validate entity type
            $entitytypedid = $this->getEvent()->getRouteParam('entity_type_id');
            if(!isset($entitytypedid) || empty($entitytypedid)){
                throw new \Exception("Please specify a type for the entity!", 1);
            }

            $entityType = $this->_types->getById($entitytypedid);

            if(!$entityType || $entityType->Status != 1){
                throw new \Exception("Please specify a valid entity type!", 1);
            }
            unset($entityType); 
            $fields = $this->_model->getAllByTypeId($entitytypedid);
            // $fields = $fields->toArray();

            // $entityEntity = new \MicroIceEventManager\V1\Rest\Entities\EntitiesEntity();
            // $entityFields = $entityEntity->getArrayCopy();
            // $idx = 1000;

            // $tpl = new \MicroIceEventManager\V1\Rest\EntityDetailFields\EntityDetailFieldsEntity();
            // $tpl = $tpl->toArray();
            // foreach ($entityFields as $key => $value) 
            // {
            //     $pattern = null;
            //     $type = 'text';
            //     if('DestinationId' == $key){
            //         continue;
            //     }
            //     if('CreationDate' == $key){
            //         continue;
            //     }
            //     if('Identifier' == $key){
            //         $pattern = '[a-zA-Z0-9\-_~@.]+$';
            //     }
            //     if('StartDate' == $key || 'EndDate' == $key){
            //         $type = 'date';
            //     }
            //     $tpl['Id'] = $idx;
            //     $tpl['Field'] = $key;
            //     $tpl['Name'] = $key;
            //     $tpl['Required'] = 1;
            //     $tpl['Category'] = '_predefined_';
            //     $tpl['Type'] = $type;
            //     $tpl['Multiple'] = 0;
            //     $tpl['Pattern'] = $pattern;
            //     $tpl['TypeId'] = $entitytypedid;
            //     $tpl['Status'] = 1;
            //     $idx++;
            //     $fields[] = $tpl;
            // }

            // return new EntityDetailFieldsCollection(new \Zend\Paginator\Adapter\Iterator(new \ArrayIterator($fields)));
            return new EntityDetailFieldsCollection(new \Zend\Paginator\Adapter\Iterator($fields));

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
            
            if(!isset($id) || empty($id)){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            // validate entity type
            $entitytypedid = $this->getEvent()->getRouteParam('entity_type_id');
            if(!isset($entitytypedid) || empty($entitytypedid)){
                throw new \InvalidArgumentException("Please specify a type for the entity!", 1);
            }

            $entityType = $this->_types->getById($entitytypedid);

            if(!$entityType || $entityType->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid entity type!", 1);
            }
            unset($entityType);   
            
            $this->getEventManager()->trigger('entity_detail_fields.update.pre', $this, array());

            $result = false;

            $field = new EntityDetailFieldsEntity();
            $fieldata = $field->getArrayCopy();
            unset($fieldata['Id']);
            unset($fieldata['Timestamp']);
            unset($fieldata['CreationDate']);
            unset($field);

            if(is_object($data)){
                $data = get_object_vars($data);
            }
            
            foreach($data as $prop => $value)
            {
                if(!array_key_exists($prop, $fieldata)){
                    unset($data[$prop]);
                }

                // convert boolean values
                if(in_array($prop, array('Required', 'Multiple'))){
                    $data[$prop] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                }
            }

            if(!empty($data)){
                $this->_model->update($data, array('Id' => $id, 'TypeId' => $entitytypedid));
            }else{
                throw new \Exception("Error Processing Request", 1);
            }
            
            $this->getEventManager()->trigger('entity_detail_fields.update.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            return $this->_model->getByIdAndTypeId($id, $entitytypedid);
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
}
