<?php
namespace MicroIceEventManager\V1\Rest\EventDataPreferences;

use MicroIceEventManager\V1\Rest\AbstractEntity as Entity;

class EventDataPreferencesEntity extends Entity
{
	protected $_data = array (
      'Id' => null,
      'DataId' => null,
      'Category' => null,
      'Content' => null,
      'ContentType' => null,
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