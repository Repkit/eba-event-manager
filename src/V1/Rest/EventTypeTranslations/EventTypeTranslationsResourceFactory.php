<?php
namespace MicroIceEventManager\V1\Rest\EventTypeTranslations;

class EventTypeTranslationsResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EventTypeTranslations\EventTypeTranslationsModel');

        return new EventTypeTranslationsResource($model);
    }
}
