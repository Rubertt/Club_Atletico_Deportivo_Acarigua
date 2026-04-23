<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la tabla `representante` de cada_db.
 *
 * Columnas: representante_id, nombre_completo, telefono, cedula,
 *           tipo_relacion (enum), direccion_id, foto, created_at, updated_at
 */
final class Representante extends Model
{
    protected string $table = 'representante';
    protected string $primaryKey = 'representante_id';

    public function findByCedula(string $cedula): ?array
    {
        return $this->queryOne(
            'SELECT * FROM representante WHERE cedula = :c LIMIT 1',
            [':c' => $cedula]
        );
    }
}
