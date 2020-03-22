<?php
namespace MicroIceEventManager\V1\Rest\EventProfileTypeTranslations;

class EventProfileTypeTranslationsResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EventProfileTypeTranslations\EventProfileTypeTranslationsModel');

        return new EventProfileTypeTranslationsResource($model);
    }
}
