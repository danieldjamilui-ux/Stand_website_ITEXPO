<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Pastikan admin sudah login
if (!is_admin_logged_in()) {
    redirect('login.php');
}

// Ambil ID produk dari parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('manage_skincare.php');
}

$product_id = $_GET['id'];

// Ambil detail produk
$product = get_product_details($db, $product_id);
if (!$product) {
    redirect('manage_skincare.php');
}

// Ambil kesesuaian dengan jenis kulit
$skin_type_compatibility = get_product_skin_type_compatibility($db, $product_id);

// Ambil kesesuaian dengan masalah kulit
$skin_concern_compatibility = get_product_skin_concern_compatibility($db, $product_id);

// Fungsi untuk mengkonversi nilai numerik ke teks
function get_compatibility_text($value) {
    if ($value >= 0.80) return 'Sangat Cocok';
    if ($value >= 0.60) return 'Cocok';
    if ($value >= 0.40) return 'Cukup Cocok';
    if ($value >= 0.20) return 'Kurang Cocok';
    return 'Tidak Cocok';
}

function get_effectiveness_text($value) {
    if ($value >= 0.80) return 'Sangat Efektif';
    if ($value >= 0.60) return 'Efektif';
    if ($value >= 0.40) return 'Cukup Efektif';
    if ($value >= 0.20) return 'Kurang Efektif';
    return 'Tidak Efektif';
}

function get_compatibility_badge($value) {
    if ($value >= 0.80) return 'badge-primary';
    if ($value >= 0.60) return 'badge-success';
    if ($value >= 0.40) return 'badge-info';
    if ($value >= 0.20) return 'badge-warning';
    return 'badge-danger';
}

function format_score($value) {
    return number_format($value, 2);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Produk - <?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="manage_skincare.php">Kelola Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_users.php">Kelola Pengguna</a>
                    </li>
                </ul>
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="logout.php" data-confirm="Apakah Anda yakin ingin keluar?">Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detail Produk Skincare</h2>
            <div>
            </div>
        </div>
        
        <div class="row">
            <!-- Informasi Produk -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Produk</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>ID Produk:</strong></td>
                                <td><?php echo $product['product_id']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Nama Produk:</strong></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Brand:</strong></td>
                                <td><?php echo htmlspecialchars($product['brand']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Ukuran:</strong></td>
                                <td><?php echo htmlspecialchars($product['size']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tipe Bahan Aktif:</strong></td>
                                <td><?php echo htmlspecialchars($product['active_ingredient_type']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tahun Rilis:</strong></td>
                                <td><?php echo htmlspecialchars($product['release_year']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Kesesuaian Jenis Kulit -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-user-check"></i> Kesesuaian Jenis Kulit</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($skin_type_compatibility) > 0): ?>
                            <div class="row">
                                <?php foreach ($skin_type_compatibility as $compatibility): ?>
                                    <div class="col-12 mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><?php echo htmlspecialchars($compatibility['skin_type_name']); ?></span>
                                            <div>
                                                <span class="badge <?php echo get_compatibility_badge($compatibility['compatibility_score']); ?>">
                                                    <?php echo get_compatibility_text($compatibility['compatibility_score']); ?>
                                                </span>
                                                <small class="text-muted ml-2">(<?php echo format_score($compatibility['compatibility_score']); ?>)</small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Belum ada data kesesuaian jenis kulit.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <!-- Efektivitas Masalah Kulit -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-heartbeat"></i> Efektivitas untuk Masalah Kulit</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($skin_concern_compatibility) > 0): ?>
                            <div class="row">
                                <?php foreach ($skin_concern_compatibility as $concern): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span><?php echo htmlspecialchars($concern['concern_name']); ?></span>
                                            <div>
                                                <span class="badge <?php echo get_compatibility_badge($concern['effectiveness_score']); ?>">
                                                    <?php echo get_effectiveness_text($concern['effectiveness_score']); ?>
                                                </span>
                                                <small class="text-muted ml-2">(<?php echo format_score($concern['effectiveness_score']); ?>)</small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Belum ada data efektivitas masalah kulit.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>