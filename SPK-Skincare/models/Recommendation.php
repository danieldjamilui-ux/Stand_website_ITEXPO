<?php
require_once '../config/database.php';

class Recommendation {
    private $db;
    private $table = 'recommendations';
    private $details_table = 'recommendation_details';
    
    // Properties sesuai permintaan
    public $recommendation_id;
    public $user_id;
    public $profile_id;
    public $created_at;
    
    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Get user recommendations
    public function getUserRecommendations($user_id) {
        $query = "SELECT r.*, COUNT(rd.id) as total_products 
                 FROM {$this->table} r 
                 JOIN {$this->details_table} rd ON r.recommendation_id = rd.recommendation_id 
                 WHERE r.user_id = ? 
                 GROUP BY r.recommendation_id 
                 ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get recommendation details
    public function getRecommendationDetails($recommendation_id) {
        $query = "SELECT rd.*, sp.name as product_name, sp.image, sp.price, 
                 b.name as brand_name, pc.name as category_name 
                 FROM {$this->details_table} rd 
                 JOIN skincare_products sp ON rd.product_id = sp.product_id 
                 JOIN brands b ON sp.brand_id = b.brand_id 
                 JOIN product_categories pc ON sp.category_id = pc.category_id 
                 WHERE rd.recommendation_id = ? 
                 ORDER BY rd.rank";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$recommendation_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Create recommendation
    public function create() {
        $query = "INSERT INTO {$this->table} (user_id, profile_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $this->user_id,
            $this->profile_id
        ]);
        return $this->db->lastInsertId();
    }
    
    // Add recommendation detail
    public function addDetail($recommendation_id, $product_id, $similarity_score, $rank) {
        $query = "INSERT INTO {$this->details_table} (recommendation_id, product_id, similarity_score, rank) 
                 VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$recommendation_id, $product_id, $similarity_score, $rank]);
    }
    
    // Save recommendation with details
    public function saveRecommendation($user_id, $profile_id, $recommendations) {
        try {
            // Mulai transaksi
            $this->db->beginTransaction();
            
            // Insert recommendation
            $this->user_id = $user_id;
            $this->profile_id = $profile_id;
            $recommendation_id = $this->create();
            
            // Insert recommendation details
            $rank = 1;
            foreach ($recommendations as $product) {
                $this->addDetail(
                    $recommendation_id,
                    $product['product_id'],
                    $product['similarity_score'],
                    $rank
                );
                $rank++;
                
                // Hapus batasan 10 produk teratas
                // if ($rank > 10) break;
            }
            
            // Commit transaksi
            $this->db->commit();
            
            return $recommendation_id;
        } catch (PDOException $e) {
            // Rollback transaksi jika terjadi error
            $this->db->rollBack();
            return false;
        }
    }
}
?>