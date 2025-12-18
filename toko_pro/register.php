<?php
include 'db.php';

// --- LOGIKA REGISTER ---
if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Cek apakah email sudah ada
    $check = mysqli_query($conn, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Email sudah terdaftar! Silakan gunakan email lain.";
    } else {
        // Simpan data user baru
        $insert = mysqli_query($conn, "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')");
        if ($insert) {
            echo "<script>
                alert('Pendaftaran Berhasil! Silakan Login.');
                window.location = 'login.php';
            </script>";
            exit;
        } else {
            $error = "Terjadi kesalahan saat mendaftar.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Toko Pro</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Animate.css (Library Animasi) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            overflow: hidden;
            background: #fff;
        }

        /* --- SISI KIRI (GAMBAR & ANIMASI) --- */
        .register-image {
            /* Gradient Bergerak */
            background: linear-gradient(-45deg, #483d8b, #667eea, #764ba2, #ba55d3);
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 3rem;
            position: relative;
        }

        /* Overlay Foto Toko (Opsional, transparan) */
        .register-image::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=2070&auto=format&fit=crop') center/cover;
            opacity: 0.2;
            mix-blend-mode: overlay;
        }

        .image-content {
            position: relative;
            z-index: 2;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* --- SISI KANAN (FORM) --- */
        .register-form-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-color: #ffffff;
        }

        .register-card {
            width: 100%;
            max-width: 450px;
        }

        .brand-logo {
            font-size: 1.5rem;
            font-weight: 800;
            background: -webkit-linear-gradient(45deg, #667eea, #764ba2);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            display: inline-block;
            text-decoration: none;
        }

        /* Input Styling */
        .form-floating > .form-control {
            border: 2px solid #f1f1f1;
            border-radius: 12px;
            padding-left: 20px;
            transition: 0.3s;
        }
        .form-floating > .form-control:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 4px rgba(118, 75, 162, 0.1);
        }
        .form-floating > label { padding-left: 20px; color: #999; }

        /* Button Styling */
        .btn-register {
            background-image: linear-gradient(to right, #667eea, #764ba2, #9370DB);
            background-size: 200% auto;
            border: none;
            border-radius: 50px;
            padding: 14px;
            color: white;
            font-weight: 600;
            width: 100%;
            transition: 0.5s;
            box-shadow: 0 4px 15px rgba(118, 75, 162, 0.3);
        }
        .btn-register:hover {
            background-position: right center; /* Efek gerak gradient */
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(118, 75, 162, 0.5);
            color: white;
        }

        /* Link Login */
        .login-link {
            color: #764ba2;
            font-weight: 700;
            text-decoration: none;
            transition: 0.3s;
        }
        .login-link:hover { color: #483d8b; text-decoration: underline; }

        /* Alert Error Shake */
        .alert-custom { border-radius: 12px; font-size: 0.9rem; background-color: #fee2e2; color: #b91c1c; border: none; }

        /* Responsif Mobile */
        @media (max-width: 768px) {
            .register-image { display: none; }
            .register-form-container { background: #f8f9fa; height: auto; min-height: 100vh; }
            .register-card { background: white; padding: 2.5rem; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        
        <!-- BAGIAN KIRI: Animasi Background -->
        <div class="col-md-7 col-lg-8 register-image animate__animated animate__fadeIn">
            <div class="image-content animate__animated animate__fadeInUp animate__delay-1s">
                <h1 class="display-3 fw-bold mb-3">Bergabunglah!</h1>
                <p class="lead opacity-90">Buat akun sekarang untuk mendapatkan akses ke ribuan produk eksklusif dan penawaran menarik.</p>
            </div>
        </div>

        <!-- BAGIAN KANAN: Form Register -->
        <div class="col-md-5 col-lg-4 register-form-container">
            <div class="register-card animate__animated animate__fadeInRight">
                
                <a href="index.php" class="brand-logo">
                    <i class="fas fa-shopping-bag me-2"></i> TOKO PRO
                </a>
                
                <h2 class="fw-bold mb-2">Buat Akun Baru 🚀</h2>
                <p class="text-muted mb-4">Lengkapi data diri Anda untuk mendaftar.</p>

                <!-- Pesan Error -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-custom d-flex align-items-center mb-4 animate__animated animate__headShake">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Input Nama -->
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="floatingName" name="name" placeholder="Nama Lengkap" required>
                        <label for="floatingName">Nama Lengkap</label>
                    </div>

                    <!-- Input Email -->
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="floatingEmail" name="email" placeholder="name@example.com" required>
                        <label for="floatingEmail">Alamat Email</label>
                    </div>

                    <!-- Input Password -->
                    <div class="form-floating mb-4">
                        <input type="password" class="form-control" id="floatingPassword" name="password" placeholder="Password" required>
                        <label for="floatingPassword">Password</label>
                    </div>

                    <!-- Tombol Submit -->
                    <button type="submit" name="register" class="btn btn-register mb-4">
                        Daftar Sekarang <i class="fas fa-user-plus ms-2"></i>
                    </button>
                </form>

                <!-- Link ke Login -->
                <div class="text-center">
                    <span class="text-muted small">Sudah punya akun?</span>
                    <a href="login.php" class="login-link ms-1">Login Disini</a>
                </div>

            </div> <!-- End Card -->
        </div> <!-- End Col Kanan -->
    </div> <!-- End Row -->
</div> <!-- End Container Fluid -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>