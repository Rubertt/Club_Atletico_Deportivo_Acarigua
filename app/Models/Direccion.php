<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Direccion extends Model
{
    protected string $table = 'direcciones';
    protected string $primaryKey = 'direccion_id';
}
