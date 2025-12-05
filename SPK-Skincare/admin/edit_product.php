<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Pastikan admin sudah login
if (!is_admin_logged_in()) {
    redirect('login.php');
}

// Ambil ID produk dari parameter
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('manage_skincare.php');
}

$product_id = $_GET['id'];

// Ambil detail produk
$product = get_product_details($db, $product_id);
if (!$product) {
    redirect('manage_skincare.php');
}

// Ambil kesesuaian dengan jenis kulit
$skin_type_compatibility = get_product_skin_type_compatibility($db, $product_id);
$skin_type_scores = [];
foreach ($skin_type_compatibility as $compatibility) {
    $skin_type_scores[$compatibility['skin_type_id']] = $compatibility['compatibility_score'];
}

// Ambil kesesuaian dengan masalah kulit
$skin_concern_compatibility = get_product_skin_concern_compatibility($db, $product_id);
$concern_scores = [];
foreach ($skin_concern_compatibility as $concern) {
    $concern_scores[$concern['concern_id']] = $concern['effectiveness_score'];
}

// Ambil semua brand
$stmt = $db->query("SELECT DISTINCT brand FROM skincare_products ORDER BY brand");
$brands = $stmt->fetchAll();

// Ambil semua jenis kulit
$skin_types = get_all_skin_types($db);

// Ambil semua masalah kulit
$skin_concerns = get_all_skin_concerns($db);

$success_msg = $error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $name = clean_input($_POST['name']);
    $brand = clean_input($_POST['brand']);
    $size = clean_input($_POST['size']);
    $active_ingredient_type = clean_input($_POST['active_ingredient_type']);
    $release_year = clean_input($_POST['release_year']);
}
    // Validasi data wajib
    if (empty($name) || empty($brand)) {
        $error_msg = "Nama produk dan brand wajib diisi.";
    } else {
        // Validasi nilai kesesuaian kulit
        $valid_scores = true;
        if (isset($_POST['skin_type_compatibility'])) {
            foreach ($_POST['skin_type_compatibility'] as $score) {
                if ($score < 0 || $score > 1) {
                    $valid_scores = false;
                    break;
                }
            }
        }
    
        if (isset($_POST['concern_effectiveness'])) {
            foreach ($_POST['concern_effectiveness'] as $score) {
                if ($score < 0 || $score > 1) {
                    $valid_scores = false;
                    break;
                }
            }
        }
        
        if (!$valid_scores) {
            $error_msg = "Nilai kesesuaian dan efektivitas harus antara 0.00 dan 1.00.";
        } else {
            try {
                // Mulai transaksi
                $db->beginTransaction();
                
                // Update produk
                $stmt = $db->prepare("UPDATE skincare_products SET name = ?, brand = ?, size = ?, active_ingredient_type = ?, release_year = ? WHERE product_id = ?");
                $stmt->execute([$name, $brand, $size, $active_ingredient_type, $release_year, $product_id]);
                
                // Hapus kesesuaian jenis kulit yang lama
                $stmt = $db->prepare("DELETE FROM product_skin_type_compatibility WHERE product_id = ?");
                $stmt->execute([$product_id]);
                
                // Insert kesesuaian jenis kulit yang baru
                if (isset($_POST['skin_type_compatibility']) && is_array($_POST['skin_type_compatibility'])) {
                    $stmt = $db->prepare("INSERT INTO product_skin_type_compatibility (product_id, skin_type_id, compatibility_score) VALUES (?, ?, ?)");
                    
                    foreach ($_POST['skin_type_compatibility'] as $skin_type_id => $score) {
                        $stmt->execute([$product_id, $skin_type_id, $score]);
                    }
                }
                
                // Hapus efektivitas masalah kulit yang lama
                $stmt = $db->prepare("DELETE FROM product_skin_concern_compatibility WHERE product_id = ?");
                $stmt->execute([$product_id]);
                
                // Insert efektivitas masalah kulit yang baru
                if (isset($_POST['concern_effectiveness']) && is_array($_POST['concern_effectiveness'])) {
                    $stmt = $db->prepare("INSERT INTO product_skin_concern_compatibility (product_id, concern_id, effectiveness_score) VALUES (?, ?, ?)");
                    
                    foreach ($_POST['concern_effectiveness'] as $concern_id => $score) {
                        $stmt->execute([$product_id, $concern_id, $score]);
                    }
                }
                
                // Commit transaksi
                $db->commit();
                
                $success_msg = "Produk berhasil diperbarui.";
                
            } catch (PDOException $e) {
                // Rollback transaksi jika terjadi error
                $db->rollBack();
                $error_msg = "Error: " . $e->getMessage();
            }
        }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - <?php echo htmlspecialchars($product['name']); ?></title>
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
            <h2>Edit Produk Skincare</h2>
        </div>
        
        <?php if (!empty($success_msg)): ?>
            <?php echo display_success($success_msg); ?>
        <?php endif; ?>
        
        <?php if (!empty($error_msg)): ?>
            <?php echo display_error($error_msg); ?>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Edit Produk: <?php echo htmlspecialchars($product['name']); ?></h5>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Nama Produk</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="brand">Brand</label>
                            <input type="text" class="form-control" id="brand" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>" list="brandList" required>
                            <datalist id="brandList">
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo htmlspecialchars($brand['brand']); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label for="size">Ukuran</label>
                            <input type="text" class="form-control" id="size" name="size" value="<?php echo htmlspecialchars($product['size']); ?>" placeholder="Contoh: 100ml, 50g">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="active_ingredient_type">Tipe Bahan Aktif</label>
                            <input type="text" class="form-control" id="active_ingredient_type" name="active_ingredient_type" value="<?php echo htmlspecialchars($product['active_ingredient_type']); ?>">
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label for="release_year">Tahun Rilis</label>
                            <input type="number" class="form-control" id="release_year" name="release_year" value="<?php echo htmlspecialchars($product['release_year']); ?>" min="1900" max="<?php echo date('Y'); ?>">
                        </div>
                    </div>
                    
                    <h5 class="mt-4">Kesesuaian dengan Jenis Kulit</h5>
                    <div class="form-row">
                        <?php foreach ($skin_types as $skin_type): ?>
                            <div class="form-group col-md-6">
                                <label for="skin_type_<?php echo $skin_type['skin_type_id']; ?>"><?php echo htmlspecialchars($skin_type['name']); ?></label>
                                <input type="number" class="form-control" id="skin_type_<?php echo $skin_type['skin_type_id']; ?>" name="skin_type_compatibility[<?php echo $skin_type['skin_type_id']; ?>]" value="<?php echo isset($skin_type_scores[$skin_type['skin_type_id']]) ? number_format($skin_type_scores[$skin_type['skin_type_id']], 2) : '0.00'; ?>" min="0" max="1" step="0.01" required>
                                <small class="form-text text-muted">Nilai 0.00 (tidak cocok) sampai 1.00 (sangat cocok)</small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <h5 class="mt-4">Efektivitas untuk Masalah Kulit</h5>
                    <div class="form-row">
                        <?php foreach ($skin_concerns as $concern): ?>
                            <div class="form-group col-md-6">
                                <label for="concern_<?php echo $concern['concern_id']; ?>"><?php echo htmlspecialchars($concern['name']); ?></label>
                                <input type="number" class="form-control" id="concern_<?php echo $concern['concern_id']; ?>" name="concern_effectiveness[<?php echo $concern['concern_id']; ?>]" value="<?php echo isset($concern_scores[$concern['concern_id']]) ? number_format($concern_scores[$concern['concern_id']], 2) : '0.00'; ?>" min="0" max="1" step="0.01" required>
                                <small class="form-text text-muted">Nilai 0.00 (tidak efektif) sampai 1.00 (sangat efektif)</small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="manage_skincare.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
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