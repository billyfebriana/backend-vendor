<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'], // Tambahkan login, logout jika ingin mencakupnya
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*', 'http://localhost:3000'], // ATAU ['http://localhost:3000'] untuk lebih spesifik
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];