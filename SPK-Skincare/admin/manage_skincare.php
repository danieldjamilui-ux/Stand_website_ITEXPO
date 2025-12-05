<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../includes/functions.php';

// Pastikan admin sudah login
if (!is_admin_logged_in()) {
    redirect('login.php');
}

// Inisialisasi variabel
$success_msg = $error_msg = '';

// Ambil pesan dari session jika ada
if (isset($_SESSION['success_msg'])) {
    $success_msg = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

if (isset($_SESSION['error_msg'])) {
    $error_msg = $_SESSION['error_msg'];
    unset($_SESSION['error_msg']);
}

// Proses hapus produk
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $product_id = $_GET['delete'];
    
    try {
        // Mulai transaksi
        $db->beginTransaction();
        
        // Hapus data di tabel product_skin_type_compatibility
        $stmt = $db->prepare("DELETE FROM product_skin_type_compatibility WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        // Hapus data di tabel product_skin_concern_compatibility
        $stmt = $db->prepare("DELETE FROM product_skin_concern_compatibility WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        // Hapus data di tabel recommendation_details yang terkait dengan produk
        $stmt = $db->prepare("DELETE FROM recommendation_details WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        // Hapus produk
        $stmt = $db->prepare("DELETE FROM skincare_products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        // Commit transaksi
        $db->commit();
        
        $success_msg = "Produk berhasil dihapus.";
    } catch (PDOException $e) {
        // Rollback transaksi jika terjadi error
        $db->rollBack();
        $error_msg = "Error: " . $e->getMessage();
    }
}

// Ambil semua produk skincare
$stmt = $db->query("SELECT * FROM skincare_products ORDER BY name");
$products = $stmt->fetchAll();

// Ambil semua brand (daftar brand unik dari tabel skincare_products)
$stmt = $db->query("SELECT DISTINCT brand FROM skincare_products ORDER BY brand");
$brands = $stmt->fetchAll();

// Ambil semua jenis kulit
$skin_types = get_all_skin_types($db);

// Ambil semua masalah kulit
$skin_concerns = get_all_skin_concerns($db);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk Skincare - Admin</title>
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
            <h2>Kelola Produk Skincare</h2>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#addProductModal">
                <i class="fas fa-plus"></i> Tambah Produk
            </button>
        </div>
        
        <?php if (!empty($success_msg)): ?>
            <?php echo display_success($success_msg); ?>
        <?php endif; ?>
        
        <?php if (!empty($error_msg)): ?>
            <?php echo display_error($error_msg); ?>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Produk</th>
                                <th>Brand</th>
                                <th>Ukuran</th>
                                <th>Tipe Bahan Aktif</th>
                                <th>Tahun Rilis</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($products) > 0): ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo $product['product_id']; ?></td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['brand']); ?></td>
                                        <td><?php echo htmlspecialchars($product['size']); ?></td>
                                        <td><?php echo htmlspecialchars($product['active_ingredient_type']); ?></td>
                                        <td><?php echo htmlspecialchars($product['release_year']); ?></td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="Product Actions">
                                                <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-info" title="Detail Produk">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-primary" title="Edit Produk">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="manage_skincare.php?delete=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')" title="Hapus Produk">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada produk skincare.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Tambah Produk -->
    <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addProductModalLabel">Tambah Produk Skincare</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="add_product.php" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Nama Produk</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="brand">Brand</label>
                                <input type="text" class="form-control" id="brand" name="brand" list="brandList" required>
                                <datalist id="brandList">
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?php echo htmlspecialchars($brand['brand']); ?>">
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="size">Ukuran</label>
                                <input type="text" class="form-control" id="size" name="size" placeholder="Contoh: 100ml, 50g">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="active_ingredient_type">Tipe Bahan Aktif</label>
                                <input type="text" class="form-control" id="active_ingredient_type" name="active_ingredient_type">
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="release_year">Tahun Rilis</label>
                                <input type="number" class="form-control" id="release_year" name="release_year" min="1900" max="<?php echo date('Y'); ?>">
                            </div>
                        </div>
                        
                        <h5 class="mt-4">Kesesuaian dengan Jenis Kulit</h5>
                        <div class="form-row">
                            <?php foreach ($skin_types as $skin_type): ?>
                                <div class="form-group col-md-6">
                                    <label for="skin_type_<?php echo $skin_type['skin_type_id']; ?>"><?php echo htmlspecialchars($skin_type['name']); ?></label>
                                    <input type="number" class="form-control" id="skin_type_<?php echo $skin_type['skin_type_id']; ?>" name="skin_type_compatibility[<?php echo $skin_type['skin_type_id']; ?>]" min="0" max="1" step="0.01" placeholder="0.00 - 1.00" value="0.50" required>
                                    <small class="form-text text-muted">Nilai 0.00 (tidak cocok) sampai 1.00 (sangat cocok)</small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <h5 class="mt-4">Efektivitas untuk Masalah Kulit</h5>
                        <div class="form-row">
                            <?php foreach ($skin_concerns as $concern): ?>
                                <div class="form-group col-md-6">
                                    <label for="concern_<?php echo $concern['concern_id']; ?>"><?php echo htmlspecialchars($concern['name']); ?></label>
                                    <input type="number" class="form-control" id="concern_<?php echo $concern['concern_id']; ?>" name="concern_effectiveness[<?php echo $concern['concern_id']; ?>]" min="0" max="1" step="0.01" placeholder="0.00 - 1.00" value="0.50" required>
                                    <small class="form-text text-muted">Nilai 0.00 (tidak efektif) sampai 1.00 (sangat efektif)</small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
