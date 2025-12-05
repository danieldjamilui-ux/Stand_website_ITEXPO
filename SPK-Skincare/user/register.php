<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = clean_input($_POST["username"]);
    $password = clean_input($_POST["password"]);
    $confirm_password = clean_input($_POST["confirm_password"]);
    $name = clean_input($_POST["name"]);
    $email = clean_input($_POST["email"]);
    
    // Validasi input
    if (empty($username) || empty($password) || empty($confirm_password) || empty($name) || empty($email)) {
        $error = "Semua field harus diisi.";
    } elseif ($password != $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error = "Password harus minimal 6 karakter.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // Cek apakah username atau email sudah digunakan
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $error = "Username atau email sudah digunakan.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Simpan user baru
            $stmt = $db->prepare("INSERT INTO users (username, password, name, email) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$username, $hashed_password, $name, $email]);
            
            if ($result) {
                $success = "Registrasi berhasil! Silakan login.";
            } else {
                $error = "Terjadi kesalahan. Silakan coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Sistem Rekomendasi Skincare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 auth-navbar">
            <div class="container">
                <a class="navbar-brand" href="/SPK-Skincare/index.php">
                    <img src="../assets/images/Logo.png" alt="Logo" height="40" class="mr-2" onerror="this.src='https://via.placeholder.com/40x40?text=SK'">
                    BeautiFi
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <div class="container flex-grow-1 d-flex align-items-center">
        <div class="row justify-content-center w-100 auth-container">
            <div class="col-md-6">
                <div class="card auth-card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Buat Akun Baru</h4>
                    </div>
                    <div class="card-body">
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger auth-alert">
                                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success auth-alert">
                                <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="" class="mt-4">
                            <div class="form-group">
                                <label for="name">Nama Lengkap</label>
                                <div class="input-group">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" class="form-control input-with-icon" id="name" name="name" placeholder="Masukkan nama lengkap" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email</label>
                                <div class="input-group">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" class="form-control input-with-icon" id="email" name="email" placeholder="Masukkan email" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="username">Username</label>
                                <div class="input-group">
                                    <i class="fas fa-id-badge input-icon"></i>
                                    <input type="text" class="form-control input-with-icon" id="username" name="username" placeholder="Masukkan username" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" class="form-control input-with-icon" id="password" name="password" placeholder="Masukkan password" required>
                                    <span class="toggle-password" onclick="togglePassword('password')">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                <small class="form-text text-muted">Password minimal 6 karakter.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Konfirmasi Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input type="password" class="form-control input-with-icon" id="confirm_password" name="confirm_password" placeholder="Konfirmasi password" required>
                                    <span class="toggle-password" onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-auth btn-block mt-4">
                                <i class="fas fa-user-plus mr-2"></i> Daftar Sekarang
                            </button>
                        </form>
                        
                        <div class="auth-footer">
                            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleIcon = passwordField.parentNode.querySelector('.toggle-password i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>