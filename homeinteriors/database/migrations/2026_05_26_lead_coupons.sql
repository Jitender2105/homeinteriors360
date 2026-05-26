CREATE TABLE IF NOT EXISTS lead_coupons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(80) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  discount_type ENUM('percentage','flat') NOT NULL DEFAULT 'percentage',
  discount_value DECIMAL(12,2) NOT NULL DEFAULT 0,
  min_leads INT DEFAULT NULL,
  max_leads INT DEFAULT NULL,
  min_order_amount DECIMAL(12,2) DEFAULT NULL,
  max_discount_amount DECIMAL(12,2) DEFAULT NULL,
  valid_from DATE DEFAULT NULL,
  valid_to DATE DEFAULT NULL,
  usage_limit INT DEFAULT NULL,
  used_count INT NOT NULL DEFAULT 0,
  show_on_frontend BOOLEAN NOT NULL DEFAULT FALSE,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_lead_coupons_frontend (show_on_frontend, is_active),
  INDEX idx_lead_coupons_dates (valid_from, valid_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE lead_purchases ADD COLUMN coupon_code VARCHAR(80) DEFAULT NULL AFTER razorpay_signature;
ALTER TABLE lead_purchases ADD COLUMN discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER coupon_code;
