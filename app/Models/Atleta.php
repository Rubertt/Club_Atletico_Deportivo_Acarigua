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

        if (!empty($filters['categoria_id'])) {
            $where[] = 'a.categoria_id = :categoria';
            $params[':categoria'] = (int) $filters['categoria_id'];
        }
        if (isset($filters['estatus']) && $filters['estatus'] !== '') {
            $where[] = 'a.estatus = :estatus';
            $params[':estatus'] = (int) $filters['estatus'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(a.nombre LIKE :q1 OR a.apellido LIKE :q2 OR a.cedula LIKE :q3)';
            $qVal = '%' . $filters['q'] . '%';
            $params[':q1'] = $qVal;
            $params[':q2'] = $qVal;
            $params[':q3'] = $qVal;
        }
        if (isset($filters['cedula']) && $filters['cedula'] !== '') {
            if ((string) $filters['cedula'] === '1') {
                $where[] = '(a.cedula IS NOT NULL AND TRIM(a.cedula) <> \'\')';
            } elseif ((string) $filters['cedula'] === '0') {
                $where[] = '(a.cedula IS NULL OR TRIM(COALESCE(a.cedula, \'\')) = \'\')';
            }
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $totalSql = "SELECT COUNT(*) FROM atletas a $whereSql";
        $stmt = $this->db()->prepare($totalSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $offset = max(0, ($page - 1) * $perPage);
        $sql = "
            SELECT a.atleta_id, a.nombre, a.apellido, a.cedula, a.telefono, a.foto,
                   a.fecha_nac, a.estatus,
                   c.nombre_categoria,
                   p.nombre_posicion,
                   (SELECT MAX(fecha_medicion) FROM medidas_antropometricas WHERE atleta_id = a.atleta_id) AS ultima_medicion
             FROM atletas a
             LEFT JOIN categorias c ON c.categoria_id = a.categoria_id
             LEFT JOIN posiciones_juegos p ON p.posicion_id = a.posicion_juego_id
             $whereSql
             ORDER BY a.apellido, a.nombre
             LIMIT $perPage OFFSET $offset
        ";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

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
                   c.nombre_categoria,
                   p.nombre_posicion,
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
            LEFT JOIN categorias c ON c.categoria_id = a.categoria_id
            LEFT JOIN posiciones_juegos p ON p.posicion_id = a.posicion_juego_id
            LEFT JOIN representantes rep ON rep.representante_id = a.representante_id
            LEFT JOIN direcciones d ON d.direccion_id = a.direccion_id
            LEFT JOIN parroquias pa ON pa.parroquia_id = d.parroquias_id
            LEFT JOIN municipios m ON m.municipio_id = pa.municipio_id
            LEFT JOIN estados e ON e.estado_id = m.estado_id
            LEFT JOIN fichas_medicas f ON f.atleta_id = a.atleta_id
            WHERE a.atleta_id = :id
            LIMIT 1
        ";
        return $this->queryOne($sql, [':id' => $id]);
    }

    public function countByEstatus(): array
    {
        return $this->query("SELECT estatus, COUNT(*) AS total FROM atletas GROUP BY estatus");
    }
}
