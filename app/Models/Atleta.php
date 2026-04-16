<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

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
        if (!empty($filters['estatus'])) {
            $where[] = 'a.estatus = :estatus';
            $params[':estatus'] = $filters['estatus'];
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

    public function findCompleto(int $id): ?array
    {
        $sql = "
            SELECT a.*,
                   c.nombre_categoria,
                   p.nombre_posicion,
                   t.nombres AS tutor_nombres, t.apellidos AS tutor_apellidos,
                   t.cedula AS tutor_cedula, t.telefono AS tutor_telefono,
                   t.correo AS tutor_correo, t.tipo_relacion AS tutor_relacion,
                   d.parroquia_id, d.punto_referencia, d.calle_avenida, d.casa_edificio,
                   pa.nombre AS parroquia, m.nombre AS municipio,
                   e.nombre AS estado, pais.nombre AS pais,
                   pa.municipio_id, m.estado_id, e.pais_id,
                   f.alergias, f.tipo_sanguineo, f.lesion, f.condicion_medica, f.observacion
            FROM atletas a
            LEFT JOIN categoria c ON c.categoria_id = a.categoria_id
            LEFT JOIN posicion_juego p ON p.posicion_id = a.posicion_de_juego
            LEFT JOIN tutor t ON t.tutor_id = a.tutor_id
            LEFT JOIN direcciones d ON d.direccion_id = a.direccion_id
            LEFT JOIN ubicacion_parroquia pa ON pa.parroquia_id = d.parroquia_id
            LEFT JOIN ubicacion_municipio m ON m.municipio_id = pa.municipio_id
            LEFT JOIN ubicacion_estado e ON e.estado_id = m.estado_id
            LEFT JOIN ubicacion_pais pais ON pais.pais_id = e.pais_id
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
