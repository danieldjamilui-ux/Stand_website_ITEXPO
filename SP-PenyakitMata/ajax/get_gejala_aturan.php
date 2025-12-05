<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AturanController.php';

// Check if user is logged in
if (!isset($_SESSION['id_pengguna'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if id_aturan is provided
if (!isset($_GET['id_aturan']) || empty($_GET['id_aturan'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID Aturan tidak ditemukan']);
    exit;
}

try {
    $aturanController = new AturanController();
    $result = $aturanController->getGejalaByAturan($_GET['id_aturan']);
    
    header('Content-Type: application/json');
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}
?>