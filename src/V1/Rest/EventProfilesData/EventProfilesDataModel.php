<?php

namespace MicroIceEventManager\V1\Rest\EventProfilesData;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EventProfilesDataModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EventProfilesData\EventProfilesDataEntity';
	protected $TableName 	= 'event_profiles_data';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function getByProfileIdAndDataId($ProfileId, $DataId)
    {

        if(!isset($ProfileId) || empty($ProfileId)){
            return false;
        }
        if(!isset($DataId) || empty($DataId)){
            return false;
        }
        return $this->select(array('ProfileId' => $ProfileId, 'DataId' => $DataId, 'Status' => array(0,1)))->current();

    }

    public function getByProfileIdAndId($ProfileId, $Id)
    {

        if(!isset($ProfileId) || empty($ProfileId)){
            return false;
        }
        if(!isset($Id) || empty($Id)){
            return false;
        }
        return $this->select(array('ProfileId' => $ProfileId, 'Id' => $Id, 'Status' => array(0,1)))->current();

    }

    public function getAllDataByProfileId($ProfileId)
    {
        if(!isset($ProfileId) || empty($ProfileId)){
            return false;
        }

        return $this->select(array('ProfileId' => $ProfileId, 'Status' => array(0,1)));
    }
}
