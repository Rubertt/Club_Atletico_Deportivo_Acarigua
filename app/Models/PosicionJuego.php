<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class PosicionJuego extends Model
{
    protected string $table = 'posicion_juego';
    protected string $primaryKey = 'posicion_id';
}
