<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

final class CsrfMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $method = $request->method();
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return $next($request);
        }

        // APIs usan JWT (cookie httpOnly + sameSite Lax) — CSRF no aplica igual
        if ($request->isJson() && !$request->isAjax() && str_starts_with($request->uri(), '/api/')) {
            return $next($request);
        }

        $sent = $request->input('_csrf') ?? $request->header('x-csrf-token');
        $session = $_SESSION['_csrf'] ?? null;

        if (!$sent || !$session || !hash_equals((string) $session, (string) $sent)) {
            if ($request->isJson() || $request->isAjax()) {
                return Response::json(['error' => 'Token CSRF inválido'], 419);
            }
            flash('error', 'Sesión expirada. Intenta nuevamente.');
            return Response::redirect('/');
        }

        return $next($request);
    }
}
