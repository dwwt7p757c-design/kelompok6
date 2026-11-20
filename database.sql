-- KELOMPOK 6 luxury e-commerce schema (MySQL 5.7+/MariaDB 10.2+).
-- InfinityFree: create the database via Control Panel, remove CREATE DATABASE/USE
-- if the hosting account assigns a prefixed database name, then import this file.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS admins (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(80) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  failed_attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id), UNIQUE KEY uq_admin_username (username), UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  description VARCHAR(500) NOT NULL DEFAULT '',
  image VARCHAR(255) NOT NULL DEFAULT '',
  active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order SMALLINT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id), UNIQUE KEY uq_category_slug (slug), KEY idx_category_active_order (active,sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS products (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(180) NOT NULL,
  slug VARCHAR(200) NOT NULL,
  sku VARCHAR(80) NOT NULL,
  short_description VARCHAR(500) NOT NULL DEFAULT '',
  description TEXT NOT NULL,
  category_id BIGINT UNSIGNED NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  sale_price DECIMAL(12,2) NULL,
  cost_price DECIMAL(12,2) NULL,
  image VARCHAR(255) NOT NULL DEFAULT '',
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  low_stock_threshold INT UNSIGNED NOT NULL DEFAULT 5,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id), UNIQUE KEY uq_product_slug (slug), UNIQUE KEY uq_product_sku (sku),
  KEY idx_products_active_created (active,created_at), KEY idx_products_active_featured (active,featured),
  KEY idx_products_category (category_id),
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  CONSTRAINT chk_product_price CHECK (price >= 0),
  CONSTRAINT chk_product_sale CHECK (sale_price IS NULL OR (sale_price >= 0 AND sale_price < price))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_images (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id BIGINT UNSIGNED NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  alt_text VARCHAR(255) NOT NULL DEFAULT '',
  sort_order SMALLINT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), KEY idx_product_images_order (product_id,sort_order),
  CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_sizes (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id BIGINT UNSIGNED NOT NULL,
  size_name VARCHAR(20) NOT NULL,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id), UNIQUE KEY uq_product_size (product_id,size_name),
  CONSTRAINT fk_product_sizes_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product_variants (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id BIGINT UNSIGNED NOT NULL,
  variant_type VARCHAR(40) NOT NULL,
  variant_value VARCHAR(80) NOT NULL,
  sku VARCHAR(100) NOT NULL DEFAULT '',
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id), UNIQUE KEY uq_product_variant (product_id,variant_type,variant_value),
  KEY idx_variant_sku (sku),
  CONSTRAINT fk_variants_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_code VARCHAR(40) NOT NULL,
  customer_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  province VARCHAR(120) NOT NULL DEFAULT '',
  city VARCHAR(120) NOT NULL DEFAULT '',
  district VARCHAR(120) NOT NULL DEFAULT '',
  address TEXT NOT NULL,
  postal_code VARCHAR(20) NOT NULL DEFAULT '',
  notes TEXT NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  discount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  shipping_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(12,2) NOT NULL,
  payment_status ENUM('Pending','Awaiting Payment','Paid','Refunded') NOT NULL DEFAULT 'Awaiting Payment',
  status ENUM('Pending','Awaiting Payment','Paid','Processing','Shipped','Completed','Cancelled') NOT NULL DEFAULT 'Awaiting Payment',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id), UNIQUE KEY uq_order_code (order_code), KEY idx_orders_status_created (status,created_at),
  KEY idx_orders_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NULL,
  product_name VARCHAR(180) NOT NULL,
  product_sku VARCHAR(80) NOT NULL DEFAULT '',
  size_name VARCHAR(20) NOT NULL DEFAULT '',
  variant_snapshot VARCHAR(255) NOT NULL DEFAULT '',
  quantity INT UNSIGNED NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  line_total DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (id), KEY idx_order_items_order (order_id), KEY idx_order_items_product (product_id),
  CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(120) NOT NULL,
  setting_value TEXT NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  status ENUM('active','unsubscribed') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), UNIQUE KEY uq_newsletter_email (email), KEY idx_newsletter_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO settings (setting_key,setting_value) VALUES
('store_name','KELOMPOK 6'),
('store_tagline','CURATED FOR YOUR LIFESTYLE.'),
('store_description','A considered collection for modern living.'),
('currency','IDR'),
('announcement','Discover our latest collection.'),
('hero_eyebrow','THE CURATED EDIT'),
('hero_heading','THE ART OF EVERYDAY LIVING'),
('hero_description','Discover a carefully curated collection designed for modern lifestyles.'),
('hero_cta','EXPLORE COLLECTION'),
('hero_image',''),
('promo_heading','Objects of distinction'),
('promo_description','Considered essentials for the everyday.'),
('promo_image',''),
('promo_url','shop.php'),
('brand_story_heading','Curated with intention.'),
('brand_story','KELOMPOK 6 brings together considered pieces that balance function, form, and enduring style.'),
('contact_email',''),
('contact_whatsapp',''),
('contact_address',''),
('instagram_url',''),
('tiktok_url',''),
('shipping_fee','0');

SET FOREIGN_KEY_CHECKS = 1;
-- Admin account creation intentionally omitted; use the protected, one-time setup workflow.
