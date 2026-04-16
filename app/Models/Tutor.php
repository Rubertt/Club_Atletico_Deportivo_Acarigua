<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Tutor extends Model
{
    protected string $table = 'tutor';
    protected string $primaryKey = 'tutor_id';

    public function findByCedula(string $cedula): ?array
    {
        return $this->queryOne('SELECT * FROM tutor WHERE cedula = :c LIMIT 1', [':c' => $cedula]);
    }
}
