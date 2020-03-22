<?php
namespace MicroIceEventManager\V1\Rest\EventsEntities;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EventsEntitiesResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_eventsModel;
    private $_entitiesModel;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $EventsModel, TableGatewayInterface $EntitiesModel)
    {
        $this->_model = $Model;
        $this->_eventsModel = $EventsModel;
        $this->_entitiesModel = $EntitiesModel;
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
            $dbeventId = $event->Id;
            if( !isset($dbeventId) || empty($dbeventId) ){
                throw new \InvalidArgumentException("No event with id = $eventId", 1);
            }
            
            $this->getEventManager()->trigger('events_entities.create.pre', $this, array());

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
            
            $this->getEventManager()->trigger('events_entities.create.post', $this, array('entity' => $entity, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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

            $eventId = $this->getEvent()->getRouteParam('event_id');
            if( !isset($eventId) || empty($eventId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }
            
            $this->getEventManager()->trigger('events_entities.delete.pre', $this, array());

            $this->_model->delete(array('EntityId' => $id, 'EventId' => $eventId));
            
            $this->getEventManager()->trigger('events_entities.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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

            $eventId = $this->getEvent()->getRouteParam('event_id');
            if( !isset($eventId) || empty($eventId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            if( $eventId == '*' )
            {
                // get list of events for a specific entity
                $events = $this->_model->getAllEventsByEntityId($id, array('where'=>array('events.Status' => 1)));
                /*$events = array();
                $events[$id] = array();
                foreach ($events as $event) 
                {
                    // var_dump($event);exit(__FILE__.'::'.__LINE__);
                    $event = $event->getArrayCopy();
                    unset($event['EventId']);
                    $events[$id][] = $event;
                }
                $result = array('events' => $events);

                $events = new \ArrayIterator($result);*/

                return new EventsEntitiesCollection(new \Zend\Paginator\Adapter\Iterator($events));
            }
            else
            {
                $events = $this->_model->getByEventIdAndEntityId($eventId, $id);

                if(!$events || $events->Status == 99){
                    throw new \InvalidArgumentException("Error Processing Request", 1);
                }

                return ($events->Id) ? $events : false;
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
            $eventId = $this->getEvent()->getRouteParam('event_id');
            if( !isset($eventId) || empty($eventId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            $where = array();
            if(!empty($params['filter'])){
                $where = $params['filter'];
            }
            foreach ($where as $key => $value) {
                if('EntityTypeId' == $value['name']){
                    $where[$key]['name'] = 'entities_types.TypeId';
                    break;
                }
            }

            $storage = array();
            /*if(!empty($this->_settings['event_entities'])){
                if(!empty($this->_settings['event_entities']['storage'])){
                    if(!empty($this->_settings['event_entities']['storage']['joins'])){
                        $storage = $this->_settings['event_entities']['storage'];
                    }
                }
            }*/


            if( $eventId == '*' )
            {
                
                $eventsEntities = $this->_model->getAllExtended($storage, $where);
                // $eventsEntities = $this->_model->fetchAll(array('where'=>));

                $events = array();
                foreach ($eventsEntities as $eventEntity) 
                {
                    $eventEntity = $eventEntity->getArrayCopy();
                    $eventId = $eventEntity['EventId'];
                    unset($eventEntity['EventId']);
                    if( !isset($events[$eventId]) ){
                        $events[$eventId] = array();
                    }
                    $events[$eventId][] =  $eventEntity;
                }

                $result = array('events' => $events);

                $collection = new \ArrayIterator($result);
            }
            else
            {
                $event = $this->_eventsModel->getById($eventId);
                $dbEventId = $event->Id;
                if( !isset($dbEventId) || empty($dbEventId) ){
                    throw new \InvalidArgumentException("No event with id = $eventId", 1);
                }

                 // exclude 99
                // $collection = $this->_model->getAllEntitiesByEventId($eventId);
                $collection = $this->_model->getAllExtended($storage, $where, $eventId);
            }

            /*foreach ($collection as $key => $value) {
                var_dump($value);exit(__FILE__.'::'.__LINE__);
            }*/
           
            return new EventsEntitiesCollection(new \Zend\Paginator\Adapter\Iterator($collection));

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
