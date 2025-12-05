<?php
require_once '../config/database.php';

class SkinConcern {
    private $db;
    private $table = 'skin_concerns';
    
    // Properties
    public $concern_id;
    public $name;
    public $description;
    
    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Get all skin concerns
    public function getAllConcerns() {
        $query = "SELECT * FROM {$this->table} ORDER BY name";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get concern by ID
    public function getConcernById($concern_id) {
        $query = "SELECT * FROM {$this->table} WHERE concern_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$concern_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Add new concern (admin function)
    public function addConcern() {
        $query = "INSERT INTO {$this->table} (name, description) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->name,
            $this->description
        ]);
    }
    
    // Update concern (admin function)
    public function updateConcern() {
        $query = "UPDATE {$this->table} SET name = ?, description = ? WHERE concern_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->name,
            $this->description,
            $this->concern_id
        ]);
    }
}
?>