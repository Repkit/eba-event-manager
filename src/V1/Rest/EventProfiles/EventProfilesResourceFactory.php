<?php
namespace MicroIceEventManager\V1\Rest\EventProfiles;

class EventProfilesResourceFactory
{
    public function __invoke($services)
    {
        $model = $services->get('MicroIceEventManager\V1\Rest\EventProfiles\EventProfilesModel');
    	$translation = $services->get('MicroIceEventManager\V1\Rest\EventProfileTranslations\EventProfileTranslationsModel');
        $profilesTypes = $services->get('MicroIceEventManager\V1\Rest\EventProfilesTypes\EventProfilesTypesModel');
        $details = $services->get('MicroIceEventManager\V1\Rest\EventProfileDetails\EventProfileDetailsModel');
        $detailFields = $services->get('MicroIceEventManager\V1\Rest\EventProfileDetailFields\EventProfileDetailFieldsModel');
        $profileTypesDataTypes = $services->get('MicroIceEventManager\V1\Rest\EventProfileTypesDataTypes\EventProfileTypesDataTypesModel');
        $profileData = $services->get('MicroIceEventManager\V1\Rest\EventProfileData\EventProfileDataModel');
        $profileDataFields = $services->get('MicroIceEventManager\V1\Rest\EventProfileDataFields\EventProfileDataFieldsModel');
        $profileDataFieldConfig = $services->get('MicroIceEventManager\V1\Rest\EventProfileDataFieldConfig\EventProfileDataFieldConfigModel');

        return new EventProfilesResource($model, $translation, $profilesTypes, $details, $detailFields, $profileTypesDataTypes, $profileData, $profileDataFields, $profileDataFieldConfig);
    }
}
