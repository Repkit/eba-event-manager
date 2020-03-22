<?php
namespace MicroIceEventManager\V1\Rest\EntityProfiles;

class EntityProfilesResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EntityProfiles\EntityProfilesModel');
        $translation = $services->get('MicroIceEventManager\V1\Rest\EntityProfileTranslations\EntityProfileTranslationsModel');
        $profilesTypes = $services->get('MicroIceEventManager\V1\Rest\EntityProfilesTypes\EntityProfilesTypesModel');
        $details = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDetails\EntityProfileDetailsModel');
        $detailFields = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDetailFields\EntityProfileDetailFieldsModel');
        $profileTypesDataTypes = $services->get('MicroIceEventManager\V1\Rest\EntityProfileTypesDataTypes\EntityProfileTypesDataTypesModel');
        $profileData = $services->get('MicroIceEventManager\V1\Rest\EntityProfileData\EntityProfileDataModel');
        $profileDataFields = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDataFields\EntityProfileDataFieldsModel');
        $profileDataFieldConfig = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDataFieldConfig\EntityProfileDataFieldConfigModel');

        return new EntityProfilesResource($model, $translation, $profilesTypes, $details, $detailFields, $profileTypesDataTypes, $profileData, $profileDataFields, $profileDataFieldConfig);
    }
}
