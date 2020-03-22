<?php
namespace MicroIceEventManager\V1\Rest\EventProfileDataPreferences;

class EventProfileDataPreferencesResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EventProfileDataPreferences\EventProfileDataPreferencesModel');
        $dataModel = $services->get('MicroIceEventManager\V1\Rest\EventProfileData\EventProfileDataModel');

        return new EventProfileDataPreferencesResource($model, $dataModel);
    }
}
