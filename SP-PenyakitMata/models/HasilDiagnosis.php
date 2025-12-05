<?php
class HasilDiagnosis {
    private $penyakit;
    private $cfHasil;
    private $deskripsiPenyakit;
    private $solusiPenyakit;
    
    public function __construct() {
        // Konstruktor kosong
    }
    
    // Getters
    public function getPenyakit() { return $this->penyakit; }
    public function getCfHasil() { return $this->cfHasil; }
    public function getDeskripsiPenyakit() { return $this->deskripsiPenyakit; }
    public function getSolusiPenyakit() { return $this->solusiPenyakit; }
    
    // Setters
    public function setPenyakit($penyakit) { $this->penyakit = $penyakit; }
    public function setCfHasil($cfHasil) { $this->cfHasil = $cfHasil; }
    public function setDeskripsiPenyakit($deskripsi) { $this->deskripsiPenyakit = $deskripsi; }
    public function setSolusiPenyakit($solusi) { $this->solusiPenyakit = $solusi; }
    
    // Helper methods
    public function getPersentaseKepastian() {
        return round($this->cfHasil * 100, 2);
    }
    
    public function getKategoriKepastian() {
        $persentase = $this->getPersentaseKepastian();
        
        if ($persentase >= 80) {
            return 'Sangat Yakin';
        } elseif ($persentase >= 60) {
            return 'Yakin';
        } elseif ($persentase >= 40) {
            return 'Cukup Yakin';
        } elseif ($persentase >= 20) {
            return 'Kurang Yakin';
        } else {
            return 'Tidak Yakin';
        }
    }
    
    public function toArray() {
        return [
            'penyakit' => $this->penyakit,
            'cf_hasil' => $this->cfHasil,
            'deskripsi_penyakit' => $this->deskripsiPenyakit,
            'solusi_penyakit' => $this->solusiPenyakit,
            'persentase_kepastian' => $this->getPersentaseKepastian(),
            'kategori_kepastian' => $this->getKategoriKepastian()
        ];
    }
}
?>