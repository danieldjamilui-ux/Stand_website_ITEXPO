<?php
class Penyakit {
    private $db;
    private $idPenyakit;
    private $namaPenyakit;
    private $deskripsiPenyakit;
    private $solusiPenyakit;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // Getters
    public function getIdPenyakit() { return $this->idPenyakit; }
    public function getNamaPenyakit() { return $this->namaPenyakit; }
    public function getDeskripsiPenyakit() { return $this->deskripsiPenyakit; }
    public function getSolusiPenyakit() { return $this->solusiPenyakit; }
    
    // Setters
    public function setIdPenyakit($id) { $this->idPenyakit = $id; }
    public function setNamaPenyakit($nama) { $this->namaPenyakit = $nama; }
    public function setDeskripsiPenyakit($deskripsi) { $this->deskripsiPenyakit = $deskripsi; }
    public function setSolusiPenyakit($solusi) { $this->solusiPenyakit = $solusi; }
    
    // Methods
    public function tambahPenyakit() {
        try {
            // Mulai transaksi
            $this->db->beginTransaction();
            
            // Jika ID diatur secara manual
            if (!empty($this->idPenyakit)) {
                $query = "INSERT INTO penyakit (id_penyakit, nama_penyakit, deskripsi_penyakit, solusi_penyakit) 
                      VALUES (:id, :nama, :deskripsi, :solusi)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $this->idPenyakit);
                $stmt->bindParam(':nama', $this->namaPenyakit);
                $stmt->bindParam(':deskripsi', $this->deskripsiPenyakit);
                $stmt->bindParam(':solusi', $this->solusiPenyakit);
                
                error_log("Mencoba menyimpan penyakit dengan ID manual: " . $this->idPenyakit);
                error_log("Data penyakit: " . json_encode([
                    'id' => $this->idPenyakit,
                    'nama' => $this->namaPenyakit,
                    'deskripsi' => $this->deskripsiPenyakit,
                    'solusi' => $this->solusiPenyakit
                ]));
            } else {
                // Jika ID dibiarkan kosong, cari ID tertinggi dan tambahkan 1
                $query_max = "SELECT MAX(id_penyakit) as max_id FROM penyakit";
                $stmt_max = $this->db->prepare($query_max);
                $stmt_max->execute();
                $result = $stmt_max->fetch(PDO::FETCH_ASSOC);
                $next_id = ($result['max_id'] ?? 0) + 1;
                
                $query = "INSERT INTO penyakit (id_penyakit, nama_penyakit, deskripsi_penyakit, solusi_penyakit) 
                      VALUES (:id, :nama, :deskripsi, :solusi)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $next_id);
                $stmt->bindParam(':nama', $this->namaPenyakit);
                $stmt->bindParam(':deskripsi', $this->deskripsiPenyakit);
                $stmt->bindParam(':solusi', $this->solusiPenyakit);
                $this->idPenyakit = $next_id;
                
                error_log("Mencoba menyimpan penyakit dengan ID otomatis: " . $next_id);
            }
            
            // Tambahkan debugging untuk melihat status koneksi database
            error_log("Status koneksi database: " . ($this->db ? "Terhubung" : "Tidak terhubung"));
            
            // Coba eksekusi query
            $result = $stmt->execute();
            
            // Tambahkan debugging untuk melihat hasil eksekusi
            error_log("Hasil eksekusi query: " . ($result ? "Berhasil" : "Gagal"));
            error_log("Jumlah baris terpengaruh: " . $stmt->rowCount());
            
            if ($result && $stmt->rowCount() > 0) {
                // Commit transaksi jika berhasil
                $this->db->commit();
                error_log("Penyakit berhasil disimpan dengan ID: " . $this->idPenyakit);
                return true;
            } else {
                // Rollback jika gagal
                $this->db->rollBack();
                error_log("Gagal menyimpan penyakit: " . print_r($stmt->errorInfo(), true));
                return false;
            }
        } catch (PDOException $e) {
            // Rollback jika terjadi exception
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error saat menyimpan penyakit: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            return false;
        }
    }
    
public function ubahPenyakit() {
    try {
        // Mulai transaksi
        $this->db->beginTransaction();
        
        $query = "UPDATE penyakit SET 
                 nama_penyakit = :nama, 
                 deskripsi_penyakit = :deskripsi, 
                 solusi_penyakit = :solusi 
                 WHERE id_penyakit = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nama', $this->namaPenyakit);
        $stmt->bindParam(':deskripsi', $this->deskripsiPenyakit);
        $stmt->bindParam(':solusi', $this->solusiPenyakit);
        $stmt->bindParam(':id', $this->idPenyakit);
        
        $result = $stmt->execute();
        
        if ($result) {
            // Commit transaksi jika berhasil
            $this->db->commit();
            error_log("Data penyakit berhasil diupdate di database");
            return true;
        } else {
            // Rollback jika gagal
            $this->db->rollBack();
            error_log("Gagal update penyakit: " . print_r($stmt->errorInfo(), true));
            return false;
        }
    } catch (PDOException $e) {
        // Rollback jika terjadi exception
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        error_log("Error update penyakit: " . $e->getMessage());
        return false;
    }
}
    
public function hapusPenyakit($id) {
    try {
        $this->db->beginTransaction();
        
        error_log("Mencoba menghapus penyakit dengan ID: " . $id);
        
        $query = "DELETE FROM penyakit WHERE id_penyakit = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        $result = $stmt->execute();
        
        if ($result && $stmt->rowCount() > 0) {
            $this->db->commit();
            error_log("Penyakit berhasil dihapus dengan ID: " . $id);
            return true;
        } else {
            $this->db->rollBack();
            error_log("Gagal menghapus penyakit: " . print_r($stmt->errorInfo(), true));
            return false;
        }
    } catch (PDOException $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        error_log("Error saat menghapus penyakit: " . $e->getMessage());
        return false;
    }
}
    
    public function lihatSemuaPenyakit() {
        $query = "SELECT * FROM penyakit ORDER BY nama_penyakit";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function lihatSemua() {
        $query = "SELECT * FROM penyakit";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function lihatDetailPenyakit($id) {
        error_log("Executing query with id: " . $id);
        $query = "SELECT * FROM penyakit WHERE id_penyakit = :id";
        error_log("SQL Query: " . $query);
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        try {
            $stmt->execute();
            error_log("Query executed successfully");
        } catch (PDOException $e) {
            error_log("PDO Error: " . $e->getMessage());
            error_log("Error Info: " . print_r($stmt->errorInfo(), true));
            throw $e;
        }
        
        if ($stmt->rowCount() > 0) {
            $penyakit = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->idPenyakit = $penyakit['id_penyakit'];
            $this->namaPenyakit = $penyakit['nama_penyakit'];
            $this->deskripsiPenyakit = $penyakit['deskripsi_penyakit'];
            $this->solusiPenyakit = $penyakit['solusi_penyakit'];
            return $penyakit;
        }
        return false;
    }
    
    public function getTotalCount() {
        $query = "SELECT COUNT(*) as total FROM penyakit";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    public function getAllPenyakit($limit = null, $offset = 0, $search = '') {
        $query = "SELECT * FROM penyakit";
        $params = [];
        
        if (!empty($search)) {
            $query .= " WHERE nama_penyakit LIKE :search OR deskripsi_penyakit LIKE :search";
            $params[':search'] = "%$search%";
        }
        
        $query .= " ORDER BY nama_penyakit";
        
        // Hanya tambahkan LIMIT jika parameter limit tidak null
        if ($limit !== null) {
            $query .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->db->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        // Hanya bind parameter limit dan offset jika limit tidak null
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Tambahkan method findByKode
    public function findByKode($kode) {
        $query = "SELECT * FROM penyakit WHERE kode_penyakit = :kode";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':kode', $kode);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
    
    // Tambahkan method findById sebagai alias untuk lihatDetailPenyakit
    public function findById($id) {
        return $this->lihatDetailPenyakit($id);
    }
    
    // Tambahkan method save sebagai penghubung
    public function save() {
        if (empty($this->idPenyakit)) {
            // Jika tidak ada ID, berarti tambah baru
            return $this->tambahPenyakit();
        } else {
            // Jika ada ID, berarti update
            return $this->ubahPenyakit();
        }
    }
}
?>