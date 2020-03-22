<?php
namespace MicroIceEventManager\V1\Rest\EventsTypes;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EventsTypesResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_eventsModel;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $EventsModel)
    {
        $this->_model = $Model;
        $this->_eventsModel = $EventsModel;
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
            $eventId = $this->getEvent()->getRouteParam('event_id');
            if( !isset($eventId) || empty($eventId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            $event = $this->_eventsModel->getById($eventId);
            $typeEventId = $event->Id;
            if( !isset($typeEventId) || empty($typeEventId) ){
                throw new \InvalidArgumentException("No event with id = $typeEventId", 1);
            }
            
            $this->getEventManager()->trigger('events_types.create.pre', $this, array());

            // create entity
            $entityClass = $this->_model->getEntityClass();
            $entity = new $entityClass();
            $entity->exchangeArray((array)$data);
            $entity->EventId = $eventId;
            
            // persist entity
            $inserted = $this->_model->insert($entity->getArrayCopy());

            if($inserted){
                $entity->Id = $this->_model->getLastInsertValue();
            }
            
            $this->getEventManager()->trigger('events_types.create.post', $this, array('entity' => $entity, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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
                throw new \Exception("Error Processing Request", 1);
            }

            $eventId = $this->getEvent()->getRouteParam('event_id');
            if( !isset($eventId) || empty($eventId) ){
                throw new \Exception("Error Processing Request",1);
            }
            
            $this->getEventManager()->trigger('events_types.delete.pre', $this, array());

            $this->_model->delete(array('TypeId' => $id, 'EventId' => $eventId));
            
            $this->getEventManager()->trigger('events_types.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            return true;

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
        // /event-manager/events-types/:event_id/types[/:type_id]
        // /cost-center/:cost_center_id/users[/:user_id]
        try
        {

            if(!isset($id) || empty($id)){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $eventId = $this->getEvent()->getRouteParam('event_id');
            if( !isset($eventId) || empty($eventId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }
            $filter = $this->getEvent()->getQueryParam('filter');
            $filter = empty($filter) ? [] : $filter;
            if( $eventId == '*' )
            {
                // get list of events for a specific type
                $filter['where'] = array('events.Status' => 1);
                $eventTypes = $this->_model->getAllEventsByTypeId($id, $filter);
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

                return new EventsTypesCollection(new \Zend\Paginator\Adapter\Iterator($eventTypes));
            }
            else
            {
                if('*' == $id){
                    return $this->fetchAll([]);
                }
                $eventTypes = $this->_model->getByTypeIdAndEventId($id, $eventId);

                if(!$eventTypes || $eventTypes->Status == 99){
                    throw new \InvalidArgumentException("Error Processing Request", 1);
                }

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
            $eventId = $this->getEvent()->getRouteParam('event_id');
            if( !isset($eventId) || empty($eventId) ){
                throw new \Exception("Error Processing Request",1);
            }

            if( $eventId == '*' )
            {
                $eventsTypes = $this->_model->fetchAll(array('where'=>array('Status' => array(0,1))));

                $events = array();
                foreach ($eventsTypes as $eventType) 
                {
                    $eventType = $eventType->getArrayCopy();
                    $eventId = $eventType['EventId'];
                    unset($eventType['EventId']);
                    if( !isset($events[$eventId]) ){
                        $events[$eventId] = array();
                    }
                    $events[$eventId][] =  $eventType;
                }

                $result = array('events' => $events);

                $eventsTypes = new \ArrayIterator($result);
            }
            else
            {
                $event = $this->_eventsModel->getById($eventId);
                $typeEventId = $event->Id;
                if( !isset($typeEventId) || empty($typeEventId) ){
                    throw new \Exception("No event with id = $eventId", 1);
                }

                 // exclude 99
                // $eventsTypes = $this->_model->fetchAll(array('where'=>array('EventId'=> $eventId, 'Status' => array(0,1))));
                $filter = $params['filter'] ? $params['filter'] : [];
                $eventsTypes = $this->_model->getAllTypesByEventId($eventId, $filter);
                // $eventsTypes = $this->_model->getAllExtended($joins, $where);
                // $eventsTypes = new \ArrayIterator(array());
            }
           
            return new EventsTypesCollection(new \Zend\Paginator\Adapter\Iterator($eventsTypes));

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
