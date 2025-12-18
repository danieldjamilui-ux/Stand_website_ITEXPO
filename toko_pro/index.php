<?php
session_start();
include 'db.php';

// Logika Search & Filter
$where = "WHERE 1=1";
$title_filter = "Rekomendasi Pilihan";

if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where .= " AND name LIKE '%$search%'";
    $title_filter = "Hasil Pencarian: " . htmlspecialchars($search);
}

if (isset($_GET['cat'])) {
    $cat = mysqli_real_escape_string($conn, $_GET['cat']);
    $where .= " AND category = '$cat'";
    $title_filter = "Kategori: " . htmlspecialchars($cat);
}

$query = mysqli_query($conn, "SELECT * FROM products $where ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Commerce - Belanja Modern</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --accent-color: #ff9f43;
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
            background: -webkit-linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* --- HERO SECTION --- */
        .hero-banner {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 20px;
            padding: 60px 40px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(118, 75, 162, 0.3);
            margin-bottom: 40px;
        }
        .hero-content { position: relative; z-index: 2; }
        .hero-title { font-size: 3rem; font-weight: 800; line-height: 1.2; }
        .hero-subtitle { font-size: 1.1rem; opacity: 0.9; margin-bottom: 25px; }
        
        .circle-bg { position: absolute; background: rgba(255,255,255,0.1); border-radius: 50%; }
        .c1 { width: 200px; height: 200px; top: -50px; right: -50px; }
        .c2 { width: 100px; height: 100px; bottom: 20px; right: 100px; }

        /* Animasi Gambar */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        .floating-img { animation: float 4s ease-in-out infinite; }

        /* --- SEARCH BAR --- */
        .search-container { position: relative; width: 100%; max-width: 500px; }
        .search-input {
            border-radius: 50px; padding: 12px 25px; padding-right: 55px;
            border: 2px solid #e5e7eb; background: #fff; transition: 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        }
        .search-input:focus { border-color: var(--primary-color); box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1); }
        .search-btn {
            position: absolute; right: 5px; top: 5px; width: 40px; height: 40px;
            border-radius: 50%; background: var(--secondary-color); color: white;
            border: none; display: flex; align-items: center; justify-content: center; transition: 0.3s;
        }
        .search-btn:hover { background: var(--primary-color); transform: scale(1.05); }

        /* --- KATEGORI CHIPS --- */
        .cat-chip {
            background: white; border: 1px solid #e5e7eb; padding: 10px 25px;
            border-radius: 30px; color: #555; font-weight: 600; text-decoration: none;
            transition: 0.3s; display: inline-flex; align-items: center; gap: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .cat-chip:hover, .cat-chip.active {
            background: var(--secondary-color); color: white; border-color: var(--secondary-color);
            transform: translateY(-3px); box-shadow: 0 10px 20px rgba(118, 75, 162, 0.2);
        }

        /* --- PRODUCT CARD --- */
        .card-product {
            border: none; border-radius: 20px; background: white;
            transition: all 0.3s ease; position: relative; overflow: hidden;
            height: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex; flex-direction: column; 
        }
        .card-product:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        
        .img-wrap {
            height: 250px; width: 100%; padding: 0; position: relative;
            overflow: hidden; background-color: #fff;
        }
        .product-img {
            width: 100%; height: 100%; object-fit: cover; object-position: center; transition: 0.5s;
        }
        .card-product:hover .product-img { transform: scale(1.1); }
        
        .badge-new {
            position: absolute; top: 15px; left: 15px;
            background: var(--accent-color); color: white;
            padding: 5px 12px; border-radius: 20px;
            font-size: 0.75rem; font-weight: 700;
            box-shadow: 0 4px 10px rgba(255, 159, 67, 0.4); z-index: 10;
        }

        .card-info { padding: 20px; display: flex; flex-direction: column; flex-grow: 1; }
        .cat-name { font-size: 0.75rem; text-transform: uppercase; color: #9ca3af; letter-spacing: 1px; font-weight: 700; }
        .prod-name { font-size: 1.1rem; font-weight: 700; color: #1f2937; margin: 5px 0; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .price { font-size: 1.2rem; font-weight: 800; color: var(--secondary-color); margin-bottom: 15px; }
        
        /* Tombol Beli */
        .btn-cart {
            width: 100%; background: linear-gradient(to right, #667eea, #764ba2);
            border: none; color: white; padding: 10px; border-radius: 12px;
            font-weight: 600; transition: 0.3s;
        }
        .btn-cart:hover { box-shadow: 0 10px 20px rgba(118, 75, 162, 0.3); transform: translateY(-2px); }

        /* --- FOOTER --- */
        footer { background: #1f2937; color: #d1d5db; padding-top: 60px; padding-bottom: 30px; margin-top: 80px; }
        footer h5 { color: white; font-weight: 700; margin-bottom: 20px; }
        footer a { color: #9ca3af; text-decoration: none; transition: 0.3s; }
        footer a:hover { color: var(--primary-color); padding-left: 5px; }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-glass sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-shopping-bag me-2"></i>SHOP COMMERCE
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navContent">
            <form class="mx-auto my-3 my-lg-0 search-container" method="GET" action="index.php">
                <input type="text" name="search" class="form-control search-input" placeholder="Cari barang impianmu..." value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
            </form>

            <ul class="navbar-nav ms-auto align-items-center gap-3">
                <li class="nav-item">
                    <a href="cart.php" class="btn btn-light rounded-circle position-relative shadow-sm" style="width: 45px; height: 45px; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-shopping-cart text-secondary"></i>
                        <?php if(isset($_SESSION['cart']) && count($_SESSION['cart']) > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">
                                <?= count($_SESSION['cart']) ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle fw-bold text-dark d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                           <!-- Menggunakan '??' untuk memberikan nama cadangan jika session kosong -->
<img src="https://ui-avatars.com/api/?name=<?= $_SESSION['name'] ?? 'User' ?>&background=764ba2&color=fff&bold=true" class="rounded-circle me-2 shadow-sm" width="35">
                           <!-- Gunakan tanda ?? untuk memberi nilai cadangan -->
<span class="d-none d-lg-block"><?= $_SESSION['name'] ?? 'User' ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 mt-3 p-2">
                          <li>
        <a class="dropdown-item rounded-3 py-2" href="profil.php">
            <i class="fas fa-user me-2 text-muted"></i> Profil Saya
        </a>
    </li>

    <!-- 2. RIWAYAT PESANAN -->
    <li>
        <a class="dropdown-item rounded-3 py-2" href="riwayat.php">
            <i class="fas fa-history me-2 text-muted"></i> Riwayat Pesanan
        </a>
    </li>
    
    <!-- 3. MENU ADMIN (Hanya muncul jika login sebagai admin) -->
    <?php if($_SESSION['role'] == 'admin'): ?>
        <li>
            <a class="dropdown-item rounded-3 py-2" href="admin/index.php">
                <i class="fas fa-columns me-2 text-muted"></i> Dashboard Admin
            </a>
        </li>
    <?php endif; ?>
    
    <li><hr class="dropdown-divider"></li>
    
    <!-- 4. LOGOUT -->
    <li>
        <a class="dropdown-item rounded-3 py-2 text-danger" href="logout.php" onclick="confirmLogoutUser(event)">
            <i class="fas fa-sign-out-alt me-2"></i> Logout
        </a>
    </li>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="login.php" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background: var(--secondary-color); border:none;">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    
    <!-- Hero Banner -->
    <?php if(!isset($_GET['search']) && !isset($_GET['cat'])): ?>
    <div class="hero-banner">
        <div class="circle-bg c1"></div>
        <div class="circle-bg c2"></div>
        <div class="row align-items-center hero-content">
            <div class="col-md-7">
                <span class="badge bg-warning text-dark mb-3 px-3 py-2 rounded-pill"><i class="fas fa-star me-1"></i> PROMO SPESIAL</span>
                <h1 class="hero-title mb-3">Upgrade Gaya Hidup <br>Dengan Produk Terbaik</h1>
                <p class="hero-subtitle">Diskon hingga 50% untuk produk elektronik dan fashion pilihan minggu ini.</p>
                <a href="#produk-area" class="btn btn-light text-primary rounded-pill px-5 py-3 fw-bold shadow-lg">Belanja Sekarang <i class="fas fa-arrow-right ms-2"></i></a>
            </div>
            <div class="col-md-5 text-center d-none d-md-block">
                <!-- GAMBAR 3D KERANJANG -->
                <img src="assets/img/1.png" 
                     width="65%" class="floating-img" alt="Keranjang Belanja">
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filter -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3" id="produk-area">
        <h3 class="fw-bold mb-0 text-dark border-start border-4 border-primary ps-3"><?= $title_filter ?></h3>
        
        <div class="d-flex flex-wrap gap-2">
            <a href="index.php" class="cat-chip <?= !isset($_GET['cat']) ? 'active' : '' ?>">
                <i class="fas fa-border-all"></i> Semua
            </a>
            <a href="index.php?cat=Elektronik" class="cat-chip <?= (isset($_GET['cat']) && $_GET['cat']=='Elektronik') ? 'active' : '' ?>">
                <i class="fas fa-laptop"></i> Elektronik
            </a>
            <a href="index.php?cat=Fashion" class="cat-chip <?= (isset($_GET['cat']) && $_GET['cat']=='Fashion') ? 'active' : '' ?>">
                <i class="fas fa-tshirt"></i> Fashion
            </a>
            <a href="index.php?cat=Hobi" class="cat-chip <?= (isset($_GET['cat']) && $_GET['cat']=='Hobi') ? 'active' : '' ?>">
                <i class="fas fa-gamepad"></i> Hobi
            </a>
        </div>
    </div>

    <!-- Grid Produk -->
    <div class="row g-4">
        <?php if(mysqli_num_rows($query) > 0): ?>
            
            <!-- LOOPING UTAMA -->
            <?php while ($row = mysqli_fetch_assoc($query)): ?>
            
            <div class="col-6 col-md-3">
                <div class="card-product">
                    <div class="img-wrap">
                        <span class="badge-new">BARU</span>
                        <a href="detail.php?id=<?= $row['id'] ?>">
                            <img src="assets/img/<?= $row['image'] ?>" class="product-img" alt="<?= $row['name'] ?>" onerror="this.src='https://via.placeholder.com/300?text=No+Image'">
                        </a>
                    </div>
                    
                    <div class="card-info">
                        <div class="cat-name"><?= $row['category'] ?></div>
                        <a href="detail.php?id=<?= $row['id'] ?>" class="text-decoration-none text-dark">
                            <h5 class="prod-name" title="<?= $row['name'] ?>"><?= $row['name'] ?></h5>
                        </a>
                        <div class="price">Rp <?= number_format($row['price'], 0, ',', '.') ?></div>
                        
                        <!-- LOGIKA KOMBUNGAN: TOMBOL BELI / ADMIN MODE / STOK -->
                        <div class="mt-auto">
                            <?php if ($row['stock'] <= 0): ?>
                                <!-- JIKA STOK HABIS -->
                                <button type="button" class="btn btn-danger w-100 rounded-3 fw-bold mt-3" disabled style="opacity: 0.6; cursor: not-allowed;">
                                    <i class="fas fa-times-circle me-2"></i> Stok Habis
                                </button>

                            <?php elseif(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                                <!-- JIKA ADMIN (View Only) -->
                                <button type="button" class="btn btn-secondary w-100 rounded-3 fw-bold mt-3" disabled style="cursor: not-allowed; opacity: 0.6;">
                                    <i class="fas fa-user-shield me-2"></i> Admin Mode
                                </button>

                            <?php else: ?>
                                <!-- JIKA USER & STOK ADA (Bisa Beli) -->
                                <form action="cart.php" method="POST">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="name" value="<?= $row['name'] ?>">
                                    <input type="hidden" name="price" value="<?= $row['price'] ?>">
                                    <input type="hidden" name="image" value="<?= $row['image'] ?>">
                                    <button type="submit" name="add_to_cart" class="btn-cart">
                                        <i class="fas fa-shopping-cart me-2"></i> + Keranjang
                                    </button>
                                </form>
                                <div class="text-end mt-2">
                                    <small class="text-muted">Sisa: <span class="fw-bold text-success"><?= $row['stock'] ?></span></small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- AKHIR LOGIKA -->

                    </div>
                </div>
            </div> 
            
            <?php endwhile; ?>
            <!-- AKHIR LOOPING -->

        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h4 class="fw-bold text-secondary">Produk tidak ditemukan</h4>
                <p class="text-muted">Coba kata kunci lain atau kategori berbeda.</p>
                <a href="index.php" class="btn btn-outline-primary rounded-pill px-4 mt-2">Lihat Semua</a>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-5 mb-4">
                <h5 class="text-white"><i class="fas fa-shopping-bag me-2"></i>SHOP COMMERCE</h5>
                <p class="small text-secondary">Destinasi belanja online terlengkap dan terpercaya. Temukan produk impianmu dengan harga terbaik.</p>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-twitter fa-lg"></i></a>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <h5>Layanan</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#">Cara Pemesanan</a></li>
                    <li class="mb-2"><a href="#">Informasi Pengiriman</a></li>
                    <li class="mb-2"><a href="#">Pengembalian Barang</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5>Tentang Kami</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#">Profil Perusahaan</a></li>
                    <li class="mb-2"><a href="#">Syarat & Ketentuan</a></li>
                    <li class="mb-2"><a href="#">Hubungi Kami</a></li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary mt-4">
        <div class="text-center small text-secondary">
            &copy; 2025 Shop Commerce Official. All rights reserved.
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmLogoutUser(event) {
        event.preventDefault();
        Swal.fire({
            title: 'Keluar?', text: "Sampai jumpa lagi!", icon: 'question',
            showCancelButton: true, confirmButtonColor: '#764ba2', cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Logout'
        }).then((result) => { if (result.isConfirmed) window.location.href = 'logout.php'; });
    }
</script>

</body>
</html>