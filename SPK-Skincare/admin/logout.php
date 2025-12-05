<?php
require_once '../includes/functions.php';

// Pastikan admin sudah login
if (!is_admin_logged_in()) {
    redirect('../admin/login.php');
}

// Hapus semua data session
session_unset();

// Hancurkan session
session_destroy();

// Redirect ke halaman login admin
redirect('login.php');
?>