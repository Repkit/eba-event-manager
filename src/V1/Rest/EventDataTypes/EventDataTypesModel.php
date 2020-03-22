<?php

namespace MicroIceEventManager\V1\Rest\EventDataTypes;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EventDataTypesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EventDataTypes\EventDataTypesEntity';
	protected $TableName 	= 'event_data_types';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }
}
