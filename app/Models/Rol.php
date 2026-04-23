<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Rol extends Model
{
    protected string $table = 'rol_usuarios';
    protected string $primaryKey = 'rol_id';

    /**
     * Trae roles activos, excluyendo al Médico (ID 4) por solicitud.
     */
    public function allActive(): array
    {
        return $this->query('SELECT * FROM rol_usuarios WHERE rol_id != 4 ORDER BY nombre_rol');
    }
}
