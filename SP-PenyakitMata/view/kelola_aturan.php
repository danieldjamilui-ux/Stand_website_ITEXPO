<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: index.php');
    exit();
}

// Hapus atau komentari var_dump jika tidak diperlukan lagi
// var_dump($_SESSION);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AturanController.php';
require_once __DIR__ . '/../controllers/PenyakitController.php';
require_once __DIR__ . '/../controllers/GejalaController.php';

$database = new Database();
$db = $database->getConnection();

$aturanController = new AturanController($db);
$penyakitController = new PenyakitController($db);
$gejalaController = new GejalaController($db);

$message = '';
$message_type = '';

// Handle form submissions
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        $data = [
            'cf_pakar' => floatval($_POST['cf_pakar'] ?? 0),
            'id_penyakit_hasil' => intval($_POST['id_penyakit_hasil'] ?? 0),
            'gejala_ids' => $_POST['gejala_ids'] ?? []
        ];
        
        // Validasi minimal 1 gejala dipilih
        if (empty($data['gejala_ids'])) {
            $message = 'Pilih minimal 1 gejala';
            $message_type = 'danger';
        } else {
            $result = $aturanController->prosesSimpanAturan($data);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'danger';
        }
        
    } elseif ($action == 'edit') {
        $id = intval($_POST['id_aturan'] ?? 0);
        $data = [
            'cf_pakar' => floatval($_POST['cf_pakar'] ?? 0),
            'id_penyakit_hasil' => intval($_POST['id_penyakit_hasil'] ?? 0),
            'gejala_ids' => $_POST['gejala_ids'] ?? []
        ];
        
        // Validasi minimal 1 gejala dipilih
        if (empty($data['gejala_ids'])) {
            $message = 'Pilih minimal 1 gejala';
            $message_type = 'danger';
        } else {
            $result = $aturanController->prosesUpdateAturan($id, $data);
            $message = $result['message'];
            $message_type = $result['success'] ? 'success' : 'danger';
        }
        
    } elseif ($action == 'delete') {
        $id = intval($_POST['id_aturan'] ?? 0);
        $result = $aturanController->prosesHapusAturan($id);
        $message = $result['message'];
        $message_type = $result['success'] ? 'success' : 'danger';
    }
}

// Get data for display
$page = $_GET['page'] ?? 1;
$aturanResult = $aturanController->index($page, 10);
$aturan_list = $aturanResult['data'] ?? [];
$pagination = $aturanResult['pagination'] ?? [];

$penyakitResult = $penyakitController->tampilkanDaftarPenyakit();
$penyakit_list = $penyakitResult['data'] ?? [];

$gejalaResult = $gejalaController->tampilkanDaftarGejala();
$gejala_list = $gejalaResult['data'] ?? [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Aturan - Sistem Pakar Penyakit Mata</title>
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
                            <li><a class="dropdown-item" href="kelola_penyakit.php"><i class="fas fa-disease me-2"></i>Penyakit</a></li>
                            <li><a class="dropdown-item" href="kelola_gejala.php"><i class="fas fa-symptoms me-2"></i>Gejala</a></li>
                            <li><a class="dropdown-item active" href="kelola_aturan.php"><i class="fas fa-rules me-2"></i>Aturan</a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : $_SESSION['username']; ?>
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
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-1">
                                    <i class="fas fa-cogs text-primary me-2"></i>
                                    Kelola Aturan
                                </h2>
                                <p class="text-muted mb-0">Kelola aturan sistem pakar untuk diagnosis penyakit mata</p>
                            </div>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                                <i class="fas fa-plus me-2"></i>Tambah Aturan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?= $message_type == 'success' ? 'check-circle' : 'exclamation-triangle' ?> me-2"></i>
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Aturan List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Penyakit</th>
                                        <th>Gejala</th>
                                        <th>CF Pakar</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($aturan_list)): ?>
                                        <?php foreach ($aturan_list as $aturan): ?>
                                        <tr>
                                            <td><?= $aturan['id_aturan'] ?></td>
                                            <td>
                                                <span class="badge bg-primary"><?= htmlspecialchars($aturan['nama_penyakit']) ?></span>
                                            </td>
                                            <td>
                                                <?php 
                                                // Get gejala for this aturan
                                                $gejalaAturan = $aturanController->getGejalaByAturan($aturan['id_aturan']);
                                                if ($gejalaAturan['success']) {
                                                    foreach ($gejalaAturan['data'] as $gejala) {
                                                        echo '<span class="badge bg-secondary me-1">' . htmlspecialchars($gejala['nama_gejala']) . '</span>';
                                                    }
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= number_format($aturan['cf_pakar'], 2) ?></span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-warning me-1" 
                                                        onclick="editAturan(<?= htmlspecialchars(json_encode($aturan)) ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteAturan(<?= $aturan['id_aturan'] ?>, '<?= htmlspecialchars($aturan['nama_penyakit']) ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <i class="fas fa-inbox fa-2x mb-2"></i><br>
                                                Belum ada data aturan
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if (!empty($pagination) && $pagination['total_pages'] > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-plus me-2"></i>Tambah Aturan Baru
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="id_penyakit_hasil" class="form-label">Penyakit</label>
                            <select class="form-select" name="id_penyakit_hasil" required>
                                <option value="">Pilih Penyakit</option>
                                <?php foreach ($penyakit_list as $penyakit): ?>
                                <option value="<?= $penyakit['id_penyakit'] ?>">
                                    <?= htmlspecialchars($penyakit['nama_penyakit']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Gejala</label>
                            <div class="row">
                                <?php foreach ($gejala_list as $gejala): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="gejala_ids[]" value="<?= $gejala['id_gejala'] ?>" 
                                               id="gejala_add_<?= $gejala['id_gejala'] ?>">
                                        <label class="form-check-label" for="gejala_add_<?= $gejala['id_gejala'] ?>">
                                            <?= htmlspecialchars($gejala['nama_gejala']) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="cf_pakar" class="form-label">CF Pakar (0.0 - 1.0)</label>
                            <input type="number" class="form-control" name="cf_pakar" 
                                   min="0" max="1" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="editForm">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id_aturan" id="edit_id_aturan">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-edit me-2"></i>Edit Aturan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_id_penyakit_hasil" class="form-label">Penyakit</label>
                            <select class="form-select" name="id_penyakit_hasil" id="edit_id_penyakit_hasil" required>
                                <option value="">Pilih Penyakit</option>
                                <?php foreach ($penyakit_list as $penyakit): ?>
                                <option value="<?= $penyakit['id_penyakit'] ?>">
                                    <?= htmlspecialchars($penyakit['nama_penyakit']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Gejala</label>
                            <div class="row" id="edit_gejala_list">
                                <?php foreach ($gejala_list as $gejala): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="gejala_ids[]" value="<?= $gejala['id_gejala'] ?>" 
                                               id="gejala_edit_<?= $gejala['id_gejala'] ?>">
                                        <label class="form-check-label" for="gejala_edit_<?= $gejala['id_gejala'] ?>">
                                            <?= htmlspecialchars($gejala['nama_gejala']) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_cf_pakar" class="form-label">CF Pakar (0.0 - 1.0)</label>
                            <input type="number" class="form-control" name="cf_pakar" id="edit_cf_pakar"
                                   min="0" max="1" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-2"></i>Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id_aturan" id="delete_id_aturan">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-trash me-2"></i>Hapus Aturan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus aturan untuk penyakit <strong id="delete_penyakit_name"></strong>?</p>
                        <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Hapus
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>

function editAturan(aturan) {
    console.log('Editing aturan:', aturan); // Debug log
    
    // Set nilai form
    document.getElementById('edit_id_aturan').value = aturan.id_aturan;
    document.getElementById('edit_id_penyakit_hasil').value = aturan.id_penyakit_hasil;
    document.getElementById('edit_cf_pakar').value = parseFloat(aturan.cf_pakar).toFixed(2);
    
    // Reset semua checkbox gejala
    document.querySelectorAll('#edit_gejala_list input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
    });
    
    // Ambil gejala yang terkait dengan aturan ini
    fetch(`../controllers/AturanController.php?action=getGejalaByAturan&id_aturan=${aturan.id_aturan}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Gejala data:', data); // Debug log
            if (data.success && data.data && Array.isArray(data.data)) {
                data.data.forEach(gejala => {
                    const checkbox = document.getElementById(`gejala_edit_${gejala.id_gejala}`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            } else {
                console.error('Data gejala tidak valid:', data);
            }
        })
        .catch(error => {
            console.error('Error fetching gejala:', error);
        });
    
    // Tampilkan modal edit
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    editModal.show();
}
        
        function deleteAturan(id, penyakitName) {
            document.getElementById('delete_id_aturan').value = id;
            document.getElementById('delete_penyakit_name').textContent = penyakitName;
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
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