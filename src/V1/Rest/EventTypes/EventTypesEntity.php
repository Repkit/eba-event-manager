<?php
namespace MicroIceEventManager\V1\Rest\EventTypes;

use MicroIceEventManager\V1\Rest\AbstractEntity as Entity;

class EventTypesEntity extends Entity
{
	protected $_data = array (
      'Id' => null,
      'ParentId' => 0,
      'CreationDate' => null,
      'Status' => null,
    );
}