<?php
namespace MicroIceEventManager\V1\Rest\EntityTypesDataTypes;

class EntityTypesDataTypesResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EntityTypesDataTypes\EntityTypesDataTypesModel');
    	
        return new EntityTypesDataTypesResource($model);
    }
}
