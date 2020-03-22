<?php

namespace MicroIceEventManager\V1\Rest\Listener;

class ResourceEventListenerFactory
{
    public function __invoke($Services)
    {
        return new ResourceEventListener();
    }
}
