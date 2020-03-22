<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileDataPreferences;

class EntityProfileDataPreferencesResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDataPreferences\EntityProfileDataPreferencesModel');
        $dataModel = $services->get('MicroIceEventManager\V1\Rest\EntityProfileData\EntityProfileDataModel');

        return new EntityProfileDataPreferencesResource($model, $dataModel);
    }
}
