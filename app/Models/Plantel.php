<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Plantel extends Model
{
    protected string $table = 'plantel';
    protected string $primaryKey = 'plantel_id';

    public function allWithRol(): array
    {
        return $this->query(
            'SELECT p.*, r.nombre_rol
             FROM plantel p
             JOIN rol_usuarios r ON r.rol_id = p.rol_id
             ORDER BY p.apellido, p.nombre'
        );
    }

    public function entrenadores(): array
    {
        return $this->query(
            'SELECT plantel_id, nombre, apellido FROM plantel
             WHERE rol_id = :r ORDER BY apellido, nombre',
            [':r' => ROL_ENTRENADOR]
        );
    }
}
