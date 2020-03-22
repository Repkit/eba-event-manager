<?php
namespace MicroIceEventManager\V1\Rest\EntityDataPreferences;

class EntityDataPreferencesResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EntityDataPreferences\EntityDataPreferencesModel');
		$dataModel = $services->get('MicroIceEventManager\V1\Rest\EntityData\EntityDataModel');

        return new EntityDataPreferencesResource($model, $dataModel);
    }
}
