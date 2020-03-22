<?php
namespace MicroIceEventManager\V1\Rest\EventTypesDataTypes;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EventTypesDataTypesResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;

    public function __construct(TableGatewayInterface $Model)
    {
        $this->_model = $Model;
    }

    /**
     * Create a resource
     *
     * @param  mixed $data
     * @return ApiProblem|mixed
     */
    public function create($data)
    {
        // /event-manager/events-types/:event_id/types[/:type_id]
        // /event-manager/event-types-data-types/:event_type_id/data-types[/:data_type_id]
        try
        {
            $eventTypeId = $this->getEvent()->getRouteParam('event_type_id');
            if( !isset($eventTypeId) || empty($eventTypeId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            // $eventType = $this->_eventsModel->getById($eventId);
            // $typeEventId = $event->Id;
            // if( !isset($typeEventId) || empty($typeEventId) ){
            //     throw new \InvalidArgumentException("No event with id = $typeEventId", 1);
            // }
            
            $this->getEventManager()->trigger('event_types_data_types.create.pre', $this, array());

            // create entity
            $entityClass = $this->_model->getEntityClass();
            $entity = new $entityClass();
            $entity->exchangeArray((array)$data);
            $entity->EventTypeId = $eventTypeId;
            
            // persist entity
            $inserted = $this->_model->insert($entity->getArrayCopy());

            if($inserted){
                $entity->Id = $this->_model->getLastInsertValue();
            }
            
            $this->getEventManager()->trigger('event_types_data_types.create.post', $this, array('entity' => $entity, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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
            if(!isset($id) || empty($id)){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $eventTypeId = $this->getEvent()->getRouteParam('event_type_id');
            if( !isset($eventTypeId) || empty($eventTypeId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }
            
            $this->getEventManager()->trigger('event_types_data_types.delete.pre', $this, array());

            $this->_model->delete(array('EventDataTypeId' => $id, 'EventTypeId' => $eventTypeId));
            
            $this->getEventManager()->trigger('event_types_data_types.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            return true;

        }
        catch(\InvalidArgumentException $e)
        {
            return new ApiProblem(400, $e->getMessage());
        }
        catch(\Exception $e)
        {
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

            $eventTypeId = $this->getEvent()->getRouteParam('event_type_id');
            if( !isset($eventTypeId) || empty($eventTypeId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            if( $eventTypeId == '*' )
            {
                // get list of events for a specific type
                $eventTypes = $this->_model->getAllEventTypesByEventDataTypeId($id);
                /*$events = array();
                $events[$id] = array();
                foreach ($eventTypes as $event) 
                {
                    // var_dump($event);exit(__FILE__.'::'.__LINE__);
                    $event = $event->getArrayCopy();
                    unset($event['EventId']);
                    $events[$id][] = $event;
                }
                $result = array('events' => $events);

                $eventTypes = new \ArrayIterator($result);*/

                return new EventTypesDataTypesCollection(new \Zend\Paginator\Adapter\Iterator($eventTypes));
            }
            else
            {
                $eventTypes = $this->_model->getByEventDataTypeIdAndEventTypeId($id, $eventTypeId);

                // if($eventTypes->Status == 99){
                //     return false;
                // }

                return ($eventTypes->Id) ? $eventTypes : false;
            }

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
    public function fetchAll($params = [])
    {
        try
        {
            $eventTypeId = $this->getEvent()->getRouteParam('event_type_id');
            if( !isset($eventTypeId) || empty($eventTypeId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            if( $eventTypeId == '*' )
            {
                $eventsTypes = $this->_model->fetchAll();

                $events = array();
                foreach ($eventsTypes as $eventType) 
                {
                    $eventType = $eventType->getArrayCopy();
                    $eventTypeId = $eventType['EventTypeId'];
                    unset($eventType['EventTypeId']);
                    if( !isset($events[$eventTypeId]) ){
                        $events[$eventTypeId] = array();
                    }
                    $events[$eventTypeId][] =  $eventType;
                }

                $result = array('events' => $events);

                $eventsTypes = new \ArrayIterator($result);
            }
            else
            {
                // $event = $this->_eventsModel->getById($eventTypeId);
                // $typeEventId = $event->EventTypeId;
                // if( !isset($typeEventId) || empty($eventTypeId) ){
                //     throw new \Exception("No event with id = $eventTypeId", 1);
                // }

                 // exclude 99
                // $eventsTypes = $this->_model->fetchAll(array('where'=>array('EventTypeId'=> $eventTypeId)));
                $eventsTypes = $this->_model->getAllEventDataTypesByEventTypeId($eventTypeId);
                // $eventsTypes = $this->_model->getAllExtended($joins, $where);
                // $eventsTypes = new \ArrayIterator(array());
            }
           
            return new EventTypesDataTypesCollection(new \Zend\Paginator\Adapter\Iterator($eventsTypes));

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
        return new ApiProblem(405, 'The PATCH method has not been defined for individual resources');
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
        return new ApiProblem(405, 'The PUT method has not been defined for individual resources');
    }
}
