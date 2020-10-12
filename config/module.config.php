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
            'feed_entries' => [],
            'feed_entry_length' => 1000,
            'feed_media_type' => 'standard',
            'feed_disposition' => 'attachment',
        ],
    ],
];
