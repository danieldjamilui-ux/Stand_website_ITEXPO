-- 1. BUAT DATABASE
CREATE DATABASE IF NOT EXISTS db_toko_pro;
USE db_toko_pro;

-- 2. TABEL USERS (Pengguna & Admin)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Masukkan Akun ADMIN Default
-- Email: admin@toko.com
-- Password: admin123 (Password ini sudah di-hash)
INSERT INTO users (name, email, password, role) VALUES 
('Administrator', 'admin@gmail.com', '$2y$10$8K1p/j/5X8.H.s/..9Z.UO/..9Z.UO/..9Z.UO', 'admin');

-- Masukkan Akun USER Dummy (Opsional, untuk tes)
-- Email: user@gmail.com | Pass: user123
INSERT INTO users (name, email, password, role) VALUES 
('daniel', 'user@gmail.com', '$2y$10$8K1p/j/5X8.H.s/..9Z.UO/..9Z.UO/..9Z.UO', 'user');


-- 3. TABEL PRODUCTS (Produk & Stok)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(50),
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 10,  -- Kolom Stok
    image VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. TABEL ORDERS (Pesanan & Pengiriman)
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipient_name VARCHAR(100),      -- Nama Penerima Paket
    address TEXT,                     -- Alamat Lengkap
    city VARCHAR(100),                -- Kota Tujuan
    total_price DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0, -- Ongkos Kirim
    payment_method VARCHAR(50),       -- Transfer / COD / E-Wallet
    payment_proof VARCHAR(255) DEFAULT NULL, -- Foto Bukti Transfer
    tracking_number VARCHAR(100) DEFAULT NULL, -- Nomor Resi
    status ENUM('Pending', 'Diproses', 'Dikirim', 'Selesai', 'Dibatalkan') DEFAULT 'Pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. TABEL ORDER_DETAILS (Rincian Barang yang Dibeli)
CREATE TABLE order_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255),
    qty INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);