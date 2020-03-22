<?php
namespace MicroIceEventManager\V1\Rest\EntityTranslations;

class EntityTranslationsResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EntityTranslations\EntityTranslationsModel');

        return new EntityTranslationsResource($model);
    }
}
