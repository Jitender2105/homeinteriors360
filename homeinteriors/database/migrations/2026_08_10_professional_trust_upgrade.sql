SET NAMES utf8mb4;

ALTER TABLE pros
  ADD COLUMN IF NOT EXISTS verification_status_code ENUM('UNVERIFIED','PHONE_VERIFIED','IDENTITY_VERIFIED','BUSINESS_VERIFIED','PROFESSIONAL_VERIFIED') NOT NULL DEFAULT 'UNVERIFIED' AFTER verification_status,
  ADD COLUMN IF NOT EXISTS listing_tier ENUM('FREE','PAID','SPONSORED') NOT NULL DEFAULT 'FREE' AFTER is_premium,
  ADD COLUMN IF NOT EXISTS accepting_leads TINYINT(1) NOT NULL DEFAULT 1 AFTER listing_tier,
  ADD COLUMN IF NOT EXISTS suspension_reason TEXT DEFAULT NULL AFTER accepting_leads,
  ADD COLUMN IF NOT EXISTS verification_notes TEXT DEFAULT NULL AFTER suspension_reason;

ALTER TABLE projects
  ADD COLUMN IF NOT EXISTS moderation_status ENUM('DRAFT','SUBMITTED','APPROVED','REJECTED') NOT NULL DEFAULT 'APPROVED' AFTER testimonial_rating,
  ADD COLUMN IF NOT EXISTS project_verification_status ENUM('UNVERIFIED','ADMIN_REVIEWED','CLIENT_CONFIRMED','PROFESSIONAL_VERIFIED') NOT NULL DEFAULT 'UNVERIFIED' AFTER moderation_status,
  ADD COLUMN IF NOT EXISTS is_featured TINYINT(1) NOT NULL DEFAULT 0 AFTER project_verification_status,
  ADD COLUMN IF NOT EXISTS moderation_notes TEXT DEFAULT NULL AFTER is_featured;

CREATE TABLE IF NOT EXISTS professional_verification_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pro_id INT NOT NULL,
  old_status VARCHAR(80) DEFAULT NULL,
  new_status VARCHAR(80) NOT NULL,
  listing_tier VARCHAR(80) DEFAULT NULL,
  notes TEXT,
  performed_by INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_professional_verification_log_pro FOREIGN KEY (pro_id) REFERENCES pros(id) ON DELETE CASCADE,
  INDEX idx_professional_verification_log_pro (pro_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

UPDATE pros
SET verification_status_code = CASE
    WHEN verification_status = 1 AND verification_status_code = 'UNVERIFIED' THEN 'PROFESSIONAL_VERIFIED'
    ELSE verification_status_code
  END,
  listing_tier = CASE
    WHEN is_premium = 1 AND listing_tier = 'FREE' THEN 'PAID'
    ELSE listing_tier
  END;
