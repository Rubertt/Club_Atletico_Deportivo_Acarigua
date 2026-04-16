<?php
declare(strict_types=1);

// Autoloader (prefiere Composer si está disponible; fallback a PSR-4 manual)
$composerAutoload = BASE_PATH . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
} else {
    require BASE_PATH . '/app/Core/Autoloader.php';
    \App\Core\Autoloader::register();
    require BASE_PATH . '/app/Helpers/constants.php';
    require BASE_PATH . '/app/Helpers/functions.php';
}

// Carga variables de entorno (.env) manualmente (sin depender de phpdotenv)
if (is_file(BASE_PATH . '/.env')) {
    foreach (file(BASE_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
        $name  = trim($name);
        $value = trim($value);
        if (strlen($value) >= 2 && (
            ($value[0] === '"' && $value[-1] === '"') ||
            ($value[0] === "'" && $value[-1] === "'")
        )) {
            $value = substr($value, 1, -1);
        }
        $_ENV[$name]    = $value;
        $_SERVER[$name] = $value;
        putenv("$name=$value");
    }
}

// Configuración global
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Caracas');
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
}

$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
error_reporting($debug ? E_ALL : (E_ALL & ~E_NOTICE & ~E_DEPRECATED));
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');

// Sesión con cookies seguras (usada para flash messages y CSRF)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => ($_ENV['APP_ENV'] ?? 'local') === 'production',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('cada_session');
    session_start();
}
