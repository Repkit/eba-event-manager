<?php
namespace MicroIceEventManager\V1\Rest\EventData;

class EventDataResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EventData\EventDataModel');
    	$dataFields = $services->get('MicroIceEventManager\V1\Rest\EventDataFields\EventDataFieldsModel');
    	$dataFieldConfig = $services->get('MicroIceEventManager\V1\Rest\EventDataFieldConfig\EventDataFieldConfigModel');
    	$dataType = $services->get('MicroIceEventManager\V1\Rest\EventDataTypes\EventDataTypesModel');
    	$eventsData = $services->get('MicroIceEventManager\V1\Rest\EventsData\EventsDataModel');
    	$events = $services->get('MicroIceEventManager\V1\Rest\Events\EventsModel');
        // $preferences = $services->get('MicroIceEventManager\V1\Rest\EventDataPreferences\EventDataPreferencesModel');
        $preferences = null; // not used yet

        return new EventDataResource($model, $dataFields, $dataFieldConfig, $dataType, $eventsData, $events, $preferences);
    }
}
