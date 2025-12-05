<?php
require_once '../includes/functions.php';

// Redirect ke halaman login admin jika belum login
if (!is_admin_logged_in()) {
    redirect('login.php');
} else {
    // Jika sudah login, redirect ke dashboard
    redirect('dashboard.php');
}
?>