<?php
namespace MicroIceEventManager\V1\Rest\EntityDataFields;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EntityDataFieldsResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_modelFieldCfg;
    private $_data;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $ModelFieldCfg, TableGatewayInterface $Data)
    {
        $this->_model = $Model;
        $this->_modelFieldCfg = $ModelFieldCfg;
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
        // var_dump($data);exit(__FILE__.'::'.__LINE__);
        try
        {

            // validate entity data
            $dataId = $this->getEvent()->getRouteParam('entity_data_id');
            if(!isset($dataId) || empty($dataId)){
                throw new \InvalidArgumentException("Please specify an entity data for the entity!", 1);
            }

            $dbdata = $this->_data->getById($dataId);

            if(!$dbdata || $dbdata->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid entity data!", 1);
            }
            
            $this->getEventManager()->trigger('entity_data_fields.create.pre', $this, array());

            if(is_object($data)){
                $data = get_object_vars($data);
            }

            $fieldcfg = $this->_modelFieldCfg->getAllByTypeId($dbdata->TypeId);
            
            foreach ($fieldcfg as $key => $field) 
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

                // create entity
                $dataField = new EntityDataFieldsEntity();
                $dataField->Field = $fname;
                $dataField->Value = $fvalue;
                $dataField->DataId = $dataId;
                $dataField->Status = 1;

                

                // prepare for persist
                $result = $this->_model->createBulkInsert($dataField);
                // var_dump($result);exit(__FILE__.'::'.__LINE__);

            }

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            //clear old data for this entity
            $this->_model->delete(array('DataId' => $dataId));

            // persist
            $result = $this->_model->runBulkInsert(false);
            if(false == $result){
                throw new \Exception("Error Processing Request", 1);
            }
            // var_dump($result);exit(__FILE__.'::'.__LINE__);

            /*if($persist){
                // set entity type if anything is ok
                $this->_model->setentitytype();
            }*/
            
            $this->getEventManager()->trigger('entity_data_fields.create.post', $this, array('entity' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );
            
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
        try
        {
            if('*' == $id){
                return $this->fetchAll([]);
            }
            // validate entity data
            $dataId = $this->getEvent()->getRouteParam('entity_data_id');
            if(!isset($dataId) || empty($dataId)){
                throw new \InvalidArgumentException("Please specify an entity data for the entity!", 1);
            }

            $data = $this->_data->getById($dataId);

            if(!$data || $data->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid entity data!", 1);
            }
            unset($data);


            $field = $this->_model->getByIdAndDataId($id, $dataId);

            return $field;
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
            // validate entity data
            $dataId = $this->getEvent()->getRouteParam('entity_data_id');
            if(!isset($dataId) || empty($dataId)){
                throw new \InvalidArgumentException("Please specify an entity data for the entity!", 1);
            }

            $data = $this->_data->getById($dataId);

            if(!$data || $data->Status != 1){
                throw new \InvalidArgumentException("Please specify a valid entity data!", 1);
            }
            unset($data);

            $fields = $this->_model->getAllByDataId($dataId);

            return new EntityDataFieldsCollection(new \Zend\Paginator\Adapter\Iterator($fields));
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
