<?php
session_start();

// Fungsi untuk membersihkan input
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk memeriksa apakah user sudah login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk memeriksa apakah admin sudah login
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

// Fungsi untuk redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fungsi untuk menampilkan pesan error
function display_error($message) {
    return "<div class='alert alert-danger'>$message</div>";
}

// Fungsi untuk menampilkan pesan sukses
function display_success($message) {
    return "<div class='alert alert-success'>$message</div>";
}

// Fungsi untuk mendapatkan data user berdasarkan ID
function get_user_by_id($db, $user_id) {
    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Fungsi untuk mendapatkan data admin berdasarkan ID
function get_admin_by_id($db, $admin_id) {
    $stmt = $db->prepare("SELECT * FROM admin WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    return $stmt->fetch();
}

// Fungsi untuk mendapatkan profil kulit user
function get_user_skin_profile($db, $user_id) {
    $stmt = $db->prepare("SELECT usp.*, st.name as skin_type_name 
                         FROM user_skin_profiles usp 
                         JOIN skin_types st ON usp.skin_type_id = st.skin_type_id 
                         WHERE usp.user_id = ? 
                         ORDER BY usp.created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Fungsi untuk mendapatkan masalah kulit user
function get_user_skin_concerns($db, $profile_id) {
    $stmt = $db->prepare("SELECT usc.*, sc.name as concern_name 
                         FROM user_skin_concerns usc 
                         JOIN skin_concerns sc ON usc.concern_id = sc.concern_id 
                         WHERE usc.profile_id = ?");
    $stmt->execute([$profile_id]);
    return $stmt->fetchAll();
}

// Fungsi untuk mendapatkan semua jenis kulit
function get_all_skin_types($db) {
    $stmt = $db->query("SELECT * FROM skin_types ORDER BY name");
    return $stmt->fetchAll();
}

// Fungsi untuk mendapatkan semua masalah kulit
function get_all_skin_concerns($db) {
    $stmt = $db->query("SELECT * FROM skin_concerns ORDER BY name");
    return $stmt->fetchAll();
}

// Fungsi untuk mendapatkan detail produk
function get_product_details($db, $product_id) {
    $stmt = $db->prepare("SELECT sp.* FROM skincare_products sp WHERE sp.product_id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetch();
}

// Fungsi untuk mendapatkan kesesuaian produk dengan jenis kulit
function get_product_skin_type_compatibility($db, $product_id) {
    $stmt = $db->prepare("SELECT pstc.*, st.name as skin_type_name 
                         FROM product_skin_type_compatibility pstc 
                         JOIN skin_types st ON pstc.skin_type_id = st.skin_type_id 
                         WHERE pstc.product_id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll();
}

// Fungsi untuk mendapatkan kesesuaian produk dengan masalah kulit
function get_product_skin_concern_compatibility($db, $product_id) {
    $stmt = $db->prepare("SELECT pscc.*, sc.name as concern_name 
                         FROM product_skin_concern_compatibility pscc 
                         JOIN skin_concerns sc ON pscc.concern_id = sc.concern_id 
                         WHERE pscc.product_id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll();
}

// Fungsi untuk mendapatkan riwayat rekomendasi user
function get_user_recommendations($db, $user_id) {
    $stmt = $db->prepare("SELECT r.*, COUNT(rd.id) as total_products 
                         FROM recommendations r 
                         JOIN recommendation_details rd ON r.recommendation_id = rd.recommendation_id 
                         WHERE r.user_id = ? 
                         GROUP BY r.recommendation_id 
                         ORDER BY r.created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Fungsi untuk mendapatkan detail rekomendasi
function get_recommendation_details($db, $recommendation_id) {
    $stmt = $db->prepare("SELECT rd.*, sp.name as product_name, sp.image, sp.price, 
                         sp.brand as brand_name, pc.name as category_name 
                         FROM recommendation_details rd 
                         JOIN skincare_products sp ON rd.product_id = sp.product_id 
                         LEFT JOIN product_categories pc ON sp.category_id = pc.category_id 
                         WHERE rd.recommendation_id = ? 
                         ORDER BY rd.rank");
    $stmt->execute([$recommendation_id]);
    return $stmt->fetchAll();
}
?>