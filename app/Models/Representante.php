<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la tabla `representante` de cada_db.
 *
 * Columnas: representante_id, nombre, apellido, telefono, cedula,
 *           tipo_relacion (enum), created_at, updated_at
 */
final class Representante extends Model
{
    protected string $table = 'representantes';
    protected string $primaryKey = 'representante_id';

    public function findByCedula(string $cedula): ?array
    {
        return $this->queryOne(
            'SELECT * FROM representantes WHERE cedula = :c LIMIT 1',
            [':c' => $cedula]
        );
    }
}
