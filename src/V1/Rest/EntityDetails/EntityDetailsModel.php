<?php
namespace MicroIceEventManager\V1\Rest\EntityDetails;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EntityDetailsModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EntityDetails\EntityDetailsEntity';
	protected $TableName 	= 'entity_details';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        $this->AutoIncrementColumns = array('Id');
        $this->ColumnNames = array('EntityId', 'Field', 'Value', 'Category', 'TypeId', 'Status');
        parent::__construct($Adapter, $Options);
    }

    public function getAllByTypeId($TypeId)
    {

        if(!isset($TypeId) || empty($TypeId)){
            return false;
        }
        return $this->select(array('TypeId' => $TypeId));

    }

    public function getAllByEntityId($EntityId)
    {

        if(!isset($EntityId) || empty($EntityId)){
            return false;
        }
        return $this->select(array('EntityId' => $EntityId));

    }

    public function getAllByEntityIdAndTypeId($EntityId, $TypeId)
    {

        if(!isset($EntityId) || empty($EntityId)){
            return false;
        }
        if(!isset($TypeId) || empty($TypeId)){
            return false;
        }
        return $this->select(array('EntityId' => $EntityId, 'TypeId' => $TypeId));

    }
}
