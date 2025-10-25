<?php
namespace Core;

use PDO;
use PDOException;

class Database {
    private $connection;
    private static $instance = null;

    public function __construct() {
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect() {
        // Cargar configuración manualmente
        $config = $this->loadConfig();
        
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";

        try {
            $this->connection = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    private function loadConfig() {
        // Cargar desde .env manualmente
        $envFile = __DIR__ . '/../.env';
        $config = [
            'host' => 'localhost',
            'database' => 'grifo_app',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4'
        ];

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue; // Saltar comentarios
                
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                switch ($key) {
                    case 'DB_HOST': $config['host'] = $value; break;
                    case 'DB_NAME': $config['database'] = $value; break;
                    case 'DB_USER': $config['username'] = $value; break;
                    case 'DB_PASS': $config['password'] = $value; break;
                    case 'DB_CHARSET': $config['charset'] = $value; break;
                }
            }
        }
        
        return $config;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Métodos helper para consultas comunes
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Error en la consulta: " . $e->getMessage());
        }
    }

    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    // Métodos para transacciones
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollBack() {
        return $this->connection->rollBack();
    }

    // Método para verificar si la tabla existe
    public function tableExists($tableName) {
        try {
            $result = $this->query("SHOW TABLES LIKE ?", [$tableName]);
            return $result->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}