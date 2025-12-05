<?php

require_once __DIR__ . '/../models/Gejala.php';
require_once __DIR__ . '/../config/database.php';

class GejalaController {
    private $db;
    private $gejala;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->gejala = new Gejala($this->db);
    }
    
    public function index() {
        return $this->tampilkanDaftarGejala(1, 10);
    }
    
    /**
     * Menampilkan daftar gejala dengan pagination
     * @param int $page Halaman saat ini
     * @param int $limit Jumlah data per halaman
     * @param string $search Kata kunci pencarian
     * @return array Data gejala dengan pagination
     */
    public function tampilkanDaftarGejala($page = 1, $limit = 10, $search = '') {
        try {
            $offset = ($page - 1) * $limit;
            
            // Ambil data gejala
            $gejala_list = $this->gejala->lihatSemuaGejala();
            
            // Hitung total data
            $total = count($gejala_list);
            $total_pages = ceil($total / $limit);
            
            return [
                'success' => true,
                'data' => $gejala_list,
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
     * Menampilkan form tambah gejala
     * @return array Data untuk form
     */
    public function tampilkanFormTambahGejala() {
        return [
            'success' => true,
            'data' => [
                'title' => 'Tambah Gejala Baru',
                'action' => 'tambah'
            ]
        ];
    }
    
    /**
     * Menampilkan form edit gejala
     * @param int $id ID gejala
     * @return array Data untuk form
     */
    public function tampilkanFormEditGejala($id) {
        try {
            $gejala_data = $this->gejala->findById($id);
            
            if (!$gejala_data) {
                return [
                    'success' => false,
                    'message' => 'Gejala tidak ditemukan'
                ];
            }
            
            return [
                'success' => true,
                'data' => [
                    'title' => 'Edit Gejala',
                    'action' => 'edit',
                    'gejala' => $gejala_data
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
     * Simpan gejala baru
     * @param array $data Data gejala
     * @return array Response dengan status
     */
 public function prosesSimpanGejala($data) {
    try {
        // Validasi input
        $validation = $this->validateGejala($data);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $validation['errors']
            ];
        }
        
        // Set data gejala
        $this->gejala->setNamaGejala($data['nama_gejala']);
        // Di dalam prosesSimpanGejala dan prosesUpdateGejala, tambahkan:
$this->gejala->setKodeGejala($data['kode_gejala']);
        $this->gejala->setDeskripsiGejala($data['deskripsi'] ?? '');
        
        // Simpan gejala
        if ($this->gejala->save()) {
            return [
                'success' => true,
                'message' => 'Gejala berhasil ditambahkan',
                'data' => [
                    'id' => $this->gejala->getId(),
                    'nama_gejala' => $this->gejala->getNamaGejala()
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal menyimpan gejala'
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
     * Update gejala
     * @param int $id ID gejala
     * @param array $data Data gejala yang akan diupdate
     * @return array Response dengan status
     */
public function prosesUpdateGejala($id, $data) {
    try {
        // Cek apakah gejala ada
        $existing_gejala = $this->gejala->findById($id);
        if (!$existing_gejala) {
            return [
                'success' => false,
                'message' => 'Gejala tidak ditemukan'
            ];
        }
        
        // Validasi input
        $validation = $this->validateGejala($data, $id);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $validation['errors']
            ];
        }
        
        // Set data gejala
        $this->gejala->setId($id);
        $this->gejala->setNamaGejala($data['nama_gejala']);
        $this->gejala->setDeskripsiGejala($data['deskripsi'] ?? '');
        
        // Update gejala
        if ($this->gejala->save()) {
            return [
                'success' => true,
                'message' => 'Gejala berhasil diupdate',
                'data' => [
                    'id' => $this->gejala->getId(),
                    'nama_gejala' => $this->gejala->getNamaGejala()
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal mengupdate gejala'
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
     * Hapus gejala
     * @param int $id ID gejala
     * @return array Response dengan status
     */
public function prosesHapusGejala($id) {
    try {
        // Cek apakah gejala ada
        $existing_gejala = $this->gejala->findById($id);
        if (!$existing_gejala) {
            return [
                'success' => false,
                'message' => 'Gejala tidak ditemukan'
            ];
        }
        
        // Cek apakah gejala sedang digunakan dalam aturan
        if ($this->isGejalaUsedInRules($id)) {
            return [
                'success' => false,
                'message' => 'Gejala tidak dapat dihapus karena sedang digunakan dalam aturan diagnosis'
            ];
        }
        
        // Hapus gejala
        $this->gejala->setId($id);
        if ($this->gejala->delete()) {
            return [
                'success' => true,
                'message' => 'Gejala berhasil dihapus'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Gagal menghapus gejala'
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
     * Validasi data gejala
     * @param array $data Data yang akan divalidasi
     * @param int|null $id ID gejala untuk update (optional)
     * @return array Hasil validasi
     */
private function validateGejala($data, $id = null) {
    $errors = [];
    
    // Validasi nama gejala
    if (empty($data['nama_gejala'])) {
        $errors[] = 'Nama gejala harus diisi';
    } elseif (strlen($data['nama_gejala']) < 3) {
        $errors[] = 'Nama gejala minimal 3 karakter';
    }
    
    // Validasi deskripsi (optional)
    if (isset($data['deskripsi']) && strlen($data['deskripsi']) > 500) {
        $errors[] = 'Deskripsi maksimal 500 karakter';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
    
    /**
     * Cek apakah gejala sedang digunakan dalam aturan
     * @param int $id_gejala ID gejala
     * @return bool True jika sedang digunakan
     */
private function isGejalaUsedInRules($id_gejala) {
    $query = "SELECT COUNT(*) as count FROM aturan_gejala WHERE id_gejala = :id_gejala";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':id_gejala', $id_gejala);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['count'] > 0;
}
    
    /**
     * Dapatkan semua gejala untuk dropdown/select
     * @return array Daftar gejala
     */
    public function getAllForSelect() {
        try {
            $gejala_list = $this->gejala->getAll();
            
            return [
                'success' => true,
                'data' => $gejala_list
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
}

?>