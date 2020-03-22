<?php
namespace MicroIceEventManager\V1\Rest\EntitiesTypes;

class EntitiesTypesResourceFactory
{
    public function __invoke($services)
    {
		$model = $services->get('MicroIceEventManager\V1\Rest\EntitiesTypes\EntitiesTypesModel');
        $entities = $services->get('MicroIceEventManager\V1\Rest\Entities\EntitiesModel');
        
        return new EntitiesTypesResource($model, $entities);
    }
}
