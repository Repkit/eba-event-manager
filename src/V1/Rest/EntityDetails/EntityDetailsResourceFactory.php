<?php
namespace MicroIceEventManager\V1\Rest\EntityDetails;

class EntityDetailsResourceFactory
{
    public function __invoke($services)
    {
    	// get config
    	// $config = $services->get('config');

        $model = $services->get('MicroIceEventManager\V1\Rest\EntityDetails\EntityDetailsModel');
        $modelFields = $services->get('MicroIceEventManager\V1\Rest\EntityDetailFields\EntityDetailFieldsModel');
        $types = $services->get('MicroIceEventManager\V1\Rest\EntityTypes\EntityTypesModel');
        $entities = $services->get('MicroIceEventManager\V1\Rest\Entities\EntitiesModel');
        $entitiesTypes = $services->get('MicroIceEventManager\V1\Rest\EntitiesTypes\EntitiesTypesModel');

        return new EntityDetailsResource($model, $modelFields, $types, $entities, $entitiesTypes);
    }
}
