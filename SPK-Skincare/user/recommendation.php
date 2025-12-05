<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
// Ganti cbf.php dengan cbf_bridge.php untuk menggunakan implementasi Python
require_once '../algorithm/cbf_bridge.php';

// Cek apakah user sudah login
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$recommendation_id = null;
$products = [];
$best_product = null; // Variabel untuk menyimpan produk terbaik

// Cek apakah ada profile_id yang diberikan
if (isset($_GET['profile_id'])) {
    $profile_id = $_GET['profile_id'];
    
    // Cek apakah profile_id valid dan milik user yang login
    $stmt = $db->prepare("SELECT * FROM user_skin_profiles WHERE profile_id = ? AND user_id = ?");
    $stmt->execute([$profile_id, $user_id]);
    $profile = $stmt->fetch();
    
    if ($profile) {
        // Cek apakah sudah ada rekomendasi untuk profil ini
        $stmt = $db->prepare("SELECT * FROM recommendations WHERE profile_id = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$profile_id]);
        $existing_recommendation = $stmt->fetch();
        
        if ($existing_recommendation) {
            $recommendation_id = $existing_recommendation['recommendation_id'];
        } else {
            // Buat rekomendasi baru menggunakan algoritma CBF
            $cbf = new ManajerRekomendasi($db);
            $recommendation_id = $cbf->rekomendasi($user_id, $profile_id);
            
            if (!$recommendation_id) {
                $error = "Gagal menghasilkan rekomendasi. Silakan coba lagi.";
            }
        }
        
        // Ambil detail rekomendasi
        if ($recommendation_id) {
            // Ubah query untuk tidak mengambil kolom price dan image
            $stmt = $db->prepare("SELECT rd.*, sp.name as product_name, 
                            sp.brand as brand_name, sp.active_ingredient_type, sp.size, sp.release_year 
                            FROM recommendation_details rd 
                            JOIN skincare_products sp ON rd.product_id = sp.product_id 
                            WHERE rd.recommendation_id = ? 
                            ORDER BY rd.rank");
            $stmt->execute([$recommendation_id]);
            $products = $stmt->fetchAll();
            
            // Ambil produk dengan peringkat terbaik (rank = 1)
            if (!empty($products)) {
                $best_product = $products[0]; // Produk pertama adalah yang terbaik
            }
        }
    } else {
        $error = "Profil kulit tidak valid.";
    }
} else {
    // Jika tidak ada profile_id, cek apakah user memiliki profil kulit
    $skin_profile = get_user_skin_profile($db, $user_id);
    
    if ($skin_profile) {
        redirect("recommendation.php?profile_id=" . $skin_profile['profile_id']);
    } else {
        redirect("profile.php");
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekomendasi Skincare - Sistem Rekomendasi Skincare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .product-table {
            margin-top: 20px;
        }
        .best-product {
            border: 2px solid #28a745;
            background-color: rgba(40, 167, 69, 0.05);
        }
        .score-high {
            color: #28a745;
            font-weight: bold;
        }
        .score-medium {
            color: #ffc107;
            font-weight: bold;
        }
        .score-low {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h2>Rekomendasi Produk Skincare</h2>
                <p>Berdasarkan profil kulit dan masalah kulit yang Anda masukkan, berikut adalah rekomendasi produk skincare yang sesuai untuk Anda.</p>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (empty($products) && empty($error)): ?>
                    <div class="alert alert-info">Tidak ada rekomendasi produk yang tersedia. Silakan perbarui profil kulit Anda.</div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($best_product)): ?>
        <!-- Tampilkan rekomendasi terbaik di bagian atas -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card mb-4 border-success">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Rekomendasi Terbaik Untuk Anda</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h3 class="card-title"><?php echo $best_product['product_name']; ?></h3>
                                <p class="card-text">
                                    <strong>Brand:</strong> <?php echo $best_product['brand_name']; ?><br>
                                    <strong>Ukuran:</strong> <?php echo $best_product['size']; ?><br>
                                    <strong>Bahan Aktif:</strong> <?php echo $best_product['active_ingredient_type']; ?><br>
                                    <strong>Tahun Rilis:</strong> <?php echo $best_product['release_year']; ?><br>
                                    <strong>Skor Kesesuaian:</strong> <span class="score-high"><?php echo number_format($best_product['similarity_score'] * 100, 1); ?>%</span>
                                </p>
                                <p class="text-success"><i class="fa fa-check-circle"></i> Produk ini memiliki kesesuaian tertinggi dengan profil dan masalah kulit Anda.</p>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="mt-3">
                                    <span class="display-4 text-success">#1</span>
                                    <p class="text-muted">Peringkat Teratas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($products)): ?>
        <!-- Tampilkan semua produk dalam bentuk tabel -->
        <div class="row mt-4">
            <div class="col-md-12">
                <h3>Semua Produk Berdasarkan Peringkat</h3>
                <p>Berikut adalah daftar lengkap produk yang direkomendasikan untuk Anda berdasarkan tingkat kesesuaian:</p>
                
                <div class="table-responsive product-table">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Peringkat</th>
                                <th>Nama Produk</th>
                                <th>Brand</th>
                                <th>Ukuran</th>
                                <th>Bahan Aktif</th>
                                <th>Tahun Rilis</th>
                                <th>Skor Kesesuaian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr <?php echo ($product['rank'] == 1) ? 'class="best-product"' : ''; ?>>
                                <td><strong>#<?php echo $product['rank']; ?></strong></td>
                                <td><strong><?php echo $product['product_name']; ?></strong></td>
                                <td><?php echo $product['brand_name']; ?></td>
                                <td><?php echo $product['size']; ?></td>
                                <td><?php echo $product['active_ingredient_type']; ?></td>
                                <td><?php echo $product['release_year']; ?></td>
                                <td>
                                    <span class="<?php 
                                        if ($product['similarity_score'] >= 0.7) echo 'score-high';
                                        else if ($product['similarity_score'] >= 0.4) echo 'score-medium';
                                        else echo 'score-low';
                                    ?>">
                                        <?php echo number_format($product['similarity_score'] * 100, 1); ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <p><strong>Catatan:</strong> Rekomendasi ini dihasilkan berdasarkan profil kulit dan masalah kulit yang Anda masukkan. Hasil mungkin berbeda untuk setiap orang.</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>