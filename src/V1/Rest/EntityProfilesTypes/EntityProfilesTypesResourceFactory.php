<?php
namespace MicroIceEventManager\V1\Rest\EntityProfilesTypes;

class EntityProfilesTypesResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EntityProfilesTypes\EntityProfilesTypesModel');
        $profile = $services->get('MicroIceEventManager\V1\Rest\EntityProfiles\EntityProfilesModel');
        $type = $services->get('MicroIceEventManager\V1\Rest\EntityProfileTypes\EntityProfileTypesModel');

        return new EntityProfilesTypesResource($model, $profile, $type);
    }
}
