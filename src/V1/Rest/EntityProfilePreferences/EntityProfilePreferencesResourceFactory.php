<?php
namespace MicroIceEventManager\V1\Rest\EntityProfilePreferences;

class EntityProfilePreferencesResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EntityProfilePreferences\EntityProfilePreferencesModel');
		$profilesModel = $services->get('MicroIceEventManager\V1\Rest\EntityProfiles\EntityProfilesModel');

        return new EntityProfilePreferencesResource($model, $profilesModel);
    }
}
