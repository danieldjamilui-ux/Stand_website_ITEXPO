<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Mulai sesi jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

// Redirect jika admin sudah login
if (is_admin_logged_in()) {
    redirect('dashboard.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = clean_input($_POST["username"]);
    $password = clean_input($_POST["password"]);
    
    // Validasi input
    if (empty($username) || empty($password)) {
        $error = "Username dan password harus diisi.";
    } else {
        // Cek admin di database
        $stmt = $db->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin) {
            $hash = $admin['password'];

            // Debug opsional untuk lihat isi hash
            // echo "<pre>"; print_r($admin); echo "</pre>"; exit;

            // Login berhasil jika password cocok
            if (password_verify($password, $hash) || $password === $hash) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_name'] = $admin['name'];

                redirect('dashboard.php');
            } else {
                $error = "Username atau password salah.";
            }
        } else {
            $error = "Username atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Sistem Rekomendasi Skincare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page-admin">
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 auth-navbar">
            <div class="container">
                <a class="navbar-brand" href="/SPK-Skincare/index.php">
                    <img src="../assets/images/logo.png" alt="Logo" height="40" class="mr-2" onerror="this.src='https://via.placeholder.com/40x40?text=SK'">
                    Sistem Rekomendasi Skincare
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/SPK-Skincare/user/login.php">Login Klien</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    
    <div class="container flex-grow-1 d-flex align-items-center">
        <div class="row justify-content-center w-100 auth-container">
            <div class="col-md-5">
                <div class="card auth-card auth-card-admin">
                    <div class="card-header bg-admin">
                        <h4 class="mb-0">Login Admin Panel</h4>
                    </div>
                    <div class="card-body">
                        <div class="brand-logo">
                            <img src="../assets/images/logo.png" alt="Logo" onerror="this.src='https://via.placeholder.com/80x80?text=Admin'">
                            <div class="brand-text" style="color: var(--admin-color);">Admin Dashboard</div>
                            <div class="brand-tagline">Kelola sistem rekomendasi skincare</div>
                        </div>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger auth-alert">
                                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="" class="mt-4">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <div class="input-group">
                                    <i class="fas fa-user input-icon" style="color: var(--admin-color);"></i>
                                    <input type="text" class="form-control input-with-icon" id="username" name="username" placeholder="Masukkan username" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">Password</label>
                                <div class="input-group">
                                    <i class="fas fa-lock input-icon" style="color: var(--admin-color);"></i>
                                    <input type="password" class="form-control input-with-icon" id="password" name="password" placeholder="Masukkan password" required>
                                    <span class="toggle-password" onclick="togglePassword()" style="color: var(--admin-color);">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-admin btn-block mt-4">
                                <i class="fas fa-sign-in-alt mr-2"></i> Masuk
                            </button>
                        </form>
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
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password i');
            
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
