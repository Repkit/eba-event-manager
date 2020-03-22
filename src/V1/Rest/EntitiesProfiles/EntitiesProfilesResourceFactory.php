<?php
namespace MicroIceEventManager\V1\Rest\EntitiesProfiles;

class EntitiesProfilesResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EntitiesProfiles\EntitiesProfilesModel');
    	$profile = $services->get('MicroIceEventManager\V1\Rest\EntityProfiles\EntityProfilesModel');
    	
        return new EntitiesProfilesResource($model, $profile);
    }
}
