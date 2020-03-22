<?php
namespace MicroIceEventManager\V1\Rest\EventProfileDataFields;

class EventProfileDataFieldsResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EventProfileDataFields\EventProfileDataFieldsModel');
        $fieldCfg = $services->get('MicroIceEventManager\V1\Rest\EventProfileDataFieldConfig\EventProfileDataFieldConfigModel');
        $profileData = $services->get('MicroIceEventManager\V1\Rest\EventProfileData\EventProfileDataModel');

        return new EventProfileDataFieldsResource($model, $fieldCfg, $profileData);
    }
}
