<?php
declare(strict_types=1);

namespace App\Controllers\Web;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $db = Database::connection();

        // Chequeo diario pasivo de inactividad de atletas (desactivación tras 2 meses sin asistencias)
        if (session_status() === PHP_SESSION_ACTIVE && ($_SESSION['last_inactivity_check'] ?? '') !== date('Y-m-d')) {
            try {
                $db->query("
                    UPDATE atletas 
                    SET estatus = 3 
                    WHERE estatus = 1 
                    AND creado_en <= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)
                    AND atleta_id NOT IN (
                        SELECT DISTINCT a.atleta_id 
                        FROM asistencias a
                        INNER JOIN actividades act ON act.actividad_id = a.actividad_id
                        WHERE act.fecha >= DATE_SUB(CURDATE(), INTERVAL 2 MONTH)
                    )
                ");
                $_SESSION['last_inactivity_check'] = date('Y-m-d');
            } catch (\Throwable $e) {
                \App\Core\Logger::error($e);
            }
        }

        $atletas   = (int) $db->query('SELECT COUNT(*) FROM atletas')->fetchColumn();
        $activos   = (int) $db->query("SELECT COUNT(*) FROM atletas WHERE estatus = 1")->fetchColumn();
        $categorias = (int) $db->query("SELECT COUNT(*) FROM categorias WHERE estatus = 1")->fetchColumn();
        $lesionados = (int) $db->query("SELECT COUNT(*) FROM atletas WHERE estatus = 2")->fetchColumn();

        $dataAsistencia = $db->query("
            SELECT 
                categoria_id,
                nombre_categoria,
                tipo_actividad,
                SUM(presentes) AS presentes,
                SUM(total_registros) AS total_registros
            FROM (
                -- 1. Entrenamientos (1) y Eventos Especiales (3) desde asistencias
                SELECT 
                    c.categoria_id,
                    c.nombre_categoria,
                    CASE 
                        WHEN act.tipo_actividad = 'Entrenamiento' OR act.tipo_actividad = '1' OR act.tipo_actividad = 1 THEN 1
                        ELSE 3
                    END AS tipo_actividad,
                    COUNT(CASE WHEN a.estatus = 1 THEN 1 END) AS presentes,
                    COUNT(a.asistencia_id) AS total_registros
                FROM asistencias a
                INNER JOIN actividades act ON act.actividad_id = a.actividad_id
                INNER JOIN asig_categorias ac ON ac.atleta_id = a.atleta_id
                INNER JOIN categorias c ON c.categoria_id = ac.categoria_id
                WHERE act.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                AND (act.tipo_actividad = 1 OR act.tipo_actividad = 3 OR act.tipo_actividad = '1' OR act.tipo_actividad = '3' OR act.tipo_actividad = 'Entrenamiento' OR act.tipo_actividad = 'Evento Especial')
                GROUP BY c.categoria_id, c.nombre_categoria, 
                CASE 
                    WHEN act.tipo_actividad = 'Entrenamiento' OR act.tipo_actividad = '1' OR act.tipo_actividad = 1 THEN 1
                    ELSE 3
                END

                UNION ALL

                -- 2. Partidos (0) desde convocatorias
                SELECT 
                    c.categoria_id,
                    c.nombre_categoria,
                    0 AS tipo_actividad,
                    COUNT(CASE WHEN conv.asistencia = 3 THEN 1 END) AS presentes,
                    COUNT(conv.convocatoria_id) AS total_registros
                FROM convocatorias conv
                INNER JOIN actividades act ON act.actividad_id = conv.actividad_id
                INNER JOIN asig_categorias ac ON ac.atleta_id = conv.atleta_id
                INNER JOIN categorias c ON c.categoria_id = ac.categoria_id
                WHERE act.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                AND (act.tipo_actividad = 0 OR act.tipo_actividad = '0' OR act.tipo_actividad = 'Partido')
                AND conv.estatus = 1
                GROUP BY c.categoria_id, c.nombre_categoria

                UNION ALL

                -- 3. Pruebas Físicas (2) desde resultados_pruebas
                SELECT 
                    c.categoria_id,
                    c.nombre_categoria,
                    2 AS tipo_actividad,
                    COUNT(rp.test_id) AS presentes,
                    COUNT(rp.test_id) AS total_registros
                FROM resultados_pruebas rp
                INNER JOIN actividades act ON act.actividad_id = rp.actividad_id
                INNER JOIN asig_categorias ac ON ac.atleta_id = rp.atleta_id
                INNER JOIN categorias c ON c.categoria_id = ac.categoria_id
                WHERE act.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                AND (act.tipo_actividad = 2 OR act.tipo_actividad = '2' OR act.tipo_actividad = 'Pruebas Físicas' OR act.tipo_actividad = 'Pruebas Fisicas')
                GROUP BY c.categoria_id, c.nombre_categoria
            ) unified_stats
            GROUP BY categoria_id, nombre_categoria, tipo_actividad
            ORDER BY nombre_categoria ASC, tipo_actividad ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $dataActividades = $db->query("
            SELECT dia, tipo_actividad, COUNT(*) AS total
            FROM (
                SELECT DATE_FORMAT(fecha, '%d') AS dia,
                    CASE 
                        WHEN tipo_actividad = 'Partido' OR tipo_actividad = '0' OR tipo_actividad = 0 THEN 0
                        WHEN tipo_actividad = 'Entrenamiento' OR tipo_actividad = '1' OR tipo_actividad = 1 THEN 1
                        WHEN tipo_actividad = 'Pruebas Físicas' OR tipo_actividad = 'Pruebas Fisicas' OR tipo_actividad = '2' OR tipo_actividad = 2 THEN 2
                        WHEN tipo_actividad = 'Evento Especial' OR tipo_actividad = '3' OR tipo_actividad = 3 THEN 3
                        ELSE 1
                    END AS tipo_actividad
                FROM actividades
                WHERE YEAR(fecha) = YEAR(CURDATE()) AND MONTH(fecha) = MONTH(CURDATE())
            ) sub
            GROUP BY dia, tipo_actividad
            ORDER BY dia ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $dataEntrenadores = $db->query("
            SELECT CONCAT_WS(' ', u.nombre, u.apellido) AS entrenador,
            COUNT(ac.atleta_id) AS total_atletas
            FROM usuarios u
            INNER JOIN categorias c ON c.usuario_id = u.usuario_id
            INNER JOIN asig_categorias ac ON ac.categoria_id = c.categoria_id
            GROUP BY u.usuario_id, entrenador
            ORDER BY total_atletas DESC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        // 1. Top 5 Rendimiento Físico por Categoría de Edad
        $resultadosRaw = $db->query("
            SELECT rp.test_de_fuerza, rp.test_resistencia, rp.test_velocidad, 
            rp.test_coordinacion, rp.test_de_reaccion,
            a.nombre, a.apellido, a.fecha_nac,
            TIMESTAMPDIFF(YEAR, a.fecha_nac, CURDATE()) AS edad
            FROM resultados_pruebas rp
            INNER JOIN (
                SELECT atleta_id, MAX(test_id) AS max_test_id
                FROM resultados_pruebas
                GROUP BY atleta_id
            ) latest ON latest.max_test_id = rp.test_id
            INNER JOIN atletas a ON a.atleta_id = rp.atleta_id
            WHERE a.estatus = 1
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $datosRendimiento = [];
        foreach ($resultadosRaw as $row) {
            $edad = (int) $row['edad'];
            
            // Determinar categoría por edad (según los grupos del modelo de pruebas físicas)
            if ($edad <= 6) {
                $rangoEdad = 'Sub-7';
            } elseif ($edad <= 9) {
                $rangoEdad = 'Sub-10';
            } elseif ($edad <= 12) {
                $rangoEdad = 'Sub-13';
            } elseif ($edad <= 15) {
                $rangoEdad = 'Sub-16';
            } elseif ($edad <= 18) {
                $rangoEdad = 'Sub-19';
            } elseif ($edad <= 40) {
                $rangoEdad = 'Sub-40';
            } elseif ($edad <= 49) {
                $rangoEdad = 'Master-49';
            } elseif ($edad <= 59) {
                $rangoEdad = 'Master-59';
            } elseif ($edad <= 69) {
                $rangoEdad = 'Master-69';
            } else {
                $rangoEdad = 'Master-70+';
            }

            // Obtener factor de exigencia
            $factor = match (true) {
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

            $scores = [];

            // 1. Fuerza (CMJ) - Directa (20.0 a 45.0)
            if ($row['test_de_fuerza'] !== null) {
                $min = 20.0 * $factor;
                $max = 45.0 * $factor;
                $score = ($max - $min > 0.0001) ? (($row['test_de_fuerza'] - $min) / ($max - $min)) * 100 : 0.0;
                $scores[] = max(0.0, min(100.0, round($score, 1)));
            }

            // 2. Resistencia (Yo-Yo) - Directa (600.0 a 2200.0)
            if ($row['test_resistencia'] !== null) {
                $min = 600.0 * $factor;
                $max = 2200.0 * $factor;
                $score = ($max - $min > 0.0001) ? (($row['test_resistencia'] - $min) / ($max - $min)) * 100 : 0.0;
                $scores[] = max(0.0, min(100.0, round($score, 1)));
            }

            // 3. Velocidad (30m) - Inversa (5.20 a 4.10)
            if ($row['test_velocidad'] !== null && $factor > 0) {
                $min = 5.20 / $factor;
                $max = 4.10 / $factor;
                $score = ($min - $max > 0.0001) ? (($min - $row['test_velocidad']) / ($min - $max)) * 100 : 0.0;
                $scores[] = max(0.0, min(100.0, round($score, 1)));
            }

            // 4. Coordinación - Inversa (22.50 a 16.50)
            if ($row['test_coordinacion'] !== null && $factor > 0) {
                $min = 22.50 / $factor;
                $max = 16.50 / $factor;
                $score = ($min - $max > 0.0001) ? (($min - $row['test_coordinacion']) / ($min - $max)) * 100 : 0.0;
                $scores[] = max(0.0, min(100.0, round($score, 1)));
            }

            // 5. Reacción - Inversa (450.0 a 220.0)
            if ($row['test_de_reaccion'] !== null && $factor > 0) {
                $min = 450.0 / $factor;
                $max = 220.0 / $factor;
                $score = ($min - $max > 0.0001) ? (($min - $row['test_de_reaccion']) / ($min - $max)) * 100 : 0.0;
                $scores[] = max(0.0, min(100.0, round($score, 1)));
            }

            if (count($scores) > 0) {
                $promedio = array_sum($scores) / count($scores);
                $datosRendimiento[$rangoEdad][] = [
                    'nombre' => $row['nombre'] . ' ' . $row['apellido'],
                    'promedio' => round($promedio, 1)
                ];
            }
        }

        // Ordenar descendentemente cada categoría de edad y cortar para quedarse con el Top 5
        $topAtletas = [];
        $rangosValidos = [
            'Sub-7',
            'Sub-10',
            'Sub-13',
            'Sub-16',
            'Sub-19',
            'Sub-40',
            'Master-49',
            'Master-59',
            'Master-69',
            'Master-70+'
        ];
        foreach ($rangosValidos as $r) {
            if (isset($datosRendimiento[$r])) {
                usort($datosRendimiento[$r], function ($a, $b) {
                    return $b['promedio'] <=> $a['promedio'];
                });
                $topAtletas[$r] = array_slice($datosRendimiento[$r], 0, 5);
            } else {
                $topAtletas[$r] = [];
            }
        }

        // 2. Evolución del Roster (Crecimiento Histórico)
        $dataRosterRaw = $db->query("
            SELECT DATE_FORMAT(creado_en, '%Y-%m') AS mes,
            COUNT(*) AS total_nuevos
            FROM atletas
            GROUP BY mes
            ORDER BY mes ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $acumulado = 0;
        $evolucionRoster = [];
        foreach ($dataRosterRaw as $row) {
            $acumulado += (int) $row['total_nuevos'];
            $evolucionRoster[] = [
                'mes' => $row['mes'],
                'nuevos' => (int) $row['total_nuevos'],
                'acumulado' => $acumulado
            ];
        }

        // 3. Índice de Consistencia y Asistencia por Categoría y Entrenador
        $consistenciaCategorias = $db->query("
            SELECT 
                categoria_id,
                nombre_categoria,
                entrenador,
                COALESCE(ROUND(SUM(presentes) * 100.0 / NULLIF(SUM(total_atletas), 0), 1), 0) AS tasa_asistencia_promedio,
                COALESCE(ROUND(COUNT(CASE WHEN (presentes / NULLIF(total_atletas, 0)) >= 0.80 THEN 1 END) * 100.0 / NULLIF(COUNT(actividad_id), 0), 1), 0) AS indice_consistencia
            FROM (
                -- 1. Asistencias para tipo 1 y 3
                SELECT 
                    c.categoria_id,
                    c.nombre_categoria,
                    CONCAT_WS(' ', u.nombre, u.apellido) AS entrenador,
                    act.actividad_id,
                    COUNT(CASE WHEN a.estatus = 1 THEN 1 END) AS presentes,
                    COUNT(a.asistencia_id) AS total_atletas
                FROM asistencias a
                INNER JOIN actividades act ON act.actividad_id = a.actividad_id
                INNER JOIN asig_categorias ac ON ac.atleta_id = a.atleta_id AND ac.estatus = 1
                INNER JOIN categorias c ON c.categoria_id = ac.categoria_id
                INNER JOIN usuarios u ON u.usuario_id = c.usuario_id
                WHERE act.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                AND act.tipo_actividad IN (1, 3)
                GROUP BY c.categoria_id, c.nombre_categoria, u.nombre, u.apellido, act.actividad_id

                UNION ALL

                -- 2. Convocatorias para tipo 0 (Partidos)
                SELECT 
                    c.categoria_id,
                    c.nombre_categoria,
                    CONCAT_WS(' ', u.nombre, u.apellido) AS entrenador,
                    act.actividad_id,
                    COUNT(CASE WHEN conv.asistencia = 3 THEN 1 END) AS presentes,
                    COUNT(conv.convocatoria_id) AS total_atletas
                FROM convocatorias conv
                INNER JOIN actividades act ON act.actividad_id = conv.actividad_id
                INNER JOIN asig_categorias ac ON ac.atleta_id = conv.atleta_id AND ac.estatus = 1
                INNER JOIN categorias c ON c.categoria_id = ac.categoria_id
                INNER JOIN usuarios u ON u.usuario_id = c.usuario_id
                WHERE act.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                AND act.tipo_actividad = 0
                AND conv.estatus = 1
                GROUP BY c.categoria_id, c.nombre_categoria, u.nombre, u.apellido, act.actividad_id

                UNION ALL

                -- 3. Resultados Pruebas para tipo 2 (Pruebas Físicas)
                SELECT 
                    c.categoria_id,
                    c.nombre_categoria,
                    CONCAT_WS(' ', u.nombre, u.apellido) AS entrenador,
                    act.actividad_id,
                    COUNT(rp.test_id) AS presentes,
                    COUNT(rp.test_id) AS total_atletas
                FROM resultados_pruebas rp
                INNER JOIN actividades act ON act.actividad_id = rp.actividad_id
                INNER JOIN asig_categorias ac ON ac.atleta_id = rp.atleta_id AND ac.estatus = 1
                INNER JOIN categorias c ON c.categoria_id = ac.categoria_id
                INNER JOIN usuarios u ON u.usuario_id = c.usuario_id
                WHERE act.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                AND act.tipo_actividad = 2
                GROUP BY c.categoria_id, c.nombre_categoria, u.nombre, u.apellido, act.actividad_id
            ) act_stats
            GROUP BY categoria_id, nombre_categoria, entrenador
        ")->fetchAll(\PDO::FETCH_ASSOC);

        return $this->view('dashboard.index', [
            'title'      => 'Inicio',
            'active'     => 'inicio',
            'breadcrumb' => ['Inicio'],
            'stats'      => ['atletas' => $atletas, 'activos' => $activos, 'categorias' => $categorias, 'lesionados' => $lesionados],
            'dataAsistencia' => $dataAsistencia,
            'dataActividades' => $dataActividades,
            'dataEntrenadores' => $dataEntrenadores,
            'topAtletas' => $topAtletas,
            'evolucionRoster' => $evolucionRoster,
            'consistenciaCategorias' => $consistenciaCategorias,
        ], 'admin');
    }
}
