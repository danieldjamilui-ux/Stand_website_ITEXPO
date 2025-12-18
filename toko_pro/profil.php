<?php
session_start();
include 'db.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// Ambil Data User
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$uid'");
$u = mysqli_fetch_assoc($query);

// --- LOGIKA UPDATE PROFIL ---
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Update DB
    $update = mysqli_query($conn, "UPDATE users SET name='$name', email='$email' WHERE id='$uid'");
    if ($update) {
        $_SESSION['name'] = $name;
        $success_msg = "Profil berhasil diperbarui!";
        $u['name'] = $name;
        $u['email'] = $email;
    } else {
        $error_msg = "Gagal mengupdate profil.";
    }
}

// --- LOGIKA GANTI PASSWORD ---
if (isset($_POST['change_pass'])) {
    $old_pass = $_POST['old_pass'];
    $new_pass = $_POST['new_pass'];
    $confirm_pass = $_POST['confirm_pass'];

    if (password_verify($old_pass, $u['password'])) {
        if ($new_pass == $confirm_pass) {
            $hash_new = password_hash($new_pass, PASSWORD_DEFAULT);
            mysqli_query($conn, "UPDATE users SET password='$hash_new' WHERE id='$uid'");
            $success_msg = "Password berhasil diubah!";
        } else {
            $error_msg = "Konfirmasi password baru tidak cocok.";
        }
    } else {
        $error_msg = "Password lama salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - SHOP COMMERCE</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
        }

        /* Navbar */
        .navbar-glass {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
        }
        .navbar-brand {
            font-weight: 800; font-size: 1.5rem;
            background: -webkit-linear-gradient(45deg, #667eea, #764ba2);
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* --- CARD PROFILE --- */
        .card-profile {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            overflow: hidden;
            background: white;
            transition: 0.3s;
        }
        .card-profile:hover { transform: translateY(-5px); }

        /* --- HEADER ANIMASI (GRADIENT) --- */
        .profile-header {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            padding: 60px 20px;
            text-align: center;
            color: white;
            position: relative;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* --- AVATAR --- */
        .avatar-box {
            width: 120px; height: 120px;
            background: white;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 3.5rem; font-weight: 800; color: #764ba2;
            margin: 0 auto 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            border: 5px solid rgba(255,255,255,0.3);
            position: relative;
            z-index: 2;
        }

        /* --- TABS MODERN --- */
        .nav-pills .nav-link {
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            color: #6c757d;
            background: #f8f9fa;
            margin: 0 5px;
            transition: 0.3s;
        }
        .nav-pills .nav-link.active {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
            box-shadow: 0 5px 15px rgba(118, 75, 162, 0.3);
        }

        /* --- FORM --- */
        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            border: 1px solid #e9ecef;
            background-color: #fcfcfc;
            transition: 0.3s;
        }
        .form-control:focus {
            background-color: #fff;
            border-color: #764ba2;
            box-shadow: 0 0 0 4px rgba(118, 75, 162, 0.1);
        }

        .btn-save {
            background: linear-gradient(to right, #667eea, #764ba2);
            border: none; color: white;
            padding: 12px; border-radius: 12px;
            font-weight: 700; width: 100%;
            transition: 0.3s;
            box-shadow: 0 10px 20px rgba(118, 75, 162, 0.2);
        }
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(118, 75, 162, 0.3);
            color: white;
        }

        .btn-warning-custom {
            background: #ff9f43; color: white; border: none;
            padding: 12px; border-radius: 12px; font-weight: 700; width: 100%;
            transition: 0.3s;
        }
        .btn-warning-custom:hover { background: #e67e22; color: white; }

        /* Alert Animation */
        .alert-float { animation: fadeInDown 0.5s ease-out; border-radius: 12px; border: none; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-glass sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-shopping-bag me-2"></i>SHOP COMMERCE</a>
        <div class="ms-auto">
            <a href="index.php" class="btn btn-outline-dark rounded-pill px-4 btn-sm fw-bold border-2">
                <i class="fas fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 animate__animated animate__fadeInUp">
            
            <?php if($success_msg): ?>
                <div class="alert alert-success alert-float mb-4 shadow-sm">
                    <i class="fas fa-check-circle me-2"></i> <?= $success_msg ?>
                </div>
            <?php endif; ?>
            
            <?php if($error_msg): ?>
                <div class="alert alert-danger alert-float mb-4 shadow-sm">
                    <i class="fas fa-exclamation-circle me-2"></i> <?= $error_msg ?>
                </div>
            <?php endif; ?>

            <div class="card-profile">
                <!-- Header Profil -->
                <div class="profile-header">
                    <div class="avatar-box animate__animated animate__zoomIn animate__delay-1s">
                        <?= strtoupper(substr($u['name'], 0, 1)) ?>
                    </div>
                    <h3 class="fw-bold mb-1"><?= $u['name'] ?></h3>
                    <p class="mb-2 opacity-75 small"><?= $u['email'] ?></p>
                    <?php if ($u['role'] == 'admin'): ?>
    <!-- TAMPILAN JIKA ADMIN -->
    <span class="badge bg-danger text-white rounded-pill px-3 py-2 shadow-sm">
        <i class="fas fa-user-shield me-1"></i> Administrator
    </span>
<?php else: ?>
    <!-- TAMPILAN JIKA USER -->
    <span class="badge bg-warning text-dark rounded-pill px-3 py-2 shadow-sm">
        <i class="fas fa-star me-1"></i> Member
    </span>
<?php endif; ?>
                </div>

                <div class="p-4 p-md-5">
                    
                    <!-- Tabs -->
                    <ul class="nav nav-pills mb-4 justify-content-center" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button"><i class="fas fa-user-edit me-2"></i>Edit Profil</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button"><i class="fas fa-key me-2"></i>Keamanan</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="pills-tabContent">
                        
                        <!-- TAB 1: EDIT PROFIL -->
                        <div class="tab-pane fade show active animate__animated animate__fadeIn" id="pills-home">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted">NAMA LENGKAP</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fas fa-user text-muted"></i></span>
                                        <input type="text" name="name" class="form-control border-start-0 ps-0" value="<?= $u['name'] ?>" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-muted">ALAMAT EMAIL</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 rounded-start-3"><i class="fas fa-envelope text-muted"></i></span>
                                        <input type="email" name="email" class="form-control border-start-0 ps-0" value="<?= $u['email'] ?>" required>
                                    </div>
                                </div>
                                <button type="submit" name="update_profile" class="btn-save">
                                    Simpan Perubahan <i class="fas fa-check ms-2"></i>
                                </button>
                            </form>
                        </div>

                        <!-- TAB 2: GANTI PASSWORD -->
                        <div class="tab-pane fade animate__animated animate__fadeIn" id="pills-profile">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-muted">PASSWORD LAMA</label>
                                    <input type="password" name="old_pass" class="form-control" required placeholder="Masukkan password saat ini">
                                </div>
                                <hr class="my-4">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold small text-muted">PASSWORD BARU</label>
                                        <input type="password" name="new_pass" class="form-control" required placeholder="Minimal 6 karakter">
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <label class="form-label fw-bold small text-muted">ULANGI PASSWORD</label>
                                        <input type="password" name="confirm_pass" class="form-control" required placeholder="Konfirmasi password baru">
                                    </div>
                                </div>
                                <button type="submit" name="change_pass" class="btn-warning-custom">
                                    Update Password <i class="fas fa-lock ms-2"></i>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>