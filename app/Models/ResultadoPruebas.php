<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class ResultadoPruebas extends Model
{
    protected string $table = 'resultado_pruebas';
    protected string $primaryKey = 'test_id';

    public function historial(int $atletaId): array
    {
        return $this->query(
            'SELECT rp.*, ev.fecha_evento, ev.tipo_evento
             FROM resultado_pruebas rp
             JOIN evento_deportivo ev ON ev.evento_id = rp.evento_id
             WHERE rp.atleta_id = :a
             ORDER BY ev.fecha_evento DESC',
            [':a' => $atletaId]
        );
    }
}
