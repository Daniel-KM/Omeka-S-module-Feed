<?php
namespace Feed;

return [
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
                        'type' => \Zend\Router\Http\Segment::class,
                        'options' => [
                            'route' => '/feed[/:feed]',
                            'constraints' => [
                                'feed' => 'atom|rss',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Feed\Controller',
                                'controller' => 'Feed',
                                'action' => 'index',
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
            'feed_entries' => [],
        ],
    ],
];
