<?php
class MesinInferensi {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Proses diagnosis menggunakan algoritma Certainty Factor
     * @param array $gejala_terpilih Array ID gejala yang dipilih
     * @param array $cf_user Array nilai CF dari user
     * @return HasilDiagnosis Hasil diagnosis dengan CF tertinggi
     */
    public function prosesDiagnosis($gejala_terpilih, $cf_user) {
        if (empty($gejala_terpilih)) {
            return null;
        }
        
        // Ambil semua penyakit
        $penyakit_list = $this->getAllPenyakit();
        $hasil_cf = [];
        
        foreach ($penyakit_list as $penyakit) {
            $cf_combine = 0;
            $cf_values = [];
            $aturan_terpicu = [];
            
            // Ambil aturan untuk penyakit ini
            $aturan_list = $this->getAturanByPenyakit($penyakit['id_penyakit']);
            
            foreach ($aturan_list as $aturan) {
                if (in_array($aturan['id_gejala'], $gejala_terpilih)) {
                    $cf_user_value = isset($cf_user[$aturan['id_gejala']]) ? floatval($cf_user[$aturan['id_gejala']]) : 1.0;
                    $cf_rule = $aturan['cf_pakar'] * $cf_user_value;
                    $cf_values[] = $cf_rule;
                    
                    $aturan_terpicu[] = $aturan['id_aturan'];
                }
            }
            
            // Kombinasi CF menggunakan rumus CF Combine
            if (!empty($cf_values)) {
                $cf_combine = $this->kombinasiCF($cf_values);
                
                // Buat objek HasilDiagnosis
                $hasilDiagnosis = new HasilDiagnosis();
                $hasilDiagnosis->setPenyakit($penyakit);
                $hasilDiagnosis->setCfHasil($cf_combine);
                $hasilDiagnosis->setDeskripsiPenyakit($penyakit['deskripsi_penyakit']);
                $hasilDiagnosis->setSolusiPenyakit($penyakit['solusi_penyakit']);
                
                $hasil_cf[] = [
                    'hasil_diagnosis' => $hasilDiagnosis,
                    'cf_hasil' => $cf_combine,
                    'aturan_terpicu' => $aturan_terpicu
                ];
            }
        }
        
        // Urutkan berdasarkan CF tertinggi
        usort($hasil_cf, function($a, $b) {
            return $b['cf_hasil'] <=> $a['cf_hasil'];
        });
        
        return !empty($hasil_cf) ? $hasil_cf[0]['hasil_diagnosis'] : null;
    }
    
    /**
     * Mendapatkan aturan yang terpicu berdasarkan gejala yang dipilih
     * @param array $listGejalaPilihan Array objek GejalaPilihan
     * @return array Daftar aturan yang terpicu
     */
    public function getAturanTerpicu($listGejalaPilihan) {
        if (empty($listGejalaPilihan)) {
            return [];
        }
        
        // Ekstrak ID gejala
        $gejala_ids = [];
        foreach ($listGejalaPilihan as $gejala) {
            $gejala_ids[] = $gejala->getGejala()->getIdGejala();
        }
        
        // Query untuk mendapatkan aturan yang terpicu
        $placeholders = implode(',', array_fill(0, count($gejala_ids), '?'));
        $query = "SELECT a.*, p.nama_penyakit, g.nama_gejala 
                 FROM aturan a 
                 JOIN penyakit p ON a.id_penyakit_hasil = p.id_penyakit 
                 JOIN aturan_gejala ag ON a.id_aturan = ag.id_aturan 
                 JOIN gejala g ON ag.id_gejala = g.id_gejala 
                 WHERE ag.id_gejala IN ($placeholders)";
        
        $stmt = $this->db->prepare($query);
        foreach ($gejala_ids as $index => $id) {
            $stmt->bindValue($index + 1, $id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Kombinasi nilai CF menggunakan rumus CF Combine
     * @param array $cf_values Array nilai CF yang akan dikombinasi
     * @return float Hasil kombinasi CF
     */
    private function kombinasiCF($cf_values) {
        if (empty($cf_values)) {
            return 0;
        }
        
        $cf_combine = $cf_values[0];
        for ($i = 1; $i < count($cf_values); $i++) {
            $cf_combine = $cf_combine + $cf_values[$i] * (1 - $cf_combine);
        }
        
        return $cf_combine;
    }
    
    /**
     * Ambil semua penyakit dari database
     * @return array Daftar penyakit
     */
    private function getAllPenyakit() {
        $query = "SELECT * FROM penyakit ORDER BY nama_penyakit";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
 * Mendapatkan semua hasil diagnosis beserta penjelasannya
 * @param array $gejala_terpilih Array ID gejala yang dipilih
 * @param array $cf_user Array nilai CF dari user
 * @return array Semua hasil diagnosis dengan penjelasan
 */
public function getAllHasilDiagnosis($gejala_terpilih, $cf_user) {
    if (empty($gejala_terpilih)) {
        return [];
    }
    
    $penyakit_list = $this->getAllPenyakit();
    $semua_hasil = [];
    
    foreach ($penyakit_list as $penyakit) {
        $cf_combine = 0;
        $cf_values = [];
        $aturan_terpicu = [];
        $gejala_cocok = 0;
        
        $aturan_list = $this->getAturanByPenyakit($penyakit['id_penyakit']);
        
        foreach ($aturan_list as $aturan) {
            if (in_array($aturan['id_gejala'], $gejala_terpilih)) {
                $cf_user_value = $cf_user[$aturan['id_gejala']] ?? 1.0;
                $cf_rule = $aturan['cf_pakar'] * $cf_user_value;
                $cf_values[] = $cf_rule;
                $gejala_cocok++;
                
                $aturan_terpicu[] = [
                    'id_aturan' => $aturan['id_aturan'],
                    'id_gejala' => $aturan['id_gejala'],
                    'nama_gejala' => $aturan['nama_gejala'],
                    'cf_pakar' => $aturan['cf_pakar'],
                    'cf_user' => $cf_user_value,
                    'cf_rule' => $cf_rule
                ];
            }
        }
        
        if (!empty($cf_values)) {
            $cf_combine = $this->kombinasiCF($cf_values);
            
            $semua_hasil[] = [
                'id_penyakit' => $penyakit['id_penyakit'],
                'nama_penyakit' => $penyakit['nama_penyakit'],
                'deskripsi_penyakit' => $penyakit['deskripsi_penyakit'],
                'solusi_penyakit' => $penyakit['solusi_penyakit'],
                'cf_hasil' => $cf_combine,
                'gejala_cocok' => $gejala_cocok,
                'aturan_terpicu' => $aturan_terpicu
            ];
        }
    }
    
    // Urutkan berdasarkan CF tertinggi
    usort($semua_hasil, function($a, $b) {
        return $b['cf_hasil'] <=> $a['cf_hasil'];
    });
    
    return $semua_hasil;
}

/**
 * Kategorikan tingkat keyakinan berdasarkan nilai CF
 * @param float $cf Nilai certainty factor
 * @return string Kategori keyakinan
 */
public function kategoriKeyakinan($cf) {
    $persentase = $cf * 100;
    
    if ($persentase >= 80) {
        return 'Sangat Tinggi';
    } elseif ($persentase >= 60) {
        return 'Tinggi';
    } elseif ($persentase >= 40) {
        return 'Sedang';
    } elseif ($persentase >= 20) {
        return 'Rendah';
    } else {
        return 'Sangat Rendah';
    }
}
    
    /**
     * Ambil aturan berdasarkan ID penyakit
     * @param int $id_penyakit ID penyakit
     * @return array Daftar aturan
     */
    private function getAturanByPenyakit($id_penyakit) {
        $query = "SELECT a.*, ag.id_gejala, g.nama_gejala 
                 FROM aturan a 
                 JOIN aturan_gejala ag ON a.id_aturan = ag.id_aturan 
                 JOIN gejala g ON ag.id_gejala = g.id_gejala 
                 WHERE a.id_penyakit_hasil = :id_penyakit";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id_penyakit', $id_penyakit);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>