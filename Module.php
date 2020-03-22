<?php
namespace MicroIceEventManager;

use ZF\Apigility\Provider\ApigilityProviderInterface;
use Zend\Mvc\MvcEvent;

class Module implements ApigilityProviderInterface
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return [
            'ZF\Apigility\Autoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src',
                ],
            ],
        ];
    }
    
    /**
     * For each apigility controller defined in module.config.php we create listeners for: 
     *      create.pre, create.post, patch.pre, patch.post, update.pre, update.post, delete.pre, delete.post
     * Event names will have the following pattern:
     *      [collection_name].[event_type].[pre|post], eg: event_profile_events.patch.post
     * Callback methods inside ResourceEventListener class will have the following naming convention:
     *      [collectionName][EventType][Pre|Post], eg: eventProfileEventsPatchPost
     * 
     * @param  MvcEvent $E The MvcEvent instance
     * @return void
     */
    public function onBootstrap(MvcEvent $E)
    {
        $serviceManager = $E->getApplication()->getServiceManager();
        $resourceEventListenerV1 = $serviceManager->get(V1\Rest\Listener\ResourceEventListener::class);

        $sharedManager = $E->getApplication()->getEventManager()->getSharedManager();
        
        $managedEvents = array('create.pre', 'create.post', 'patch.pre', 'patch.post', 'update.pre', 'update.post', 'delete.pre', 'delete.post');
        
        $config = $serviceManager->get('config');
        foreach ($config['zf-rest'] as $controller) {
            if (!preg_match('~MicroIceEventManager\\\V([0-9]+)\\\Rest\\\~', $controller['listener'], $matches)) {
                // only attach events to MicroIceEventManager
                continue;
            }
            foreach ($managedEvents as $eventType) {
                $eventName = $controller['collection_name'] . '.' . $eventType;
                $listener = 'resourceEventListenerV' . $matches[1];
                $callbackMethod = lcfirst(str_replace(' ', '', ucwords(preg_replace('/[\.\_]+/', ' ', $eventName))));
                $sharedManager->attach($controller['listener'], $eventName, array($$listener, $callbackMethod));
            }
        }
    }
}
