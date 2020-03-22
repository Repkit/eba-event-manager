<?php
namespace MicroIceEventManager\V1\Rest\EntitiesData;

class EntitiesDataResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EntitiesData\EntitiesDataModel');
    	$data = $services->get('MicroIceEventManager\V1\Rest\EntityData\EntityDataModel');
    	
        return new EntitiesDataResource($model, $data);
    }
}
