<?php
namespace MicroIceEventManager\V1\Rest\EventsTypes;

use MicroIceEventManager\V1\Rest\AbstractEntity as Entity;

class EventsTypesEntity extends Entity
{
	protected $_data = array (
      'Id' => null,
      'EventId' => null,
      'TypeId' => null,
      'Status' => 1,
    );
}