<?php
declare(strict_types=1);

return [
    'name'     => $_ENV['APP_NAME'] ?? 'Club Atlético Deportivo Acarigua',
    'env'      => $_ENV['APP_ENV'] ?? 'production',
    'debug'    => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url'      => rtrim($_ENV['APP_URL'] ?? 'http://localhost:8000', '/'),
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'America/Caracas',

    'uploads' => [
        'max_size'     => (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 2097152),
        'allowed_mime' => explode(',', $_ENV['UPLOAD_ALLOWED_MIME'] ?? 'image/jpeg,image/png,image/webp'),
        'atletas_dir'  => '/assets/uploads/atletas',
    ],

    'paths' => [
        'root'    => dirname(__DIR__),
        'app'     => dirname(__DIR__) . '/app',
        'public'  => dirname(__DIR__) . '/public',
        'views'   => dirname(__DIR__) . '/app/Views',
        'storage' => dirname(__DIR__) . '/storage',
        'logs'    => dirname(__DIR__) . '/storage/logs',
    ],
];
