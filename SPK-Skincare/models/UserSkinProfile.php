<?php
require_once '../config/database.php';

class UserSkinProfile {
    private $db;
    private $table = 'user_skin_profiles';
    
    // Properties
    public $profile_id;
    public $user_id;
    public $skin_type_id;
    public $created_at;
    public $updated_at;
    
    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Get user's latest skin profile
    public function getUserProfile($user_id) {
        $query = "SELECT usp.*, st.name as skin_type_name 
                 FROM {$this->table} usp 
                 JOIN skin_types st ON usp.skin_type_id = st.skin_type_id 
                 WHERE usp.user_id = ? 
                 ORDER BY usp.created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Create skin profile
    public function createProfile() {
        try {
            // Mulai transaksi
            $this->db->beginTransaction();
            
            // Insert profile
            $query = "INSERT INTO {$this->table} (user_id, skin_type_id) VALUES (?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $this->user_id,
                $this->skin_type_id
            ]);
            
            $this->profile_id = $this->db->lastInsertId();
            
            // Commit transaksi
            $this->db->commit();
            
            return $this->profile_id;
        } catch (PDOException $e) {
            // Rollback transaksi jika terjadi error
            $this->db->rollBack();
            return false;
        }
    }
    
    // Get all profiles for a user
    public function getAllUserProfiles($user_id) {
        $query = "SELECT usp.*, st.name as skin_type_name 
                 FROM {$this->table} usp 
                 JOIN skin_types st ON usp.skin_type_id = st.skin_type_id 
                 WHERE usp.user_id = ? 
                 ORDER BY usp.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get profile by ID
    public function getProfileById($profile_id) {
        $query = "SELECT usp.*, st.name as skin_type_name 
                 FROM {$this->table} usp 
                 JOIN skin_types st ON usp.skin_type_id = st.skin_type_id 
                 WHERE usp.profile_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$profile_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>