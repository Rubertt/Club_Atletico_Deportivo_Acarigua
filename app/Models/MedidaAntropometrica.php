<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class MedidaAntropometrica extends Model
{
    protected string $table = 'medidas_antropometricas';
    protected string $primaryKey = 'medidas_id';

    public function historial(int $atletaId): array
    {
        return $this->query(
            'SELECT * FROM medidas_antropometricas WHERE atleta_id = :a ORDER BY fecha_medicion ASC',
            [':a' => $atletaId]
        );
    }

    public function ultima(int $atletaId): ?array
    {
        return $this->queryOne(
            'SELECT * FROM medidas_antropometricas WHERE atleta_id = :a ORDER BY fecha_medicion DESC LIMIT 1',
            [':a' => $atletaId]
        );
    }
}
