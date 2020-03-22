<?php
namespace MicroIceEventManager\V1\Rest\EventProfileData;

use MicroIceEventManager\V1\Rest\AbstractEntity as Entity;

class EventProfileDataEntity extends Entity
{
    protected $_data = array (
        'Id' => null,
        'Name' => null,
        'TypeId' => null,
        'CreationDate' => null,
        'Status' => null,
    );
}
