<?php
namespace MicroIceEventManager\V1\Rest\EventsData;

class EventsDataResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EventsData\EventsDataModel');
    	$data = $services->get('MicroIceEventManager\V1\Rest\EventData\EventDataModel');
    	
        return new EventsDataResource($model, $data);
    }
}
