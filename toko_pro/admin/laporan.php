<?php
session_start();
include '../db.php';

// Cek Akses Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// --- 1. HITUNG TOTAL PENDAPATAN (Seumur Hidup) ---
$q_total = mysqli_query($conn, "SELECT SUM(total_price) as omset, COUNT(*) as transaksi FROM orders WHERE status='Selesai'");
$data_total = mysqli_fetch_assoc($q_total);
$grand_total = $data_total['omset'] ?? 0;
$total_transaksi = $data_total['transaksi'] ?? 0;

// --- 2. SIAPKAN DATA UNTUK GRAFIK (Pendapatan Harian Bulan Ini) ---
$bulan_ini = date('m');
$tahun_ini = date('Y');

// Query: Kelompokkan pendapatan berdasarkan tanggal (hari)
$q_chart = mysqli_query($conn, "
    SELECT DATE_FORMAT(order_date, '%d %M') as tanggal, SUM(total_price) as total 
    FROM orders 
    WHERE status='Selesai' AND MONTH(order_date) = '$bulan_ini' AND YEAR(order_date) = '$tahun_ini'
    GROUP BY DATE(order_date)
    ORDER BY order_date ASC
");

$chart_labels = [];
$chart_data = [];

while($c = mysqli_fetch_assoc($q_chart)){
    $chart_labels[] = $c['tanggal']; // Sumbu X (Tanggal)
    $chart_data[] = $c['total'];     // Sumbu Y (Nominal)
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan - Admin Shop</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; }
        
        /* Sidebar (Konsisten dengan halaman lain) */
        .sidebar { min-height: 100vh; background: #2c3e50; color: #fff; position: fixed; width: 250px; padding-top: 20px; z-index: 1000; }
        .sidebar-brand { text-align: center; font-size: 1.5rem; font-weight: 700; padding-bottom: 20px; border-bottom: 1px solid #34495e; margin-bottom: 20px; color: #ecf0f1; text-decoration: none; display: block; }
        .sidebar-menu a { display: block; color: #b0bec5; padding: 15px 25px; text-decoration: none; transition: 0.3s; font-weight: 500; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: #34495e; color: #fff; border-left: 4px solid #3498db; }
        .sidebar-menu i { width: 25px; }

        .main-content { margin-left: 250px; padding: 30px; }

        /* Kartu Statistik */
        .card-stat { border: none; border-radius: 12px; padding: 25px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .bg-gradient-green { background: linear-gradient(135deg, #2ecc71, #27ae60); }
        .bg-gradient-blue { background: linear-gradient(135deg, #3498db, #2980b9); }

        /* Grafik Card */
        .card-chart { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); background: white; padding: 20px; margin-bottom: 30px; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <a href="index.php" class="sidebar-brand"><i class="fas fa-rocket me-2"></i> ADMIN SHOP</a>
    <div class="sidebar-menu">
        <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="produk.php"><i class="fas fa-box"></i> Kelola Produk</a>
        <a href="pesanan.php"><i class="fas fa-shopping-cart"></i> Kelola Pesanan</a>
        <a href="pelanggan.php"><i class="fas fa-users"></i> Pelanggan</a>
        <a href="laporan.php" class="active"><i class="fas fa-chart-line"></i> Laporan</a>
        <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i> Lihat Website</a>
        <a href="#" class="text-danger mt-4" onclick="konfirmasiLogout(event)"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark">Laporan Pendapatan</h3>
        <button onclick="window.print()" class="btn btn-outline-secondary"><i class="fas fa-print me-2"></i> Cetak Laporan</button>
    </div>

    <!-- 1. Ringkasan Atas -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card-stat bg-gradient-green">
                <div>
                    <h5 class="mb-1 opacity-75">TOTAL PENDAPATAN</h5>
                    <h2 class="fw-bold mb-0">Rp <?= number_format($grand_total, 0, ',', '.') ?></h2>
                </div>
                <i class="fas fa-money-bill-wave fa-3x opacity-50"></i>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-stat bg-gradient-blue">
                <div>
                    <h5 class="mb-1 opacity-75">TRANSAKSI BERHASIL</h5>
                    <h2 class="fw-bold mb-0"><?= $total_transaksi ?> <span class="fs-6 fw-normal">Pesanan</span></h2>
                </div>
                <i class="fas fa-shopping-bag fa-3x opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- 2. Grafik Pendapatan (Chart.js) -->
    <div class="card-chart">
        <h5 class="fw-bold text-secondary mb-4">Grafik Penjualan Bulan Ini (<?= date('F Y') ?>)</h5>
        <canvas id="incomeChart" style="height: 300px; width: 100%;"></canvas>
    </div>

    <!-- 3. Tabel Rincian Transaksi -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3">
            <h6 class="fw-bold mb-0">Rincian Transaksi Masuk</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">No Order</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th class="text-end pe-4">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Ambil detail data tabel
                        $q_list = mysqli_query($conn, "SELECT orders.*, users.name FROM orders JOIN users ON orders.user_id=users.id WHERE orders.status='Selesai' ORDER BY id DESC LIMIT 10");
                        
                        if(mysqli_num_rows($q_list) > 0):
                            while($row = mysqli_fetch_assoc($q_list)):
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-primary">#<?= $row['id'] ?></td>
                            <td><?= date('d M Y H:i', strtotime($row['order_date'])) ?></td>
                            <td><?= $row['name'] ?></td>
                            <td class="text-end pe-4 fw-bold text-success">+ Rp <?= number_format($row['total_price']) ?></td>
                        </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4" class="text-center py-4">Belum ada data penjualan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Script Chart.js (Wajib Ada) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // Konfigurasi Grafik
    const ctx = document.getElementById('incomeChart').getContext('2d');
    
    // Data dari PHP
    const labels = <?php echo json_encode($chart_labels); ?>;
    const dataOmset = <?php echo json_encode($chart_data); ?>;

    new Chart(ctx, {
        type: 'line', // Jenis grafik: Line (Garis) atau Bar (Batang)
        data: {
            labels: labels,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: dataOmset,
                borderColor: '#2ecc71', // Warna Garis Hijau
                backgroundColor: 'rgba(46, 204, 113, 0.1)', // Warna Area Bawah (Transparan)
                borderWidth: 3,
                tension: 0.4, // Membuat garis melengkung halus
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#27ae60',
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false } // Sembunyikan legenda atas
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [5, 5] } // Garis putus-putus
                },
                x: {
                    grid: { display: false } // Hilangkan grid vertikal
                }
            }
        }
    });
</script>

</body>
</html>