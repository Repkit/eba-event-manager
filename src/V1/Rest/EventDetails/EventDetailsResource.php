<?php
namespace MicroIceEventManager\V1\Rest\EventDetails;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use MicroIceEventManager\V1\Rest\Events\EventsEntity;
use MicroIceEventManager\V1\Rest\EventTypes\EventTypesEntity;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EventDetailsResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_modelFields;
    private $_types;
    private $_events;
    private $_eventsTypes;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface$ModelFields, TableGatewayInterface $Types, TableGatewayInterface $Events, TableGatewayInterface $EventsTypes)
    {
        $this->_model = $Model;
        $this->_modelFields = $ModelFields;
        $this->_types = $Types;
        $this->_events = $Events;
        $this->_eventsTypes = $EventsTypes;
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

            // validate event type
            $eventtypedid = $this->getEvent()->getRouteParam('event_type_id');
            if(!isset($eventtypedid) || empty($eventtypedid)){
                throw new \InvalidArgumentException("Please specify a type for the event!", 1);
            }

            $eTranslationId = $this->getEvent()->getRouteParam('event_translation_id');
            if(!isset($eTranslationId) || empty($eTranslationId)){
                throw new \InvalidArgumentException("Please specify an event!", 1);
            }

            $eventType = $this->_types->validateTypeAndEventTranslationId($eventtypedid,  $eTranslationId);

            if( !$eventType || $eventType->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid event type or translation!", 1);
            }
            $eventAssigned = $eventType->Assigned;
            $eventid = $eventType->EventId;
            unset($eventType);

            /*$event = $this->_events->getById($eTranslationId);

            if( !$event || $event->Status != 1){
                throw new \InvalidArgumentException("Please specify an active event!", 1);
            }*/
            
            $this->getEventManager()->trigger('event_details.create.pre', $this, array());

            if(is_object($data)){
                $data = get_object_vars($data);
            }

            $fields = $this->_modelFields->getAllByTypeId($eventtypedid);
            
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
                $udetails = new EventDetailsEntity();
                $udetails->EventId = $eTranslationId;
                $udetails->Field = $fname;
                $udetails->Value = $fvalue;
                $udetails->Category = null;
                $udetails->TypeId = $eventtypedid;
                $udetails->Status = 1;

                

                // prepare for persist
                $result = $this->_model->createBulkInsert($udetails);
                // var_dump($result);exit(__FILE__.'::'.__LINE__);

            }

            // start trasaction as we need to delete also other information like price categories and other stuff
            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            //clear old data for this event
            $this->_model->delete(array('EventId' => $eTranslationId, 'TypeId' => $eventtypedid));

            // persist
            $result = $this->_model->runBulkInsert(false);
            // var_dump($result);exit(__FILE__.'::'.__LINE__);

            /*if($persist){
                // set event type if anything is ok
                $this->_model->seteventtype();
            }*/

            if(!$eventAssigned){
                $ptClass = $this->_eventsTypes->getEntityClass();
                $pt = new $ptClass();
                $pt->TypeId = $eventtypedid;
                $pt->EventId = $eventid;
                $pt->Status = 1;

                $inserted = $this->_eventsTypes->insert($pt->getArrayCopy());
                if(!$inserted){
                    throw new \Exception("Could not assign event on type", 1);
                }
            }
            
            $this->getEventManager()->trigger('event_details.create.post', $this, array('entity' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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
        // /event-manager/event-details/:event_id[/:event_type_id]

        try
        {
            $eTranslationId = $this->getEvent()->getRouteParam('event_translation_id');
            if(!isset($eTranslationId) || empty($eTranslationId)){
                throw new \InvalidArgumentException("Please specify an event!", 1);
            }

            // $eventtypedid = $this->getEvent()->getRouteParam('event_type_id');
            if(!isset($id) || empty($id)){
                throw new \InvalidArgumentException("Please specify a type for the event!", 1);
                // $eventDetails = $this->_model->getAllByEventId($id);
                // return new EventDetailsCollection(new \Zend\Paginator\Adapter\Iterator($eventDetails));
            }

            $eventType = $this->_types->getById($id);

            if( !$eventType || $eventType->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid event type!", 1);
            }
            unset($eventType);

            $eventDetails = $this->_model->getAllByEventIdAndTypeId($eTranslationId, $id);

            return new EventDetailsCollection(new \Zend\Paginator\Adapter\Iterator($eventDetails));
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
            $eTranslationId = $this->getEvent()->getRouteParam('event_translation_id');
            if(!isset($eTranslationId) || empty($eTranslationId)){
                throw new \InvalidArgumentException("Please specify an event!", 1);
            }
            
            $eventFields = $this->_model->getAllByEventId($eTranslationId);
            if(!$eventFields){
                $eventFields = array();
            }

            $types = array();
            foreach ($eventFields as $key => $field) {
                // var_dump($field);exit(__FILE__.'::'.__LINE__);
                  $field = $field->getArrayCopy();
                  $typeId = $field['TypeId'];
                  unset($field['TypeId']);
                  unset($field['EventId']);
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
            // return new EventDetailsCollection(new \Zend\Paginator\Adapter\Iterator($data));
            return new EventDetailsCollection($data);
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
            // validate event type
            $eventtypedid = $this->getEvent()->getRouteParam('event_type_id');
            if(!isset($eventtypedid) || empty($eventtypedid)){
                throw new \InvalidArgumentException("Please specify a type for the event!", 1);
            }

            $eTranslationId = $this->getEvent()->getRouteParam('event_translation_id');
            if(!isset($eTranslationId) || empty($eTranslationId)){
                throw new \InvalidArgumentException("Please specify an event!", 1);
            }

            $eventType = $this->_types->getById($eventtypedid);

            if(!$eventType || $eventType->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid event type!", 1);
            }
            unset($eventType);

            /* $event = $this->_events->getById($eTranslationId);

             if(!$event || $event->Status != 1){
                 throw new \Exception("Please specify an active event!", 1);
             }*/
            $fields = $this->_modelFields->getAllByTypeId($eventtypedid);
            if( !$fields->count() ){
                throw new \InvalidArgumentException('Invalid type fields');
            }

            $this->getEventManager()->trigger('event_details.patch.pre', $this, array());

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
                $udetails = new EventDetailsEntity();
                $udetails->EventId = $eTranslationId;
                $udetails->Field = $fname;
                $udetails->Value = $fvalue;
                $udetails->Category = null;
                $udetails->TypeId = $eventtypedid;
                $udetails->Status = 1;

                // prepare for persist
                $this->_model->createBulkInsert($udetails);
            }

            if( empty($updatedFields) ){
                throw new \InvalidArgumentException('Invalid update fields');
            }

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            //clear old data for this event
            $this->_model->delete(array('EventId' => $eTranslationId, 'TypeId' => $eventtypedid,'Field' => $updatedFields));

            // persist
            $result = $this->_model->runBulkInsert(false);

            $this->getEventManager()->trigger('event_details.patch.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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
