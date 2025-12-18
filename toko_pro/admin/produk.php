<?php
session_start();
include '../db.php';

// Cek Akses Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// --- LOGIKA CRUD ---
$edit_mode = false;
$product_data = [];

// 1. Edit Data (Ambil data lama)
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $edit_mode = true;
    $q = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
    $product_data = mysqli_fetch_assoc($q);
}

// 2. Simpan Data (Insert / Update)
if (isset($_POST['simpan'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $cat = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock']; // Ambil stok
    $desc = mysqli_real_escape_string($conn, $_POST['description']);

    // Logika Upload Gambar
    $image_query = "";
    if (!empty($_FILES['image']['name'])) {
        $img_name = time() . '_' . $_FILES['image']['name'];
        
        // Pastikan folder ada
        $target_dir = "../assets/img/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

        if(move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $img_name)){
            $image_query = ", image='$img_name'";
        }
    }

    if (isset($_POST['id_produk']) && !empty($_POST['id_produk'])) {
        // UPDATE
        $id = $_POST['id_produk'];
        $query = "UPDATE products SET name='$name', category='$cat', price='$price', stock='$stock', description='$desc' $image_query WHERE id='$id'";
    } else {
        // INSERT
        $img_fix = isset($img_name) ? $img_name : 'default.jpg';
        $query = "INSERT INTO products (name, category, price, stock, description, image) VALUES ('$name', '$cat', '$price', '$stock', '$desc', '$img_fix')";
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data berhasil disimpan!'); window.location='produk.php';</script>";
    }
}

// 3. Hapus Data
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    // Hapus file fisik
    $q = mysqli_query($conn, "SELECT image FROM products WHERE id='$id'");
    $row = mysqli_fetch_assoc($q);
    if ($row['image'] && file_exists("../assets/img/" . $row['image'])) {
        unlink("../assets/img/" . $row['image']);
    }
    
    mysqli_query($conn, "DELETE FROM products WHERE id='$id'");
    echo "<script>alert('Produk dihapus!'); window.location='produk.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Produk - Admin Shop</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; overflow-x: hidden; }

        /* Sidebar & Layout */
        .sidebar { min-height: 100vh; background: #2c3e50; color: #fff; position: fixed; width: 250px; padding-top: 20px; z-index: 1000; transition: 0.3s; left: 0; }
        .sidebar.hide { left: -250px; }
        .sidebar-brand { text-align: center; font-size: 1.5rem; font-weight: 700; padding-bottom: 20px; margin-bottom: 20px; border-bottom: 1px solid #34495e; color: #ecf0f1; text-decoration: none; display: block; }
        .sidebar-menu a { display: block; color: #b0bec5; padding: 15px 25px; text-decoration: none; transition: 0.3s; font-weight: 500; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background-color: #34495e; color: #fff; border-left: 4px solid #3498db; }
        .main-content { margin-left: 250px; padding: 30px; transition: 0.3s; }
        .main-content.expand { margin-left: 0; }

        /* Card Modern */
        .card-modern {
            background: white;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            padding: 25px;
            transition: 0.3s;
        }
        .card-modern:hover { box-shadow: 0 15px 35px rgba(0,0,0,0.08); }

        /* Form Styling */
        .form-label { font-weight: 600; font-size: 0.85rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #e9ecef;
            padding: 12px 15px;
            background-color: #f8f9fa;
            transition: 0.3s;
        }
        .form-control:focus, .form-select:focus {
            background-color: #fff;
            border-color: #3498db;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }

        /* Image Upload Box */
        .upload-area {
            border: 2px dashed #cbd5e0;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            background: #f8f9fa;
            transition: 0.3s;
            position: relative;
        }
        .upload-area:hover { border-color: #3498db; background: #fff; }
        .upload-area img { max-width: 100%; max-height: 200px; border-radius: 10px; object-fit: contain; }
        .upload-icon { font-size: 2rem; color: #adb5bd; margin-bottom: 10px; }
        .file-input { position: absolute; width: 100%; height: 100%; top: 0; left: 0; opacity: 0; cursor: pointer; }

        /* Table Styling */
        .table-modern thead th {
            border: none;
            background: #f1f5f9;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            padding: 15px;
            border-radius: 10px;
        }
        .table-modern td { padding: 15px; vertical-align: middle; border-bottom: 1px solid #f1f5f9; }
        .product-thumb { width: 60px; height: 60px; border-radius: 10px; object-fit: cover; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        
        .badge-cat { background: #e0f2fe; color: #0284c7; padding: 5px 12px; border-radius: 50px; font-weight: 600; font-size: 0.75rem; }
        .btn-action { width: 35px; height: 35px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; transition: 0.2s; border: none; }
        .btn-edit { background: #fff3cd; color: #d97706; }
        .btn-edit:hover { background: #ffecb3; color: #b45309; }
        .btn-delete { background: #fee2e2; color: #dc2626; }
        .btn-delete:hover { background: #fecaca; color: #b91c1c; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <a href="index.php" class="sidebar-brand"><i class="fas fa-rocket me-2"></i> ADMIN SHOP</a>
    <div class="sidebar-menu">
        <a href="index.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
        <a href="produk.php" class="active"><i class="fas fa-box me-2"></i> Kelola Produk</a>
        <a href="pesanan.php"><i class="fas fa-shopping-cart me-2"></i> Kelola Pesanan</a>
        <a href="pelanggan.php"><i class="fas fa-users me-2"></i> Pelanggan</a>
        <a href="laporan.php" class="active"><i class="fas fa-chart-line"></i> Laporan</a>
        <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i> Lihat Website</a>
        <a href="#" class="text-danger mt-4" onclick="konfirmasiLogout(event)"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
    </div>
</div>

<div class="main-content" id="content">
    
    <div class="d-flex align-items-center mb-4">
        <i class="fas fa-bars fs-4 me-3" id="menu-toggle" style="cursor:pointer"></i>
        <div>
            <h3 class="fw-bold mb-0">Manajemen Produk & Stok</h3>
            <p class="text-muted mb-0">Tambah, edit, dan pantau stok barang Anda.</p>
        </div>
    </div>

    <div class="row">
        <!-- FORM INPUT (KIRI - STICKY) -->
        <div class="col-lg-4 mb-4">
            <div class="card-modern position-sticky" style="top: 20px;">
                <h5 class="fw-bold mb-4"><i class="fas fa-edit me-2 text-primary"></i><?= $edit_mode ? 'Edit Produk' : 'Tambah Produk' ?></h5>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_produk" value="<?= $edit_mode ? $product_data['id'] : '' ?>">
                    
                    <!-- Upload Area -->
                    <div class="mb-3">
                        <label class="form-label">Foto Produk</label>
                        <div class="upload-area" id="uploadArea">
                            <input type="file" name="image" class="file-input" id="imgInput" onchange="previewImage()" <?= $edit_mode ? '' : 'required' ?>>
                            
                            <img id="preview" src="<?= $edit_mode ? '../assets/img/'.$product_data['image'] : '' ?>" style="display: <?= $edit_mode ? 'block' : 'none' ?>;">
                            
                            <div id="uploadPlaceholder" style="display: <?= $edit_mode ? 'none' : 'block' ?>;">
                                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <p class="mb-0 text-muted small">Klik atau Drag foto ke sini</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" name="name" class="form-control" required value="<?= $edit_mode ? $product_data['name'] : '' ?>" placeholder="Contoh: Sepatu Nike">
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select">
                                <?php 
                                $cats = ['Elektronik', 'Fashion', 'Hobi', 'Makanan'];
                                foreach($cats as $c){
                                    $sel = ($edit_mode && $product_data['category'] == $c) ? 'selected' : '';
                                    echo "<option value='$c' $sel>$c</option>";
                                } ?>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" name="price" class="form-control" required value="<?= $edit_mode ? $product_data['price'] : '' ?>" placeholder="0">
                        </div>
                    </div>

                    <!-- INPUT STOK (BARU) -->
                    <div class="mb-3">
                        <label class="form-label text-danger">Stok Tersedia</label>
                        <input type="number" name="stock" class="form-control border-danger" required value="<?= $edit_mode ? $product_data['stock'] : '' ?>" placeholder="Jumlah stok...">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi singkat..."><?= $edit_mode ? $product_data['description'] : '' ?></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="simpan" class="btn btn-primary py-2 fw-bold rounded-3">
                            <i class="fas fa-save me-1"></i> Simpan Data
                        </button>
                        <?php if($edit_mode): ?>
                            <a href="produk.php" class="btn btn-light text-muted">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABEL DATA (KANAN) -->
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Daftar Produk</h5>
                    <span class="badge bg-primary rounded-pill px-3 py-2">Total: <?php echo mysqli_num_rows(mysqli_query($conn, "SELECT id FROM products")); ?></span>
                </div>

                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th>Harga</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC");
                            $no = 1;
                            while($row = mysqli_fetch_assoc($q)):
                                // Warna stok: Merah jika < 5
                                $stokClass = $row['stock'] < 5 ? 'text-danger fw-bold' : 'text-success fw-bold';
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="../assets/img/<?= $row['image'] ?>" class="product-thumb me-3" onerror="this.src='https://via.placeholder.com/60'">
                                        <div>
                                            <div class="fw-bold text-dark"><?= $row['name'] ?></div>
                                            <small class="text-muted" style="font-size: 0.75rem;">No. <?= $no++ ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge-cat"><?= $row['category'] ?></span></td>
                                
                                <!-- TAMPILAN STOK -->
                                <td class="<?= $stokClass ?>"><?= $row['stock'] ?> Pcs</td>
                                
                                <td class="fw-bold text-primary">Rp <?= number_format($row['price'], 0, ',', '.') ?></td>
                                <td class="text-end">
                                    <a href="produk.php?edit=<?= $row['id'] ?>" class="btn-action btn-edit me-1" title="Edit">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                    <a href="#" onclick="confirmDelete(<?= $row['id'] ?>)" class="btn-action btn-delete" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script JS -->
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

    // Preview Image Script
    function previewImage() {
        const file = document.getElementById('imgInput').files[0];
        const preview = document.getElementById('preview');
        const placeholder = document.getElementById('uploadPlaceholder');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
            }
            reader.readAsDataURL(file);
        }
    }

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

    // Konfirmasi Hapus
    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Produk?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'produk.php?hapus=' + id;
            }
        });
    }
</script>

</body>
</html>