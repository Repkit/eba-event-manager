<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileDataFields;

class EntityProfileDataFieldsResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDataFields\EntityProfileDataFieldsModel');
        $fieldCfg = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDataFieldConfig\EntityProfileDataFieldConfigModel');
        $profileData = $services->get('MicroIceEventManager\V1\Rest\EntityProfileData\EntityProfileDataModel');

        return new EntityProfileDataFieldsResource($model, $fieldCfg, $profileData);
    }
}
