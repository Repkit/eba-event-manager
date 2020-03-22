<?php
namespace MicroIceEventManager\V1\Rest\EventsEntities;

class EventsEntitiesResourceFactory
{
    public function __invoke($services)
    {

    	$model = $services->get('MicroIceEventManager\V1\Rest\EventsEntities\EventsEntitiesModel');
        $events = $services->get('MicroIceEventManager\V1\Rest\Events\EventsModel');
        $entities = $services->get('MicroIceEventManager\V1\Rest\Entities\EntitiesModel');

        return new EventsEntitiesResource($model, $events, $entities);
    }
}
