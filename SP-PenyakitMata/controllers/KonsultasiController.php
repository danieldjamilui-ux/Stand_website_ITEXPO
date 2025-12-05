<?php
require_once __DIR__ . '/../models/Konsultasi.php';
require_once __DIR__ . '/../models/GejalaPilihan.php';
require_once __DIR__ . '/../models/MesinInferensi.php';
require_once __DIR__ . '/../models/Gejala.php';
require_once __DIR__ . '/../models/HasilDiagnosis.php';

class KonsultasiController {
    private $db;
    private $konsultasi;
    private $gejalaPilihan;
    private $mesinInferensi;
    private $gejala;
    private $hasilDiagnosis;
    
     public function __construct($database) {
        $this->db = $database;
        $this->konsultasi = new Konsultasi($this->db);
        $this->mesinInferensi = new MesinInferensi($this->db);
        $this->gejala = new Gejala($this->db);
    }

 public function mulaiSesiKonsultasi($user_id, $gejala_terpilih, $cf_user) {
    try {
        // Validasi input lebih ketat
        if (empty($gejala_terpilih) || !is_array($gejala_terpilih)) {
            throw new Exception('Pilih minimal 1 gejala');
        }

        // Pastikan $cf_user adalah array dan memiliki nilai untuk setiap gejala
        $cf_user = is_array($cf_user) ? $cf_user : [];
        foreach ($gejala_terpilih as $id_gejala) {
            if (!isset($cf_user[$id_gejala])) {
                $cf_user[$id_gejala] = 1.0; // Nilai default jika tidak ada
            }
        }

        // Mulai transaksi
        $this->db->beginTransaction();

        // Buat objek konsultasi
        $konsultasi = new Konsultasi($this->db);
        $konsultasi->setIdPengguna($user_id);
        $konsultasi->setTanggalKonsultasi(date('Y-m-d H:i:s'));
        
        // Simpan konsultasi awal (tanpa hasil diagnosis dulu)
        if (!$konsultasi->save()) {
            $this->db->rollBack();
            throw new Exception('Gagal menyimpan data konsultasi awal');
        }

        // Pastikan ID konsultasi tersedia
        if (!$konsultasi->getIdKonsultasi()) {
            $this->db->rollBack();
            throw new Exception('Gagal mendapatkan ID konsultasi');
        }

        // Set gejala yang dipilih setelah ID konsultasi tersedia
        $konsultasi->setIdGejalaDipilih($gejala_terpilih);
        $konsultasi->setCfGejalaPengguna($cf_user);

        // Proses diagnosis
        $hasil_diagnosis = $this->mesinInferensi->prosesDiagnosis($gejala_terpilih, $cf_user);
        if (!$hasil_diagnosis) {
            $this->db->rollBack();
            throw new Exception('Proses diagnosis gagal menghasilkan hasil');
        }

        // Update hasil diagnosis ke konsultasi
        $konsultasi->setIdPenyakitHasil($hasil_diagnosis->getPenyakit()['id_penyakit']);
        $konsultasi->setCfHasilDiagnosis($hasil_diagnosis->getCfHasil());

        // Simpan kembali dengan hasil diagnosis
        if (!$konsultasi->save()) {
            $this->db->rollBack();
            throw new Exception('Gagal menyimpan hasil diagnosis');
        }

        // Commit transaksi jika semua berhasil
        $this->db->commit();

        return [
            'success' => true,
            'id_konsultasi' => $konsultasi->getIdKonsultasi(),
            'hasil_diagnosis' => $hasil_diagnosis
        ];
    } catch (Exception $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        error_log('Error in mulaiSesiKonsultasi: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}


    /**
     * Proses input gejala dari pengguna
     * @param array $gejala_terpilih Array ID gejala yang dipilih
     * @param array $cf_user Array nilai CF dari user
     * @return array Data gejala yang diproses
     */
    public function prosesInputGejala($gejala_terpilih, $cf_user) {
        $gejala_data = [];
        foreach ($gejala_terpilih as $id_gejala) {
            $cf_user_value = isset($cf_user[$id_gejala]) ? floatval($cf_user[$id_gejala]) : 1.0;
            $gejala_data[] = [
                'id_gejala' => $id_gejala,
                'cf_user' => $cf_user_value
            ];
        }
        return $gejala_data;
    }
    
    /**
     * Hitung diagnosis dengan mesin inferensi
     * @param array $gejala_terpilih Array ID gejala yang dipilih
     * @param array $cf_user Array nilai CF dari user
     * @return array Hasil diagnosis
     */
    public function hitungDiagnosisDenganInferensi($gejala_terpilih, $cf_user) {
        // Get relevant rules first
        $aturan_relevan = $this->mesinInferensi->getAturanRelevan($gejala_terpilih);
        
        // Then get disease information
        $penyakit_info = $this->mesinInferensi->getPenyakitInfo($gejala_terpilih);
        
        // Finally calculate CF using forward chaining
        return $this->mesinInferensi->hitungCFDenganForwardChaining(
            $gejala_terpilih, 
            $cf_user,
            $aturan_relevan,
            $penyakit_info
        );
    }
    
    /**
     * Simpan hasil konsultasi
     * @param int $id_konsultasi ID konsultasi
     * @param array $hasil_diagnosis Hasil diagnosis
     * @return bool True jika berhasil disimpan
     */
    public function simpanHasilKonsultasi($id_konsultasi, $hasil_diagnosis) {
        try {
            $this->konsultasi->setId($id_konsultasi);
            $this->konsultasi->setHasilDiagnosis($hasil_diagnosis['nama_penyakit']);
            $this->konsultasi->setCfHasil($hasil_diagnosis['cf_hasil']);
            $this->konsultasi->setIdPenyakitHasil($hasil_diagnosis['id_penyakit']);
            
            return $this->konsultasi->save();
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Dapatkan semua gejala untuk form konsultasi
     * @return array Daftar gejala
     */
    public function getAllGejala() {
        return $this->gejala->getAll();
    }
    
    /**
     * Dapatkan detail konsultasi
     * @param int $id_konsultasi ID konsultasi
     * @return array|null Detail konsultasi
     */
    public function getDetailKonsultasi($id_konsultasi) {
        try {
            // Ambil data konsultasi
            $konsultasi_data = $this->konsultasi->findById($id_konsultasi);
            if (!$konsultasi_data) {
                return null;
            }
            
            // Ambil gejala yang dipilih
            $gejala_terpilih = $this->gejalaPilihan->getByKonsultasi($id_konsultasi);
            
            // Jika ada hasil diagnosis, ambil semua hasil untuk perbandingan
            $semua_hasil = null;
            if (!empty($gejala_terpilih)) {
                $gejala_ids = array_column($gejala_terpilih, 'id_gejala');
                $cf_user = array_column($gejala_terpilih, 'cf_user', 'id_gejala');
                $semua_hasil = $this->mesinInferensi->getAllHasilDiagnosis($gejala_ids, $cf_user);
            }
            
            return [
                'konsultasi' => $konsultasi_data,
                'gejala_terpilih' => $gejala_terpilih,
                'semua_hasil' => $semua_hasil
            ];
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Hapus konsultasi
     * @param int $id_konsultasi ID konsultasi
     * @param int $user_id ID pengguna (untuk validasi kepemilikan)
     * @return array Response dengan status
     */
    public function deleteKonsultasi($id_konsultasi, $user_id) {
        try {
            // Cek kepemilikan konsultasi
            $konsultasi_data = $this->konsultasi->findById($id_konsultasi);
            if (!$konsultasi_data || $konsultasi_data['id_user'] != $user_id) {
                return [
                    'success' => false,
                    'message' => 'Konsultasi tidak ditemukan atau bukan milik Anda'
                ];
            }
            
            // Mulai transaksi
            $this->db->beginTransaction();
            
            // Hapus detail konsultasi
            if (!$this->gejalaPilihan->deleteByKonsultasi($id_konsultasi)) {
                $this->db->rollback();
                return [
                    'success' => false,
                    'message' => 'Gagal menghapus detail konsultasi'
                ];
            }
            
            // Hapus konsultasi
            $this->konsultasi->setId($id_konsultasi);
            if (!$this->konsultasi->delete()) {
                $this->db->rollback();
                return [
                    'success' => false,
                    'message' => 'Gagal menghapus konsultasi'
                ];
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Konsultasi berhasil dihapus'
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Dapatkan riwayat konsultasi pengguna
     * @param int $user_id ID pengguna
     * @param int $limit Batas jumlah data
     * @param int $offset Offset data
     * @return array Daftar konsultasi
     */
    public function getRiwayatKonsultasi($user_id, $limit = 10, $offset = 0) {
        return $this->konsultasi->getByUser($user_id, $limit, $offset);
    }
    
    /**
     * Hitung total konsultasi pengguna
     * @param int $user_id ID pengguna
     * @return int Total konsultasi
     */
    public function getTotalKonsultasi($user_id) {
        return $this->konsultasi->getTotalByUser($user_id);
    }
}

?>