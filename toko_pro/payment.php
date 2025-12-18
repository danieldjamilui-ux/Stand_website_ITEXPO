<?php
session_start();
include 'db.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Cek ID Order di URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Ambil Data Order (Pastikan order punya user yang sedang login)
$query = mysqli_query($conn, "SELECT * FROM orders WHERE id = '$order_id' AND user_id = '$user_id'");
$order = mysqli_fetch_assoc($query);

if (!$order) {
    echo "<script>alert('Order tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

$metode = $order['payment_method'];
$total = $order['total_price'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - SHOP COMMERCE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* BASE STYLES & FONT */
        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(135deg, #f4f6f9 0%, #e0e5ec 100%); /* Subtle gradient background */
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        /* CARD ENHANCEMENT & FADE-IN ANIMATION */
        .card-payment { 
            border: none; 
            border-radius: 25px; /* Softer corners */
            box-shadow: 0 15px 40px rgba(0,0,0,0.15); /* Deeper shadow */
            background: white;
            /* Initial state for animation */
            transform: translateY(30px); 
            opacity: 0; 
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        /* TOTAL AMOUNT PULSE ANIMATION */
        .total-amount { 
            font-size: 2.5rem; /* Bigger */
            font-weight: 800; 
            color: #764ba2; /* Main color */
            display: inline-block;
            animation: pulseAmount 2s infinite alternate; /* The cool animation */
        }

        /* COPY BUTTON INTERACTION */
        .copy-btn { 
            cursor: pointer; 
            color: #764ba2; 
            font-size: 0.9rem; 
            font-weight: 600; 
            transition: all 0.2s; /* Smooth transition */
            user-select: none;
        }
        .copy-btn:hover {
            color: #5d3d82;
            transform: scale(1.05); /* Subtle scale on hover */
        }

        /* PAYMENT BOX HOVER EFFECT (Bank Transfer) */
        .payment-option-box {
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }
        .payment-option-box:hover {
            border-color: #764ba2;
            box-shadow: 0 5px 15px rgba(118, 75, 162, 0.1);
            transform: translateY(-2px);
        }

        /* QR CODE GLOW */
        .qr-img {
            width: 200px; height: 200px; object-fit: contain; 
            border: 5px solid #764ba2; /* Accent border */
            border-radius: 10px; padding: 10px; 
            box-shadow: 0 0 20px rgba(118, 75, 162, 0.4); /* Glow effect */
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .qr-img:hover {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(118, 75, 162, 0.6);
        }
        
        /* COD ICON ANIMATION */
        .cod-icon {
            color: #764ba2; 
            animation: truckMove 4s infinite cubic-bezier(0.455, 0.03, 0.515, 0.955);
        }

        /* MAIN BUTTON STYLE */
        .btn-payment-cta {
            background: #764ba2 !important; 
            border:none !important;
            transition: all 0.3s;
        }
        .btn-payment-cta:hover {
            background: #5d3d82 !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(118, 75, 162, 0.4);
        }

        /* KEYFRAME ANIMATIONS */
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes pulseAmount {
            0% { transform: scale(1); }
            100% { transform: scale(1.02); }
        }
        @keyframes truckMove {
            0% { transform: translateX(-5px) rotate(0deg); }
            50% { transform: translateX(5px) rotate(2deg); }
            100% { transform: translateX(-5px) rotate(0deg); }
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            
            <div class="card card-payment p-4">
                <div class="text-center mb-4">
                    <h5 class="text-muted mb-2">Total Pembayaran</h5>
                    <!-- Total Amount with Pulse Animation -->
                    <div class="total-amount">Rp <?= number_format($total, 0, ',', '.') ?></div>
                    <span class="badge bg-warning text-dark mt-3 py-2 px-3 fw-bold rounded-pill">Menunggu Pembayaran</span>
                </div>

                <hr class="my-4">

                <!-- 1. TAMPILAN JIKA TRANSFER BANK -->
                <?php if ($metode == 'Transfer Bank'): ?>
                    <div class="text-center">
                        <h5 class="fw-bold mb-4 text-secondary"><i class="fas fa-university me-2 text-primary"></i>Transfer Bank</h5>
                        <p class="text-muted small">Silakan transfer ke salah satu rekening resmi kami:</p>
                        
                        <!-- BCA Account -->
                        <div class="bg-light p-3 rounded mb-3 text-start d-flex justify-content-between align-items-center payment-option-box">
                            <div>
                                <img src="https://upload.wikimedia.org/wikipedia/commons/5/5c/Bank_Central_Asia.svg" width="60" class="mb-1">
                                <div class="fw-bold mt-1">123-456-7890</div>
                                <small class="text-muted">a.n Toko Pro Official</small>
                            </div>
                            <span class="copy-btn" onclick="copyToClipboard('1234567890')"><i class="fas fa-copy me-1"></i> Salin</span>
                        </div>

                        <!-- Mandiri Account -->
                        <div class="bg-light p-3 rounded mb-3 text-start d-flex justify-content-between align-items-center payment-option-box">
                            <div>
                                <img src="https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo_2016.svg" width="60" class="mb-1">
                                <div class="fw-bold mt-1">987-654-3210</div>
                                <small class="text-muted">a.n SHOP COMMERCE Official</small>
                            </div>
                            <span class="copy-btn" onclick="copyToClipboard('9876543210')"><i class="fas fa-copy me-1"></i> Salin</span>
                        </div>
                        
                        <div class="alert alert-info small mt-4 rounded-pill">
                            <i class="fas fa-info-circle me-1"></i> Proses pembayaran otomatis.
                        </div>
                    </div>

                <!-- 2. TAMPILAN JIKA E-WALLET (QRIS) -->
                <?php elseif ($metode == 'E-Wallet'): ?>
                    <div class="text-center">
                        <h5 class="fw-bold mb-4 text-secondary"><i class="fas fa-qrcode me-2 text-primary"></i>Scan QRIS (E-Wallet)</h5>
                        <p class="text-muted small">Buka aplikasi E-Wallet Anda (Dana/OVO/GoPay/LinkAja) dan scan QR Code di bawah:</p>
                        
                        <!-- Generate QR Code Asli berdasarkan Total Harga dengan Glow Effect -->
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=Bayar Ke Toko Pro Sejumlah Rp <?= $total ?>" class="qr-img mb-3">
                        
                        <p class="small fw-bold mb-1">SHOP COMMERCE Official</p>
                        <p class="small text-muted">NMID: ID123456789</p>

                        <div class="alert alert-warning small mt-4 rounded-pill">
                            <i class="fas fa-exclamation-circle me-1"></i> Pastikan nominalnya **Rp <?= number_format($total, 0, ',', '.') ?>**.
                        </div>
                    </div>

                <!-- 3. TAMPILAN JIKA COD -->
                <?php else: ?>
                    <div class="text-center py-4">
                        <!-- Animated COD Icon -->
                        <i class="fas fa-shipping-fast fa-5x cod-icon mb-4"></i>
                        <h5 class="fw-bold mb-3">Bayar di Tempat (COD)</h5>
                        <p class="text-muted px-3">Pesanan Anda sedang kami proses. Mohon siapkan uang tunai sejumlah <br>
                            <strong><span class="total-amount" style="font-size: 1.5rem;">Rp <?= number_format($total, 0, ',', '.') ?></span></strong> 
                            saat kurir tiba.</p>
                        <div class="alert alert-success small mt-4 rounded-pill">
                            <i class="fas fa-check-circle me-1"></i> Anda tidak perlu melakukan apa-apa sekarang.
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mt-4 pt-2">
                    <a href="riwayat.php" class="btn btn-payment-cta w-100 py-2 rounded-pill fw-bold">
                        <i class="fas fa-receipt me-1"></i> Saya Sudah Bayar / Cek Riwayat
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Script Salin No Rek -->
<script>
    function copyToClipboard(text) {
        // Remove dashes/spaces if present (clean up the number)
        const cleanText = text.replace(/-/g, '').replace(/ /g, '');
        
        navigator.clipboard.writeText(cleanText).then(function() {
            // Use a fancier notification if possible, but alert works fine too
            alert('Nomor rekening ' + cleanText + ' berhasil disalin!');
        }, function(err) {
            alert('Gagal menyalin teks: ' + err);
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>