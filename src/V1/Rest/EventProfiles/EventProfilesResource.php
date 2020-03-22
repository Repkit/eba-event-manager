<?php
namespace MicroIceEventManager\V1\Rest\EventProfiles;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EventProfilesResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_translations;
    private $_profilesTypes;
    private $_profileDetails;
    private $_profileDetailFields;
    private $_profileTypesDataTypes;
    private $_profileData;
    private $_profileDataFields;
    private $_profileDataFieldConfig;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $Translations, TableGatewayInterface $ProfilesTypes, TableGatewayInterface $ProfileDetails, TableGatewayInterface $ProfileDetailFields, TableGatewayInterface $ProfileTypesDataTypes, TableGatewayInterface $ProfileData, TableGatewayInterface $ProfileDataFields, TableGatewayInterface $ProfileDataFieldConfig)
    {
        $this->_model = $Model;
        $this->_translations = $Translations;
        $this->_profilesTypes = $ProfilesTypes;
        $this->_profileDetails = $ProfileDetails;
        $this->_profileDetailFields = $ProfileDetailFields;
        $this->_profileTypesDataTypes = $ProfileTypesDataTypes;
        $this->_profileData = $ProfileData;
        $this->_profileDataFields = $ProfileDataFields;
        $this->_profileDataFieldConfig = $ProfileDataFieldConfig;
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
            $id = $this->getEvent()->getRouteParam('event_profile_id');
            $lang = $this->getEvent()->getRouteParam('language_code');
            if( $lang == '*' || $id == '*' )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $default = empty($id) ? true : false;
            $rdata = (array)$data;
            
            $this->getEventManager()->trigger('event_profiles.create.pre', $this, array());

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
                        throw new \InvalidArgumentException("An event profile with same name already exists!", 1);
                    }
                }

                // persist entity
                $inserted = $this->_model->insert($entity->getArrayCopy());
                if( !$inserted )
                {
                    throw new \Exception("Could not create event profile", 1);
                }
                $entity->Id = $this->_model->getLastInsertValue();
            }
            else
            {
                $entity = $this->_model->getById($id);
                if( !$entity || $entity->Status == 99 )
                {
                    throw new \InvalidArgumentException("Please specify a valid event profile!", 1);
                }
            }

            $entityTransClass = $this->_translations->getEntityClass();
            $translation = new $entityTransClass();

            $translation->exchangeArray($rdata);

            $translation->ProfileId = $entity->Id;
            $translation->Language = $lang;
            $translation->CreationDate = $creationDate;

            $inserted = $this->_translations->insert($translation->getArrayCopy());
            if( !$inserted )
            {
                throw new \Exception("Could not create event profile translation", 2);
            }

            $entity->Language = $lang;
            $entity->TranslationId = $this->_translations->getLastInsertValue();
            $entity->Name = $translation->Name;

            $this->getEventManager()->trigger('event_profiles.create.post', $this, array('entity' => $entity, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()));

            $connection->commit();

            return $entity;
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
        try
        {
            $this->getEventManager()->trigger('event_profiles.delete.pre', $this, array());

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            // deleting profile
            $result = $this->_model->delete(array('Id' => $id));
            if( !$result )
            {
                throw new \Exception("Error Processing Request", 1);
            }

            $this->getEventManager()->trigger('event_profiles.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()));

            $connection->commit();

            return (bool)$result;
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
            if( !isset($id) || empty($id) )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $lang = $this->getEvent()->getRouteParam('language_code');
            if( $lang == '*')
            {
                $eventProfiles = $this->_model->getTranslationsById($id);
            }
            else
            {
                if( !empty($lang) )
                {
                    if( $id == '*' )
                    {
                        $eventProfiles = $this->_model->getAllProfilesByLanguage($lang);
                    }
                    else
                    {
                        $eventProfile = $this->_model->getByIdAndLanguage($id, $lang);
                        if( !$eventProfile )
                        {
                            // if no translation return default
                            $eventProfile = $this->_model->getById($id);
                        }
                    }
                }
                else
                {
                    $eventProfile = $this->_model->getById($id);
                }

                if( !$eventProfile || $eventProfile->Status == 99 )
                {
                    if( !isset($eventProfiles) )
                    {
                        throw new \InvalidArgumentException("Error Processing Request", 1);
                    }
                }

                if( !isset($eventProfiles) && isset($eventProfile) )
                {
                    $eventProfiles = array($eventProfile);
                    $eventProfiles = new \ArrayIterator($eventProfiles);
                }
            }

            $collection = array();
            foreach ($eventProfiles as $key => $eventProfile)
            {
                if( !$eventProfile || $eventProfile->Status == 99 )
                {
                    throw new \InvalidArgumentException("Error Processing Request", 1);
                }

                $tid = $eventProfile->TranslationId;
                if( empty($tid) )
                {
                    continue;
                }

                $details = $this->_profileDetails->getAllByProfileId($tid);
                $detailTypes = array();
                foreach($details as $idx => $field)
                {
                    $fname = $field->Field;
                    $fvalue = $field->Value;
                    $ftype = $field->TypeId;
                    if(!isset($detailTypes[$ftype])){
                        $detailTypes[$ftype] = array();
                    }
                    $detailTypes[$ftype][$fname] = $fvalue;
                }

                $profilelang = $eventProfile->Language;
                if( $profilelang == null )
                {
                    $types = $this->_profilesTypes->getAllTypesByProfileId($id);
                }
                else
                {
                    $types = $this->_profilesTypes->getAllTypesByProfileIdAndLanguage($id, $profilelang);
                }

                $profileTypes = array();
                foreach ($types as $k => $type)
                {
                    $typeId = $type->TypeId;

                    //get detail field config for default values
                    $typeFieldConfigs = $this->_profileDetailFields->getAllByTypeId($typeId);
                    foreach ($typeFieldConfigs as $i => $typeFieldConfig)
                    {
                        $fname = $typeFieldConfig->Field;
                        $fvalue = $typeFieldConfig->Value;
                        if(!isset($detailTypes[$typeId][$fname])){
                            $detailTypes[$typeId][$fname] = $fvalue;
                        }
                    }

                    //data types
                    $profileDataTypesDb = $this->_profileTypesDataTypes->getAllEventProfileDataTypesByEventProfileTypeId($typeId);
                    $profileDataTypes = array();
                    foreach ($profileDataTypesDb as $j => $dataType)
                    {
                        if(!isset($profileDataTypes[$dataType->Name])){
                            $profileDataTypes[$dataType->Name] = array();
                        }

                        $dataTypeId = $dataType->Id;
                        $profileData = $this->_profileData->fetchAllByProfileIdAndTypeId($tid, $dataTypeId);

                        // create an array for easy of use
                        $xtraFields = array();
                        $fieldconfigs = $this->_profileDataFieldConfig->getAllByTypeId($dataTypeId);
                        foreach ($fieldconfigs as $key => $fieldconfig) {
                            $xtraFields[$fieldconfig->Field] = $fieldconfig->Value ? $fieldconfig->Value : null;
                        }

                        // prepare for assigment extra fields
                        $profileDataIds = array();
                        $profilesData = array();
                        foreach ($profileData as $key => $udata) {
                            $profileDataIds[] = $udata->Id;
                            foreach ($xtraFields as $xtraField => $xtraFieldValue) {
                                $udata->$xtraField = $xtraFieldValue;
                            }
                            $profilesData[$udata->Id] = $udata;
                        }
                        // interswitch
                        $profileData = $profilesData;
                        unset($profilesData);

                        if(!isset($profileDataIds) || empty($profileDataIds)){
                            continue;
                        }

                        // get extra fields for all event profile data
                        $profileDataFields = $this->_profileDataFields->fetchAllByDataId($profileDataIds);

                        // assign extra fields on each event data
                        foreach ($profileDataFields as $key => $udatafield) {
                            if( $udatafield->Status == 99 )
                            {
                                continue;
                            }
                            $fname = $udatafield->Field;
                            $profileData[$udatafield->DataId]->$fname = $udatafield->Value;
                            unset($fname);
                        }
                        # code...
                        $profileDataTypes[$dataType->Name] = array_values($profileData);
                    }

                    $type->Fields = $detailTypes[$typeId];
                    $type->Data = $profileDataTypes;
                    $profileTypes[] = $type;
                }

                $eventProfile->Types = $profileTypes;
                $collection[] = $eventProfile;
            }

            // $eventProfiles = new \ArrayIterator($collection);
            $eventProfiles = new \Zend\Paginator\Adapter\ArrayAdapter($collection);
            // return new EventProfilesCollection(new \Zend\Paginator\Adapter\Iterator($eventProfiles));
            return new EventProfilesCollection($eventProfiles);
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
            $eventProfiles = $this->_model->fetchAll();

            return new EventProfilesCollection(new \Zend\Paginator\Adapter\Iterator($eventProfiles));
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
            if( !isset($id) || empty($id) || '*' == $id )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $lang = $this->getEvent()->getRouteParam('language_code');
            if( $lang == '*' )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $this->getEventManager()->trigger('event_profiles.patch.pre', $this, array());

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
                // update translated event profile
                $tentity = $this->_translations->getByProfileIdAndLanguage($id, $lang);
                if( empty($tentity) || $tentity->Status == 99 )
                {
                    throw new \InvalidArgumentException("Error Processing Request", 1);
                }

                $rdata = (array)$data;
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

            $this->getEventManager()->trigger('event_profiles.patch.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()));

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

            $this->getEventManager()->trigger('event_profiles.update.pre', $this, array());

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
            $this->getEventManager()->trigger('event_profiles.update.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()));
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
        catch(\Exception $e)
        {
            if( !empty($connection) && $connection instanceof \Zend\Db\Adapter\Driver\ConnectionInterface )
            {
                $connection->rollback();
            }
            return new ApiProblem(417, $e->getMessage());
        }
    }
}
