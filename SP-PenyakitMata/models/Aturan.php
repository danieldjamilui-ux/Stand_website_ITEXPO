<?php
class Aturan {
    private $db;
    private $idAturan;
    private $cfPakar;
    private $idPenyakit; // Untuk relasi
    private $idGejala; // Untuk relasi (array)
    
    public function __construct($database) {
        $this->db = $database;
        $this->idGejala = [];
    }
    
    // Getters
    public function getIdAturan() { return $this->idAturan; }
    public function getCfPakar() { return $this->cfPakar; }
    public function getIdPenyakit() { return $this->idPenyakit; }
    public function getIdGejala() { return $this->idGejala; }
    
    // Setters
    public function setIdAturan($id) { $this->idAturan = $id; }
    public function setCfPakar($cf) { $this->cfPakar = $cf; }
    public function setIdPenyakit($id) { $this->idPenyakit = $id; }
    public function setIdGejala($ids) { $this->idGejala = $ids; }
    
    // Methods
public function tambahAturan() {
    try {
        $query = "INSERT INTO aturan (id_penyakit_hasil, cf_pakar) VALUES (:id_penyakit, :cf_pakar)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_penyakit', $this->idPenyakit);
        $stmt->bindParam(':cf_pakar', $this->cfPakar);
        
        $this->db->beginTransaction();
        
        if ($stmt->execute()) {
            $this->idAturan = $this->db->lastInsertId();
            
            // Jika ada gejala, tambahkan relasinya
            if (!empty($this->idGejala)) {
                $this->addGejalaToAturan($this->idAturan, $this->idGejala);
            }
            
            $this->db->commit();
            return true;
        }
        
        $this->db->rollBack();
        return false;
    } catch (PDOException $e) {
        $this->db->rollBack();
        error_log("Error in tambahAturan: " . $e->getMessage());
        throw $e;
    }
}

public function ubahAturan() {
    $query = "UPDATE aturan SET 
                id_penyakit_hasil = :id_penyakit, 
                cf_pakar = :cf_pakar 
              WHERE id_aturan = :id";
    
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':id_penyakit', $this->idPenyakit);
    $stmt->bindParam(':cf_pakar', $this->cfPakar);
    $stmt->bindParam(':id', $this->idAturan);
    
    return $stmt->execute();
}

public function addGejalaToAturan($idAturan, $gejalaIds) {
    try {
        // Hapus relasi lama
        $query = "DELETE FROM aturan_gejala WHERE id_aturan = :id_aturan";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_aturan', $idAturan);
        
        if (!$stmt->execute()) {
            error_log("Gagal menghapus relasi gejala lama");
            return false;
        }
        
        // Tambah relasi baru
        if (!empty($gejalaIds)) {
            $query = "INSERT INTO aturan_gejala (id_aturan, id_gejala) VALUES (:id_aturan, :id_gejala)";
            $stmt = $this->db->prepare($query);
            
            foreach ($gejalaIds as $idGejala) {
                $stmt->bindParam(':id_aturan', $idAturan);
                $stmt->bindParam(':id_gejala', $idGejala);
                
                if (!$stmt->execute()) {
                    error_log("Gagal menambahkan relasi gejala id: " . $idGejala);
                    return false;
                }
            }
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Error in addGejalaToAturan: " . $e->getMessage());
        throw $e;
    }
}

public function hapusAturan($id) {
    try {
        $this->db->beginTransaction();
        
        // Hapus relasi aturan-gejala terlebih dahulu
        $query = "DELETE FROM aturan_gejala WHERE id_aturan = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Hapus aturan
        $query = "DELETE FROM aturan WHERE id_aturan = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $result = $stmt->execute();
        
        $this->db->commit();
        return $result;
    } catch (PDOException $e) {
        $this->db->rollBack();
        error_log("Error in hapusAturan: " . $e->getMessage());
        throw $e;
    }
}
    
    public function lihatSemuaAturan() {
        try {
            $query = "SELECT a.id_aturan, p.nama_penyakit, 
                                GROUP_CONCAT(g.nama_gejala SEPARATOR ', ') AS gejala,
                                a.cf_pakar
                              FROM aturan a
                              JOIN penyakit p ON a.id_penyakit_hasil = p.id_penyakit
                              LEFT JOIN aturan_gejala ag ON a.id_aturan = ag.id_aturan
                              LEFT JOIN gejala g ON ag.id_gejala = g.id_gejala
                              GROUP BY a.id_aturan, p.nama_penyakit, a.cf_pakar";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in lihatSemuaAturan: " . $e->getMessage());
            return [];
        }
    }
    
    public function lihatDetailAturan($id) {
        $query = "SELECT * FROM aturan WHERE id_aturan = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $aturan = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->idAturan = $aturan['id_aturan'];
            $this->idPenyakit = $aturan['id_penyakit_hasil'];
            $this->cfPakar = $aturan['cf_pakar'];
            
            // Ambil gejala terkait
            $this->idGejala = $this->getGejalaIds($this->idAturan);
            
            return $aturan;
        }
        return false;
    }
    
public function getById($id) {
    return $this->lihatDetailAturan($id);
}
    
    private function getGejalaIds($idAturan) {
        $query = "SELECT id_gejala FROM aturan_gejala WHERE id_aturan = :id_aturan";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_aturan', $idAturan);
        $stmt->execute();
        
        $ids = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ids[] = $row['id_gejala'];
        }
        
        return $ids;
    }
    
    public function getGejalaByAturan($idAturan) {
        $query = "SELECT g.* FROM gejala g 
                 JOIN aturan_gejala ag ON g.id_gejala = ag.id_gejala 
                 WHERE ag.id_aturan = :id_aturan";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_aturan', $idAturan);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotalCount() {
        $query = "SELECT COUNT(*) as total FROM aturan";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
?>