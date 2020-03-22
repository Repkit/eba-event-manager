<?php
namespace MicroIceEventManager\V1\Rest\EntityDataFieldConfig;

class EntityDataFieldConfigResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EntityDataFieldConfig\EntityDataFieldConfigModel');
    	$dataType = $services->get('MicroIceEventManager\V1\Rest\EntityDataTypes\EntityDataTypesModel');
    	$dataFields = $services->get('MicroIceEventManager\V1\Rest\EntityDataFields\EntityDataFieldsModel');
    	
        return new EntityDataFieldConfigResource($model, $dataType, $dataFields);
    }
}
