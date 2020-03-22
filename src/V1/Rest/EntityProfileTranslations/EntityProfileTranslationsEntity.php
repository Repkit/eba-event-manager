<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileTranslations;

use MicroIceEventManager\V1\Rest\AbstractEntity as Entity;

class EntityProfileTranslationsEntity extends Entity
{
    protected $_data = array (
        'Id' => null,
        'ProfileId' => null,
        'Name' => null,
        'Language' => null,
        'CreationDate' => null,
        'Status' => null,
    );

    public function validate()
    {
        $lang = $this->_data['Language'];
        if(null != $lang){
            $pattern = '/^[a-z]{2}$/';
            if(!preg_match($pattern, $lang)){
                throw new \InvalidArgumentException("Invalid language code", 1);
            }
        }

        return true;
    }
}
