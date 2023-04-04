<?php
return [
    'default' => 'pgsql',
    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
        'pgVisor' => [
            'driver'    => 'pgsql',
            'host'      => env('DB_HOST_VISOR'),
            'database'  => env('DB_DATABASE_VISOR'),
            'username'  => env('DB_USERNAME_VISOR'),
            'password'  => env('DB_PASSWORD_VISOR'),
            'charset'   => env('DB_CHARSET_VISOR'),
            'collation' => env('DB_COLLATION_VISOR'),
            'migrations'=> 'migrations',
        ],
        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL2'),
            'host' => env('DB_HOST2', 'localhost'),
            'port' => env('DB_PORT2', '1433'),
            'database' => env('DB_DATABASE2', 'forge'),
            'username' => env('DB_USERNAME2', 'forge'),
            'password' => env('DB_PASSWORD2', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
        ],
        'sqlsrv2' => [
            'driver'   => 'sqlsrv',
            'host'     => env('DB_HOST2', 'localhost'),
            'port'     => env('DB_PORT2', '1433'),
            'database' => env('DB_DATABASE2', 'database'),
            'username' => env('DB_USERNAME2', 'sa'),
            'password' => env('DB_PASSWORD2', ''),
            'charset'  => 'utf8',
            'prefix'   => '',
        ],

    ]
];
