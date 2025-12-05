<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: index.php');
    exit();
}

require_once '../config/database.php';

// Ambil statistik
$database = new Database();
$db = $database->getConnection();

// Hitung total data
$total_penyakit = $db->query("SELECT COUNT(*) FROM penyakit")->fetchColumn();
$total_gejala = $db->query("SELECT COUNT(*) FROM gejala")->fetchColumn();
$total_aturan = $db->query("SELECT COUNT(*) FROM aturan")->fetchColumn();
$total_konsultasi = $db->query("SELECT COUNT(*) FROM konsultasi WHERE id_pengguna = " . $_SESSION['id_pengguna'])->fetchColumn();

// Hapus kode pengambilan riwayat konsultasi terbaru karena tidak diperlukan lagi
// $query_riwayat = "SELECT k.*, p.nama_penyakit 
//                  FROM konsultasi k 
//                  LEFT JOIN penyakit p ON k.id_penyakit_hasil = p.id_penyakit 
//                  WHERE k.id_pengguna = :id_pengguna 
//                  ORDER BY k.tanggal_konsultasi DESC 
//                  LIMIT 5";
// $stmt_riwayat = $db->prepare($query_riwayat);
// $stmt_riwayat->bindParam(':id_pengguna', $_SESSION['id_pengguna']);
// $stmt_riwayat->execute();
// $riwayat_konsultasi = $stmt_riwayat->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Pakar Penyakit Mata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5fff5;
        }
        .navbar {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
        }
        .stat-card {
            background: linear-gradient(135deg, #388e3c 0%,rgb(23, 186, 33) 100%);
        }
        .btn-primary {
            background-color: #2e7d32;
            border-color: #2e7d32;
        }
        .btn-primary:hover {
            background-color: #1b5e20;
            border-color: #1b5e20;
        }
        <style>
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-eye me-2"></i>
                Sistem Pakar Penyakit Mata
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i>
                        <?= htmlspecialchars($_SESSION['username']) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <h2 class="mb-3">Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
                        <p class="lead text-muted">Sistem Pakar untuk Diagnosis Penyakit Mata menggunakan Metode Certainty Factor</p>
                        <a href="konsultasi.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-stethoscope me-2"></i>Mulai Konsultasi
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-disease fa-2x mb-3"></i>
                        <div class="stat-number"><?= $total_penyakit ?></div>
                        <div>Penyakit</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-list-ul fa-2x mb-3"></i>
                        <div class="stat-number"><?= $total_gejala ?></div>
                        <div>Gejala</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-cogs fa-2x mb-3"></i>
                        <div class="stat-number"><?= $total_aturan ?></div>
                        <div>Aturan</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <i class="fas fa-history fa-2x mb-3"></i>
                        <div class="stat-number"><?= $total_konsultasi ?></div>
                        <div>Konsultasi</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-stethoscope fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Konsultasi</h5>
                        <p class="card-text">Mulai proses diagnosis penyakit mata berdasarkan gejala yang dialami</p>
                        <a href="konsultasi.php" class="btn btn-primary">
                            <i class="fas fa-play me-2"></i>Mulai Konsultasi
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-history fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Riwayat</h5>
                        <p class="card-text">Lihat riwayat konsultasi dan hasil diagnosis yang pernah dilakukan</p>
                        <a href="riwayat.php" class="btn btn-success">
                            <i class="fas fa-eye me-2"></i>Lihat Riwayat
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-cogs fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Kelola Data</h5>
                        <p class="card-text">Kelola data penyakit, gejala, dan aturan sistem pakar</p>
                        <div class="btn-group-vertical w-100">
                            <a href="kelola_penyakit.php" class="btn btn-outline-warning btn-sm mb-1">
                                <i class="fas fa-disease me-2"></i>Penyakit
                            </a>
                            <a href="kelola_gejala.php" class="btn btn-outline-warning btn-sm mb-1">
                                <i class="fas fa-list-ul me-2"></i>Gejala
                            </a>
                            <a href="kelola_aturan.php" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-cogs me-2"></i>Aturan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hapus seluruh bagian Recent Consultations -->
        <!-- <?php if (!empty($riwayat_konsultasi)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>Konsultasi Terbaru
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Hasil Diagnosis</th>
                                        <th>CF Hasil</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($riwayat_konsultasi as $konsultasi): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($konsultasi['tanggal_konsultasi'])) ?></td>
                                        <td>
                                            <?php if ($konsultasi['nama_penyakit']): ?>
                                                <span class="badge bg-success"><?= htmlspecialchars($konsultasi['nama_penyakit']) ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Tidak terdiagnosis</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($konsultasi['cf_hasil_diagnosis']): ?>
                                                <span class="badge bg-info"><?= number_format($konsultasi['cf_hasil_diagnosis'] * 100, 1) ?>%</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="detail_konsultasi.php?id=<?= $konsultasi['id_konsultasi'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Detail
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-3">
                            <a href="riwayat.php" class="btn btn-outline-primary">
                                <i class="fas fa-history me-2"></i>Lihat Semua Riwayat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?> -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Footer -->
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