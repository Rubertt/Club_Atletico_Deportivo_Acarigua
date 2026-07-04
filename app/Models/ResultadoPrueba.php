<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la tabla `resultados_pruebas` de cada_db.
 */
final class ResultadoPrueba extends Model
{
    protected string $table = 'resultados_pruebas';
    protected string $primaryKey = 'test_id';

    public ?float $promedio = null;

    public function historial(int $atletaId): array
    {
        $rows = $this->query(
            'SELECT rp.*, act.fecha AS fecha_evento, act.tipo_actividad AS tipo_evento, act.usuario_id,
                    u.nombre AS nombre_entrenador, u.apellido AS apellido_entrenador, a.fecha_nac
             FROM resultados_pruebas rp
             JOIN actividades act ON act.actividad_id = rp.actividad_id
             LEFT JOIN usuarios u ON u.usuario_id = act.usuario_id
             JOIN atletas a ON a.atleta_id = rp.atleta_id
             WHERE rp.atleta_id = :a
             ORDER BY act.fecha DESC',
            [':a' => $atletaId]
        );

        foreach ($rows as &$row) {
            // Preservar valores crudos originales
            $row['test_de_fuerza_raw'] = $row['test_de_fuerza'] !== null ? (float)$row['test_de_fuerza'] : null;
            $row['test_resistencia_raw'] = $row['test_resistencia'] !== null ? (float)$row['test_resistencia'] : null;
            $row['test_velocidad_raw'] = $row['test_velocidad'] !== null ? (float)$row['test_velocidad'] : null;
            $row['test_coordinacion_raw'] = $row['test_coordinacion'] !== null ? (float)$row['test_coordinacion'] : null;
            $row['test_de_reaccion_raw'] = $row['test_de_reaccion'] !== null ? (float)$row['test_de_reaccion'] : null;

            if (!empty($row['fecha_nac'])) {
                $refDate = !empty($row['fecha_evento']) ? (string)$row['fecha_evento'] : null;
                $puntajes = $this->calcularPuntajes($row, (string)$row['fecha_nac'], $refDate);
                $row['test_de_fuerza'] = $puntajes['test_de_fuerza'];
                $row['test_resistencia'] = $puntajes['test_resistencia'];
                $row['test_velocidad'] = $puntajes['test_velocidad'];
                $row['test_coordinacion'] = $puntajes['test_coordinacion'];
                $row['test_de_reaccion'] = $puntajes['test_de_reaccion'];

                $puntajesNac = $this->calcularPuntajesNacionales($row, (string)$row['fecha_nac'], $refDate);
                $row['test_de_fuerza_nac'] = $puntajesNac['test_de_fuerza'];
                $row['test_resistencia_nac'] = $puntajesNac['test_resistencia'];
                $row['test_velocidad_nac'] = $puntajesNac['test_velocidad'];
                $row['test_coordinacion_nac'] = $puntajesNac['test_coordinacion'];
                $row['test_de_reaccion_nac'] = $puntajesNac['test_de_reaccion'];
            } else {
                $row['test_de_fuerza_nac'] = null;
                $row['test_resistencia_nac'] = null;
                $row['test_velocidad_nac'] = null;
                $row['test_coordinacion_nac'] = null;
                $row['test_de_reaccion_nac'] = null;
            }
        }
        unset($row);

        return $rows;
    }

    public function calcularEdad(string $fechaNac, ?string $fechaReferencia = null): int
    {
        try {
            $birthDate = new \DateTime($fechaNac);
            $refDate = new \DateTime($fechaReferencia ?? 'today');
            return $birthDate->diff($refDate)->y;
        } catch (\Throwable $e) {
            return 20;
        }
    }

    public function obtenerFactorExigencia(int $edad): float
    {
        return match (true) {
            $edad <= 6  => 0.40,
            $edad <= 9  => 0.55,
            $edad <= 12 => 0.70,
            $edad <= 15 => 0.85,
            $edad <= 18 => 0.95,
            $edad <= 40 => 1.00,
            $edad <= 49 => 0.85,
            $edad <= 59 => 0.70,
            $edad <= 69 => 0.55,
            default     => 0.40,
        };
    }

    private function calcularDirecta(float $resultado, float $minBase, float $maxBase, float $factor): float
    {
        $minAdaptado = $minBase * $factor;
        $maxAdaptado = $maxBase * $factor;
        if (abs($maxAdaptado - $minAdaptado) < 0.0001) {
            return 0.0;
        }
        $score = (($resultado - $minAdaptado) / ($maxAdaptado - $minAdaptado)) * 100;
        return max(0.0, min(100.0, round($score, 1)));
    }

    private function calcularInversa(float $resultado, float $minBase, float $maxBase, float $factor): float
    {
        if ($factor == 0.0) {
            return 0.0;
        }
        $minAdaptado = $minBase / $factor;
        $maxAdaptado = $maxBase / $factor;
        if (abs($minAdaptado - $maxAdaptado) < 0.0001) {
            return 0.0;
        }
        $score = (($minAdaptado - $resultado) / ($minAdaptado - $maxAdaptado)) * 100;
        return max(0.0, min(100.0, round($score, 1)));
    }

    private function calcularPuntajes(array $row, string $fechaNac, ?string $fechaReferencia = null): array
    {
        $edad = $this->calcularEdad($fechaNac, $fechaReferencia);
        $factor = $this->obtenerFactorExigencia($edad);

        return [
            'test_de_fuerza'    => $row['test_de_fuerza'] !== null ? $this->calcularDirecta((float)$row['test_de_fuerza'], 20.0, 45.0, $factor) : null,
            'test_resistencia'  => $row['test_resistencia'] !== null ? $this->calcularDirecta((float)$row['test_resistencia'], 600.0, 2200.0, $factor) : null,
            'test_velocidad'    => $row['test_velocidad'] !== null ? $this->calcularInversa((float)$row['test_velocidad'], 5.20, 4.10, $factor) : null,
            'test_coordinacion' => $row['test_coordinacion'] !== null ? $this->calcularInversa((float)$row['test_coordinacion'], 22.50, 16.50, $factor) : null,
            'test_de_reaccion'  => $row['test_de_reaccion'] !== null ? $this->calcularInversa((float)$row['test_de_reaccion'], 450.0, 220.0, $factor) : null,
        ];
    }

    public function calcularPuntajesNacionales(array $row, string $fechaNac, ?string $fechaReferencia = null): array
    {
        $edad = $this->calcularEdad($fechaNac, $fechaReferencia);
        $factor = $this->obtenerFactorExigencia($edad);

        return [
            'test_de_fuerza'    => $row['test_de_fuerza_raw'] !== null ? $this->calcularDirecta((float)$row['test_de_fuerza_raw'], 20.0, 41.5, $factor) : null,
            'test_resistencia'  => $row['test_resistencia_raw'] !== null ? $this->calcularDirecta((float)$row['test_resistencia_raw'], 600.0, 1880.0, $factor) : null,
            'test_velocidad'    => $row['test_velocidad_raw'] !== null ? $this->calcularInversa((float)$row['test_velocidad_raw'], 5.20, 4.24, $factor) : null,
            'test_coordinacion' => $row['test_coordinacion_raw'] !== null ? $this->calcularInversa((float)$row['test_coordinacion_raw'], 22.50, 17.10, $factor) : null,
            'test_de_reaccion'  => $row['test_de_reaccion_raw'] !== null ? $this->calcularInversa((float)$row['test_de_reaccion_raw'], 450.0, 260.0, $factor) : null,
        ];
    }

    public function calcularPromedioNacional(array $row, string $fechaNac, ?string $fechaReferencia = null): ?float
    {
        $rawRow = [
            'test_de_fuerza_raw'    => $row['test_de_fuerza_raw'] ?? $row['test_de_fuerza'] ?? null,
            'test_resistencia_raw'  => $row['test_resistencia_raw'] ?? $row['test_resistencia'] ?? null,
            'test_velocidad_raw'    => $row['test_velocidad_raw'] ?? $row['test_velocidad'] ?? null,
            'test_coordinacion_raw' => $row['test_coordinacion_raw'] ?? $row['test_coordinacion'] ?? null,
            'test_de_reaccion_raw'  => $row['test_de_reaccion_raw'] ?? $row['test_de_reaccion'] ?? null,
        ];

        if (
            $rawRow['test_de_fuerza_raw'] === null &&
            $rawRow['test_resistencia_raw'] === null &&
            $rawRow['test_velocidad_raw'] === null &&
            $rawRow['test_coordinacion_raw'] === null &&
            $rawRow['test_de_reaccion_raw'] === null
        ) {
            $this->promedio = null;
            return null;
        }

        $puntajes = $this->calcularPuntajesNacionales($rawRow, $fechaNac, $fechaReferencia);

        $suma = (float)($puntajes['test_de_fuerza'] ?? 0.0) +
                (float)($puntajes['test_resistencia'] ?? 0.0) +
                (float)($puntajes['test_velocidad'] ?? 0.0) +
                (float)($puntajes['test_coordinacion'] ?? 0.0) +
                (float)($puntajes['test_de_reaccion'] ?? 0.0);

        $this->promedio = round($suma / 5.0, 1);
        return $this->promedio;
    }

    public function calcularPromedioMasReciente(int $atletaId): ?float
    {
        $db = $this->db();
        $stmt = $db->prepare("
            SELECT rp.*, a.fecha_nac, act.fecha AS fecha_evento
            FROM resultados_pruebas rp
            JOIN atletas a ON a.atleta_id = rp.atleta_id
            JOIN actividades act ON act.actividad_id = rp.actividad_id
            WHERE rp.atleta_id = ?
            ORDER BY act.fecha DESC, rp.test_id DESC
            LIMIT 1
        ");
        $stmt->execute([$atletaId]);
        $reciente = $stmt->fetch();

        if (!$reciente || empty($reciente['fecha_nac'])) {
            $this->promedio = null;
            return null;
        }

        return $this->calcularPromedioNacional($reciente, (string)$reciente['fecha_nac'], (string)$reciente['fecha_evento']);
    }

    /**
     * Registra una sesión de pruebas físicas (actividad de tipo 2) y sus resultados correspondientes de forma transaccional.
     *
     * @param array $actividadData Datos para la tabla `actividades`
     * @param array $resultadosData Array de resultados por atleta
     * @return int El ID de la actividad creada
     */
    public function registrarLote(array $actividadData, array $resultadosData): int
    {
        $db = $this->db();
        $db->beginTransaction();
        try {
            // 1. Insertar en actividades
            $actividadModel = new Actividad();
            $actividadId = $actividadModel->insert($actividadData);

            // 2. Insertar cada resultado de prueba física
            $stmt = $db->prepare(
                'INSERT INTO resultados_pruebas (actividad_id, atleta_id, test_de_fuerza, test_resistencia, test_velocidad, test_coordinacion, test_de_reaccion)
                 VALUES (:actividad_id, :atleta_id, :test_de_fuerza, :test_resistencia, :test_velocidad, :test_coordinacion, :test_de_reaccion)'
            );

            foreach ($resultadosData as $res) {
                $stmt->execute([
                    ':actividad_id'      => $actividadId,
                    ':atleta_id'         => (int)$res['atleta_id'],
                    ':test_de_fuerza'    => $res['test_de_fuerza'],
                    ':test_resistencia'  => $res['test_resistencia'],
                    ':test_velocidad'    => $res['test_velocidad'],
                    ':test_coordinacion' => $res['test_coordinacion'],
                    ':test_de_reaccion'  => $res['test_de_reaccion'],
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
     * Actualiza una sesión de pruebas físicas (actividad) y sus resultados de forma transaccional.
     *
     * @param int $actividadId ID de la actividad a actualizar
     * @param array $actividadData Datos de la actividad
     * @param array $resultadosData Datos de los resultados por atleta
     */
    public function actualizarLote(int $actividadId, array $actividadData, array $resultadosData): void
    {
        $db = $this->db();
        $db->beginTransaction();
        try {
            // 1. Actualizar actividad
            $actividadModel = new Actividad();
            $actividadModel->update($actividadId, $actividadData);

            // 2. Eliminar resultados anteriores de esta actividad
            $stmtDel = $db->prepare('DELETE FROM resultados_pruebas WHERE actividad_id = ?');
            $stmtDel->execute([$actividadId]);

            // 3. Insertar nuevos resultados
            $stmt = $db->prepare(
                'INSERT INTO resultados_pruebas (actividad_id, atleta_id, test_de_fuerza, test_resistencia, test_velocidad, test_coordinacion, test_de_reaccion)
                 VALUES (:actividad_id, :atleta_id, :test_de_fuerza, :test_resistencia, :test_velocidad, :test_coordinacion, :test_de_reaccion)'
            );

            foreach ($resultadosData as $res) {
                $stmt->execute([
                    ':actividad_id'      => $actividadId,
                    ':atleta_id'         => (int)$res['atleta_id'],
                    ':test_de_fuerza'    => $res['test_de_fuerza'],
                    ':test_resistencia'  => $res['test_resistencia'],
                    ':test_velocidad'    => $res['test_velocidad'],
                    ':test_coordinacion' => $res['test_coordinacion'],
                    ':test_de_reaccion'  => $res['test_de_reaccion'],
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
     * Elimina una sesión de pruebas físicas (actividad) y todos sus resultados asociados de forma transaccional.
     *
     * @param int $actividadId ID de la actividad a eliminar
     */
    public function eliminarLote(int $actividadId): void
    {
        $db = $this->db();
        $db->beginTransaction();
        try {
            // 1. Eliminar resultados
            $stmtDel = $db->prepare('DELETE FROM resultados_pruebas WHERE actividad_id = ?');
            $stmtDel->execute([$actividadId]);

            // 2. Eliminar actividad
            $actividadModel = new Actividad();
            $actividadModel->delete($actividadId);

            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
    }
}
