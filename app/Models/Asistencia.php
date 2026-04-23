<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la tabla `asistencias` de cada_db.
 *
 * Columnas: asistencia_id, actividad_id, atleta_id, estatus, observaciones
 */
final class Asistencia extends Model
{
    protected string $table = 'asistencias';
    protected string $primaryKey = 'asistencia_id';

    public function resumenAtleta(int $atletaId, ?string $desde = null, ?string $hasta = null): array
    {
        $where = 'WHERE a.atleta_id = :a';
        $bindings = [':a' => $atletaId];
        if ($desde) { $where .= ' AND act.fecha >= :desde'; $bindings[':desde'] = $desde; }
        if ($hasta) { $where .= ' AND act.fecha <= :hasta'; $bindings[':hasta'] = $hasta; }

        return $this->query(
            "SELECT a.estatus, COUNT(*) AS total
             FROM asistencias a
             JOIN actividades act ON act.actividad_id = a.actividad_id
             $where
             GROUP BY a.estatus",
            $bindings
        );
    }
}
