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

        $cedula = (string) ($user['cedula'] ?? '');
        $pwdNum = preg_replace('/[^0-9A-Za-z]/', '', $password);
        // Extraer número de documento después del prefijo
        $cedNum = str_contains($cedula, '-') ? str_replace('.', '', explode('-', $cedula, 2)[1] ?? '') : preg_replace('/[^0-9]/', '', $cedula);

        // Verificar si la contraseña es el número de documento de identidad
        $needsSetup = ($cedNum !== '' && $pwdNum === $cedNum);

        // Verificar si el usuario NO tiene preguntas de seguridad configuradas
        if (!$needsSetup) {
            $respuestas = (new \App\Models\RespuestaSeguridad())->getByUser((int) $user['usuario_id']);
            $needsSetup = empty($respuestas);
        }

        if ($needsSetup) {
            $_SESSION['must_change_password'] = true;
            flash('warning', 'Bienvenido. Por seguridad, debes configurar tu cuenta antes de continuar.');
            return $this->redirect('/admin/setup');
        }

        flash('success', 'Bienvenido, ' . ($user['correo'] ?? ''));
        return $this->redirect('/admin');
    }

    public function logout(Request $request): Response
    {
        $user = Auth::user();
        if ($user) {
            Logger::audit('logout', ['email' => $user['correo'] ?? '']);
        }
        Auth::logout();
        flash('success', 'Sesión cerrada correctamente.');
        return $this->redirect('/');
    }

    // ──────────────────────────────────────────────
    // Recuperación de contraseña — Paso 1: Correo
    // ──────────────────────────────────────────────
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

        $user = (new \App\Models\Usuario())->findByCorreo($correo);

        // Si no existe o está inactivo → mensaje genérico (no revelar info)
        if (!$user || $user['estatus'] !== 'Activo') {
            Logger::info('recuperar.correo_no_encontrado', ['correo' => $correo, 'ip' => $request->ip()]);
            flash('error', 'No se encontró una cuenta activa con ese correo o no tiene preguntas de seguridad configuradas.');
            return $this->redirect('/recuperar');
        }

        // Verificar que tenga preguntas de seguridad
        $respuestas = (new \App\Models\RespuestaSeguridad())->getByUserWithAnswers((int) $user['usuario_id']);
        if (empty($respuestas)) {
            Logger::info('recuperar.sin_preguntas', ['correo' => $correo, 'ip' => $request->ip()]);
            flash('error', 'No se encontró una cuenta activa con ese correo o no tiene preguntas de seguridad configuradas.');
            return $this->redirect('/recuperar');
        }

        // Guardar en sesión para los pasos siguientes
        $_SESSION['recovery_user_id'] = (int) $user['usuario_id'];
        $_SESSION['recovery_cedula'] = $user['cedula'] ?? '';
        $_SESSION['recovery_preguntas'] = $respuestas;
        unset($_SESSION['recovery_attempts']);

        Logger::info('recuperar.paso1_ok', ['correo' => $correo, 'ip' => $request->ip()]);
        return $this->redirect('/recuperar/preguntas');
    }

    // ──────────────────────────────────────────────
    // Recuperación — Paso 2: Preguntas de seguridad
    // ──────────────────────────────────────────────
    public function showPreguntas(Request $request): Response
    {
        if (empty($_SESSION['recovery_user_id']) || empty($_SESSION['recovery_preguntas'])) {
            flash('error', 'Debes iniciar el proceso de recuperación desde el paso 1.');
            return $this->redirect('/recuperar');
        }

        return $this->view('public.recuperar_preguntas', [
            'title'      => 'Verificar identidad - ' . config('app.name'),
            'hideHeader' => true,
            'hideFooter' => true,
            'preguntas'  => $_SESSION['recovery_preguntas'],
        ], 'public');
    }

    public function verificarPreguntas(Request $request): Response
    {
        if (empty($_SESSION['recovery_user_id']) || empty($_SESSION['recovery_preguntas'])) {
            flash('error', 'Sesión de recuperación expirada. Intenta de nuevo.');
            return $this->redirect('/recuperar');
        }

        // Rate limit: máximo 3 intentos
        $attempts = $_SESSION['recovery_attempts'] ?? 0;
        if ($attempts >= 3) {
            unset($_SESSION['recovery_user_id'], $_SESSION['recovery_preguntas'], $_SESSION['recovery_cedula'], $_SESSION['recovery_attempts']);
            Logger::warning('recuperar.bloqueado', ['ip' => $request->ip()]);
            flash('error', 'Has superado el número máximo de intentos. Inicia el proceso nuevamente.');
            return $this->redirect('/recuperar');
        }

        $preguntas = $_SESSION['recovery_preguntas'];
        $allCorrect = true;

        foreach ($preguntas as $i => $pregunta) {
            $respuestaInput = strtolower(trim((string) $request->input('respuesta_' . ($i + 1), '')));
            if (empty($respuestaInput) || !password_verify($respuestaInput, $pregunta['respuesta'])) {
                $allCorrect = false;
                break;
            }
        }

        if (!$allCorrect) {
            $_SESSION['recovery_attempts'] = $attempts + 1;
            $remaining = 3 - ($attempts + 1);
            Logger::warning('recuperar.respuestas_incorrectas', [
                'user_id' => $_SESSION['recovery_user_id'],
                'ip' => $request->ip(),
                'intentos_restantes' => $remaining,
            ]);

            // Si se acabaron los intentos, bloquear inmediatamente
            if ($remaining <= 0) {
                unset($_SESSION['recovery_user_id'], $_SESSION['recovery_preguntas'], $_SESSION['recovery_cedula'], $_SESSION['recovery_attempts']);
                flash('error', 'Has superado el número máximo de intentos. Inicia el proceso nuevamente.');
                return $this->redirect('/recuperar');
            }

            $palabra = $remaining === 1 ? 'intento' : 'intentos';
            flash('error', "Las respuestas no coinciden. Te quedan $remaining $palabra.");
            return $this->redirect('/recuperar/preguntas');
        }

        // Verificación exitosa
        $_SESSION['recovery_verified'] = true;
        unset($_SESSION['recovery_attempts']);
        Logger::info('recuperar.paso2_ok', ['user_id' => $_SESSION['recovery_user_id'], 'ip' => $request->ip()]);
        return $this->redirect('/recuperar/nueva-clave');
    }

    // ──────────────────────────────────────────────
    // Recuperación — Paso 3: Nueva contraseña
    // ──────────────────────────────────────────────
    public function showNuevaClave(Request $request): Response
    {
        if (empty($_SESSION['recovery_verified'])) {
            flash('error', 'Debes verificar tu identidad primero.');
            return $this->redirect('/recuperar');
        }

        return $this->view('public.recuperar_clave', [
            'title'      => 'Nueva contraseña - ' . config('app.name'),
            'hideHeader' => true,
            'hideFooter' => true,
        ], 'public');
    }

    public function cambiarClave(Request $request): Response
    {
        if (empty($_SESSION['recovery_verified']) || empty($_SESSION['recovery_user_id'])) {
            flash('error', 'Sesión de recuperación expirada. Intenta de nuevo.');
            return $this->redirect('/recuperar');
        }

        $password = (string) $request->input('password', '');
        $confirm  = (string) $request->input('password_confirm', '');

        // Validaciones
        $errors = [];
        if (strlen($password) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if (!preg_match('/[A-Za-z]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos una letra.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos un número.';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'La contraseña debe contener al menos un símbolo especial.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        // No permitir que la contraseña sea el número de documento de identidad
        $cedula = $_SESSION['recovery_cedula'] ?? '';
        $pwdClean = preg_replace('/[^0-9A-Za-z]/', '', $password);
        $cedClean = str_contains($cedula, '-') ? str_replace('.', '', explode('-', $cedula, 2)[1] ?? '') : preg_replace('/[^0-9]/', '', $cedula);
        if ($cedClean !== '' && $pwdClean === $cedClean) {
            $errors[] = 'La contraseña no puede ser igual a tu número de documento de identidad.';
        }

        if (!empty($errors)) {
            flash('error', implode(' ', $errors));
            return $this->redirect('/recuperar/nueva-clave');
        }

        // Actualizar la contraseña
        $userId = (int) $_SESSION['recovery_user_id'];
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $db = \App\Core\Database::connection();
        $db->prepare('UPDATE usuarios SET contrasena = ? WHERE usuario_id = ?')
           ->execute([$hash, $userId]);

        // Limpiar sesión de recuperación
        unset(
            $_SESSION['recovery_user_id'],
            $_SESSION['recovery_cedula'],
            $_SESSION['recovery_preguntas'],
            $_SESSION['recovery_verified'],
            $_SESSION['recovery_attempts']
        );

        Logger::audit('recuperar.clave_cambiada', ['user_id' => $userId, 'ip' => $request->ip()]);
        flash('success', '¡Contraseña actualizada correctamente! Ya puedes iniciar sesión.');
        return $this->redirect('/login');
    }

    public function keepAlive(Request $request): Response
    {
        // Usamos userWithGrace para permitir renovar tokens recién expirados
        // (dentro de un período de gracia de 3 minutos)
        $user = Auth::userWithGrace(180);
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'Sesión expirada.'], 401);
        }
        Auth::setCookie($user);
        $_SESSION['_last_activity'] = time();
        return $this->json(['success' => true, 'message' => 'Sesión extendida.']);
    }
}
