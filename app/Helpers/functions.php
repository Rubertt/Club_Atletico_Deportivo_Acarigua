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

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim((string) config('app.url'), '/');
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
