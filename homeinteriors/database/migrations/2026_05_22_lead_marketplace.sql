CREATE TABLE IF NOT EXISTS lead_buyers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_lead_buyers_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lead_purchases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  buyer_id INT NOT NULL,
  razorpay_order_id VARCHAR(120) NOT NULL,
  razorpay_payment_id VARCHAR(120) DEFAULT NULL,
  razorpay_signature VARCHAR(255) DEFAULT NULL,
  amount_total DECIMAL(12,2) NOT NULL DEFAULT 0,
  currency VARCHAR(12) NOT NULL DEFAULT 'INR',
  payment_status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending',
  cart_json JSON DEFAULT NULL,
  paid_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_lead_purchases_buyer FOREIGN KEY (buyer_id) REFERENCES lead_buyers(id) ON DELETE CASCADE,
  UNIQUE KEY uniq_lead_purchase_order (razorpay_order_id),
  INDEX idx_lead_purchases_buyer (buyer_id),
  INDEX idx_lead_purchases_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lead_purchase_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  purchase_id INT NOT NULL,
  buyer_id INT NOT NULL,
  filter_name VARCHAR(255) NOT NULL,
  filter_json JSON NOT NULL,
  date_filter VARCHAR(40) DEFAULT NULL,
  leads_count INT NOT NULL DEFAULT 0,
  amount_total DECIMAL(12,2) NOT NULL DEFAULT 0,
  pricing_json JSON DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_lead_purchase_items_purchase FOREIGN KEY (purchase_id) REFERENCES lead_purchases(id) ON DELETE CASCADE,
  CONSTRAINT fk_lead_purchase_items_buyer FOREIGN KEY (buyer_id) REFERENCES lead_buyers(id) ON DELETE CASCADE,
  INDEX idx_lead_purchase_items_buyer (buyer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
