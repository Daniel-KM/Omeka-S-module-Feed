<?php

namespace Feed\Service\Controller;

use Feed\Controller\FeedController;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class FeedControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new FeedController(
            $services->get('Omeka\ModuleManager')->getModule('Feed')->getIni('version')
        );
    }
}
