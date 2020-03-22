<?php

namespace MicroIceEventManager\V1\Rest\EntityDetails;

use MicroIceEventManager\V1\Rest\AbstractEntity as Entity;

class EntityDetailsEntity extends Entity
{
	protected $_data = array (
      'Id' => null,
      'EntityId' => null,
      'Field' => null,
      'Value' => null,
      'Category' => null,
      'TypeId' => null,
      'Status' => null,
    );
}