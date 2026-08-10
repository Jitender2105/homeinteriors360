CREATE TABLE IF NOT EXISTS mobile_professional_otps (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pro_id INT DEFAULT NULL,
  user_id INT DEFAULT NULL,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  otp_hash VARCHAR(255) NOT NULL,
  attempts INT NOT NULL DEFAULT 0,
  expires_at DATETIME NOT NULL,
  verified_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_mobile_professional_email (email),
  INDEX idx_mobile_professional_phone (phone),
  INDEX idx_mobile_professional_pro (pro_id),
  CONSTRAINT fk_mobile_professional_otp_pro FOREIGN KEY (pro_id) REFERENCES pros(id) ON DELETE SET NULL,
  CONSTRAINT fk_mobile_professional_otp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
