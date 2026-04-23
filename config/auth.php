<?php
declare(strict_types=1);

return [
    'jwt' => [
        'secret'      => $_ENV['JWT_SECRET'] ?? 'insecure_default_change_me',
        'ttl'         => (int) ($_ENV['JWT_TTL'] ?? 7200),
        'refresh_ttl' => (int) ($_ENV['JWT_REFRESH_TTL'] ?? 604800),
        'algo'        => 'HS256',
        'issuer'      => $_ENV['APP_URL'] ?? 'cada',
    ],

    'cookie' => [
        'name'     => 'cada_token',
        'path'     => '/',
        'domain'   => '',
        'secure'   => ($_ENV['APP_ENV'] ?? 'local') === 'production',
        'httponly' => true,
        'samesite' => 'Lax',
    ],

    'roles' => [
        'super_user'   => 1,
        'admin'        => 2,
        'entrenador'   => 3,
        'medico'       => 4,
    ],

    'login' => [
        'max_attempts'   => 5,
        'lockout_minutes' => 5,
    ],
];
