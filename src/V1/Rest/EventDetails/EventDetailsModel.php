<?php
namespace MicroIceEventManager\V1\Rest\EventDetails;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EventDetailsModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EventDetails\EventDetailsEntity';
	protected $TableName 	= 'event_details';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        $this->AutoIncrementColumns = array('Id');
        $this->ColumnNames = array('EventId', 'Field', 'Value', 'Category', 'TypeId', 'Status');
        parent::__construct($Adapter, $Options);
    }

    public function getAllByTypeId($TypeId)
    {

        if(!isset($TypeId) || empty($TypeId)){
            return false;
        }
        return $this->select(array('TypeId' => $TypeId));

    }

    public function getAllByEventId($EventId)
    {

        if(!isset($EventId) || empty($EventId)){
            return false;
        }
        return $this->select(array('EventId' => $EventId));

    }

    public function getAllByEventIdAndTypeId($EventId, $TypeId)
    {

        if(!isset($EventId) || empty($EventId)){
            return false;
        }
        if(!isset($TypeId) || empty($TypeId)){
            return false;
        }
        return $this->select(array('EventId' => $EventId, 'TypeId' => $TypeId));

    }
}
