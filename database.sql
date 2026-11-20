CREATE DATABASE IF NOT EXISTS luxury_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE luxury_store;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  slug VARCHAR(200) NOT NULL UNIQUE,
  description TEXT,
  price DECIMAL(12,2) NOT NULL DEFAULT 0,
  sale_price DECIMAL(12,2) NULL,
  category VARCHAR(100) DEFAULT 'New Arrivals',
  image VARCHAR(255) DEFAULT '',
  stock INT NOT NULL DEFAULT 0,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE product_sizes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  size_name VARCHAR(20) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  UNIQUE KEY product_size_unique (product_id, size_name),
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_code VARCHAR(40) NOT NULL UNIQUE,
  customer_name VARCHAR(150) NOT NULL,
  email VARCHAR(180) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  address TEXT NOT NULL,
  notes TEXT,
  total DECIMAL(12,2) NOT NULL,
  status ENUM('Menunggu','Diproses','Dikirim','Selesai','Dibatalkan') DEFAULT 'Menunggu',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NULL,
  product_name VARCHAR(180) NOT NULL,
  size_name VARCHAR(20) DEFAULT '',
  quantity INT NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);
CREATE TABLE settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value TEXT
);
INSERT INTO settings (setting_key, setting_value) VALUES
('store_name','ATELIER'),
('store_tagline','Considered pieces. Made to be worn.'),
('store_logo',''),
('currency','IDR');
-- Buat akun admin pertama lewat admin/create_admin.php, lalu HAPUS file tersebut.
