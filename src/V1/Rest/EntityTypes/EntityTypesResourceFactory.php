<?php
namespace MicroIceEventManager\V1\Rest\EntityTypes;

class EntityTypesResourceFactory
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
    	
        $model 	= $services->get('MicroIceEventManager\V1\Rest\EntityTypes\EntityTypesModel');

        $translations = $services->get('MicroIceEventManager\V1\Rest\EntityTypeTranslations\EntityTypeTranslationsModel');

        return new EntityTypesResource($model, $translations, $settings);
    
    }
}