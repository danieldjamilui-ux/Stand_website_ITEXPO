<?php
require_once '../config/database.php';

class UserSkinConcern {
    private $db;
    private $table = 'user_skin_concerns';
    
    // Properties
    public $id;
    public $profile_id;
    public $concern_id;
    public $severity;
    
    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Get user's skin concerns by profile ID
    public function getUserConcerns($profile_id) {
        $query = "SELECT usc.*, sc.name as concern_name 
                 FROM {$this->table} usc 
                 JOIN skin_concerns sc ON usc.concern_id = sc.concern_id 
                 WHERE usc.profile_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$profile_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Add user skin concern
    public function addConcern() {
        $query = "INSERT INTO {$this->table} (profile_id, concern_id, severity) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->profile_id,
            $this->concern_id,
            $this->severity
        ]);
    }
    
    // Update concern severity
    public function updateSeverity() {
        $query = "UPDATE {$this->table} SET severity = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->severity,
            $this->id
        ]);
    }
    
    // Delete concern
    public function deleteConcern($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>