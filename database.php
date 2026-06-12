<?php
/**
 * Singleton Database Connection
 * Chỉ tạo 1 instance PDO duy nhất trong toàn bộ ứng dụng
 */
class Database {
    private static ?Database $instance = null;
    private PDO $pdo;

    private string $host   = 'localhost';
    private string $dbname = 'helpdesk_db';
    private string $user   = 'root';
    private string $pass   = '';

    private function __construct() {
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $this->user, $this->pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }
}
