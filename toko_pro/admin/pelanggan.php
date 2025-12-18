<?php
session_start();
include '../db.php';

// 1. Cek Akses Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// 2. Logika Hapus Pelanggan
if (isset($_GET['hapus'])) {
    $id_user = $_GET['hapus'];
    // Hapus user dari database
    $delete = mysqli_query($conn, "DELETE FROM users WHERE id='$id_user' AND role='user'");
    if ($delete) {
        echo "<script>alert('Data pelanggan berhasil dihapus.'); window.location='pelanggan.php';</script>";
    }
}

// 3. Logika Pencarian
$where_clause = "WHERE role='user'";
if (isset($_GET['q'])) {
    $keyword = mysqli_real_escape_string($conn, $_GET['q']);
    $where_clause .= " AND (name LIKE '%$keyword%' OR email LIKE '%$keyword%')";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan - Admin Shop</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; overflow-x: hidden; }

        /* Sidebar Styling */
        .sidebar { min-height: 100vh; background: #2c3e50; color: #fff; position: fixed; width: 250px; padding-top: 20px; z-index: 1000; transition: 0.3s; left: 0; }
        .sidebar.hide { left: -250px; }
        .sidebar-brand { text-align: center; font-size: 1.5rem; font-weight: 700; padding-bottom: 20px; border-bottom: 1px solid #34495e; margin-bottom: 20px; color: #ecf0f1; text-decoration: none; display: block; }
        .sidebar-menu a { display: block; color: #b0bec5; padding: 15px 25px; text-decoration: none; transition: 0.3s; font-weight: 500; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: #34495e; color: #fff; border-left: 4px solid #3498db; }
        
        /* Main Content */
        .main-content { margin-left: 250px; padding: 30px; transition: 0.3s; }
        .main-content.expand { margin-left: 0; }

        /* Card Modern */
        .card-modern { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); background: white; overflow: hidden; }
        
        /* Table Styling */
        .table-modern thead th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
            padding: 15px;
        }
        .table-modern td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .table-modern tr:hover { background-color: #f8fafc; }

        /* Avatar Gradient */
        .avatar-gradient {
            width: 45px; height: 45px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: bold; font-size: 1.2rem;
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3);
        }

        /* Stats Card (Pojok Kanan Atas) */
        .stat-badge {
            background: linear-gradient(to right, #06b6d4, #3b82f6);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(6, 182, 212, 0.2);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Search Box */
        .search-box { position: relative; max-width: 300px; }
        .search-box input { border-radius: 50px; padding-left: 40px; border: 1px solid #e2e8f0; height: 40px; }
        .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }

        /* Delete Button */
        .btn-delete { 
            width: 35px; height: 35px; 
            border-radius: 10px; 
            background: #fee2e2; color: #ef4444; 
            border: none; transition: 0.3s;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-delete:hover { background: #ef4444; color: white; transform: translateY(-2px); }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="index.php" class="sidebar-brand"><i class="fas fa-rocket me-2"></i> ADMIN SHOP</a>
    <div class="sidebar-menu">
        <a href="index.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="produk.php"><i class="fas fa-box me-2"></i> Kelola Produk</a>
        <a href="pesanan.php"><i class="fas fa-shopping-cart me-2"></i> Kelola Pesanan</a>
        <a href="pelanggan.php" class="active"><i class="fas fa-users me-2"></i> Pelanggan</a>
        <a href="laporan.php" class="active"><i class="fas fa-chart-line"></i> Laporan</a>
        <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i> Lihat Website</a>
        <a href="#" class="text-danger mt-4" onclick="konfirmasiLogout(event)"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>
</div>

<div class="main-content" id="content">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div class="d-flex align-items-center">
            <i class="fas fa-bars fs-4 me-3 text-muted" id="menu-toggle" style="cursor:pointer"></i>
            <div>
                <h3 class="fw-bold mb-0 text-dark">Data Pelanggan</h3>
                <p class="text-muted mb-0 small">Kelola data pengguna yang terdaftar.</p>
            </div>
        </div>

        <div class="d-flex gap-3">
            <!-- Search Bar -->
            <div class="search-box d-none d-md-block">
                <i class="fas fa-search"></i>
                <form method="GET">
                    <input type="text" name="q" class="form-control" placeholder="Cari nama / email..." value="<?= isset($_GET['q']) ? $_GET['q'] : '' ?>">
                </form>
            </div>

            <!-- Total Badge -->
            <?php 
            $count = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE role='user'"));
            ?>
            <div class="stat-badge">
                <i class="fas fa-users"></i>
                <span><?= $count ?> Registered</span>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card-modern">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">No</th>
                        <th>User Profile</th>
                        <th>Kontak Email</th>
                        <th>Tanggal Daftar</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    $query = mysqli_query($conn, "SELECT * FROM users $where_clause ORDER BY id DESC");
                    
                    if(mysqli_num_rows($query) > 0):
                        while($row = mysqli_fetch_assoc($query)):
                            $inisial = strtoupper(substr($row['name'], 0, 1));
                    ?>
                    <tr>
                        <td class="ps-4 text-muted fw-bold">#<?= $no++ ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-gradient me-3"><?= $inisial ?></div>
                                <div>
                                    <div class="fw-bold text-dark"><?= $row['name'] ?></div>
                                    <small class="text-muted" style="font-size: 0.75rem;">ID User: <?= $row['id'] ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-primary fw-medium"><i class="far fa-envelope me-2"></i><?= $row['email'] ?></span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center text-muted">
                                <i class="far fa-calendar-alt me-2 text-secondary"></i> 
                                <?= date('d M Y', strtotime($row['created_at'])) ?>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <a href="#" onclick="confirmDelete(<?= $row['id'] ?>)" class="btn-delete" title="Hapus User">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">Tidak ada data pelanggan ditemukan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
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

    // Konfirmasi Hapus User
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Pengguna?',
            text: "User ini akan dihapus permanen dari sistem!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'pelanggan.php?hapus=' + id;
            }
        });
    }
</script>

</body>
</html>