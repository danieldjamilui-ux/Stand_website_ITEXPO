<?php

require_once __DIR__ . '/../models/MesinInferensi.php';
require_once __DIR__ . '/../models/Aturan.php';
require_once __DIR__ . '/../models/Gejala.php';
require_once __DIR__ . '/../models/Penyakit.php';
require_once __DIR__ . '/../models/HasilDiagnosis.php';

class PenjelasanController {
    private $db;
    private $mesinInferensi;
    private $aturan;
    private $gejala;
    private $penyakit;
    private $hasilDiagnosis;
    
    public function __construct($database) {
        $this->db = $database;
        $this->mesinInferensi = new MesinInferensi($this->db);
        $this->aturan = new Aturan($this->db);
        $this->gejala = new Gejala($this->db);
        $this->penyakit = new Penyakit($this->db);
        $this->hasilDiagnosis = new HasilDiagnosis();
    }
    
    /**
     * Dapatkan penjelasan detail diagnosis
     * @param array $gejala_terpilih Array ID gejala yang dipilih
     * @param array $cf_user Array nilai CF dari user
     * @return array Penjelasan lengkap proses diagnosis
     */
public function tampilkanPenjelasanDiagnosis($id_konsultasi, $id_pengguna) {
    try {
        // Ambil data konsultasi dengan pengecekan kepemilikan
// In the tampilkanPenjelasanDiagnosis method, update the query:
$query = "SELECT k.id_konsultasi as id, k.id_pengguna as id_user, 
                 k.tanggal_konsultasi, k.cf_hasil_diagnosis as cf_hasil, 
                 k.id_penyakit_hasil, p.nama_penyakit,
                 gp.id_gejala, gp.cf_pengguna as cf_user 
          FROM konsultasi k 
          LEFT JOIN penyakit p ON k.id_penyakit_hasil = p.id_penyakit
          JOIN konsultasi_gejalapilihan gp ON k.id_konsultasi = gp.id_konsultasi 
          WHERE k.id_konsultasi = :id_konsultasi AND k.id_pengguna = :id_pengguna";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_konsultasi', $id_konsultasi);
        $stmt->bindParam(':id_pengguna', $id_pengguna);
        $stmt->execute();
            
            $konsultasi_data = [];
            $gejala_terpilih = [];
            $cf_user = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
if (empty($konsultasi_data)) {
    $konsultasi_data = [
        'id' => $row['id'],
        'id_user' => $row['id_user'],
        'tanggal_konsultasi' => $row['tanggal_konsultasi'],
        'cf_hasil' => $row['cf_hasil'],
        'id_penyakit_hasil' => $row['id_penyakit_hasil'],
        'nama_penyakit' => $row['nama_penyakit'] ?? null
    ];
}
                
                $gejala_terpilih[] = $row['id_gejala'];
                $cf_user[$row['id_gejala']] = $row['cf_user'];
            }
            
            if (empty($konsultasi_data)) {
                return [
                    'success' => false,
                    'message' => 'Konsultasi tidak ditemukan'
                ];
            }
            
            // Dapatkan semua hasil diagnosis
            $semua_hasil = $this->mesinInferensi->getAllHasilDiagnosis($gejala_terpilih, $cf_user);
            
            // Dapatkan detail gejala yang dipilih
            $detail_gejala = [];
            foreach ($gejala_terpilih as $id_gejala) {
                $gejala_data = $this->gejala->findById($id_gejala);
 if ($gejala_data) {
    $cf_user_value = isset($cf_user[$id_gejala]) ? floatval($cf_user[$id_gejala]) : 1.0;
    $detail_gejala[] = [
        'id' => isset($gejala_data['id']) ? $gejala_data['id'] : null,
        'nama_gejala' => isset($gejala_data['nama_gejala']) ? $gejala_data['nama_gejala'] : 'Tidak diketahui',
        'kode_gejala' => isset($gejala_data['kode_gejala']) ? $gejala_data['kode_gejala'] : 'N/A',
        'cf_user' => $cf_user_value,
        'tingkat_keyakinan' => $this->getTingkatKeyakinan($cf_user_value)
    ];
}

            }
            
            // Buat penjelasan untuk setiap penyakit
            $penjelasan_penyakit = [];
            foreach ($semua_hasil as $hasil) {
                $penjelasan_penyakit[] = [
                    'penyakit' => $hasil,
                    'penjelasan_cf' => $this->buatPenjelasanCF($hasil['aturan_terpicu']),
                    'langkah_perhitungan' => $this->buatLangkahPerhitungan($hasil['aturan_terpicu'])
                ];
            }
            
            return [
                'success' => true,
                'data' => [
                    'konsultasi' => $konsultasi_data,
                    'gejala_dipilih' => $detail_gejala,
                    'hasil_diagnosis' => $semua_hasil,
                    'penjelasan_penyakit' => $penjelasan_penyakit,
                    'kesimpulan' => $this->buatKesimpulan($semua_hasil)
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Buat penjelasan perhitungan CF untuk setiap aturan
     * @param array $aturan_terpicu Daftar aturan yang terpicu
     * @return array Penjelasan CF
     */
    private function buatPenjelasanCF($aturan_terpicu) {
        $penjelasan = [];
        
        foreach ($aturan_terpicu as $aturan) {
            $penjelasan[] = [
                'gejala' => $aturan['nama_gejala'],
                'cf_pakar' => $aturan['cf_pakar'],
                'cf_user' => $aturan['cf_user'],
                'cf_rule' => $aturan['cf_rule'],
                'rumus' => "CF(Rule) = CF(Pakar) × CF(User) = {$aturan['cf_pakar']} × {$aturan['cf_user']} = {$aturan['cf_rule']}"
            ];
        }
        
        return $penjelasan;
    }
    
    /**
     * Buat langkah-langkah perhitungan kombinasi CF
     * @param array $aturan_terpicu Daftar aturan yang terpicu
     * @return array Langkah perhitungan
     */
    private function buatLangkahPerhitungan($aturan_terpicu) {
        $langkah = [];
        
        if (count($aturan_terpicu) == 1) {
            $langkah[] = [
                'step' => 1,
                'deskripsi_gejala' => 'Hanya ada satu aturan yang terpicu',
                'rumus' => "CF(Kombinasi) = CF(Rule1) = {$aturan_terpicu[0]['cf_rule']}"
            ];
        } else {
            $cf_combine = $aturan_terpicu[0]['cf_rule'];
            $langkah[] = [
                'step' => 1,
                'deskripsi_gejala' => 'Inisialisasi dengan CF aturan pertama',
                'rumus' => "CF(Kombinasi) = {$cf_combine}"
            ];
            
            for ($i = 1; $i < count($aturan_terpicu); $i++) {
                $cf_old = $cf_combine;
                $cf_new = $aturan_terpicu[$i]['cf_rule'];
                $cf_combine = $cf_old + $cf_new * (1 - $cf_old);
                
                $langkah[] = [
                    'step' => $i + 1,
                    'deskripsi_gejala' => "Kombinasi dengan aturan ke-" . ($i + 1),
                    'rumus' => "CF(Kombinasi) = {$cf_old} + {$cf_new} × (1 - {$cf_old}) = {$cf_combine}"
                ];
            }
        }
        
        return $langkah;
    }
    
    /**
     * Buat kesimpulan diagnosis
     * @param array $semua_hasil Semua hasil diagnosis
     * @return array Kesimpulan
     */
    private function buatKesimpulan($semua_hasil) {
        if (empty($semua_hasil)) {
            return [
                'status' => 'tidak_ada_diagnosis',
                'pesan' => 'Tidak ada penyakit yang dapat didiagnosis berdasarkan gejala yang dipilih.'
            ];
        }
        
        $hasil_terbaik = $semua_hasil[0];
        $persentase = round($hasil_terbaik['cf_hasil'] * 100, 2);
        
        $status = 'rendah';
        $rekomendasi = 'Disarankan untuk berkonsultasi dengan dokter mata untuk pemeriksaan lebih lanjut.';
        
        if ($persentase >= 80) {
            $status = 'sangat_tinggi';
            $rekomendasi = 'Sangat disarankan untuk segera berkonsultasi dengan dokter mata.';
        } elseif ($persentase >= 60) {
            $status = 'tinggi';
            $rekomendasi = 'Disarankan untuk berkonsultasi dengan dokter mata dalam waktu dekat.';
        } elseif ($persentase >= 40) {
            $status = 'sedang';
            $rekomendasi = 'Pantau gejala dan pertimbangkan untuk berkonsultasi dengan dokter mata.';
        }
        
        return [
            'status' => $status,
            'penyakit_terdiagnosis' => $hasil_terbaik['nama_penyakit'],
            'tingkat_keyakinan' => $persentase,
            'kategori_keyakinan' => $this->mesinInferensi->kategoriKeyakinan($hasil_terbaik['cf_hasil']),
            'jumlah_gejala_cocok' => $hasil_terbaik['gejala_cocok'],
            'rekomendasi' => $rekomendasi,
            'pesan' => "Berdasarkan gejala yang Anda pilih, sistem mendiagnosis kemungkinan {$hasil_terbaik['nama_penyakit']} dengan tingkat keyakinan {$persentase}%."
        ];
    }
    
    /**
     * Dapatkan tingkat keyakinan dalam bentuk teks
     * @param float $cf_value Nilai CF
     * @return string Tingkat keyakinan
     */
    private function getTingkatKeyakinan($cf_value) {
        if ($cf_value >= 0.8) {
            return 'Sangat Yakin';
        } elseif ($cf_value >= 0.6) {
            return 'Yakin';
        } elseif ($cf_value >= 0.4) {
            return 'Cukup Yakin';
        } elseif ($cf_value >= 0.2) {
            return 'Kurang Yakin';
        } else {
            return 'Tidak Yakin';
        }
    }
    
    /**
     * Dapatkan penjelasan tentang metode Certainty Factor
     * @return array Penjelasan metode CF
     */
    public function getPenjelasanMetode() {
        return [
            'success' => true,
            'data' => [
                'nama_metode' => 'Certainty Factor (CF)',
                'deskripsi_gejala' => 'Certainty Factor adalah metode untuk menangani ketidakpastian dalam sistem pakar dengan menggunakan nilai kepercayaan.',
                'rumus_cf_rule' => 'CF(Rule) = CF(Pakar) × CF(User)',
                'rumus_kombinasi' => 'CF(Kombinasi) = CF1 + CF2 × (1 - CF1)',
                'rentang_nilai' => 'Nilai CF berkisar antara -1 hingga +1',
                'interpretasi' => [
                    '+1' => 'Sangat yakin benar',
                    '+0.8 hingga +0.99' => 'Yakin benar',
                    '+0.6 hingga +0.79' => 'Cukup yakin benar',
                    '+0.2 hingga +0.59' => 'Mungkin benar',
                    '+0.01 hingga +0.19' => 'Sedikit yakin benar',
                    '0' => 'Tidak tahu',
                    '-0.01 hingga -0.19' => 'Sedikit yakin salah',
                    '-0.2 hingga -0.59' => 'Mungkin salah',
                    '-0.6 hingga -0.79' => 'Cukup yakin salah',
                    '-0.8 hingga -0.99' => 'Yakin salah',
                    '-1' => 'Sangat yakin salah'
                ]
            ]
        ];
    }
}

?>