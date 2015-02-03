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
            'JhFlexiTime\Controller\BookingAdmin'       => 'JhFlexiTime\Controller\BookingAdminController'
        ],
        'factories' => [
            'JhFlexiTime\Controller\Booking'            => 'JhFlexiTime\Controller\Factory\BookingControllerFactory',
            'JhFlexiTime\Controller\BookingRest'        => 'JhFlexiTime\Controller\Factory\BookingRestControllerFactory',
            'JhFlexiTime\Controller\Settings'           => 'JhFlexiTime\Controller\Factory\SettingsControllerFactory',
            'JhFlexiTime\Controller\RunningBalanceCli'  => 'JhFlexiTime\Controller\Factory\RunningBalanceCliControllerFactory',
            'JhFlexiTime\Controller\UserReminderCli'    => 'JhFlexiTime\Controller\Factory\UserReminderCliControllerFactory',
            'JhFlexiTime\Controller\UserRest'           => 'JhFlexiTime\Controller\Factory\UserRestControllerFactory',
        ],
    ],

    //router
    'router' => [
        'routes' => [
            'flexi-time' => [
                'type'      => 'literal',
                'options'   => [
                    'route'     => '/flexi-time',
                    'defaults' => [
                        'controller' => 'JhFlexiTime\Controller\Booking',
                        'action'     => 'list',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'list' => [
                        'type'      => 'literal',
                        'options'   => [
                            'route' => '/list',
                        ],
                    ],
                ],
            ],
            'flexi-time-rest' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/flexi-time-rest[/:id]',
                    'constraints' => [
                        'id' => '[0-9-]+',
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
                        'type' => 'literal',
                        'options' => [
                            'route' => '/flexi-time',
                            'defaults' => [
                                'controller' => 'JhFlexiTime\Controller\BookingAdmin',
                                'action'     => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    //console routes
    'console' => [
        'router' => [
            'routes' => [
                'calc-prev-month-balance' => [
                    'options'   => [
                        'route'     => 'calc-prev-month-balance',
                        'defaults'  => [
                            'controller' => 'JhFlexiTime\Controller\RunningBalanceCli',
                            'action'     => 'calc-prev-month-balance'
                        ],
                    ],
                ],
                're-calc-balance-all' => [
                    'options'   => [
                        'route'     => 're-calc-balance-all',
                        'defaults'  => [
                            'controller' => 'JhFlexiTime\Controller\RunningBalanceCli',
                            'action'     => 're-calc-running-balance'
                        ],
                    ],
                ],
                're-calc-balance-user' => [
                    'options'   => [
                        'route'     => 're-calc-balance-user <userEmail>',
                        'defaults'  => [
                            'controller' => 'JhFlexiTime\Controller\RunningBalanceCli',
                            'action'     => 're-calc-running-balance'
                        ],
                    ],
                ],
                'set-user-starting-balance' => [
                    'options'   => [
                        'route'     => 'set user init-balance <userEmail> <balance>',
                        'defaults'  => [
                            'controller' => 'JhFlexiTime\Controller\RunningBalanceCli',
                            'action'     => 'set-user-starting-balance'
                        ],
                        'constraints' => [
                            //regex to match decimal and floats
                            'balance' => '/^-?(?:\d+|\d*\.\d+)$/',
                        ],

                    ],
                ],
                'notify-missing-bookings' => [
                    'options'   => [
                        'route'     => 'notify-missing-bookings',
                        'defaults'  => [
                            'controller' => 'JhFlexiTime\Controller\UserReminderCli',
                            'action'     => 'find-and-notify-missing-bookings '
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
            'JhFlexiTime\Repository\BookingRepository'          => 'JhFlexiTime\Repository\Factory\BookingRepositoryFactory',
            'JhFlexiTime\Repository\BalanceRepository'          => 'JhFlexiTime\Repository\Factory\BalanceRepositoryFactory',
            'JhFlexiTime\Service\BookingService'                => 'JhFlexiTime\Service\Factory\BookingServiceFactory',
            'JhFlexiTime\Service\TimeCalculatorService'         => 'JhFlexiTime\Service\Factory\TimeCalculatorServiceFactory',
            'JhFlexiTime\Service\PeriodService'                 => 'JhFlexiTime\Service\Factory\PeriodServiceFactory',
            'JhFlexiTime\Service\BalanceService'                => 'JhFlexiTime\Service\Factory\BalanceServiceFactory',
            'JhFlexiTime\Listener\BookingSaveListener'          => 'JhFlexiTime\Listener\Factory\BookingSaveListenerFactory',
            'JhFlexiTime\Listener\CappedCreditListener'         => 'JhFlexiTime\Listener\Factory\CappedCreditListenerFactory',
            'JhFlexiTime\Options\ModuleOptions'                 => 'JhFlexiTime\Options\Factory\ModuleOptionsFactory',
            'JhFlexiTime\Options\BookingOptions'                => 'JhFlexiTime\Options\Factory\BookingOptionsFactory',
            'JhFlexiTime\Options\NotificationOptions'           => 'JhFlexiTime\Options\Factory\NotificationOptionsFactory',
            'JhFlexiTime\Service\RunningBalanceService'         => 'JhFlexiTime\Service\Factory\RunningBalanceServiceFactory',
            'JhFlexiTime\Repository\UserSettingsRepository'     => 'JhFlexiTime\Repository\Factory\UserSettingsRepositoryFactory',
            'JhFlexiTime\Entity\UserSettings'                   => 'JhFlexiTime\Entity\Factory\UserSettingsFactory',
            'JhFlexiTime\Install\Installer'                     => 'JhFlexiTime\Install\Factory\InstallerFactory',
            'JhFlexiTime\Repository\CappedCreditRepository'     => 'JhFlexiTime\Repository\Factory\CappedCreditRepositoryFactory',
            'JhFlexiTime\Repository\BookedCreditRepository'     => 'JhFlexiTime\Repository\Factory\BookedCreditRepositoryFactory',
            'JhFlexiTime\Service\CappedCreditService'           => 'JhFlexiTime\Service\Factory\CappedCreditServiceFactory',
            'JhFlexiTime\Service\MissingBookingReminderService' => 'JhFlexiTime\Service\Factory\MissingBookingReminderServiceFactory',
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
    'spiffy_navigation' => [
        'containers' => [
            'default' => [
                [
                    'options' => [
                        'name'          => 'Flexitime',
                        'label'         => 'Flexitime',
                        'route'         => 'flexi-time/list',
                        'role'          => 'user',
                        'permission'    => 'user-nav.view'
                    ],
                ],
            ],
            'admin' => [
                [
                    'options' => [
                        'name'          => 'Flexitime Admin',
                        'label'         => 'Flexitime',
                        'route'         => 'zfcadmin/flexi-time',
                    ],
                ]
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

    'zfc_rbac' => [
        'guards' => [
            'ZfcRbac\Guard\RouteGuard' => [
                'flexi-time*' => ['user'],
            ]
        ],
    ],

    'jh_hub' => [
        'roles' => [
            'admin' => [
                'permissions' => [
                    'flexi-time.createOthers',
                    'flexi-time.editOthers',
                    'flexi-time.readOthers',
                    'flexi-time.deleteOthers',
                ],
                'children' => [
                    'user' => [
                        'permissions' => [
                            'flexi-time.create',
                            'flexi-time.edit',
                            'flexi-time.read',
                            'flexi-time.delete',
                        ],
                    ],
                ],
            ],
        ]
    ],

    'flexi' => [
        'notification_options' => [
            'remind_start'  => '2 days ago',
            'remind_days'   => '7 days'
        ]
    ],
];
