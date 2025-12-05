<?php
require_once '../config/database.php';

class SkincareProduct {
    private $db;
    private $table = 'skincare_products';
    
    // Properties
    public $product_id;
    public $name;
    public $brand;
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
    
    // Metode untuk mengecek kesesuaian produk dengan profil user
    public function cocokDenganProfilUser($profile_id) {
        // Ambil tipe kulit dari profil
        $stmt = $this->db->prepare("SELECT skin_type_id FROM user_skin_profiles WHERE profile_id = ?");
        $stmt->execute([$profile_id]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$profile) {
            return false;
        }
        
        $skin_type_id = $profile['skin_type_id'];
        
        // Cek kompatibilitas dengan tipe kulit
        $stmt = $this->db->prepare("SELECT compatibility_score FROM product_skin_type_compatibility 
                                  WHERE product_id = ? AND skin_type_id = ?");
        $stmt->execute([$this->product_id, $skin_type_id]);
        $type_compatibility = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Jika tidak ada data kompatibilitas atau skor rendah, produk tidak cocok
        if (!$type_compatibility || $type_compatibility['compatibility_score'] < 0.5) {
            return false;
        }
        
        // Ambil masalah kulit dari profil
        $stmt = $this->db->prepare("SELECT concern_id FROM user_skin_concerns WHERE profile_id = ?");
        $stmt->execute([$profile_id]);
        $concerns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($concerns)) {
            // Jika tidak ada masalah kulit, hanya pertimbangkan tipe kulit
            return $type_compatibility['compatibility_score'] >= 0.7;
        }
        
        // Hitung rata-rata efektivitas untuk masalah kulit
        $total_score = 0;
        $count = 0;
        
        foreach ($concerns as $concern) {
            $stmt = $this->db->prepare("SELECT effectiveness_score FROM product_skin_concern_compatibility 
                                      WHERE product_id = ? AND concern_id = ?");
            $stmt->execute([$this->product_id, $concern['concern_id']]);
            $concern_compatibility = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($concern_compatibility) {
                $total_score += $concern_compatibility['effectiveness_score'];
                $count++;
            }
        }
        
        // Jika tidak ada data kompatibilitas untuk masalah kulit, produk tidak cocok
        if ($count == 0) {
            return false;
        }
        
        $avg_concern_score = $total_score / $count;
        
        // Produk cocok jika rata-rata skor efektivitas dan kompatibilitas tipe kulit cukup tinggi
        return ($avg_concern_score >= 0.6 && $type_compatibility['compatibility_score'] >= 0.6);
    }
}
?>