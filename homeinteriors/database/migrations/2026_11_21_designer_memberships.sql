SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS designer_membership_plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL,
  name VARCHAR(120) NOT NULL,
  billing_cycle ENUM('monthly','yearly') NOT NULL,
  price_paise INT NOT NULL,
  list_price_paise INT DEFAULT NULL,
  lead_discount_percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
  bonus_lead_credits INT NOT NULL DEFAULT 0,
  featured_locality_slots INT NOT NULL DEFAULT 0,
  razorpay_plan_id VARCHAR(120) DEFAULT NULL,
  features_json JSON DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_designer_membership_plan_cycle (code, billing_cycle),
  KEY idx_designer_membership_plan_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS designer_memberships (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  pro_id INT NOT NULL,
  plan_id INT NOT NULL,
  status VARCHAR(32) NOT NULL DEFAULT 'created',
  razorpay_subscription_id VARCHAR(120) DEFAULT NULL,
  razorpay_payment_id VARCHAR(120) DEFAULT NULL,
  razorpay_signature VARCHAR(255) DEFAULT NULL,
  business_name VARCHAR(255) DEFAULT NULL,
  owner_name VARCHAR(255) DEFAULT NULL,
  whatsapp_phone VARCHAR(32) DEFAULT NULL,
  invoice_email VARCHAR(255) DEFAULT NULL,
  gstin VARCHAR(32) DEFAULT NULL,
  featured_locality VARCHAR(160) DEFAULT NULL,
  starts_at DATETIME DEFAULT NULL,
  ends_at DATETIME DEFAULT NULL,
  cancelled_at DATETIME DEFAULT NULL,
  metadata_json JSON DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_designer_membership_rzp (razorpay_subscription_id),
  KEY idx_designer_membership_user (user_id, status),
  KEY idx_designer_membership_pro (pro_id, status),
  CONSTRAINT fk_designer_membership_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_designer_membership_pro FOREIGN KEY (pro_id) REFERENCES pros(id) ON DELETE CASCADE,
  CONSTRAINT fk_designer_membership_plan FOREIGN KEY (plan_id) REFERENCES designer_membership_plans(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO designer_membership_plans (code,name,billing_cycle,price_paise,list_price_paise,lead_discount_percentage,bonus_lead_credits,featured_locality_slots,sort_order,features_json) VALUES
('free','Free Listing','monthly',0,0,0,0,0,1,JSON_ARRAY('Free professional profile','Portfolio listing after approval','3 one-time free leads')),
('pro','Pro Member','monthly',99900,99900,20,5,0,2,JSON_ARRAY('Everything in Free','Priority lead alerts','20% lead purchase benefit','5 bonus lead credits each month')),
('pro','Pro Member','yearly',999900,1198800,20,5,0,3,JSON_ARRAY('Everything in Free','Priority lead alerts','20% lead purchase benefit','5 bonus lead credits each month')),
('featured','Pro + Featured','monthly',299900,299900,50,10,1,4,JSON_ARRAY('Everything in Pro','Featured placement','First look at matching leads','50% lead purchase benefit')),
('featured','Pro + Featured','yearly',2999900,3598800,50,10,1,5,JSON_ARRAY('Everything in Pro','Featured placement','First look at matching leads','50% lead purchase benefit'))
ON DUPLICATE KEY UPDATE name=VALUES(name),price_paise=VALUES(price_paise),list_price_paise=VALUES(list_price_paise),lead_discount_percentage=VALUES(lead_discount_percentage),bonus_lead_credits=VALUES(bonus_lead_credits),featured_locality_slots=VALUES(featured_locality_slots),features_json=VALUES(features_json),is_active=1;
