<?php

require_once __DIR__ . '/../models/Penyakit.php';
require_once __DIR__ . '/../config/database.php';

class PenyakitController {
    private $db;
    private $penyakit;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->penyakit = new Penyakit($this->db);
    }
    
    public function index() {
        return $this->tampilkanDaftarPenyakit(1, null);
    }
    
    /**
     * Menampilkan daftar penyakit dengan pagination
     * @param int $page Halaman saat ini
     * @param int $limit Jumlah data per halaman
     * @param string $search Kata kunci pencarian
     * @return array Data penyakit dengan pagination
     */
    public function tampilkanDaftarPenyakit($page = 1, $limit = null, $search = '') {
        try {
            $offset = ($page - 1) * ($limit ?? 0);
            
            // Ambil semua data penyakit tanpa batasan
            $penyakit_list = $this->penyakit->getAllPenyakit($limit, $offset, $search);
            
            // Hitung total data
            $total = $this->penyakit->getTotalCount($search);
            $total_pages = $limit ? ceil($total / $limit) : 1;
            
            return [
                'success' => true,
                'data' => $penyakit_list,
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
     * Menampilkan form tambah penyakit
     * @return array Data untuk form
     */
    public function tampilkanFormTambahPenyakit() {
        return [
            'success' => true,
            'data' => [
                'title' => 'Tambah Penyakit Baru',
                'action' => 'tambah'
            ]
        ];
    }
    
    /**
     * Menampilkan form edit penyakit
     * @param int $id ID penyakit
     * @return array Data untuk form
     */
    public function tampilkanFormEditPenyakit($id) {
        try {
            $penyakit_data = $this->penyakit->findById($id);
            
            if (!$penyakit_data) {
                return [
                    'success' => false,
                    'message' => 'Penyakit tidak ditemukan'
                ];
            }
            
            // Ambil gejala yang terkait dengan penyakit ini
            $gejala_terkait = $this->getGejalaByPenyakit($id);
            
            return [
                'success' => true,
                'data' => [
                    'title' => 'Edit Penyakit',
                    'action' => 'edit',
                    'penyakit' => $penyakit_data,
                    'gejala_terkait' => $gejala_terkait
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
     * Simpan penyakit baru
     * @param array $data Data penyakit
     * @return array Response dengan status
     */
    public function prosesSimpanPenyakit($data) {
        try {
            // Validasi input
            $validation = $this->validatePenyakit($data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Data tidak valid',
                    'errors' => $validation['errors']
                ];
            }
            
            // Set data penyakit (tanpa mengatur ID)
            // ID akan diatur otomatis di metode tambahPenyakit()
            $this->penyakit->setNamaPenyakit($data['nama_penyakit']);
            $this->penyakit->setDeskripsiPenyakit($data['deskripsi'] ?? '');
            $this->penyakit->setSolusiPenyakit($data['solusi'] ?? '');
            
            // Simpan penyakit
            if ($this->penyakit->save()) {
                return [
                    'success' => true,
                    'message' => 'Penyakit berhasil ditambahkan',
                    'data' => [
                        'id' => $this->penyakit->getIdPenyakit(),
                        'nama_penyakit' => $this->penyakit->getNamaPenyakit()
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal menyimpan penyakit'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update penyakit
     * @param int $id ID penyakit
     * @param array $data Data penyakit yang akan diupdate
     * @return array Response dengan status
     */
/**
 * Update penyakit
 * @param int $id ID penyakit
 * @param array $data Data penyakit yang akan diupdate
 * @return array Response dengan status
 */
public function prosesUpdatePenyakit($id, $data) {
    try {
        // Validasi ID
        if (empty($id)) {
            throw new Exception("ID penyakit tidak valid");
        }

        // Set data ke model
        $this->penyakit->setIdPenyakit($id);
        $this->penyakit->setNamaPenyakit($data['nama_penyakit']);
        $this->penyakit->setDeskripsiPenyakit($data['deskripsi'] ?? '');
        $this->penyakit->setSolusiPenyakit($data['solusi'] ?? '');
        
        // Simpan perubahan
        if ($this->penyakit->save()) {
            // Verifikasi perubahan di database
            $updated = $this->penyakit->findById($id);
            if ($updated['nama_penyakit'] != $data['nama_penyakit']) {
                throw new Exception("Data tidak terupdate di database");
            }
            
            return [
                'success' => true,
                'message' => 'Data penyakit berhasil diperbarui',
                'data' => $updated
            ];
        } else {
            throw new Exception("Gagal menyimpan perubahan");
        }
    } catch (Exception $e) {
        error_log("Error update penyakit: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Gagal update: ' . $e->getMessage()
        ];
    }
}
    
    /**
     * Hapus penyakit
     * @param int $id ID penyakit
     * @return array Response dengan status
     */
public function prosesHapusPenyakit($id) {
    try {
        // Validasi ID
        if (empty($id)) {
            return [
                'success' => false,
                'message' => 'ID penyakit tidak valid'
            ];
        }

        // Cek apakah penyakit ada
        $existing_penyakit = $this->penyakit->lihatDetailPenyakit($id);
        if (!$existing_penyakit) {
            return [
                'success' => false,
                'message' => 'Penyakit tidak ditemukan'
            ];
        }
        
        // Cek apakah penyakit sedang digunakan dalam aturan
        if ($this->isPenyakitUsedInRules($id)) {
            return [
                'success' => false,
                'message' => 'Penyakit tidak dapat dihapus karena sedang digunakan dalam aturan diagnosis'
            ];
        }
        
        // Hapus penyakit
        if ($this->penyakit->hapusPenyakit($id)) {
            return [
                'success' => true,
                'message' => 'Penyakit berhasil dihapus'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal menghapus penyakit'
            ];
        }
    } catch (PDOException $e) {
        error_log("Error deleting penyakit: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan saat menghapus penyakit: ' . $e->getMessage()
        ];
    }
}
        
        // Cek apakah penyakit sedang digunakan dalam aturan
/**
 * Cek apakah penyakit sedang digunakan dalam aturan
 * @param int $id_penyakit ID penyakit
 * @return bool True jika sedang digunakan
 */
private function isPenyakitUsedInRules($id_penyakit) {
    $query = "SELECT COUNT(*) as count FROM aturan WHERE id_penyakit_hasil = :id_penyakit";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':id_penyakit', $id_penyakit);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] > 0;
}
        
 
    /**
     * Validasi data penyakit
     * @param array $data Data yang akan divalidasi
     * @param int|null $id ID penyakit untuk update (optional)
     * @return array Hasil validasi
     */
    private function validatePenyakit($data, $id = null) {
        $errors = [];
        
        // Hapus validasi ID penyakit
        // Validasi ID hanya diperlukan untuk update
        if ($id !== null && empty($data['id_penyakit'])) {
            $errors[] = 'ID Penyakit harus diisi untuk update';
        }
        
        // Validasi nama penyakit
        if (empty($data['nama_penyakit'])) {
            $errors[] = 'Nama penyakit harus diisi';
        } elseif (strlen($data['nama_penyakit']) > 100) {
            $errors[] = 'Nama penyakit maksimal 100 karakter';
        }
        
        // Validasi deskripsi (optional)
        if (isset($data['deskripsi']) && strlen($data['deskripsi']) > 1000) {
            $errors[] = 'Deskripsi maksimal 1000 karakter';
        }
        
        // Validasi solusi (optional)
        if (isset($data['solusi']) && strlen($data['solusi']) > 1000) {
            $errors[] = 'Solusi maksimal 1000 karakter';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Dapatkan gejala yang terkait dengan penyakit
     * @param int $id_penyakit ID penyakit
     * @return array Daftar gejala
     */
    private function getGejalaByPenyakit($id_penyakit) {
        $query = "SELECT g.*, a.cf_pakar 
                 FROM gejala g 
                 JOIN aturan a ON g.id = a.id_gejala 
                 WHERE a.id_penyakit = :id_penyakit 
                 ORDER BY a.cf_pakar DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_penyakit', $id_penyakit);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Dapatkan semua penyakit untuk dropdown/select
     * @return array Daftar penyakit
     */
    public function getAllForSelect() {
        try {
            $penyakit_list = $this->penyakit->getAllPenyakit();
            
            return [
                'success' => true,
                'data' => $penyakit_list
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Simpan penyakit baru (method baru)
     * @param array $data Data penyakit
     * @return array Response dengan status
     */
    public function store($data) {
        return $this->prosesSimpanPenyakit($data);
    }
    
    /**
     * Update penyakit (method baru)
     * @param int $id ID penyakit
     * @param array $data Data penyakit yang akan diupdate
     * @return array Response dengan status
     */
    public function update($id, $data) {
        return $this->prosesUpdatePenyakit($id, $data);
    }
}

?>