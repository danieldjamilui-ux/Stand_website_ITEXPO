<?php
class Konsultasi {
    private $db;
    private $idKonsultasi;
    private $idPengguna;
    private $tanggalKonsultasi;
    private $cfHasilDiagnosis;
    private $idPenyakitHasil;
    private $hasilDiagnosis;
    private $idGejalaDipilih = [];
    private $cfGejalaPengguna = [];
    
    public function __construct($database) {
        $this->db = $database;
    }

    
    // Getters
    public function getIdKonsultasi() { return $this->idKonsultasi; }
    public function getIdPengguna() { return $this->idPengguna; }
    public function getTanggalKonsultasi() { return $this->tanggalKonsultasi; }
    public function getCfHasilDiagnosis() { return $this->cfHasilDiagnosis; }
    public function getIdPenyakitHasil() { return $this->idPenyakitHasil; }
    public function getHasilDiagnosis() { return $this->hasilDiagnosis; }
    
    // Setters
    public function setIdKonsultasi($id) { $this->idKonsultasi = $id; }
    public function setIdPengguna($id) { $this->idPengguna = $id; }
    public function setTanggalKonsultasi($tanggal) { $this->tanggalKonsultasi = $tanggal; }
    public function setCfHasilDiagnosis($cf) { $this->cfHasilDiagnosis = $cf; }
    public function setIdPenyakitHasil($id) { $this->idPenyakitHasil = $id; }
    public function setHasilDiagnosis($hasil) { $this->hasilDiagnosis = $hasil; }
    public function setIdGejalaDipilih($gejala) { $this->idGejalaDipilih = $gejala; }
    public function setCfGejalaPengguna($cf) { $this->cfGejalaPengguna = $cf; }
    
 public function save() {
    try {
        // Cek apakah sudah ada transaksi aktif
        $startedTransaction = false;
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
            $startedTransaction = true;
        }
        
        if ($this->idKonsultasi) {
            $result = $this->update();
        } else {
            $result = $this->create();
        }
        
        if (!$result) {
            if ($startedTransaction) {
                $this->db->rollBack();
            }
            return false;
        }
        
        // Hanya simpan gejala jika ID konsultasi sudah ada
        if ($this->idKonsultasi && !empty($this->idGejalaDipilih)) {
            if (!$this->simpanGejalaDipilih()) {
                if ($startedTransaction) {
                    $this->db->rollBack();
                }
                return false;
            }
        }
        
        if ($startedTransaction) {
            $this->db->commit();
        }
        return true;
    } catch (PDOException $e) {
        if ($this->db->inTransaction() && $startedTransaction) {
            $this->db->rollBack();
        }
        error_log("Error saving konsultasi: " . $e->getMessage());
        return false;
    }
}

private function create() {
    $query = "INSERT INTO konsultasi 
             (id_pengguna, tanggal_konsultasi, id_penyakit_hasil, cf_hasil_diagnosis) 
             VALUES (:id_pengguna, :tanggal, :id_penyakit, :cf_hasil)";
    $stmt = $this->db->prepare($query);
    
    // Gunakan null jika id_penyakit_hasil atau cf_hasil_diagnosis belum ada
    $idPenyakit = $this->idPenyakitHasil ?? null;
    $cfHasil = $this->cfHasilDiagnosis ?? null;
    
    $stmt->bindValue(':id_pengguna', $this->idPengguna, PDO::PARAM_INT);
    $stmt->bindValue(':tanggal', $this->tanggalKonsultasi);
    $stmt->bindValue(':id_penyakit', $idPenyakit, PDO::PARAM_INT);
    $stmt->bindValue(':cf_hasil', $cfHasil);
    
    if ($stmt->execute()) {
        $this->idKonsultasi = $this->db->lastInsertId();
        return true;
    }
    
    error_log("Gagal membuat konsultasi: " . print_r($stmt->errorInfo(), true));
    return false;
}
    
    private function update() {
        $query = "UPDATE konsultasi SET 
                 id_penyakit_hasil = :id_penyakit,
                 cf_hasil_diagnosis = :cf_hasil
                 WHERE id_konsultasi = :id";
        $stmt = $this->db->prepare($query);
        
        $stmt->bindValue(':id_penyakit', $this->idPenyakitHasil, PDO::PARAM_INT);
        $stmt->bindValue(':cf_hasil', $this->cfHasilDiagnosis);
        $stmt->bindValue(':id', $this->idKonsultasi, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return true;
        }
        
        error_log("Gagal update konsultasi: " . print_r($stmt->errorInfo(), true));
        return false;
    }
    
    private function simpanGejalaDipilih() {
        if (empty($this->idGejalaDipilih) || !$this->idKonsultasi) {
            error_log("Gejala kosong atau ID konsultasi tidak ada");
            return false;
        }
        
        // Hapus gejala lama terlebih dahulu
        $queryDelete = "DELETE FROM konsultasi_gejalapilihan WHERE id_konsultasi = :id_konsultasi";
        $stmtDelete = $this->db->prepare($queryDelete);
        $stmtDelete->bindValue(':id_konsultasi', $this->idKonsultasi, PDO::PARAM_INT);
        
        if (!$stmtDelete->execute()) {
            error_log("Gagal menghapus gejala pilihan lama: " . print_r($stmtDelete->errorInfo(), true));
            return false;
        }
        
        // Insert gejala baru
        $query = "INSERT INTO konsultasi_gejalapilihan 
                 (id_konsultasi, id_gejala, cf_pengguna) 
                 VALUES (:id_konsultasi, :id_gejala, :cf_pengguna)";
        $stmt = $this->db->prepare($query);
        
        foreach ($this->idGejalaDipilih as $idGejala) {
            $cfPengguna = $this->cfGejalaPengguna[$idGejala] ?? 1.0;
            
            $stmt->bindValue(':id_konsultasi', $this->idKonsultasi, PDO::PARAM_INT);
            $stmt->bindValue(':id_gejala', $idGejala, PDO::PARAM_INT);
            $stmt->bindValue(':cf_pengguna', $cfPengguna);
            
            if (!$stmt->execute()) {
                error_log("Gagal menyimpan gejala pilihan: " . print_r($stmt->errorInfo(), true));
                return false;
            }
        }
        
        return true;
    }
    
    public function lihatRiwayat($idPengguna, $limit = null) {
        $query = "SELECT k.*, p.nama_penyakit, p.deskripsi_penyakit, p.solusi_penyakit 
                 FROM konsultasi k 
                 JOIN penyakit p ON k.id_penyakit_hasil = p.id_penyakit 
                 WHERE k.id_pengguna = :id_pengguna 
                 ORDER BY k.tanggal_konsultasi DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_pengguna', $idPengguna);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function lihatDetailRiwayat($idKonsultasi) {
        $query = "SELECT k.*, p.nama_penyakit, p.deskripsi_penyakit, p.solusi_penyakit 
                 FROM konsultasi k 
                 JOIN penyakit p ON k.id_penyakit_hasil = p.id_penyakit 
                 WHERE k.id_konsultasi = :id_konsultasi";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_konsultasi', $idKonsultasi);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $konsultasi = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->idKonsultasi = $konsultasi['id_konsultasi'];
            $this->tanggalKonsultasi = $konsultasi['tanggal_konsultasi'];
            $this->cfHasilDiagnosis = $konsultasi['cf_hasil_diagnosis'];
            $this->idPenyakitHasil = $konsultasi['id_penyakit_hasil'];
            
            // Ambil gejala yang dipilih
            $this->idGejalaDipilih = $this->getGejalaDipilih($idKonsultasi);
            
            return $konsultasi;
        }
        return false;
    }
    
private function getGejalaDipilih($idKonsultasi) {
    $query = "SELECT id_gejala, cf_pengguna FROM konsultasi_gejalapilihan WHERE id_konsultasi = :id_konsultasi";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':id_konsultasi', $idKonsultasi);
    $stmt->execute();
    
    $gejala = [];
    $cfPengguna = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $gejala[] = $row['id_gejala'];
        $cfPengguna[$row['id_gejala']] = $row['cf_pengguna'];
    }
    
    $this->idGejalaDipilih = $gejala;
    $this->cfGejalaPengguna = $cfPengguna;
    
    return $gejala;
}
    
    /**
     * Dapatkan riwayat konsultasi pengguna dengan detail penyakit
     * @param int $idPengguna ID pengguna
     * @param int $limit Batas jumlah data
     * @param int $offset Offset data
     * @return array Daftar konsultasi dengan detail
     */
    public function getByUserWithDetails($idPengguna, $limit = 10, $offset = 0) {
        $query = "SELECT k.*, p.nama_penyakit, p.deskripsi_penyakit, p.solusi_penyakit 
                 FROM konsultasi k 
                 LEFT JOIN penyakit p ON k.id_penyakit_hasil = p.id_penyakit 
                 WHERE k.id_pengguna = :id_pengguna 
                 ORDER BY k.tanggal_konsultasi DESC 
                 LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_pengguna', $idPengguna, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Hitung total konsultasi pengguna
     * @param int $idPengguna ID pengguna
     * @return int Total konsultasi
     */
    public function getTotalByUser($idPengguna) {
        $query = "SELECT COUNT(*) as total FROM konsultasi WHERE id_pengguna = :id_pengguna";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_pengguna', $idPengguna, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total'];
    }
}
?>