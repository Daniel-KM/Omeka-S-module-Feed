<?php declare(strict_types=1);

namespace Feed;

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
        ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

use Generic\AbstractModule;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;

/**
 * Feed
 *
 * Provide a rss feed.
 *
 * @copyright Daniel Berthereau, 2020
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;

    public function onBootstrap(MvcEvent $event): void
    {
        parent::onBootstrap($event);

        $this->getServiceLocator()->get('Omeka\Acl')
            ->allow(
                null,
                ['Feed\Controller\Feed']
            )
        ;
    }

    protected function postInstall(): void
    {
        $messenger = $this->getServiceLocator()->get('ControllerPluginManager')->get('messenger');
        $message = new \Omeka\Stdlib\Message(
            'Links to automatic feeds from search results can be appended to item / browse and item sets / browse pages, with module BlocksDisposition or through the theme. Furthermore, each site can add a specific manual feed via site settings.' // @translate
        );
        $messenger->addSuccess($message);
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {
        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_elements',
            [$this, 'handleSiteSettings']
        );
    }
}
