<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use Throwable;

final class AuthController extends Controller
{
    public function showLogin(Request $request): Response
    {
        if (Auth::check()) {
            return $this->redirect('/admin');
        }
        return $this->view('public.login', [
            'title'      => 'Iniciar sesión - ' . config('app.name'),
            'hideHeader' => true,
            'hideFooter' => true,
        ], 'public');
    }

    public function login(Request $request): Response
    {
        $email    = trim((string) $request->input('email', ''));
        $password = (string) $request->input('password', '');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            [
                'email'    => 'required|max:100',
                'password' => 'required|min:4|max:255',
            ]
        );

        if (!$validator->validate()) {
            $this->withOld(['email' => $email])->withErrors($validator->errors());
            return $this->redirect('/login');
        }

        // Rate limit por IP + email (5 intentos / 5 min)
        $maxAttempts = (int) config('auth.login.max_attempts');
        $lockMinutes = (int) config('auth.login.lockout_minutes');
        $key = 'login_attempts_' . md5($request->ip() . '|' . strtolower($email));
        $attempts = $_SESSION[$key] ?? ['count' => 0, 'first' => time()];
        if ($attempts['count'] >= $maxAttempts && (time() - $attempts['first']) < $lockMinutes * 60) {
            $remaining = $lockMinutes * 60 - (time() - $attempts['first']);
            $mins = max(1, (int) ceil($remaining / 60));
            flash('error', "Demasiados intentos. Intenta nuevamente en $mins minuto(s).");
            return $this->redirect('/login');
        }
        if ((time() - $attempts['first']) >= $lockMinutes * 60) {
            $attempts = ['count' => 0, 'first' => time()];
        }

        try {
            $user = Auth::attempt($email, $password);
        } catch (Throwable $e) {
            flash('error', $e->getMessage());
            return $this->redirect('/login');
        }

        if ($user === null) {
            $attempts['count']++;
            $_SESSION[$key] = $attempts;
            Logger::warning('login.fallido', ['email' => $email, 'ip' => $request->ip()]);
            $this->withOld(['email' => $email]);
            flash('error', 'Credenciales inválidas.');
            return $this->redirect('/login');
        }

        unset($_SESSION[$key]);
        session_regenerate_id(true);
        Logger::audit('login.ok', ['email' => $email]);

        flash('success', 'Bienvenido, ' . ($user['email'] ?? ''));
        return $this->redirect('/admin');
    }

    public function logout(Request $request): Response
    {
        $user = Auth::user();
        if ($user) {
            Logger::audit('logout', ['email' => $user['email']]);
        }
        Auth::logout();
        flash('success', 'Sesión cerrada correctamente.');
        return $this->redirect('/');
    }

    public function showRecuperar(Request $request): Response
    {
        return $this->view('public.recuperar', [
            'title'      => 'Recuperar contraseña - ' . config('app.name'),
            'hideHeader' => true,
            'hideFooter' => true,
        ], 'public');
    }

    public function recuperar(Request $request): Response
    {
        $correo = trim((string) $request->input('correo', ''));
        $validator = Validator::make(['correo' => $correo], ['correo' => 'required|email']);
        if (!$validator->validate()) {
            $this->withErrors($validator->errors());
            return $this->redirect('/recuperar');
        }

        Logger::info('recuperar.solicitud', ['correo' => $correo, 'ip' => $request->ip()]);
        flash('success', 'Si el correo está registrado, recibirás instrucciones en tu bandeja de entrada.');
        return $this->redirect('/recuperar');
    }
}
