SET NAMES utf8mb4;

ALTER TABLE users
  MODIFY role ENUM('admin','super_admin','designer') NOT NULL DEFAULT 'admin',
  ADD COLUMN IF NOT EXISTS pro_id INT DEFAULT NULL AFTER role;

CREATE INDEX idx_users_pro ON users (pro_id);

ALTER TABLE users
  ADD CONSTRAINT fk_users_pro FOREIGN KEY (pro_id) REFERENCES pros(id) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS designer_feature_registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(255) DEFAULT NULL,
  city VARCHAR(120) DEFAULT NULL,
  company_name VARCHAR(255) DEFAULT NULL,
  plan_name VARCHAR(160) NOT NULL DEFAULT 'Designer Quotation Builder',
  offer_price DECIMAL(12,2) NOT NULL DEFAULT 399,
  mrp_price DECIMAL(12,2) NOT NULL DEFAULT 2999,
      message TEXT,
      consent TINYINT(1) NOT NULL DEFAULT 1,
      password_hash VARCHAR(255) DEFAULT NULL,
      status ENUM('new','contacted','converted','closed') NOT NULL DEFAULT 'new',
      razorpay_order_id VARCHAR(120) DEFAULT NULL,
      razorpay_payment_id VARCHAR(120) DEFAULT NULL,
      razorpay_signature VARCHAR(255) DEFAULT NULL,
      payment_status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
      pro_id INT DEFAULT NULL,
      user_id INT DEFAULT NULL,
      paid_at DATETIME DEFAULT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_designer_reg_status (status),
      INDEX idx_designer_reg_phone (phone),
      INDEX idx_designer_reg_order (razorpay_order_id),
      INDEX idx_designer_reg_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
