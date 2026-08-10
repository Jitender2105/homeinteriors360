SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS project_budget_ranges (
  id INT AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(120) NOT NULL,
  min_amount DECIMAL(14,2) DEFAULT NULL,
  max_amount DECIMAL(14,2) DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_project_budget_label (label),
  INDEX idx_project_budget_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_requirements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_id INT DEFAULT NULL,
  name VARCHAR(255) NOT NULL,
  mobile VARCHAR(30) NOT NULL,
  email VARCHAR(255) DEFAULT NULL,
  city VARCHAR(120) NOT NULL,
  locality VARCHAR(190) DEFAULT NULL,
  society_name VARCHAR(255) DEFAULT NULL,
  pincode VARCHAR(12) DEFAULT NULL,
  property_type VARCHAR(80) DEFAULT NULL,
  bhk VARCHAR(20) DEFAULT NULL,
  area_sqft INT DEFAULT NULL,
  property_status VARCHAR(80) DEFAULT NULL,
  timeline VARCHAR(80) DEFAULT NULL,
  services_json JSON DEFAULT NULL,
  budget_range VARCHAR(120) DEFAULT NULL,
  style_preference VARCHAR(80) DEFAULT NULL,
  preferred_contact_method VARCHAR(80) DEFAULT NULL,
  preferred_consultation_time VARCHAR(120) DEFAULT NULL,
  notes TEXT,
  source VARCHAR(80) NOT NULL DEFAULT 'homepage',
  lead_quality ENUM('basic','verified','qualified','exclusive') NOT NULL DEFAULT 'basic',
  otp_verified TINYINT(1) NOT NULL DEFAULT 0,
  contact_share_consent TINYINT(1) NOT NULL DEFAULT 0,
  marketing_consent TINYINT(1) NOT NULL DEFAULT 0,
  status ENUM('new','verification_pending','verified','matched','closed') NOT NULL DEFAULT 'verification_pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_project_requirement_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
  INDEX idx_project_requirements_city (city),
  INDEX idx_project_requirements_mobile (mobile),
  INDEX idx_project_requirements_quality (lead_quality),
  INDEX idx_project_requirements_status (status),
  INDEX idx_project_requirements_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_requirement_files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  requirement_id INT NOT NULL,
  file_type ENUM('floor_plan','site_photo','inspiration_image','existing_quotation','other') NOT NULL DEFAULT 'other',
  file_url VARCHAR(500) NOT NULL,
  original_name VARCHAR(255) DEFAULT NULL,
  mime_type VARCHAR(120) DEFAULT NULL,
  file_size INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_requirement_files_requirement FOREIGN KEY (requirement_id) REFERENCES project_requirements(id) ON DELETE CASCADE,
  INDEX idx_requirement_files_requirement (requirement_id, file_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS project_requirement_otps (
  id INT AUTO_INCREMENT PRIMARY KEY,
  requirement_id INT NOT NULL,
  mobile VARCHAR(30) NOT NULL,
  otp_hash VARCHAR(255) NOT NULL,
  attempts INT NOT NULL DEFAULT 0,
  expires_at DATETIME NOT NULL,
  verified_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_requirement_otps_requirement FOREIGN KEY (requirement_id) REFERENCES project_requirements(id) ON DELETE CASCADE,
  INDEX idx_requirement_otps_mobile (mobile),
  INDEX idx_requirement_otps_requirement (requirement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO project_budget_ranges (label, min_amount, max_amount, sort_order, is_active) VALUES
('Below ₹3 lakh', 0, 300000, 10, 1),
('₹3-5 lakh', 300000, 500000, 20, 1),
('₹5-8 lakh', 500000, 800000, 30, 1),
('₹8-12 lakh', 800000, 1200000, 40, 1),
('₹12-20 lakh', 1200000, 2000000, 50, 1),
('₹20-35 lakh', 2000000, 3500000, 60, 1),
('₹35-50 lakh', 3500000, 5000000, 70, 1),
('₹50 lakh+', 5000000, NULL, 80, 1),
('Not Decided', NULL, NULL, 90, 1)
ON DUPLICATE KEY UPDATE
  min_amount=VALUES(min_amount),
  max_amount=VALUES(max_amount),
  sort_order=VALUES(sort_order),
  is_active=VALUES(is_active),
  updated_at=NOW();
