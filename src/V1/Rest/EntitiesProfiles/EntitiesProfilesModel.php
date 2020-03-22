<?php

namespace MicroIceEventManager\V1\Rest\EntitiesProfiles;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EntitiesProfilesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EntitiesProfiles\EntitiesProfilesEntity';
	protected $TableName 	= 'entities_profiles';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function getByEntityIdAndProfileId($EntityId, $ProfileId)
    {

        if(!isset($EntityId) || empty($EntityId)){
            return false;
        }
        if(!isset($ProfileId) || empty($ProfileId)){
            return false;
        }
        return $this->select(array('EntityId' => $EntityId, 'ProfileId' => $ProfileId, 'Status' => array(0,1)))->current();

    }

    public function getAllProfilesByEntityId($EntityId)
    {
        if(!isset($EntityId) || empty($EntityId)){
            return false;
        }

        return $this->select(array('EntityId' => $EntityId, 'Status' => array(0,1)));
    }

    public function getAllEntitiesByProfileId($ProfileId)
    {
        if( !isset($ProfileId) || empty($ProfileId) )
        {
            return false;
        }

        return $this->select(array('ProfileId' => $ProfileId, 'Status' => array(0,1)));
    }
}
