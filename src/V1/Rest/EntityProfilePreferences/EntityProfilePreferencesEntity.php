<?php
namespace MicroIceEventManager\V1\Rest\EntityProfilePreferences;

use MicroIceEventManager\V1\Rest\AbstractEntity as Entity;

class EntityProfilePreferencesEntity extends Entity
{
	protected $_data = array (
      'Id' => null,
      'ProfileId' => null,
      'Category' => null,
      'Content' => null,
      'ContentType' => null,
      'CreationDate' => null,
      'Status' => null,
    );

    public function getExtendedData()
    {
        $data = $this->toArray();

        switch ($data['ContentType'])
        {
            case 'json':
                $data['Content'] = json_decode($data['Content']);
                break;
            case 'serialize':
                $data['Content'] = unserialize($data['Content']);
                break;
        }

        return $data;
    }
}
