<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileTypes;

class EntityProfileTypesResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EntityProfileTypes\EntityProfileTypesModel');
        $translations = $services->get('MicroIceEventManager\V1\Rest\EntityProfileTypeTranslations\EntityProfileTypeTranslationsModel');
        
        return new EntityProfileTypesResource($model, $translations);
    }
}
