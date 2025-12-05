<?php
// Aktifkan error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: index.php');
    exit();
}

require_once __DIR__ . '/../controllers/PenyakitController.php';

// Inisialisasi variabel di luar try-catch
$message = '';
$message_type = '';
$penyakit_list = []; // Inisialisasi dengan array kosong

try {
    $penyakitController = new PenyakitController();
    
    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action == 'add') {
            $result = $penyakitController->store($_POST);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'danger';
        }
        elseif ($action == 'edit') {
            $result = $penyakitController->update($_POST['id'], $_POST);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'danger';
        }
        elseif ($action == 'delete') {
            $result = $penyakitController->prosesHapusPenyakit($_POST['id']);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'danger';
        }
    }
    
    // Ambil semua data penyakit
    $result = $penyakitController->index();
    if ($result['success']) {
        $penyakit_list = $result['data'];
    } else {
        $message = $result['message'];
        $message_type = 'danger';
    }
    
} catch (Exception $e) {
    $message = 'Terjadi kesalahan: ' . $e->getMessage();
    $message_type = 'danger';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Penyakit - Sistem Pakar Penyakit Mata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-cogs me-1"></i>Kelola Data
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="kelola_penyakit.php"><i class="fas fa-disease me-2"></i>Penyakit</a></li>
                            <li><a class="dropdown-item" href="kelola_gejala.php"><i class="fas fa-symptoms me-2"></i>Gejala</a></li>
                            <li><a class="dropdown-item" href="kelola_aturan.php"><i class="fas fa-rules me-2"></i>Aturan</a></li>
                        </ul>
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

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>
                        <i class="fas fa-disease me-2"></i>
                        Kelola Data Penyakit
                    </h2>
                    <button class="btn btn-primary-custom btn-custom" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="fas fa-plus me-2"></i>Tambah Penyakit
                    </button>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Data Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Daftar Penyakit
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Penyakit</th>
                                        <th>Deskripsi</th>
                                        <th>Solusi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($penyakit_list as $penyakit): ?>
                                        <tr>
                                            <td><span class="badge bg-primary"><?php echo $penyakit['id_penyakit']; ?></span></td>
                                            <td><strong><?php echo $penyakit['nama_penyakit']; ?></strong></td>
                                            <td><?php echo substr($penyakit['deskripsi_penyakit'], 0, 100) . (strlen($penyakit['deskripsi_penyakit']) > 100 ? '...' : ''); ?></td>
                                            <td><?php echo substr($penyakit['solusi_penyakit'], 0, 100) . (strlen($penyakit['solusi_penyakit']) > 100 ? '...' : ''); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="editPenyakit(<?php echo htmlspecialchars(json_encode($penyakit)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="deletePenyakit(<?php echo $penyakit['id_penyakit']; ?>, '<?php echo $penyakit['nama_penyakit']; ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Tambah Penyakit Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <!-- Form Tambah Penyakit -->
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <!-- Hapus bagian input ID Penyakit berikut -->
                        <!--
                        <div class="mb-3">
                            <label for="id_penyakit" class="form-label">ID Penyakit</label>
                            <input type="number" class="form-control" id="id_penyakit" name="id_penyakit" 
                                   placeholder="Nomor ID" required>
                            <small class="text-muted">Nomor ID tidak boleh kosong</small>
                        </div>
                        -->
                        
                        <div class="mb-3">
                            <label for="nama_penyakit" class="form-label">Nama Penyakit</label>
                            <input type="text" class="form-control" id="nama_penyakit" name="nama_penyakit" 
                                   placeholder="Masukkan nama penyakit" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" 
                                      placeholder="Masukkan deskripsi penyakit"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="solusi" class="form-label">Solusi/Pengobatan</label>
                            <textarea class="form-control" id="solusi" name="solusi" rows="3" 
                                      placeholder="Masukkan solusi atau pengobatan"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
                
                
                <!-- JavaScript Functions -->
                <script>
                    function editPenyakit(penyakit) {
                        document.getElementById('edit_id_penyakit').value = penyakit.id_penyakit;
                        // Hapus baris berikut: document.getElementById('edit_kode_penyakit').value = penyakit.kode_penyakit || '';
                        document.getElementById('edit_nama_penyakit').value = penyakit.nama_penyakit;
                        document.getElementById('edit_deskripsi').value = penyakit.deskripsi_penyakit;
                        document.getElementById('edit_solusi').value = penyakit.solusi_penyakit;
                        
                        const modal = new bootstrap.Modal(document.getElementById('editModal'));
                        modal.show();
                    }
                    
                    function deletePenyakit(id, nama) {
                        document.getElementById('delete_id').value = id;
                        document.getElementById('delete_nama').textContent = nama;
                        
                        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
                        modal.show();
                    }
                </script>
            </div>
        </div>
    </div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Edit Penyakit
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id_penyakit">
                    
                    <div class="mb-3">
                        <label for="edit_nama_penyakit" class="form-label">Nama Penyakit</label>
                        <input type="text" class="form-control" id="edit_nama_penyakit" name="nama_penyakit" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_solusi" class="form-label">Solusi/Pengobatan</label>
                        <textarea class="form-control" id="edit_solusi" name="solusi" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-trash me-2"></i>Hapus Penyakit
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus penyakit <strong id="delete_nama"></strong>?</p>
                    <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function editPenyakit(penyakit) {
        // Pastikan menggunakan nama field yang benar
        document.getElementById('edit_id_penyakit').value = penyakit.id_penyakit;
        document.getElementById('edit_nama_penyakit').value = penyakit.nama_penyakit;
        document.getElementById('edit_deskripsi').value = penyakit.deskripsi_penyakit;
        document.getElementById('edit_solusi').value = penyakit.solusi_penyakit;
        
        const modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.show();
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