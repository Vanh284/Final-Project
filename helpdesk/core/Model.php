<?php
/**
 * Base Model (Repository pattern base)
 * All models extend this class.
 */
abstract class Model
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Find a single record by primary key */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1";
        $row = $this->db->query($sql, [$id])->fetch();
        return $row ?: null;
    }

    /** Get all records, optionally with WHERE clause */
    public function all(string $where = '', array $params = [], string $order = ''): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        if ($where) $sql .= " WHERE {$where}";
        if ($order) $sql .= " ORDER BY {$order}";
        return $this->db->query($sql, $params)->fetchAll();
    }

    /** Count records */
    public function count(string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->table}`";
        if ($where) $sql .= " WHERE {$where}";
        return (int) $this->db->query($sql, $params)->fetchColumn();
    }

    /** Insert a row; returns new id */
    public function create(array $data): int
    {
        $cols = implode('`, `', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO `{$this->table}` (`{$cols}`) VALUES ({$placeholders})";
        $this->db->query($sql, array_values($data));
        return (int) $this->db->lastInsertId();
    }

    /** Update a row by primary key */
    public function update(int $id, array $data): bool
    {
        $sets = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        $sql  = "UPDATE `{$this->table}` SET {$sets} WHERE `{$this->primaryKey}` = ?";
        $this->db->query($sql, [...array_values($data), $id]);
        return true;
    }

    /** Delete a row by primary key */
    public function delete(int $id): bool
    {
        $this->db->query("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?", [$id]);
        return true;
    }

    /** Paginate: returns ['data' => [], 'total' => n, 'pages' => n] */
    public function paginate(int $page, int $perPage, string $where = '', array $params = [], string $order = ''): array
    {
        $total  = $this->count($where, $params);
        $pages  = (int) ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM `{$this->table}`";
        if ($where) $sql .= " WHERE {$where}";
        if ($order) $sql .= " ORDER BY {$order}";
        $sql .= " LIMIT {$perPage} OFFSET {$offset}";

        return [
            'data'  => $this->db->query($sql, $params)->fetchAll(),
            'total' => $total,
            'pages' => $pages,
            'page'  => $page,
        ];
    }
}
