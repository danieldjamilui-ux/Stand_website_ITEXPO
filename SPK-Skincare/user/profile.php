<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Cek apakah user sudah login
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($db, $user_id);
$skin_profile = get_user_skin_profile($db, $user_id);
$error = '';
$success = '';

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $skin_type_id = clean_input($_POST["skin_type_id"]);
    $concerns = isset($_POST["concerns"]) ? $_POST["concerns"] : [];
    $severities = isset($_POST["severities"]) ? $_POST["severities"] : [];
    
    // Validasi input
    if (empty($skin_type_id)) {
        $error = "Pilih jenis kulit Anda.";
    } else {
        // Validasi masalah kulit - maksimal 8 dan tidak boleh duplikasi
        $valid_concerns = [];
        $used_concerns = [];
        
        foreach ($concerns as $key => $concern_id) {
            if (!empty($concern_id)) {
                if (in_array($concern_id, $used_concerns)) {
                    $error = "Tidak boleh memilih masalah kulit yang sama.";
                    break;
                }
                if (count($valid_concerns) >= 8) {
                    $error = "Maksimal hanya 8 masalah kulit yang dapat dipilih.";
                    break;
                }
                $used_concerns[] = $concern_id;
                $valid_concerns[] = [
                    'concern_id' => $concern_id,
                    'severity' => isset($severities[$key]) ? $severities[$key] : 1
                ];
            }
        }
        
        if (empty($error)) {
            try {
                $db->beginTransaction();
                
                // Simpan profil kulit
                $stmt = $db->prepare("INSERT INTO user_skin_profiles (user_id, skin_type_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $skin_type_id]);
                $profile_id = $db->lastInsertId();
                
                // Simpan masalah kulit
                foreach ($valid_concerns as $concern) {
                    $stmt = $db->prepare("INSERT INTO user_skin_concerns (profile_id, concern_id, severity) VALUES (?, ?, ?)");
                    $stmt->execute([$profile_id, $concern['concern_id'], $concern['severity']]);
                }
                
                $db->commit();
                $success = "Profil kulit berhasil disimpan.";
                $skin_profile = get_user_skin_profile($db, $user_id); // Refresh data
                
                // Redirect ke halaman rekomendasi
                redirect("recommendation.php?profile_id=" . $profile_id);
            } catch (Exception $e) {
                $db->rollBack();
                $error = "Terjadi kesalahan: " . $e->getMessage();
            }
        }
    }
}

// Ambil semua jenis kulit
$skin_types = get_all_skin_types($db);

// Ambil semua masalah kulit
$skin_concerns = get_all_skin_concerns($db);

// Ambil masalah kulit user jika ada profil
$user_concerns = [];
if ($skin_profile) {
    $profile_id = $skin_profile['profile_id'];
    $user_concerns = get_user_skin_concerns($db, $profile_id);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Kulit - Sistem Rekomendasi Skincare</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- <link rel="stylesheet" href="../assets/css/style.css"> -->
    <style>
        /* Membuat footer selalu di bawah */
        html, body {
            height: 100%;
        }
        
        body {
            display: flex;
            flex-direction: column;
        }
        
        .main-content {
            flex: 1;
        }
        
        footer {
            margin-top: auto;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="main-content">
        <div class="container mt-5">
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Profil Pengguna</h5>
                        </div>
                        <div class="card-body">
                            <h5><?php echo $user['name']; ?></h5>
                            <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
                            <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                            
                            <?php if ($skin_profile): ?>
                            <div class="mt-3">
                                <h6>Profil Kulit Saat Ini:</h6>
                                <p><strong>Jenis Kulit:</strong> <?php echo $skin_profile['skin_type_name']; ?></p>
                                
                                <?php if (!empty($user_concerns)): ?>
                                <p><strong>Masalah Kulit:</strong></p>
                                <ul>
                                    <?php foreach ($user_concerns as $concern): ?>
                                    <li><?php echo $concern['concern_name']; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                                
                                <a href="recommendation.php?profile_id=<?php echo $skin_profile['profile_id']; ?>" class="btn btn-success btn-sm">Lihat Rekomendasi</a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Kelola Profil Kulit</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($error)): ?>
                                <?php echo display_error($error); ?>
                            <?php endif; ?>
                            
                            <?php if (!empty($success)): ?>
                                <?php echo display_success($success); ?>
                            <?php endif; ?>
                            
                            <form method="post" action="">
                                <div class="form-group">
                                    <label for="skin_type_id"><strong>Jenis Kulit</strong></label>
                                    <select class="form-control" id="skin_type_id" name="skin_type_id" required>
                                        <option value="">Pilih Jenis Kulit</option>
                                        <?php foreach ($skin_types as $type): ?>
                                        <option value="<?php echo $type['skin_type_id']; ?>">
                                            <?php echo $type['name']; ?> - <?php echo $type['description']; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label><strong>Masalah Kulit</strong></label>
                                    <p class="text-muted small">Pilih maksimal 8 masalah kulit yang Anda alami dan tentukan tingkat keparahannya (1-5). Tidak boleh memilih masalah kulit yang sama.</p>
                                    
                                    <div id="concerns-container">
                                        <div class="row mb-2 concern-row">
                                            <div class="col-md-8">
                                                <select class="form-control concern-select" name="concerns[]">
                                                    <option value="">Pilih Masalah Kulit</option>
                                                    <?php foreach ($skin_concerns as $concern): ?>
                                                    <option value="<?php echo $concern['concern_id']; ?>">
                                                        <?php echo $concern['name']; ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button" class="btn btn-sm btn-danger remove-concern">X</button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="button" id="add-concern" class="btn btn-sm btn-secondary mt-2">Tambah Masalah Kulit</button>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Simpan & Dapatkan Rekomendasi</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Fungsi untuk mengecek duplikasi
        function checkDuplicates() {
            var selectedValues = [];
            var hasDuplicate = false;
            
            $('.concern-select').each(function() {
                var value = $(this).val();
                if (value && selectedValues.includes(value)) {
                    hasDuplicate = true;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                    if (value) selectedValues.push(value);
                }
            });
            
            return hasDuplicate;
        }
        
        // Tambah baris masalah kulit
        $('#add-concern').click(function() {
            // Cek apakah sudah mencapai batas maksimal (8 baris)
            if ($('.concern-row').length < 8) {
                var newRow = $('.concern-row:first').clone();
                newRow.find('select').val('');
                newRow.find('.concern-select').removeClass('is-invalid');
                $('#concerns-container').append(newRow);
            } else {
                alert('Maksimal hanya 8 masalah kulit yang dapat ditambahkan.');
            }
        });
        
        // Hapus baris masalah kulit
        $(document).on('click', '.remove-concern', function() {
            if ($('.concern-row').length > 1) {
                $(this).closest('.concern-row').remove();
                checkDuplicates();
            }
        });
        
        // Cek duplikasi saat memilih masalah kulit
        $(document).on('change', '.concern-select', function() {
            checkDuplicates();
        });
        
        // Validasi sebelum submit
        $('form').submit(function(e) {
            if (checkDuplicates()) {
                e.preventDefault();
                alert('Tidak boleh memilih masalah kulit yang sama!');
                return false;
            }
        });
    });
    </script>
</body>
</html>