<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la tabla `usuarios` de cada_db.
 *
 * PK: usuario_id (INT UNSIGNED AUTO_INCREMENT).
 * Columnas: usuario_id, correo, contrasena, token, rol_id, estatus,
 *           nombre, apellido, cedula, telefono, fecha_nac,
 *           direccion_id, foto, ultimo_acceso, creado_en, actualizado_en
 */
final class Usuario extends Model
{
    protected string $table = 'usuarios';
    protected string $primaryKey = 'usuario_id';

    /**
     * Lista todos los usuarios con su rol.
     */
    public function allWithRol(): array
    {
        return $this->query(
            'SELECT u.usuario_id, u.correo, u.nombre, u.apellido, u.cedula,
                    u.telefono, u.estatus, u.foto, u.ultimo_acceso, u.creado_en,
                    r.nombre_rol, u.rol_id
             FROM usuarios u
             JOIN roles_usuarios r ON r.rol_id = u.rol_id
             ORDER BY u.apellido, u.nombre'
        );
    }

    /**
     * Lista los usuarios con rol de entrenador.
     */
    public function entrenadores(): array
    {
        return $this->query(
            'SELECT usuario_id, nombre, apellido FROM usuarios
             WHERE rol_id IN (:r_entrenador, :r_admin) AND estatus = "Activo"
             ORDER BY apellido, nombre',
            [
                ':r_entrenador' => ROL_ENTRENADOR,
                ':r_admin' => ROL_ADMIN
            ]
        );
    }

    /**
     * Lista los usuarios activos con rol de entrenador o directivo para coordinar categorías.
     */
    public function coordinadoresCategorias(): array
    {
        return $this->query(
            'SELECT usuario_id, nombre, apellido FROM usuarios
             WHERE rol_id IN (:r_entrenador, :r_directivo) AND estatus = "Activo"
             ORDER BY apellido, nombre',
            [
                ':r_entrenador' => ROL_ENTRENADOR,
                ':r_directivo' => ROL_DIRECTIVO
            ]
        );
    }

    /**
     * Trae un usuario con toda su información de dirección (JOINs cascada).
     */
    public function findCompleto(int $id): ?array
    {
        $sql = "
            SELECT u.*, r.nombre_rol,
                   d.parroquias_id, d.localidad, d.tipo_vivienda, d.ubicacion_vivienda,
                   pa.parroquia AS parroquia_nombre,
                   m.municipio AS municipio_nombre,
                   e.estado AS estado_nombre,
                   pa.municipio_id, m.estado_id
            FROM usuarios u
            LEFT JOIN roles_usuarios r ON r.rol_id = u.rol_id
            LEFT JOIN direcciones d ON d.direccion_id = u.direccion_id
            LEFT JOIN parroquias pa ON pa.parroquia_id = d.parroquias_id
            LEFT JOIN municipios m ON m.municipio_id = pa.municipio_id
            LEFT JOIN estados e ON e.estado_id = m.estado_id
            WHERE u.usuario_id = :id
            LIMIT 1
        ";
        return $this->queryOne($sql, [':id' => $id]);
    }

    /**
     * Busca un usuario por correo electrónico (para recuperación de contraseña).
     */
    public function findByCorreo(string $correo): ?array
    {
        return $this->queryOne(
            'SELECT usuario_id, correo, nombre, apellido, cedula, estatus
             FROM usuarios WHERE correo = ? LIMIT 1',
            [$correo]
        );
    }
}
