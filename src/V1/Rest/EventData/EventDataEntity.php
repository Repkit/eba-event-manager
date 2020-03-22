<?php

namespace MicroIceEventManager\V1\Rest\EventData;

use MicroIceEventManager\V1\Rest\AbstractEntity as Entity;

class EventDataEntity extends Entity
{
	protected $_data = array (
	    'Id' => null,
	    'Name' => null,
	    'TypeId' => null,
	    'CreationDate' => null,
	    'Status' => null,
	);
}
