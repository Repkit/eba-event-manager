<?php
namespace MicroIceEventManager\V1\Rest\EntityTypesDataTypes;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EntityTypesDataTypesResource extends AbstractResourceListener implements EventManagerAwareInterface
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
            $entityTypeId = $this->getEvent()->getRouteParam('entity_type_id');
            if( !isset($entityTypeId) || empty($entityTypeId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            // $entityType = $this->_entitiessModel->getById($entityId);
            // $typeEntityId = $entity->Id;
            // if( !isset($typeEntityId) || empty($typeEntityId) ){
            //     throw new \Exception("No entity with id = $typeEntityId", 1);
            // }
            
            $this->getEventManager()->trigger('entity_types_data_types.create.pre', $this, array());

            // create entity
            $entityClass = $this->_model->getEntityClass();
            $entity = new $entityClass();
            $entity->exchangeArray((array)$data);
            $entity->EntityTypeId = $entityTypeId;
            
            // persist entity
            $inserted = $this->_model->insert($entity->getArrayCopy());

            if($inserted){
                $entity->Id = $this->_model->getLastInsertValue();
            }
            
            $this->getEventManager()->trigger('entity_types_data_types.create.post', $this, array('entity' => $entity, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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
            
            $this->getEventManager()->trigger('entity_types_data_types.delete.pre', $this, array());

            $entityTypeId = $this->getEvent()->getRouteParam('entity_type_id');
            if( !isset($entityTypeId) || empty($entityTypeId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            $this->_model->delete(array('EntityDataTypeId' => $id, 'EntityTypeId' => $entityTypeId));
            
            $this->getEventManager()->trigger('entity_types_data_types.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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
        try
        {

            if(!isset($id) || empty($id)){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $entityTypeId = $this->getEvent()->getRouteParam('entity_type_id');
            if( !isset($entityTypeId) || empty($entityTypeId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            if( $entityTypeId == '*' )
            {
                // get list of entities for a specific type
                $entityTypes = $this->_model->getAllEntityTypesByEntityDataTypeId($id);
                /*$entities = array();
                $entities[$id] = array();
                foreach ($entityTypes as $entity) 
                {
                    // var_dump($entity);exit(__FILE__.'::'.__LINE__);
                    $entity = $entity->getArrayCopy();
                    unset($entity['EntityId']);
                    $entities[$id][] = $entity;
                }
                $result = array('entities' => $entities);

                $entityTypes = new \ArrayIterator($result);*/

                return new EntityTypesDataTypesCollection(new \Zend\Paginator\Adapter\Iterator($entityTypes));
            }
            else
            {
                $entityTypes = $this->_model->getByEntityDataTypeIdAndEntityTypeId($id, $entityTypeId);

                // if($entityTypes->Status == 99){
                //     throw new \InvalidArgumentException("Error processing request");
                // }

                return ($entityTypes->Id) ? $entityTypes : false;
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
            $entityTypeId = $this->getEvent()->getRouteParam('entity_type_id');
            if( !isset($entityTypeId) || empty($entityTypeId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            if( $entityTypeId == '*' )
            {
                $entitiesTypes = $this->_model->fetchAll();

                $entities = array();
                foreach ($entitiesTypes as $entityType) 
                {
                    $entityType = $entityType->getArrayCopy();
                    $entityTypeId = $entityType['EntityTypeId'];
                    unset($entityType['EntityTypeId']);
                    if( !isset($entities[$entityTypeId]) ){
                        $entities[$entityTypeId] = array();
                    }
                    $entities[$entityTypeId][] =  $entityType;
                }

                $result = array('entities' => $entities);

                $entitiesTypes = new \ArrayIterator($result);
            }
            else
            {
                // $entity = $this->_entitiesModel->getById($entityTypeId);
                // $typeEntityId = $entity->EntityTypeId;
                // if( !isset($typeEntityId) || empty($entityTypeId) ){
                //     throw new \Exception("No entity with id = $entityTypeId", 1);
                // }

                 // exclude 99
                // $entitiesTypes = $this->_model->fetchAll(array('where'=>array('EntityTypeId'=> $entityTypeId)));
                $entitiesTypes = $this->_model->getAllEntityDataTypesByEntityTypeId($entityTypeId);
                // $entitiesTypes = $this->_model->getAllExtended($joins, $where);
                // $entitiesTypes = new \ArrayIterator(array());
            }
           
            return new EntityTypesDataTypesCollection(new \Zend\Paginator\Adapter\Iterator($entitiesTypes));

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
