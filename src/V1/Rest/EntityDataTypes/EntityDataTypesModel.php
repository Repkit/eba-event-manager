<?php

namespace MicroIceEventManager\V1\Rest\EntityDataTypes;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EntityDataTypesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EntityDataTypes\EntityDataTypesEntity';
	protected $TableName 	= 'entity_data_types';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }
}