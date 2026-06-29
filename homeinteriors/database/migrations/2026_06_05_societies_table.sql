CREATE TABLE IF NOT EXISTS societies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  city VARCHAR(120) NOT NULL DEFAULT '',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_societies_city_name (city, name),
  INDEX idx_societies_name (name),
  INDEX idx_societies_city (city),
  INDEX idx_societies_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO societies (name, city, is_active)
SELECT DISTINCT TRIM(society_area), COALESCE(NULLIF(TRIM(city), ''), ''), 1
FROM leads
WHERE society_area IS NOT NULL AND TRIM(society_area) <> ''
ON DUPLICATE KEY UPDATE is_active = VALUES(is_active), updated_at = CURRENT_TIMESTAMP;
