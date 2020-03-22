<?php
namespace MicroIceEventManager\V1\Rest\EventProfileTranslations;

class EventProfileTranslationsResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EventProfileTranslations\EventProfileTranslationsModel');

        return new EventProfileTranslationsResource($model);
    }
}
