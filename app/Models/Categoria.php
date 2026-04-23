<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la tabla `categoria` de cada_db.
 *
 * La FK entrenador_id apunta a personal.personal_id (no a plantel).
 */
final class Categoria extends Model
{
    protected string $table = 'categoria';
    protected string $primaryKey = 'categoria_id';

    public function allWithEntrenador(): array
    {
        return $this->query(
            "SELECT c.*,
                    CONCAT_WS(' ', p.nombre, p.apellido) AS entrenador,
                    (SELECT COUNT(*) FROM atletas a WHERE a.categoria_id = c.categoria_id) AS total_atletas
             FROM categoria c
             LEFT JOIN personal p ON p.personal_id = c.entrenador_id
             ORDER BY c.edad_min"
        );
    }

    public function activas(): array
    {
        return $this->query(
            "SELECT categoria_id, nombre_categoria FROM categoria WHERE estatus = 'Activa' ORDER BY edad_min"
        );
    }
}
