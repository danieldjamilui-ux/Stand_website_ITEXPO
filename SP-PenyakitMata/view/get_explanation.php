<?php
session_start();

if (!isset($_SESSION['id_pengguna']) || empty($_SESSION['id_pengguna'])) {
    echo '<div class="alert alert-danger">Akses ditolak. Silakan login terlebih dahulu.</div>';
    exit();
}

require_once '../config/database.php';
require_once '../controllers/PenjelasanController.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    if (!$db) {
        throw new Exception('Koneksi database gagal');
    }
    
    $penjelasanController = new PenjelasanController($db);
    $id_konsultasi = $_GET['id'] ?? 0;

    if ($id_konsultasi <= 0) {
        throw new Exception('ID Konsultasi tidak valid');
    }

    $result = $penjelasanController->tampilkanPenjelasanDiagnosis($id_konsultasi, $_SESSION['id_pengguna']);
    
    if (!$result['success']) {
        echo '<div class="alert alert-danger">'.htmlspecialchars($result['message']).'</div>';
        exit();
    }

    if (empty($result['data'])) {
        echo '<div class="alert alert-warning">Data penjelasan tidak tersedia untuk konsultasi ini.</div>';
        exit();
    }
    
    $penjelasan = $result['data'];
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Terjadi kesalahan: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit();
}
?>

<div class="explanation-content">
    <!-- Informasi Konsultasi -->
    <div class="mb-4">
        <h6><i class="fas fa-info-circle me-2"></i>Informasi Konsultasi</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <tr>
                    <td><strong>ID Konsultasi:</strong></td>
                    <td>#<?php echo $penjelasan['konsultasi']['id']; ?></td>
                </tr>
                <tr>
                    <td><strong>Tanggal:</strong></td>
                    <td><?php echo date('d F Y, H:i:s', strtotime($penjelasan['konsultasi']['tanggal_konsultasi'])); ?></td>
                </tr>
                <tr>
                    <td><strong>Hasil Diagnosis:</strong></td>
                    <td>
                        <?php if ($penjelasan['konsultasi']['nama_penyakit']): ?>
                            <span class="badge bg-success"><?php echo $penjelasan['konsultasi']['nama_penyakit']; ?></span>
                        <?php else: ?>
                            <span class="badge bg-warning">Tidak Terdiagnosis</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($penjelasan['konsultasi']['cf_hasil']): ?>
                <tr>
                    <td><strong>Tingkat Keyakinan:</strong></td>
                    <td>
                        <span class="badge bg-info"><?php echo number_format($penjelasan['konsultasi']['cf_hasil'] * 100, 2); ?>%</span>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- Gejala yang Dipilih -->
    <div class="mb-4">
        <h6><i class="fas fa-symptoms me-2"></i>Gejala yang Dipilih</h6>
        <div class="row">
            <?php if (!empty($penjelasan['gejala_dipilih'])): ?>
                <?php foreach ($penjelasan['gejala_dipilih'] as $gejala): ?>
                    <div class="col-md-6 mb-2">
                        <div class="card card-body">
                            <small><strong><?= htmlspecialchars($gejala['nama_gejala'] ?? 'Nama gejala tidak tersedia'); ?></strong></small>
                            <small class="text-muted">
                                <?php 
                                // Debug: Tampilkan semua key yang tersedia
                                // echo '<pre>'; print_r(array_keys($gejala)); echo '</pre>';
                                
                                // Cek berbagai kemungkinan key untuk deskripsi
                                $deskripsi = '';
                                if (isset($gejala['deskripsi_gejala']) && !empty($gejala['deskripsi_gejala'])) {
                                    $deskripsi = $gejala['deskripsi_gejala'];
                                } elseif (isset($gejala['deskripsi_gejala']) && !empty($gejala['deskripsi_gejala'])) {
                                    $deskripsi = $gejala['deskripsi_gejala'];
                                } elseif (isset($gejala['keterangan']) && !empty($gejala['keterangan'])) {
                                    $deskripsi = $gejala['keterangan'];
                                } else {
                                    $deskripsi = 'Deskripsi tidak tersedia';
                                }
                                echo htmlspecialchars($deskripsi);
                                ?>
                            </small>
                            <span class="badge bg-primary mt-1">
                                CF User: <?= isset($gejala['cf_pengguna']) && $gejala['cf_pengguna'] !== null ? htmlspecialchars($gejala['cf_pengguna']) : 'N/A'; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">Tidak ada data gejala yang dipilih.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($penjelasan['konsultasi']['nama_penyakit']): ?>
    <!-- Proses Diagnosis -->
    <div class="mb-4">
        <h6><i class="fas fa-search me-2"></i>Proses Diagnosis</h6>
        <p>Sistem menganalisis gejala yang Anda pilih dan membandingkannya dengan basis pengetahuan penyakit mata. Berikut proses yang terjadi:</p>
        <ol>
            <li>Sistem mencari semua penyakit yang memiliki gejala sesuai dengan pilihan Anda</li>
            <li>Menghitung nilai Certainty Factor (CF) untuk setiap penyakit</li>
            <li>Menentukan penyakit dengan nilai CF tertinggi sebagai hasil diagnosis</li>
        </ol>
    </div>

    <!-- Langkah Perhitungan -->
    <div class="mb-4">
        <h6><i class="fas fa-calculator me-2"></i>Langkah Perhitungan CF</h6>
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle me-2"></i>Rumus Certainty Factor:</h6>
            <p><strong>CF(H,E) = CF(E) × CF(H,E)</strong></p>
            <ul>
                <li><strong>CF(E)</strong>: Nilai keyakinan pengguna terhadap gejala (0-1)</li>
                <li><strong>CF(H,E)</strong>: Nilai keyakinan pakar bahwa gejala menyebabkan penyakit (0-1)</li>
                <li><strong>CF Kombinasi</strong>: CF1 + CF2 × (1 - CF1) untuk gejala yang sama pada penyakit yang sama</li>
            </ul>
        </div>
        <div class="card card-body">
            <?php 
            if (isset($penjelasan['langkah_perhitungan']) && !empty($penjelasan['langkah_perhitungan'])) {
                // Jika langkah_perhitungan adalah array
                if (is_array($penjelasan['langkah_perhitungan'])) {
                    foreach ($penjelasan['langkah_perhitungan'] as $langkah) {
                        if (is_array($langkah)) {
                            echo '<div class="mb-3">';
                            foreach ($langkah as $key => $value) {
                                echo '<strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($value) . '<br>';
                            }
                            echo '</div>';
                        } else {
                            echo '<div class="mb-2">' . htmlspecialchars($langkah) . '</div>';
                        }
                    }
                } 
                // Jika langkah_perhitungan adalah string HTML
                else {
                    echo $penjelasan['langkah_perhitungan'];
                }
            } else {
                echo '<em>Tidak ada data langkah perhitungan.</em>';
            }
            ?>
        </div>
    </div>

    <!-- Kesimpulan -->
    <div class="mb-4">
        <h6><i class="fas fa-clipboard-check me-2"></i>Kesimpulan Diagnosis</h6>
        <div class="card bg-light">
            <div class="card-body">
                <p>Berdasarkan perhitungan Certainty Factor, sistem menyimpulkan bahwa Anda mungkin mengalami:</p>
                <h5 class="text-success"><?php echo $penjelasan['konsultasi']['nama_penyakit']; ?></h5>
                <p>dengan tingkat keyakinan <strong><?php echo number_format($penjelasan['konsultasi']['cf_hasil'] * 100, 2); ?>%</strong>.</p>
                <?php 
                if (isset($penjelasan['kesimpulan']) && !empty($penjelasan['kesimpulan'])) {
                    // Jika kesimpulan adalah array
                    if (is_array($penjelasan['kesimpulan'])) {
                        foreach ($penjelasan['kesimpulan'] as $item) {
                            if (is_array($item)) {
                                echo '<div class="mb-2">';
                                foreach ($item as $key => $value) {
                                    echo '<strong>' . htmlspecialchars($key) . ':</strong> ' . htmlspecialchars($value) . '<br>';
                                }
                                echo '</div>';
                            } else {
                                echo '<p>' . htmlspecialchars($item) . '</p>';
                            }
                        }
                    } 
                    // Jika kesimpulan adalah string HTML
                    else {
                        echo $penjelasan['kesimpulan'];
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Saran -->
    <div class="mb-4">
        <h6><i class="fas fa-lightbulb me-2"></i>Saran dan Rekomendasi</h6>
        <div class="alert alert-warning">
            <p><strong>Perhatian:</strong> Hasil diagnosis ini bersifat prediktif berdasarkan sistem pakar. Untuk diagnosis yang lebih akurat, disarankan:</p>
            <ul>
                <li>Melakukan konsultasi langsung dengan dokter spesialis mata</li>
                <li>Menjelaskan semua gejala yang Anda rasakan secara detail</li>
                <li>Melakukan pemeriksaan penunjang jika diperlukan</li>
            </ul>
        </div>
    </div>

    <!-- Penjelasan Metode CF -->
    <div class="mb-4">
        <h6><i class="fas fa-question-circle me-2"></i>Tentang Metode Certainty Factor</h6>
        <div class="card bg-light">
            <div class="card-body">
                <p>Certainty Factor (CF) adalah metode untuk menangani ketidakpastian dalam sistem pakar. Metode ini menggabungkan:</p>
                <ul>
                    <li><strong>Keyakinan Pakar (CF Pakar):</strong> Nilai yang diberikan pakar tentang seberapa kuat gejala mengindikasikan suatu penyakit</li>
                    <li><strong>Keyakinan Pengguna (CF User):</strong> Nilai yang Anda berikan tentang seberapa yakin Anda mengalami gejala tersebut</li>
                </ul>
                <p>Nilai CF berkisar antara -1 (sangat tidak yakin) sampai 1 (sangat yakin). Nilai positif menunjukkan keyakinan terhadap hipotesis, sedangkan nilai negatif menunjukkan ketidakpercayaan.</p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Jika tidak terdiagnosis -->
    <div class="mb-4">
        <h6><i class="fas fa-exclamation-triangle me-2"></i>Hasil Diagnosis</h6>
        <div class="alert alert-warning">
            <h5 class="mb-3">Tidak Dapat Menentukan Diagnosis</h5>
            <p>Sistem tidak dapat menentukan diagnosis karena:</p>
            <ul>
                <li>Gejala yang dipilih tidak cukup spesifik untuk mengindikasikan penyakit tertentu</li>
                <li>Nilai Certainty Factor terlalu rendah untuk semua kemungkinan penyakit</li>
            </ul>
            <p class="mb-0"><strong>Saran:</strong> Silakan mencoba konsultasi lagi dengan memilih gejala yang lebih spesifik atau konsultasikan langsung dengan dokter.</p>
        </div>
    </div>
    <?php endif; ?>
</div>