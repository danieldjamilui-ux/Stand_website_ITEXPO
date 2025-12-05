<?php
require_once '../config/database.php';

class ProductSkinConcernCompatibility {
    private $db;
    private $table = 'product_skin_concern_compatibility';
    
    // Properties
    public $id;
    public $product_id;
    public $concern_id;
    public $effectiveness_score;
    
    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Get compatibility data for a product
    public function getByProductId($product_id) {
        $query = "SELECT pscc.*, sc.name as concern_name 
                 FROM {$this->table} pscc 
                 JOIN skin_concerns sc ON pscc.concern_id = sc.concern_id 
                 WHERE pscc.product_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Set compatibility score
    public function setCompatibility() {
        $query = "INSERT INTO {$this->table} (product_id, concern_id, effectiveness_score) 
                 VALUES (?, ?, ?) 
                 ON DUPLICATE KEY UPDATE effectiveness_score = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->product_id, 
            $this->concern_id, 
            $this->effectiveness_score, 
            $this->effectiveness_score
        ]);
    }
    
    // Get compatibility by product and concern
    public function getByProductAndConcern($product_id, $concern_id) {
        $query = "SELECT * FROM {$this->table} 
                 WHERE product_id = ? AND concern_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$product_id, $concern_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Delete compatibility
    public function deleteCompatibility($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
}
?>