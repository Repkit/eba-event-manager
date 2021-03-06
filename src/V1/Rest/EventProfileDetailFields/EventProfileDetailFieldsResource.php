<?php
namespace MicroIceEventManager\V1\Rest\EventProfileDetailFields;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EventProfileDetailFieldsResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_profileType;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $ProfileType)
    {
        $this->_model = $Model;
        $this->_profileType = $ProfileType;
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
            // validate event profile type
            $profileType = $this->getEvent()->getRouteParam('event_profile_type_id');
            if( !isset($profileType) || empty($profileType) )
            {
                throw new \InvalidArgumentException("Please specify a type for the event profile!", 1);
            }

            $type = $this->_profileType->getById($profileType);

            if( !$type || $type->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid event profile type!", 1);
            }
            $typeId = $type->Id;
            unset($type);   
            
            $this->getEventManager()->trigger('event_profile_detail_fields.create.pre', $this, array());

            $creationDate = date('Y-m-d H:i:s');

            $fieldcfg = new EventProfileDetailFieldsEntity();
            $fieldcfg->hydrate($data);
            $fieldcfg->TypeId = $typeId;
            $fieldcfg->CreationDate = $creationDate;

            //make sure wont define a field that is predefined already
            $eventProfile = new \MicroIceEventManager\V1\Rest\EventProfiles\EventProfilesEntity();
            $predefinedFields = $eventProfile->toArray();
            if( array_key_exists($fieldcfg->Field, $predefinedFields) )
            {
                throw new \Exception("{$fieldcfg->Field} is a predefined mandatory field!", 1);
            }
            unset($eventProfile);
            unset($predefinedFields);
            
            $required = filter_var($fieldcfg->Required, FILTER_VALIDATE_BOOLEAN);
            $fieldcfg->Required = $required;

            $multiple = filter_var($fieldcfg->Multiple, FILTER_VALIDATE_BOOLEAN);
            $fieldcfg->Multiple = $multiple;

            // persist entity
            $inserted = $this->_model->insert($fieldcfg->getArrayCopy());
            if( $inserted )
            {
                $fieldcfg->Id = $this->_model->getLastInsertValue();
            }
            
            $this->getEventManager()->trigger('event_profile_detail_fields.create.post', $this, array('entity' => $fieldcfg, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            return $fieldcfg;
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
            if( !isset($id) || empty($id) )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }
            
            // validate event profile type
            $typeId = $this->getEvent()->getRouteParam('event_profile_type_id');
            if( !isset($typeId) || empty($typeId) )
            {
                throw new \InvalidArgumentException("Please specify a type for the event profile!", 1);
            }
            
            $this->getEventManager()->trigger('event_profile_detail_fields.delete.pre', $this, array());

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            // get the actual field config
            $fieldcfg = $this->_model->getByIdAndTypeId($id, $typeId);
            $field = $fieldcfg->Field;
            unset($fieldcfg);

            // if there is no field something is WRONG
            if(!isset($field) || empty($field)){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            // delete the actual field config
            $result = $this->_model->delete(array('TypeId' => $typeId, 'Id' => $id));
            if(!$result){
                throw new \Exception("Error Processing Request", 1);
            }
            
            $this->getEventManager()->trigger('event_profile_detail_fields.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );
            $connection->commit();
            return (bool)$result;
        }
        catch(\InvalidArgumentException $e)
        {
            if( !empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface )
            {
                $connection->rollback();
            }
            return new ApiProblem(400, $e->getMessage());
        }
        catch(\Exception $e)
        {
            if( !empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface )
            {
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
            if( !isset($id) || empty($id) )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            // validate event profile type
            $profileType = $this->getEvent()->getRouteParam('event_profile_type_id');
            if( !isset($profileType) || empty($profileType) )
            {
                throw new \InvalidArgumentException("Please specify a type for the event profile!", 1);
            }

            $type = $this->_profileType->getById($profileType);

            if( !$type || $type->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid event profile type!", 1);
            }
            $typeId = $type->Id;
            unset($type); 

            $fieldcfg = $this->_model->getByIdAndTypeId($id, $typeId);

            return $fieldcfg;
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
            // validate event profile type
            $profileType = $this->getEvent()->getRouteParam('event_profile_type_id');
            if( !isset($profileType) || empty($profileType) )
            {
                throw new \InvalidArgumentException("Please specify a type for the event profile!", 1);
            }

            $type = $this->_profileType->getById($profileType);

            if( !$type || $type->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid event profile type!", 1);
            }
            $typeId = $type->Id;
            unset($type);  

            $fields = $this->_model->fetchAll(array('where'=>array('TypeId' => $typeId, 'Status' => array(0,1))));

            return new EventProfileDetailFieldsCollection(new \Zend\Paginator\Adapter\Iterator($fields));
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
        return $this->update($id ,$data);
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
            if( !isset($id) || empty($id) )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            // validate event profile type
            $typeId = $this->getEvent()->getRouteParam('event_profile_type_id');
            if( !isset($typeId) || empty($typeId) )
            {
                throw new \InvalidArgumentException("Please specify a type for the event profile!", 1);
            }

            $type = $this->_profileType->getById($typeId);

            if( !$type || $type->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid event profile type!", 1);
            }
            unset($type);  
            
            $this->getEventManager()->trigger('event_profile_detail_fields.update.pre', $this, array());

            $field = new EventProfileDetailFieldsEntity();
            $fieldata = $field->getArrayCopy();
            unset($fieldata['Id']);
            unset($fieldata['Timestamp']);
            unset($fieldata['CreationDate']);
            unset($field);

            if( is_object($data) )
            {
                $data = get_object_vars($data);
            }
            
            foreach($data as $prop => $value)
            {
                if( !array_key_exists($prop, $fieldata) )
                {
                    unset($data[$prop]);
                }

                // convert boolean values
                if( in_array($prop, array('Required', 'Multiple')) )
                {
                    $data[$prop] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                }
            }

            if( !empty($data) )
            {
                $this->_model->update($data, array('TypeId' => $typeId, 'Id' => $id));
            }
            else
            {
                throw new \Exception("Error Processing Request", 1);
            }
            
            $this->getEventManager()->trigger('event_profile_detail_fields.update.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            return $this->fetch($id);
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
    