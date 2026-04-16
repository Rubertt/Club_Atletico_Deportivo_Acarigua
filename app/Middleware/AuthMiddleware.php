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
        $request->setUser($user);
        return $next($request);
    }
}
