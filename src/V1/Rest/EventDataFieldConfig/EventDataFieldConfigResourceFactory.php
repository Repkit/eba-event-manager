<?php
namespace MicroIceEventManager\V1\Rest\EventDataFieldConfig;

class EventDataFieldConfigResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EventDataFieldConfig\EventDataFieldConfigModel');
    	$dataType = $services->get('MicroIceEventManager\V1\Rest\EventDataTypes\EventDataTypesModel');
    	$dataFields = $services->get('MicroIceEventManager\V1\Rest\EventDataFields\EventDataFieldsModel');
    	
        return new EventDataFieldConfigResource($model, $dataType, $dataFields);
    }
}
