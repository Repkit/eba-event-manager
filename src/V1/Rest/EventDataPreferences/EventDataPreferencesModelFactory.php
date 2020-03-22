<?php

	/**  
	 * EventDataPreferencesModelFactory object  
	 *  
	 * @file       EventDataPreferencesFactory.php  
	 * @category   MicroIce
	 * @package    event data preferences
	 * @author     Ionut Andrei Baches <ionut.baches@dcsplus.net>
	 * @copyright  2018 dcs plus  
	 * @since      2018-08-20
	 */

namespace MicroIceEventManager\V1\Rest\EventDataPreferences;

class EventDataPreferencesModelFactory
{
	public function __invoke($services)
    {
        // model
        /*we first check for DbManager as we plan to make it a standalone 
        library and then it will become a composer dependency*/
        if($services->has('DbManager'))
        {
        	$model = $services->get('DbManager')->get('MicroIceEventManager\V1\Rest\EventDataPreferences\EventDataPreferencesEntity');
        }
        else
        {
        	$config = $services->get('config');
	    	if( !isset($config['event_manager_settings']) || empty($config['event_manager_settings']) )
	        {
	            throw new \Exception("Error reading event-manager settings", 1);
	        }
	        $settings = $config['event_manager_settings'];

        	// check if has it's own connection
        	if(!empty($settings['db']))
            {
                $adapter = new \Zend\Db\Adapter\Adapter($settings['db']);
            }
            // check for global instance connection
        	elseif($services->has("Zend\Db\Adapter\Adapter"))
            {
                $adapter = $services->get("Zend\Db\Adapter\Adapter");
            }
            // check for global connection config
            elseif(!empty($config['db']))
            {
                $adapter = new \Zend\Db\Adapter\Adapter($config['db']);
            }
            else
	        {
	            throw new \Exception("Could not create adapter for event manager", 1);
	        }

        	$model =  new EventDataPreferencesModel($adapter);

        }
        
        return $model;
    }
}