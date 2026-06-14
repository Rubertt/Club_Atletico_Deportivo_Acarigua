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

    /**
     * Valida que no se registren más de 3 consultas por día para un mismo atleta.
     * Utiliza un contador dentro de un ciclo para cumplir con el requerimiento.
     */
    public function validarLimiteConsultas(int $atletaId, string $fechaSuceso, ?int $excluirConsultaId = null): bool
    {
        $consultas = $this->query(
            "SELECT consulta_id, fecha_suceso FROM `consulta_medica` WHERE atleta_id = :atleta_id",
            [':atleta_id' => $atletaId]
        );

        $contador = 0;
        foreach ($consultas as $row) {
            if ($excluirConsultaId !== null && (int)$row['consulta_id'] === $excluirConsultaId) {
                continue;
            }
            if ($row['fecha_suceso'] === $fechaSuceso) {
                $contador++;
            }
        }

        return $contador < 3;
    }
}
