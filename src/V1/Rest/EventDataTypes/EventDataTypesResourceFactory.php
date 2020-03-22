<?php
namespace MicroIceEventManager\V1\Rest\EventDataTypes;

class EventDataTypesResourceFactory
{
    public function __invoke($services)
    {

        $model                   = $services->get('MicroIceEventManager\V1\Rest\EventDataTypes\EventDataTypesModel');
        $eventData               = $services->get('MicroIceEventManager\V1\Rest\EventData\EventDataModel');
        $eventDataFields         = $services->get('MicroIceEventManager\V1\Rest\EventDataFields\EventDataFieldsModel');
        $eventDataFieldsConfig   = $services->get('MicroIceEventManager\V1\Rest\EventDataFieldConfig\EventDataFieldConfigModel');
        $eventsData              = $services->get('MicroIceEventManager\V1\Rest\EventsData\EventsDataModel');
        // $eventDataPreferences = $services->get('MicroIceEventManager\V1\Rest\EventDataPreferences\EventDataPreferencesModel');
        $eventDataPreferences    = null;

        return new EventDataTypesResource($model, $eventData, $eventDataFields, $eventDataFieldsConfig, $eventsData, $eventDataPreferences);
    
    }
}
