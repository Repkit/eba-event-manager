<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileTypes;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EntityProfileTypesResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_translations;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $Translations)
    {
        $this->_model = $Model;
        $this->_translations = $Translations;
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
            $this->getEventManager()->trigger('entity_profile_types.create.pre', $this, array());

            $id = $this->getEvent()->getRouteParam('entity_profile_types_id');
            $lang = $this->getEvent()->getRouteParam('language_code');
            if( $lang == '*' || $id == '*' )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $default = empty($id) ? true : false;
            $rdata = (array)$data;

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            $creationDate = date('Y-m-d H:i:s');
            if( $default )
            {
                // create entity
                $entityClass = $this->_model->getEntityClass();
                $entity = new $entityClass();
                $entity->exchangeArray($rdata);
                $entity->CreationDate = $creationDate;

                //validate translation
                if( !empty($rdata['Name']) )
                {
                    $texists = $this->_translations->getByNameAndStatusAndLanguage($rdata['Name'], $entity->Status, $lang);
                    if( !empty($texists) )
                    {
                        throw new \InvalidArgumentException("An entity profile type with same name already exists!", 1);
                    }
                }

                // persist entity
                $inserted = $this->_model->insert($entity->getArrayCopy());
                if( !$inserted )
                {
                    throw new \Exception("Could not create entity profile type", 1);
                }
                $entity->Id = $this->_model->getLastInsertValue();
            }
            else
            {
                $entity = $this->_model->getById($id);
                if( !$entity || $entity->Status == 99 )
                {
                    throw new \InvalidArgumentException("Please specify a valid entity profile type!", 1);
                }
            }

            $entityTransClass = $this->_translations->getEntityClass();
            $translation = new $entityTransClass();

            $translation->exchangeArray($rdata);

            $translation->TypeId = $entity->Id;
            $translation->Language = $lang;
            $translation->CreationDate = $creationDate;

            $inserted = $this->_translations->insert($translation->getArrayCopy());
            if( !$inserted )
            {
                throw new \Exception("Could not create entity profile type translation", 2);
            }

            $entity->Language = $lang;
            $entity->TranslationId = $this->_translations->getLastInsertValue();
            $entity->Name = $translation->Name;

            $this->getEventManager()->trigger('entity_profile_types.create.post', $this, array('entity' => $entity, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()));

            $connection->commit();

            return $entity;
        }
        catch (\InvalidArgumentException $e)
        {
            if( !empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface )
            {
                $connection->rollback();
            }
            return new ApiProblem(400, $e->getMessage());
        }
        catch (\Exception $e)
        {
            if( !empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface )
            {
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
        try
        {
            $this->getEventManager()->trigger('entity_profile_types.delete.pre', $this, array());

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            // deleting profile type
            $result = $this->_model->delete(array('Id' => $id));
            if( !$result )
            {
                throw new \Exception("Error Processing Request", 1);
            }

            $this->getEventManager()->trigger('entity_profile_types.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()));

            $connection->commit();

            return (bool)$result;
        }
        catch (\Exception $e)
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

            $lang = $this->getEvent()->getRouteParam('language_code');
            if( $lang == '*')
            {
                $profileTypes = $this->_model->getTranslationsById($id);
            }
            else
            {
                if( !empty($lang) )
                {
                    if( $id == '*' )
                    {
                        $profileTypes = $this->_model->getAllProfileTypesByLanguage($lang);
                    }
                    else
                    {
                        $profileType = $this->_model->getByIdAndLanguage($id, $lang);
                        if( !$profileType )
                        {
                            // if no translation return default
                            $profileType = $this->_model->getById($id);
                        }
                    }
                }
                else
                {
                    $profileType = $this->_model->getById($id);
                }

                if( !$profileType || $profileType->Status == 99 )
                {
                    if( !isset($profileTypes) )
                    {
                        throw new \InvalidArgumentException("Error Processing Request", 1);
                    }
                }

                if( !isset($profileTypes) && isset($profileType) )
                {
                    $profileTypes = array($profileType);
                    $profileTypes = new \ArrayIterator($profileTypes);
                }
            }

            return new EntityProfileTypesCollection(new \Zend\Paginator\Adapter\Iterator($profileTypes));
        }
        catch (\InvalidArgumentException $e)
        {
            return new ApiProblem(400, $e->getMessage());
        }
        catch (\Exception $e)
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
            $profileTypes = $this->_model->fetchAll();

            return new EntityProfileTypesCollection(new \Zend\Paginator\Adapter\Iterator($profileTypes));
        }
        catch (\Exception $e)
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
            if( !isset($id) || empty($id) || '*' == $id )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $lang = $this->getEvent()->getRouteParam('language_code');
            if( $lang == '*' )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $this->getEventManager()->trigger('entity_profile_types.patch.pre', $this, array());

            $rdata = (array)$data;

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            $entity = $this->_model->getById($id);
            if( !$entity )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }
            $edata = $entity->getArrayCopy();

            foreach ($rdata as $field => $value)
            {
                if( in_array($field, array('Id', 'Timestamp', 'CreationDate')) )
                {
                    continue;
                }
                if( array_key_exists($field, $edata) )
                {
                    $entity->$field = $rdata[$field];
                    unset($rdata[$field]);
                }
            }
            $updateData = $entity->getArrayCopy();
            unset($updateData['TranslationId']);
            unset($updateData['Name']);
            unset($updateData['Language']);
            unset($updateData['Timestamp']);

            $this->_model->update($updateData, array('Id' => $id));

            if( isset($lang) && !empty($lang) )
            {
                // update translated entity profile type
                $tentity = $this->_translations->getByTypeIdAndLanguage($id, $lang);
                if( empty($tentity) || $tentity->Status == 99 )
                {
                    throw new \InvalidArgumentException("Error Processing Request", 1);
                }

                $tedata = $tentity->getArrayCopy();
                foreach ($rdata as $field => $value)
                {
                    if( in_array($field, array('Id', 'Timestamp', 'CreationDate')) )
                    {
                        continue;
                    }
                    if( array_key_exists($field, $tedata) )
                    {
                        $tentity->$field = $rdata[$field];
                        unset($rdata[$field]);
                    }
                }
                //keep parent status
                $tentity->Status = $entity->Status;

                if( !empty($lang) && !empty($rdata) )
                {
                    throw new \InvalidArgumentException("The folowing fields can't be translated: " . implode(', ', array_keys($rdata)), 1);
                }

                $tupdateData = $tentity->getArrayCopy();
                $this->_translations->update($tupdateData, array('Id' => $tentity->Id));
            }

            $this->getEventManager()->trigger('entity_profile_types.patch.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()));

            $connection->commit();

            return $this->fetch($id);
        }
        catch (\InvalidArgumentException $e)
        {
            if( !empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface )
            {
                $connection->rollback();
            }
            return new ApiProblem(400, $e->getMessage());
        }
        catch (\Exception $e)
        {
            if( !empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface )
            {
                $connection->rollback();
            }
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
            if( !isset($id) || empty($id) || $id == '*' )
            {
                throw new \Exception("Error Processing Request", 1);
            }

            $lang = $this->getEvent()->getRouteParam('language_code');
            if( !empty($lang) )
            {
                return new ApiProblem(405, 'The PUT method has not been defined for translated resources');
            }

            $this->getEventManager()->trigger('entity_profile_types.update.pre', $this, array());

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            $entity = $this->_model->getById($id);
            if( empty($entity) || $entity->Status == 99 )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $rdata = (array)$data;
            $edata = $entity->getArrayCopy();
            foreach ($rdata as $field => $value)
            {
                if( array_key_exists($field, $edata) )
                {
                    if( in_array($field, array('Id', 'CreationDate', 'Timestamp')) )
                    {
                        continue;
                    }
                    $entity->$field = $rdata[$field];
                    unset($rdata[$field]);
                }
            }
            // update profile type
            $updateData = $entity->getArrayCopy();
            unset($updateData['TranslationId']);
            unset($updateData['Name']);
            unset($updateData['Language']);
            unset($updateData['Timestamp']);
            $this->_model->update($updateData, array('Id' => $id));
            $this->getEventManager()->trigger('entity_profile_types.update.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()));
            $connection->commit();

            return $this->fetch($id);
        }
        catch (\InvalidArgumentException $e)
        {
            if( !empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface )
            {
                $connection->rollback();
            }
            return new ApiProblem(400, $e->getMessage());
        }
        catch (\Exception $e)
        {
            if( !empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface )
            {
                $connection->rollback();
            }
            return new ApiProblem(417, $e->getMessage());
        }
    }
}
