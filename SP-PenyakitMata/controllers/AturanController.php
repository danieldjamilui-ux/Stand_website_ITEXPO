<?php
require_once '../models/Aturan.php';
require_once '../models/Penyakit.php';
require_once '../models/Gejala.php';
require_once '../config/database.php';

class AturanController {
    private $db;
    private $aturanModel;
    private $penyakitModel;
    private $gejalaModel;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->aturanModel = new Aturan($this->db);
        $this->penyakitModel = new Penyakit($this->db);
        $this->gejalaModel = new Gejala($this->db);
    }
    
    public function index($page = 1, $limit = 10) {
        error_log('Memanggil method index()'); // Log baru
        $dataAturan = $this->aturanModel->lihatSemuaAturan();
        error_log(print_r($dataAturan, true)); // Log data
        return $this->tampilkanDaftarAturan($page, $limit);
    }
    
    /**
     * Menampilkan daftar aturan dengan pagination
     */
    public function tampilkanDaftarAturan($page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            $aturan = $this->aturanModel->lihatSemuaAturan();
            $total = count($aturan);
            $totalPages = ceil($total / $limit);
            
            return [
                'success' => true,
                'data' => $aturan,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_records' => $total,
                    'limit' => $limit
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengambil data aturan: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Menampilkan form tambah aturan
     */
    public function tampilkanFormTambahAturan() {
        try {
            $penyakit = $this->penyakitModel->lihatSemuaPenyakit();
            $gejala = $this->gejalaModel->lihatSemuaGejala();
            
            return [
                'success' => true,
                'data' => [
                    'title' => 'Tambah Aturan Baru',
                    'action' => 'tambah',
                    'penyakit' => $penyakit,
                    'gejala' => $gejala
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal memuat form: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Menampilkan form edit aturan
     */
    public function tampilkanFormEditAturan($id) {
        try {
            $aturan = $this->aturanModel->getById($id);
            
            if (!$aturan) {
                return [
                    'success' => false,
                    'message' => 'Aturan tidak ditemukan'
                ];
            }
            
            $penyakit = $this->penyakitModel->lihatSemuaPenyakit();
            $gejala = $this->gejalaModel->lihatSemuaGejala();
            $gejala_terpilih = $this->getGejalaByAturan($id);
            
            return [
                'success' => true,
                'data' => [
                    'title' => 'Edit Aturan',
                    'action' => 'edit',
                    'aturan' => $aturan,
                    'penyakit' => $penyakit,
                    'gejala' => $gejala,
                    'gejala_terpilih' => $gejala_terpilih
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal memuat form: ' . $e->getMessage()
            ];
        }
    }
    
/**
 * Menambah aturan baru
 */
public function prosesSimpanAturan($data) {
    try {
        // Validasi input
        if (empty($data['cf_pakar']) || empty($data['id_penyakit_hasil']) || empty($data['gejala_ids'])) {
            return [
                'success' => false,
                'message' => 'Semua field harus diisi (CF Pakar, Penyakit, dan minimal 1 Gejala)'
            ];
        }

        // Validasi CF Pakar antara 0 dan 1
        if ($data['cf_pakar'] < 0 || $data['cf_pakar'] > 1) {
            return [
                'success' => false,
                'message' => 'Nilai CF Pakar harus antara 0 dan 1'
            ];
        }

        // Buat aturan baru
        $this->aturanModel->setIdPenyakit($data['id_penyakit_hasil']);
        $this->aturanModel->setCfPakar($data['cf_pakar']);
        
        $result = $this->aturanModel->tambahAturan();
        
        if ($result) {
            // Tambahkan relasi gejala
            $this->aturanModel->addGejalaToAturan($this->aturanModel->getIdAturan(), $data['gejala_ids']);
            
            return [
                'success' => true,
                'message' => 'Aturan berhasil ditambahkan'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal menambahkan aturan'
            ];
        }
    } catch (PDOException $e) {
        error_log("Error in prosesSimpanAturan: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan database: ' . $e->getMessage()
        ];
    } catch (Exception $e) {
        error_log("Error in prosesSimpanAturan: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ];
    }
}

/**
 * Mengupdate aturan
 */
public function prosesUpdateAturan($id, $data) {
    try {
        // Validasi input
        if (empty($data['cf_pakar']) || empty($data['id_penyakit_hasil']) || empty($data['gejala_ids'])) {
            return [
                'success' => false,
                'message' => 'Semua field harus diisi (CF Pakar, Penyakit, dan minimal 1 Gejala)'
            ];
        }

        // Set data ke model
        $this->aturanModel->setIdAturan($id);
        $this->aturanModel->setIdPenyakit($data['id_penyakit_hasil']);
        $this->aturanModel->setCfPakar($data['cf_pakar']);
        
        // Mulai transaksi
        $this->db->beginTransaction();
        
        // Update aturan utama
        if (!$this->aturanModel->ubahAturan()) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Gagal mengupdate data aturan'
            ];
        }
        
        // Update relasi gejala
        if (!$this->aturanModel->addGejalaToAturan($id, $data['gejala_ids'])) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Gagal mengupdate relasi gejala'
            ];
        }
        
        $this->db->commit();
        return [
            'success' => true,
            'message' => 'Aturan berhasil diupdate'
        ];
        
    } catch (PDOException $e) {
        $this->db->rollBack();
        error_log("Error in prosesUpdateAturan: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan database: ' . $e->getMessage()
        ];
    } catch (Exception $e) {
        $this->db->rollBack();
        error_log("Error in prosesUpdateAturan: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ];
    }
}

/**
 * Menghapus aturan
 */
public function prosesHapusAturan($id) {
    try {
        // Cek apakah aturan ada
        $aturan = $this->aturanModel->lihatDetailAturan($id);
        if (!$aturan) {
            return [
                'success' => false,
                'message' => 'Aturan tidak ditemukan'
            ];
        }
        
        $result = $this->aturanModel->hapusAturan($id);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'Aturan berhasil dihapus'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal menghapus aturan'
            ];
        }
    } catch (PDOException $e) {
        error_log("Error in prosesHapusAturan: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan database: ' . $e->getMessage()
        ];
    } catch (Exception $e) {
        error_log("Error in prosesHapusAturan: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ];
    }
}
    
    /**
     * Mendapatkan semua penyakit untuk dropdown
     */
    private function getAllPenyakit() {
        try {
            $penyakit = $this->penyakitModel->lihatSemuaPenyakit();
            return [
                'success' => true,
                'data' => $penyakit
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengambil data penyakit: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Mendapatkan semua gejala untuk checkbox
     */
    public function getAllGejala() {
        try {
            $gejala = $this->gejalaModel->lihatSemuaGejala();
            return [
                'success' => true,
                'data' => $gejala
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengambil data gejala: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Mendapatkan aturan berdasarkan penyakit
     */
    public function getByPenyakit($idPenyakit) {
        try {
            $aturan = $this->aturanModel->getByPenyakit($idPenyakit);
            
            return [
                'success' => true,
                'data' => $aturan
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengambil aturan: ' . $e->getMessage()
            ];
        }
    }
    
    public function getGejalaByAturan($id_aturan) {
        $gejala = $this->aturanModel->getGejalaByAturan($id_aturan);
        return [
            'success' => true,
            'data' => $gejala
        ];
    }
    
    /**
     * Validasi data aturan
     */
    private function validateAturanData($data) {
        // Cek field yang required
        $requiredFields = ['id_penyakit', 'id_gejala', 'cf_pakar'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return [
                    'valid' => false,
                    'message' => 'Field ' . $field . ' harus diisi'
                ];
            }
        }
        
        // Validasi ID penyakit
        if (!is_numeric($data['id_penyakit']) || $data['id_penyakit'] <= 0) {
            return [
                'valid' => false,
                'message' => 'ID penyakit harus berupa angka positif'
            ];
        }
        
        // Validasi ID gejala
        if (!is_numeric($data['id_gejala']) || $data['id_gejala'] <= 0) {
            return [
                'valid' => false,
                'message' => 'ID gejala harus berupa angka positif'
            ];
        }
        
        // Validasi CF Pakar (harus antara 0 dan 1)
        if (!is_numeric($data['cf_pakar']) || $data['cf_pakar'] < 0 || $data['cf_pakar'] > 1) {
            return [
                'valid' => false,
                'message' => 'CF Pakar harus berupa angka antara 0 dan 1'
            ];
        }
        
        // Cek apakah penyakit ada
        $penyakit = $this->penyakitModel->getById($data['id_penyakit']);
        if (!$penyakit) {
            return [
                'valid' => false,
                'message' => 'Penyakit tidak ditemukan'
            ];
        }
        
        // Cek apakah gejala ada
        $gejala = $this->gejalaModel->getById($data['id_gejala']);
        if (!$gejala) {
            return [
                'valid' => false,
                'message' => 'Gejala tidak ditemukan'
            ];
        }
        
        return [
            'valid' => true,
            'message' => 'Data valid'
        ];
    }
    
public function handleRequest() {
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
        $action = $_GET['action'];
        
        if ($action === 'getGejalaByAturan' && isset($_GET['id_aturan'])) {
            $id_aturan = intval($_GET['id_aturan']);
            $result = $this->getGejalaByAturan($id_aturan);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit();
        }
    }
}

    /**
     * Import aturan dari array data
     */
    public function importAturan($dataArray) {
        try {
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            
            foreach ($dataArray as $index => $data) {
                $result = $this->prosesSimpanAturan($data);
                
                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Baris " . ($index + 1) . ": " . $result['message'];
                }
            }
            
            return [
                'success' => true,
                'message' => "Import selesai. Berhasil: {$successCount}, Gagal: {$errorCount}",
                'details' => [
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'errors' => $errors
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal melakukan import: ' . $e->getMessage()
            ];
        }
    }
}
?>