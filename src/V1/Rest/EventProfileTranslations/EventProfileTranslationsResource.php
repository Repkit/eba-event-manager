<?php
namespace MicroIceEventManager\V1\Rest\EventProfileTranslations;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EventProfileTranslationsResource extends AbstractResourceListener implements EventManagerAwareInterface
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
        return new ApiProblem(405, 'The POST method has not been defined');
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
            $deleted = false;
            $this->getEventManager()->trigger('event_profile_translations.delete.pre', $this, array());

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            // logical deletion here
            $deleted = $this->_model->delete(array('Id' => $id));
            $deleted = (bool)$deleted;

            if(!$deleted){
                throw new \Exception("Error deleting translation", 1);
            }

            // trigger event for other dependencies
            $this->getEventManager()->trigger('event_profile_translations.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            return $deleted;
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
            $translation = $this->_model->getById($id);
            if( !$translation || $translation->Status == 99 ){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            return $translation;

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
            $translations = $this->_model->fetchAll(array('where'=>array('Status' => array(0,1))));

            return new EventProfileTranslationsCollection(new \Zend\Paginator\Adapter\Iterator($translations));

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
        try
        {
            $translation = $this->_model->getById($id);
            if(empty($translation) || $translation->Status == 99){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $this->getEventManager()->trigger('event_profile_translations.patch.pre', $this, array());

            $rdata = (array)$data;
            $edata = $translation->getArrayCopy();

            foreach($rdata as $field => $value)
            {
                if(array_key_exists($field, $edata)){
                    $translation->$field = $rdata[$field];
                    unset($rdata[$field]);
                }
            }

            if($edata != $translation->getArrayCopy())
            {
                $affectedRows = $this->_model->update($translation->getArrayCopy(), array('Id' => $id));
                if(!$affectedRows){
                    throw new \Exception("Error patching translation", 1);
                }
            }
            else
            {
                throw new \InvalidArgumentException("Nothing to patch", 1);
            }

            $this->getEventManager()->trigger('event_profile_translations.patch.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            return $translation;

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
            $translation = $this->_model->getById($id);
            if(empty($translation) || $translation->Status == 99){
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $this->getEventManager()->trigger('event_profile_translations.update.pre', $this, array());

            $rdata = (array)$data;
            $edata = $translation->getArrayCopy();
            foreach ($edata as $key => $value)
            {
                if('Id' == $key){
                    continue;
                }
                if(!isset($rdata[$key]) && !is_null($value)){
                    throw new \InvalidArgumentException("Incomplete translation", 1);
                }
                $translation->$key = $rdata[$key];
                unset($rdata[$key]);
            }

            if($edata != $translation->getArrayCopy())
            {
                $affectedRows = $this->_model->update($translation->getArrayCopy(), array('Id' => $id));
                if(!$affectedRows){
                    throw new \Exception("Error updating translation", 1);
                }
            }

            $this->getEventManager()->trigger('event_profile_translations.update.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            return $translation;
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
