<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class Rol extends Model
{
    protected string $table = 'rol_usuarios';
    protected string $primaryKey = 'rol_id';
}
