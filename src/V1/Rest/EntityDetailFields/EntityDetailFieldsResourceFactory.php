<?php
namespace MicroIceEventManager\V1\Rest\EntityDetailFields;

class EntityDetailFieldsResourceFactory
{
    public function __invoke($services)
    {
    	// get config
    	// $config = $services->get('config');

        $model = $services->get('MicroIceEventManager\V1\Rest\EntityDetailFields\EntityDetailFieldsModel');
        $types = $services->get('MicroIceEventManager\V1\Rest\EntityTypes\EntityTypesModel');

        
        return new EntityDetailFieldsResource($model ,$types);
    }
}
