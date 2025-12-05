<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek apakah user sudah login
if (!is_logged_in()) {
    redirect('login.php');
}

$product_id = clean_input($_GET['product_id']);
$product = get_product_details($db, $product_id);



// Dapatkan kesesuaian produk dengan jenis kulit
$skin_type_compatibility = get_product_skin_type_compatibility($db, $product_id);

// Dapatkan kesesuaian produk dengan masalah kulit
$skin_concern_compatibility = get_product_skin_concern_compatibility($db, $product_id);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Detail Produk - Sistem Rekomendasi Skincare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .compatibility-score {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .score-high {
            color: #28a745;
        }
        .score-medium {
            color: #ffc107;
        }
        .score-low {
            color: #dc3545;
        }
        .product-image {
            max-height: 300px;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Kesesuaian dengan Jenis Kulit</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($skin_type_compatibility)): ?>
                            <p class="text-muted">Tidak ada data kesesuaian jenis kulit.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($skin_type_compatibility as $compatibility): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $compatibility['skin_type_name']; ?>
                                        <span class="compatibility-score <?php 
                                            if ($compatibility['compatibility_score'] >= 0.7) echo 'score-high';
                                            else if ($compatibility['compatibility_score'] >= 0.4) echo 'score-medium';
                                            else echo 'score-low';
                                        ?>">
                                            <?php echo $compatibility['compatibility_score']; ?>/1.0
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Efektivitas untuk Masalah Kulit</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($skin_concern_compatibility)): ?>
                            <p class="text-muted">Tidak ada data efektivitas untuk masalah kulit.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($skin_concern_compatibility as $compatibility): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $compatibility['concern_name']; ?>
                                        <span class="compatibility-score <?php 
                                            if ($compatibility['effectiveness_score'] >= 0.7) echo 'score-high';
                                            else if ($compatibility['effectiveness_score'] >= 0.4) echo 'score-medium';
                                            else echo 'score-low';
                                        ?>">
                                            <?php echo $compatibility['effectiveness_score']; ?>/1.0
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>