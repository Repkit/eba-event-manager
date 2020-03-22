<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileDetails;

use MicroIceEventManager\V1\Rest\EntityProfilesTypes\EntityProfilesTypesEntity;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EntityProfileDetailsResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_modelFieldCfg;
    private $_profileTranslation;
    private $_profileTypes;
    private $_modelProfilesTypes;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $ModelFieldCfg, TableGatewayInterface $ProfileTranslation, TableGatewayInterface $ProfileTypes, TableGatewayInterface $ProfilesTypes)
    {
        $this->_model = $Model;
        $this->_modelFieldCfg = $ModelFieldCfg;
        $this->_profileTranslation = $ProfileTranslation;
        $this->_profileTypes = $ProfileTypes;
        $this->_modelProfilesTypes = $ProfilesTypes;
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
            // validate entity profile type
            $typeId = $this->getEvent()->getRouteParam('entity_profile_type_id');
            if( !isset($typeId) || empty($typeId) )
            {
                throw new \InvalidArgumentException("Please specify an entity profile type for the entity!", 1);
            }

            $profileType = $this->_profileTypes->getById($typeId);
            if( !$profileType || $profileType->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid entity profile type!", 1);
            }

            // validate entity profile translation
            $translationId = $this->getEvent()->getRouteParam('entity_profile_translation_id');
            if( !isset($translationId) || empty($translationId) )
            {
                throw new \InvalidArgumentException("Please specify an entity profile translation for the entity!", 1);
            }

            $profileTranslation = $this->_profileTranslation->getById($translationId);
            if( !$profileTranslation || $profileTranslation->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid entity profile translation!", 1);
            }

            $this->getEventManager()->trigger('entity_profile_details.create.pre', $this, array());

            if( is_object($data) )
            {
                $data = get_object_vars($data);
            }

            $fieldcfg = $this->_modelFieldCfg->getAllByTypeId($typeId);
            if( $fieldcfg->count() == 0 )
            {
                throw new \Exception("There are no field configuration for this profile type!", 1);
            }
            $noConfiguration = true;
            foreach ($fieldcfg as $key => $field)
            {
                $fname = $field->Field;
                $fvalue = null;
                if( isset($data[$fname]) )
                {
                    $fvalue = $data[$fname];
                    $noConfiguration = false;
                }

                // validate required
                $required = $field->Required;
                if( $required )
                {
                    if( !$fvalue )
                    {
                        throw new \Exception("$fname is mandatory", 1);
                    }
                }

                // validate against pattern
                $pattern = $field->Pattern;
                if( !empty($fvalue) && !empty($pattern) )
                {
                    if( !preg_match("/$pattern/", $fvalue) )
                    {
                        throw new \Exception("Invalid $fname", 1);
                    }
                }

                if( empty($fvalue) )
                {
                    continue;
                }

                // create entity
                $profileField = new EntityProfileDetailsEntity();
                $profileField->ProfileId = $translationId;
                $profileField->Field = $fname;
                $profileField->Value = $fvalue;
                $profileField->Category = null;
                $profileField->TypeId = $typeId;
                $profileField->Status = 1;

                // prepare for persist
                $result = $this->_model->createBulkInsert($profileField);
                // var_dump($result);exit(__FILE__.'::'.__LINE__);
            }

            if( $noConfiguration )
            {
                throw new \Exception("Received fields have no configuration!", 1);
            }

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            //clear old data for this entity
            $this->_model->delete(array('ProfileId' => $translationId, 'TypeId' => $typeId));

            // persist
            $result = $this->_model->runBulkInsert(false);

            if( !isset($result) || empty($result) )
            {
                throw new \Exception("Error Processing Request", 1);
            }

            // assign type to profile
            $profilesTypes = new EntityProfilesTypesEntity();
            $profilesTypes->ProfileId = $profileTranslation->ProfileId;
            $profilesTypes->TypeId = $typeId;
            $profilesTypes->Status = 1;

            $assigned = $this->_modelProfilesTypes->insert($profilesTypes->getArrayCopy());
            if(!isset($assigned) || empty($assigned)){
                throw new \Exception("Could not assign data to Entity Profiles Types!", 1);
            }

            $this->getEventManager()->trigger('entity_profile_details.create.post', $this, array('entity' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            return (bool)$result;
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
            // validate entity profile translation
            $translationId = $this->getEvent()->getRouteParam('entity_profile_translation_id');
            if( !isset($translationId) || empty($translationId) )
            {
                throw new \InvalidArgumentException("Please specify an entity profile translation for the entity!", 1);
            }

            $profileTranslation = $this->_profileTranslation->getById($translationId);
            if( !$profileTranslation || $profileTranslation->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid entity profile translation!", 1);
            }

            // validate entity profile type
            if( !isset($id) || empty($id) )
            {
                throw new \InvalidArgumentException("Please specify an entity profile type for the entity!", 1);
            }

            $profileType = $this->_profileTypes->getById($id);
            if( !$profileType || $profileType->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid entity profile type!", 1);
            }

            $fields = $this->_model->getAllByProfileIdAndTypeId($translationId, $profileType->Id);

            return new EntityProfileDetailsCollection(new \Zend\Paginator\Adapter\Iterator($fields));
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
    public function fetchAll($params = array())
    {
        try
        {
            // validate entity profile translation
            $translationId = $this->getEvent()->getRouteParam('entity_profile_translation_id');
            if( !isset($translationId) || empty($translationId) )
            {
                throw new \InvalidArgumentException("Please specify an entity profile translation for the entity!", 1);
            }

            $profileTranslation = $this->_profileTranslation->getById($translationId);
            if( !$profileTranslation || $profileTranslation->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid entity profile translation!", 1);
            }

            $profileFields = $this->_model->getAllByProfileId($translationId);
            if(!$profileFields){
                $profileFields = array();
            }

            $types = array();
            foreach ($profileFields as $key => $field) {
                $field = $field->getArrayCopy();
                $typeId = $field['TypeId'];
                unset($field['TypeId']);
                unset($field['ProfileId']);
                if(!isset($typeId) || empty($typeId)){
                    continue;
                }
                if(!isset($types[$typeId])){
                    $types[$typeId] = array();
                }
                $types[$typeId][] = $field;
            }

            $result = array('types' => $types);

            // $data = new \ArrayIterator($result);
            $data = new \Zend\Paginator\Adapter\ArrayAdapter($result);

            // return new EntityProfileDetailsCollection(new \Zend\Paginator\Adapter\Iterator($data));
            return new EntityProfileDetailsCollection($data);
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
            $data = (array)$data;
            if( empty($data) )
            {
                throw new \InvalidArgumentException('Invalid patch data');
            }
            // validate entity profile type
            $typeId = $this->getEvent()->getRouteParam('entity_profile_type_id');
            if( !isset($typeId) || empty($typeId) )
            {
                throw new \InvalidArgumentException("Please specify an entity profile type for the entity!", 1);
            }

            $profileType = $this->_profileTypes->getById($typeId);
            if( !$profileType || $profileType->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid entity profile type!", 1);
            }
            unset($profileType);

            // validate entity profile translation
            $translationId = $this->getEvent()->getRouteParam('entity_profile_translation_id');
            if( !isset($translationId) || empty($translationId) )
            {
                throw new \InvalidArgumentException("Please specify an entity profile translation for the entity!", 1);
            }

            $profileTranslation = $this->_profileTranslation->getById($translationId);
            if( !$profileTranslation || $profileTranslation->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid entity profile translation!", 1);
            }
            unset($profileTranslation);

            $fields = $this->_modelFieldCfg->getAllByTypeId($typeId);
            if( !$fields->count() )
            {
                throw new \InvalidArgumentException('Invalid type fields');
            }

            $this->getEventManager()->trigger('entity_profile_details.patch.pre', $this, array());

            $updatedFields = array();
            foreach ($fields as $key => $field)
            {
                $fname = $field->Field;
                if( !isset($data[$fname]) )
                {
                    continue;
                }
                $fvalue = $data[$fname];

                // validate required
                $required = $field->Required;
                if( $required )
                {
                    if( !$fvalue )
                    {
                        throw new \InvalidArgumentException("$fname  is mandatory", 1);
                    }
                }

                // validate against pattern
                $pattern = $field->Pattern;
                if( !empty($fvalue) && !empty($pattern) )
                {
                    if( !preg_match("/$pattern/", $fvalue) )
                    {
                        throw new \InvalidArgumentException("Invalid $fname", 1);
                    }
                }
                $updatedFields[] = $fname;

                // create entity
                $udetails = new EntityProfileDetailsEntity();
                $udetails->ProfileId = $translationId;
                $udetails->Field = $fname;
                $udetails->Value = $fvalue;
                $udetails->Category = null;
                $udetails->TypeId = $typeId;
                $udetails->Status = 1;

                // prepare for persist
                $this->_model->createBulkInsert($udetails);
            }

            if( empty($updatedFields) )
            {
                throw new \InvalidArgumentException('Invalid update fields');
            }

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            //clear old data for this entity
            $this->_model->delete(array('ProfileId' => $translationId, 'TypeId' => $typeId,'Field' => $updatedFields));

            // persist
            $result = $this->_model->runBulkInsert(false);

            $this->getEventManager()->trigger('entity_profile_details.patch.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            return (bool)$result;
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
