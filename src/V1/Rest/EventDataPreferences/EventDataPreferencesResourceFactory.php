<?php
namespace MicroIceEventManager\V1\Rest\EventDataPreferences;

class EventDataPreferencesResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EventDataPreferences\EventDataPreferencesModel');
		$dataModel = $services->get('MicroIceEventManager\V1\Rest\EventData\EventDataModel');

        return new EventDataPreferencesResource($model, $dataModel);
    }
}
