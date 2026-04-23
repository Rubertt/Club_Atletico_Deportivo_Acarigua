<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la tabla `atletas` de cada_db.
 *
 * Relaciones clave:
 *   - representante_id → representante.representante_id
 *   - direccion_id     → direcciones.direccion_id
 *   - categoria_id     → categoria.categoria_id
 *   - posicion_de_juego→ posicion_juego.posicion_id
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
            $where[] = '(a.nombre LIKE :q OR a.apellido LIKE :q OR a.cedula LIKE :q)';
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $totalSql = "SELECT COUNT(*) FROM atletas a $whereSql";
        $stmt = $this->db()->prepare($totalSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $offset = max(0, ($page - 1) * $perPage);
        $sql = "
            SELECT a.atleta_id, a.nombre, a.apellido, a.cedula, a.telefono, a.foto,
                   a.fecha_nacimiento, a.estatus,
                   c.nombre_categoria,
                   p.nombre_posicion
            FROM atletas a
            LEFT JOIN categoria c ON c.categoria_id = a.categoria_id
            LEFT JOIN posicion_juego p ON p.posicion_id = a.posicion_de_juego
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
                   rep.nombre_completo AS representante_nombre,
                   rep.cedula AS representante_cedula,
                   rep.telefono AS representante_telefono,
                   rep.tipo_relacion AS representante_relacion,
                   d.parroquias_id, d.localidad, d.tipo_vivienda, d.ubicacion_vivienda,
                   pa.parroquia AS parroquia_nombre,
                   m.municipio AS municipio_nombre,
                   e.estado AS estado_nombre,
                   pa.municipio_id, m.estado_id,
                   f.ficha_id, f.grupo_sanguineo, f.alergias, f.antecedentes_familiares,
                   f.antecedentes_quirurgicos, f.condicion_cronica, f.medicacion_actual
            FROM atletas a
            LEFT JOIN categoria c ON c.categoria_id = a.categoria_id
            LEFT JOIN posicion_juego p ON p.posicion_id = a.posicion_de_juego
            LEFT JOIN representante rep ON rep.representante_id = a.representante_id
            LEFT JOIN direcciones d ON d.direccion_id = a.direccion_id
            LEFT JOIN parroquias pa ON pa.parroquia_id = d.parroquias_id
            LEFT JOIN municipios m ON m.municipio_id = pa.municipio_id
            LEFT JOIN estados e ON e.estado_id = m.estado_id
            LEFT JOIN ficha_medica f ON f.atleta_id = a.atleta_id
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
