<?php
namespace MicroIceEventManager\V1\Rest\EventTranslations;

class EventTranslationsResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EventTranslations\EventTranslationsModel');

        return new EventTranslationsResource($model);
    }
}
