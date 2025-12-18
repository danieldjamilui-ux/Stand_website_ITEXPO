<?php
session_start();
include '../db.php';

// Cek Akses Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Update Status
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status_baru = $_POST['status'];
    mysqli_query($conn, "UPDATE orders SET status='$status_baru' WHERE id='$order_id'");
    echo "<script>window.location='pesanan.php';</script>";
}

// Hapus Pesanan
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM order_details WHERE order_id='$id_hapus'");
    mysqli_query($conn, "DELETE FROM orders WHERE id='$id_hapus'");
    echo "<script>window.location='pesanan.php';</script>";
}

// Search Logic
$where_clause = "";
if (isset($_GET['q'])) {
    $keyword = mysqli_real_escape_string($conn, $_GET['q']);
    $where_clause = "AND (orders.id LIKE '%$keyword%' OR users.name LIKE '%$keyword%')";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Pesanan - Admin Shop</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; overflow-x: hidden; }

        /* Sidebar Styling */
        .sidebar { min-height: 100vh; background: #2c3e50; color: #fff; position: fixed; width: 250px; padding-top: 20px; z-index: 1000; left: 0; transition: 0.3s; }
        .sidebar.hide { left: -250px; }
        .sidebar-brand { text-align: center; font-size: 1.5rem; font-weight: 700; padding-bottom: 20px; border-bottom: 1px solid #34495e; margin-bottom: 20px; color: #ecf0f1; text-decoration: none; display: block; }
        .sidebar-menu a { display: block; color: #b0bec5; padding: 15px 25px; text-decoration: none; transition: 0.3s; font-weight: 500; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: #34495e; color: #fff; border-left: 4px solid #3498db; }
        
        /* Main Content */
        .main-content { margin-left: 250px; padding: 30px; transition: 0.3s; }
        .main-content.expand { margin-left: 0; }
        
        /* Card & Table Modern */
        .card-modern { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); background: white; overflow: hidden; }
        .table-modern thead th { background-color: #f8fafc; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; border-bottom: 2px solid #e2e8f0; padding: 15px; }
        .table-modern td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .table-modern tr:hover { background-color: #fcfcfc; }
        
        /* Badges */
        .badge-soft { padding: 6px 12px; border-radius: 30px; font-weight: 600; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 5px; }
        .badge-pending { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .badge-proses { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
        .badge-kirim { background: #f5f3ff; color: #7c3aed; border: 1px solid #ede9fe; }
        .badge-selesai { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
        .badge-batal { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }

        /* Search Box */
        .search-box { position: relative; max-width: 300px; }
        .search-box input { border-radius: 50px; padding-left: 40px; height: 40px; border: 1px solid #e2e8f0; }
        .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
        
        /* Action Buttons */
        .btn-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; transition: 0.2s; border: none; }
        .btn-save { background: #eff6ff; color: #3b82f6; } .btn-save:hover { background: #3b82f6; color: white; }
        .btn-delete { background: #fef2f2; color: #ef4444; } .btn-delete:hover { background: #ef4444; color: white; }
        
        /* Toggle Menu */
        #menu-toggle { cursor: pointer; font-size: 1.4rem; color: #475569; transition: 0.3s; }
        #menu-toggle:hover { color: #2c3e50; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="index.php" class="sidebar-brand"><i class="fas fa-rocket me-2"></i> ADMIN SHOP</a>
    <div class="sidebar-menu">
        <a href="index.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="produk.php"><i class="fas fa-box me-2"></i> Kelola Produk</a>
        <a href="pesanan.php" class="active"><i class="fas fa-shopping-cart me-2"></i> Kelola Pesanan</a>
        <a href="pelanggan.php"><i class="fas fa-users me-2"></i> Pelanggan</a>
        <a href="laporan.php" class="active"><i class="fas fa-chart-line"></i> Laporan</a>
        <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i> Lihat Website</a>
        <a href="#" class="text-danger mt-4" onclick="konfirmasiLogout(event)"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>
</div>

<div class="main-content" id="content">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-bars me-3" id="menu-toggle" title="Toggle Sidebar"></i>
            <div>
                <h3 class="fw-bold mb-0 text-dark">Manajemen Pesanan</h3>
                <p class="text-muted mb-0 small">Pantau dan kelola semua pesanan pelanggan.</p>
            </div>
        </div>
        
        <!-- Search Bar -->
        <div class="search-box">
            <i class="fas fa-search"></i>
            <form method="GET">
                <input type="text" name="q" class="form-control" placeholder="Cari ID / Nama..." value="<?= isset($_GET['q']) ? $_GET['q'] : '' ?>">
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card-modern">
        <div class="table-responsive">
            <table class="table table-modern mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">ID Order</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Detail</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT orders.*, users.name as user_name, users.email 
                              FROM orders 
                              JOIN users ON orders.user_id = users.id 
                              WHERE 1=1 $where_clause 
                              ORDER BY orders.id DESC";
                    $result = mysqli_query($conn, $query);

                    if(mysqli_num_rows($result) > 0):
                        while($row = mysqli_fetch_assoc($result)):
                            // Tentukan Badge Status
                            $status = $row['status'];
                            $badgeClass = 'badge-pending'; $icon = 'fa-clock';
                            if($status == 'Diproses') { $badgeClass = 'badge-proses'; $icon='fa-cog fa-spin'; }
                            if($status == 'Dikirim') { $badgeClass = 'badge-kirim'; $icon='fa-truck'; }
                            if($status == 'Selesai') { $badgeClass = 'badge-selesai'; $icon='fa-check-circle'; }
                            if($status == 'Dibatalkan') { $badgeClass = 'badge-batal'; $icon='fa-times-circle'; }
                    ?>
                    <tr>
                        <td class="ps-4 text-primary fw-bold">#<?= $row['id'] ?></td>
                        <td>
                            <div class="fw-bold text-dark"><?= $row['user_name'] ?></div>
                            <small class="text-muted"><?= $row['email'] ?></small>
                        </td>
                        <td class="text-muted small">
                            <?= date('d M Y', strtotime($row['order_date'])) ?><br>
                            <?= date('H:i', strtotime($row['order_date'])) ?> WIB
                        </td>
                        <td class="fw-bold">Rp <?= number_format($row['total_price']) ?></td>
                        <td>
                            <span class="badge-soft <?= $badgeClass ?>">
                                <i class="fas <?= $icon ?>"></i> <?= $status ?>
                            </span>
                        </td>
                        
                        <!-- KOLOM DETAIL (MODAL POPUP) -->
                        <td>
                            <button type="button" class="btn btn-sm btn-light border text-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $row['id'] ?>">
                                <i class="fas fa-eye me-1"></i> Lihat
                            </button>

                            <!-- MODAL -->
                            <div class="modal fade" id="modalDetail<?= $row['id'] ?>" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow-lg rounded-4">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold">Detail Order #<?= $row['id'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            
                                            <!-- INFO BUKTI TRANSFER -->
                                            <?php if(!empty($row['payment_proof'])): ?>
                                                <div class="alert alert-success d-flex align-items-center mb-3 p-2 small">
                                                    <i class="fas fa-receipt me-2 fs-5"></i> 
                                                    <div>
                                                        <strong>Bukti Transfer Diterima</strong><br>
                                                        <a href="../assets/img/pembayaran/<?= $row['payment_proof'] ?>" target="_blank" class="fw-bold text-decoration-none">Lihat Foto</a>
                                                    </div>
                                                </div>
                                            <?php elseif($row['payment_method'] != 'COD'): ?>
                                                <div class="alert alert-warning small mb-3"><i class="fas fa-exclamation-circle me-1"></i> Belum ada bukti transfer.</div>
                                            <?php endif; ?>

                                            <!-- INFO PENGIRIMAN -->
                                            <div class="bg-light p-3 rounded mb-3 border">
                                                <small class="text-muted text-uppercase fw-bold">Penerima:</small>
                                                <div class="fw-bold text-dark"><?= $row['recipient_name'] ?></div>
                                                <div class="small text-muted"><?= $row['address'] ?>, <?= $row['city'] ?></div>
                                                <div class="small text-muted mt-1">Pembayaran: <strong><?= $row['payment_method'] ?></strong></div>
                                            </div>

                                            <h6 class="fw-bold small text-uppercase text-muted mb-2">Item Produk:</h6>
                                            <ul class="list-group list-group-flush mb-3">
                                                <?php
                                                $oid = $row['id'];
                                                $q_d = mysqli_query($conn, "SELECT * FROM order_details WHERE order_id='$oid'");
                                                while($d = mysqli_fetch_assoc($q_d)):
                                                ?>
                                                <li class="list-group-item d-flex justify-content-between px-0 py-2">
                                                    <div>
                                                        <span class="fw-bold text-dark small"><?= $d['product_name'] ?></span>
                                                        <div class="text-muted small">x<?= $d['qty'] ?></div>
                                                    </div>
                                                    <span class="fw-bold text-dark small">Rp <?= number_format($d['price'] * $d['qty']) ?></span>
                                                </li>
                                                <?php endwhile; ?>
                                            </ul>
                                            
                                            <div class="d-flex justify-content-between bg-light p-3 rounded">
                                                <span class="fw-bold text-dark">Total Tagihan</span>
                                                <span class="fw-bold text-primary fs-5">Rp <?= number_format($row['total_price']) ?></span>
                                            </div>

                                            <!-- TOMBOL CETAK INVOICE -->
                                            <div class="d-grid mt-3">
                                                <a href="../invoice.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-outline-dark fw-bold">
                                                    <i class="fas fa-print me-2"></i> Cetak Invoice
                                                </a>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- END MODAL -->
                        </td>

                        <td class="text-end pe-4">
                            <form method="POST" class="d-inline-flex gap-2 align-items-center">
                                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                <select name="status" class="form-select form-select-sm" style="width: 120px; border-radius: 8px;">
                                    <option value="Pending" <?= $status=='Pending'?'selected':'' ?>>Pending</option>
                                    <option value="Diproses" <?= $status=='Diproses'?'selected':'' ?>>Diproses</option>
                                    <option value="Dikirim" <?= $status=='Dikirim'?'selected':'' ?>>Dikirim</option>
                                    <option value="Selesai" <?= $status=='Selesai'?'selected':'' ?>>Selesai</option>
                                    <option value="Dibatalkan" <?= $status=='Dibatalkan'?'selected':'' ?>>Batal</option>
                                </select>
                                <button type="submit" name="update_status" class="btn-icon btn-save" title="Simpan Status"><i class="fas fa-check"></i></button>
                                <a href="#" onclick="confirmDelete(<?= $row['id'] ?>)" class="btn-icon btn-delete" title="Hapus Permanen"><i class="fas fa-trash-alt"></i></a>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="7" class="text-center py-5 text-muted">Belum ada data pesanan.</td></tr>
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

    // Konfirmasi Hapus Pesanan
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Pesanan?',
            text: "Data pesanan ini akan hilang permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'pesanan.php?hapus=' + id;
            }
        });
    }
</script>

</body>
</html>