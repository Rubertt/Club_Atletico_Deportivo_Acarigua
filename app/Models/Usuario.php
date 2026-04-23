<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la tabla `usuarios` de cada_db.
 *
 * PK: email (VARCHAR 100), no tiene usuario_id autoincrement.
 * Columnas: email, password, token, rol (FK), estatus, foto, ultimo_acceso, created_at, updated_at
 */
final class Usuario extends Model
{
    protected string $table = 'usuarios';
    protected string $primaryKey = 'email';

    public function allWithRol(): array
    {
        return $this->query(
            'SELECT u.email, u.estatus, u.ultimo_acceso, u.created_at,
                    r.nombre_rol, u.rol
             FROM usuarios u
             JOIN rol_usuarios r ON r.rol_id = u.rol
             ORDER BY u.email'
        );
    }
}
