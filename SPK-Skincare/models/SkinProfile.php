<?php
require_once '../config/database.php';

class SkinProfile {
    private $db;
    private $profile_table = 'user_skin_profiles';
    private $concerns_table = 'user_skin_concerns';
    
    // Properties
    public $profile_id;
    public $user_id;
    public $skin_type_id;
    public $created_at;
    public $concerns = [];
    
    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Get user's latest skin profile
    public function getUserProfile($user_id) {
        $query = "SELECT usp.*, st.name as skin_type_name 
                 FROM {$this->profile_table} usp 
                 JOIN skin_types st ON usp.skin_type_id = st.skin_type_id 
                 WHERE usp.user_id = ? 
                 ORDER BY usp.created_at DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get user's skin concerns
    public function getUserConcerns($profile_id) {
        $query = "SELECT usc.*, sc.name as concern_name 
                 FROM {$this->concerns_table} usc 
                 JOIN skin_concerns sc ON usc.concern_id = sc.concern_id 
                 WHERE usc.profile_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$profile_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Create skin profile
    public function createProfile() {
        try {
            // Mulai transaksi
            $this->db->beginTransaction();
            
            // Insert profile
            $query = "INSERT INTO {$this->profile_table} (user_id, skin_type_id) VALUES (?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $this->user_id,
                $this->skin_type_id
            ]);
            
            $this->profile_id = $this->db->lastInsertId();
            
            // Insert concerns
            if (!empty($this->concerns)) {
                $query = "INSERT INTO {$this->concerns_table} (profile_id, concern_id) VALUES (?, ?)";
                $stmt = $this->db->prepare($query);
                
                foreach ($this->concerns as $concern_id) {
                    $stmt->execute([
                        $this->profile_id,
                        $concern_id
                    ]);
                }
            }
            
            // Commit transaksi
            $this->db->commit();
            
            return $this->profile_id;
        } catch (PDOException $e) {
            // Rollback transaksi jika terjadi error
            $this->db->rollBack();
            return false;
        }
    }
    
    // Get all skin types
    public function getAllSkinTypes() {
        $query = "SELECT * FROM skin_types ORDER BY name";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get all skin concerns
    public function getAllSkinConcerns() {
        $query = "SELECT * FROM skin_concerns ORDER BY name";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>