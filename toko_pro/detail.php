<?php
session_start();
include 'db.php';

// 1. Cek ID Produk di URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// 2. Logika Simpan Ulasan (Jika tombol diklik)
if (isset($_POST['kirim_ulasan'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Silakan login dulu.'); window.location='login.php';</script>";
        exit;
    }
    
    $uid = $_SESSION['user_id'];
    $rating = $_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);
    $pid = $_POST['product_id'];

    mysqli_query($conn, "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES ('$pid', '$uid', '$rating', '$comment')");
    echo "<script>window.location='detail.php?id=$pid';</script>";
}

// 3. Ambil Data Produk
$query = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
$p = mysqli_fetch_assoc($query);

if (!$p) {
    echo "<script>alert('Produk tidak ditemukan.'); window.location='index.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $p['name'] ?> - Shop Commerce</title>
    
    <!-- Bootstrap & Font -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root { --primary: #667eea; --secondary: #764ba2; --accent: #ff9f43; }
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; }

        /* Navbar */
        .navbar-glass { background: rgba(255,255,255,0.95); box-shadow: 0 4px 30px rgba(0,0,0,0.05); }
        .navbar-brand { font-weight: 800; font-size: 1.5rem; background: -webkit-linear-gradient(45deg, var(--primary), var(--secondary)); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }

        /* Product Section */
        .img-container { background: white; border-radius: 20px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center; }
        .main-img { max-width: 100%; height: auto; max-height: 400px; object-fit: contain; }
        
        .cat-badge { background: #e0f2fe; color: #0284c7; padding: 5px 15px; border-radius: 50px; font-weight: 600; font-size: 0.9rem; }
        .price { font-size: 2rem; font-weight: 800; color: var(--secondary); margin: 15px 0; }
        
        .btn-buy { background: linear-gradient(to right, var(--primary), var(--secondary)); border: none; color: white; padding: 12px; border-radius: 12px; width: 100%; font-weight: 600; transition: 0.3s; }
        .btn-buy:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(118, 75, 162, 0.3); color: white; }

        /* Reviews */
        .review-card { background: white; border: none; border-radius: 15px; padding: 20px; margin-bottom: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.03); }
        .star-active { color: #ffc107; }
        .star-inactive { color: #e4e5e9; }

        /* Related Products */
        .card-related { border: none; border-radius: 15px; overflow: hidden; transition: 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .card-related:hover { transform: translateY(-5px); }
        .related-img { height: 150px; object-fit: contain; width: 100%; padding: 10px; background: white; }

        footer { background: #1f2937; color: #d1d5db; padding: 50px 0; margin-top: 80px; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-glass sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-shopping-bag me-2"></i>SHOP COMMERCE</a>
        <div class="ms-auto">
            <a href="cart.php" class="btn btn-light rounded-circle position-relative shadow-sm" style="width: 45px; height: 45px; display:flex; align-items:center; justify-content:center;">
                <i class="fas fa-shopping-cart text-secondary"></i>
                <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white"><?= count($_SESSION['cart']) ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</nav>

<div class="container py-5">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item text-dark fw-bold" aria-current="page"><?= $p['name'] ?></li>
        </ol>
    </nav>

    <div class="row g-5">
        <!-- GAMBAR -->
        <div class="col-md-5">
            <div class="img-container">
                <img src="assets/img/<?= $p['image'] ?>" class="main-img" onerror="this.src='https://via.placeholder.com/500'">
            </div>
        </div>

        <!-- INFO -->
        <div class="col-md-7">
            <span class="cat-badge"><?= $p['category'] ?></span>
            <h1 class="fw-bold mt-3"><?= $p['name'] ?></h1>
            <div class="price">Rp <?= number_format($p['price'], 0, ',', '.') ?></div>

            <div class="d-flex align-items-center mb-4">
                <i class="fas fa-box me-2 text-muted"></i> 
                <span class="<?= $p['stock'] > 0 ? 'text-success fw-bold' : 'text-danger fw-bold' ?>">
                    Stok: <?= $p['stock'] ?> Unit
                </span>
            </div>

            <p class="text-muted" style="line-height: 1.8;"><?= nl2br($p['description']) ?></p>

            <hr class="my-4">

            <!-- TOMBOL BELI -->
            <?php if ($p['stock'] <= 0): ?>
                <button class="btn btn-danger w-100 py-3 fw-bold" disabled>STOK HABIS</button>
            <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <button class="btn btn-secondary w-100 py-3 fw-bold" disabled>MODE ADMIN (VIEW ONLY)</button>
            <?php else: ?>
                <form action="cart.php" method="POST">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="name" value="<?= $p['name'] ?>">
                    <input type="hidden" name="price" value="<?= $p['price'] ?>">
                    <input type="hidden" name="image" value="<?= $p['image'] ?>">
                    
                    <div class="row">
                        <div class="col-3">
                            <input type="number" name="qty" value="1" min="1" max="<?= $p['stock'] ?>" class="form-control text-center py-3 fw-bold border-2">
                        </div>
                        <div class="col-9">
                            <button type="submit" name="add_to_cart" class="btn-buy py-3">
                                <i class="fas fa-cart-plus me-2"></i> Masukkan Keranjang
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- BAGIAN ULASAN & REVIEW -->
    <div class="row mt-5 pt-4 border-top">
        <div class="col-md-7">
            <h4 class="fw-bold mb-4">Ulasan Pembeli</h4>
            
            <?php
            $q_rev = mysqli_query($conn, "SELECT reviews.*, users.name FROM reviews JOIN users ON reviews.user_id = users.id WHERE product_id='$id' ORDER BY id DESC");
            if (mysqli_num_rows($q_rev) > 0) {
                while ($rev = mysqli_fetch_assoc($q_rev)) {
                    $stars = str_repeat('<i class="fas fa-star star-active"></i>', $rev['rating']);
                    $empty = str_repeat('<i class="fas fa-star star-inactive"></i>', 5 - $rev['rating']);
                    echo '
                    <div class="review-card">
                        <div class="d-flex justify-content-between">
                            <div class="fw-bold">'.$rev['name'].'</div>
                            <small class="text-muted">'.date('d M Y', strtotime($rev['created_at'])).'</small>
                        </div>
                        <div class="mb-2">'.$stars.$empty.'</div>
                        <p class="mb-0 text-muted">'.$rev['comment'].'</p>
                    </div>';
                }
            } else {
                echo '<div class="alert alert-light border text-center">Belum ada ulasan untuk produk ini.</div>';
            }
            ?>
        </div>

        <!-- FORM TULIS ULASAN (Hanya jika User Pernah Beli & Selesai) -->
        <div class="col-md-5">
            <?php
            if (isset($_SESSION['user_id'])) {
                $uid = $_SESSION['user_id'];
                // Cek apakah user pernah beli dan status Selesai
                $cek = mysqli_query($conn, "SELECT * FROM order_details JOIN orders ON order_details.order_id = orders.id WHERE orders.user_id='$uid' AND order_details.product_id='$id' AND orders.status='Selesai'");
                
                if (mysqli_num_rows($cek) > 0) {
            ?>
                <div class="card p-4 border-0 shadow-sm">
                    <h5 class="fw-bold mb-3">Tulis Ulasan</h5>
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?= $id ?>">
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <select name="rating" class="form-select">
                                <option value="5">⭐⭐⭐⭐⭐ Sangat Puas</option>
                                <option value="4">⭐⭐⭐⭐ Puas</option>
                                <option value="3">⭐⭐⭐ Cukup</option>
                                <option value="2">⭐⭐ Kurang</option>
                                <option value="1">⭐ Kecewa</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Komentar</label>
                            <textarea name="comment" class="form-control" rows="3" required placeholder="Bagaimana kualitas produk ini?"></textarea>
                        </div>
                        <button type="submit" name="kirim_ulasan" class="btn btn-primary w-100">Kirim Ulasan</button>
                    </form>
                </div>
            <?php 
                } 
            } 
            ?>
        </div>
    </div>

    <!-- PRODUK TERKAIT -->
    <div class="mt-5">
        <h4 class="fw-bold mb-4">Produk Lainnya</h4>
        <div class="row g-4">
            <?php
            $cat = $p['category'];
            $related = mysqli_query($conn, "SELECT * FROM products WHERE category='$cat' AND id != '$id' LIMIT 4");
            if(mysqli_num_rows($related) > 0) {
                while($rel = mysqli_fetch_assoc($related)) {
            ?>
            <div class="col-6 col-md-3">
                <div class="card card-related h-100">
                    <img src="assets/img/<?= $rel['image'] ?>" class="related-img" onerror="this.src='https://via.placeholder.com/150'">
                    <div class="card-body">
                        <h6 class="card-title text-truncate"><?= $rel['name'] ?></h6>
                        <div class="text-primary fw-bold">Rp <?= number_format($rel['price']) ?></div>
                        <a href="detail.php?id=<?= $rel['id'] ?>" class="btn btn-sm btn-outline-primary w-100 mt-2 rounded-pill">Lihat</a>
                    </div>
                </div>
            </div>
            <?php 
                } 
            } else {
                echo '<div class="col-12 text-muted">Tidak ada produk terkait.</div>';
            } 
            ?>
        </div>
    </div>

</div>

<!-- Footer -->
<footer>
    <div class="container text-center">
        <h5 class="text-white fw-bold mb-3">SHOP COMMERCE</h5>
        <p class="small text-secondary mb-0">&copy; 2025 Shop Commerce Official. All Rights Reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>