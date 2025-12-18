<?php
session_start();
include 'db.php';

// 1. Cek Login & ID Pesanan
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$oid = mysqli_real_escape_string($conn, $_GET['id']);
$uid = $_SESSION['user_id'];
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

// 2. Logika Akses (Admin boleh lihat semua, User cuma punya sendiri)
if ($role == 'admin') {
    $query = mysqli_query($conn, "SELECT * FROM orders WHERE id='$oid'");
} else {
    $query = mysqli_query($conn, "SELECT * FROM orders WHERE id='$oid' AND user_id='$uid'");
}

$order = mysqli_fetch_assoc($query);

// 3. Jika pesanan tidak ditemukan
if (!$order) {
    die("<h3 style='text-align:center; margin-top:50px; font-family:sans-serif;'>Data tidak ditemukan atau Anda tidak memiliki akses.</h3>");
}

// 4. Ambil Detail Barang
$details = mysqli_query($conn, "SELECT * FROM order_details WHERE order_id='$oid'");

// Jika query detail gagal
if (!$details) {
    die("Gagal mengambil detail barang.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $oid ?> - SHOP COMMERCE</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; padding: 40px; max-width: 800px; margin: 0 auto; line-height: 1.6; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #764ba2; text-transform: uppercase; }
        .invoice-title { font-size: 20px; font-weight: bold; text-transform: uppercase; }
        
        .info-section { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .info-box h5 { margin: 0 0 5px 0; font-size: 14px; color: #777; text-transform: uppercase; }
        .info-box p { margin: 0; font-size: 15px; font-weight: bold; color: #333; }
        .info-address { font-weight: normal !important; max-width: 300px; line-height: 1.4; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { text-align: left; background: #f8f9fa; padding: 15px; border-bottom: 2px solid #ddd; font-size: 14px; text-transform: uppercase; color: #555; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        .total-section { text-align: right; }
        .total-row { display: flex; justify-content: flex-end; margin-bottom: 10px; }
        .total-label { width: 150px; color: #555; font-size: 14px; }
        .total-value { width: 150px; font-weight: bold; font-size: 15px; }
        
        .grand-total { border-top: 2px solid #333; padding-top: 15px; margin-top: 15px; align-items: center; }
        .grand-total .total-label { font-weight: bold; font-size: 16px; text-transform: uppercase; }
        .grand-total .total-value { font-size: 20px; color: #764ba2; }

        .footer { margin-top: 60px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee; padding-top: 20px; }
        
        /* STYLE UNTUK RESI */
        .resi-box { 
            border: 2px dashed #764ba2; 
            background: #fdfaff; 
            color: #764ba2;
            padding: 8px 15px; 
            margin-top: 15px; 
            display: inline-block; 
            font-weight: bold;
            font-family: monospace;
            font-size: 16px;
        }

        @media print { .no-print { display: none; } }
        .btn-print {
            background: #333; color: white; border: none; padding: 12px 25px; 
            cursor: pointer; border-radius: 50px; font-weight: bold; 
            display: inline-flex; align-items: center; gap: 10px; transition: 0.3s;
        }
        .btn-print:hover { background: #555; }
    </style>
    <!-- FontAwesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body onload="window.print()">

    <div class="header">
        <div class="logo">SHOP COMMERCE</div>
        <div class="invoice-title">INVOICE #<?= $order['id'] ?></div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <h5>Dikirim Kepada:</h5>
            <p><?= $order['recipient_name'] ?></p>
            <p class="info-address">
                <?= $order['address'] ?><br>
                <?= $order['city'] ?>
            </p>
        </div>
        <div class="info-box" style="text-align: right;">
            <h5>Tanggal Order:</h5>
            <p><?= date('d F Y, H:i', strtotime($order['order_date'])) ?></p>
            
            <h5 style="margin-top: 15px;">Metode Pembayaran:</h5>
            <p><?= $order['payment_method'] ?></p>
            
            <h5 style="margin-top: 15px;">Status Pesanan:</h5>
            <p style="text-transform: uppercase; font-weight:bold; color: #764ba2;"><?= $order['status'] ?></p>

            <!-- MENAMPILKAN RESI JIKA ADA -->
            <!-- Pastikan di database sudah ada kolom 'tracking_number' -->
            <?php if (!empty($order['tracking_number'])): ?>
                <div class="resi-box">
                    RESI: <?= $order['tracking_number'] ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th style="width: 150px;">Harga Satuan</th>
                <th style="width: 80px; text-align: center;">Qty</th>
                <th style="width: 150px; text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while($item = mysqli_fetch_assoc($details)): ?>
            <tr>
                <td><?= $item['product_name'] ?></td>
                <td>Rp <?= number_format($item['price']) ?></td>
                <td style="text-align: center;"><?= $item['qty'] ?></td>
                <td style="text-align: right;">Rp <?= number_format($item['price'] * $item['qty']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <div class="total-label">Subtotal Produk</div>
            <div class="total-value">Rp <?= number_format($order['total_price'] - $order['shipping_cost']) ?></div>
        </div>
        <div class="total-row">
            <div class="total-label">Biaya Pengiriman</div>
            <div class="total-value">Rp <?= number_format($order['shipping_cost']) ?></div>
        </div>
        <div class="total-row grand-total">
            <div class="total-label">TOTAL TAGIHAN</div>
            <div class="total-value">Rp <?= number_format($order['total_price']) ?></div>
        </div>
    </div>

    <div class="footer">
        <p>Terima kasih telah berbelanja di Shop Commerce.<br>Simpan invoice ini sebagai bukti pembayaran yang sah.</p>
        <small>&copy; <?= date('Y') ?> Shop Commerce Official</small>
    </div>

    <center class="no-print" style="margin-top: 40px;">
        <button onclick="window.print()" class="btn-print">
            <i class="fas fa-print"></i> Cetak / Simpan PDF
        </button>
    </center>

</body>
</html>