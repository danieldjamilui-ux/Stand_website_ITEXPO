<?php
require_once '../config/database.php';

class ManajerProduk {
    private $db;
    private $table = 'skincare_products';
    
    // Constructor
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Metode tambahProduk sesuai permintaan
    public function tambahProduk($name, $brand, $size, $active_ingredient_type, $release_year) {
        try {
            // Mulai transaksi
            $this->db->beginTransaction();
            
            // Insert product
            $query = "INSERT INTO {$this->table} (name, brand, size, active_ingredient_type, release_year) 
                     VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $name,
                $brand,
                $size,
                $active_ingredient_type,
                $release_year
            ]);
            
            $product_id = $this->db->lastInsertId();
            
            // Commit transaksi
            $this->db->commit();
            
            return $product_id;
        } catch (PDOException $e) {
            // Rollback transaksi jika terjadi error
            $this->db->rollBack();
            return false;
        }
    }
    
    // Metode hapusProduk sesuai permintaan
    public function hapusProduk($id) {
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
    
    // Metode tambahan untuk mendapatkan semua produk
    public function getAllProducts() {
        $query = "SELECT * FROM {$this->table} ORDER BY name";
        $stmt = $this->db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Metode tambahan untuk mendapatkan produk berdasarkan ID
    public function getProductById($id) {
        $query = "SELECT * FROM {$this->table} WHERE product_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>