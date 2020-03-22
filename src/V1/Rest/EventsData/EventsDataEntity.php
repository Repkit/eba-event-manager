<?php
namespace MicroIceEventManager\V1\Rest\EventsData;

use MicroIceEventManager\V1\Rest\AbstractEntity as Entity;

class EventsDataEntity extends Entity
{
	protected $_data = array (
      'Id' => null,
      'EventId' => null,
      'DataId' => null,
      'Status' => null,
    );
}
