<?php declare(strict_types=1);
namespace Feed;

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
        ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

use Generic\AbstractModule;
use Laminas\EventManager\Event;
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

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {
        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_elements',
            [$this, 'handleSiteSettings']
        );
        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_input_filters',
            [$this, 'handleSiteSettingsFilters']
        );
    }

    public function handleSiteSettings(Event $event): void
    {
        parent::handleSiteSettings($event);

        $services = $this->getServiceLocator();
        $settings = $services->get('Omeka\Settings\Site');

        $fieldset = $event
            ->getTarget()
            ->get('feed');

        $entries = $settings->get('feed_entries') ?: [];
        $value = is_array($entries) ? implode("\n", $entries) : $entries;
        $fieldset
            ->get('feed_entries')
            ->setValue($value);
    }

    public function handleSiteSettingsFilters(Event $event): void
    {
        $event->getParam('inputFilter')
            ->get('feed')
            ->add([
                'name' => 'feed_entries',
                'required' => false,
                'filters' => [
                    [
                        'name' => \Laminas\Filter\Callback::class,
                        'options' => [
                            'callback' => [$this, 'stringToList'],
                        ],
                    ],
                ],
            ])
            ->add([
                'name' => 'feed_entry_length',
                'required' => false,
            ])
        ;
    }
}
