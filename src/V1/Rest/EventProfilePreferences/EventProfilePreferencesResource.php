<?php
namespace MicroIceEventManager\V1\Rest\EventProfilePreferences;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EventProfilePreferencesResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_profilesModel;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $ProfilesModel)
    {
        $this->_model = $Model;
        $this->_profilesModel = $ProfilesModel;
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
            $profileId = $this->getEvent()->getRouteParam('event_profiles_id');
            if( !isset($profileId) || empty($profileId) )
            {
                throw new \Exception("Error Processing Request",1);
            }

            $eventProfile = $this->_profilesModel->getById($profileId);
            $eventProfileId = $eventProfile->Id;
            if( !isset($eventProfileId) || empty($eventProfileId) || $eventProfile->Status == 99 )
            {
                throw new \Exception("No event profile with id = $profileId",1);
            }
            
            $this->getEventManager()->trigger('event_profile_preferences.create.pre', $this, array());

            $data = (array)$data;
            if( isset($data['Content']) && !empty($data['Content']) )
            {
                if( isset($data['ContentType']) && !empty($data['ContentType']) )
                {
                    switch ($data['ContentType'])
                    {
                        case 'json':
                            $encodedContent = json_encode($data['Content']);
                            break;
                        case 'serialize':
                            $encodedContent = serialize($data['Content']);
                            break;
                        default:
                            $encodedContent = $data['Content'];
                    }

                    if( empty($encodedContent) )
                    {
                        throw new \Exception("Invalid preference content", 1);
                    }

                    $data['Content'] = $encodedContent;
                }
            }

            $creationDate = date('Y-m-d H:i:s');

            $eventProfilePreference = new EventProfilePreferencesEntity();
            $eventProfilePreference->hydrate($data);
            $eventProfilePreference->Status = 1;
            $eventProfilePreference->ProfileId = $profileId;
            $eventProfilePreference->CreationDate = $creationDate;
            
            $exists = false;
            if( isset($data['Category']) && !empty($data['Category']) )
            {
                $exists = $this->_model->getByProfileIdAndCategory($profileId, $data['Category']);
            }
            if( $exists )
            {
                $this->_model->update($eventProfilePreference->getArrayCopy(),array('Id'=>$exists->Id));
            }
            else
            {
                $inserted = $this->_model->insert($eventProfilePreference->getArrayCopy());
                if( $inserted )
                {
                    $eventProfilePreference->Id = $this->_model->getLastInsertValue();
                }
            }
            
            $this->getEventManager()->trigger('event_profile_preferences.create.post', $this, array('entity' => $eventProfilePreference, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            return $eventProfilePreference->getExtendedData();
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
            $profileId = $this->getEvent()->getRouteParam('event_profiles_id');
            if( !isset($profileId) || empty($profileId) )
            {
                throw new \Exception("Error Processing Request",1);
            }

            $this->getEventManager()->trigger('event_profile_preferences.delete.pre', $this, array());
            
            $result = $this->_model->delete(array('Id' => $id,'ProfileId' => $profileId));

            $this->getEventManager()->trigger('event_profile_preferences.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            return (bool)$result; 
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
            $profileId = $this->getEvent()->getRouteParam('event_profiles_id');
            if( !isset($profileId) || empty($profileId) )
            {
                throw new \Exception("Error Processing Request",1);
            }

            $eventProfile = $this->_profilesModel->getById($profileId);
            $eventProfileId = $eventProfile->Id;
            if( !isset($eventProfileId) || empty($eventProfileId) || $eventProfile->Status == 99 )
            {
                throw new \Exception("No event profile with id = $profileId",1);
            }

            $entity = $this->_model->getById($id);

            if( !$entity || $entity->Status == 99 || $entity->ProfileId != $profileId )
            {
                throw new \Exception("Error Processing Request", 1);
            }

            return ($entity->Id) ? $entity->getExtendedData() : false;
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
            $profileId = $this->getEvent()->getRouteParam('event_profiles_id');
            if( $profileId == '*' )
            {
                $collection = $this->_model->fetchAll(array('where' => array('Status' => array(0, 1))));

                $preferences = array();
                foreach ($collection as $record)
                {
                    $record = $record->getExtendedData();
                    $profileId = $record['ProfileId'];
                    unset($record['ProfileId']);
                    if( !isset($preferences[$profileId]) )
                    {
                        $preferences[$profileId] = array();
                    }
                    $preferences[$profileId][] = $record;
                }

                $result = array('event_profile_preferences' => $preferences);

                // $collection = new \ArrayIterator($result);
                $collection = new \Zend\Paginator\Adapter\ArrayAdapter($result);
            }
            else
            {
                $eventProfile = $this->_profilesModel->getById($profileId);
                $eventProfileId = $eventProfile->Id;
                if( !isset($eventProfileId) || empty($eventProfileId) || $eventProfile->Status == 99 )
                {
                    throw new \Exception("No event profile with id = $profileId", 1);
                }

                // exclude 99
                $collection = $this->_model->fetchAll(array('where' => array('ProfileId' => $profileId, 'Status' => array(0, 1))));

                foreach ($collection as $record)
                {
                    $extendedRecord[] = $record->getExtendedData();
                }
                $result = array('event_profile_preferences' => $extendedRecord);
                // $collection = new \ArrayIterator($result);
                $collection = new \Zend\Paginator\Adapter\ArrayAdapter($result);
            }

            // return new EventProfilePreferencesCollection(new \Zend\Paginator\Adapter\Iterator($collection));
            return new EventProfilePreferencesCollection($collection);
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
        try
        {
            if(!isset($id) || empty($id)){
                throw new \Exception("Error Processing Request", 1);
            }

            $profileId = $this->getEvent()->getRouteParam('event_profiles_id');
            $eventProfile = $this->_profilesModel->getById($profileId);
            $eventProfileId = $eventProfile->Id;
            if( !isset($eventProfileId) || empty($eventProfileId) || $eventProfile->Status == 99 )
            {
                throw new \Exception("No event profile with id = $profileId", 1);
            }
            
            $this->getEventManager()->trigger('event_profile_preferences.patch.pre', $this, array());

            $entityClass = $this->_model->getEntityClass();
            $entity = new $entityClass();
            $entitydata = $entity->getArrayCopy();
            unset($entity);

            $rdata = (array)$data;
            if( isset($rdata['Id']) )
            {
                unset($rdata['Id']);
            }
            if( isset($rdata['Timestamp']) )
            {
                unset($rdata['Timestamp']);
            }
            if( isset($rdata['CreationDate']) )
            {
                unset($rdata['CreationDate']);
            }

            if( isset($rdata['Content']) && !empty($rdata['Content']) )
            {
                if( !isset($rdata['ContentType']) || empty($rdata['ContentType']) )
                {
                    throw new \InvalidArgumentException("Invalid Content Type", 1);
                }

                switch ($rdata['ContentType'])
                {
                    case 'json':
                        $encodedContent = json_encode($rdata['Content']);
                        break;
                    case 'serialize':
                        $encodedContent = serialize($rdata['Content']);
                        break;
                    default:
                        $encodedContent = $rdata['Content'];
                }

                if( !empty($encodedContent) )
                {
                    $rdata['Content'] = $encodedContent;
                }
            }

            foreach ($rdata as $field => $value)
            {
                if( !array_key_exists($field, $entitydata) )
                {
                    unset($rdata[$field]);
                }
            }

            if( !empty($rdata) )
            {
                $affectedRows = $this->_model->update($rdata, array('Id' => $id, 'ProfileId' => $profileId));
                if( !$affectedRows )
                {
                    throw new \Exception("Error patching entity", 1);
                }
            }
            
            $this->getEventManager()->trigger('event_profile_preferences.patch.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            return $this->_model->getById($id);
        }
        catch(\Exception $e)
        {
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
        try
        {
            $profileId = $this->getEvent()->getRouteParam('event_profiles_id');
            $eventProfile = $this->_profilesModel->getById($profileId);
            $eventProfileId = $eventProfile->Id;
            if( !isset($eventProfileId) || empty($eventProfileId) || $eventProfile->Status == 99 )
            {
                throw new \Exception("No event profile with id = $profileId", 1);
            }

            $entity = $this->_model->getById($id);
            if( !$entity || $entity->Status == 99 || $entity->ProfileId != $profileId )
            {
                throw new \InvalidArgumentException("Invalid entity", 1);
            }

            $rdata = (array)$data;
            $edata = $entity->getArrayCopy();

            if( isset($rdata['Content']) && !empty($rdata['Content']) )
            {
                if( !isset($rdata['ContentType']) || empty($rdata['ContentType']) )
                {
                    throw new \InvalidArgumentException("Invalid Content Type", 1);
                }

                switch ($rdata['ContentType'])
                {
                    case 'json':
                        $encodedContent = json_encode($rdata['Content']);
                        break;
                    case 'serialize':
                        $encodedContent = serialize($rdata['Content']);
                        break;
                    default:
                        $encodedContent = $rdata['Content'];
                }

                if( !empty($encodedContent) )
                {
                    $entity->Content = $encodedContent;
                    $entity->ContentType = $rdata['ContentType'];
                }
            }

            foreach ($edata as $key => $value)
            {
                if( in_array($key, array('Id', 'Content', 'ContentType')) )
                {
                    continue;
                }
                if( !isset($rdata[$key]) && !is_null($value) )
                {
                    throw new \InvalidArgumentException("Incomplete entity", 1);
                }
                $entity->$key = $rdata[$key];
                unset($rdata[$key]);
            }

            if( $edata != $entity->getArrayCopy() )
            {
                $affectedRows = $this->_model->update($entity->getArrayCopy(), array('Id' => $id));
                if( !$affectedRows )
                {
                    throw new \Exception("Error updating entity", 1);
                }
            } else { exit('no difference');}

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
}
