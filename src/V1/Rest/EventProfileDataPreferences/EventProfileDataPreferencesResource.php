<?php
namespace MicroIceEventManager\V1\Rest\EventProfileDataPreferences;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EventProfileDataPreferencesResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_dataModel;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $DataModel)
    {
        $this->_model = $Model;
        $this->_dataModel = $DataModel;
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
            $profileDataId = $this->getEvent()->getRouteParam('event_profile_data_id');
            if( !isset($profileDataId) || empty($profileDataId) )
            {
                throw new \Exception("Error Processing Request",1);
            }

            $profileData = $this->_dataModel->getById($profileDataId);
            $eventProfileId = $profileData->Id;
            if( !isset($eventProfileId) || empty($eventProfileId) || $profileData->Status == 99 )
            {
                throw new \Exception("No event profile data with id = $profileDataId",1);
            }

            $this->getEventManager()->trigger('event_profile_data_preferences.create.pre', $this, array());

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

            $profileDataPreference = new EventProfileDataPreferencesEntity();
            $profileDataPreference->hydrate($data);
            $profileDataPreference->DataId = $profileDataId;
            $profileDataPreference->CreationDate = $creationDate;
            $profileDataPreference->Status = 1;

            $exists = false;
            if( isset($data['Category']) && !empty($data['Category']) )
            {
                $exists = $this->_model->getByDataIdAndCategory($profileDataId, $data['Category']);
            }
            if( $exists )
            {
                $this->_model->update($profileDataPreference->getArrayCopy(),array('Id'=>$exists->Id));
            }
            else
            {
                $inserted = $this->_model->insert($profileDataPreference->getArrayCopy());
                if( $inserted )
                {
                    $profileDataPreference->Id = $this->_model->getLastInsertValue();
                }
            }

            $this->getEventManager()->trigger('event_profile_data_preferences.create.post', $this, array('entity' => $profileDataPreference, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            return $profileDataPreference->getExtendedData();
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
            $dataId = $this->getEvent()->getRouteParam('event_profile_data_id');
            $eventData = $this->_dataModel->getById($dataId);
            $eventDataId = $eventData->Id;
            if( !isset($eventDataId) || empty($eventDataId) || $eventData->Status == 99 )
            {
                throw new \Exception("No event profile data with id = $dataId", 1);
            }

            $result = $this->_model->delete(array('Id' => $id, 'DataId' => $dataId));
            $result = (bool)$result;
            return $result;
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
            $dataId = $this->getEvent()->getRouteParam('event_profile_data_id');
            $eventData = $this->_dataModel->getById($dataId);
            $eventDataId = $eventData->Id;
            if( !isset($eventDataId) || empty($eventDataId) || $eventData->Status == 99 )
            {
                throw new \Exception("No event data with id = $dataId", 1);
            }

            $entity = $this->_model->getById($id);

            if( !$entity || $entity->Status == 99 || $entity->DataId != $dataId )
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
    public function fetchAll($params = [])
    {
        try
        {
            $dataId = $this->getEvent()->getRouteParam('event_profile_data_id');
            if( $dataId == '*' )
            {
                $collection = $this->_model->fetchAll(array('where' => array('Status' => array(0, 1))));

                $preferences = array();
                foreach ($collection as $code)
                {
                    $code = $code->getExtendedData();
                    $dataId = $code['DataId'];
                    unset($code['DataId']);
                    if( !isset($preferences[$dataId]) )
                    {
                        $preferences[$dataId] = array();
                    }
                    $preferences[$dataId][] = $code;
                }

                $result = array('event_profile_data_preferences' => $preferences);

                // $collection = new \ArrayIterator($result);
                $collection = new \Zend\Paginator\Adapter\ArrayAdapter($result);
            }
            else
            {
                $eventData = $this->_dataModel->getById($dataId);
                $eventDataId = $eventData->Id;
                if( !isset($eventDataId) || empty($eventDataId) || $eventData->Status == 99 )
                {
                    throw new \Exception("No event profile data with id = $dataId", 1);
                }

                // exclude 99
                $collection = $this->_model->fetchAll(array('where' => array('DataId' => $dataId, 'Status' => array(0, 1))));

                foreach ($collection as $code)
                {
                    $extendedCode[] = $code->getExtendedData();
                }
                $result = array('event_profile_data_preferences' => $extendedCode);
                // $collection = new \ArrayIterator($result);
                $collection = new \Zend\Paginator\Adapter\ArrayAdapter($result);
            }

            // return new EventProfileDataPreferencesCollection(new \Zend\Paginator\Adapter\Iterator($collection));
            return new EventProfileDataPreferencesCollection($collection);

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
            $dataId = $this->getEvent()->getRouteParam('event_profile_data_id');
            $eventData = $this->_dataModel->getById($dataId);
            $eventDataId = $eventData->Id;
            if( !isset($eventDataId) || empty($eventDataId) || $eventData->Status == 99 )
            {
                throw new \Exception("No event profile data with id = $dataId", 1);
            }

            $entityClass = $this->_model->getEntityClass();
            $entity = new $entityClass();
            $entitydata = $entity->getArrayCopy();
            unset($entity);

            $rdata = (array)$data;
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
                $affectedRows = $this->_model->update($rdata, array('Id' => $id, 'DataId' => $dataId));
                if( !$affectedRows )
                {
                    throw new \Exception("Error patching entity", 1);
                }
            }

            return $this->_model->getById($id);
        }
        catch(\Exception $e)
        {
            return new ApiProblem(417, $e->getMessage());
        }
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
        try
        {
            $dataId = $this->getEvent()->getRouteParam('event_profile_data_id');
            $eventData = $this->_dataModel->getById($dataId);
            $eventDataId = $eventData->Id;
            if( !isset($eventDataId) || empty($eventDataId) || $eventData->Status == 99 )
            {
                throw new \Exception("No event profile data with id = $dataId", 1);
            }

            $entity = $this->_model->getById($id);
            if( !$entity || $entity->Status == 99 || $entity->DataId != $dataId )
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
            }

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
