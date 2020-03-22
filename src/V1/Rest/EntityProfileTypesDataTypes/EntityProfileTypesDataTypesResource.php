<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileTypesDataTypes;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EntityProfileTypesDataTypesResource extends AbstractResourceListener implements EventManagerAwareInterface
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
        try
        {
            $profileTypeId = $this->getEvent()->getRouteParam('entity_profile_type_id');
            if( !isset($profileTypeId) || empty($profileTypeId) )
            {
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            $this->getEventManager()->trigger('entity_profile_types_data_types.create.pre', $this, array());

            // create entity
            $entityClass = $this->_model->getEntityClass();
            $entity = new $entityClass();
            $entity->exchangeArray((array)$data);
            $entity->EntityProfileTypeId = $profileTypeId;

            // persist entity
            $inserted = $this->_model->insert($entity->getArrayCopy());

            if( !$inserted )
            {
                throw new \Exception("Could not associate entity profiles types data types", 1);
            }

            $entity->Id = $this->_model->getLastInsertValue();

            $this->getEventManager()->trigger('entity_profile_types_data_types.create.post', $this, array('entity' => $entity, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $profileTypeId = $this->getEvent()->getRouteParam('entity_profile_type_id');
            if( !isset($profileTypeId) || empty($profileTypeId) )
            {
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            $this->getEventManager()->trigger('entity_profile_types_data_types.delete.pre', $this, array());

            $this->_model->delete(array('EntityProfileDataTypeId' => $id, 'EntityProfileTypeId' => $profileTypeId));

            $this->getEventManager()->trigger('entity_profile_types_data_types.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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

            $profileTypeId = $this->getEvent()->getRouteParam('entity_profile_type_id');
            if( !isset($profileTypeId) || empty($profileTypeId) )
            {
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            if( $profileTypeId == '*' )
            {
                // get list of entities for a specific data type
                $entityProfileTypes = $this->_model->getAllEntityProfileTypesByEntityProfileDataTypeId($id);

                return new EntityProfileTypesDataTypesCollection(new \Zend\Paginator\Adapter\Iterator($entityProfileTypes));
            }
            else
            {
                $entityProfileTypes = $this->_model->getByEntityProfileDataTypeIdAndEntityProfileTypeId($id, $profileTypeId);

                return ($entityProfileTypes->Id) ? $entityProfileTypes : false;
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
            $profileTypeId = $this->getEvent()->getRouteParam('entity_profile_type_id');
            if( !isset($profileTypeId) || empty($profileTypeId) )
            {
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            if( $profileTypeId == '*' )
            {
                $entityProfilesTypes = $this->_model->fetchAll();

                $entities = array();
                foreach ($entityProfilesTypes as $entityType)
                {
                    $entityType = $entityType->getArrayCopy();
                    $profileTypeId = $entityType['EntityProfileTypeId'];
                    unset($entityType['EntityProfileTypeId']);
                    if( !isset($entities[$profileTypeId]) )
                    {
                        $entities[$profileTypeId] = array();
                    }
                    $entities[$profileTypeId][] = $entityType;
                }

                $result = array('entities' => $entities);

                $entityProfilesTypes = new \ArrayIterator($result);
            }
            else
            {
                $entityProfilesTypes = $this->_model->getAllEntityProfileDataTypesByEntityProfileTypeId($profileTypeId);
            }

            return new EntityProfileTypesDataTypesCollection(new \Zend\Paginator\Adapter\Iterator($entityProfilesTypes));

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
