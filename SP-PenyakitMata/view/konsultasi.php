<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['id_pengguna']) || empty($_SESSION['id_pengguna'])) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu';
    header('Location: index.php');
    exit();
}

require_once '../config/database.php';
require_once '../controllers/KonsultasiController.php';

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
    
    $konsultasiController = new KonsultasiController($db);
} catch (Exception $e) {
    $_SESSION['error'] = 'Terjadi kesalahan sistem: ' . $e->getMessage();
    error_log("Database connection error: " . $e->getMessage());
    header('Location: dashboard.php');
    exit();
}

// Ambil semua gejala
$gejala_list = $konsultasiController->getAllGejala();
if (empty($gejala_list)) {
    $_SESSION['error'] = 'Data gejala tidak tersedia';
    header('Location: dashboard.php');
    exit();
}

$hasil_diagnosis = null;
$id_konsultasi = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (empty($_POST['gejala'])) {
            throw new Exception('Pilih minimal 1 gejala');
        }
        
        // Konversi dan validasi input
        $gejala_terpilih = array_map('intval', $_POST['gejala']);
        $cf_user = [];
        
        foreach ($gejala_terpilih as $id_gejala) {
            $cf_value = isset($_POST['cf_user'][$id_gejala]) ? 
                max(0.2, min(1.0, floatval($_POST['cf_user'][$id_gejala]))) : 1.0;
            $cf_user[$id_gejala] = $cf_value;
        }
        
        // Proses konsultasi
        $result = $konsultasiController->mulaiSesiKonsultasi(
            $_SESSION['id_pengguna'],
            $gejala_terpilih,
            $cf_user
        );
        
        if ($result['success']) {
            // Simpan ke session dan redirect untuk menghindari resubmit
            $_SESSION['hasil_diagnosis'] = serialize($result['hasil_diagnosis']);
            $_SESSION['id_konsultasi'] = $result['id_konsultasi'];
            header('Location: konsultasi.php');
            exit();
        } else {
            throw new Exception($result['message']);
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: konsultasi.php');
        exit();
    }
}

// Ambil hasil dari session jika ada
if (isset($_SESSION['hasil_diagnosis'])) {
    $hasil_diagnosis = unserialize($_SESSION['hasil_diagnosis']);
    $id_konsultasi = $_SESSION['id_konsultasi'];
    unset($_SESSION['hasil_diagnosis']);
    unset($_SESSION['id_konsultasi']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konsultasi - Sistem Pakar Penyakit Mata</title>
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
        .gejala-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .gejala-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .gejala-card.selected {
            border: 2px solid #2e7d32;
            background-color: #f8fff8;
        }
        .cf-slider {
            margin-top: 10px;
        }
        .hasil-diagnosis {
            background: linear-gradient(135deg, #388e3c 0%, #1b5e20 100%);
            color: white;
            border-radius: 15px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1b5e20 0%, #0d3c10 100%);
            border: none;
        }
        .btn-light {
            background: white;
            color: #2e7d32;
            font-weight: 600;
        }
        .btn-light:hover {
            background: #f0f0f0;
            color: #1b5e20;
        }
        .progress-bar {
            background-color: rgba(255, 255, 255, 0.8);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
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
                        <a class="nav-link active" href="konsultasi.php">
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
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['username']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-4">
                        <h2 class="mb-3">
                            <i class="fas fa-stethoscope text-success me-2"></i>
                            Konsultasi Diagnosis Penyakit Mata
                        </h2>
                        <p class="lead text-muted">Pilih gejala yang Anda alami dan tentukan tingkat keyakinan Anda</p>
                    </div>
                </div>
            </div>
        </div>

    <?php if ($hasil_diagnosis): ?>
<!-- Hasil Diagnosis -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card hasil-diagnosis">
            <div class="card-body p-4">
                <h4 class="mb-3">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Hasil Diagnosis
                </h4>
                
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="mb-2">Penyakit: <?= htmlspecialchars($hasil_diagnosis->getPenyakit()['nama_penyakit']) ?></h5>
                        <p class="mb-2"><?= htmlspecialchars($hasil_diagnosis->getPenyakit()['deskripsi_penyakit']) ?></p>
                        
                        <div class="mb-3">
                            <strong>Tingkat Keyakinan: <?= $hasil_diagnosis->getPersentaseKepastian() ?>%</strong>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-light" role="progressbar" 
                                     style="width: <?= $hasil_diagnosis->getPersentaseKepastian() ?>%"></div>
                            </div>
                            <small class="text-light">Kategori: <?= $hasil_diagnosis->getKategoriKepastian() ?></small>
                        </div>
                        
                        <div class="mb-3">
                            <strong>Solusi Penanganan:</strong>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($hasil_diagnosis->getPenyakit()['solusi_penyakit'])) ?></p>
                        </div>
                    </div>
                    
                    <div class="col-md-4 text-end">
                        <a href="#" onclick="showExplanation(<?= $id_konsultasi ?>); return false;" 
                           class="btn btn-light btn-lg mb-2">
                            <i class="fas fa-info-circle me-2"></i>
                            Penjelasan Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

        <!-- Form Konsultasi -->
        <?php if (!$hasil_diagnosis): ?>
        <form method="POST" id="konsultasiForm">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-list-check me-2"></i>
                                Pilih Gejala yang Anda Alami
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($gejala_list as $gejala): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card gejala-card h-100" onclick="toggleGejala(<?= $gejala['id_gejala'] ?>)">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="gejala[]" value="<?= $gejala['id_gejala'] ?>" 
                                                       id="gejala_<?= $gejala['id_gejala'] ?>">
                                                <label class="form-check-label" for="gejala_<?= $gejala['id_gejala'] ?>">
                                                    <strong><?= htmlspecialchars($gejala['nama_gejala']) ?></strong>
                                                </label>
                                            </div>
                                            <p class="text-muted small mt-2 mb-0">
                                                <?= htmlspecialchars($gejala['deskripsi_gejala']) ?>
                                            </p>
                                            
                                            <div class="cf-slider" id="slider_<?= $gejala['id_gejala'] ?>" style="display: none;">
                                                <label class="form-label small">Tingkat Keyakinan:</label>
                                                <input type="range" class="form-range" 
                                                       name="cf_user[<?= $gejala['id_gejala'] ?>]" 
                                                       min="0.2" max="1" step="0.1" value="1"
                                                       oninput="updateCFValue(<?= $gejala['id_gejala'] ?>, this.value)">
                                                <div class="d-flex justify-content-between">
                                                    <small>Tidak Yakin (20%)</small>
                                                    <small id="cf_value_<?= $gejala['id_gejala'] ?>">Sangat Yakin (100%)</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                    <i class="fas fa-search me-2"></i>
                                    Proses Diagnosis
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <!-- Modal Penjelasan -->
    <div class="modal fade" id="explanationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>Penjelasan Diagnosis
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="explanationContent">
                    <div class="text-center">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleGejala(idGejala) {
            const checkbox = document.getElementById('gejala_' + idGejala);
            const slider = document.getElementById('slider_' + idGejala);
            const card = checkbox.closest('.gejala-card');
            
            checkbox.checked = !checkbox.checked;
            
            if (checkbox.checked) {
                slider.style.display = 'block';
                card.classList.add('selected');
            } else {
                slider.style.display = 'none';
                card.classList.remove('selected');
            }
            
            updateSubmitButton();
        }
        
        function updateCFValue(idGejala, value) {
            const cfValueElement = document.getElementById('cf_value_' + idGejala);
            const percentage = Math.round(value * 100);
            
            let label = '';
            if (percentage >= 80) label = 'Sangat Yakin';
            else if (percentage >= 60) label = 'Yakin';
            else if (percentage >= 40) label = 'Cukup Yakin';
            else label = 'Tidak Yakin';
            
            cfValueElement.textContent = label + ' (' + percentage + '%)';
        }
        
        function updateSubmitButton() {
            const checkedBoxes = document.querySelectorAll('input[name="gejala[]"]:checked');
            const submitBtn = document.getElementById('submitBtn');
            
            if (checkedBoxes.length > 0) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('btn-secondary');
                submitBtn.classList.add('btn-primary');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-secondary');
            }
        }
        
        function showExplanation(konsultasiId) {
            const modal = new bootstrap.Modal(document.getElementById('explanationModal'));
            const content = document.getElementById('explanationContent');
            
            // Reset content
            content.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-success" role="status">
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
        
        // Prevent card click when clicking on checkbox or slider
        document.querySelectorAll('.form-check-input, .form-range').forEach(element => {
            element.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
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