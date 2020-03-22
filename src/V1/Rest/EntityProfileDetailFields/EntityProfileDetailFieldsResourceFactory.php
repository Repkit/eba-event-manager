<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileDetailFields;

class EntityProfileDetailFieldsResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDetailFields\EntityProfileDetailFieldsModel');
        $profileType = $services->get('MicroIceEventManager\V1\Rest\EntityProfileTypes\EntityProfileTypesModel');
        
        return new EntityProfileDetailFieldsResource($model, $profileType);
    }
}
