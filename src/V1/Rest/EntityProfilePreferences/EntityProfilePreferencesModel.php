<?php
namespace MicroIceEventManager\V1\Rest\EntityProfilePreferences;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EntityProfilePreferencesModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EntityProfilePreferences\EntityProfilePreferencesEntity';
	protected $TableName 	= 'entity_profile_preferences';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        $this->AutoIncrementColumns = array('Id');
        parent::__construct($Adapter, $Options);
    }

    public function getByProfileIdAndCategory($ProfileId, $Category)
    {
    	if(!isset($ProfileId) || empty($ProfileId)){
            return false;
        }
        if(!isset($Category) || empty($Category)){
            return false;
        }
        return $this->select(array('ProfileId' => $ProfileId ,'Category' => $Category))->current();
    }

    public function getAllByProfileId($ProfileId)
    {
        if(!isset($ProfileId) || empty($ProfileId)){
            return false;
        }
        return $this->select(array('ProfileId' => $ProfileId));
    }

    public function delete($Where)
    {
        return $this->update(array('Status' => 99), $Where);
    }
}
