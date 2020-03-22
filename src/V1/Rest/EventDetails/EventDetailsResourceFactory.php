<?php
namespace MicroIceEventManager\V1\Rest\EventDetails;

class EventDetailsResourceFactory
{
    public function __invoke($services)
    {
    	// get config
    	// $config = $services->get('config');

        $model = $services->get('MicroIceEventManager\V1\Rest\EventDetails\EventDetailsModel');
        $modelFields = $services->get('MicroIceEventManager\V1\Rest\EventDetailFields\EventDetailFieldsModel');
        $types = $services->get('MicroIceEventManager\V1\Rest\EventTypes\EventTypesModel');
        $events = $services->get('MicroIceEventManager\V1\Rest\Events\EventsModel');
        $eventsTypes = $services->get('MicroIceEventManager\V1\Rest\EventsTypes\EventsTypesModel');

        return new EventDetailsResource($model, $modelFields, $types, $events, $eventsTypes);
    }
}
