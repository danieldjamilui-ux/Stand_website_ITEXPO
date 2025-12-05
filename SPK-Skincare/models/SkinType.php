<?php
require_once '../config/database.php';

class SkinType {
    private $db;
    private $table = 'skin_types';
    
    // Properties
    public $skin_type_id;
    public $name;
    public $description;
    
    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Get all skin types
    public function getAllTypes() {
        $query = "SELECT * FROM {$this->table} ORDER BY name";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get type by ID
    public function getTypeById($skin_type_id) {
        $query = "SELECT * FROM {$this->table} WHERE skin_type_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$skin_type_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Add new skin type (admin function)
    public function addType() {
        $query = "INSERT INTO {$this->table} (name, description) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->name,
            $this->description
        ]);
    }
    
    // Update skin type (admin function)
    public function updateType() {
        $query = "UPDATE {$this->table} SET name = ?, description = ? WHERE skin_type_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->name,
            $this->description,
            $this->skin_type_id
        ]);
    }
}
?>