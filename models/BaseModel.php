<?php
/**
 * ClinicDesk - BaseModel
 *
 * Abstract base for all model classes.
 * Provides a shared Database instance and a protected execute() wrapper.
 */

require_once __DIR__ . '/../core/Database.php';

abstract class BaseModel
{
    /** @var Database Shared singleton database connection. */
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Executes a prepared SQL statement.
     */
    protected function execute(string $sql, string $types = '', array $params = []): mysqli_result|bool
    {
        return $this->db->query($sql, $types, $params);
    }

    /**
     * Fetches a single row as an associative array.
     */
    protected function fetchOne(string $sql, string $types = '', array $params = []): ?array
    {
        $result = $this->execute($sql, $types, $params);
        if (!$result instanceof mysqli_result) return null;
        $row = $result->fetch_assoc();
        return $row ?: null;
    }

    /**
     * Fetches all rows as an array of associative arrays.
     */
    protected function fetchAll(string $sql, string $types = '', array $params = []): array
    {
        $result = $this->execute($sql, $types, $params);
        if (!$result instanceof mysqli_result) return [];
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Fetches a single scalar value (e.g. COUNT(*)).
     */
    protected function fetchCount(string $sql, string $types = '', array $params = []): int
    {
        $result = $this->execute($sql, $types, $params);
        if (!$result instanceof mysqli_result) return 0;
        $row = $result->fetch_row();
        return (int) ($row[0] ?? 0);
    }
}
