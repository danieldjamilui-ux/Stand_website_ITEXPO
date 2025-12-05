<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../models/Product.php';

// Cek apakah user sudah login
if (!is_logged_in()) {
    redirect('login.php');
}

// Inisialisasi objek Product untuk mendapatkan semua produk
$productObj = new Product($db);
$products = $productObj->getAll();

// Filter produk berdasarkan brand jika ada parameter
$selected_brand = '';
if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $selected_brand = clean_input($_GET['brand']);
    $filtered_products = [];
    foreach ($products as $product) {
        if ($product['brand'] == $selected_brand) {
            $filtered_products[] = $product;
        }
    }
    $products = $filtered_products;
}

// Dapatkan semua brand untuk filter
$brands = [];
$stmt = $db->query("SELECT DISTINCT brand FROM skincare_products ORDER BY brand");
$brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dapatkan data kesesuaian produk dengan jenis kulit dan masalah kulit
function get_product_compatibilities($db, $products) {
    $result = [];
    foreach ($products as $product) {
        $product_id = $product['product_id'];
        $result[$product_id] = [
            'skin_types' => get_product_skin_type_compatibility($db, $product_id),
            'skin_concerns' => get_product_skin_concern_compatibility($db, $product_id)
        ];
    }
    return $result;
}

$product_compatibilities = get_product_compatibilities($db, $products);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Produk Skincare - Sistem Rekomendasi Skincare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- <link rel="stylesheet" href="../assets/css/style.css"> -->
    <style>
        .product-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .filter-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .compatibility-score {
            font-size: 0.9rem;
            font-weight: bold;
        }
        .score-high {
            color: #28a745;
        }
        .score-medium {
            color: #ffc107;
        }
        .score-low {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-5 mb-5">
        <h2 class="mb-4">Daftar Produk Skincare</h2>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form method="get" action="products.php" class="form-inline">
                <div class="form-group mr-3">
                    <label for="brand" class="mr-2">Filter berdasarkan Brand:</label>
                    <select name="brand" id="brand" class="form-control" onchange="this.form.submit()">
                        <option value="">Semua Brand</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo $brand['brand']; ?>" <?php echo ($selected_brand == $brand['brand']) ? 'selected' : ''; ?>>
                                <?php echo $brand['brand']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!empty($selected_brand)): ?>
                    <a href="products.php" class="btn btn-outline-secondary">Reset Filter</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Products Table -->
        <?php if (empty($products)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <h4>Tidak ada produk yang tersedia</h4>
                <?php if (!empty($selected_brand)): ?>
                    <p>Tidak ada produk untuk brand "<?php echo $selected_brand; ?>"</p>
                    <a href="products.php" class="btn btn-primary mt-2">Lihat Semua Produk</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Nama Produk</th>
                            <th>Brand</th>
                            <th>Ukuran</th>
                            <th>Bahan Aktif</th>
                            <th>Tahun Rilis</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><strong><?php echo $product['name']; ?></strong></td>
                                <td><?php echo $product['brand']; ?></td>
                                <td><?php echo $product['size']; ?></td>
                                <td><?php echo $product['active_ingredient_type']; ?></td>
                                <td><?php echo $product['release_year']; ?></td>
                                <td>
                                    <a href="product_detail.php?product_id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>