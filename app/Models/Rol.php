<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Rol extends Model
{
    protected string $table = 'roles_usuarios';
    protected string $primaryKey = 'rol_id';

    /**
     * Trae todos los roles activos ordenados por nombre.
     */
    public function allActive(): array
    {
        return $this->query('SELECT * FROM roles_usuarios ORDER BY nombre_rol');
    }

    /**
     * Retorna los roles que un usuario puede asignar según su propio rol.
     */
    public function allowedRolesFor(int $currentUserRolId): array
    {
        if ($currentUserRolId === ROL_SUPERUSER) {
            return $this->allActive();
        }
        return $this->query(
            'SELECT * FROM roles_usuarios WHERE rol_id IN (?, ?, ?) ORDER BY nombre_rol',
            [ROL_ADMIN, ROL_ENTRENADOR, ROL_DIRECTIVO]
        );
    }
}
