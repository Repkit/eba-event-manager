<?php
namespace MicroIceEventManager\V1\Rest\EntityDataFields;

class EntityDataFieldsResourceFactory
{
    public function __invoke($services)
    {

    	$model 	  = $services->get('MicroIceEventManager\V1\Rest\EntityDataFields\EntityDataFieldsModel');
    	$fieldCfg = $services->get('MicroIceEventManager\V1\Rest\EntityDataFieldConfig\EntityDataFieldConfigModel');
    	$data     = $services->get('MicroIceEventManager\V1\Rest\EntityData\EntityDataModel');

        return new EntityDataFieldsResource($model, $fieldCfg, $data);
    }
}
