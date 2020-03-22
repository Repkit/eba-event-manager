<?php

namespace MicroIceEventManager\V1\Rest\EventDataFieldConfig;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EventDataFieldConfigModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EventDataFieldConfig\EventDataFieldConfigEntity';
	protected $TableName 	= 'event_data_field_config';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    public function getByIdAndTypeId($Id, $TypeId)
    {

        if(!isset($Id) || empty($Id)){
            return false;
        }
        if(!isset($TypeId) || empty($TypeId)){
            return false;
        }
        return $this->select(array('Id' => $Id ,'TypeId' => $TypeId))->current();

    }

    public function getAllByTypeId($TypeId)
    {

        if(!isset($TypeId) || empty($TypeId)){
            return false;
        }
        return $this->select(array('TypeId' => $TypeId));

    }
}
