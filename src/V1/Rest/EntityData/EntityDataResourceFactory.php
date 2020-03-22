<?php
namespace MicroIceEventManager\V1\Rest\EntityData;

class EntityDataResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EntityData\EntityDataModel');
    	$dataFields = $services->get('MicroIceEventManager\V1\Rest\EntityDataFields\EntityDataFieldsModel');
    	$dataFieldConfig = $services->get('MicroIceEventManager\V1\Rest\EntityDataFieldConfig\EntityDataFieldConfigModel');
    	$dataType = $services->get('MicroIceEventManager\V1\Rest\EntityDataTypes\EntityDataTypesModel');
    	$entitiesData = $services->get('MicroIceEventManager\V1\Rest\EntitiesData\EntitiesDataModel');
        $entities = $services->get('MicroIceEventManager\V1\Rest\Entities\EntitiesModel');
    	// $preferences = $services->get('MicroIceEventManager\V1\Rest\EntityDataPreferences\EntityDataPreferencesModel');
        $preferences = null; // not used yet

        return new EntityDataResource($model, $dataFields, $dataFieldConfig, $dataType, $entitiesData, $entities, $preferences);
    }
}
