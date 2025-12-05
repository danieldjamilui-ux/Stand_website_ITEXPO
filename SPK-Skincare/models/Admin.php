<?php
require_once '../config/database.php';

class Admin {
    private $db;
    private $table = 'admin';
    
    // Properties dengan penambahan email
    public $admin_id;
    public $name;
    public $username;
    public $password;
    public $email; // Atribut baru sesuai permintaan
    
    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Get admin by ID
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE admin_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get admin by username
    public function getByUsername($username) {
        $query = "SELECT * FROM {$this->table} WHERE username = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Create admin
    public function create() {
        $query = "INSERT INTO {$this->table} (name, username, password, email) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $this->name,
            $this->username,
            $this->password,
            $this->email // Menambahkan email
        ]);
        return $this->db->lastInsertId();
    }
    
    // Update admin
    public function update() {
        $query = "UPDATE {$this->table} SET name = ?, username = ?, email = ? WHERE admin_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->name,
            $this->username,
            $this->email, // Menambahkan email
            $this->admin_id
        ]);
    }
    
    // Update password
    public function updatePassword() {
        $query = "UPDATE {$this->table} SET password = ? WHERE admin_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->password,
            $this->admin_id
        ]);
    }
}
?>