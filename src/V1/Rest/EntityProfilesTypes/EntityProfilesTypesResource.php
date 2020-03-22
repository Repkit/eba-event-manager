<?php
namespace MicroIceEventManager\V1\Rest\EntityProfilesTypes;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EntityProfilesTypesResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_profile;
    private $_type;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $Profile, TableGatewayInterface $Type)
    {
        $this->_model = $Model;
        $this->_profile = $Profile;
        $this->_type = $Type;
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
            // validate entity profile
            $profileId = $this->getEvent()->getRouteParam('entity_profiles_id');
            if( !isset($profileId) || empty($profileId) )
            {
                throw new \InvalidArgumentException("Please specify an entity profile!", 1);
            }

            $profile = $this->_profile->getById($profileId);
            if( !$profile || $profile->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid entity profile!", 1);
            }

            // validate entity profile type
            /*$typeId = $this->getEvent()->getRouteParam('entity_profile_types_id');
            if( !isset($typeId) || empty($typeId) )
            {
                throw new \InvalidArgumentException("Please specify an entity profile type!", 1);
            }

            $profileType = $this->_type->getById($typeId);
            if( !$profileType || $profileType->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid entity profile type!", 1);
            }*/

            $rdata = (array)$data;

            $this->getEventManager()->trigger('entity_profiles_types.create.pre', $this, array());

            // create entity
            $entityClass = $this->_model->getEntityClass();
            $entity = new $entityClass();
            $entity->exchangeArray($rdata);
            $entity->ProfileId = $profileId;
            //$entity->TypeId = $typeId;

            // persist entity
            $inserted = $this->_model->insert($entity->getArrayCopy());
            if( !$inserted )
            {
                throw new \Exception("Could not associate entity profiles types", 1);
            }
            $entity->Id = $this->_model->getLastInsertValue();

            $this->getEventManager()->trigger('entity_profiles_types.create.post', $this, array());

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

            $profileId = $this->getEvent()->getRouteParam('entity_profiles_id');
            if( !isset($profileId) || empty($profileId) )
            {
                throw new \InvalidArgumentException("Please specify an entity profile!", 1);
            }

            $this->getEventManager()->trigger('entity_profiles_types.delete.pre', $this, array());

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            $result = $this->_model->delete(array('ProfileId' => $profileId, 'TypeId' => $id));
            $result = (bool)$result;

            $this->getEventManager()->trigger('entity_profiles_types.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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

            $profileId = $this->getEvent()->getRouteParam('entity_profiles_id');
            if( !isset($profileId) || empty($profileId) )
            {
                throw new \InvalidArgumentException("Please specify an entity profile!", 1);
            }

            if( $profileId == '*' )
            {
                $entityProfilesTypes = $this->_model->getAllProfilesByTypeId($id);
                return new EntityProfilesTypesCollection(new \Zend\Paginator\Adapter\Iterator($entityProfilesTypes));
            }
            else
            {
                if( $id == '*' )
                {
                    return $this->fetchAll([]);
                }

                $entityProfileType = $this->_model->getByProfileIdAndTypeId($profileId, $id);

                if( !$entityProfileType || $entityProfileType->Status == 99 )
                {
                    throw new \InvalidArgumentException("Error Processing Request", 1);
                }

                return ($entityProfileType->Id) ? $entityProfileType : false;
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
            $profileId = $this->getEvent()->getRouteParam('entity_profiles_id');
            if( !isset($profileId) || empty($profileId) || $profileId == '*' )
            {
                throw new \InvalidArgumentException("Please specify an entity profile!", 1);
            }

            $collection = $this->_model->getAllTypesByProfileId($profileId);

            return new EntityProfilesTypesCollection(new \Zend\Paginator\Adapter\Iterator($collection));
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
