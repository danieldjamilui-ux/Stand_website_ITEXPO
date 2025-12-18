<?php
session_start();
include 'db.php';

// 2. Simpan detail & KURANGI STOK
        foreach ($_SESSION['cart'] as $item) {
            $pid = $item['id'];
            $pname = $item['name'];
            $qty = $item['qty'];
            $price = $item['price'];
            
            // Simpan Detail
            // --- KURANGI STOK DI DATABASE ---
            mysqli_query($conn, "UPDATE products SET stock = stock - $qty WHERE id = '$pid'");
        }

// --- PROTEKSI: ADMIN DILARANG CHECKOUT ---
if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    echo "<script>
        alert('Akses Ditolak: Admin tidak bisa checkout barang.');
        window.location = 'admin/index.php';
    </script>";
    exit;
}

// Cek Login & Keranjang
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if (empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit;
}

// --- DATA ONGKIR (Sama seperti sebelumnya) ---
$shipping_rates = [
    'Bacan' => 50000,
    'Ternate' => 10000,
    'Tidore' => 150000,
    'Halmahera' => 20000,
    'Jailolo' => 25000,
    'Weda' => 30000,
    'Morotai' => 35000,
    'Lainnya' => 40000
];

// Hitung Subtotal
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += ($item['price'] * $item['qty']);
}

// --- PROSES ORDER ---
if (isset($_POST['place_order'])) {
    $user_id = $_SESSION['user_id'];
    $recipient_name = mysqli_real_escape_string($conn, $_POST['recipient_name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = $_POST['city'];
    $payment = $_POST['payment_method'];
    
    $shipping_cost = isset($shipping_rates[$city]) ? $shipping_rates[$city] : 50000;
    $final_total = $subtotal + $shipping_cost;

    $query_order = "INSERT INTO orders (user_id, recipient_name, address, city, total_price, shipping_cost, payment_method, status) 
                    VALUES ('$user_id', '$recipient_name', '$address', '$city', '$final_total', '$shipping_cost', '$payment', 'Pending')";
    
    if (mysqli_query($conn, $query_order)) {
        $order_id = mysqli_insert_id($conn);
        foreach ($_SESSION['cart'] as $item) {
            $pid = $item['id']; $pname = $item['name']; $qty = $item['qty']; $price = $item['price'];
            mysqli_query($conn, "INSERT INTO order_details (order_id, product_id, product_name, qty, price) VALUES ('$order_id', '$pid', '$pname', '$qty', '$price')");
        }
        unset($_SESSION['cart']);
        // Redirect ke Payment
        echo "<script>window.location = 'payment.php?id=$order_id';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Shop Commerce</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --accent-color: #764ba2;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f3f4f6;
        }

        /* Navbar */
        .navbar-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
            padding: 15px 0;
        }
        .navbar-brand {
            font-weight: 800; font-size: 1.5rem;
            background: -webkit-linear-gradient(45deg, #667eea, #764ba2);
            background-clip: text;
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        /* Layout Kartu */
        .card-custom {
            border: none;
            border-radius: 20px;
            background: white;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            padding: 30px;
            transition: 0.3s;
        }
        .card-custom:hover { box-shadow: 0 20px 40px rgba(0,0,0,0.08); }

        /* Judul Section */
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title i { color: var(--accent-color); }

        /* Form Input Modern */
        .input-group-text {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #9ca3af;
        }
        .form-control, .form-select {
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            padding: 12px 15px;
            border-radius: 0 12px 12px 0; /* Input menyatu dengan ikon */
            transition: 0.3s;
        }
        /* Fix border radius untuk select & textarea */
        .form-select { border-radius: 12px; }
        textarea.form-control { border-radius: 12px; border-left: 1px solid #e5e7eb; }

        .form-control:focus, .form-select:focus {
            background: #fff;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(118, 75, 162, 0.1);
        }

        /* Order Summary Card (Sticky) */
        .card-summary {
            position: sticky;
            top: 100px;
            background: linear-gradient(145deg, #ffffff, #fdfdfd);
            border: 1px solid #fff;
        }
        
        .product-list { max-height: 250px; overflow-y: auto; padding-right: 5px; }
        .product-list::-webkit-scrollbar { width: 5px; }
        .product-list::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }

        .price-row { display: flex; justify-content: space-between; margin-bottom: 12px; color: #666; font-size: 0.95rem; }
        .price-total {
            display: flex; justify-content: space-between;
            margin-top: 20px; padding-top: 20px;
            border-top: 2px dashed #eee;
            font-size: 1.3rem; font-weight: 800; color: #333;
        }
        .price-total span:last-child { color: var(--accent-color); }

        /* Tombol Checkout */
        .btn-checkout {
            background: var(--primary-gradient);
            border: none; color: white;
            font-weight: 700; padding: 15px;
            border-radius: 12px; width: 100%;
            margin-top: 25px; transition: 0.3s;
            box-shadow: 0 10px 20px rgba(118, 75, 162, 0.3);
            position: relative; overflow: hidden;
        }
        .btn-checkout:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(118, 75, 162, 0.4);
        }
        /* Efek Kilau Tombol */
        .btn-checkout::after {
            content: ""; position: absolute; top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        .btn-checkout:hover::after { left: 100%; }

    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-glass sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-shopping-bag me-2"></i>SHOP COMMERCE</a>
        <div class="ms-auto">
            <a href="cart.php" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold border-2">
                <i class="fas fa-chevron-left me-2"></i> Kembali
            </a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <form method="POST">
        <div class="row g-4">
            
            <!-- KOLOM KIRI: Formulir (Animasi Slide dari Kiri) -->
            <div class="col-lg-7 animate__animated animate__fadeInLeft">
                <div class="card-custom">
                    <h4 class="section-title"><i class="fas fa-shipping-fast"></i> Informasi Pengiriman</h4>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">NAMA PENERIMA</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="recipient_name" class="form-control" value="<?= $_SESSION['name'] ?>" required placeholder="Nama lengkap penerima">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">ALAMAT LENGKAP</label>
                        <textarea name="address" class="form-control" rows="3" placeholder="Jalan, Nomor Rumah, RT/RW, Kelurahan, Kecamatan..." required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">KOTA TUJUAN</label>
                            <select name="city" id="citySelect" class="form-select" required onchange="updateTotal()">
                                <option value="" data-cost="0" selected disabled>-- Pilih Kota --</option>
                                <?php foreach($shipping_rates as $city => $cost): ?>
                                    <option value="<?= $city ?>" data-cost="<?= $cost ?>">
                                        <?= $city ?> (Ongkir: Rp <?= number_format($cost, 0, ',', '.') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold small text-muted">METODE PEMBAYARAN</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="" selected disabled>-- Pilih Metode --</option>
                                <option value="Transfer Bank">Transfer Bank (BCA/Mandiri)</option>
                                <option value="COD">COD (Bayar di Tempat)</option>
                                <option value="E-Wallet">E-Wallet (QRIS)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: Ringkasan (Animasi Slide dari Kanan) -->
            <div class="col-lg-5 animate__animated animate__fadeInRight animate__delay-0.5s">
                <div class="card-custom card-summary">
                    <h4 class="section-title"><i class="fas fa-receipt"></i> Ringkasan Pesanan</h4>
                    
                    <div class="product-list mb-3">
                        <?php foreach($_SESSION['cart'] as $item): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded p-2 me-3">
                                    <i class="fas fa-box text-secondary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 small fw-bold"><?= $item['name'] ?></h6>
                                    <small class="text-muted"><?= $item['qty'] ?> x Rp <?= number_format($item['price'], 0, ',', '.') ?></small>
                                </div>
                            </div>
                            <span class="small fw-bold">Rp <?= number_format($item['price'] * $item['qty'], 0, ',', '.') ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="price-row">
                        <span>Subtotal Produk</span>
                        <span class="fw-bold text-dark">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                    </div>
                    <div class="price-row">
                        <span>Biaya Pengiriman</span>
                        <span class="fw-bold text-success" id="shippingDisplay">Rp 0</span>
                    </div>
                    
                    <div class="price-total">
                        <span>Total Tagihan</span>
                        <!-- ID untuk animasi angka -->
                        <span id="totalDisplay" data-value="<?= $subtotal ?>">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                    </div>

                    <button type="submit" name="place_order" class="btn-checkout">
                        PROSES SEKARANG <i class="fas fa-arrow-right ms-2"></i>
                    </button>

                    <div class="text-center mt-3 text-muted small">
                        <i class="fas fa-lock me-1"></i> Pembayaran Aman & Terenkripsi
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<!-- JAVASCRIPT ANIMASI HARGA -->
<script>
    const baseSubtotal = <?= $subtotal ?>;

    // Fungsi Format Rupiah
    function formatRupiah(num) {
        return 'Rp ' + num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Fungsi Animasi Angka (Count Up)
    function animateValue(obj, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            obj.innerHTML = formatRupiah(value);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    function updateTotal() {
        const citySelect = document.getElementById('citySelect');
        const selectedOption = citySelect.options[citySelect.selectedIndex];
        const shippingCost = parseInt(selectedOption.getAttribute('data-cost')) || 0;
        
        // Update Ongkir Teks
        document.getElementById('shippingDisplay').innerText = formatRupiah(shippingCost);

        // Ambil elemen Total
        const totalElem = document.getElementById('totalDisplay');
        const currentTotal = parseInt(totalElem.getAttribute('data-value')); // Nilai lama
        const newTotal = baseSubtotal + shippingCost; // Nilai baru

        // Jalankan Animasi Angka (Lama ke Baru)
        animateValue(totalElem, currentTotal, newTotal, 800); // 800ms durasi animasi
        
        // Simpan nilai baru ke atribut untuk animasi berikutnya
        totalElem.setAttribute('data-value', newTotal);
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>