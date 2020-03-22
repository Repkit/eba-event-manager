<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileDataFieldConfig;

class EntityProfileDataFieldConfigResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDataFieldConfig\EntityProfileDataFieldConfigModel');
        $dataType = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDataTypes\EntityProfileDataTypesModel');

        return new EntityProfileDataFieldConfigResource($model, $dataType);
    }
}
