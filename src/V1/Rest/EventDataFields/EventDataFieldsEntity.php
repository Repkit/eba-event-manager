<?php

namespace MicroIceEventManager\V1\Rest\EventDataFields;

use MicroIceEventManager\V1\Rest\AbstractEntity as Entity;

class EventDataFieldsEntity extends Entity
{
	protected $_data = array (
      'Id' => null,
      'Field' => null,
      'Value' => null,
      'DataId' => null,
      'Status' => null,
    );
}
