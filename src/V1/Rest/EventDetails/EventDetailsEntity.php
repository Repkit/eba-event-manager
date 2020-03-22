<?php

namespace MicroIceEventManager\V1\Rest\EventDetails;

use MicroIceEventManager\V1\Rest\AbstractEntity as Entity;

class EventDetailsEntity extends Entity
{
	protected $_data = array (
      'Id' => null,
      'EventId' => null,
      'Field' => null,
      'Value' => null,
      'Category' => null,
      'TypeId' => null,
      'Status' => null,
    );
}