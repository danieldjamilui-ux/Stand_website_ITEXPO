<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!is_admin_logged_in()) {
    redirect('login.php');
}

// Ambil data admin yang sedang login
$admin = get_admin_by_id($db, $_SESSION['admin_id']);

// Hitung jumlah data untuk dashboard
$stmt_users = $db->query("SELECT COUNT(*) as total FROM users");
$total_users = $stmt_users->fetch()['total'];

$stmt_products = $db->query("SELECT COUNT(*) as total FROM skincare_products");
$total_products = $stmt_products->fetch()['total'];

$stmt_recommendations = $db->query("SELECT COUNT(*) as total FROM recommendations");
$total_recommendations = $stmt_recommendations->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Rekomendasi Skincare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
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

    <div class="content-wrapper">
        <div class="container mt-4 admin-dashboard">
            <div class="admin-welcome">
                <h2>Dashboard Admin</h2>
                <p>Selamat datang, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>!</p>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-4 mb-4">
                    <div class="card admin-card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Total Pengguna</h5>
                                    <h2 class="mb-0"><?php echo $total_users; ?></h2>
                                </div>
                                <i class="fas fa-users fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="manage_users.php" class="text-white">Lihat Detail <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card admin-card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Total Produk</h5>
                                    <h2 class="mb-0"><?php echo $total_products; ?></h2>
                                </div>
                                <i class="fas fa-flask fa-3x"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <a href="manage_skincare.php" class="text-white">Lihat Detail <i class="fas fa-arrow-right ml-1"></i></a>
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