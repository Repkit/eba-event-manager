<?php
namespace MicroIceEventManager\V1\Rest\Entities;

class EntitiesResourceFactory
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

        $model = $services->get('MicroIceEventManager\V1\Rest\Entities\EntitiesModel');
		$entitiesTypes = $services->get('MicroIceEventManager\V1\Rest\EntitiesTypes\EntitiesTypesModel');
        $entityDetails = $services->get('MicroIceEventManager\V1\Rest\EntityDetails\EntityDetailsModel');
        $entityDetailFields = $services->get('MicroIceEventManager\V1\Rest\EntityDetailFields\EntityDetailFieldsModel');
        $entityDatas = $services->get('MicroIceEventManager\V1\Rest\EntityData\EntityDataModel');
        $dataFields = $services->get('MicroIceEventManager\V1\Rest\EntityDataFields\EntityDataFieldsModel');
        $dataFieldConfig = $services->get('MicroIceEventManager\V1\Rest\EntityDataFieldConfig\EntityDataFieldConfigModel');
        $entityTypeDataType = $services->get('MicroIceEventManager\V1\Rest\EntityTypesDataTypes\EntityTypesDataTypesModel');
        $entityTranslations = $services->get('MicroIceEventManager\V1\Rest\EntityTranslations\EntityTranslationsModel');

        return new EntitiesResource($model, $entitiesTypes, $entityDetails, $entityDetailFields, $entityDatas, $dataFields, $dataFieldConfig, $entityTypeDataType, $entityTranslations, $settings);
    }
}
