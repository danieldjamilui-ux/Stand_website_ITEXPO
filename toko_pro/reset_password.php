<?php
session_start();
include 'db.php';

// Jika tidak ada session reset_email (belum input email), tendang balik
if (!isset($_SESSION['reset_email'])) {
    header("Location: lupa_password.php");
    exit;
}

if (isset($_POST['reset_pass'])) {
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];
    $email = $_SESSION['reset_email'];

    if ($pass1 === $pass2) {
        // Hash password baru
        $new_password = password_hash($pass1, PASSWORD_DEFAULT);
        
        // Update database
        $update = mysqli_query($conn, "UPDATE users SET password = '$new_password' WHERE email = '$email'");
        
        if ($update) {
            // Hapus session reset
            unset($_SESSION['reset_email']);
            
            // Tampilkan sukses (JavaScript Alert)
            echo "<script>
                alert('Password berhasil diubah! Silakan login dengan password baru.');
                window.location = 'login.php';
            </script>";
        } else {
            $error = "Gagal mengupdate password.";
        }
    } else {
        $error = "Konfirmasi password tidak cocok!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Toko Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f6f9; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .card-reset { width: 100%; max-width: 450px; border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .btn-custom { background: #764ba2; color: white; border: none; padding: 10px; border-radius: 10px; font-weight: 600; transition: 0.3s; }
        .btn-custom:hover { background: #5a3780; color: white; }
    </style>
</head>
<body>

<div class="card card-reset p-4 p-md-5">
    <h3 class="fw-bold text-center mb-4">Buat Password Baru</h3>
    
    <div class="alert alert-info text-center small mb-4">
        Reset password untuk akun: <strong><?= $_SESSION['reset_email'] ?></strong>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger text-center small"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label small fw-bold text-muted">PASSWORD BARU</label>
            <input type="password" name="pass1" class="form-control" required placeholder="Minimal 6 karakter">
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold text-muted">KONFIRMASI PASSWORD</label>
            <input type="password" name="pass2" class="form-control" required placeholder="Ulangi password">
        </div>
        
        <button type="submit" name="reset_pass" class="btn btn-custom w-100">Simpan Password</button>
        <a href="login.php" class="btn btn-link text-muted w-100 text-decoration-none mt-2 small">Batal</a>
    </form>
</div>

</body>
</html>