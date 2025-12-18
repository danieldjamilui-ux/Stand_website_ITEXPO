<?php
session_start();
include '../db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Akses Ditolak");
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];
    
    mysqli_query($conn, "UPDATE orders SET status='$status' WHERE id='$id'");
}

header("Location: index.php");
?>