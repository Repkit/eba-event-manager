<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileDetails;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EntityProfileDetailsModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EntityProfileDetails\EntityProfileDetailsEntity';
	protected $TableName 	= 'entity_profile_details';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        $this->AutoIncrementColumns = array('Id');
        $this->ColumnNames = array('ProfileId', 'Field', 'Value', 'Category', 'TypeId', 'Status');
        parent::__construct($Adapter, $Options);
    }

    public function getAllByTypeId($TypeId)
    {
        if(!isset($TypeId) || empty($TypeId)){
            return false;
        }
        return $this->select(array('TypeId' => $TypeId));
    }

    public function getAllByProfileId($ProfileId)
    {
        if(!isset($ProfileId) || empty($ProfileId)){
            return false;
        }
        return $this->select(array('ProfileId' => $ProfileId));
    }

    public function getAllByProfileIdAndTypeId($ProfileId, $TypeId)
    {
        if(!isset($ProfileId) || empty($ProfileId)){
            return false;
        }
        if(!isset($TypeId) || empty($TypeId)){
            return false;
        }
        return $this->select(array('ProfileId' => $ProfileId, 'TypeId' => $TypeId));
    }
}