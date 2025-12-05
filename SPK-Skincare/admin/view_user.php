<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Pastikan admin sudah login
if (!is_admin_logged_in()) {
    redirect('login.php');
}

// Ambil ID user dari parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('manage_users.php');
}

$user_id = $_GET['id'];

// Ambil data user
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    redirect('manage_users.php');
}

// Ambil profil kulit user
$stmt = $db->prepare("
    SELECT usp.*, st.name as skin_type_name, st.description as skin_type_description
    FROM user_skin_profiles usp
    LEFT JOIN skin_types st ON usp.skin_type_id = st.skin_type_id
    WHERE usp.user_id = ?
    ORDER BY usp.created_at DESC
");
$stmt->execute([$user_id]);
$skin_profiles = $stmt->fetchAll();

// Ambil masalah kulit untuk setiap profil
$user_concerns = [];
foreach ($skin_profiles as $profile) {
    $stmt = $db->prepare("
        SELECT usc.*, sc.name as concern_name, sc.description as concern_description
        FROM user_skin_concerns usc
        LEFT JOIN skin_concerns sc ON usc.concern_id = sc.concern_id
        WHERE usc.profile_id = ?
        ORDER BY usc.severity DESC
    ");
    $stmt->execute([$profile['profile_id']]);
    $user_concerns[$profile['profile_id']] = $stmt->fetchAll();
}

// Ambil riwayat rekomendasi
$stmt = $db->prepare("
    SELECT r.*, 
           COUNT(rd.id) as total_products,
           st.name as skin_type_name
    FROM recommendations r
    LEFT JOIN recommendation_details rd ON r.recommendation_id = rd.recommendation_id
    LEFT JOIN user_skin_profiles usp ON r.profile_id = usp.profile_id
    LEFT JOIN skin_types st ON usp.skin_type_id = st.skin_type_id
    WHERE r.user_id = ?
    GROUP BY r.recommendation_id
    ORDER BY r.created_at DESC
");
$stmt->execute([$user_id]);
$recommendations = $stmt->fetchAll();

// Function untuk mengkonversi severity ke text
function getSeverityText($severity) {
    switch($severity) {
        case 1: return 'Ringan';
        case 2: return 'Ringan-Sedang';
        case 3: return 'Sedang';
        case 4: return 'Sedang-Berat';
        case 5: return 'Berat';
        default: return 'Tidak Diketahui';
    }
}

// Function untuk mengkonversi severity ke badge class
function getSeverityBadgeClass($severity) {
    switch($severity) {
        case 1: return 'badge-success';
        case 2: return 'badge-info';
        case 3: return 'badge-warning';
        case 4: return 'badge-danger';
        case 5: return 'badge-dark';
        default: return 'badge-secondary';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengguna - <?php echo htmlspecialchars($user['name']); ?></title>
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
                    <li class="nav-item">
                        <a class="nav-link" href="manage_skincare.php">Kelola Produk</a>
                    </li>
                    <li class="nav-item active">
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
            <h2>Detail Pengguna</h2>
            <a href="manage_users.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        
        <!-- Informasi Dasar User -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user"></i> Informasi Dasar</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>ID User:</strong></td>
                                <td><?php echo $user['user_id']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Nama:</strong></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Username:</strong></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Password:</strong></td>
                                <td>
                                    <code><?php echo htmlspecialchars($user['password']); ?></code>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Daftar:</strong></td>
                                <td><?php echo date('d-m-Y H:i', strtotime($user['created_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profil Kulit -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-spa"></i> Profil Kulit</h5>
            </div>
            <div class="card-body">
                <?php if (count($skin_profiles) > 0): ?>
                    <div class="accordion" id="profileAccordion">
                        <?php foreach ($skin_profiles as $index => $profile): ?>
                            <div class="card">
                                <div class="card-header" id="heading<?php echo $index; ?>">
                                    <h6 class="mb-0">
                                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse<?php echo $index; ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $index; ?>">
                                            <i class="fas fa-user-circle"></i> Profil #<?php echo $profile['profile_id']; ?> - <?php echo htmlspecialchars($profile['skin_type_name']); ?>
                                            <small class="text-muted">(<?php echo date('d-m-Y H:i', strtotime($profile['created_at'])); ?>)</small>
                                        </button>
                                    </h6>
                                </div>
                                <div id="collapse<?php echo $index; ?>" class="collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $index; ?>" data-parent="#profileAccordion">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6><i class="fas fa-info-circle"></i> Tipe Kulit</h6>
                                                <p><strong><?php echo htmlspecialchars($profile['skin_type_name']); ?></strong></p>
                                                <p class="text-muted"><?php echo htmlspecialchars($profile['skin_type_description']); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <h6><i class="fas fa-exclamation-triangle"></i> Masalah Kulit</h6>
                                                <?php if (isset($user_concerns[$profile['profile_id']]) && count($user_concerns[$profile['profile_id']]) > 0): ?>
                                                    <?php foreach ($user_concerns[$profile['profile_id']] as $concern): ?>
                                                        <div class="mb-2">
                                                            <span class="badge <?php echo getSeverityBadgeClass($concern['severity']); ?>">
                                                                <?php echo htmlspecialchars($concern['concern_name']); ?> 
                                                                (<?php echo getSeverityText($concern['severity']); ?>)
                                                            </span>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($concern['concern_description']); ?></small>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <p class="text-muted">Tidak ada masalah kulit yang tercatat.</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> User belum membuat profil kulit.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Riwayat Rekomendasi -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Riwayat Rekomendasi</h5>
            </div>
            <div class="card-body">
                <?php if (count($recommendations) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID Rekomendasi</th>
                                    <th>Tipe Kulit</th>
                                    <th>Total Produk</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recommendations as $recommendation): ?>
                                    <tr>
                                        <td>#<?php echo $recommendation['recommendation_id']; ?></td>
                                        <td>
                                            <span class="badge badge-primary">
                                                <?php echo htmlspecialchars($recommendation['skin_type_name']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $recommendation['total_products']; ?> produk</td>
                                        <td><?php echo date('d-m-Y H:i', strtotime($recommendation['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> User belum memiliki riwayat rekomendasi.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistik -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Statistik</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-primary"><?php echo count($skin_profiles); ?></h4>
                            <p class="text-muted">Total Profil Kulit</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <h4 class="text-success"><?php echo count($recommendations); ?></h4>
                            <p class="text-muted">Total Rekomendasi</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <?php 
                            $total_concerns = 0;
                            foreach ($user_concerns as $concerns) {
                                $total_concerns += count($concerns);
                            }
                            ?>
                            <h4 class="text-warning"><?php echo $total_concerns; ?></h4>
                            <p class="text-muted">Total Masalah Kulit</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <?php 
                            $days_since_register = floor((time() - strtotime($user['created_at'])) / (60 * 60 * 24));
                            ?>
                            <h4 class="text-info"><?php echo $days_since_register; ?></h4>
                            <p class="text-muted">Hari Sejak Daftar</p>
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
</body>
</html>