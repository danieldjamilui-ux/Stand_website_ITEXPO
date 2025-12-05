<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-white py-3">
        <div class="container">
            <a class="navbar-brand" href="<?php echo is_admin_logged_in() ? '/SPK-Skincare/admin/dashboard.php' : '/SPK-Skincare/index.php'; ?>">
                <img src="../assets/images/Logo.png" alt="Logo" height="40" class="mr-2">
                BeautiFi
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <?php if (is_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../user/dashboard.php"><i class="fas fa-home"></i> Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../user/profile.php">Profil Kulit</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../user/recommendation.php">Rekomendasi</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php echo $_SESSION['name']; ?>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="/SPK-Skincare/user/logout.php" data-confirm="Apakah Anda yakin ingin keluar?">Logout</a>
                            </div>
                        </li>
                    <?php elseif (is_admin_logged_in()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/SPK-Skincare/admin/dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/SPK-Skincare/admin/manage_skincare.php">Kelola Produk</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/SPK-Skincare/admin/manage_users.php">Kelola User</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php echo $_SESSION['admin_name']; ?>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="/SPK-Skincare/admin/logout.php" data-confirm="Apakah Anda yakin ingin keluar?">Logout</a>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/SPK-Skincare/user/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/SPK-Skincare/user/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>