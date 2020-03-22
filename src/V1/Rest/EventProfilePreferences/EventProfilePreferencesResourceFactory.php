<?php
namespace MicroIceEventManager\V1\Rest\EventProfilePreferences;

class EventProfilePreferencesResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EventProfilePreferences\EventProfilePreferencesModel');
		$profilesModel = $services->get('MicroIceEventManager\V1\Rest\EventProfiles\EventProfilesModel');

        return new EventProfilePreferencesResource($model, $profilesModel);
    }
}
