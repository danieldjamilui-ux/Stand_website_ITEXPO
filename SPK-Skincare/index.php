<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    redirect('user/dashboard.php');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang - Sistem Rekomendasi Skincare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <!-- Navbar Khusus Landing Page -->
    <nav class="navbar navbar-expand-lg navbar-landing">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/Logo.png" alt="Logo" height="45" class="mr-2">
                <span>BeautiFi</span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarLanding" aria-controls="navbarLanding" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarLanding">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Login
                        </a>
                        <div class="dropdown-menu" aria-labelledby="loginDropdown">
                            <a class="dropdown-item" href="user/login.php">Login Klien</a>
                            <a class="dropdown-item" href="admin/login.php">Login Admin</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary" href="user/register.php">Daftar Sekarang</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 font-weight-bold mb-4">Temukan Skincare Terbaik untuk Kulitmu</h1>
                    <p class="lead mb-4">Sistem rekomendasi skincare berbasis AI yang akan membantu menemukan produk yang cocok dengan jenis dan masalah kulitmu.</p>
                    <div class="mt-5">
                        <a class="btn btn-light btn-lg btn-landing mr-3" href="user/register.php">Daftar Sekarang</a>
                        <a class="btn btn-outline-light btn-lg btn-landing" href="user/login.php">Login</a>
                    </div>
                </div>
                <div class="col-md-6 text-center">
                    <img src="assets/images/image.png" alt="Skincare Products" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </section>
    
    <div class="container">
        <!-- Features Section -->
        <div class="row my-5">
            <div class="col-12 text-center mb-5">
                <h2 class="font-weight-bold">Fitur Utama</h2>
                <p class="lead text-muted">Apa yang kami tawarkan untuk perawatan kulitmu</p>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-user-check feature-icon"></i>
                        <h4 class="card-title">Profil Kulit Personal</h4>
                        <p class="card-text">Buat profil kulit personal dengan mengidentifikasi jenis kulit dan masalah kulit yang kamu alami.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-magic feature-icon"></i>
                        <h4 class="card-title">Rekomendasi AI</h4>
                        <p class="card-text">Dapatkan rekomendasi produk skincare yang dipersonalisasi berdasarkan algoritma AI canggih.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card">
                    <div class="card-body text-center p-4">
                        <i class="fas fa-history feature-icon"></i>
                        <h4 class="card-title">Riwayat & Tracking</h4>
                        <p class="card-text">Simpan dan lacak riwayat rekomendasi untuk melihat perkembangan perawatan kulitmu.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- How It Works Section -->
        <div class="row my-5">
            <div class="col-12 text-center mb-5">
                <h2 class="font-weight-bold">Bagaimana Cara Kerjanya?</h2>
                <p class="lead text-muted">Empat langkah mudah untuk mendapatkan rekomendasi skincare</p>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h5 class="text-center">Daftar Akun</h5>
                    <p class="text-center">Buat akun baru dengan mengisi data diri kamu.</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h5 class="text-center">Buat Profil Kulit</h5>
                    <p class="text-center">Pilih jenis kulit dan masalah kulit yang kamu alami.</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h5 class="text-center">Dapatkan Rekomendasi</h5>
                    <p class="text-center">Sistem akan memberikan rekomendasi produk yang cocok untukmu.</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="step-card">
                    <div class="step-number">4</div>
                    <h5 class="text-center">Lihat Detail Produk</h5>
                    <p class="text-center">Lihat detail produk dan alasan mengapa produk tersebut direkomendasikan.</p>
                </div>
            </div>
        </div>
        
        <!-- CTA Section -->
        <div class="row my-5">
    <div class="col-12">
        <div class="card text-white" style="background-color:rgb(242, 139, 182);">
            <div class="card-body p-5 text-center">
                <h2 class="font-weight-bold mb-4">Siap Mendapatkan Rekomendasi Skincare?</h2>
                <p class="lead mb-4">Daftar sekarang dan temukan produk skincare yang cocok untuk kulitmu!</p>
                <a href="user/register.php" class="btn btn-gradient-pink btn-lg btn-landing text-white">Daftar Sekarang</a>
            </div>
        </div>
    </div>
</div>

    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
