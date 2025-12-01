<?php declare(strict_types=1);

namespace Feed;

use Common\Stdlib\PsrMessage;

/**
 * @var Module $this
 * @var \Laminas\ServiceManager\ServiceLocatorInterface $services
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Omeka\Api\Manager $api
 * @var \Omeka\View\Helper\Url $url
 * @var \Laminas\Log\Logger $logger
 * @var \Omeka\Settings\Settings $settings
 * @var \Laminas\I18n\View\Helper\Translate $translate
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Laminas\Mvc\I18n\Translator $translator
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Settings\SiteSettings $siteSettings
 * @var \Omeka\Mvc\Controller\Plugin\Messenger $messenger
 */
$plugins = $services->get('ControllerPluginManager');
$url = $services->get('ViewHelperManager')->get('url');
$api = $plugins->get('api');
$config = $services->get('Config');
$logger = $services->get('Omeka\Logger');
$settings = $services->get('Omeka\Settings');
$translate = $plugins->get('translate');
$translator = $services->get('MvcTranslator');
$connection = $services->get('Omeka\Connection');
$messenger = $plugins->get('messenger');
$siteSettings = $services->get('Omeka\Settings\Site');
$entityManager = $services->get('Omeka\EntityManager');

if (!method_exists($this, 'checkModuleActiveVersion') || !$this->checkModuleActiveVersion('Common', '3.4.74')) {
    $message = new \Omeka\Stdlib\Message(
        $translate('The module %1$s should be upgraded to version %2$s or later.'), // @translate
        'Common', '3.4.74'
    );
    throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $message);
}

if (PHP_VERSION_ID < 80100) {
    $message = new \Common\Stdlib\PsrMessage(
        'To use the module with php lower than {php}, use version {version}.', // @translate
        ['php' => '8.1', 'version' => '3.4.6']
    );
    throw new \Omeka\Module\Exception\ModuleCannotInstallException((string) $message->setTranslator($translator));
}

if (version_compare($oldVersion, '3.3.3.3', '<')) {
    $message = new PsrMessage(
        'It’s now possible to get automatic feeds from search results.' // @translate
    );
    $messenger->addSuccess($message);
}
