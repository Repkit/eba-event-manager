<?php
namespace MicroIceEventManager\V1\Rest\EventProfileData;

use MicroIceEventManager\V1\Rest\EventProfileDataFields\EventProfileDataFieldsEntity;
use MicroIceEventManager\V1\Rest\EventProfilesData\EventProfilesDataEntity;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;

use Zend\Db\TableGateway\TableGatewayInterface;

use Zend\EventManager\EventManagerAwareTrait;
use Zend\EventManager\EventManagerAwareInterface;

class EventProfileDataResource extends AbstractResourceListener implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    private $_model;
    private $_dataFields;
    private $_dataFieldConfig;
    private $_dataType;
    private $_profilesData;

    public function __construct(TableGatewayInterface $Model, TableGatewayInterface $DataFields, TableGatewayInterface $DataFieldConfig, TableGatewayInterface $DataType, TableGatewayInterface $ProfilesData)
    {
        $this->_model = $Model;
        $this->_dataFields = $DataFields;
        $this->_dataFieldConfig = $DataFieldConfig;
        $this->_dataType = $DataType;
        $this->_profilesData = $ProfilesData;
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
            // validate event data type
            $dataType = $this->getEvent()->getRouteParam('event_profile_data_type_id');
            if( !isset($dataType) || empty($dataType) )
            {
                throw new \InvalidArgumentException("Please specify a type for the event profile data!", 1);
            }

            $type = $this->_dataType->getById($dataType);
            if( !$type || $type->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid event profile data type!", 1);
            }
            $typeId = $type->Id;
            unset($type);

            $eTranslationId = $this->getEvent()->getRouteParam('event_profile_translation_id');
            if( !isset($eTranslationId) || empty($eTranslationId) )
            {
                throw new \InvalidArgumentException("Please specify an event profile!", 1);
            }

            $this->getEventManager()->trigger('event_profile_data.create.pre', $this, array());

            // create entity
            $data = (array)$data;
            $creationDate = date('Y-m-d H:i:s');

            $eventdata = new EventProfileDataEntity();
            $eventdataArray = $eventdata->getArrayCopy();
            foreach ($eventdataArray as $key => $value)
            {
                $eventdata[$key] = $data[$key];
                unset($data[$key]);
            }
            $eventdata->TypeId = $typeId;
            $eventdata->CreationDate = $creationDate;

            // var_dump($eventdata, $data);exit(__FILE__.'::'.__LINE__);

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            // persist entity
            $inserted = $this->_model->insert($eventdata->getArrayCopy());

            if( $inserted )
            {
                $eventdata->Id = $this->_model->getLastInsertValue();
            }

            // validate insert
            $eventdataId = $eventdata->Id;
            if( !isset($eventdataId) || empty($eventdataId) )
            {
                throw new \Exception("Could not save event profile data!", 1);
            }

            $fieldconfigs = $this->_dataFieldConfig->getAllByTypeId($typeId);
            if( $fieldconfigs->count() > 0 )
            {
                $needpersist = false;
                foreach ($fieldconfigs as $fidx => $fieldconfig)
                {
                    $fname = $fieldconfig->Field;
                    $fvalue = null;
                    if( isset($data[$fname]) )
                    {
                        $fvalue = $data[$fname];
                    }

                    // validate required
                    $required = $fieldconfig->Required;
                    if( $required )
                    {
                        if( !$fvalue )
                        {
                            throw new \InvalidArgumentException("$fname is mandatory", 1);
                        }
                    }

                    // validate against pattern
                    $pattern = $fieldconfig->Pattern;
                    if( !empty($fvalue) && !empty($pattern) )
                    {
                        if ( !preg_match("/$pattern/", $fvalue) )
                        {
                            throw new \InvalidArgumentException("Invalid $fname", 1);
                        }
                    }

                    // assign to our entity to output purposes
                    $eventdata->$fname = $fvalue;

                    // if field value is null and filed is optional then don't add to persist
                    if( $fvalue === NULL )
                    {
                        continue;
                    }

                    // create entity
                    $datafield = new EventProfileDataFieldsEntity();
                    $datafield->Field = $fname;
                    $datafield->Value = $fvalue;
                    $datafield->Category = isset($data['Category']) ? $data['Category'] : null;
                    $datafield->DataId = $eventdata->Id;
                    $datafield->Status = 1;

                    // prepare for persist
                    $resultdf = $this->_dataFields->createBulkInsert($datafield);
                    if( empty($resultdf) )
                    {
                        if( $required )
                        {
                            throw new \Exception("Could not persist $fname which is mandatory", 1);
                        }
                    }
                    else
                    {
                        $needpersist = true;
                    }
                }

                // persist
                if( $needpersist )
                {
                    $result = $this->_dataFields->runBulkInsert(false);
                    if( !isset($result) || empty($result) )
                    {
                        throw new \Exception("Could not persist additional fields", 1);
                    }
                }
            }

            // assign entity to the specified event profile
            $eventProfilesData = new EventProfilesDataEntity();
            $eventProfilesData->ProfileId = $eTranslationId;
            $eventProfilesData->DataId = $eventdata->Id;
            $eventProfilesData->Status = $eventdata->Status;

            $assigned = $this->_profilesData->insert($eventProfilesData->getArrayCopy());
            if( !isset($assigned) || empty($assigned) )
            {
                throw new \Exception("Could not assign data to eventProfilesData!", 1);
            }

            $this->getEventManager()->trigger('event_profile_data.create.post', $this, array('entity' => $eventdata , 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            return $eventdata;
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
        try
        {
            // validate event data type
            $dataType = $this->getEvent()->getRouteParam('event_profile_data_type_id');
            if( !isset($dataType) || empty($dataType) )
            {
                throw new \InvalidArgumentException("Please specify a type for the event profile data!", 1);
            }

            $eTranslationId = $this->getEvent()->getRouteParam('event_profile_translation_id');
            if( !isset($eTranslationId) || empty($eTranslationId) )
            {
                throw new \InvalidArgumentException("Please specify an event profile!", 1);
            }

            $typeId = $dataType;

            $this->getEventManager()->trigger('event_profile_data.delete.pre', $this, array());

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            // delete the actual event data
            $ok = $this->_model->delete(array('TypeId' => $typeId, 'Id' => $id));

            // if nothing was deleted then we must stop here because a security breach can be here if combine wrong id with type id as bellow we delete by id only
            if( !$ok )
            {
                throw new \Exception("Error deleting data", 1);
            }

            $this->getEventManager()->trigger('event_profile_data.delete.post', $this, array('id' => $id, 'adapter' => $this->_model->getAdapter(), 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            return TRUE;
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

            // validate event data type
            $dataType = $this->getEvent()->getRouteParam('event_profile_data_type_id');
            if( !isset($dataType) || empty($dataType) )
            {
                throw new \InvalidArgumentException("Please specify a type for the event profile data!", 1);
            }

            $eTranslationId = $this->getEvent()->getRouteParam('event_profile_translation_id');
            if( !isset($eTranslationId) || empty($eTranslationId) )
            {
                throw new \InvalidArgumentException("Please specify an event profile!", 1);
            }

            $type = $this->_dataType->getById($dataType);
            if( !$type || $type->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid event profile data type!", 1);
            }
            $typeId = $type->Id;
            unset($type);

            $eventData = $this->_model->getByIdAndTypeId($id, $typeId);

            $id = $eventData->Id;
            if( !isset($id) || empty($id) || $eventData->Status == 99 )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            // validate data is assigned to the required event
            $eventsData = $this->_profilesData->getByProfileIdAndDataId($eTranslationId, $id);
            $eventsdataid = $eventsData->Id;
            if( !isset($eventsdataid) || empty($eventsdataid) || $eventsData->Status == 99 )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            // add extra defined fields if any
            $fieldconfigs = $this->_dataFieldConfig->getAllByTypeId($typeId);
            if( $fieldconfigs->count() > 0 )
            {
                foreach ($fieldconfigs as $key => $fieldconfig) {
                    $fname = $fieldconfig->Field;
                    $eventData->$fname = null;
                    unset($fname);
                }

                $xtraFields = $this->_dataFields->getAllByDataId($id);
                foreach ($xtraFields as $key => $xtra) {
                    $fname = $xtra->Field;
                    $eventData->$fname = $xtra->Value;
                    unset($fname);
                }
            }

            return $eventData;
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
            // validate event data type
            $dataType = $this->getEvent()->getRouteParam('event_profile_data_type_id');
            if( !isset($dataType) || empty($dataType) )
            {
                throw new \InvalidArgumentException("Please specify a type for the event profile data!", 1);
            }

            $eTranslationId = $this->getEvent()->getRouteParam('event_profile_translation_id');
            if( !isset($eTranslationId) || empty($eTranslationId) )
            {
                throw new \InvalidArgumentException("Please specify an event profile!", 1);
            }

            $type = $this->_dataType->getById($dataType);
            if( !$type || $type->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid event profile data type!", 1);
            }
            $typeId = $type->Id;
            unset($type);

            $detailsLimit = 100;
            if( count($params) )
            {
                $fetchall = false;
                // overwrite default limit
                if( !empty($params['limit']) )
                {
                    $rqlimit = intval($params['limit']);
                    if( $rqlimit > $detailsLimit )
                    {
                        $params['limit'] = $detailsLimit;
                    }
                    else
                    {
                        $fetchall = true;
                    }
                    unset($rqlimit);
                }
                else
                {
                    $params['limit'] = $detailsLimit;
                }

                if( $fetchall )
                {
                    $where = null;
                    if( !empty($params['filter']) )
                    {
                        $where = $params['filter'];
                    }

                    $eventData = $this->_model->fetchAllByProfileIdAndTypeId($eTranslationId, $typeId);

                    // create an array for easy of use
                    $xtraFields = array();
                    // get extra fields for this type to add to entities
                    $fieldconfigs = $this->_dataFieldConfig->getAllByTypeId($typeId);
                    foreach ($fieldconfigs as $key => $fieldconfig)
                    {
                        $xtraFields[] = $fieldconfig->Field;
                    }

                    // prepare for assigment extra fields
                    $eventdataIds = array();
                    $eventDatas = array();
                    foreach ($eventData as $key => $udata)
                    {
                        $eventdataIds[] = $udata->Id;
                        foreach ($xtraFields as $xtraField)
                        {
                            $udata->$xtraField = null;
                        }
                        $eventDatas[$udata->Id] = $udata;
                    }
                    // interswitch
                    $eventData = $eventDatas;
                    unset($eventDatas);

                    if( !isset($eventdataIds) || empty($eventdataIds) )
                    {
                        return;
                    }

                    // get extra fields for all event data
                    $eventDataFields = $this->_dataFields->fetchAllByDataId($eventdataIds);

                    // assign extra fields on each event data
                    foreach ($eventDataFields as $key => $udatafield)
                    {
                        $fname = $udatafield->Field;
                        $eventData[$udatafield->DataId]->$fname = $udatafield->Value;
                        unset($fname);
                    }

                    // convert to iterator
                    $eventData = new \ArrayIterator($eventData);
                }
                else
                {
                    \Zend\EventManager\StaticEventManager::getInstance()->attach(
                        'ZF\Rest\RestController', 'getList.post', function ($e) {

                        $halCollection = $e->getParam('collection');
                        $halCollection->setAttributes(
                            array(
                                // '_error' => array(
                                "type" => "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html",
                                "title" => "Request Entity Too Large",
                                "status" => 413,
                                "detail" => "Please specify a valid limit parameter!"
                                // ),
                                // added here just to specify display order
                                // "_links" => null,
                                // "_embedded" => null,
                                // "page_size" => 10,
                                // "page_count" => 0,
                                // "total_items" => null,
                                // "page" => 0,
                            )
                        );

                        $e->getTarget()->getResponse()->setStatusCode(413);

                    });

                    \Zend\EventManager\StaticEventManager::getInstance()->attach(
                        'ZF\Hal\Plugin\Hal', 'renderCollection.post', function ($e) {

                        // $halCollection = $e->getParam('collection');
                        $payload = $e->getParam('payload');
                        unset($payload['_embedded']);
                        unset($payload['page_count']);
                        unset($payload['page_size']);
                        unset($payload['total_items']);
                        unset($payload['page']);

                        // var_dump($payload['_links']);exit(__FILE__.'::'.__LINE__);

                    });

                    $eventData = new \ArrayIterator(array());
                }

            }
            else
            {
                // exclude 99
                $eventData = $this->_model->fetchAllByProfileIdAndTypeId($eTranslationId, $typeId);
            }

            return new EventProfileDataCollection(new \Zend\Paginator\Adapter\Iterator($eventData));

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
        return $this->update($id, $data);
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
            // validate event data type
            $dataType = $this->getEvent()->getRouteParam('event_profile_data_type_id');
            if( !isset($dataType) || empty($dataType) )
            {
                throw new \InvalidArgumentException("Please specify a type for the event profile data!", 1);
            }

            $eTranslationId = $this->getEvent()->getRouteParam('event_profile_translation_id');
            if( !isset($eTranslationId) || empty($eTranslationId) )
            {
                throw new \InvalidArgumentException("Please specify an event profile!", 1);
            }

            $type = $this->_dataType->getById($dataType);
            if( !$type || $type->Status != 1 )
            {
                throw new \InvalidArgumentException("Please specify a valid event profile data type!", 1);
            }
            $typeId = $type->Id;
            unset($type);

            // select entity
            $eventdata = $this->_model->getByIdAndTypeId($id, $typeId);
            if( empty($eventdata) )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }
            $id = $eventdata->Id;
            if( !isset($id) || empty($id) || $eventdata->Status == 99 )
            {
                throw new \InvalidArgumentException("Error Processing Request", 1);
            }

            $this->getEventManager()->trigger('event_profile_data.update.pre', $this, array());

            // populate with patch values
            $data = (array)$data;
            $eventdataArray = $eventdata->getArrayCopy();

            $needupdate = false; //flag if main entity need updated
            foreach ($eventdataArray as $key => $value)
            {
                if( in_array($key, array('Timestamp','CreationDate')) )
                {
                    unset($data[$key]);
                    continue;
                }
                if( isset($data[$key]) )
                {
                    $eventdata[$key] = $data[$key];
                    unset($data[$key]);
                    $needupdate = true;
                }
            }

            // make sure id and type stay the same
            $eventdata->TypeId = $typeId;
            $eventdata->Id = $id;

            $connection = $this->_model->getAdapter()->getDriver()->getConnection();
            $connection->beginTransaction();

            // update entity
            if( $needupdate )
            {
                $updated = $this->_model->update($eventdata->getArrayCopy(),array('Id' => $id));
            }

            $fieldconfigs = $this->_dataFieldConfig->getAllByTypeId($typeId);
            if( $fieldconfigs->count() > 0 )
            {
                // attach extra field to the main entity
                $datafields = $this->_dataFields->fetchAllByDataId($eventdata->Id);
                $xtraFields = array();
                foreach ($datafields as $key => $datafield)
                {
                    $field = $datafield->Field;
                    $eventdata->$field = $datafield->Value;
                    $xtraFields[$field] = $datafield;
                    unset($field);
                }

                foreach ($fieldconfigs as $fidx => $fieldconfig)
                {
                    $fname = $fieldconfig->Field;
                    $fvalue = null;
                    if( !isset($data[$fname]) )
                    {
                        continue;
                    }

                    $fvalue = $data[$fname];

                    // select entity
                    if( isset($xtraFields[$fname]) )
                    {
                        $datafield = $xtraFields[$fname];
                        unset($xtraFields[$fname]);
                    }
                    else
                    {
                        $datafield = new EventProfileDataFieldsEntity();
                    }

                    $datafieldId = $datafield->Id;
                    $insert = false;
                    if( !isset($datafieldId) || empty($datafieldId) )
                    {
                        // continue;
                        $datafield->DataId = $eventdata->Id;
                        $datafield->Field = $fname;
                        $insert = true;
                    }

                    // validate required
                    $required = $fieldconfig->Required;
                    if( $required )
                    {
                        if( !$fvalue )
                        {
                            throw new \InvalidArgumentException("$fname is mandatory", 1);
                        }
                    }

                    // validate against pattern
                    $pattern = $fieldconfig->Pattern;
                    if( !empty($fvalue) && !empty($pattern) )
                    {
                        if ( !preg_match("/$pattern/", $fvalue) )
                        {
                            throw new \InvalidArgumentException("Invalid $fname", 1);
                        }
                    }

                    $datafield->Value = $fvalue;
                    $datafield->Status = $eventdata->Status;

                    if( $insert )
                    {
                        $this->_dataFields->insert($datafield->getArrayCopy());
                    }
                    else
                    {
                        $this->_dataFields->update($datafield->getArrayCopy(), array('Id' => $datafield->Id));
                    }

                    // add extra field to our entity
                    $eventdata->$fname = $fvalue;
                }
            }

            $this->getEventManager()->trigger('event_profile_data.update.post', $this, array('id' => $id, 'data' => $data, 'routeParams' => $this->getEvent()->getRouteMatch()->getParams()) );

            $connection->commit();

            // attach extra field to the main entity
            /*$datafields = $this->_dataFields->fetchAllByDataId($eventdata->Id);
            foreach ($datafields as $key => $datafield)
            {
                $field = $datafield->Field;
                $eventdata->$field = $datafield->Value;
                unset($field);
            }*/

            return $eventdata;
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
}
