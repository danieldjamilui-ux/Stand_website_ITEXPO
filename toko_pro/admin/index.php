<?php
session_start();
include '../db.php';

// Cek Akses Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// --- LOGIKA STATISTIK ---
$q_revenue = mysqli_query($conn, "SELECT SUM(total_price) as total FROM orders WHERE status='Selesai'");
$d_revenue = mysqli_fetch_assoc($q_revenue);
$total_revenue = $d_revenue['total'] ? $d_revenue['total'] : 0;

$q_pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status='Pending'");
$d_pending = mysqli_fetch_assoc($q_pending);
$count_pending = $d_pending['total'];

$q_product = mysqli_query($conn, "SELECT COUNT(*) as total FROM products");
$d_product = mysqli_fetch_assoc($q_product);
$count_product = $d_product['total'];

$q_user = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='user'");
$d_user = mysqli_fetch_assoc($q_user);
$count_user = $d_user['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SHOP COMMERCE</title>
    
    <!-- Bootstrap 5 -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; overflow-x: hidden; }

        /* --- SIDEBAR STYLE --- */
        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
            color: #fff;
            position: fixed;
            width: 250px;
            padding-top: 20px;
            z-index: 1000;
            transition: all 0.3s;
            left: 0;
        }
        .sidebar.hide { left: -250px; }
        .sidebar-brand { text-align: center; font-size: 1.5rem; font-weight: 700; padding-bottom: 20px; border-bottom: 1px solid #34495e; margin-bottom: 20px; color: #ecf0f1; text-decoration: none; display: block; }
        .sidebar-menu a { display: block; color: #b0bec5; padding: 15px 25px; text-decoration: none; transition: 0.3s; font-weight: 500; border-left: 4px solid transparent; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: #34495e; color: #fff; border-left: 4px solid #3498db; }
        .sidebar-menu i { width: 25px; }

        /* --- MAIN CONTENT --- */
        .main-content { margin-left: 250px; padding: 30px; transition: all 0.3s; }
        .main-content.expand { margin-left: 0; }

        /* STAT CARDS (Gradient Style) */
        .stat-card {
            border: none;
            border-radius: 15px;
            padding: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            color: white;
            position: relative;
            overflow: hidden;
            height: 100%;
        }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        
        /* Gradient Colors */
        .bg-grad-green { background: linear-gradient(135deg, #2ecc71, #27ae60); }
        .bg-grad-orange { background: linear-gradient(135deg, #f1c40f, #f39c12); }
        .bg-grad-blue { background: linear-gradient(135deg, #3498db, #2980b9); }
        .bg-grad-cyan { background: linear-gradient(135deg, #00c6ff, #0072ff); }

        .stat-icon { font-size: 3rem; opacity: 0.3; position: absolute; right: 20px; bottom: 10px; }
        .stat-number { font-size: 2rem; font-weight: 700; margin-top: 5px; }
        .stat-label { font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; }

        /* MODERN CARD (White Containers) */
        .card-modern {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
            background: white;
            overflow: hidden;
        }
        
        /* TABLE STYLING */
        .table thead th {
            border: none;
            background: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 15px;
        }
        .table td { vertical-align: middle; padding: 15px; border-bottom: 1px solid #f1f5f9; }
        
        /* Soft Badges */
        .badge-soft { padding: 6px 12px; border-radius: 50px; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; }
        .bg-soft-warning { background: #fff7ed; color: #c2410c; }
        .bg-soft-success { background: #f0fdf4; color: #15803d; }
        .bg-soft-primary { background: #eff6ff; color: #1d4ed8; }
        .bg-soft-danger { background: #fef2f2; color: #b91c1c; }

        /* LIST PRODUK */
        .list-group-item { border: none; border-bottom: 1px solid #f1f5f9; padding: 15px; transition: 0.2s; }
        .list-group-item:hover { background-color: #f8fafc; }
        .list-group-item:last-child { border-bottom: none; }

        /* DATE PICKER STYLE */
        .date-container { position: relative; display: inline-block; cursor: pointer; background: #fff; padding: 8px 15px; border-radius: 50px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: 0.3s; }
        .date-container:hover { background: #e9ecef; transform: translateY(-1px); }
        .date-text { font-size: 0.95rem; color: #555; font-weight: 600; display: flex; align-items: center; pointer-events: none; }
        .date-input-hidden { position: absolute; left: 0; top: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10; }
        
        /* Tombol Toggle */
        #menu-toggle { cursor: pointer; font-size: 1.4rem; color: #475569; transition: 0.3s; }
        #menu-toggle:hover { color: #2c3e50; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="index.php" class="sidebar-brand"><i class="fas fa-rocket me-2"></i> ADMIN SHOP</a>
    <div class="sidebar-menu">
        <a href="index.php" class="active"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="produk.php"><i class="fas fa-box me-2"></i> Kelola Produk</a>
        <a href="pesanan.php"><i class="fas fa-shopping-cart me-2"></i> Kelola Pesanan</a>
        <a href="pelanggan.php"><i class="fas fa-users me-2"></i> Pelanggan</a>
        <a href="laporan.php" class="active"><i class="fas fa-chart-line"></i> Laporan</a>
        
        <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i> Lihat Website</a>
        <a href="#" class="text-danger mt-4" onclick="konfirmasiLogout(event)"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content" id="content">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div class="d-flex align-items-center">
            <i class="fas fa-bars me-3" id="menu-toggle" title="Toggle Menu"></i>
            <div>
                <h3 class="fw-bold text-dark mb-0">Dashboard</h3>
                <p class="text-muted mb-0 small">Overview statistik toko Anda hari ini.</p>
            </div>
        </div>
        
        <!-- Tanggal -->
        <div class="date-container" title="Ganti Tanggal">
            <input type="date" id="datePicker" class="date-input-hidden">
            <div class="date-text">
                <i class="far fa-calendar-alt me-2 text-primary"></i> 
                <span id="dateDisplay">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Statistik Cards (Gradient) -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <a href="laporan.php" class="text-decoration-none">
                <div class="stat-card bg-grad-green">
                    <div>
                        <div class="stat-label">Total Pendapatan</div>
                        <div class="stat-number">Rp <?= number_format($total_revenue, 0, ',', '.') ?></div>
                    </div>
                    <i class="fas fa-wallet stat-icon"></i>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="pesanan.php" class="text-decoration-none">
                <div class="stat-card bg-grad-orange">
                    <div>
                        <div class="stat-label">Pesanan Baru</div>
                        <div class="stat-number"><?= $count_pending ?></div>
                        <small class="opacity-75">Perlu diproses</small>
                    </div>
                    <i class="fas fa-bell stat-icon"></i>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="produk.php" class="text-decoration-none">
                <div class="stat-card bg-grad-blue">
                    <div>
                        <div class="stat-label">Total Produk</div>
                        <div class="stat-number"><?= $count_product ?></div>
                    </div>
                    <i class="fas fa-box-open stat-icon"></i>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="pelanggan.php" class="text-decoration-none">
                <div class="stat-card bg-grad-cyan">
                    <div>
                        <div class="stat-label">Pelanggan</div>
                        <div class="stat-number"><?= $count_user ?></div>
                    </div>
                    <i class="fas fa-users stat-icon"></i>
                </div>
            </a>
        </div>
    </div>

    <!-- Konten Bawah (Split View) -->
    <div class="row">
        <!-- Tabel Pesanan Terbaru -->
        <div class="col-lg-8 mb-4">
            <div class="card card-modern h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                    <h5 class="mb-0 fw-bold text-dark">Pesanan Terbaru</h5>
                    <a href="pesanan.php" class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr><th class="ps-4">ID</th><th>Pelanggan</th><th>Total</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_recent = mysqli_query($conn, "SELECT orders.*, users.name FROM orders JOIN users ON orders.user_id = users.id ORDER BY id DESC LIMIT 5");
                            if(mysqli_num_rows($q_recent) > 0):
                                while($r = mysqli_fetch_assoc($q_recent)):
                                    // Badge Soft Colors
                                    $badge = 'bg-soft-primary';
                                    $icon = 'fa-spinner';
                                    if($r['status'] == 'Pending') { $badge = 'bg-soft-warning'; $icon = 'fa-clock'; }
                                    if($r['status'] == 'Selesai') { $badge = 'bg-soft-success'; $icon = 'fa-check'; }
                                    if($r['status'] == 'Dibatalkan') { $badge = 'bg-soft-danger'; $icon = 'fa-times'; }
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary">#<?= $r['id'] ?></td>
                                <td class="fw-bold text-dark"><?= $r['name'] ?></td>
                                <td>Rp <?= number_format($r['total_price']) ?></td>
                                <td><span class="badge-soft <?= $badge ?>"><i class="fas <?= $icon ?>"></i> <?= $r['status'] ?></span></td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="4" class="text-center py-5 text-muted">Belum ada pesanan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- List Produk Terbaru -->
        <div class="col-lg-4 mb-4">
            <div class="card card-modern h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom-0">
                    <h5 class="mb-0 fw-bold text-dark">Produk Baru</h5>
                    <a href="produk.php" class="btn btn-sm btn-light text-primary fw-bold rounded-pill px-3">Kelola</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php
                        $q_prod = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC LIMIT 5");
                        if(mysqli_num_rows($q_prod) > 0):
                            while($p = mysqli_fetch_assoc($q_prod)):
                        ?>
                        <div class="list-group-item d-flex align-items-center">
                            <img src="../assets/img/<?= $p['image'] ?>" class="rounded-3 shadow-sm" width="50" height="50" style="object-fit:cover;" onerror="this.src='https://via.placeholder.com/50'">
                            <div class="ms-3 flex-grow-1">
                                <h6 class="mb-0 fw-bold text-dark"><?= $p['name'] ?></h6>
                                <small class="text-muted">Rp <?= number_format($p['price']) ?></small>
                            </div>
                            <span class="badge bg-light text-secondary rounded-pill"><i class="fas fa-chevron-right"></i></span>
                        </div>
                        <?php endwhile; else: ?>
                            <div class="text-center py-5 text-muted">Belum ada produk.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Toggle Sidebar
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('hide');
        content.classList.toggle('expand');
    });

    // Konfirmasi Logout
    function konfirmasiLogout(event) {
        event.preventDefault();
        Swal.fire({
            title: 'Logout?',
            text: "Anda harus login kembali nanti.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Logout'
        }).then((result) => { if (result.isConfirmed) window.location.href = '../logout.php'; });
    }

    // Date Picker Logic
    const dateInput = document.getElementById('datePicker');
    const dateDisplay = document.getElementById('dateDisplay');
    function formatDate(dateObj) {
        return dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    const today = new Date();
    dateInput.value = today.toISOString().split('T')[0];
    dateDisplay.innerText = formatDate(today);
    dateInput.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        if (!isNaN(selectedDate)) dateDisplay.innerText = formatDate(selectedDate);
    });
    dateInput.addEventListener('click', function() { try { this.showPicker(); } catch (e) {} });
</script>

</body>
</html>