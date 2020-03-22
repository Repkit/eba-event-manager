<?php
namespace MicroIceEventManager\V1\Rest\EventProfileDataTypes;

class EventProfileDataTypesResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EventProfileDataTypes\EventProfileDataTypesModel');

        return new EventProfileDataTypesResource($model);
    }
}
