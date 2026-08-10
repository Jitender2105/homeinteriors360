SET NAMES utf8mb4;

ALTER TABLE quotation_rate_cards
  ADD COLUMN IF NOT EXISTS designer_id INT DEFAULT NULL AFTER package_id;

CREATE INDEX idx_quote_rate_designer ON quotation_rate_cards (designer_id);

ALTER TABLE quotation_rate_cards
  ADD CONSTRAINT fk_quote_rate_designer FOREIGN KEY (designer_id) REFERENCES pros(id) ON DELETE SET NULL;

ALTER TABLE designer_feature_registrations
  ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) DEFAULT NULL AFTER consent,
  ADD COLUMN IF NOT EXISTS razorpay_order_id VARCHAR(120) DEFAULT NULL AFTER status,
  ADD COLUMN IF NOT EXISTS razorpay_payment_id VARCHAR(120) DEFAULT NULL AFTER razorpay_order_id,
  ADD COLUMN IF NOT EXISTS razorpay_signature VARCHAR(255) DEFAULT NULL AFTER razorpay_payment_id,
  ADD COLUMN IF NOT EXISTS payment_status ENUM('pending','paid','failed') NOT NULL DEFAULT 'pending' AFTER razorpay_signature,
  ADD COLUMN IF NOT EXISTS pro_id INT DEFAULT NULL AFTER payment_status,
  ADD COLUMN IF NOT EXISTS user_id INT DEFAULT NULL AFTER pro_id,
  ADD COLUMN IF NOT EXISTS paid_at DATETIME DEFAULT NULL AFTER user_id;

CREATE INDEX idx_designer_reg_order ON designer_feature_registrations (razorpay_order_id);
CREATE INDEX idx_designer_reg_payment_status ON designer_feature_registrations (payment_status);
