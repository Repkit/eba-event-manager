<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileTranslations;

class EntityProfileTranslationsResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EntityProfileTranslations\EntityProfileTranslationsModel');

        return new EntityProfileTranslationsResource($model);
    }
}
