<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['id_pengguna']) || empty($_SESSION['id_pengguna'])) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu';
    header('Location: index.php');
    exit();
}

require_once '../config/database.php';
require_once '../controllers/RiwayatController.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) {
        throw new Exception('Koneksi database gagal');
    }
    
    // Test connection
    $test = $db->query("SELECT 1");
    if (!$test) {
        throw new Exception('Database connection test failed');
    }
    
    $riwayatController = new RiwayatController($db);
    
    // Tidak menggunakan pagination lagi
    $page = 1;
    $limit = 9999; // Angka besar untuk menampilkan semua data
    
    // Ambil riwayat konsultasi
    $result = $riwayatController->tampilkanDaftarRiwayat($_SESSION['id_pengguna'], $page, $limit);
    $riwayat_konsultasi = $result['data'];
    $total_konsultasi = $result['pagination']['total_records'];

    // Ambil statistik
    $statistik_result = $riwayatController->getStatistik($_SESSION['id_pengguna']);
    $statistik = isset($statistik_result['data']) ? $statistik_result['data'] : [];

} catch (Exception $e) {
    $_SESSION['error'] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
    error_log("Database connection error: " . $e->getMessage());
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Konsultasi - Sistem Pakar Penyakit Mata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5fff5;
        }
        .navbar {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .btn-custom {
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 600;
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            color: white;
            border: none;
        }
        .timeline-item {
            border-left: 3px solid #2e7d32;
            padding-left: 20px;
            margin-bottom: 30px;
            position: relative;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 0;
            width: 13px;
            height: 13px;
            border-radius: 50%;
            background: #2e7d32;
        }
        .cf-badge {
            font-size: 0.8em;
            padding: 4px 8px;
        }
        .stat-card {
            background: linear-gradient(135deg, #388e3c 0%, #1b5e20 100%);
            color: white !important;
        }
        .stat-card i {
            color: white !important;
        }
        .stat-number {
            color: white !important;
        }
        .stat-card div:last-child {
            color: white !important;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-eye me-2"></i>
                Sistem Pakar Penyakit Mata
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="konsultasi.php">
                            <i class="fas fa-stethoscope me-1"></i>Konsultasi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="riwayat.php">
                            <i class="fas fa-history me-1"></i>Riwayat
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['nama_lengkap'] ?? 'Pengguna'; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
        
        <!-- Daftar Riwayat -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Riwayat Konsultasi
                        </h5>
                        <div>
                            <span class="badge bg-success"><?php echo $total_konsultasi; ?> Total Konsultasi</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($riwayat_konsultasi) > 0): ?>
                            <div class="timeline">
                                <?php foreach ($riwayat_konsultasi as $riwayat): ?>
                                    <div class="timeline-item">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                                            <h6 class="mb-0">
                                                                <i class="fas fa-calendar me-2 text-success"></i>
                                                                <?php echo date('d F Y, H:i', strtotime($riwayat['tanggal_konsultasi'])); ?>
                                                            </h6>
                                                            <span class="badge bg-secondary">#<?php echo $riwayat['id_konsultasi']; ?></span>
                                                        </div>
                                                        
                                                        <?php if ($riwayat['id_penyakit_hasil']): ?>
                                                            <div class="mb-3">
                                                                <h5 class="text-success mb-2">
                                                                    <i class="fas fa-check-circle me-2"></i>
                                                                    <?php echo $riwayat['nama_penyakit']; ?>
                                                                </h5>
                                                                
                                                                <?php if ($riwayat['cf_hasil_diagnosis']): ?>
                                                                    <div class="mb-2">
                                                                        <span class="badge bg-info cf-badge">
                                                                            Tingkat Keyakinan: <?php echo number_format($riwayat['cf_hasil_diagnosis'] * 100, 2); ?>%
                                                                        </span>
                                                                    </div>
                                                                    
                                                                    <div class="progress mb-3" style="height: 8px;">
                                                                        <div class="progress-bar bg-success" 
                                                                             style="width: <?php echo ($riwayat['cf_hasil_diagnosis'] * 100); ?>%">
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                                
                                                                <p class="text-muted mb-2">
                                                                    <strong>Deskripsi:</strong> <?php echo $riwayat['deskripsi_penyakit']; ?>
                                                                </p>
                                                                
                                                                <p class="text-muted mb-0">
                                                                    <strong>Solusi:</strong> <?php echo $riwayat['solusi_penyakit']; ?>
                                                                </p>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="mb-3">
                                                                <h5 class="text-warning mb-2">
                                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                                    Tidak Terdiagnosis
                                                                </h5>
                                                                <p class="text-muted mb-0">
                                                                    Gejala yang dipilih tidak cukup untuk menentukan diagnosis yang akurat.
                                                                </p>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-md-12 text-end">
                                                        <button class="btn btn-sm btn-primary-custom" onclick="showExplanation(<?php echo $riwayat['id_konsultasi']; ?>)">
                                                            <i class="fas fa-info-circle me-1"></i>Penjelasan Detail
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-4x text-muted mb-4"></i>
                                <h4 class="text-muted mb-3">Belum Ada Riwayat Konsultasi</h4>
                                <p class="text-muted mb-4">
                                    Anda belum pernah melakukan konsultasi diagnosis. 
                                    Mulai konsultasi pertama Anda sekarang!
                                </p>
                                <a href="konsultasi.php" class="btn btn-primary-custom btn-custom">
                                    <i class="fas fa-stethoscope me-2"></i>Mulai Konsultasi
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

 <!-- Modal Penjelasan -->
<div class="modal fade" id="explanationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>Penjelasan Diagnosis
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="explanationContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat penjelasan...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

        function showExplanation(konsultasiId) {
            const modal = new bootstrap.Modal(document.getElementById('explanationModal'));
            const content = document.getElementById('explanationContent');
            
            // Reset content
            content.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            modal.show();
            
            // Load explanation via AJAX
            fetch('get_explanation.php?id=' + konsultasiId)
                .then(response => response.text())
                .then(data => {
                    content.innerHTML = data;
                })
                .catch(error => {
                    content.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Terjadi kesalahan saat memuat penjelasan.
                        </div>
                    `;
                });
        }
    </script>
        <footer class="bg-white text-dark py-4 mt-5" style="border-top: 2px solid #2e7d32;">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Sistem Pakar Penyakit Mata</h5>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2025 Tim Pengembang. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
