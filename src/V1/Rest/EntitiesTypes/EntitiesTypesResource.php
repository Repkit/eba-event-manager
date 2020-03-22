<?php
namespace MicroIceEventManager\V1\Rest\EntitiesTypes;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EntitiesTypesResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_entitiesModel;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $EntitiesModel)
    {
        $this->_model = $Model;
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
            $entityId = $this->getEvent()->getRouteParam('entity_id');
            if( !isset($entityId) || empty($entityId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }
            
            $this->getEventManager()->trigger('entities_types.create.pre', $this, array());

            $entity = $this->_entitiesModel->getById($entityId);
            $typeEntityId = $entity->Id;
            if( !isset($typeEntityId) || empty($typeEntityId) ){
                throw new \InvalidArgumentException("No entity with id = $typeEntityId", 1);
            }

            // create entity
            $entityClass = $this->_model->getEntityClass();
            $entity = new $entityClass();
            $entity->exchangeArray((array)$data);
            $entity->EntityId = $entityId;
            
            // persist entity
            $inserted = $this->_model->insert($entity->getArrayCopy());

            if($inserted){
                $entity->Id = $this->_model->getLastInsertValue();
            }
            
            $this->getEventManager()->trigger('entities_types.create.post', $this, array('entity' => $entity, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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
            
            $this->getEventManager()->trigger('entities_types.delete.pre', $this, array());

            $entityId = $this->getEvent()->getRouteParam('entity_id');
            if( !isset($entityId) || empty($entityId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            $this->_model->delete(array('TypeId' => $id, 'EntityId' => $entityId));
            
            $this->getEventManager()->trigger('entities_types.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

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

            $entityId = $this->getEvent()->getRouteParam('entity_id');
            if( !isset($entityId) || empty($entityId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }
            $filter = $this->getEvent()->getQueryParam('filter');
            $filter = empty($filter) ? [] : $filter;
            if( $entityId == '*' )
            {
                // get list of entities for a specific type
                $filter['where'] = array('entities.Status' => 1);
                $entityTypes = $this->_model->getAllEntitiesByTypeId($id, $filter);
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

                // $e = $this->getEventManager();
                /*$e = new \Zend\EventManager\EventManager();
                $e->setIdentifiers(array(self::class));
                $e->attach('renderCollection',function($e){
                    var_dump('coco');exit(__FILE__.'::'.__LINE__);
                    $halCollection = $e->getParam('collection');
                    $halCollection->setPageSize(721);
                    $e->setParam('collection', $halCollection);
                });*/

                return new EntitiesTypesCollection(new \Zend\Paginator\Adapter\Iterator($entityTypes));

                return $entityTypes;
            }
            else
            {
                if('*' == $id){
                    return $this->fetchAll([]);
                }
                $entityTypes = $this->_model->getByTypeIdAndEntityId($id, $entityId);

                if(!$entityTypes || $entityTypes->Status == 99){
                    throw new \InvalidArgumentException("Error Processing Request", 1);
                }

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
            $entityId = $this->getEvent()->getRouteParam('entity_id');
            if( !isset($entityId) || empty($entityId) ){
                throw new \InvalidArgumentException("Error Processing Request",1);
            }

            if( $entityId == '*' )
            {
                $entitiesTypes = $this->_model->fetchAll(array('where'=>array('Status' => array(0,1))));

                $entities = array();
                foreach ($entitiesTypes as $entityType) 
                {
                    $entityType = $entityType->getArrayCopy();
                    $entityId = $entityType['EntityId'];
                    unset($entityType['EntityId']);
                    if( !isset($entities[$entityId]) ){
                        $entities[$entityId] = array();
                    }
                    $entities[$entityId][] =  $entityType;
                }

                $result = array('entities' => $entities);

                $entitiesTypes = new \ArrayIterator($result);
            }
            else
            {
                $entity = $this->_entitiesModel->getById($entityId);
                $typeEntityId = $entity->Id;
                if( !isset($typeEntityId) || empty($entityId) ){
                    throw new \InvalidArgumentException("No entity with id = $entityId", 1);
                }

                $filter = $params['filter'] ? $params['filter'] : [];

                 // exclude 99
                // $entitiesTypes = $this->_model->fetchAll(array('where'=>array('EntityId'=> $entityId, 'Status' => array(0,1))));
                $entitiesTypes = $this->_model->getAllTypesByEntityId($entityId, $filter);
                // $entitiesTypes = $this->_model->getAllExtended($joins, $where);
                // $entitiesTypes = new \ArrayIterator(array());
            }
           
            return new EntitiesTypesCollection(new \Zend\Paginator\Adapter\Iterator($entitiesTypes));

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
