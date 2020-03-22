<?php
namespace MicroIceEventManager\V1\Rest\EntityDataTypes;

class EntityDataTypesResourceFactory
{
    public function __invoke($services)
    {

        $model                    = $services->get('MicroIceEventManager\V1\Rest\EntityDataTypes\EntityDataTypesModel');
        $entityData               = $services->get('MicroIceEventManager\V1\Rest\EntityData\EntityDataModel');
        $entityDataFields         = $services->get('MicroIceEventManager\V1\Rest\EntityDataFields\EntityDataFieldsModel');
        $entityDataFieldsConfig   = $services->get('MicroIceEventManager\V1\Rest\EntityDataFieldConfig\EntityDataFieldConfigModel');
        $entitiesData             = $services->get('MicroIceEventManager\V1\Rest\EntitiesData\EntitiesDataModel');
        // $entityDataPreferences = $services->get('MicroIceEventManager\V1\Rest\EntityDataPreferences\EntityDataPreferencesModel');
        $entityDataPreferences    = null;

        return new EntityDataTypesResource($model, $entityData, $entityDataFields, $entityDataFieldsConfig, $entitiesData, $entityDataPreferences);
    
    }
}
