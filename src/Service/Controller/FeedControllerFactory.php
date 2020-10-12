<?php declare(strict_types=1);

namespace Feed\Service\Controller;

use Feed\Controller\FeedController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class FeedControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new FeedController(
            $services->get('ViewRenderer'),
            $services->get('Omeka\ModuleManager')->getModule('Feed')->getIni('version')
        );
    }
}
