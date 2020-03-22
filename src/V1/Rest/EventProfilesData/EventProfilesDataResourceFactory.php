<?php
namespace MicroIceEventManager\V1\Rest\EventProfilesData;

class EventProfilesDataResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EventProfilesData\EventProfilesDataModel');
        $data = $services->get('MicroIceEventManager\V1\Rest\EventProfileData\EventProfileDataModel');

        return new EventProfilesDataResource($model, $data);
    }
}
