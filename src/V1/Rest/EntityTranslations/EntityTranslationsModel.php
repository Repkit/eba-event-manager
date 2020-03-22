<?php

namespace MicroIceEventManager\V1\Rest\EntityTranslations;

use MicroIceEventManager\V1\Rest\AbstractModel as Model;

class EntityTranslationsModel extends Model
{
	protected $EntityClass 	= 'MicroIceEventManager\V1\Rest\EntityTranslations\EntityTranslationsEntity';
	protected $TableName 	= 'entity_translations';

	public function __construct(\Zend\Db\Adapter\AdapterInterface $Adapter, $Options = null)
    {
        $this->PrimaryKey = 'Id';
        $this->AutoIncrementColumns = array('Id');
        parent::__construct($Adapter, $Options);
    }

    public function getByEntityIdAndLanguage($EntityId, $LangCode)
    {

    	if(!isset($EntityId) || empty($EntityId)){
    		return false;
    	}

    	// if(!isset($LangCode) || empty($LangCode)){
    	// 	return false;
    	// }

    	return $this->select(array('EntityId' => $EntityId, 'Language' => $LangCode))->current();

    }

    public function getByLanguageAndEntityId($LangCode, $EntityId)
    {
    	return $this->getByEntityIdAndLanguage($EntityId, $LangCode);
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
