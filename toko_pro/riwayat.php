<?php
session_start();
include 'db.php';

// 1. Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];

// --- LOGIKA UPLOAD BUKTI ---
if (isset($_POST['upload_bukti'])) {
    $order_id = $_POST['order_id'];
    $file_name = $_FILES['bukti']['name'];
    $tmp_name = $_FILES['bukti']['tmp_name'];
    $new_file_name = "BUKTI_" . $order_id . "_" . time() . "_" . $file_name;
    
    $target_dir = "assets/img/pembayaran/";
    if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

    if (move_uploaded_file($tmp_name, $target_dir . $new_file_name)) {
        mysqli_query($conn, "UPDATE orders SET payment_proof = '$new_file_name' WHERE id = '$order_id'");
        echo "<script>alert('Bukti pembayaran berhasil dikirim!'); window.location='riwayat.php';</script>";
    } else {
        echo "<script>alert('Gagal upload bukti.');</script>";
    }
}

// --- LOGIKA BATALKAN ---
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['oid'])) {
    $oid = mysqli_real_escape_string($conn, $_GET['oid']);
    $cancel_query = "UPDATE orders SET status = 'Dibatalkan' WHERE id = '$oid' AND user_id = '$uid' AND status = 'Pending'";
    mysqli_query($conn, $cancel_query);

    if (mysqli_affected_rows($conn) > 0) {
        echo "<script>alert('Pesanan berhasil dibatalkan.'); window.location='riwayat.php';</script>";
    }
}

// Ambil Data Pesanan
$query = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = '$uid' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Toko Pro</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; }
        .navbar { box-shadow: 0 2px 15px rgba(0,0,0,0.03); background: white; }
        .navbar-brand { font-weight: 800; font-size: 1.5rem; color: #764ba2 !important; }
        
        /* Header Page */
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 50px 0 90px; color: white; border-radius: 0 0 30px 30px; margin-bottom: -60px;
        }

        /* Order Card */
        .order-card {
            border: none; border-radius: 20px; background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: all 0.3s ease;
            overflow: hidden; margin-bottom: 25px;
        }
        .order-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }

        .card-header-custom {
            background-color: #fff; padding: 20px 25px; border-bottom: 1px dashed #eee;
            display: flex; justify-content: space-between; align-items: center;
        }
        .card-body-custom { padding: 25px; }

        /* Badges */
        .badge-soft { padding: 8px 15px; border-radius: 30px; font-weight: 600; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 5px; }
        .bg-soft-warning { background: #fff7ed; color: #c2410c; }
        .bg-soft-info { background: #eff6ff; color: #1d4ed8; }
        .bg-soft-primary { background: #f5f3ff; color: #7c3aed; }
        .bg-soft-success { background: #f0fdf4; color: #15803d; }
        .bg-soft-danger { background: #fef2f2; color: #b91c1c; }

        /* Typography & Buttons */
        .order-id { font-weight: 700; color: #333; font-size: 1.1rem; }
        .total-price { font-size: 1.4rem; font-weight: 700; color: #764ba2; }
        
        .btn-action {
            border-radius: 50px; padding: 8px 20px; font-size: 0.9rem; font-weight: 500;
            transition: 0.3s; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; cursor: pointer;
        }
        .btn-detail { background: #f3f4f6; color: #555; border: 1px solid #eee; }
        .btn-detail:hover { background: #e5e7eb; color: #333; }
        .btn-cancel { background: #fee2e2; color: #ef4444; }
        .btn-cancel:hover { background: #fecaca; color: #dc2626; }
        .btn-review { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; text-decoration: none; padding: 5px 10px; font-size: 0.8rem; border-radius: 10px; }
        .btn-review:hover { background: #ffe8a1; color: #856404; }

        .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-shopping-bag me-2"></i>SHOP COMMERCE</a>
        <div class="ms-auto">
            <a href="index.php" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold">
                <i class="fas fa-arrow-left me-2"></i> Lanjut Belanja
            </a>
        </div>
    </div>
</nav>

<div class="page-header text-center">
    <div class="container">
        <h2 class="fw-bold mb-2">Riwayat Pesanan</h2>
        <p class="opacity-75">Kelola pesanan, pembayaran, dan beri ulasan.</p>
    </div>
</div>

<div class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <?php if(mysqli_num_rows($query) > 0): ?>
                
                <?php while ($row = mysqli_fetch_assoc($query)): 
                    // Tentukan warna badge
                    $badgeClass = 'bg-soft-warning'; $icon = 'fa-clock';
                    if($row['status'] == 'Diproses') { $badgeClass = 'bg-soft-info'; $icon = 'fa-cog fa-spin'; }
                    if($row['status'] == 'Dikirim') { $badgeClass = 'bg-soft-primary'; $icon = 'fa-truck'; }
                    if($row['status'] == 'Selesai') { $badgeClass = 'bg-soft-success'; $icon = 'fa-check-circle'; }
                    if($row['status'] == 'Dibatalkan') { $badgeClass = 'bg-soft-danger'; $icon = 'fa-times-circle'; }
                ?>
                
                <div class="order-card fade-in">
                    <!-- Header Kartu -->
                    <div class="card-header-custom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="fas fa-box text-secondary"></i>
                            </div>
                            <div>
                                <div class="order-id">#ORD-<?= $row['id'] ?></div>
                                <div class="text-muted small">
                                    <i class="far fa-calendar-alt me-1"></i> <?= date('d M Y, H:i', strtotime($row['order_date'])) ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <span class="badge-soft <?= $badgeClass ?>">
                                <i class="fas <?= $icon ?>"></i> <?= $row['status'] ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Body Kartu -->
                    <div class="card-body-custom">
                        <div class="row align-items-center">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="total-label">Total Belanja</div>
                                <div class="total-price">Rp <?= number_format($row['total_price'], 0, ',', '.') ?></div>
                                <small class="text-muted">Metode: <span class="fw-bold text-dark"><?= $row['payment_method'] ?></span></small>
                            </div>
                            
                            <div class="col-md-6 text-md-end d-flex gap-2 justify-content-md-end flex-wrap">
                                <!-- Tombol Batal (Hanya Pending) -->
                                <?php if($row['status'] == 'Pending'): ?>
                                    <a href="riwayat.php?action=cancel&oid=<?= $row['id'] ?>" class="btn-action btn-cancel" onclick="return confirm('Batalkan pesanan?')"><i class="fas fa-ban"></i> Batalkan</a>
                                <?php endif; ?>

                                <!-- Indikator Ulasan (Jika Selesai) -->
                                <?php if($row['status'] == 'Selesai'): ?>
                                    <div class="d-none d-md-block text-warning small me-2 align-self-center">
                                        <i class="fas fa-star"></i> Yuk Ulas Produk!
                                    </div>
                                <?php endif; ?>

                                <!-- Tombol Detail (Trigger Modal) -->
                                <button type="button" class="btn-action btn-detail" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $row['id'] ?>">
                                    Detail / Ulasan <i class="fas fa-chevron-right ms-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- MODAL DETAIL -->
                <div class="modal fade" id="modalDetail<?= $row['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 rounded-4 shadow-lg">
                            <div class="modal-header border-0 pb-0 pt-4 px-4">
                                <h5 class="modal-title fw-bold">Detail Pesanan</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                
                                <!-- Bagian Upload Bukti (Khusus Pending & Non-COD) -->
                                <?php if($row['payment_method'] != 'COD' && $row['status'] == 'Pending'): ?>
                                    <div class="bg-light p-3 rounded-3 mb-3 border">
                                        <?php if(empty($row['payment_proof'])): ?>
                                            <h6 class="fw-bold text-dark mb-2">Upload Bukti Transfer</h6>
                                            <form method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                                                <input type="file" name="bukti" class="form-control mb-2" required accept="image/*">
                                                <button type="submit" name="upload_bukti" class="btn btn-success w-100 btn-sm fw-bold">Kirim Bukti</button>
                                            </form>
                                        <?php else: ?>
                                            <div class="text-center"><span class="badge bg-success mb-2">Bukti Terkirim</span><br><img src="assets/img/pembayaran/<?= $row['payment_proof'] ?>" class="img-fluid rounded border" style="max-height: 100px;"></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Info Resi -->
                                <?php if(!empty($row['tracking_number'])): ?>
                                    <div class="alert alert-primary d-flex align-items-center mb-3 py-2">
                                        <i class="fas fa-shipping-fast me-2"></i>
                                        <div><strong>Resi:</strong> <?= $row['tracking_number'] ?></div>
                                    </div>
                                <?php endif; ?>

                                <!-- LIST PRODUK + TOMBOL ULASAN -->
                                <h6 class="fw-bold small text-uppercase text-muted mb-2">Item Produk:</h6>
                                <ul class="list-group list-group-flush mb-3">
                                    <?php 
                                    $oid = $row['id'];
                                    $q_detail = mysqli_query($conn, "SELECT * FROM order_details WHERE order_id = '$oid'");
                                    while($item = mysqli_fetch_assoc($q_detail)):
                                    ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                        <div>
                                            <span class="fw-bold text-dark small d-block"><?= $item['product_name'] ?></span>
                                            <small class="text-muted">x<?= $item['qty'] ?></small>
                                        </div>
                                        
                                        <div class="text-end">
                                            <div class="fw-bold text-dark small mb-1">Rp <?= number_format($item['price'] * $item['qty']) ?></div>
                                            
                                            <!-- TOMBOL RATING (Hanya Jika Selesai) -->
                                            <?php if($row['status'] == 'Selesai'): ?>
                                                <a href="detail.php?id=<?= $item['product_id'] ?>" class="btn-review">
                                                    <i class="fas fa-star text-warning"></i> Beri Ulasan
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                    <?php endwhile; ?>
                                </ul>
                                
                                <!-- Total Harga -->
                                <div class="bg-light p-3 rounded mb-3">
                                    <div class="d-flex justify-content-between mb-1"><small>Ongkir</small><span class="fw-bold small">Rp <?= number_format($row['shipping_cost'] ?? 0) ?></span></div>
                                    <div class="d-flex justify-content-between border-top pt-2"><span class="fw-bold text-dark">Total Bayar</span><span class="fw-bold text-primary">Rp <?= number_format($row['total_price']) ?></span></div>
                                </div>

                                <div class="d-grid">
                                    <a href="invoice.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-outline-dark fw-bold btn-sm"><i class="fas fa-print me-2"></i> Cetak Invoice</a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <!-- END MODAL -->

                <?php endwhile; ?>

            <?php else: ?>
                <div class="empty-state fade-in">
                    <i class="fas fa-shopping-basket empty-icon"></i>
                    <h4 class="fw-bold text-dark">Belum ada pesanan</h4>
                    <a href="index.php" class="btn btn-primary rounded-pill px-5 mt-3 shadow-sm">Mulai Belanja</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>