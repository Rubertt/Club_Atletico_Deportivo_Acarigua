<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la tabla `actividades` de cada_db.
 *
 * Columnas: actividad_id, tipo_actividad (tinyint), objetivo_principal,
 *           fecha, hora_inicio, hora_fin, ubicacion, clima, estatus, micro_id
 */
final class Actividad extends Model
{
    protected string $table = 'actividades';
    protected string $primaryKey = 'actividad_id';
}
