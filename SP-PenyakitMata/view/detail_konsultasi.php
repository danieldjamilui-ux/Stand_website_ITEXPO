<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

require_once '../controllers/RiwayatController.php';
require_once '../controllers/PenjelasanController.php';

$riwayatController = new RiwayatController();
$penjelasanController = new PenjelasanController();

$id_konsultasi = $_GET['id'] ?? 0;
if ($id_konsultasi <= 0) {
    $_SESSION['error'] = 'Parameter konsultasi tidak valid';
    header('Location: riwayat.php');
    exit();
}

// Ambil detail konsultasi
$konsultasi = $riwayatController->getDetailKonsultasi($id_konsultasi, $_SESSION['user_id']);

if (!$konsultasi) {
    header('Location: riwayat.php');
    exit();
}

// Ambil penjelasan diagnosis
$penjelasan = $penjelasanController->getPenjelasanDiagnosis($id_konsultasi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Konsultasi - Sistem Pakar Penyakit Mata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .result-card {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }
        .warning-card {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }
        .gejala-item {
            border-left: 4px solid #667eea;
            background: #f8f9ff;
        }
        .aturan-item {
            border-left: 4px solid #43e97b;
            background: #f0fff4;
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
                        <a class="nav-link" href="riwayat.php">
                            <i class="fas fa-history me-1"></i>Riwayat
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['nama_lengkap']; ?>
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
    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>
                        <i class="fas fa-file-medical me-2"></i>
                        Detail Konsultasi #<?php echo $konsultasi['id']; ?>
                    </h2>
                    <a href="riwayat.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
                <p class="text-muted">
                    <i class="fas fa-calendar me-2"></i>
                    <?php echo date('d F Y, H:i:s', strtotime($konsultasi['tanggal_konsultasi'])); ?>
                </p>
            </div>
        </div>

        <div class="row">
            <!-- Hasil Diagnosis -->
            <div class="col-md-6 mb-4">
                <?php if ($konsultasi['nama_penyakit']): ?>
                    <div class="card result-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-check-circle me-2"></i>Hasil Diagnosis
                            </h5>
                        </div>
                        <div class="card-body">
                            <h4 class="mb-3"><?php echo $konsultasi['nama_penyakit']; ?></h4>
                            
                            <?php if ($konsultasi['cf_hasil']): ?>
                                <div class="mb-3">
                                    <strong>Tingkat Keyakinan:</strong>
                                    <div class="progress mt-2" style="height: 25px;">
                                        <div class="progress-bar bg-light text-dark fw-bold" 
                                             style="width: <?php echo ($konsultasi['cf_hasil'] * 100); ?>%">
                                            <?php echo number_format($konsultasi['cf_hasil'] * 100, 2); ?>%
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <strong>Deskripsi:</strong>
                                <p class="mt-2"><?php echo $konsultasi['deskripsi']; ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Solusi/Pengobatan:</strong>
                                <p class="mt-2"><?php echo $konsultasi['solusi']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card warning-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>Hasil Diagnosis
                            </h5>
                        </div>
                        <div class="card-body">
                            <h4 class="mb-3">Tidak Terdiagnosis</h4>
                            <p class="mb-0">
                                Gejala yang dipilih tidak cukup untuk menentukan diagnosis yang akurat. 
                                Silakan konsultasi dengan dokter untuk pemeriksaan lebih lanjut.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Gejala yang Dipilih -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-symptoms me-2"></i>Gejala yang Dipilih
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($detail_gejala as $gejala): ?>
                            <div class="gejala-item p-3 mb-3 rounded">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo $gejala['nama_gejala']; ?></h6>
                                        <p class="text-muted small mb-0"><?php echo $gejala['deskripsi_gejala']; ?></p>
                                    </div>
                                    <span class="badge bg-primary">
                                        CF: <?php echo $gejala['cf_user']; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Penjelasan Diagnosis -->
        <?php if (!empty($aturan_terpicu)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Penjelasan Diagnosis
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">
                                Berikut adalah aturan-aturan yang terpicu berdasarkan gejala yang Anda pilih:
                            </p>
                            
                            <?php foreach ($aturan_terpicu as $aturan): ?>
                                <div class="aturan-item p-3 mb-3 rounded">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="mb-1">
                                                <i class="fas fa-arrow-right me-2 text-success"></i>
                                                <?php echo $aturan['nama_gejala']; ?>
                                            </h6>
                                            <p class="text-muted small mb-0"><?php echo $aturan['deskripsi_gejala']; ?></p>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <span class="badge bg-info">CF Pakar: <?php echo $aturan['cf_pakar']; ?></span><br>
                                            <span class="badge bg-primary mt-1">CF User: <?php echo $aturan['cf_user']; ?></span>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <span class="badge bg-success">
                                                CF Hasil: <?php echo number_format($aturan['cf_pakar'] * $aturan['cf_user'], 3); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="alert alert-info mt-4">
                                <h6><i class="fas fa-calculator me-2"></i>Perhitungan Certainty Factor:</h6>
                                <p class="mb-2">
                                    <strong>CF(H,E) = CF(E) × CF(H,E)</strong>
                                </p>
                                <ul class="mb-0">
                                    <li>CF(E) = Certainty Factor dari user (tingkat keyakinan terhadap gejala)</li>
                                    <li>CF(H,E) = Certainty Factor dari pakar (tingkat keyakinan pakar terhadap aturan)</li>
                                    <li>CF Kombinasi menggunakan rumus: CF1 + CF2 × (1 - CF1)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Rekomendasi -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="mb-3">
                            <i class="fas fa-lightbulb me-2"></i>Rekomendasi
                        </h5>
                        <p class="text-muted mb-4">
                            Hasil diagnosis ini hanya sebagai referensi awal. Untuk diagnosis yang lebih akurat 
                            dan pengobatan yang tepat, sangat disarankan untuk berkonsultasi dengan dokter spesialis mata.
                        </p>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="konsultasi.php" class="btn btn-primary w-100">
                                    <i class="fas fa-stethoscope me-2"></i>Konsultasi Lagi
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="riwayat.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-history me-2"></i>Lihat Riwayat
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <button onclick="window.print()" class="btn btn-outline-info w-100">
                                    <i class="fas fa-print me-2"></i>Cetak Hasil
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>