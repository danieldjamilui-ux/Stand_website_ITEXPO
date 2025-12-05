<?php
class GejalaPilihan {
    private $gejala;
    private $cfPengguna;
    
    public function __construct() {
        // Konstruktor kosong
    }
    
    // Getters
    public function getGejala() { return $this->gejala; }
    public function getCfPengguna() { return $this->cfPengguna; }
    
    // Setters
    public function setGejala($gejala) { $this->gejala = $gejala; }
    public function setCfPengguna($cf) { $this->cfPengguna = $cf; }
    
    // Helper methods
    public function toArray() {
        return [
            'gejala' => $this->gejala,
            'cf_pengguna' => $this->cfPengguna
        ];
    }
}
?>