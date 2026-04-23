<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use Throwable;

/**
 * Autenticación adaptada a la tabla `usuarios` de cada_db.
 *
 * Estructura de la tabla:
 *   - email       VARCHAR(100)  PK
 *   - password    VARCHAR(255)  bcrypt hash
 *   - token       VARCHAR(500)  (legacy, no usado por PHP)
 *   - rol         INT           FK → rol_usuarios.rol_id
 *   - estatus     ENUM('Activo','Inactivo')
 *   - foto        VARCHAR(255)
 *   - ultimo_acceso DATETIME
 */
final class Auth
{
    private static ?array $user = null;
    private static bool $attempted = false;

    /**
     * Autentica con email/password. Devuelve el usuario y setea cookie JWT.
     */
    public static function attempt(string $email, string $password): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            'SELECT u.email, u.password, u.rol, u.estatus, u.foto, r.nombre_rol
             FROM usuarios u
             JOIN rol_usuarios r ON r.rol_id = u.rol
             WHERE u.email = :email
             LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        if ($row['estatus'] !== 'Activo') {
            throw new RuntimeException('Usuario inactivo. Contacta a la directiva.');
        }
        if (!password_verify($password, (string) $row['password'])) {
            return null;
        }

        $db->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE email = :email')
            ->execute([':email' => $row['email']]);

        unset($row['password']);
        self::$user = $row;
        self::setCookie($row);
        return $row;
    }

    /**
     * Genera y emite la cookie JWT con los datos del usuario.
     */
    public static function setCookie(array $user): void
    {
        $cfg = config('auth.cookie');
        $ttl = (int) config('auth.jwt.ttl');

        $now = time();
        $token = JWT::encode([
            'iss'   => (string) config('auth.jwt.issuer'),
            'iat'   => $now,
            'exp'   => $now + $ttl,
            'sub'   => $user['email'],
            'rol'   => (int) $user['rol'],
            'rol_name' => $user['nombre_rol'] ?? null,
        ]);

        setcookie($cfg['name'], $token, [
            'expires'  => $now + $ttl,
            'path'     => $cfg['path'],
            'domain'   => $cfg['domain'],
            'secure'   => (bool) $cfg['secure'],
            'httponly' => (bool) $cfg['httponly'],
            'samesite' => (string) $cfg['samesite'],
        ]);
    }

    public static function logout(): void
    {
        $cfg = config('auth.cookie');
        setcookie($cfg['name'], '', [
            'expires'  => time() - 3600,
            'path'     => $cfg['path'],
            'domain'   => $cfg['domain'],
            'secure'   => (bool) $cfg['secure'],
            'httponly' => (bool) $cfg['httponly'],
            'samesite' => (string) $cfg['samesite'],
        ]);
        self::$user = null;
        session_unset();
        session_regenerate_id(true);
    }

    /**
     * Obtiene el usuario actual desde la cookie JWT, o null si no autenticado.
     */
    public static function user(): ?array
    {
        if (self::$user !== null) {
            return self::$user;
        }
        if (self::$attempted) {
            return null;
        }
        self::$attempted = true;

        $name = (string) config('auth.cookie.name');
        $token = $_COOKIE[$name] ?? null;
        if (!$token) {
            return null;
        }

        try {
            $payload = JWT::decode($token);
        } catch (Throwable) {
            return null;
        }

        $db = Database::connection();
        $stmt = $db->prepare(
            'SELECT u.email, u.rol, u.estatus, u.foto, r.nombre_rol
             FROM usuarios u
             JOIN rol_usuarios r ON r.rol_id = u.rol
             WHERE u.email = :email AND u.estatus = "Activo"
             LIMIT 1'
        );
        $stmt->execute([':email' => $payload['sub'] ?? '']);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        self::$user = $row;
        return $row;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?string
    {
        $u = self::user();
        return $u ? $u['email'] : null;
    }

    public static function hasRole(int|string $role): bool
    {
        $u = self::user();
        if ($u === null) {
            return false;
        }
        if (is_string($role)) {
            $map = config('auth.roles') ?? [];
            $role = $map[$role] ?? 0;
        }
        return (int) $u['rol'] === (int) $role;
    }

    public static function isAdmin(): bool
    {
        return self::hasRole(ROL_ADMIN) || self::hasRole(ROL_SUPERUSER);
    }

    public static function isEntrenador(): bool
    {
        return self::hasRole(ROL_ENTRENADOR);
    }

    public static function isMedico(): bool
    {
        return self::hasRole(ROL_MEDICO);
    }

    /** Para tests o flujos especiales. */
    public static function setUser(?array $user): void
    {
        self::$user = $user;
        self::$attempted = true;
    }
}
