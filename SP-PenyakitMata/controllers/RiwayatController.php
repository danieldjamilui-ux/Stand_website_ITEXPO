<?php

require_once __DIR__ . '/../models/Konsultasi.php';
require_once __DIR__ . '/../models/GejalaPilihan.php';
require_once __DIR__ . '/../models/Penyakit.php';
require_once __DIR__ . '/../models/HasilDiagnosis.php';

class RiwayatController {
    private $db;
    private $konsultasi;
    private $gejalaPilihan;
    private $penyakit;
    private $hasilDiagnosis;
    
    public function __construct($database) {
        $this->db = $database;
        $this->konsultasi = new Konsultasi($this->db);
        $this->gejalaPilihan = new GejalaPilihan($this->db);
        $this->penyakit = new Penyakit($this->db);
        $this->hasilDiagnosis = new HasilDiagnosis();
    }
    
    /**
     * Dapatkan riwayat konsultasi pengguna dengan pagination
     * @param int $user_id ID pengguna
     * @param int $page Halaman saat ini
     * @param int $limit Jumlah data per halaman
     * @return array Data riwayat dengan informasi pagination
     */
    public function tampilkanDaftarRiwayat($user_id, $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            
            // Modified query to include required fields
            $riwayat = $this->konsultasi->getByUserWithDetails($user_id, $limit, $offset);
            
            // Hitung total data
            $total = $this->konsultasi->getTotalByUser($user_id);
            $total_pages = ceil($total / $limit);
            
            return [
                'success' => true,
                'data' => $riwayat,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $total_pages,
                    'total_records' => $total,
                    'limit' => $limit,
                    'has_next' => $page < $total_pages,
                    'has_prev' => $page > 1
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Dapatkan detail riwayat konsultasi
     * @param int $id_konsultasi ID konsultasi
     * @param int $user_id ID pengguna untuk validasi kepemilikan
     * @return array|null Detail riwayat konsultasi
     */
    public function tampilkanDetailRiwayat($id_konsultasi, $user_id) {
        try {
            // Ambil data konsultasi
            $konsultasi_data = $this->konsultasi->findById($id_konsultasi);
            
            if (!$konsultasi_data || $konsultasi_data['id_user'] != $user_id) {
                return [
                    'success' => false,
                    'message' => 'Konsultasi tidak ditemukan atau bukan milik Anda'
                ];
            }
            
            // Ambil gejala yang dipilih
            $gejala_terpilih = $this->gejalaPilihan->getByKonsultasi($id_konsultasi);
            
            // Ambil detail penyakit jika ada hasil diagnosis
            $detail_penyakit = null;
            if ($konsultasi_data['id_penyakit_hasil']) {
                $detail_penyakit = $this->penyakit->findById($konsultasi_data['id_penyakit_hasil']);
            }
            
            return [
                'success' => true,
                'data' => [
                    'konsultasi' => $konsultasi_data,
                    'gejala_terpilih' => $gejala_terpilih,
                    'detail_penyakit' => $detail_penyakit,
                    'persentase_keyakinan' => $konsultasi_data['cf_hasil'] ? round($konsultasi_data['cf_hasil'] * 100, 2) : 0
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Hapus riwayat konsultasi
     * @param int $id_konsultasi ID konsultasi
     * @param int $user_id ID pengguna untuk validasi kepemilikan
     * @return array Response dengan status
     */
    public function hapusRiwayat($id_konsultasi, $user_id) {
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
                'message' => 'Riwayat konsultasi berhasil dihapus'
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
     * Dapatkan statistik riwayat konsultasi pengguna
     * @param int $user_id ID pengguna
     * @return array Statistik konsultasi
     */
    public function getStatistik($user_id) {
        try {
            $query = "SELECT 
                        COUNT(*) as total_konsultasi,
                        COUNT(CASE WHEN hasil_diagnosis IS NOT NULL THEN 1 END) as konsultasi_berhasil,
                        AVG(cf_hasil) as rata_rata_cf,
                        MAX(tanggal_konsultasi) as konsultasi_terakhir
                     FROM konsultasi 
                     WHERE id_user = :user_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Hitung persentase keberhasilan
            $persentase_berhasil = 0;
            if ($stats['total_konsultasi'] > 0) {
                $persentase_berhasil = round(($stats['konsultasi_berhasil'] / $stats['total_konsultasi']) * 100, 2);
            }
            
            return [
                'success' => true,
                'data' => [
                    'total_konsultasi' => (int)$stats['total_konsultasi'],
                    'konsultasi_berhasil' => (int)$stats['konsultasi_berhasil'],
                    'persentase_berhasil' => $persentase_berhasil,
                    'rata_rata_keyakinan' => $stats['rata_rata_cf'] ? round($stats['rata_rata_cf'] * 100, 2) : 0,
                    'konsultasi_terakhir' => $stats['konsultasi_terakhir']
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
    
    public function getDetailKonsultasi($id_konsultasi, $user_id) {
        try {
            $konsultasi = $this->konsultasi->findById($id_konsultasi);
            
            if (!$konsultasi || $konsultasi['id_user'] != $user_id) {
                return ['success' => false, 'message' => 'Akses ditolak'];
            }
            
            $gejala = $this->gejalaPilihan->getByKonsultasi($id_konsultasi);
            $penyakit = $this->penyakit->findById($konsultasi['id_penyakit_hasil']);
            
            return [
                'success' => true,
                'data' => [
                    'tanggal' => $konsultasi['tanggal_konsultasi'],
                    'penyakit' => $penyakit ? $penyakit['nama_penyakit'] : null,
                    'cf' => $konsultasi['cf_hasil'],
                    'gejala' => $gejala,
                    'penjelasan' => $penyakit ? $penyakit['penjelasan'] : null
                ]
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

?>