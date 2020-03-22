<?php
namespace MicroIceEventManager\V1\Rest\EventProfileDataFieldConfig;

class EventProfileDataFieldConfigResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EventProfileDataFieldConfig\EventProfileDataFieldConfigModel');
        $dataType = $services->get('MicroIceEventManager\V1\Rest\EventProfileDataTypes\EventProfileDataTypesModel');

        return new EventProfileDataFieldConfigResource($model, $dataType);
    }
}
