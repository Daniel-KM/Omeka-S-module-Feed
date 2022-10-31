<?php declare(strict_types=1);
namespace Feed;

return [
    'view_manager' => [
        'template_path_stack' => [
            dirname(__DIR__) . '/view',
        ],
    ],
    'form_elements' => [
        'invokables' => [
            Form\SiteSettingsFieldset::class => Form\SiteSettingsFieldset::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            'Feed\Controller\Feed' => Service\Controller\FeedControllerFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'site' => [
                'child_routes' => [
                    // Static atom/rss feed of a site.
                    'feed' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/feed[/:feed]',
                            'constraints' => [
                                'feed' => 'atom|rss',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Feed\Controller',
                                'controller' => 'Feed',
                                'action' => 'index',
                                'feed' => 'rss',
                            ],
                        ],
                    ],
                    // Dynamic feed for search of each resource type.
                    // A search shall be /s/site/item.rss?query (or atom.xml),
                    // but it is used by Bulk Export. So add a rss feed to it
                    // and module Feed is mainly used for static feed.
                    'feed-resource-atom' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/atom[/:resource-type]',
                            'constraints' => [
                                'action' => 'rss',
                                'feed' => 'atom',
                                'resource-type' => 'resource|item-set|item|media|annotation',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Feed\Controller',
                                'controller' => 'Feed',
                                'action' => 'rss',
                                'feed' => 'atom',
                                'resource-type' => 'item',
                            ],
                        ],
                    ],
                    'feed-resource-rss' => [
                        'type' => \Laminas\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/rss[/:resource-type]',
                            'constraints' => [
                                'action' => 'rss',
                                'feed' => 'rss',
                                'resource-type' => 'resource|item-set|item|media|annotation',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Feed\Controller',
                                'controller' => 'Feed',
                                'action' => 'rss',
                                'feed' => 'rss',
                                'resource-type' => 'item',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => dirname(__DIR__) . '/language',
                'pattern' => '%s.mo',
                'text_domain' => null,
            ],
        ],
    ],
    'feed' => [
        'site_settings' => [
            'feed_logo' => null,
            'feed_entry_length' => 1000,
            'feed_entries' => [],
            'feed_media_type' => 'standard',
            'feed_disposition' => 'attachment',
        ],
    ],
];
