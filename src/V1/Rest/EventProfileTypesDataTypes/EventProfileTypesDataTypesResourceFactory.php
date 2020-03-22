<?php
namespace MicroIceEventManager\V1\Rest\EventProfileTypesDataTypes;

class EventProfileTypesDataTypesResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EventProfileTypesDataTypes\EventProfileTypesDataTypesModel');

        return new EventProfileTypesDataTypesResource($model);
    }
}
