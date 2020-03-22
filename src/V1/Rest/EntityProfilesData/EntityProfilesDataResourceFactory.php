<?php
namespace MicroIceEventManager\V1\Rest\EntityProfilesData;

class EntityProfilesDataResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EntityProfilesData\EntityProfilesDataModel');
        $data = $services->get('MicroIceEventManager\V1\Rest\EntityProfileData\EntityProfileDataModel');

        return new EntityProfilesDataResource($model, $data);
    }
}
