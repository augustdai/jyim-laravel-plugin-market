<?php

return [
    // api 授权
    'auth' => [
        'guard' => 'market',
        'guards' => [
            'market' => [
                'driver' => 'token',
                'provider' => 'market',
                'hash' => false,
            ],
        ],
        'providers' => [
            'market' => [
                'driver' => 'eloquent',
                'model' => \Jyim\LaravelPluginMarket\Models\MarketUser::class,
            ],
        ]
    ],

    // 允许访问后台的账号
    'allow_view_admin' => [
        'admin@jyim.com'
    ],

    // 是否开启自带ui
    'ui' => [
        'enable' => true
    ],
];
