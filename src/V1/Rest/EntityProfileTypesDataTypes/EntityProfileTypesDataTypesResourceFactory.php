<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileTypesDataTypes;

class EntityProfileTypesDataTypesResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EntityProfileTypesDataTypes\EntityProfileTypesDataTypesModel');

        return new EntityProfileTypesDataTypesResource($model);
    }
}
