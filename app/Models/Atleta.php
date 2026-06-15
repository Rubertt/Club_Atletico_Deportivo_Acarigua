<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la tabla `atletas` de cada_db.
 *
 * Relaciones clave:
 *   - representante_id → representantes.representante_id
 *   - direccion_id     → direcciones.direccion_id
 *   - categoria_id     → categorias.categoria_id
 *   - posicion_juego_id→ posiciones_juegos.posicion_id
 */
final class Atleta extends Model
{
    protected string $table = 'atletas';
    protected string $primaryKey = 'atleta_id';

    /**
     * Lista paginada con joins útiles para la tabla principal.
     */
    public function paginate(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = [];
        $params = [];

        if (isset($filters['estatus']) && $filters['estatus'] !== '') {
            $where[] = 'a.estatus = :estatus';
            $params[':estatus'] = (int) $filters['estatus'];
        }
        if (!empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            if (preg_match('/\d/', $q)) {
                $digits = preg_replace('/\D/', '', $q);
                if ($digits !== '') {
                    $where[] = 'a.cedula LIKE :q_num';
                    $params[':q_num'] = '%' . $digits . '%';
                } else {
                    $where[] = '(a.nombre LIKE :q1 OR a.apellido LIKE :q2 OR a.cedula LIKE :q3)';
                    $qVal = '%' . $q . '%';
                    $params[':q1'] = $qVal;
                    $params[':q2'] = $qVal;
                    $params[':q3'] = $qVal;
                }
            } else {
                $where[] = '(a.nombre LIKE :q1 OR a.apellido LIKE :q2 OR a.cedula LIKE :q3)';
                $qVal = '%' . $q . '%';
                $params[':q1'] = $qVal;
                $params[':q2'] = $qVal;
                $params[':q3'] = $qVal;
            }
        }
        if (isset($filters['categoria_id']) && $filters['categoria_id'] !== '') {
            if ($filters['categoria_id'] === 'sin_asignacion') {
                $where[] = 'ac.categoria_id IS NULL';
            } else {
                $where[] = 'ac.categoria_id = :categoria_id';
                $params[':categoria_id'] = (int) $filters['categoria_id'];
            }
        }

        $joinSql = "
            LEFT JOIN asig_categorias ac ON ac.atleta_id = a.atleta_id
            LEFT JOIN categorias c ON c.categoria_id = ac.categoria_id
        ";

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $totalSql = "SELECT COUNT(*) FROM atletas a $joinSql $whereSql";
        $stmt = $this->db()->prepare($totalSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $offset = max(0, ($page - 1) * $perPage);
        $sql = "
            SELECT a.atleta_id, a.nombre, a.apellido, a.cedula, a.telefono, a.foto,
                   a.fecha_nac, a.estatus,
                   (SELECT MAX(fecha_medicion) FROM medidas_antropometricas WHERE atleta_id = a.atleta_id) AS ultima_medicion,
                   c.nombre_categoria, ac.estatus AS asig_estatus, c.edad_min, c.edad_max, ac.asignacion_id
             FROM atletas a
             $joinSql
             $whereSql
             ORDER BY a.apellido, a.nombre
             LIMIT $perPage OFFSET $offset
        ";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Sincronizar dinámicamente el estatus de asignación de categoría en base a la edad
        $hoy = new \DateTime();
        $asigModel = null;
        foreach ($rows as &$row) {
            $row['cedula_formateada'] = self::formatCedula($row['cedula'] ?? '');
            if (!empty($row['nombre_categoria']) && !empty($row['asignacion_id'])) {
                $nac = new \DateTime($row['fecha_nac'] ?? 'today');
                $edad = $hoy->diff($nac)->y;
                $edadMin = (int) $row['edad_min'];
                $edadMax = (int) $row['edad_max'];
                $nuevoEstatus = ($edad >= $edadMin && $edad <= $edadMax) ? 1 : 2;
                if ((int) ($row['asig_estatus'] ?? 1) !== $nuevoEstatus) {
                    if ($asigModel === null) {
                        $asigModel = new AsigCategoria();
                    }
                    $asigModel->updateStatus((int) $row['asignacion_id'], $nuevoEstatus);
                    $row['asig_estatus'] = $nuevoEstatus;
                }
            }
        }

        return [
            'data' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /**
     * Obtiene un atleta con todos sus datos relacionados (representante, dirección, ficha médica).
     */
    public function findCompleto(int $id): ?array
    {
        $sql = "
            SELECT a.*,
                   rep.nombre AS tutor_nombres,
                   rep.apellido AS tutor_apellidos,
                   rep.cedula AS tutor_cedula,
                   rep.telefono AS tutor_telefono,
                   rep.tipo_relacion AS tutor_relacion,
                   d.parroquias_id, d.localidad, d.tipo_vivienda, d.ubicacion_vivienda,
                   pa.parroquia AS parroquia,
                   m.municipio AS municipio,
                   e.estado AS estado,
                   pa.municipio_id, m.estado_id,
                   f.ficha_id, f.grupo_sanguineo, f.alergias, f.antecedentes_familiares,
                   f.antecedentes_quirurgicos, f.condicion_cronica, f.medicacion_actual
            FROM atletas a
            LEFT JOIN representantes rep ON rep.representante_id = a.representante_id
            LEFT JOIN direcciones d ON d.direccion_id = a.direccion_id
            LEFT JOIN parroquias pa ON pa.parroquia_id = d.parroquias_id
            LEFT JOIN municipios m ON m.municipio_id = pa.municipio_id
            LEFT JOIN estados e ON e.estado_id = m.estado_id
            LEFT JOIN fichas_medicas f ON f.atleta_id = a.atleta_id
            WHERE a.atleta_id = :id
            LIMIT 1
        ";
        $atleta = $this->queryOne($sql, [':id' => $id]);
        if ($atleta) {
            $atleta['cedula_formateada'] = self::formatCedula($atleta['cedula'] ?? '');
            $atleta['tutor_cedula_formateada'] = self::formatCedula($atleta['tutor_cedula'] ?? '');
        }

        // Cargar discapacidades (relación uno-a-muchos vía ficha_medica)
        if ($atleta && !empty($atleta['ficha_id'])) {
            $atleta['discapacidades'] = $this->query(
                "SELECT d.*, t.nombre_tipo, t.descripcion AS tipo_descripcion
                 FROM discapacidades d
                 LEFT JOIN tipos_discapacidades t ON t.tipo_discapacidad_id = d.tipo_discapacidad_id
                 WHERE d.ficha_id = :ficha_id
                 ORDER BY d.fecha_registro DESC",
                [':ficha_id' => $atleta['ficha_id']]
            );
        } else if ($atleta) {
            $atleta['discapacidades'] = [];
        }

        return $atleta;
    }

    public function countByEstatus(): array
    {
        return $this->query("SELECT estatus, COUNT(*) AS total FROM atletas GROUP BY estatus");
    }

    /**
     * Formatea la cédula con puntos cada 3 dígitos para el frontend.
     * Ejemplo: 'V-12345678' -> 'V-12.345.678'
     */
    public static function formatCedula(?string $cedula): string
    {
        return format_cedula($cedula);
    }
}
