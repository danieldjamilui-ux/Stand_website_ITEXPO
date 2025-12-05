<?php
require_once '../includes/functions.php';

// Pastikan user sudah login
if (!is_logged_in()) {
    redirect('login.php');
}

// Hapus semua data session
session_unset();

// Hancurkan session
session_destroy();

// Redirect ke halaman login
redirect('login.php');
?>