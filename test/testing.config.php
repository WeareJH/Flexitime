<?php

namespace JhFlexiTimeTest;

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'params' => [
                    'host'          => null,
                    'port'          => null,
                    'user'          => null,
                    'password'      => null,
                    'dbname'        => null,
                    'driver'        => 'pdo_sqlite',
                    'driverClass'   => 'Doctrine\DBAL\Driver\PDOSqlite\Driver',
                    'path'          => null,
                    'memory'        => true,
                ],
            ],
        ],
    ],
];
