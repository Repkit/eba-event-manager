<?php
namespace MicroIceEventManager\V1\Rest\EventProfilesTypes;

class EventProfilesTypesResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EventProfilesTypes\EventProfilesTypesModel');
        $profile = $services->get('MicroIceEventManager\V1\Rest\EventProfiles\EventProfilesModel');
        $type = $services->get('MicroIceEventManager\V1\Rest\EventProfileTypes\EventProfileTypesModel');

        return new EventProfilesTypesResource($model, $profile, $type);
    }
}
