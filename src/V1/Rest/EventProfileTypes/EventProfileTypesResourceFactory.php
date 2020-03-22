<?php
namespace MicroIceEventManager\V1\Rest\EventProfileTypes;

class EventProfileTypesResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EventProfileTypes\EventProfileTypesModel');
        $translations = $services->get('MicroIceEventManager\V1\Rest\EventProfileTypeTranslations\EventProfileTypeTranslationsModel');
        
        return new EventProfileTypesResource($model, $translations);
    }
}
