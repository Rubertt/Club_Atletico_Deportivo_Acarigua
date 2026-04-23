<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la tabla `personal` de cada_db.
 *
 * En el proyecto viejo se llama `personal`, no `plantel`.
 * Columnas: personal_id, email_id (FK→usuarios.email), nombre, apellido,
 *           cedula, telefono, fecha_nac, direccion_id, rol_personal, foto
 */
final class Personal extends Model
{
    protected string $table = 'personal';
    protected string $primaryKey = 'personal_id';

    public function allWithRol(): array
    {
        return $this->query(
            "SELECT p.*, r.nombre_rol
             FROM personal p
             JOIN rol_usuarios r ON r.rol_id = p.rol_personal
             ORDER BY p.apellido, p.nombre"
        );
    }

    public function entrenadores(): array
    {
        return $this->query(
            'SELECT personal_id, nombre, apellido FROM personal
             WHERE rol_personal = :r ORDER BY apellido, nombre',
            [':r' => ROL_ENTRENADOR]
        );
    }
}
