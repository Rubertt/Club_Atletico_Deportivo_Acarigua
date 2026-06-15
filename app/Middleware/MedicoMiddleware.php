<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

final class MedicoMiddleware
{
    /**
     * Maneja el acceso para el rol de médico y superiores.
     * Permite acceso a: super_user (1), admin (2), directivo (4) y medico (5).
     * Deniega el acceso a: entrenador (3) y otros.
     */
    public function handle(Request $request, callable $next): Response
    {
        $user = Auth::user();
        if ($user === null) {
            return $request->isJson()
                ? Response::json(['error' => 'No autenticado'], 401)
                : Response::redirect('/login');
        }

        $userRol = (int) ($user['rol_id'] ?? 0);
        // Permitir a super_user (1), admin (2), directivo (4) y medico (5)
        if ($userRol === 1 || $userRol === 2 || $userRol === 4 || $userRol === 5) {
            $request->setUser($user);
            return $next($request);
        }

        if ($request->isJson()) {
            return Response::json(['error' => 'No autorizado'], 403);
        }

        $view = BASE_PATH . '/app/Views/errors/403.php';
        $html = is_file($view) ? self::render($view) : '<h1>403</h1>';
        return Response::html($html, 403);
    }

    private static function render(string $path): string
    {
        ob_start();
        include $path;
        return (string) ob_get_clean();
    }
}
