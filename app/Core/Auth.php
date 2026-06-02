<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use Throwable;

/**
 * Autenticación adaptada a la tabla `usuarios` de cada_db.
 *
 * Estructura de la tabla (nueva BD):
 *   - usuario_id   INT UNSIGNED  PK AUTO_INCREMENT
 *   - correo       VARCHAR(50)
 *   - contrasena   VARCHAR(255)  bcrypt hash
 *   - token        VARCHAR(500)
 *   - rol_id       TINYINT       FK → roles_usuarios.rol_id
 *   - estatus      ENUM('Activo','Inactivo')
 *   - nombre       VARCHAR(30)
 *   - apellido     VARCHAR(30)
 *   - cedula       VARCHAR(12)
 *   - telefono     VARCHAR(15)
 *   - fecha_nac    DATE
 *   - direccion_id BIGINT UNSIGNED FK → direcciones
 *   - foto         VARCHAR(255)
 *   - ultimo_acceso DATETIME
 */
final class Auth
{
    private static ?array $user = null;
    private static bool $attempted = false;

    /**
     * Autentica con correo/contraseña. Devuelve el usuario y setea cookie JWT.
     */
    public static function attempt(string $email, string $password): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare(
            'SELECT u.usuario_id, u.correo, u.contrasena, u.rol_id, u.estatus,
                    u.foto, u.nombre, u.apellido, u.cedula, r.nombre_rol
             FROM usuarios u
             JOIN roles_usuarios r ON r.rol_id = u.rol_id
             WHERE u.correo = :correo
             LIMIT 1'
        );
        $stmt->execute([':correo' => $email]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        if ($row['estatus'] !== 'Activo') {
            throw new RuntimeException('Usuario inactivo. Contacta a la directiva.');
        }
        if (!password_verify($password, (string) $row['contrasena'])) {
            return null;
        }

        $db->prepare('UPDATE usuarios SET ultimo_acceso = NOW() WHERE usuario_id = :id')
            ->execute([':id' => $row['usuario_id']]);

        unset($row['contrasena']);
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
        // El tiempo_sesion de DB está en minutos, lo convertimos a segundos. Fallback al config.
        $ttl = (int) (config_db('tiempo_sesion', config('auth.jwt.ttl') / 60) * 60);

        $now = time();
        $token = JWT::encode([
            'iss'   => (string) config('auth.jwt.issuer'),
            'iat'   => $now,
            'exp'   => $now + $ttl,
            'sub'   => (string) $user['usuario_id'],
            'correo'=> $user['correo'],
            'rol'   => (int) $user['rol_id'],
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
            'SELECT u.usuario_id, u.correo, u.rol_id, u.estatus, u.foto,
                    u.nombre, u.apellido, u.cedula, r.nombre_rol
             FROM usuarios u
             JOIN roles_usuarios r ON r.rol_id = u.rol_id
             WHERE u.usuario_id = :id AND u.estatus = "Activo"
             LIMIT 1'
        );
        $stmt->execute([':id' => $payload['sub'] ?? '']);
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

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int) $u['usuario_id'] : null;
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
        return (int) $u['rol_id'] === (int) $role;
    }

    public static function isAdmin(): bool
    {
        return self::hasRole(ROL_ADMIN) || self::hasRole(ROL_SUPERUSER) || self::hasRole(ROL_DIRECTIVO);
    }

    public static function isEntrenador(): bool
    {
        return self::hasRole(ROL_ENTRENADOR);
    }

    /** Para tests o flujos especiales. */
    public static function setUser(?array $user): void
    {
        self::$user = $user;
        self::$attempted = true;
    }
}
