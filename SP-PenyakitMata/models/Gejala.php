<?php
class Gejala {
    private $db;
    private $idGejala;
    private $namaGejala;
    private $deskripsiGejala;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // Getters
    public function getId() { return $this->idGejala; }
    public function getNamaGejala() { return $this->namaGejala; }
     public function getKodeGejala() { return $this->kodeGejala; }
    public function getDeskripsiGejala() { return $this->deskripsiGejala; }
    
    // Setters
    public function setId($id) { $this->idGejala = $id; }
    public function setNamaGejala($nama) { $this->namaGejala = $nama; }
     public function setKodeGejala($kode) { $this->kodeGejala = $kode; }
    public function setDeskripsiGejala($deskripsi) { $this->deskripsiGejala = $deskripsi; }
    
    // Methods
    public function tambahGejala() {
        try {
            $this->db->beginTransaction();
            
            $query = "INSERT INTO gejala (nama_gejala, deskripsi_gejala) 
                     VALUES (:nama, :deskripsi)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nama', $this->namaGejala);
            $stmt->bindParam(':deskripsi', $this->deskripsiGejala);
            
            if ($stmt->execute()) {
                $this->idGejala = $this->db->lastInsertId();
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error tambah gejala: " . $e->getMessage());
            return false;
        }
    }
    
    public function ubahGejala() {
        try {
            $this->db->beginTransaction();
            
            $query = "UPDATE gejala SET 
                     nama_gejala = :nama, 
                     deskripsi_gejala = :deskripsi 
                     WHERE id_gejala = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nama', $this->namaGejala);
            $stmt->bindParam(':deskripsi', $this->deskripsiGejala);
            $stmt->bindParam(':id', $this->idGejala);
            
            $result = $stmt->execute();
            
            if ($result && $stmt->rowCount() > 0) {
                $this->db->commit();
                return true;
            } else {
                $this->db->rollBack();
                return false;
            }
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error ubah gejala: " . $e->getMessage());
            return false;
        }
    }
    
 public function hapusGejala($id) {
    try {
        $this->db->beginTransaction();
        
        $query = "DELETE FROM gejala WHERE id_gejala = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        $result = $stmt->execute();
        
        if ($result && $stmt->rowCount() > 0) {
            $this->db->commit();
            return true;
        } else {
            $this->db->rollBack();
            return false;
        }
    } catch (PDOException $e) {
        $this->db->rollBack();
        error_log("Error hapus gejala: " . $e->getMessage());
        return false;
    }
}
    
    public function lihatSemuaGejala() {
        $query = "SELECT * FROM gejala ORDER BY nama_gejala";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
/**
 * Mendapatkan semua data gejala (alias untuk lihatSemuaGejala)
 * @return array Daftar gejala
 */
public function getAll() {
    return $this->lihatSemuaGejala();
}

    public function lihatDetailGejala($id) {
        $query = "SELECT * FROM gejala WHERE id_gejala = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $gejala = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->idGejala = $gejala['id_gejala'];
            $this->namaGejala = $gejala['nama_gejala'];
            $this->deskripsiGejala = $gejala['deskripsi_gejala'];
            return $gejala;
        }
        return false;
    }
    
    public function findByKode($kode) {
        $query = "SELECT * FROM gejala WHERE kode_gejala = :kode";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':kode', $kode);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
    
    public function findById($id) {
        return $this->lihatDetailGejala($id);
    }
    
    public function save() {
        if (empty($this->idGejala)) {
            return $this->tambahGejala();
        } else {
            return $this->ubahGejala();
        }
    }
    
    public function delete() {
        if (!empty($this->idGejala)) {
            return $this->hapusGejala($this->idGejala);
        }
        return false;
    }
}
?>