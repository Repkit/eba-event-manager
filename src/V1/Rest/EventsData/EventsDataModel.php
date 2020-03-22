<?php

namespace MicroIceEventManager\V1\Rest\EventsData;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EventsDataModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EventsData\EventsDataEntity';
	protected $TableName 	= 'events_data';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function getByEventIdAndDataId($EventId, $DataId)
    {

        if(!isset($EventId) || empty($EventId)){
            return false;
        }
        if(!isset($DataId) || empty($DataId)){
            return false;
        }
        return $this->select(array('EventId' => $EventId, 'DataId' => $DataId, 'Status' => array(0,1)))->current();

    }

    public function getByEventIdAndId($EventId, $Id)
    {

        if(!isset($EventId) || empty($EventId)){
            return false;
        }
        if(!isset($Id) || empty($Id)){
            return false;
        }
        return $this->select(array('EventId' => $EventId, 'Id' => $Id, 'Status' => array(0,1)))->current();

    }

    public function getAllDataByEventId($EventId)
    {
        if(!isset($EventId) || empty($EventId)){
            return false;
        }

        return $this->select(array('EventId' => $EventId, 'Status' => array(0,1)));
    }
}
