<?php

return [
    'auth' => [
        'defaults' => [
            'guard' => 'web'
        ],
        'guards' => [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
        ],
        'providers' => [
            'users' => [
                'adapter' => 'model',
                'model'  => App\Models\User::class,
            ],
//            'users' => [
//                'adapter' => 'stream',
//                'src'   => __DIR__ . "/users.json",
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
        ],
//                'adapter' => 'memory',
//                'data'   => [
//                    0 => ["id" => 0, "name" => "admin", 'password' => 'admin', "email" => "admin@admin.ru"],
//                    1 => ["id" => 1, "name" => "admin1", 'password' => 'admin1', "email" => "admin1@admin.ru"],
//                ]
    ],
];