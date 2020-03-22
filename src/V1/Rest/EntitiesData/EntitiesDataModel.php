<?php

namespace MicroIceEventManager\V1\Rest\EntitiesData;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EntitiesDataModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EntitiesData\EntitiesDataEntity';
	protected $TableName 	= 'entities_data';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function getByEntityIdAndDataId($EntityId, $DataId)
    {

        if(!isset($EntityId) || empty($EntityId)){
            return false;
        }
        if(!isset($DataId) || empty($DataId)){
            return false;
        }
        return $this->select(array('EntityId' => $EntityId, 'DataId' => $DataId, 'Status' => array(0,1)))->current();

    }

    public function getByEntityIdAndId($EntityId, $Id)
    {

        if(!isset($EntityId) || empty($EntityId)){
            return false;
        }
        if(!isset($Id) || empty($Id)){
            return false;
        }
        return $this->select(array('EntityId' => $EntityId, 'Id' => $Id, 'Status' => array(0,1)))->current();

    }

    public function getAllDataByEntityId($EntityId)
    {
        if(!isset($EntityId) || empty($EntityId)){
            return false;
        }

        return $this->select(array('EntityId' => $EntityId, 'Status' => array(0,1)));
    }
}
