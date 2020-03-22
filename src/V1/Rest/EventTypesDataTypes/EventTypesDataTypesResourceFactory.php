<?php
namespace MicroIceEventManager\V1\Rest\EventTypesDataTypes;

class EventTypesDataTypesResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EventTypesDataTypes\EventTypesDataTypesModel');
    	
        return new EventTypesDataTypesResource($model);
    }
}
