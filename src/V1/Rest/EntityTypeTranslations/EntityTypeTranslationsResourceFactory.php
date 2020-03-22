<?php
namespace MicroIceEventManager\V1\Rest\EntityTypeTranslations;

class EntityTypeTranslationsResourceFactory
{
    public function __invoke($services)
    {
    	$model = $services->get('MicroIceEventManager\V1\Rest\EntityTypeTranslations\EntityTypeTranslationsModel');

        return new EntityTypeTranslationsResource($model);
    }
}
