-- Safe upgrade for existing HomeInteriors360 production DB
SET NAMES utf8mb4;

ALTER TABLE pros
  ADD COLUMN IF NOT EXISTS profile_description TEXT,
  ADD COLUMN IF NOT EXISTS primary_work_type VARCHAR(120) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS primary_work_area VARCHAR(120) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS min_project_value DECIMAL(12,2) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS max_project_value DECIMAL(12,2) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS consultation_fee DECIMAL(12,2) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS materials_json JSON DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS design_styles_json JSON DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS languages_json JSON DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS certifications_json JSON DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS response_time_hours INT DEFAULT NULL;

ALTER TABLE projects
  ADD COLUMN IF NOT EXISTS slug VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS project_description TEXT,
  ADD COLUMN IF NOT EXISTS project_duration_label VARCHAR(120) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS area_of_work VARCHAR(120) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS materials_json JSON DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS video_url VARCHAR(500) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS design_style VARCHAR(120) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS team_size INT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS warranty_years INT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS testimonial_client_name VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS testimonial_text TEXT,
  ADD COLUMN IF NOT EXISTS testimonial_rating TINYINT DEFAULT NULL;

UPDATE projects
SET slug = CONCAT('project-', id)
WHERE slug IS NULL OR slug = '';

ALTER TABLE projects
  MODIFY COLUMN slug VARCHAR(255) NOT NULL,
  ADD UNIQUE KEY uniq_projects_slug (slug),
  ADD INDEX idx_projects_work_type (work_type),
  ADD INDEX idx_projects_area_of_work (area_of_work);

ALTER TABLE pros
  ADD INDEX idx_pro_primary_work_type (primary_work_type),
  ADD INDEX idx_pro_primary_work_area (primary_work_area);

ALTER TABLE reviews
  ADD COLUMN IF NOT EXISTS work_type VARCHAR(120) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS area_of_work VARCHAR(120) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS materials_highlight VARCHAR(255) DEFAULT NULL;

UPDATE pros
SET
  primary_work_type = COALESCE(NULLIF(primary_work_type, ''), 'Full Home'),
  primary_work_area = COALESCE(NULLIF(primary_work_area, ''), 'Apartments'),
  profile_description = COALESCE(profile_description, bio);

INSERT INTO site_content (key_name, content_value, content_type)
VALUES
  ('seo.portfolio.title_suffix', 'Portfolio | HomeInteriors360', 'text'),
  ('directory.filter.work_type', 'Type of Work', 'text'),
  ('directory.filter.work_area', 'Area of Work', 'text'),
  ('profile.lead.title', 'Get Project Proposal from this Professional', 'text'),
  ('profile.materials.title', 'Preferred Materials', 'text'),
  ('profile.work_type', 'Type of Work', 'text'),
  ('profile.work_area', 'Area of Work', 'text'),
  ('profile.response_time', 'Average Response Time', 'text'),
  ('profile.languages', 'Languages', 'text'),
  ('profile.materials_used', 'Materials Used', 'text'),
  ('profile.project_details', 'View Project Details', 'text'),
  ('portfolio.hero.title', 'Project Portfolio Details', 'text'),
  ('portfolio.video.title', 'Project Video', 'text'),
  ('portfolio.more_projects', 'More Projects by this Professional', 'text'),
  ('portfolio.design_style', 'Design Style', 'text'),
  ('portfolio.team_size', 'Team Size', 'text'),
  ('portfolio.warranty', 'Warranty', 'text')
ON DUPLICATE KEY UPDATE content_value = VALUES(content_value), content_type = VALUES(content_type);
