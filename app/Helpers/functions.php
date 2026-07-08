<?php
declare(strict_types=1);

use App\Core\Auth;

if (!function_exists('config')) {
    /**
     * Obtiene un valor de configuración usando notación de puntos: config('app.url').
     */
    function config(string $key, mixed $default = null): mixed
    {
        static $cache = [];
        [$file, $path] = array_pad(explode('.', $key, 2), 2, null);

        if (!isset($cache[$file])) {
            $filePath = BASE_PATH . "/config/{$file}.php";
            $cache[$file] = is_file($filePath) ? require $filePath : [];
        }

        $value = $cache[$file];
        if ($path === null) {
            return $value;
        }

        foreach (explode('.', $path) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }
        return $value;
    }
}

if (!function_exists('e')) {
    /**
     * Escapa HTML. Uso obligatorio en vistas para prevenir XSS.
     */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('base_path')) {
    /**
     * Prefijo de carpeta según el servidor (vacío en php -S, /cada con Alias Apache).
     */
    function base_path(): string
    {
        $base = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($base === '/' || $base === '.' || $base === '') {
            return '';
        }
        return rtrim($base, '/');
    }
}

if (!function_exists('base_url')) {
    /**
     * URL base de la app. Si APP_URL está vacío, usa el host de la petición actual (para dual server e IPs).
     */
    function base_url(): string
    {
        $configured = trim((string) ($_ENV['APP_URL'] ?? ''));

        if ($configured === '') {
            $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443');
            $scheme = $https ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $path   = base_path();

            return rtrim($scheme . '://' . $host . $path, '/');
        }

        return rtrim($configured, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim(base_url(), '/');
        $path = '/' . ltrim($path, '/');
        return $base . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('/assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('old')) {
    /**
     * Recupera valor previo del formulario tras un error de validación.
     */
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['_old'][$key] ?? $default;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return Auth::user();
    }
}

if (!function_exists('can')) {
    /**
     * Verifica permiso por rol. Uso: can('admin') o can(ROL_ADMIN).
     */
    function can(int|string $role): bool
    {
        $user = Auth::user();
        if ($user === null) {
            return false;
        }

        // El super_usuario (ID 1) y el directivo (ID 4) siempre tienen permiso
        $userRol = (int) ($user['rol_id'] ?? 0);
        if ($userRol === 1 || $userRol === 4) {
            return true;
        }

        if (is_string($role)) {
            $map = config('auth.roles') ?? [];
            $role = $map[$role] ?? 0;
        }
        return (int) ($user['rol_id'] ?? 0) === (int) $role;
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }
        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $to, int $code = 302): never
    {
        header('Location: ' . url($to), true, $code);
        exit;
    }
}

if (!function_exists('request_method')) {
    function request_method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($_POST['_method'])) {
            $override = strtoupper($_POST['_method']);
            if (in_array($override, ['PUT', 'PATCH', 'DELETE'], true)) {
                return $override;
            }
        }
        return $method;
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): never
    {
        echo '<pre style="background:#111;color:#0f0;padding:16px;font-size:13px;">';
        foreach ($vars as $v) {
            var_dump($v);
        }
        echo '</pre>';
        exit;
    }
}

if (!function_exists('view_path')) {
    function view_path(string $view): string
    {
        return BASE_PATH . '/app/Views/' . str_replace('.', '/', $view) . '.php';
    }
}

if (!function_exists('config_db')) {
    /**
     * Obtiene un valor de configuración desde la base de datos (tabla configuraciones).
     */
    function config_db(string $key, mixed $default = null): mixed
    {
        return \App\Models\Configuracion::get($key, $default);
    }
}

if (!function_exists('has_errors')) {
    /**
     * Determina si existen errores de validación en la sesión.
     */
    function has_errors(): bool
    {
        return !empty($_SESSION['_errors']);
    }
}

if (!function_exists('errors')) {
    /**
     * Obtiene los errores de validación de la sesión.
     */
    function errors(): array
    {
        return $_SESSION['_errors'] ?? [];
    }
}

if (!function_exists('clean_cedula_dots')) {
    /**
     * Limpia los puntos y espacios de un documento de identidad
     * Ejemplo: ' V-12.345.678 ' -> 'V-12345678'
     */
    function clean_cedula_dots(?string $cedula): ?string
    {
        if ($cedula === null) {
            return null;
        }
        
        $cedula = trim($cedula);
        if ($cedula === '') {
            return '';
        }
        
        // Si contiene guión
        if (str_contains($cedula, '-')) {
            [$prefix, $num] = explode('-', $cedula, 2);
            $prefixUpper = strtoupper(trim($prefix));
            $numClean = trim($num);
            if ($prefixUpper === 'V' || $prefixUpper === 'E' || $prefixUpper === 'P') {
                return $prefixUpper . '-' . str_replace('.', '', $numClean);
            }
            return $prefixUpper . '-' . $numClean;
        }
        
        // Si no contiene guión, pero empieza con una letra de prefijo (ej: V12345678 o V12.345.678)
        $firstChar = strtoupper($cedula[0]);
        if (in_array($firstChar, ['V', 'E', 'P', 'N'], true)) {
            $num = substr($cedula, 1);
            $numClean = trim($num);
            if ($firstChar === 'V' || $firstChar === 'E' || $firstChar === 'P') {
                return $firstChar . '-' . str_replace('.', '', $numClean);
            }
            return $firstChar . '-' . $numClean;
        }
        
        return str_replace('.', '', $cedula);
    }
}

if (!function_exists('format_cedula')) {
    /**
     * Formatea el documento de identidad con puntos cada 3 dígitos para el frontend.
     * Ejemplo: 'V-12345678' -> 'V-12.345.678'
     */
    function format_cedula(?string $cedula): string
    {
        if (empty($cedula)) {
            return '';
        }
        
        $cedula = trim($cedula);
        
        if (!str_contains($cedula, '-')) {
            $firstChar = strtoupper($cedula[0]);
            if (in_array($firstChar, ['V', 'E', 'P'], true)) {
                $num = substr($cedula, 1);
                $digits = str_replace('.', '', $num);
                if (ctype_digit($digits)) {
                    if ($firstChar === 'P') {
                        return 'P-' . $digits;
                    }
                    return $firstChar . '-' . number_format((float)$digits, 0, '', '.');
                }
            } else {
                $digits = str_replace('.', '', $cedula);
                if (ctype_digit($digits)) {
                    return number_format((float)$digits, 0, '', '.');
                }
            }
            return $cedula;
        }
        
        [$prefix, $num] = explode('-', $cedula, 2);
        $prefixUpper = strtoupper(trim($prefix));
        
        if ($prefixUpper === 'V' || $prefixUpper === 'E' || $prefixUpper === 'P') {
            $digits = str_replace('.', '', $num);
            if (ctype_digit($digits)) {
                if ($prefixUpper === 'P') {
                    return 'P-' . $digits;
                }
                return $prefixUpper . '-' . number_format((float)$digits, 0, '', '.');
            }
        }
        
        return $cedula;
    }
}

if (!function_exists('clean_db_error_message')) {
    /**
     * Limpia los mensajes de error de base de datos que contienen SQLSTATE o códigos de error,
     * extrayendo únicamente el mensaje del trigger o de la restricción.
     */
    function clean_db_error_message(string $msg): string
    {
        if (str_contains($msg, 'SQLSTATE[45000]')) {
            if (preg_match('/1644\s+(.+)$/i', $msg, $matches)) {
                return trim($matches[1]);
            }
            if (preg_match('/: 1644 (.*)/i', $msg, $matches)) {
                return trim($matches[1]);
            }
        }
        return $msg;
    }
}



