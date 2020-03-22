<?php

namespace MicroIceEventManager\V1\Rest\EntityDataPreferences;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EntityDataPreferencesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EntityDataPreferences\EntityDataPreferencesEntity';
	protected $TableName 	= 'entity_data_preferences';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        $this->AutoIncrementColumns = array('Id');
        parent::__construct($Adapter, $Options);
    }

    public function getByDataIdAndCategory($DataId, $Category)
    {
    	if(!isset($DataId) || empty($DataId)){
            return false;
        }
        if(!isset($Category) || empty($Category)){
            return false;
        }
        return $this->select(array('DataId' => $DataId ,'Category' => $Category))->current();
    }

    public function getAllByDataId($DataId)
    {

        if(!isset($DataId) || empty($DataId)){
            return false;
        }
        return $this->select(array('DataId' => $DataId));

    }

    public function delete($Where)
    {
        return $this->update(array('Status' => 99), $Where);
    }
}
