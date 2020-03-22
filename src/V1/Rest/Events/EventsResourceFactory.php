<?php
namespace MicroIceEventManager\V1\Rest\Events;

class EventsResourceFactory
{
    public function __invoke($services)
    {
        // get config
    	$config = $services->get('config');

    	// get event manager settings 
    	$settings = array();
    	if(!empty($config['event_manager_settings'])){
    		$settings = $config['event_manager_settings'];
    	}

        // get config page size for this service
        // $settings['page_size'] = $config['zf-rest'][__NAMESPACE__ .'\Controller']['page_size'];

        $model = $services->get('MicroIceEventManager\V1\Rest\Events\EventsModel');
        $eventsTypes = $services->get('MicroIceEventManager\V1\Rest\EventsTypes\EventsTypesModel');
        $eventDetails = $services->get('MicroIceEventManager\V1\Rest\EventDetails\EventDetailsModel');
        $eventDetailFields = $services->get('MicroIceEventManager\V1\Rest\EventDetailFields\EventDetailFieldsModel');
        $eventDatas = $services->get('MicroIceEventManager\V1\Rest\EventData\EventDataModel');
        $dataFields = $services->get('MicroIceEventManager\V1\Rest\EventDataFields\EventDataFieldsModel');
        $dataFieldConfig = $services->get('MicroIceEventManager\V1\Rest\EventDataFieldConfig\EventDataFieldConfigModel');
        $eventTypeDataType = $services->get('MicroIceEventManager\V1\Rest\EventTypesDataTypes\EventTypesDataTypesModel');
        $eventTranslations = $services->get('MicroIceEventManager\V1\Rest\EventTranslations\EventTranslationsModel');

        return new EventsResource($model, $eventsTypes, $eventDetails, $eventDetailFields, $eventDatas, $dataFields, $dataFieldConfig, $eventTypeDataType, $eventTranslations, $settings);
    }
}
