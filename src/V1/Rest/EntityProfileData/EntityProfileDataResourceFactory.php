<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileData;

class EntityProfileDataResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EntityProfileData\EntityProfileDataModel');
        $dataFields = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDataFields\EntityProfileDataFieldsModel');
        $dataFieldConfig = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDataFieldConfig\EntityProfileDataFieldConfigModel');
        $dataType = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDataTypes\EntityProfileDataTypesModel');
        $profilesData = $services->get('MicroIceEventManager\V1\Rest\EntityProfilesData\EntityProfilesDataModel');

        return new EntityProfileDataResource($model, $dataFields, $dataFieldConfig, $dataType, $profilesData);
    }
}
