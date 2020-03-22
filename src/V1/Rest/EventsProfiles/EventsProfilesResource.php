<?php
namespace MicroIceEventManager\V1\Rest\EventsProfiles;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EventsProfilesResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_profile;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $Profile)
    {
        $this->_model = $Model;
        $this->_profile = $Profile;
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
            if( !isset($eventId) || empty($eventId) )
            {
                throw new \Exception("Please specify an event!", 1);
            }

            // validate event profile type
            /*$profileId = $this->getEvent()->getRouteParam('event_profile_id');
            if( !isset($profileId) || empty($profileId) )
            {
                throw new \InvalidArgumentException("Please specify an event profile!", 1);
            }

            $profile = $this->_profile->getById($profileId);
            if( !$profile || $profile->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid event profile!", 1);
            }*/

            $rdata = (array)$data;

            $this->getEventManager()->trigger('events_profiles.create.pre', $this, array());

            // create entity
            $entityClass = $this->_model->getEntityClass();
            $entity = new $entityClass();
            $entity->exchangeArray($rdata);
            $entity->EventId = $eventId;
            //$entity->ProfileId = $profileId;
            
            // persist entity
            $inserted = $this->_model->insert($entity->getArrayCopy());
            if( !$inserted )
            {
                throw new \Exception("Could not associate events profiles", 1);
            }
            $entity->Id = $this->_model->getLastInsertValue();

            $this->getEventManager()->trigger('events_profiles.create.post', $this, array());

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
            if( !isset($id) || empty($id) )
            {
                throw new \Exception("Error Processing Request", 1);
            }

            $eventId = $this->getEvent()->getRouteParam('event_id');
            if( !isset($eventId) || empty($eventId) )
            {
                throw new \Exception("Please specify an event!", 1);
            }

            $this->getEventManager()->trigger('events_profiles.delete.pre', $this, array());

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            $result = $this->_model->delete(array('ProfileId' => $id, 'EventId' => $eventId));
            $result = (bool)$result;
            
            $this->getEventManager()->trigger('events_profiles.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            return $result;
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

            $eventId = $this->getEvent()->getRouteParam('event_id');
            if( !isset($eventId) || empty($eventId) )
            {
                throw new \InvalidArgumentException("Please specify an event!", 1);
            }

            if( $eventId == '*' )
            {
                $eventsProfiles = $this->_model->getAllEventsByProfileId($id);
                return new EventsProfilesCollection(new \Zend\Paginator\Adapter\Iterator($eventsProfiles));
            }
            else
            {
                if( $id == '*' )
                {
                    return $this->fetchAll([]);
                }

                $eventProfile = $this->_model->getByEventIdAndProfileId($eventId, $id);

                if( !$eventProfile || $eventProfile->Status == 99 )
                {
                    throw new \InvalidArgumentException("Error Processing Request", 1);
                }

                return ($eventProfile->Id) ? $eventProfile : false;
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
            if( !isset($eventId) || empty($eventId) || $eventId == '*' )
            {
                throw new \Exception("Please specify an event!", 1);
            }

            $collection = $this->_model->getAllProfilesByEventId($eventId);

            return new EventsProfilesCollection(new \Zend\Paginator\Adapter\Iterator($collection));
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
