<?php
namespace App\Config;

class Database {
    private $host = 'db';
    private $db_name = 'docx_converter';
    private $username = 'php_docker';
    private $password = 'password';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new \PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch(\PDOException $e) {
            echo "Connection error: " . $e->getMessage();
        }
        return $this->conn;
    }
}