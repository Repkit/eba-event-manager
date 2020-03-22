<?php
namespace MicroIceEventManager\V1\Rest\EventProfilesData;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EventProfilesDataResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_data;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $Data)
    {
        $this->_model = $Model;
        $this->_data = $Data;
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
            $pTranslationId = $this->getEvent()->getRouteParam('event_profile_translation_id');
            if( !isset($pTranslationId) || empty($pTranslationId) )
            {
                throw new \InvalidArgumentException("Please specify an event profile!", 1);
            }

            $rdata = (array)$data;

            $this->getEventManager()->trigger('event_profiles_data.create.pre', $this, array());

            // create entity
            $entityClass = $this->_model->getEntityClass();
            $entity = new $entityClass();
            $entity->exchangeArray($rdata);
            $entity->ProfileId = $pTranslationId;

            $associationId = $entity->DataId;
            if( !isset($associationId) || empty($associationId) )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }
            $assoc = $this->_data->getById($associationId);
            if( !$assoc || $assoc->Status == 99 )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            // persist entity
            $inserted = $this->_model->insert($entity->getArrayCopy());
            if( !$inserted )
            {
                throw new \Exception("Could not associate event profiles data", 1);
            }
            $entity->Id = $this->_model->getLastInsertValue();

            $this->getEventManager()->trigger('event_profiles_data.create.post', $this, array());

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
            $pTranslationId = $this->getEvent()->getRouteParam('event_profile_translation_id');
            if( !isset($pTranslationId) || empty($pTranslationId) )
            {
                throw new \InvalidArgumentException("Please specify an event profile!", 1);
            }

            $this->getEventManager()->trigger('event_profiles_data.delete.pre', $this, array());

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            $result = false;
            $result = $this->_model->delete(array('Id' => $id));
            $result = (bool)$result;

            $this->getEventManager()->trigger('event_profiles_data.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            return $result;
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
            $pTranslationId = $this->getEvent()->getRouteParam('event_profile_translation_id');
            if( !isset($pTranslationId) || empty($pTranslationId) || $pTranslationId == '*' )
            {
                throw new \InvalidArgumentException("Please specify an event profile!", 1);
            }

            if( $id == '*' )
            {
                return $this->fetchAll();
            }

            $entity = $this->_model->getByProfileIdAndId($pTranslationId, $id);

            if( $entity->Status == 99 )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            return ($entity->Id) ? $entity : false;

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
            $pTranslationId = $this->getEvent()->getRouteParam('event_profile_translation_id');
            if( !isset($pTranslationId) || empty($pTranslationId) || $pTranslationId == '*' )
            {
                throw new \InvalidArgumentException("Please specify an event!", 1);
            }

            $collection = $this->_model->getAllDataByProfileId($pTranslationId);

            return new EventProfilesDataCollection(new \Zend\Paginator\Adapter\Iterator($collection));
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
            if( !isset($id) || empty($id) )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $this->getEventManager()->trigger('event_profiles_data.update.pre', $this, array());

            $entityClass = $this->_model->getEntityClass();
            $entity = new $entityClass();
            $columns = $entity->getArrayCopy();
            unset($columns['Id']);
            unset($entity);
            unset($entityClass);

            if( is_object($data) )
            {
                $data = get_object_vars($data);
            }

            foreach($data as $field => $value)
            {
                if( !array_key_exists($field, $columns) )
                {
                    unset($data[$field]);
                }
            }

            if( array_key_exists('DataId', $data) )
            {
                $assoc = $this->_data->getById($data['DataId']);
                if( !$assoc || $assoc->Status == 99 )
                {
                    throw new \InvalidArgumentException("Error Processing Request", 1);
                }
            }

            if( !empty($data) )
            {
                $this->_model->update($data, array('Id' => $id));
            }
            else
            {
                throw new \Exception("Error Processing Request", 1);
            }

            $this->getEventManager()->trigger('event_profiles_data.update.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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
