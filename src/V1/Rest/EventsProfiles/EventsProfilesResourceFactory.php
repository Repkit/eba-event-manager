<?php
namespace MicroIceEventManager\V1\Rest\EventsProfiles;

class EventsProfilesResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EventsProfiles\EventsProfilesModel');
    	$profile = $services->get('MicroIceEventManager\V1\Rest\EventProfiles\EventProfilesModel');
    	
        return new EventsProfilesResource($model, $profile);
    }
}
