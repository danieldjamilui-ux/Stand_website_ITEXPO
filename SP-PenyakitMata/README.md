# Sistem Pakar Penyakit Mata

Sistem pakar untuk diagnosis penyakit mata menggunakan metode Certainty Factor (CF) yang dikembangkan dengan PHP dan Python.

## Fitur Utama

1. **Login Pengguna (KF-001)**: Sistem login yang aman untuk mengakses aplikasi
2. **Melihat Penjelasan Diagnosis (KF-002)**: Menampilkan penjelasan detail bagaimana sistem mencapai kesimpulan diagnosis
3. **Melakukan Konsultasi Diagnosis (KF-003)**: Konsultasi dengan pemilihan gejala dan nilai CF untuk mendapatkan diagnosis
4. **Melihat Riwayat Konsultasi (KF-004)**: Menampilkan riwayat konsultasi yang telah dilakukan
5. **Mengelola Data Aturan (KF-005)**: CRUD data aturan dengan nilai CF Pakar
6. **Mengelola Data Gejala (KF-006)**: CRUD data gejala yang dikenali sistem
7. **Mengelola Data Penyakit (KF-007)**: CRUD data penyakit yang dapat didiagnosis
8. **Logout**: Keluar dari sistem dengan aman

## Teknologi yang Digunakan

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Metode**: Certainty Factor (CF) untuk sistem pakar
- **Libraries**: PDO untuk database, Select2 untuk dropdown

## Struktur Direktori

```
SP-PenyakitMata/
├── config/
│   └── database.php          # Konfigurasi koneksi database
├── database/
│   └── sp_penyakit_mata.sql  # Schema dan data awal database
├── index.php                 # Halaman login
├── dashboard.php             # Dashboard utama
├── konsultasi.php            # Halaman konsultasi diagnosis
├── riwayat.php              # Halaman riwayat konsultasi
├── detail_konsultasi.php     # Detail konsultasi dan penjelasan
├── get_explanation.php       # AJAX handler untuk penjelasan diagnosis
├── kelola_penyakit.php      # Manajemen data penyakit
├── kelola_gejala.php        # Manajemen data gejala
├── kelola_aturan.php        # Manajemen data aturan
├── logout.php               # Handler logout
└── README.md                # Dokumentasi ini
```

## Instalasi

### Prasyarat

- PHP 7.4 atau lebih tinggi
- MySQL/MariaDB
- Web server (Apache/Nginx)
- Browser modern

### Langkah Instalasi

1. **Clone atau download project**
   ```bash
   git clone [repository-url]
   cd SP-PenyakitMata
   ```

2. **Setup Database**
   - Buat database baru dengan nama `sp_penyakit_mata`
   - Import file `database/sp_penyakit_mata.sql`
   ```sql
   CREATE DATABASE sp_penyakit_mata;
   USE sp_penyakit_mata;
   SOURCE database/sp_penyakit_mata.sql;
   ```

3. **Konfigurasi Database**
   - Edit file `config/database.php` sesuai dengan konfigurasi database Anda
   ```php
   private $host = "localhost";
   private $db_name = "sp_penyakit_mata";
   private $username = "root";
   private $password = "";
   ```

4. **Setup Web Server**
   - Pastikan web server mengarah ke direktori project
   - Aktifkan mod_rewrite jika menggunakan Apache

5. **Akses Aplikasi**
   - Buka browser dan akses `http://localhost/SP-PenyakitMata`
   - Login dengan kredensial default:
     - Username: `admin`
     - Password: `admin123`

## Penggunaan

### Login
- Masukkan username dan password pada halaman login
- Sistem akan mengarahkan ke dashboard setelah login berhasil

### Dashboard
- Menampilkan statistik sistem (jumlah penyakit, gejala, aturan)
- Menampilkan riwayat konsultasi terbaru
- Navigasi ke fitur-fitur utama

### Konsultasi Diagnosis
1. Pilih gejala yang dialami dari daftar yang tersedia
2. Atur tingkat keyakinan (CF User) untuk setiap gejala menggunakan slider
3. Klik "Diagnosa" untuk mendapatkan hasil
4. Sistem akan menampilkan penyakit yang paling mungkin beserta tingkat kepastiannya

### Manajemen Data
- **Penyakit**: Tambah, edit, hapus data penyakit
- **Gejala**: Tambah, edit, hapus data gejala
- **Aturan**: Tambah, edit, hapus aturan yang menghubungkan penyakit dengan gejala beserta nilai CF Pakar

## Metode Certainty Factor

Sistem menggunakan metode Certainty Factor untuk menghitung tingkat kepastian diagnosis:

```
CF(H,E) = CF(E) × CF(Rule)
```

Dimana:
- CF(H,E) = Certainty Factor hipotesis berdasarkan evidence
- CF(E) = Certainty Factor evidence (input user)
- CF(Rule) = Certainty Factor rule (dari pakar)

Untuk kombinasi beberapa evidence:
```
CF(combine) = CF1 + CF2 × (1 - CF1)
```

## Database Schema

### Tabel Users
- `id`: Primary key
- `username`: Username login
- `password`: Password (hashed)
- `nama_lengkap`: Nama lengkap user
- `created_at`: Timestamp pembuatan

### Tabel Penyakit
- `id`: Primary key
- `kode_penyakit`: Kode unik penyakit
- `nama_penyakit`: Nama penyakit
- `deskripsi`: Deskripsi penyakit
- `solusi`: Solusi/pengobatan
- `created_at`: Timestamp pembuatan

### Tabel Gejala
- `id`: Primary key
- `kode_gejala`: Kode unik gejala
- `nama_gejala`: Nama gejala
- `deskripsi`: Deskripsi gejala
- `created_at`: Timestamp pembuatan

### Tabel Aturan
- `id`: Primary key
- `id_penyakit`: Foreign key ke tabel penyakit
- `id_gejala`: Foreign key ke tabel gejala
- `cf_pakar`: Nilai certainty factor dari pakar
- `created_at`: Timestamp pembuatan

### Tabel Konsultasi
- `id`: Primary key
- `id_user`: Foreign key ke tabel users
- `id_penyakit`: Foreign key ke tabel penyakit (hasil diagnosis)
- `cf_hasil`: Nilai CF hasil diagnosis
- `tanggal_konsultasi`: Timestamp konsultasi

### Tabel Detail Konsultasi
- `id`: Primary key
- `id_konsultasi`: Foreign key ke tabel konsultasi
- `id_gejala`: Foreign key ke tabel gejala
- `cf_user`: Nilai CF yang diinput user

## Troubleshooting

### Error Koneksi Database
- Pastikan MySQL/MariaDB berjalan
- Periksa konfigurasi di `config/database.php`
- Pastikan database `sp_penyakit_mata` sudah dibuat

### Error Permission
- Pastikan direktori project memiliki permission yang tepat
- Untuk Linux/Mac: `chmod -R 755 SP-PenyakitMata`

### Error Session
- Pastikan PHP session sudah diaktifkan
- Periksa konfigurasi `session.save_path` di php.ini

## Kontribusi

Untuk berkontribusi pada project ini:
1. Fork repository
2. Buat branch fitur baru
3. Commit perubahan
4. Push ke branch
5. Buat Pull Request

## Lisensi

Project ini dikembangkan untuk keperluan akademik dan pembelajaran.

## Kontak

Untuk pertanyaan atau dukungan, silakan hubungi pengembang.

---

**Catatan**: Sistem ini dikembangkan untuk keperluan akademik dan tidak dimaksudkan untuk menggantikan konsultasi medis profesional.