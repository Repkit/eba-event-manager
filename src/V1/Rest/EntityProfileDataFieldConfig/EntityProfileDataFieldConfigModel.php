<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileDataFieldConfig;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EntityProfileDataFieldConfigModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EntityProfileDataFieldConfig\EntityProfileDataFieldConfigEntity';
	protected $TableName 	= 'entity_profile_data_field_config';

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

    /**
     * Logical deletion
     *
     * @param  Where|\Closure|string|array $where
     * @return int
     */
    public function delete($Where)
    {
        return $this->update(array('Status' => 99), $Where);
    }
}
