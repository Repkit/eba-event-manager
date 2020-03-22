<?php
namespace MicroIceEventManager\V1\Rest\EventDetailFields;

class EventDetailFieldsResourceFactory
{
    public function __invoke($services)
    {
    	// get config
    	// $config = $services->get('config');

        $model = $services->get('MicroIceEventManager\V1\Rest\EventDetailFields\EventDetailFieldsModel');
        $types = $services->get('MicroIceEventManager\V1\Rest\EventTypes\EventTypesModel');

        
        return new EventDetailFieldsResource($model ,$types);
    }
}
