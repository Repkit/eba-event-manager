<?php

namespace MicroIceEventManager\V1\Rest\EventProfileDetails;

use MicroIceEventManager\V1\Rest\AbstractEntity as Entity;

class EventProfileDetailsEntity extends Entity
{
	protected $_data = array (
      'Id' => null,
      'ProfileId' => null,
      'Field' => null,
      'Value' => null,
      'Category' => null,
      'TypeId' => null,
      'Status' => null,
    );
}