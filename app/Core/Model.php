<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';

    protected function db(): PDO
    {
        return Database::connection();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db()->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(string $orderBy = null): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        return $this->db()->query($sql)->fetchAll();
    }

    /**
     * Inserta un registro y devuelve el ID generado.
     */
    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($c) => ":$c", $columns);

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $this->table,
            implode('`, `', $columns),
            implode(', ', $placeholders)
        );

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($this->bindValues($data));
        return (int) $this->db()->lastInsertId();
    }

    public function update(int $id, array $data): int
    {
        if (empty($data)) {
            return 0;
        }
        $sets = [];
        foreach (array_keys($data) as $col) {
            $sets[] = "`$col` = :$col";
        }
        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE `%s` = :__id',
            $this->table,
            implode(', ', $sets),
            $this->primaryKey
        );

        $stmt = $this->db()->prepare($sql);
        $values = $this->bindValues($data);
        $values[':__id'] = $id;
        $stmt->execute($values);
        return $stmt->rowCount();
    }

    public function delete(int $id): int
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    public function count(string $where = '', array $bindings = []): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        if ($where !== '') {
            $sql .= " WHERE $where";
        }
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($bindings);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Ejecuta una consulta preparada y devuelve todas las filas.
     */
    public function query(string $sql, array $bindings = []): array
    {
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    public function queryOne(string $sql, array $bindings = []): ?array
    {
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($bindings);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function bindValues(array $data): array
    {
        $values = [];
        foreach ($data as $k => $v) {
            $values[":$k"] = $v;
        }
        return $values;
    }
}
