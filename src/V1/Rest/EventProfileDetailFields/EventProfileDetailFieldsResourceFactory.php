<?php
namespace MicroIceEventManager\V1\Rest\EventProfileDetailFields;

class EventProfileDetailFieldsResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EventProfileDetailFields\EventProfileDetailFieldsModel');
        $profileType = $services->get('MicroIceEventManager\V1\Rest\EventProfileTypes\EventProfileTypesModel');
        
        return new EventProfileDetailFieldsResource($model, $profileType);
    }
}
