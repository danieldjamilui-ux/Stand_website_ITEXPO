-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 01, 2025 at 11:45 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sp_penyakit_mata`
--

-- --------------------------------------------------------

--
-- Table structure for table `aturan`
--

CREATE TABLE `aturan` (
  `id_aturan` int(11) NOT NULL,
  `cf_pakar` decimal(3,2) NOT NULL,
  `id_penyakit_hasil` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aturan`
--

INSERT INTO `aturan` (`id_aturan`, `cf_pakar`, `id_penyakit_hasil`, `created_at`) VALUES
(7, 0.85, 1, '2025-06-30 13:42:46'),
(8, 0.85, 2, '2025-06-30 13:43:59'),
(9, 0.85, 3, '2025-06-30 13:45:56'),
(10, 0.85, 4, '2025-06-30 13:47:50'),
(11, 0.85, 5, '2025-06-30 13:49:09'),
(12, 0.85, 6, '2025-06-30 13:50:35'),
(13, 0.85, 7, '2025-06-30 13:52:07'),
(14, 0.85, 8, '2025-06-30 13:52:49');

-- --------------------------------------------------------

--
-- Table structure for table `aturan_gejala`
--

CREATE TABLE `aturan_gejala` (
  `id_aturan` int(11) NOT NULL,
  `id_gejala` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aturan_gejala`
--

INSERT INTO `aturan_gejala` (`id_aturan`, `id_gejala`) VALUES
(7, 5),
(7, 12),
(7, 20),
(7, 32),
(8, 9),
(8, 24),
(9, 17),
(9, 21),
(9, 26),
(9, 30),
(10, 6),
(10, 7),
(10, 14),
(10, 15),
(10, 20),
(11, 16),
(11, 22),
(11, 23),
(11, 27),
(12, 6),
(12, 13),
(12, 28),
(13, 16),
(13, 25),
(13, 29),
(14, 5),
(14, 31);

-- --------------------------------------------------------

--
-- Table structure for table `gejala`
--

CREATE TABLE `gejala` (
  `id_gejala` int(11) NOT NULL,
  `nama_gejala` varchar(100) NOT NULL,
  `deskripsi_gejala` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gejala`
--

INSERT INTO `gejala` (`id_gejala`, `nama_gejala`, `deskripsi_gejala`, `created_at`) VALUES
(5, 'Penglihatan terasa kabur', '', '2025-06-30 13:24:30'),
(6, 'Mata berair', '', '2025-06-30 13:25:16'),
(7, 'Mata bengkak', '', '2025-06-30 13:25:31'),
(8, 'Mata terasa perih', '', '2025-06-30 13:25:49'),
(9, 'Mata terasa ada yang mengganjal', '', '2025-06-30 13:26:05'),
(10, 'Penglihatan silau', '', '2025-06-30 13:26:22'),
(11, 'Terlihat lingkaran cahaya', '', '2025-06-30 13:26:46'),
(12, 'Penglihatan objek ganda', '', '2025-06-30 13:27:03'),
(13, 'Mata berwarna merah', '', '2025-06-30 13:27:21'),
(14, 'Mata terasa gatal', '', '2025-06-30 13:27:36'),
(15, 'Mata terasa panas', '', '2025-06-30 13:27:51'),
(16, 'Sakit kepala', '', '2025-06-30 13:28:05'),
(17, 'Mata terasa sakit', '', '2025-06-30 13:28:23'),
(18, 'Mata meradang', '', '2025-06-30 13:28:40'),
(19, 'Mata nyeri hebat', '', '2025-06-30 13:29:01'),
(20, 'Mata terasa nyeri', '', '2025-06-30 13:29:20'),
(21, 'Kelainan pada pupil mata', '', '2025-06-30 13:29:35'),
(22, 'Mata lelah', '', '2025-06-30 13:29:49'),
(23, 'Sering mengedipkan mata', '', '2025-06-30 13:30:28'),
(24, 'Peka terhadap cahaya', '', '2025-06-30 13:30:43'),
(25, 'Penglihatan dekat terasa kabur', '', '2025-06-30 13:31:01'),
(26, 'Tekanan bola mata meningkat', '', '2025-06-30 13:31:19'),
(27, 'Penglihatan objek jauh kurang terlihat jelas', '', '2025-06-30 13:31:34'),
(28, 'Lemak menutupi kornea', '', '2025-06-30 13:35:14'),
(29, 'Menyipitkan mata untuk melihat benda yang dekat', '', '2025-06-30 13:35:33'),
(30, 'Sumber cahaya akan berwarna pelangi jika melihat cahaya yang terang', '', '2025-06-30 13:35:53'),
(31, 'Mata tegang', '', '2025-06-30 13:36:03'),
(32, 'Terlihat bayangan garis hitam', '', '2025-06-30 13:36:13');

-- --------------------------------------------------------

--
-- Table structure for table `konsultasi`
--

CREATE TABLE `konsultasi` (
  `id_konsultasi` int(11) NOT NULL,
  `tanggal_konsultasi` timestamp NOT NULL DEFAULT current_timestamp(),
  `cf_hasil_diagnosis` decimal(5,4) DEFAULT NULL,
  `id_pengguna` int(11) NOT NULL,
  `id_penyakit_hasil` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `konsultasi`
--

INSERT INTO `konsultasi` (`id_konsultasi`, `tanggal_konsultasi`, `cf_hasil_diagnosis`, `id_pengguna`, `id_penyakit_hasil`) VALUES
(20, '2025-06-30 08:12:03', 0.2550, 1, 2),
(21, '2025-06-30 08:15:12', 0.7327, 1, 2),
(22, '2025-06-30 14:07:59', 0.8500, 1, 6),
(23, '2025-06-30 14:19:05', 0.8500, 1, 5),
(24, '2025-06-30 14:22:34', 0.8500, 1, 6),
(25, '2025-06-30 17:22:40', 0.5950, 1, 3),
(26, '2025-06-30 18:00:53', 0.8500, 1, 6);

-- --------------------------------------------------------

--
-- Table structure for table `konsultasi_gejalapilihan`
--

CREATE TABLE `konsultasi_gejalapilihan` (
  `id_konsultasi` int(11) NOT NULL,
  `id_gejala` int(11) NOT NULL,
  `cf_pengguna` decimal(3,2) DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `konsultasi_gejalapilihan`
--

INSERT INTO `konsultasi_gejalapilihan` (`id_konsultasi`, `id_gejala`, `cf_pengguna`) VALUES
(20, 8, 0.60),
(20, 10, 1.00),
(20, 24, 0.30),
(21, 9, 0.40),
(21, 19, 0.80),
(21, 22, 0.60),
(21, 24, 0.70),
(22, 28, 1.00),
(23, 27, 1.00),
(24, 28, 1.00),
(25, 21, 0.70),
(25, 28, 0.30),
(26, 13, 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_pengguna` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_pengguna`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-06-28 12:01:17'),
(2, 'admin2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at', '2025-06-30 21:49:11');

-- --------------------------------------------------------

--
-- Table structure for table `penyakit`
--

CREATE TABLE `penyakit` (
  `id_penyakit` int(11) NOT NULL,
  `nama_penyakit` varchar(100) NOT NULL,
  `deskripsi_penyakit` text DEFAULT NULL,
  `solusi_penyakit` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penyakit`
--

INSERT INTO `penyakit` (`id_penyakit`, `nama_penyakit`, `deskripsi_penyakit`, `solusi_penyakit`, `created_at`) VALUES
(1, 'Katarak', 'Katarak adalah kekeruhan pada lensa mata yang menyebabkan penglihatan kabur. Umumnya terjadi karena penuaan, diabetes, atau paparan sinar UV. Gejalanya termasuk buram dan silau.penglihatan kabur, silau berlebih, dan penurunan ketajaman penglihatan, terutama di malam hari.', 'Diobati dengan operasi penggantian lensa mata. Kacamata bisa membantu sementara. Pencegahan dengan melindungi mata dan menjaga kesehatan.', '2025-06-30 13:16:17'),
(2, 'Dry Eye (Mata Kering)', 'Kondisi ketika mata tidak menghasilkan cukup air mata atau air mata cepat menguap. Umumnya disebabkan oleh usia, paparan layar, lingkungan kering, atau penggunaan lensa kontak. Gejalanya antara lain perih, kering, dan rasa seperti berpasir.', 'Diobati dengan tetes mata buatan, menjaga kelembapan udara, mengurangi paparan layar, dan menghindari angin langsung. Pada kasus berat, bisa digunakan obat resep atau prosedur medis.', '2025-06-30 13:20:08'),
(3, 'Glaukoma', 'Glaukoma adalah penyakit mata yang merusak saraf optik, sering akibat tekanan bola mata yang tinggi. Dapat terjadi tanpa gejala awal dan menyebabkan kebutaan permanen jika tidak ditangani.', 'Pengobatan meliputi obat tetes mata, obat oral, atau operasi untuk menurunkan tekanan mata. Deteksi dini penting untuk mencegah kerusakan lebih lanjut.', '2025-06-30 13:21:23'),
(4, 'Keratitis', 'Keratitis adalah peradangan pada kornea, bagian bening di depan mata. Bisa disebabkan oleh infeksi (bakteri, virus, jamur), cedera, atau pemakaian lensa kontak yang tidak higienis. Gejalanya meliputi nyeri, kemerahan, dan penglihatan kabur.', 'Pengobatan tergantung penyebabnya, bisa berupa tetes mata antibiotik, antivirus, atau antijamur. Pada kasus berat, dapat diperlukan perawatan intensif atau tindakan medis lanjutan.', '2025-06-30 13:22:35'),
(5, 'Myopia', 'Myopia (rabun jauh) adalah kelainan refraksi di mana objek jauh terlihat buram, sedangkan objek dekat terlihat jelas. Umumnya disebabkan oleh bola mata yang terlalu panjang atau kelengkungan kornea yang terlalu besar.', 'Dapat dikoreksi dengan kacamata atau lensa kontak minus. Untuk beberapa kasus, operasi refraktif seperti LASIK juga bisa menjadi pilihan.', '2025-06-30 13:39:41'),
(6, 'Pterygium', 'Pterygium adalah pertumbuhan jaringan berbentuk segitiga pada permukaan mata, biasanya dari bagian putih mata ke arah kornea. Umumnya disebabkan oleh paparan sinar UV, debu, atau angin.', 'Pengobatan awal dengan tetes mata untuk mengurangi iritasi. Jika mengganggu penglihatan atau estetika, dapat diangkat melalui operasi. Pencegahan dengan kacamata hitam dan pelindung debu.', '2025-06-30 13:39:55'),
(7, 'Hypermetropi', 'Hypermetropi (rabun dekat) adalah kelainan refraksi di mana mata sulit melihat objek dekat dengan jelas. Kondisi ini terjadi karena bola mata terlalu pendek atau kornea terlalu datar.', 'Dapat dikoreksi dengan kacamata atau lensa kontak plus. Operasi refraktif seperti LASIK juga bisa menjadi pilihan pada kasus tertentu.', '2025-06-30 13:40:06'),
(8, 'Astigmatisma', 'Astigmatisma adalah kelainan bentuk kornea atau lensa mata yang menyebabkan penglihatan kabur atau terdistorsi. Kondisi ini bisa terjadi bersama rabun jauh atau rabun dekat. Gejalanya termasuk penglihatan buram dan sakit kepala.', 'Dapat dikoreksi dengan kacamata, lensa kontak khusus (torik), atau prosedur bedah seperti LASIK. Pemeriksaan mata rutin penting untuk diagnosis dan penyesuaian koreksi.', '2025-06-30 13:40:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aturan`
--
ALTER TABLE `aturan`
  ADD PRIMARY KEY (`id_aturan`),
  ADD KEY `id_penyakit_hasil` (`id_penyakit_hasil`);

--
-- Indexes for table `aturan_gejala`
--
ALTER TABLE `aturan_gejala`
  ADD PRIMARY KEY (`id_aturan`,`id_gejala`),
  ADD KEY `id_gejala` (`id_gejala`);

--
-- Indexes for table `gejala`
--
ALTER TABLE `gejala`
  ADD PRIMARY KEY (`id_gejala`);

--
-- Indexes for table `konsultasi`
--
ALTER TABLE `konsultasi`
  ADD PRIMARY KEY (`id_konsultasi`),
  ADD KEY `id_pengguna` (`id_pengguna`),
  ADD KEY `id_penyakit_hasil` (`id_penyakit_hasil`);

--
-- Indexes for table `konsultasi_gejalapilihan`
--
ALTER TABLE `konsultasi_gejalapilihan`
  ADD PRIMARY KEY (`id_konsultasi`,`id_gejala`),
  ADD KEY `id_gejala` (`id_gejala`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_pengguna`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `penyakit`
--
ALTER TABLE `penyakit`
  ADD PRIMARY KEY (`id_penyakit`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aturan`
--
ALTER TABLE `aturan`
  MODIFY `id_aturan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `gejala`
--
ALTER TABLE `gejala`
  MODIFY `id_gejala` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `konsultasi`
--
ALTER TABLE `konsultasi`
  MODIFY `id_konsultasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_pengguna` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aturan`
--
ALTER TABLE `aturan`
  ADD CONSTRAINT `aturan_ibfk_1` FOREIGN KEY (`id_penyakit_hasil`) REFERENCES `penyakit` (`id_penyakit`) ON DELETE CASCADE;

--
-- Constraints for table `aturan_gejala`
--
ALTER TABLE `aturan_gejala`
  ADD CONSTRAINT `aturan_gejala_ibfk_1` FOREIGN KEY (`id_aturan`) REFERENCES `aturan` (`id_aturan`) ON DELETE CASCADE,
  ADD CONSTRAINT `aturan_gejala_ibfk_2` FOREIGN KEY (`id_gejala`) REFERENCES `gejala` (`id_gejala`) ON DELETE CASCADE;

--
-- Constraints for table `konsultasi`
--
ALTER TABLE `konsultasi`
  ADD CONSTRAINT `konsultasi_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id_pengguna`) ON DELETE CASCADE,
  ADD CONSTRAINT `konsultasi_ibfk_2` FOREIGN KEY (`id_penyakit_hasil`) REFERENCES `penyakit` (`id_penyakit`);

--
-- Constraints for table `konsultasi_gejalapilihan`
--
ALTER TABLE `konsultasi_gejalapilihan`
  ADD CONSTRAINT `konsultasi_gejalapilihan_ibfk_1` FOREIGN KEY (`id_konsultasi`) REFERENCES `konsultasi` (`id_konsultasi`) ON DELETE CASCADE,
  ADD CONSTRAINT `konsultasi_gejalapilihan_ibfk_2` FOREIGN KEY (`id_gejala`) REFERENCES `gejala` (`id_gejala`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
