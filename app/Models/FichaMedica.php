<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class FichaMedica extends Model
{
    protected string $table = 'ficha_medica';
    protected string $primaryKey = 'ficha_id';

    public function byAtleta(int $atletaId): ?array
    {
        return $this->queryOne(
            'SELECT * FROM ficha_medica WHERE atleta_id = :a LIMIT 1',
            [':a' => $atletaId]
        );
    }
}
