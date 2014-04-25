<?php

namespace JhFlexiTime;

return [
    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity'],
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ],
            ],
        ],
    ],

    //controllers
    'controllers' => [
        'invokables' => [
            'JhFlexiTime\Controller\Booking'     => 'JhFlexiTime\Controller\BookingController',
        ],
        'factories' => [
            'JhFlexiTime\Controller\BookingRest'    => 'JhFlexiTime\Controller\Factory\BookingRestControllerFactory',
            'JhFlexiTime\Controller\Settings'       => 'JhFlexiTime\Controller\Factory\SettingsControllerFactory',
            'JhFlexiTime\Controller\BookingAdmin'   => 'JhFlexiTime\Controller\Factory\BookingAdminControllerFactory',
        ],
    ],

    //routing
    'router' => [
        'routes' => [
            'flexi-time' => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/flexi-time[/][:action][/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'JhFlexiTime\Controller\Booking',
                        'action'     => 'list',
                    ],
                ],
            ],
            'flexi-time-rest' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/flexi-time-rest[/:id]',
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'JhFlexiTime\Controller\BookingRest',
                    ]
                ],
            ],
            'settings' => [
                'type'      => 'literal',
                'options'   => [
                    'route' => '/settings',
                    'defaults' => [
                        'controller' => 'JhFlexiTime\Controller\Settings',
                        'action'     => 'get',
                    ],
                ],
            ],

            //admin routes
            'zfcadmin' => [
                'child_routes' => [
                    'flexi-time' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/flexi-time[/][:action][/:id]',
                            'constraints' => [
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => 'JhFlexiTime\Controller\BookingAdminController',
                                'action'     => 'view',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    //forms & fieldsets
    'form_elements' => [
        'factories' => [
            'JhFlexiTime\Form\BookingForm' => 'JhFlexiTime\Form\Factory\BookingFormFactory'
        ],
    ],

    'service_manager' => [
        'factories' => [
            'JhFlexiTime\Repository\BookingRepository'       => 'JhFlexiTime\Repository\Factory\BookingRepositoryFactory',
            'JhFlexiTime\Repository\BalanceRepository'       => 'JhFlexiTime\Repository\Factory\BalanceRepositoryFactory',
            'JhFlexiTime\Service\BookingService'             => 'JhFlexiTime\Service\Factory\BookingServiceFactory',
            'JhFlexiTime\Service\TimeCalculatorService'      => 'JhFlexiTime\Service\Factory\TimeCalculatorServiceFactory',
            'JhFlexiTime\Service\PeriodService'              => 'JhFlexiTime\Service\Factory\PeriodServiceFactory',
            'JhFlexiTime\Service\BalanceService'             => 'JhFlexiTime\Service\Factory\BalanceServiceFactory',
            'JhFlexiTime\Listener\BookingSaveListener'       => 'JhFlexiTime\Listener\Factory\BookingSaveListenerFactory',
            'JhFlexiTime\Options\ModuleOptions'              => 'JhFlexiTime\Options\Factory\ModuleOptionsFactory',
            'JhFlexiTime\Options\BookingOptions'             => 'JhFlexiTime\Options\Factory\BookingOptionsFactory',
        ],
        'aliases' => [
            'JhFlexiTime\ObjectManager'     => 'Doctrine\ORM\EntityManager',
            'FlexiOptions'                  => 'JhFlexiTime\Options\ModuleOptions',
            'BookingOptions'                => 'JhFlexiTime\Options\BookingOptions',
        ],
    ],

    //template
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'template_map' => [
            'booking/week' => __DIR__ . '/../view/partial/booking-week.phtml',
            'booking/edit' => __DIR__ . '/../view/partial/booking-edit.phtml',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],

    'input_filters' => [
        'factories' => [
            'JhFlexiTime\InputFilter\BookingInputFilter' => 'JhFlexiTime\InputFilter\Factory\BookingInputFilterFactory',
        ],
    ],

    'view_helpers' => [
        'invokables' => [
            'bookingClasses' => 'JhFlexiTime\View\Helper\BookingClasses',
        ],
    ],

    //Add Flexitime Link to Hub navigation
    'navigation' => [
        'default' => [
            [
                'name'      => 'Flexitime',
                'label'     => 'Flexitime',
                'route'     => 'flexi-time',
                'resource'  => 'user-nav',
                'privilege' => 'view',
            ],
        ],
    ],

    'asset_manager' => [
        'resolver_configs' => [
            'paths' => [
                __DIR__ . '/../public',
            ],
        ],
    ],
];
