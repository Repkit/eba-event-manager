<?php
namespace MicroIceEventManager\V1\Rest\EventsEntities;

use MicroIceEventManager\V1\Rest\AbstractEntity as Entity;

class EventsEntitiesEntity extends Entity
{
	protected $_data = array (
      'Id' => null,
      'EventId' => null,
      'EntityId' => null,
      'Status' => 1,
    );

    public function toArray()
    {
      $entityTypeId = $this->_data['EntityTypeId'];
      if(!empty($entityTypeId)){
        $this->_data['EntityTypeId'] = explode(',', $entityTypeId);
      }
      
      return $this->_data;
    }

    public function exchangeArray(array $Input)
    {
    	$this->_data = $Input;
    }
}