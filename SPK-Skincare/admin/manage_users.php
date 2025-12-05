<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Pastikan admin sudah login
if (!is_admin_logged_in()) {
    redirect('login.php');
}

// Inisialisasi variabel
$success_msg = $error_msg = '';

// Proses hapus user
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    try {
        // Mulai transaksi
        $db->beginTransaction();
        
        // 1. Hapus data di tabel recommendation_details yang terkait dengan rekomendasi user
        $stmt = $db->prepare("DELETE rd FROM recommendation_details rd 
                             JOIN recommendations r ON rd.recommendation_id = r.recommendation_id 
                             WHERE r.user_id = ?");
        $stmt->execute([$user_id]);
        
        // 2. Hapus data di tabel recommendations
        $stmt = $db->prepare("DELETE FROM recommendations WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // 3. Hapus data di tabel user_skin_concerns
        $stmt = $db->prepare("DELETE usc FROM user_skin_concerns usc 
                             JOIN user_skin_profiles usp ON usc.profile_id = usp.profile_id 
                             WHERE usp.user_id = ?");
        $stmt->execute([$user_id]);
        
        // 4. Hapus data di tabel user_skin_profiles
        $stmt = $db->prepare("DELETE FROM user_skin_profiles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // 5. Hapus user
        $stmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Commit transaksi
        $db->commit();
        
        $success_msg = "Pengguna berhasil dihapus.";
    } catch (PDOException $e) {
        // Rollback transaksi jika terjadi error
        $db->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Ambil semua user
$stmt = $db->query("SELECT u.*, 
                   (SELECT COUNT(*) FROM recommendations r WHERE r.user_id = u.user_id) as total_recommendations,
                   (SELECT COUNT(*) FROM user_skin_profiles usp WHERE usp.user_id = u.user_id) as has_profile
                   FROM users u ORDER BY u.created_at DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - Admin</title>
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
        <h2>Kelola Pengguna</h2>
        
        <?php if (!empty($success_msg)): ?>
            <?php echo display_success($success_msg); ?>
        <?php endif; ?>
        
        <?php if (!empty($error_msg)): ?>
            <?php echo display_error($error_msg); ?>
        <?php endif; ?>
        
        <div class="card mt-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Profil Kulit</th>
                                <th>Total Rekomendasi</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['user_id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php if ($user['has_profile'] > 0): ?>
                                                <span class="badge badge-success">Ada</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Belum Ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $user['total_recommendations']; ?></td>
                                        <td><?php echo date('d-m-Y H:i', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="User Actions">
                                                <a href="view_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="manage_users.php?delete=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini? Semua data terkait pengguna ini juga akan dihapus.')" title="Hapus Pengguna">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada pengguna.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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