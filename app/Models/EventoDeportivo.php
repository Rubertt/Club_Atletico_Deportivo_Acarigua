<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class EventoDeportivo extends Model
{
    protected string $table = 'evento_deportivo';
    protected string $primaryKey = 'evento_id';
}
