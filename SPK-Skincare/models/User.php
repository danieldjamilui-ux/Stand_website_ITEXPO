<?php
require_once '../config/database.php';

class User {
    private $db;
    private $table = 'users';
    
    // Properties
    public $user_id;
    public $name;
    public $username;
    public $email;
    public $password;
    public $created_at;
    
    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Get all users
    public function getAll() {
        $query = "SELECT u.*, 
                 (SELECT COUNT(*) FROM recommendations r WHERE r.user_id = u.user_id) as total_recommendations,
                 (SELECT COUNT(*) FROM user_skin_profiles usp WHERE usp.user_id = u.user_id) as has_profile
                 FROM {$this->table} u ORDER BY u.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get single user
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get user by username or email
    public function getByUsernameOrEmail($username) {
        $query = "SELECT * FROM {$this->table} WHERE username = ? OR email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$username, $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Create user
    public function create() {
        $query = "INSERT INTO {$this->table} (name, username, email, password) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $this->name,
            $this->username,
            $this->email,
            $this->password
        ]);
        return $this->db->lastInsertId();
    }
    
    // Update user
    public function update() {
        $query = "UPDATE {$this->table} SET name = ?, username = ?, email = ? WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->name,
            $this->username,
            $this->email,
            $this->user_id
        ]);
    }
    
    // Update password
    public function updatePassword() {
        $query = "UPDATE {$this->table} SET password = ? WHERE user_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->password,
            $this->user_id
        ]);
    }
    
    // Delete user
    public function delete($id) {
        try {
            // Mulai transaksi
            $this->db->beginTransaction();
            
            // Hapus data di tabel user_skin_concerns
            $stmt = $this->db->prepare("DELETE usc FROM user_skin_concerns usc 
                                     JOIN user_skin_profiles usp ON usc.profile_id = usp.profile_id 
                                     WHERE usp.user_id = ?");
            $stmt->execute([$id]);
            
            // Hapus data di tabel user_skin_profiles
            $stmt = $this->db->prepare("DELETE FROM user_skin_profiles WHERE user_id = ?");
            $stmt->execute([$id]);
            
            // Hapus data di tabel recommendation_details yang terkait dengan rekomendasi user
            $stmt = $this->db->prepare("DELETE rd FROM recommendation_details rd 
                                     JOIN recommendations r ON rd.recommendation_id = r.recommendation_id 
                                     WHERE r.user_id = ?");
            $stmt->execute([$id]);
            
            // Hapus data di tabel recommendations
            $stmt = $this->db->prepare("DELETE FROM recommendations WHERE user_id = ?");
            $stmt->execute([$id]);
            
            // Hapus user
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ?");
            $stmt->execute([$id]);
            
            // Commit transaksi
            $this->db->commit();
            
            return true;
        } catch (PDOException $e) {
            // Rollback transaksi jika terjadi error
            $this->db->rollBack();
            return false;
        }
    }
}
?>