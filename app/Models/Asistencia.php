<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la tabla `asistencias` de cada_db.
 *
 * Columnas: asistencia_id, actividad_id, atleta_id, estatus, observaciones
 */
final class Asistencia extends Model
{
    protected string $table = 'asistencias';
    protected string $primaryKey = 'asistencia_id';

    public function resumenAtleta(int $atletaId, ?string $desde = null, ?string $hasta = null): array
    {
        $where = 'WHERE a.atleta_id = :a';
        $bindings = [':a' => $atletaId];
        if ($desde) { $where .= ' AND act.fecha >= :desde'; $bindings[':desde'] = $desde; }
        if ($hasta) { $where .= ' AND act.fecha <= :hasta'; $bindings[':hasta'] = $hasta; }

        return $this->query(
            "SELECT a.estatus, COUNT(*) AS total
             FROM asistencias a
             JOIN actividades act ON act.actividad_id = a.actividad_id
             $where
             GROUP BY a.estatus",
            $bindings
        );
    }

    public function historialAtleta(int $atletaId): array
    {
        return $this->query(
            "SELECT a.*, act.fecha, act.tipo_actividad, act.ubicacion
             FROM asistencias a
             JOIN actividades act ON act.actividad_id = a.actividad_id
             WHERE a.atleta_id = :a
             ORDER BY act.fecha DESC
             LIMIT 50",
            [':a' => $atletaId]
        );
    }

    /**
     * Obtiene los registros de asistencia que corresponden únicamente a entrenamientos (tipo_actividad = 1).
     * 
     * @param array $filters Filtros de consulta (usuario_id, categoria_id)
     * @return array Registros de entrenamientos filtrados
     */
    public function obtenerEntrenamientos(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['usuario_id'])) {
            $where[] = "a.usuario_id = :usuario_id";
            $params[':usuario_id'] = (int) $filters['usuario_id'];
        }

        if (!empty($filters['categoria_id'])) {
            $where[] = "EXISTS (SELECT 1 FROM asig_categorias ac2 WHERE ac2.asignacion_id = a.asignacion_id AND ac2.categoria_id = :categoria_id)";
            $params[':categoria_id'] = (int) $filters['categoria_id'];
        }

        $whereClause = "";
        if (!empty($where)) {
            $whereClause = "WHERE " . implode(" AND ", $where);
        }

        $sql = "SELECT a.actividad_id AS evento_id, a.tipo_actividad AS tipo_evento, a.fecha AS fecha_evento,
                    CONCAT(u.nombre, ' ', u.apellido) AS entrenador,
                    (SELECT c.nombre_categoria FROM asig_categorias ac JOIN categorias c ON ac.categoria_id = c.categoria_id WHERE ac.asignacion_id = a.asignacion_id LIMIT 1) AS nombre_categoria,
                    (SELECT COUNT(*) FROM asistencias ast WHERE ast.actividad_id = a.actividad_id) AS total,
                    (SELECT COUNT(*) FROM asistencias ast WHERE ast.actividad_id = a.actividad_id AND ast.estatus = 1) AS presentes
             FROM actividades a
             LEFT JOIN usuarios u ON a.usuario_id = u.usuario_id
             $whereClause
             ORDER BY a.fecha DESC, a.actividad_id DESC";

        $eventos = $this->query($sql, $params);

        $filtrados = [];
        foreach ($eventos as $ev) {
            $tipo = $ev['tipo_evento'];
            // Se filtran los datos con un condicional IF en PHP
            // tipo_actividad = 1 representa "Entrenamiento"
            if ($tipo == 1 || $tipo === '1' || strcasecmp((string)$tipo, 'Entrenamiento') === 0) {
                $filtrados[] = $ev;
            }
        }

        return array_slice($filtrados, 0, 50);
    }
}

