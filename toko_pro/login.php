<?php
session_start();
include 'db.php';

// --- LOGIKA PHP LOGIN ---
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($check) > 0) {
        $data = mysqli_fetch_assoc($check);
        
        if (password_verify($password, $data['password'])) {
            $_SESSION['user_id'] = $data['id'];
            $_SESSION['name'] = $data['name'];
            $_SESSION['role'] = $data['role'];

            if ($data['role'] == 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak terdaftar!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SHOP COMMERCE</title>
    
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

        /* --- LEFT SIDE (IMAGE & ANIMATION) --- */
        .login-image {
            /* Gambar Background dengan Overlay Gradient Bergerak */
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            height: 100vh;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            overflow: hidden;
        }

        /* Overlay gambar fashion agar menyatu */
        .login-image::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=2070&auto=format&fit=crop') center/cover;
            opacity: 0.4; /* Transparan agar warna gradient terlihat */
            mix-blend-mode: multiply;
        }

        .image-content {
            position: relative;
            z-index: 2;
            padding: 2rem;
            backdrop-filter: blur(5px);
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* --- RIGHT SIDE (FORM) --- */
        .login-form-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            background-color: #ffffff;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
        }

        .brand-logo {
            font-size: 1.8rem;
            font-weight: 800;
            background: -webkit-linear-gradient(45deg, #667eea, #764ba2);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 30px;
            display: inline-block;
            text-decoration: none;
        }

        /* Input Styling */
        .form-floating > .form-control {
            border: 2px solid #f0f0f0;
            border-radius: 12px;
            padding-left: 20px;
            transition: 0.3s;
        }
        
        .form-floating > .form-control:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 4px rgba(118, 75, 162, 0.1);
        }

        .form-floating > label { padding-left: 20px; }

        /* Button Styling (Gradient Animation) */
        .btn-login {
            background-image: linear-gradient(to right, #667eea, #764ba2, #6B8DD6, #8E37D7);
            box-shadow: 0 4px 15px 0 rgba(116, 79, 168, 0.45);
            width: 100%;
            border: none;
            border-radius: 50px;
            padding: 15px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            background-size: 300% 100%;
            transition: all .4s ease-in-out;
        }

        .btn-login:hover {
            background-position: 100% 0;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px 0 rgba(116, 79, 168, 0.6);
        }

        /* Links */
        .link-text { color: #764ba2; text-decoration: none; font-weight: 600; transition: 0.3s; }
        .link-text:hover { color: #5a3780; text-decoration: underline; }

        /* Alert */
        .alert-custom { border-radius: 12px; font-size: 0.9rem; border: none; background-color: #fee2e2; color: #b91c1c; }

        /* Responsif */
        @media (max-width: 768px) {
            .login-image { display: none; }
            .login-form-container { background: #f8f9fa; }
            .login-card { background: white; padding: 2.5rem; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        
        <!-- BAGIAN KIRI: Animasi & Gambar -->
        <div class="col-md-7 col-lg-8 login-image animate__animated animate__fadeIn">
            <div class="image-content animate__animated animate__zoomIn">
                <h1 class="fw-bold display-4 mb-3">Welcome Back!</h1>
                <p class="lead mb-0">Temukan gaya terbaikmu dengan harga terbaik hanya di SHOP COMMERCE.</p>
            </div>
        </div>

        <!-- BAGIAN KANAN: Form Login -->
        <div class="col-md-5 col-lg-4 login-form-container">
            <div class="login-card animate__animated animate__fadeInRight">
                
                <a href="index.php" class="brand-logo">
                    <i class="fas fa-shopping-bag me-2"></i> SHOP COMMERCE
                </a>
                
                <h2 class="fw-bold mb-2 text-dark">Halo, Selamat Datang 👋</h2>
                <p class="text-muted mb-4">Masuk untuk melanjutkan aktivitas belanja Anda.</p>

                <!-- Error Message -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-custom d-flex align-items-center mb-4 animate__animated animate__shakeX" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Email -->
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                        <label for="email">Alamat Email</label>
                    </div>

                    <!-- Password -->
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>

                    <!-- Options -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label small text-muted" for="rememberMe">Ingat Saya</label>
                        </div>
                        <a href="lupa_password.php" class="small link-text">Lupa Password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="login" class="btn btn-login">
                        Masuk Sekarang <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </form>

                <!-- Register Link -->
                <div class="text-center mt-5">
                    <span class="text-muted small">Belum punya akun?</span>
                    <a href="register.php" class="small link-text ms-1">Daftar Disini</a>
                </div>

            </div> 
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>