<?php
namespace MicroIceEventManager\V1\Rest\EventProfileDataTypes;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EventProfileDataTypesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EventProfileDataTypes\EventProfileDataTypesEntity';
	protected $TableName 	= 'event_profile_data_types';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        parent::__construct($Adapter, $Options);
    }

    /**
     * Logical deletion
     *
     * @param  Where|\Closure|string|array $where
     * @return int
     */
    public function delete($Where)
    {
        return $this->update(array('Status' => 99), $Where);
    }
}
