<?php
// Konfigurasi Database
class Database {
    private $host = 'localhost';
    private $db_name = 'sp_penyakit_mata';
    private $username = 'root';
    private $password = '';
    private $conn;

public function getConnection() {
    $this->conn = null;
    try {
        $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
        $this->conn->exec("set names utf8");
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Hapus atau comment line berikut:
        // $this->conn->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
    } catch(PDOException $exception) {
        error_log("Connection error: " . $exception->getMessage());
        throw $exception;
    }
    return $this->conn;
}
}
?>