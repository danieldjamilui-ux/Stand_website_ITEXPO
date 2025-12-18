-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 16, 2025 at 03:25 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_toko_pro`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipient_name` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` enum('Pending','Diproses','Dikirim','Selesai','Dibatalkan') DEFAULT 'Pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_proof` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `recipient_name`, `address`, `city`, `total_price`, `shipping_cost`, `payment_method`, `status`, `order_date`, `payment_proof`) VALUES
(25, 4, 'halid', 'jalan raya mandaong, rumah abu\"', 'Bali', 12025000.00, 25000.00, 'E-Wallet', 'Selesai', '2025-12-15 06:52:33', NULL),
(26, 4, 'halid', 'dtrdtydf', 'Bali', 145000.00, 25000.00, 'E-Wallet', 'Selesai', '2025-12-15 17:12:14', 'BUKTI_26_1765818793_QR Download Tring Kaos.png'),
(28, 4, 'halid', 'dhwfoiWAS', 'Bacan', 170000.00, 50000.00, 'E-Wallet', 'Selesai', '2025-12-16 01:13:06', 'BUKTI_28_1765847679_download (2).jpg'),
(29, 4, 'halid', 'campus 3 jati', 'Ternate', 17010000.00, 10000.00, 'COD', 'Selesai', '2025-12-16 11:25:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `product_name`, `qty`, `price`) VALUES
(21, 0, 16, 'Bola Futsal Size 4', 1, 620000.00),
(22, 0, 16, 'Bola Futsal Size 4', 1, 620000.00),
(23, 0, 16, 'Bola Futsal Size 4', 1, 620000.00),
(29, 25, 13, 'MSI Stealth AI', 1, 12000000.00),
(30, 26, 19, 'Pakaian Wanita', 1, 120000.00),
(32, 28, 20, 'Sendal', 1, 120000.00),
(33, 29, 12, 'Asus ROG', 1, 17000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `stock`, `image`, `description`, `created_at`) VALUES
(8, 'Nike air', 'Fashion', 1000000.00, 97, '1764668296_download.jpeg', '', '2025-12-02 09:31:17'),
(12, 'Asus ROG', 'Elektronik', 17000000.00, 98, '1764685469_download (2).jpeg', '', '2025-12-02 14:24:16'),
(13, 'MSI Stealth AI', 'Elektronik', 12000000.00, 98, '1764685685_download (3).jpeg', '', '2025-12-02 14:27:52'),
(15, 'PS 5', 'Elektronik', 6000000.00, 20, '1764840430_rW0z81uCe6ej4sCrLkOR02XTaGtwp6VKcDqfP0Ux.jpg', '', '2025-12-04 09:26:59'),
(16, 'Bola Futsal Size 4', 'Hobi', 620000.00, 89, '1764840526_3mD96yCa20230912043040.jpg.webp', '', '2025-12-04 09:28:37'),
(18, 'Sepadu gunung ', 'Hobi', 2000000.00, 10, '1765415339_b497eb169b89f1af4ad401c4cf792da8.jpg_720x720q80.jpg', 'murah Meriah', '2025-12-11 01:08:45'),
(19, 'Pakaian Wanita', 'Fashion', 120000.00, 10, '1765642986_download (6).jpeg', 'ewfgf2wefc', '2025-12-13 16:23:06'),
(20, 'Sendal', 'Fashion', 120000.00, 10, '1765780737_download.jpg', '', '2025-12-15 06:38:57'),
(21, 'Kaos Pria ', 'Fashion', 100000.00, 10, '1765780943_gerald_gerald-_baju_fashion_atasan_pakaian_kaos_fire_lengan_pendek_pria_terbaru_full05_mhwwvvqm.webp', '', '2025-12-15 06:42:23');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 19, 4, 5, 'barangnya bagus sesuai gambar', '2025-12-15 19:20:04'),
(2, 13, 4, 5, 'bagus banget', '2025-12-15 19:40:11'),
(3, 20, 4, 5, 'sangat bagus sekali\r\n', '2025-12-16 11:26:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@gmail.com', '$2y$10$BzsTNDq7.tBbP61P9zcb4OCbB/Gft4v5ofqI/brK1zG89CKjGb8wa', 'admin', '2025-12-02 08:03:16'),
(4, 'halid', 'user@gmail.com', '$2y$10$e/mkecCGKurcaNn8e27vmeZEP/.Us3/RiAoUohI7Pn9Dqhl5O4Cru', 'user', '2025-12-08 16:19:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
