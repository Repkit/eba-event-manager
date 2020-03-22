<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileTypeTranslations;

class EntityProfileTypeTranslationsResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EntityProfileTypeTranslations\EntityProfileTypeTranslationsModel');

        return new EntityProfileTypeTranslationsResource($model);
    }
}
