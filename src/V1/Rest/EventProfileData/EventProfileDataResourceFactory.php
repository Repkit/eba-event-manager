<?php
namespace MicroIceEventManager\V1\Rest\EventProfileData;

class EventProfileDataResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EventProfileData\EventProfileDataModel');
        $dataFields = $services->get('MicroIceEventManager\V1\Rest\EventProfileDataFields\EventProfileDataFieldsModel');
        $dataFieldConfig = $services->get('MicroIceEventManager\V1\Rest\EventProfileDataFieldConfig\EventProfileDataFieldConfigModel');
        $dataType = $services->get('MicroIceEventManager\V1\Rest\EventProfileDataTypes\EventProfileDataTypesModel');
        $profilesData = $services->get('MicroIceEventManager\V1\Rest\EventProfilesData\EventProfilesDataModel');

        return new EventProfileDataResource($model, $dataFields, $dataFieldConfig, $dataType, $profilesData);
    }
}
