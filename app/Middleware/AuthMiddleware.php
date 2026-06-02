<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

final class AuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $user = Auth::user();
        if ($user === null) {
            if ($request->isJson()) {
                return Response::json(['error' => 'No autenticado'], 401);
            }
            flash('error', 'Debes iniciar sesión.');
            return Response::redirect('/login');
        }

        // La ruta keep-alive puede renovar la sesión aunque haya "expirado"
        // el temporizador de inactividad, siempre que el JWT siga siendo válido.
        $isKeepAlive = $request->uri() === '/api/keep-alive';

        // Verificar expiración de sesión basada en la configuración de la BD
        $tiempoSesionMin = (int) config_db('tiempo_sesion', 120);
        $tiempoSesionSeg = $tiempoSesionMin * 60;
        $lastActivity = $_SESSION['_last_activity'] ?? 0;

        if (!$isKeepAlive && $lastActivity > 0 && (time() - $lastActivity) > $tiempoSesionSeg) {
            Auth::logout();
            if ($request->isJson()) {
                return Response::json(['error' => 'Sesión expirada'], 401);
            }
            flash('error', 'Tu sesión ha expirado por inactividad. Inicia sesión nuevamente.');
            return Response::redirect('/login');
        }

        // Actualizar marca de última actividad
        $_SESSION['_last_activity'] = time();

        // Check if user needs to set up account (first login)
        if (isset($_SESSION['must_change_password']) && $_SESSION['must_change_password'] === true) {
            $allowedPaths = ['/admin/setup', '/admin/setup/save', '/logout'];
            if (!in_array($request->uri(), $allowedPaths)) {
                if ($request->isJson()) {
                    return Response::json(['error' => 'Configuración de cuenta requerida', 'redirect' => '/admin/setup'], 403);
                }
                return Response::redirect('/admin/setup');
            }
        }

        $request->setUser($user);
        return $next($request);
    }
}
