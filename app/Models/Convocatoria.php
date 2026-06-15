<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

final class Convocatoria extends Model
{
    protected string $table = 'convocatorias';
    protected string $primaryKey = 'convocatoria_id';

    /**
     * Registra un partido (actividad tipo 0) y sus convocatorias de forma transaccional.
     */
    public function registrarLote(array $actividadData, array $convocadosData): int
    {
        $db = $this->db();
        $db->beginTransaction();
        try {
            // 1. Insertar en actividades
            $actividadModel = new Actividad();
            $actividadId = $actividadModel->insert($actividadData);

            // 2. Insertar cada convocatoria
            $stmt = $db->prepare(
                'INSERT INTO convocatorias (actividad_id, atleta_id, estatus)
                 VALUES (:actividad_id, :atleta_id, :estatus)'
            );

            foreach ($convocadosData as $c) {
                $stmt->execute([
                    ':actividad_id' => $actividadId,
                    ':atleta_id'    => (int)$c['atleta_id'],
                    ':estatus'      => (int)$c['estatus'],
                ]);
            }

            $db->commit();
            return $actividadId;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Actualiza un partido (actividad) y sus convocatorias (pase de asistencia) de forma transaccional.
     */
    public function actualizarLote(int $actividadId, array $actividadData, array $convocadosData): void
    {
        $db = $this->db();
        $db->beginTransaction();
        try {
            // 1. Actualizar actividad
            $actividadModel = new Actividad();
            $actividadModel->update($actividadId, $actividadData);

            // 2. Eliminar convocatorias anteriores de esta actividad
            $stmtDel = $db->prepare('DELETE FROM convocatorias WHERE actividad_id = ?');
            $stmtDel->execute([$actividadId]);

            // 3. Insertar nuevas convocatorias
            $stmt = $db->prepare(
                'INSERT INTO convocatorias (actividad_id, atleta_id, estatus, asistencia)
                 VALUES (:actividad_id, :atleta_id, :estatus, :asistencia)'
            );

            foreach ($convocadosData as $c) {
                $stmt->execute([
                    ':actividad_id' => $actividadId,
                    ':atleta_id'    => (int)$c['atleta_id'],
                    ':estatus'      => (int)$c['estatus'],
                    ':asistencia'   => isset($c['asistencia']) && $c['asistencia'] !== null ? (int)$c['asistencia'] : null,
                ]);
            }

            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Calcula el porcentaje de asistencia de un atleta en los últimos 30 días.
     */
    public function obtenerAsistenciaMensual(int $atletaId): float
    {
        $sql = "SELECT 
                    COUNT(CASE WHEN ast.estatus = 1 THEN 1 END) AS presentes,
                    COUNT(ast.asistencia_id) AS total
                FROM asistencias ast
                JOIN actividades act ON ast.actividad_id = act.actividad_id
                WHERE ast.atleta_id = :atleta_id
                  AND act.fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                  AND act.tipo_actividad IN (0, 1)";
        
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([':atleta_id' => $atletaId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$row || (int)$row['total'] === 0) {
            return 0.0;
        }
        
        return round(((int)$row['presentes'] / (int)$row['total']) * 100, 1);
    }

    /**
     * Calcula el promedio general del atleta en su test físico más reciente.
     */
    public function obtenerPromedioFisico(int $atletaId): float
    {
        $rp = new ResultadoPrueba();
        $historial = $rp->historial($atletaId);
        
        if (empty($historial)) {
            return 0.0;
        }
        
        $reciente = $historial[0]; // El más reciente ordenado por fecha desc
        
        $sum = 0.0;
        $count = 0;
        
        foreach (['test_de_fuerza', 'test_resistencia', 'test_velocidad', 'test_coordinacion', 'test_de_reaccion'] as $field) {
            if (isset($reciente[$field]) && $reciente[$field] !== null) {
                $sum += (float)$reciente[$field];
                $count++;
            }
        }
        
        return $count > 0 ? round($sum / $count, 1) : 0.0;
    }
}
