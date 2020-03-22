<?php
namespace MicroIceEventManager\V1\Rest\EventTypes;

class EventTypesResourceFactory
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

        $model 	= $services->get('MicroIceEventManager\V1\Rest\EventTypes\EventTypesModel');

        $translations = $services->get('MicroIceEventManager\V1\Rest\EventTypeTranslations\EventTypeTranslationsModel');

        return new EventTypesResource($model, $translations, $settings);
    
    }
}