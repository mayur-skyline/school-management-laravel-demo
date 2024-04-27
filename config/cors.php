<?php

    return [

        'paths' => [ 'api-astnext/*' ],
        'allowed_methods' => ['*'],
        'allowed_origins' => ['http://127.0.0.1','http://127.0.0.7', 'http://localhost'],
        'allowed_origins_patterns' => [],
        'allowed_headers' => ['*'],
        'exposed_headers' => [],
        'max_age' => 0,
        'supports_credentials' => true
    ];
