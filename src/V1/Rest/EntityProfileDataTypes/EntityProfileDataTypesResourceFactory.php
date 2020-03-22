<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileDataTypes;

class EntityProfileDataTypesResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDataTypes\EntityProfileDataTypesModel');

        return new EntityProfileDataTypesResource($model);
    }
}
