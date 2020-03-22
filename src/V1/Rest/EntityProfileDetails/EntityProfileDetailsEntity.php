<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileDetails;

use MicroIceEventManager\V1\Rest\AbstractEntity as Entity;

class EntityProfileDetailsEntity extends Entity
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