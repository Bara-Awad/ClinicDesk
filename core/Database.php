<?php
/**
 * ClinicDesk - Database Singleton
 *
 * Implements the Singleton pattern so only ONE mysqli connection is ever
 * created per request. All models share this single instance.
 */

require_once __DIR__ . '/../config/database.php';

class Database
{
    /** @var Database|null The single instance */
    private static ?Database $instance = null;

    /** @var mysqli The underlying connection */
    private mysqli $conn;

    /**
     * Private constructor — called only by getInstance().
     * Throws RuntimeException (without exposing real error) on failure.
     */
    private function __construct()
    {
        mysqli_report(MYSQLI_REPORT_OFF); // We handle errors manually
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->conn->connect_errno) {
            // Log the real error, show a safe message to the caller
            error_log('DB connection failed: ' . $this->conn->connect_error);
            throw new RuntimeException('Database connection failed. Please try again later.');
        }

        $this->conn->set_charset(DB_CHARSET);
    }

    /**
     * Returns the single Database instance, creating it on first call.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Executes a prepared statement.
     *
     * @param string $sql    The SQL query with ? placeholders.
     * @param string $types  bind_param type string (e.g. "ssi").
     * @param array  $params Values to bind, in the same order as placeholders.
     *
     * @return mysqli_result|bool  mysqli_result for SELECT, true/false for others.
     * @throws RuntimeException on prepare or execute failure.
     */
    public function query(string $sql, string $types = '', array $params = []): mysqli_result|bool
    {
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            error_log('DB prepare failed: ' . $this->conn->error . ' | SQL: ' . $sql);
            throw new RuntimeException('A database error occurred.');
        }

        if ($types !== '' && count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }

        $executed = $stmt->execute();
        if ($executed === false) {
            error_log('DB execute failed: ' . $stmt->error . ' | SQL: ' . $sql);
            // Return false instead of throwing so callers can handle duplicate-key etc.
            return false;
        }

        // For SELECT / SHOW / EXPLAIN return the result set
        $result = $stmt->get_result();
        if ($result instanceof mysqli_result) {
            return $result;
        }

        // For INSERT / UPDATE / DELETE return true on success
        return true;
    }

    /**
     * Returns the auto-generated ID of the last INSERT.
     */
    public function lastInsertId(): int
    {
        return (int) $this->conn->insert_id;
    }

    /**
     * Returns number of rows affected by the last INSERT/UPDATE/DELETE.
     */
    public function affectedRows(): int
    {
        return (int) $this->conn->affected_rows;
    }

    /** Prevent cloning of the singleton. */
    private function __clone() {}
}
