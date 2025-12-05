<?php

require_once 'models/Pengguna.php';

class AuthController {
    private $db;
    private $pengguna;
    
    public function __construct($database) {
        $this->db = $database;
        $this->pengguna = new Pengguna($this->db);
    }
    
    /**
     * Proses login pengguna
     * @param string $username Username
     * @param string $password Password
     * @return array Response dengan status dan pesan
     */
    public function prosesLogin($username, $password) {
        try {
            // Validasi input
            if (empty($username) || empty($password)) {
                return [
                    'success' => false,
                    'message' => 'Username dan password harus diisi'
                ];
            }
            
            // Coba login
            $user = $this->pengguna->login($username, $password);
            
            if ($user) {
                // Set session
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['login_time'] = time();
                
                return [
                    'success' => true,
                    'message' => 'Login berhasil',
                    'user' => $user,
                    'redirect' => 'dashboard.php'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Username atau password salah'
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
     * Proses logout pengguna
     * @return array Response dengan status dan pesan
     */
    public function prosesLogout() {
        session_start();
        
        // Hapus semua session
        $_SESSION = array();
        
        // Hapus session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
        
        return [
            'success' => true,
            'message' => 'Logout berhasil',
            'redirect' => 'index.php'
        ];
    }
    
    /**
     * Cek apakah user sudah login
     * @return bool True jika sudah login
     */
    public function isLoggedIn() {
        session_start();
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Dapatkan data user yang sedang login
     * @return array|null Data user atau null jika belum login
     */
    public function getCurrentUser() {
        session_start();
        
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'nama_lengkap' => $_SESSION['nama_lengkap'],
                'login_time' => $_SESSION['login_time'] ?? null
            ];
        }
        
        return null;
    }
    
    /**
     * Redirect ke halaman login jika belum login
     * @param string $redirect_to URL untuk redirect setelah login
     */
    public function requireLogin($redirect_to = null) {
        if (!$this->isLoggedIn()) {
            $redirect_url = 'index.php';
            if ($redirect_to) {
                $redirect_url .= '?redirect=' . urlencode($redirect_to);
            }
            header('Location: ' . $redirect_url);
            exit();
        }
    }
    
    /**
     * Proses registrasi pengguna baru
     * @param array $data Data pengguna
     * @return array Response dengan status dan pesan
     */
    public function prosesRegistrasi($data) {
        try {
            // Validasi input
            $validation = $this->validateRegistration($data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Data tidak valid',
                    'errors' => $validation['errors']
                ];
            }
            
            // Cek apakah username sudah ada
            if ($this->pengguna->findByUsername($data['username'])) {
                return [
                    'success' => false,
                    'message' => 'Username sudah digunakan'
                ];
            }
            
            // Set data pengguna
            $this->pengguna->setUsername($data['username']);
            $this->pengguna->setPassword($data['password']);
            $this->pengguna->setNamaLengkap($data['nama_lengkap']);
            
            // Simpan pengguna
            if ($this->pengguna->save()) {
                return [
                    'success' => true,
                    'message' => 'Registrasi berhasil',
                    'user_id' => $this->pengguna->getId()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal menyimpan data pengguna'
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
     * Validasi data registrasi
     * @param array $data Data yang akan divalidasi
     * @return array Hasil validasi
     */
    private function validateRegistration($data) {
        $errors = [];
        
        // Validasi username
        if (empty($data['username'])) {
            $errors[] = 'Username harus diisi';
        } elseif (strlen($data['username']) < 3) {
            $errors[] = 'Username minimal 3 karakter';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors[] = 'Username hanya boleh mengandung huruf, angka, dan underscore';
        }
        
        // Validasi password
        if (empty($data['password'])) {
            $errors[] = 'Password harus diisi';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        }
        
        // Validasi nama lengkap
        if (empty($data['nama_lengkap'])) {
            $errors[] = 'Nama lengkap harus diisi';
        } elseif (strlen($data['nama_lengkap']) < 2) {
            $errors[] = 'Nama lengkap minimal 2 karakter';
        }
        
        // Validasi konfirmasi password
        if (isset($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
            $errors[] = 'Konfirmasi password tidak cocok';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Update password pengguna
     * @param int $user_id ID pengguna
     * @param string $old_password Password lama
     * @param string $new_password Password baru
     * @return array Response dengan status dan pesan
     */
    public function changePassword($user_id, $old_password, $new_password) {
        try {
            // Ambil data pengguna
            $this->pengguna->setId($user_id);
            $user_data = $this->pengguna->findById($user_id);
            
            if (!$user_data) {
                return [
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan'
                ];
            }
            
            // Verifikasi password lama
            if (!password_verify($old_password, $user_data['password'])) {
                return [
                    'success' => false,
                    'message' => 'Password lama tidak benar'
                ];
            }
            
            // Validasi password baru
            if (strlen($new_password) < 6) {
                return [
                    'success' => false,
                    'message' => 'Password baru minimal 6 karakter'
                ];
            }
            
            // Update password
            $this->pengguna->setPassword($new_password);
            
            if ($this->pengguna->save()) {
                return [
                    'success' => true,
                    'message' => 'Password berhasil diubah'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Gagal mengubah password'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
}

?>