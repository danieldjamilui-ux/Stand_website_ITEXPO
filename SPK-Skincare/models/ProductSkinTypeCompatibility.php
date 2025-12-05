<?php
require_once '../config/database.php';

class ProductSkinTypeCompatibility {
    private $db;
    private $table = 'product_skin_type_compatibility';
    
    // Properties
    public $id;
    public $product_id;
    public $skin_type_id;
    public $compatibility_score;
    
    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Get compatibility data for a product
    public function getByProductId($product_id) {
        $query = "SELECT pstc.*, st.name as skin_type_name 
                 FROM {$this->table} pstc 
                 JOIN skin_types st ON pstc.skin_type_id = st.skin_type_id 
                 WHERE pstc.product_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Set compatibility score
    public function setCompatibility() {
        $query = "INSERT INTO {$this->table} (product_id, skin_type_id, compatibility_score) 
                 VALUES (?, ?, ?) 
                 ON DUPLICATE KEY UPDATE compatibility_score = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $this->product_id, 
            $this->skin_type_id, 
            $this->compatibility_score, 
            $this->compatibility_score
        ]);
    }
    
    // Get compatibility by product and skin type
    public function getByProductAndType($product_id, $skin_type_id) {
        $query = "SELECT * FROM {$this->table} 
                 WHERE product_id = ? AND skin_type_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$product_id, $skin_type_id]);
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