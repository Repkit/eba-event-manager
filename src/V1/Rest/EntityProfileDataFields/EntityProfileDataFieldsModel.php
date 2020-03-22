<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileDataFields;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EntityProfileDataFieldsModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EntityProfileDataFields\EntityProfileDataFieldsEntity';
	protected $TableName 	= 'entity_profile_data_fields';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        $this->AutoIncrementColumns = array('Id');
        $this->ColumnNames = array('Field', 'Value', 'DataId');
        parent::__construct($Adapter, $Options);
    }

    public function getAllByDataId($DataId)
    {

        if(!isset($DataId) || empty($DataId)){
            return false;
        }
        return $this->select(array('DataId' => $DataId));

    }

    public function getByIdAndDataId($Id, $DataId)
    {
        if(!isset($Id) || empty($Id)){
            return false;
        }
        if(!isset($DataId) || empty($DataId)){
            return false;
        }
        return $this->select(array('Id' => $Id, 'DataId' => $DataId))->current();
    }

    public function getByDataIdAndId($DataId, $Id)
    {
        return $this->getByIdAndDataId($Id, $DataId);
    }

    public function fetchAllByDataId($DataId)
    {
        return $this->getAllByDataId($DataId);
    }
}
