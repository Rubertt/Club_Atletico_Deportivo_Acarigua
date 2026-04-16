<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Usuario extends Model
{
    protected string $table = 'usuarios';
    protected string $primaryKey = 'usuario_id';

    public function allWithRol(): array
    {
        return $this->query(
            'SELECT u.usuario_id, u.email, u.estatus, u.ultimo_acceso, u.created_at,
                    r.nombre_rol, u.rol_id
             FROM usuarios u
             JOIN rol_usuarios r ON r.rol_id = u.rol_id
             ORDER BY u.email'
        );
    }
}
