<?php

return [
    'auth' => [
        'defaults' => [
            'guard' => 'web',
            'passwords' => 'users',
        ],
        'guards' => [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
        ],
        'providers' => [
            'users' => [
                'driver' => 'model',
                'model'  => \Models\Users::class,
            ],
//            'users' => [
//                'driver' => 'file',
//                'path'  => __DIR__ . "/users.json",
//                'passsword_crypted' => false
//            ],
/**          file format .json
            {
                "0":{"name":"admin","password": "admin","email": "admin@admin.ru"},
                "1":{"name":"admin1","password": "admin1","email": "admin1@admin1.ru"},
                 ...
                or if password_crypted = true
               "2":{"name":"admin1","password": "$2y$10$ME02QlQxWGdDNUdiUTJucuhQHYQlIglb3lG2rfdzvK3UbQXAPrc.q","email": "admin1@admin1.ru"},
            }
 * */
        ]
    ],
];