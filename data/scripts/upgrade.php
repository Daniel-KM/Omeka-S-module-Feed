<?php declare(strict_types=1);

namespace Feed;

use Omeka\Stdlib\Message;

/**
 * @var Module $this
 * @var \Laminas\ServiceManager\ServiceLocatorInterface $services
 * @var string $newVersion
 * @var string $oldVersion
 *
 * @var \Doctrine\DBAL\Connection $connection
 * @var \Doctrine\ORM\EntityManager $entityManager
 * @var \Omeka\Api\Manager $api
 * @var \Omeka\Mvc\Controller\Plugin\Messenger $messenger
 */
$plugins = $services->get('ControllerPluginManager');
// $api = $plugins->get('api');
// $config = require dirname(dirname(__DIR__)) . '/config/module.config.php';
// $settings = $services->get('Omeka\Settings');
// $connection = $services->get('Omeka\Connection');
$messenger = $plugins->get('messenger');
// $entityManager = $services->get('Omeka\EntityManager');

if (version_compare($oldVersion, '3.3.3.3', '<')) {
    $message = new Message(
        'It’s now possible to get automatic feeds from search results.' // @translate
    );
    $messenger->addSuccess($message);
}
