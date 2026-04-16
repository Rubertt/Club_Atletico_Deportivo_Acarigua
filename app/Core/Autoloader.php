<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Autoloader PSR-4 simple como fallback cuando Composer no está instalado.
 */
final class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register([self::class, 'load']);
    }

    public static function load(string $class): void
    {
        if (!str_starts_with($class, 'App\\')) {
            return;
        }

        $relative = substr($class, 4);
        $path = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';

        if (is_file($path)) {
            require $path;
        }
    }
}
