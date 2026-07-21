<?php
require_once __DIR__.'/../config/config.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            // Log the real error server-side; never show DB details to visitors
            // (leaking hostnames/credentials in a browser error is a real
            // vulnerability once this is on a public AWS server).
            error_log("Database connection failed: " . $e->getMessage());
            $debug = getenv('APP_DEBUG') === 'true';
            die($debug ? "Database connection failed: " . $e->getMessage()
                       : "Service temporarily unavailable. Please try again later.");
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}

// Abstract Class kwa ajili ya Models (Demonstrating Abstraction)
abstract class DatabaseModel {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    abstract public function getAll();
}
?>