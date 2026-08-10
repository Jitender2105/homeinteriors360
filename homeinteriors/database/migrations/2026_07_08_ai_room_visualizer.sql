SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS ai_visualizer_styles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  style_key VARCHAR(80) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  description TEXT NOT NULL,
  materials_palette TEXT NOT NULL,
  signature_elements TEXT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_visualizer_renders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  lead_id INT DEFAULT NULL,
  name VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  city VARCHAR(120) DEFAULT NULL,
  locality VARCHAR(190) DEFAULT NULL,
  room_type VARCHAR(80) NOT NULL,
  style_key VARCHAR(80) NOT NULL,
  budget_tier ENUM('economy','mid-range','premium','luxury') NOT NULL DEFAULT 'mid-range',
  detected_elements TEXT,
  freeform_notes TEXT,
  original_image_url VARCHAR(500) NOT NULL,
  rendered_image_url VARCHAR(500) DEFAULT NULL,
  prompt TEXT NOT NULL,
  negative_prompt TEXT NOT NULL,
  generation_status ENUM('prompt_ready','generated','failed') NOT NULL DEFAULT 'prompt_ready',
  provider_response LONGTEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_ai_visualizer_lead (lead_id),
  INDEX idx_ai_visualizer_style (style_key),
  INDEX idx_ai_visualizer_city (city),
  CONSTRAINT fk_ai_visualizer_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ai_visualizer_styles (style_key, name, description, materials_palette, signature_elements, sort_order)
VALUES
('modern', 'Modern', 'Clean geometry, low visual clutter', 'Matte white/grey walls, walnut wood, black metal accents, glass', 'Handleless cabinetry, recessed lighting, statement pendant light', 10),
('minimalist', 'Minimalist', 'Maximum restraint, function-first', 'Whites, off-whites, light oak, single accent color', 'Hidden storage, no ornamentation, negative space', 20),
('traditional', 'Traditional', 'Warm, classic, ornate detailing', 'Rich wood tones, brass fixtures, warm neutrals, jewel accent', 'Crown molding, carved furniture, layered textiles', 30),
('scandinavian', 'Scandinavian', 'Light, airy, cozy-functional', 'Pale wood, white walls, soft pastels, natural textiles', 'Wool throws, simple wood furniture, abundant daylight', 40),
('industrial', 'Industrial', 'Raw, urban, exposed materials', 'Exposed brick/concrete, black steel, reclaimed wood', 'Edison bulb fixtures, open shelving, metal-frame furniture', 50),
('contemporary_luxury', 'Contemporary Luxury', 'Elevated, curated, statement pieces', 'Marble, brushed gold/brass, deep neutrals, velvet', 'Statement chandelier, large-format tiles, curated art wall', 60),
('bohemian', 'Bohemian', 'Layered, eclectic, textured', 'Warm earth tones, rattan, mixed patterns, greenery', 'Macrame, layered rugs, indoor plants, woven textures', 70),
('art_deco', 'Art Deco', 'Bold geometry, glamour', 'Emerald/navy, brass, black lacquer, geometric patterns', 'Fan motifs, mirrored surfaces, bold symmetry', 80),
('coastal', 'Coastal', 'Breezy, relaxed, light', 'Whites, sandy beige, ocean blue accents, natural fiber', 'Light linen textiles, woven textures, airy sheers', 90)
ON DUPLICATE KEY UPDATE
  name=VALUES(name),
  description=VALUES(description),
  materials_palette=VALUES(materials_palette),
  signature_elements=VALUES(signature_elements),
  sort_order=VALUES(sort_order),
  is_active=1,
  updated_at=NOW();
