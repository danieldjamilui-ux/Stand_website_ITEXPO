<?php
require_once '../config/database.php';

class Product {
    private $db;
    private $table = 'skincare_products';
    
    // Properties
    public $product_id;
    public $name;
    public $brand; // Diubah dari brand_id menjadi brand
    public $size;
    public $active_ingredient_type;
    public $release_year;
    
    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Get all products
    public function getAll() {
        $query = "SELECT * FROM {$this->table} ORDER BY name";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get single product
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE product_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get product skin type compatibility
    public function getSkinTypeCompatibility($product_id) {
        $query = "SELECT pstc.*, st.name as skin_type_name 
                 FROM product_skin_type_compatibility pstc 
                 JOIN skin_types st ON pstc.skin_type_id = st.skin_type_id 
                 WHERE pstc.product_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get product skin concern compatibility
    public function getSkinConcernCompatibility($product_id) {
        $query = "SELECT pscc.*, sc.name as concern_name 
                 FROM product_skin_concern_compatibility pscc 
                 JOIN skin_concerns sc ON pscc.concern_id = sc.concern_id 
                 WHERE pscc.product_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Create product
    public function create() {
        try {
            // Mulai transaksi
            $this->db->beginTransaction();
            
            // Insert product
            $query = "INSERT INTO {$this->table} (name, brand, size, active_ingredient_type, release_year) 
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $this->name,
                $this->brand,
                $this->size,
                $this->active_ingredient_type,
                $this->release_year
            ]);
            
            $this->product_id = $this->db->lastInsertId();
            
            // Commit transaksi
            $this->db->commit();
            
            return $this->product_id;
        } catch (PDOException $e) {
            // Rollback transaksi jika terjadi error
            $this->db->rollBack();
            return false;
        }
    }
    
    // Update product
    public function update() {
        $query = "UPDATE {$this->table} 
                 SET name = ?, brand = ?, 
                 size = ?, active_ingredient_type = ?, release_year = ? 
                 WHERE product_id = ?";
        
        $params = [
            $this->name,
            $this->brand,
            $this->size,
            $this->active_ingredient_type,
            $this->release_year,
            $this->product_id
        ];
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }
    
    // Delete product
    public function delete($id) {
        try {
            // Mulai transaksi
            $this->db->beginTransaction();
            
            // Hapus data di tabel product_skin_type_compatibility
            $stmt = $this->db->prepare("DELETE FROM product_skin_type_compatibility WHERE product_id = ?");
            $stmt->execute([$id]);
            
            // Hapus data di tabel product_skin_concern_compatibility
            $stmt = $this->db->prepare("DELETE FROM product_skin_concern_compatibility WHERE product_id = ?");
            $stmt->execute([$id]);
            
            // Hapus data di tabel recommendation_details yang terkait dengan produk
            $stmt = $this->db->prepare("DELETE FROM recommendation_details WHERE product_id = ?");
            $stmt->execute([$id]);
            
            // Hapus produk
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE product_id = ?");
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
    
    // Set product skin type compatibility
    public function setSkinTypeCompatibility($product_id, $skin_type_id, $compatibility_score) {
        $query = "INSERT INTO product_skin_type_compatibility (product_id, skin_type_id, compatibility_score) 
                 VALUES (?, ?, ?) 
                 ON DUPLICATE KEY UPDATE compatibility_score = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$product_id, $skin_type_id, $compatibility_score, $compatibility_score]);
    }
    
    // Set product skin concern compatibility
    public function setSkinConcernCompatibility($product_id, $concern_id, $effectiveness_score) {
        $query = "INSERT INTO product_skin_concern_compatibility (product_id, concern_id, effectiveness_score) 
                 VALUES (?, ?, ?) 
                 ON DUPLICATE KEY UPDATE effectiveness_score = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$product_id, $concern_id, $effectiveness_score, $effectiveness_score]);
    }
    
    // Get all brands (Diubah untuk mendapatkan daftar brand unik dari tabel skincare_products)
    public function getAllBrands() {
        $query = "SELECT DISTINCT brand FROM {$this->table} ORDER BY brand";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>