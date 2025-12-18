<?php
session_start();

// --- PROTEKSI: ADMIN DILARANG MASUK KERANJANG ---
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    echo "<script>
        alert('Halo Admin! Anda tidak dapat melakukan pembelian dengan akun ini.');
        window.location = 'admin/index.php'; // Kembalikan ke Dashboard
    </script>";
    exit;
}

// Tambah Item
if (isset($_POST['add_to_cart'])) {
    $id = $_POST['id'];
    $item = [
        'id' => $id,
        'name' => $_POST['name'],
        'price' => $_POST['price'],
        'image' => $_POST['image'],
        'qty' => 1
    ];

    $found = false;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $val) {
            if ($val['id'] == $id) {
                $_SESSION['cart'][$key]['qty'] += 1;
                $found = true;
            }
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = $item;
    }
    header("Location: cart.php");
    exit;
}

// Hapus Item
if (isset($_GET['del'])) {
    $key = $_GET['del'];
    unset($_SESSION['cart'][$key]);
    $_SESSION['cart'] = array_values($_SESSION['cart']); 
    header("Location: cart.php");
    exit;
}

// Reset Cart
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit;
}

// --- PROTEKSI ADMIN: Admin tidak boleh masuk sini ---
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    echo "<script>
        alert('Halo Admin! Anda tidak bisa berbelanja menggunakan akun ini.');
        window.location = 'admin/index.php'; // Kembalikan ke dashboard
    </script>";
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Toko Pro</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --text-dark: #1f2937;
            --text-grey: #6b7280;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
        }

        /* --- NAVBAR GLASSMORPHISM --- */
        .navbar-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
            padding: 15px 0;
        }
        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            background: -webkit-linear-gradient(45deg, #667eea, #764ba2);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* --- TITLE SECTION --- */
        .page-header {
            padding: 30px 0;
        }
        .page-title {
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 0;
        }

        /* --- CART CARD --- */
        .card-modern {
            border: none;
            border-radius: 20px;
            background: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        /* Table Styling */
        .table-cart thead th {
            background-color: #f9fafb;
            color: var(--text-grey);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 15px 20px;
            border-bottom: 2px solid #e5e7eb;
        }
        .table-cart td {
            vertical-align: middle;
            padding: 20px;
            border-bottom: 1px solid #f3f4f6;
        }
        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .product-thumb {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .product-name {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 2px;
            display: block;
        }
        
        .qty-badge {
            background: #f3f4f6;
            padding: 5px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--text-dark);
            border: 1px solid #e5e7eb;
        }

        .price-text { font-weight: 600; color: var(--text-grey); }
        .total-text { font-weight: 700; color: #764ba2; font-size: 1.1rem; }

        /* Delete Button */
        .btn-delete {
            width: 35px; height: 35px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #ef4444;
            background: #fef2f2;
            transition: 0.3s;
        }
        .btn-delete:hover {
            background: #ef4444;
            color: white;
            transform: scale(1.1);
        }

        /* --- SUMMARY CARD (KANAN) --- */
        .card-summary {
            position: sticky;
            top: 100px;
            padding: 30px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: var(--text-grey);
            font-size: 0.95rem;
        }
        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px dashed #e5e7eb;
            font-size: 1.2rem;
            font-weight: 800;
            color: var(--text-dark);
        }
        .summary-total span:last-child {
            color: #764ba2;
        }

        /* Gradient Button */
        .btn-checkout {
            background: var(--primary-gradient);
            border: none;
            color: white;
            font-weight: 700;
            padding: 15px;
            border-radius: 12px;
            width: 100%;
            margin-top: 25px;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(118, 75, 162, 0.3);
        }
        .btn-checkout:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(118, 75, 162, 0.4);
            color: white;
        }

        /* Empty State */
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-icon { font-size: 5rem; color: #d1d5db; margin-bottom: 20px; animation: float 3s ease-in-out infinite; }
        
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-10px); } 100% { transform: translateY(0px); } }
    </style>
</head>
<body>

<!-- Navbar Sticky -->
<nav class="navbar navbar-expand-lg navbar-glass sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-shopping-bag me-2"></i>SHOP COMMERCE
        </a>
        <div class="ms-auto">
            <a href="index.php" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold border-2">
                <i class="fas fa-arrow-left me-2"></i> Lanjut Belanja
            </a>
        </div>
    </div>
</nav>

<div class="container pb-5">
    
    <!-- Title -->
    <div class="page-header">
        <h2 class="page-title">Keranjang Belanja <span class="fs-5 text-muted fw-normal">(<?= isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?> item)</span></h2>
    </div>

    <?php if (empty($_SESSION['cart'])): ?>
        
        <!-- TAMPILAN KOSONG -->
        <div class="card-modern empty-state">
            <i class="fas fa-shopping-basket empty-icon"></i>
            <h3 class="fw-bold text-dark">Keranjangmu masih kosong</h3>
            <p class="text-muted">Sepertinya kamu belum menambahkan barang apapun.</p>
            <a href="index.php" class="btn btn-checkout" style="width: auto; padding-left: 40px; padding-right: 40px;">
                Mulai Belanja Sekarang
            </a>
        </div>

    <?php else: ?>

        <div class="row">
            <!-- KOLOM KIRI: TABEL -->
            <div class="col-lg-8 mb-4">
                <div class="card-modern">
                    <div class="table-responsive">
                        <table class="table table-cart mb-0">
                            <thead>
                                <tr>
                                    <th width="45%">Produk</th>
                                    <th width="20%">Harga</th>
                                    <th width="10%">Qty</th>
                                    <th width="20%" class="text-end">Total</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $grand_total = 0;
                                foreach ($_SESSION['cart'] as $key => $val): 
                                    $subtotal = $val['price'] * $val['qty'];
                                    $grand_total += $subtotal;
                                ?>
                                <tr>
                                    <td>
                                        <div class="product-info">
                                            <img src="assets/img/<?= $val['image'] ?>" class="product-thumb" onerror="this.src='https://via.placeholder.com/70'">
                                            <div>
                                                <span class="product-name"><?= $val['name'] ?></span>
                                                <!-- <small class="text-muted">SKU: #<?= $val['id'] ?></small> -->
                                            </div>
                                        </div>
                                    </td>
                                    <td class="price-text">Rp <?= number_format($val['price'], 0, ',', '.') ?></td>
                                    <td>
                                        <span class="qty-badge"><?= $val['qty'] ?></span>
                                    </td>
                                    <td class="text-end total-text">
                                        Rp <?= number_format($subtotal, 0, ',', '.') ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="cart.php?del=<?= $key ?>" class="btn-delete" title="Hapus Item">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Tombol Kosongkan -->
                    <div class="p-3 bg-light text-end">
                        <a href="cart.php?clear=true" class="text-danger fw-bold text-decoration-none small" onclick="return confirm('Yakin ingin mengosongkan keranjang?')">
                            <i class="fas fa-times-circle me-1"></i> Kosongkan Keranjang
                        </a>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: SUMMARY -->
            <div class="col-lg-4">
                <div class="card-modern card-summary">
                    <h5 class="fw-bold mb-4">Ringkasan Belanja</h5>
                    
                    <div class="summary-row">
                        <span>Total Harga (<?= count($_SESSION['cart']) ?> barang)</span>
                        <span class="fw-bold text-dark">Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Ongkos Kirim</span>
                        <span class="text-success fw-bold">Gratis</span>
                    </div>
                    <div class="summary-row">
                        <span>Diskon</span>
                        <span class="text-dark fw-bold">Rp 0</span>
                    </div>
                    
                    <div class="summary-total">
                        <span>Total Tagihan</span>
                        <span>Rp <?= number_format($grand_total, 0, ',', '.') ?></span>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="checkout.php" class="btn btn-checkout">
                            Checkout Sekarang <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-primary w-100 mt-4 py-3 rounded-3 fw-bold">
                            Login untuk Checkout
                        </a>
                    <?php endif; ?>

                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1 text-success"></i> Transaksi Aman & Terenkripsi
                        </small>
                    </div>
                </div>
            </div>
        </div>
        
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>