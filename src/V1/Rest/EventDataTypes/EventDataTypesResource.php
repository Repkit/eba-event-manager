<?php
namespace MicroIceEventManager\V1\Rest\EventDataTypes;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EventDataTypesResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_eventData;
    private $_eventDataFields;
    private $_eventDataFieldsConfig;
    private $_eventsData;
    private $_eventDataPreferences;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $EventData, TableGatewayInterface $EventDataFields, TableGatewayInterface $EventDataFieldsConfig, TableGatewayInterface $EventsData, $EventDataPreferences)
    {
        $this->_model = $Model;
        $this->_eventData = $EventData;
        $this->_eventDataFields = $EventDataFields;
        $this->_eventDataFieldsConfig = $EventDataFieldsConfig;
        $this->_eventsData = $EventsData;
        $this->_eventDataPreferences = $EventDataPreferences;
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
            $this->getEventManager()->trigger('event_data_types.create.pre', $this, array());

            $creationDate = date('Y-m-d H:i:s');
            // create entity
            $entityClass = $this->_model->getEntityClass();
            $entity = new $entityClass();
            $entity->exchangeArray((array)$data);
            $entity->CreationDate = $creationDate;
            
            // persist entity
            $inserted = $this->_model->insert($entity->getArrayCopy());

            if($inserted){
                $entity->Id = $this->_model->getLastInsertValue();
            }
            
            $this->getEventManager()->trigger('event_data_types.create.post', $this, array('entity' => $entity, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            return $entity;
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

            /*$dataType = $this->_model->getById($id);

            if(!$dataType || $dataType->Status != 1){
                throw new \InvalidArgumentException("Please specify an active event data type!", 1);
            }
            unset($dataType);*/
            
            $this->getEventManager()->trigger('event_data_types.delete.pre', $this, array());

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            // deleteting data type
            $result = $this->_model->delete(array('Id' => $id));
            if(!$result){
                throw new \Exception("Error Processing Request", 1);
            }

            // delete all config for this data fields (event_data_field_config)
            $this->_eventDataFieldsConfig->delete(array('TypeId' => $id));

            // get all event data for this type
            $eventData = $this->_eventData->fetchAll(array('where'=>array('TypeId' => $id)));
            foreach($eventData as $udata)
            {
                $dataId = $udata['Id'];

                // delete all event data fields
                $this->_eventDataFields->delete(array('DataId' => $dataId));

                // delete all event data preferences
                /*$this->_eventDataPreferences->delete(array('DataId' => $dataId));*/

                // delete all assigment of events on this data
                $this->_eventsData->delete(array('DataId' => $dataId));
            }

            // delete all event data of this type (event_data)
            $this->_eventData->delete(array('TypeId' => $id));

            $this->getEventManager()->trigger('event_data_types.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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

            $dataType = $this->_model->getById($id);

            if(!$dataType || $dataType->Status == 99){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            return ($dataType->Id) ? $dataType : false;
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
            $dataTypes = $this->_model->fetchAll(array('where'=>array('Status' => array(0,1))));

            return new EventDataTypesCollection(new \Zend\Paginator\Adapter\Iterator($dataTypes));
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
            if(!isset($id) || empty($id)){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }
            
            $this->getEventManager()->trigger('event_data_types.update.pre', $this, array());

            $entityClass = $this->_model->getEntityClass();
            $entity = new $entityClass();
            $columns = $entity->getArrayCopy();
            unset($columns['Id']);
            unset($columns['Timestamp']);
            unset($columns['CreationDate']);
            unset($entity);
            unset($entityClass);

            if(is_object($data)){
                $data = get_object_vars($data);
            }
            
            foreach($data as $field => $value)
            {
                if(!array_key_exists($field, $columns)){
                    unset($data[$field]);
                }
            }

            if(!empty($data)){
                $this->_model->update($data, array('Id' => $id));
            }else{
                throw new \Exception("Error Processing Request", 1);
            }
            
            $this->getEventManager()->trigger('event_data_types.update.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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
