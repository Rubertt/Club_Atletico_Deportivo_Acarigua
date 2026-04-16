<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

final class RoleMiddleware
{
    /**
     * Uso en rutas: [RoleMiddleware::class, ['admin']] o [RoleMiddleware::class, [ROL_ADMIN]].
     */
    public function handle(Request $request, callable $next, string|int ...$roles): Response
    {
        $user = Auth::user();
        if ($user === null) {
            return $request->isJson()
                ? Response::json(['error' => 'No autenticado'], 401)
                : Response::redirect('/login');
        }

        $map = config('auth.roles') ?? [];
        $allowed = [];
        foreach ($roles as $r) {
            $allowed[] = is_string($r) ? ($map[$r] ?? 0) : (int) $r;
        }

        if (!in_array((int) $user['rol_id'], $allowed, true)) {
            if ($request->isJson()) {
                return Response::json(['error' => 'No autorizado'], 403);
            }
            $view = BASE_PATH . '/app/Views/errors/403.php';
            $html = is_file($view) ? self::render($view) : '<h1>403</h1>';
            return Response::html($html, 403);
        }

        $request->setUser($user);
        return $next($request);
    }

    private static function render(string $path): string
    {
        ob_start();
        include $path;
        return (string) ob_get_clean();
    }
}
