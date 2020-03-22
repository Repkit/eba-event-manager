<?php
namespace MicroIceEventManager\V1\Rest\EntityProfileDetails;

class EntityProfileDetailsResourceFactory
{
    public function __invoke($services)
    {

        $model = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDetails\EntityProfileDetailsModel');
        $fieldCfg = $services->get('MicroIceEventManager\V1\Rest\EntityProfileDetailFields\EntityProfileDetailFieldsModel');
        $profileTranslation = $services->get('MicroIceEventManager\V1\Rest\EntityProfileTranslations\EntityProfileTranslationsModel');
        $profileTypes = $services->get('MicroIceEventManager\V1\Rest\EntityProfileTypes\EntityProfileTypesModel');
        $modelProfilesTypes = $services->get('MicroIceEventManager\V1\Rest\EntityProfilesTypes\EntityProfilesTypesModel');

        return new EntityProfileDetailsResource($model, $fieldCfg, $profileTranslation, $profileTypes, $modelProfilesTypes);
    }
}
