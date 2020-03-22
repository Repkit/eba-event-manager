<?php
namespace MicroIceEventManager\V1\Rest\EventsTypes;

class EventsTypesResourceFactory
{
    public function __invoke($services)
    {

        $model = $services->get('MicroIceEventManager\V1\Rest\EventsTypes\EventsTypesModel');
        $events = $services->get('MicroIceEventManager\V1\Rest\Events\EventsModel');
        
        return new EventsTypesResource($model, $events);
    }
}
