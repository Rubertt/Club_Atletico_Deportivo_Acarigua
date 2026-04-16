<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class DetalleAsistencia extends Model
{
    protected string $table = 'detalle_asistencia';
    protected string $primaryKey = 'asistencia_id';

    public function resumenAtleta(int $atletaId, ?string $desde = null, ?string $hasta = null): array
    {
        $where = 'WHERE da.atleta_id = :a';
        $bindings = [':a' => $atletaId];
        if ($desde) { $where .= ' AND ev.fecha_evento >= :desde'; $bindings[':desde'] = $desde; }
        if ($hasta) { $where .= ' AND ev.fecha_evento <= :hasta'; $bindings[':hasta'] = $hasta; }

        return $this->query(
            "SELECT da.estatus, COUNT(*) AS total
             FROM detalle_asistencia da
             JOIN evento_deportivo ev ON ev.evento_id = da.evento_id
             $where
             GROUP BY da.estatus",
            $bindings
        );
    }
}
