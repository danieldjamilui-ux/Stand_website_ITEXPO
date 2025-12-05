<?php
class Pengguna {
    private $db;
    private $idPengguna;
    private $username;
    private $passwordHash;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // Getters
    public function getIdPengguna() { return $this->idPengguna; }
    public function getUsername() { return $this->username; }
    
    // Setters
    public function setIdPengguna($idPengguna) { $this->idPengguna = $idPengguna; }
    public function setUsername($username) { $this->username = $username; }
    public function setPasswordHash($password) { $this->passwordHash = password_hash($password, PASSWORD_DEFAULT); }
    
    // Methods
    public function login($username, $password) {
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user['password'])) {
                $this->idPengguna = $user['id'];
                $this->username = $user['username'];
                return true;
            }
        }
        return false;
    }
    
    public function logout() {
        // Implementasi logout (biasanya dilakukan di level session)
        session_destroy();
        return true;
    }
    
    public function findById($id) {
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->idPengguna = $user['id'];
            $this->username = $user['username'];
            return true;
        }
        return false;
    }
    
    public function save() {
        if ($this->idPengguna) {
            return $this->update();
        } else {
            return $this->create();
        }
    }
    
    private function create() {
        $query = "INSERT INTO users (username, password) VALUES (:username, :password)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $this->passwordHash);
        
        if ($stmt->execute()) {
            $this->idPengguna = $this->db->lastInsertId();
            return true;
        }
        return false;
    }
    
    private function update() {
        $query = "UPDATE users SET username = :username WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':id', $this->idPengguna);
        
        return $stmt->execute();
    }
}
?>