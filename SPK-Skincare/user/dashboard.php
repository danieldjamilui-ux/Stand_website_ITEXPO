<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../models/Product.php';

// Cek apakah user sudah login
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($db, $user_id);
$skin_profile = get_user_skin_profile($db, $user_id);

// Inisialisasi objek Product untuk mendapatkan produk terbaru
$productObj = new Product($db);

// Set latest_products menjadi array kosong agar menampilkan pesan tidak ada produk
$latest_products = [];

// Ambil tips skincare
$skincare_tips = [
    [
        'title' => 'Membersihkan Wajah 2x Sehari',
        'description' => 'Membersihkan wajah di pagi dan malam hari membantu menghilangkan kotoran, minyak, dan sisa makeup yang dapat menyumbat pori-pori.',
        'icon' => 'fas fa-soap'
    ],
    [
        'title' => 'Gunakan Sunscreen Setiap Hari',
        'description' => 'Perlindungan dari sinar UV sangat penting untuk mencegah penuaan dini dan kerusakan kulit. Gunakan SPF minimal 30.',
        'icon' => 'fas fa-sun'
    ],
    [
        'title' => 'Hidrasi Kulit dengan Pelembab',
        'description' => 'Pelembab membantu menjaga kelembaban kulit dan memperkuat skin barrier. Pilih sesuai jenis kulit Anda.',
        'icon' => 'fas fa-tint'
    ],
    [
        'title' => 'Konsistensi adalah Kunci',
        'description' => 'Hasil skincare yang baik membutuhkan konsistensi. Gunakan produk secara teratur untuk melihat perubahan.',
        'icon' => 'fas fa-calendar-check'
    ]
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Rekomendasi Skincare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-5 mb-5">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>Selamat Datang, <?php echo htmlspecialchars($user['name']); ?>!</h2>
                    <p class="lead mb-0">Temukan rekomendasi skincare terbaik untuk jenis kulitmu dan pelajari tips perawatan kulit yang tepat.</p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="profile-icon">
                        <i class="fas fa-user-circle fa-6x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Access Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-4">
                <div class="card card-dashboard">
                    <div class="card-body text-center">
                        <i class="fas fa-id-card feature-icon"></i>
                        <h5 class="card-title">Profil Kulit</h5>
                        <p class="card-text">Kelola profil kulit dan perbarui masalah kulit yang kamu alami.</p>
                        <a href="profile.php" class="btn btn-primary">Lihat Profil</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card card-dashboard">
                    <div class="card-body text-center">
                        <i class="fas fa-magic feature-icon"></i>
                        <h5 class="card-title">Rekomendasi</h5>
                        <p class="card-text">Dapatkan rekomendasi produk skincare yang sesuai dengan jenis kulitmu.</p>
                        <a href="recommendation.php" class="btn btn-primary">Lihat Rekomendasi</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card card-dashboard">
                    <div class="card-body text-center">
                        <i class="fas fa-list-alt feature-icon"></i>
                        <h5 class="card-title">Produk Skincare</h5>
                        <p class="card-text">Lihat detail produk skincare yang tersedia.</p>
                        <a href="products.php" class="btn btn-primary">Lihat Produk</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Skin Profile Summary -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Profil Kulit Saat Ini</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($skin_profile): ?>
                            <h6 class="section-title">Jenis Kulit:</h6>
                            <p class="mb-3"><span class="badge badge-info p-2"><?php echo $skin_profile['skin_type_name']; ?></span></p>
                            
                            <?php 
                            // Ambil masalah kulit user
                            $user_concerns = [];
                            if ($skin_profile) {
                                $profile_id = $skin_profile['profile_id'];
                                $user_concerns = get_user_skin_concerns($db, $profile_id);
                            }
                            
                            if (!empty($user_concerns)): ?>
                                <h6 class="section-title">Masalah Kulit:</h6>
                                <ul class="list-unstyled">
                                    <?php foreach ($user_concerns as $concern): ?>
                                        <li class="mb-2">
                                            <span class="badge badge-secondary p-2"><?php echo $concern['concern_name']; ?></span>
                                            <small class="text-muted ml-2">Tingkat: <?php echo $concern['severity']; ?>/5</small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                                <p>Kamu belum membuat profil kulit.</p>
                                <a href="profile.php" class="btn btn-primary">Buat Profil Sekarang</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Latest Products -->
            <div class="col-md-8 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Produk Skincare Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($latest_products)): ?>
                            <div class="row">
                                <?php foreach ($latest_products as $product): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card product-card h-100">
                                            <?php if (!empty($product['image'])): ?>
                                                <img src="../assets/images/products/<?php echo $product['image']; ?>" class="card-img-top" alt="<?php echo $product['name']; ?>" style="height: 150px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="card-img-top bg-light text-center py-5"><i class="fas fa-image text-muted fa-2x"></i></div>
                                            <?php endif; ?>
                                            
                                            <div class="card-body">
                                                <h6 class="card-title"><?php echo $product['name']; ?></h6>
                                                <p class="card-text small">
                                                    <strong>Brand:</strong> <?php echo $product['brand_name']; ?><br>
                                                    <strong>Kategori:</strong> <?php echo $product['category_name']; ?><br>
                                                    <strong>Harga:</strong> Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                                </p>
                                            </div>
                                            <div class="card-footer bg-transparent border-top-0 text-center">
                                                <a href="product_detail.php?product_id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-primary">Detail</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-4">
                                <a href="products.php" class="btn btn-outline-primary">Lihat Semua Produk</a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p>Tidak ada produk terbaru yang tersedia saat ini.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Skincare Tips -->
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tips Perawatan Kulit</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($skincare_tips as $tip): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex skincare-tip">
                                        <div class="flex-shrink-0">
                                            <i class="<?php echo $tip['icon']; ?> tip-icon"></i>
                                        </div>
                                        <div>
                                            <h5><?php echo $tip['title']; ?></h5>
                                            <p class="text-muted"><?php echo $tip['description']; ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
    <script src="/SPK-Skincare/assets/js/main.js"></script>
</body>
</html>