<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Pastikan admin sudah login
if (!is_admin_logged_in()) {
    redirect('login.php');
}

$success_msg = $error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $name = clean_input($_POST['name']);
    $brand = clean_input($_POST['brand']);
    $size = clean_input($_POST['size']);
    $active_ingredient_type = clean_input($_POST['active_ingredient_type']);
    $release_year = !empty($_POST['release_year']) ? intval($_POST['release_year']) : null;
    
    // Validasi data wajib
    if (empty($name) || empty($brand)) {
        $error_msg = "Nama produk dan brand wajib diisi.";
    } else {
        // Validasi nilai kesesuaian kulit
        $valid_scores = true;
        $error_details = [];
        
        if (isset($_POST['skin_type_compatibility'])) {
            foreach ($_POST['skin_type_compatibility'] as $skin_type_id => $score) {
                if (!is_numeric($score) || $score < 0 || $score > 1) {
                    $valid_scores = false;
                    $error_details[] = "Nilai kesesuaian jenis kulit ID {$skin_type_id} tidak valid";
                }
            }
        }
        
        if (isset($_POST['concern_effectiveness'])) {
            foreach ($_POST['concern_effectiveness'] as $concern_id => $score) {
                if (!is_numeric($score) || $score < 0 || $score > 1) {
                    $valid_scores = false;
                    $error_details[] = "Nilai efektivitas masalah kulit ID {$concern_id} tidak valid";
                }
            }
        }
        
        if (!$valid_scores) {
            $error_msg = "Nilai kesesuaian dan efektivitas harus antara 0.00 dan 1.00. " . implode(', ', $error_details);
        } else {
            try {
                // Mulai transaksi
                $db->beginTransaction();
                
                // Insert produk baru
                $stmt = $db->prepare("INSERT INTO skincare_products (name, brand, size, active_ingredient_type, release_year) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $brand, $size, $active_ingredient_type, $release_year]);
                
                $product_id = $db->lastInsertId();
                
                // Insert kesesuaian jenis kulit
                if (isset($_POST['skin_type_compatibility']) && is_array($_POST['skin_type_compatibility'])) {
                    $stmt = $db->prepare("INSERT INTO product_skin_type_compatibility (product_id, skin_type_id, compatibility_score) VALUES (?, ?, ?)");
                    
                    foreach ($_POST['skin_type_compatibility'] as $skin_type_id => $score) {
                        // Pastikan score dalam format decimal yang benar
                        $formatted_score = number_format((float)$score, 2, '.', '');
                        $stmt->execute([$product_id, intval($skin_type_id), $formatted_score]);
                    }
                }
                
                // Insert efektivitas masalah kulit
                if (isset($_POST['concern_effectiveness']) && is_array($_POST['concern_effectiveness'])) {
                    $stmt = $db->prepare("INSERT INTO product_skin_concern_compatibility (product_id, concern_id, effectiveness_score) VALUES (?, ?, ?)");
                    
                    foreach ($_POST['concern_effectiveness'] as $concern_id => $score) {
                        // Pastikan score dalam format decimal yang benar
                        $formatted_score = number_format((float)$score, 2, '.', '');
                        $stmt->execute([$product_id, intval($concern_id), $formatted_score]);
                    }
                }
                
                // Commit transaksi
                $db->commit();
                
                $success_msg = "Produk berhasil ditambahkan dengan ID: {$product_id}";
                
                // Redirect ke halaman manage dengan pesan sukses
                $_SESSION['success_msg'] = $success_msg;
                redirect('manage_skincare.php');
                
            } catch (PDOException $e) {
                // Rollback transaksi jika terjadi error
                $db->rollBack();
                $error_msg = "Error database: " . $e->getMessage();
                
                // Log error untuk debugging
                error_log("Add Product Error: " . $e->getMessage());
            } catch (Exception $e) {
                // Rollback transaksi jika terjadi error lain
                $db->rollBack();
                $error_msg = "Error: " . $e->getMessage();
                
                // Log error untuk debugging
                error_log("Add Product General Error: " . $e->getMessage());
            }
        }
    }
}

// Jika ada error, redirect kembali dengan pesan error
if (!empty($error_msg)) {
    $_SESSION['error_msg'] = $error_msg;
    redirect('manage_skincare.php');
}

// Jika tidak ada POST request, redirect ke manage_skincare
redirect('manage_skincare.php');
?>