<?php
namespace MicroIceEventManager\V1\Rest\EventDataFields;

class EventDataFieldsResourceFactory
{
    public function __invoke($services)
    {

    	$model 	  = $services->get('MicroIceEventManager\V1\Rest\EventDataFields\EventDataFieldsModel');
    	$fieldCfg = $services->get('MicroIceEventManager\V1\Rest\EventDataFieldConfig\EventDataFieldConfigModel');
    	$data     = $services->get('MicroIceEventManager\V1\Rest\EventData\EventDataModel');

        return new EventDataFieldsResource($model, $fieldCfg, $data);
    }
}
