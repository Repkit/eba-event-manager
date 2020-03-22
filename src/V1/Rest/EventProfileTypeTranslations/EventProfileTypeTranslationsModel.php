<?php

namespace MicroIceEventManager\V1\Rest\EventProfileTypeTranslations;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EventProfileTypeTranslationsModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EventProfileTypeTranslations\EventProfileTypeTranslationsEntity';
	protected $TableName 	= 'event_profile_type_translations';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        $this->AutoIncrementColumns = array('Id');
        parent::__construct($Adapter, $Options);
    }

    public function getByTypeIdAndLanguage($TypeId, $LangCode)
    {

    	if(!isset($TypeId) || empty($TypeId)){
    		return false;
    	}

    	return $this->select(array('TypeId' => $TypeId, 'Language' => $LangCode))->current();

    }

    public function getByLanguageAndTypeId($LangCode, $TypeId)
    {
    	return $this->getByTypeIdAndLanguage($TypeId, $LangCode);
    }

    public function getByNameAndStatusAndLanguage($Name, $Status, $LangCode = null)
    {
        if(!isset($Name) || empty($Name)){
            throw new \InvalidArgumentException("Name is mandatory", 1);
        }

        if(!isset($Status) || empty($Status)){
            throw new \InvalidArgumentException("Status is mandatory", 1);
        }


        return $this->select(array('Name' => $Name, 'Status' => $Status, 'Language' => $LangCode))->current();
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
