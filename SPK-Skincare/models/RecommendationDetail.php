<?php
require_once '../config/database.php';

class RecommendationDetail {
    private $db;
    private $table = 'recommendation_details';
    
    // Atribut sesuai permintaan
    public $recommendation_id;
    public $product_id;
    public $similarity_score;
    public $rank;
    
    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Get recommendation details
    public function getDetails($recommendation_id) {
        $query = "SELECT rd.*, sp.name as product_name, 
                 sp.brand as brand_name, sp.active_ingredient_type, sp.size, sp.release_year 
                 FROM {$this->table} rd 
                 JOIN skincare_products sp ON rd.product_id = sp.product_id 
                 WHERE rd.recommendation_id = ? 
                 ORDER BY rd.rank";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$recommendation_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Create recommendation detail
    public function create($recommendation_id, $product_id, $similarity_score, $rank) {
        $query = "INSERT INTO {$this->table} (recommendation_id, product_id, similarity_score, rank) 
                 VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$recommendation_id, $product_id, $similarity_score, $rank]);
    }
}
?>