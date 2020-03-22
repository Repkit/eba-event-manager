<?php
namespace MicroIceEventManager\V1\Rest\EventProfileDetails;

class EventProfileDetailsResourceFactory
{
    public function __invoke($services)
    {

        $model = $services->get('MicroIceEventManager\V1\Rest\EventProfileDetails\EventProfileDetailsModel');
        $fieldCfg = $services->get('MicroIceEventManager\V1\Rest\EventProfileDetailFields\EventProfileDetailFieldsModel');
        $profileTranslation = $services->get('MicroIceEventManager\V1\Rest\EventProfileTranslations\EventProfileTranslationsModel');
        $profileTypes = $services->get('MicroIceEventManager\V1\Rest\EventProfileTypes\EventProfileTypesModel');
        $modelProfilesTypes = $services->get('MicroIceEventManager\V1\Rest\EventProfilesTypes\EventProfilesTypesModel');

        return new EventProfileDetailsResource($model, $fieldCfg, $profileTranslation, $profileTypes, $modelProfilesTypes);
    }
}
