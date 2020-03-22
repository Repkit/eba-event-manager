<?php

namespace MicroIceEventManager\V1\Rest\EventsProfiles;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EventsProfilesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EventsProfiles\EventsProfilesEntity';
	protected $TableName 	= 'events_profiles';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function getByEventIdAndProfileId($EventId, $ProfileId)
    {

        if( !isset($EventId) || empty($EventId) )
        {
            return false;
        }
        if( !isset($ProfileId) || empty($ProfileId) )
        {
            return false;
        }
        return $this->select(array('EventId' => $EventId, 'ProfileId' => $ProfileId, 'Status' => array(0,1)))->current();

    }

    public function getAllProfilesByEventId($EventId)
    {
        if( !isset($EventId) || empty($EventId) )
        {
            return false;
        }

        return $this->select(array('EventId' => $EventId, 'Status' => array(0,1)));
    }

    public function getAllEventsByProfileId($ProfileId)
    {
        if( !isset($ProfileId) || empty($ProfileId) )
        {
            return false;
        }

        return $this->select(array('ProfileId' => $ProfileId, 'Status' => array(0,1)));
    }
}
