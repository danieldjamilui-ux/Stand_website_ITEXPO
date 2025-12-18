<?php
session_start();
include 'db.php';

// --- LOGIKA CEK EMAIL ---
if (isset($_POST['cek_email'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Cek apakah email ada di database
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    
    if (mysqli_num_rows($check) > 0) {
        // Jika ada, simpan email ke session sementara
        $_SESSION['reset_email'] = $email;
        header("Location: reset_password.php");
        exit;
    } else {
        $error = "Email tidak ditemukan dalam sistem kami.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SHOP COMMERCE</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            overflow: hidden;
            background: #fff;
        }

        /* --- SISI KIRI (ANIMASI) --- */
        .side-image {
            /* Gradient Animasi */
            background: linear-gradient(-45deg, #764ba2, #667eea, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            position: relative;
        }

        /* Overlay Pattern/Image */
        .side-image::before {
            content: "";
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=2070&auto=format&fit=crop') center/cover;
            opacity: 0.2; /* Transparan agar gradient tetap menonjol */
            mix-blend-mode: overlay;
        }

        .image-content {
            position: relative;
            z-index: 2;
            padding: 3rem;
            max-width: 80%;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* --- SISI KANAN (FORM) --- */
        .form-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-color: #ffffff;
        }

        .form-card { width: 100%; max-width: 450px; }

        /* Judul */
        .brand-text {
            background: -webkit-linear-gradient(45deg, #667eea, #764ba2);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }

        /* Input Modern */
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
        .form-floating > label { padding-left: 20px; color: #999; }

        /* Tombol Gradient */
        .btn-custom {
            background-image: linear-gradient(to right, #667eea, #764ba2, #6B8DD6);
            background-size: 200% auto;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 50px;
            font-weight: 600;
            width: 100%;
            transition: 0.5s;
            box-shadow: 0 4px 15px rgba(118, 75, 162, 0.3);
        }
        .btn-custom:hover {
            background-position: right center;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(118, 75, 162, 0.5);
            color: white;
        }

        /* Link Kembali */
        .back-link {
            text-decoration: none;
            color: #764ba2;
            font-weight: 600;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
        }
        .back-link:hover { color: #5a3780; transform: translateX(-5px); }
        .back-link i { margin-right: 8px; transition: 0.3s; }

        /* Alert Error */
        .alert-custom { border-radius: 12px; border: none; background-color: #fee2e2; color: #b91c1c; font-size: 0.9rem; }

        /* Responsif */
        @media (max-width: 768px) {
            .side-image { display: none; }
            .form-container { background: #f8f9fa; }
            .form-card { background: white; padding: 2.5rem; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        
        <!-- BAGIAN KIRI: GAMBAR & ANIMASI -->
        <div class="col-md-7 col-lg-8 side-image animate__animated animate__fadeIn">
            <div class="image-content animate__animated animate__fadeInUp animate__delay-1s">
                <h1 class="display-3 fw-bold mb-3">Jangan Panik! <i class="fas fa-laugh-beam"></i></h1>
                <p class="lead opacity-75">Lupa password adalah hal yang wajar. Cukup masukkan email Anda dan kami akan membantu memulihkannya dalam hitungan detik.</p>
            </div>
        </div>

        <!-- BAGIAN KANAN: FORM -->
        <div class="col-md-5 col-lg-4 form-container">
            <div class="form-card animate__animated animate__fadeInRight">
                
                <div class="mb-5">
                    <h3 class="brand-text mb-2"><i class="fas fa-unlock-alt"></i> Recovery</h3>
                    <h2 class="fw-bold text-dark">Lupa Password? 🔒</h2>
                    <p class="text-muted">Masukkan email yang terdaftar untuk mereset kata sandi.</p>
                </div>

                <!-- Pesan Error (Animasi Shake) -->
                <?php if (isset($error)): ?>
                    <div class="alert alert-custom d-flex align-items-center mb-4 animate__animated animate__shakeX">
                        <i class="fas fa-exclamation-circle me-2"></i> 
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-floating mb-4">
                        <input type="email" name="email" class="form-control" id="floatingEmail" placeholder="name@example.com" required>
                        <label for="floatingEmail">Alamat Email</label>
                    </div>
                    
                    <button type="submit" name="cek_email" class="btn btn-custom mb-4">
                        Verifikasi Email <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </form>

                <div class="text-center">
                    <a href="login.php" class="back-link">
                        <i class="fas fa-arrow-left"></i> Kembali ke Login
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>