<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la tabla `consulta_medica`.
 */
final class ConsultaMedica extends Model
{
    protected string $table = 'consulta_medica';
    protected string $primaryKey = 'consulta_id';

    /**
     * Obtiene el historial de consultas médicas de un atleta específico,
     * incluyendo el nombre del usuario que registró cada consulta.
     */
    public function byAtleta(int $atletaId): array
    {
        return $this->query("
            SELECT cm.*, u.nombre AS usuario_nombre, u.apellido AS usuario_apellido
            FROM `consulta_medica` cm
            LEFT JOIN `usuarios` u ON u.usuario_id = cm.usuario_id
            WHERE cm.atleta_id = :atleta_id
            ORDER BY cm.creado_en DESC, cm.actualizado_en DESC, cm.consulta_id DESC
        ", [':atleta_id' => $atletaId]);
    }
}
