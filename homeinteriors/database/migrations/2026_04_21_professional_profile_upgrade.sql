-- Safe upgrade for existing HomeInteriors360 production DB
SET NAMES utf8mb4;

ALTER TABLE pros
  ADD COLUMN IF NOT EXISTS profile_description TEXT,
  ADD COLUMN IF NOT EXISTS primary_work_type VARCHAR(120) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS primary_work_area VARCHAR(120) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS projects_delivered INT NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS is_premium BOOLEAN NOT NULL DEFAULT FALSE,
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

ALTER TABLE leads
  ADD COLUMN IF NOT EXISTS society_area VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS budget VARCHAR(120) DEFAULT NULL;

UPDATE pros
SET
  primary_work_type = COALESCE(NULLIF(primary_work_type, ''), 'Full Home'),
  primary_work_area = COALESCE(NULLIF(primary_work_area, ''), 'Apartments'),
  profile_description = COALESCE(profile_description, bio),
  is_premium = CASE
    WHEN slug IN ('ananya-sharma-interiors', 'raghav-menon-architects') THEN 1
    ELSE COALESCE(is_premium, 0)
  END,
  projects_delivered = CASE
    WHEN projects_delivered > 0 THEN projects_delivered
    ELSE COALESCE((SELECT COUNT(*) FROM projects WHERE projects.pro_id = pros.id), 0)
  END;

INSERT INTO site_content (key_name, content_value, content_type)
VALUES
  ('home.hero.eyebrow', 'TURNKEY INTERIORS, BEAUTIFULLY EXECUTED', 'text'),
  ('home.hero.title', 'Design your dream home with verified experts.', 'text'),
  ('home.hero.subtitle', 'From modular kitchens to complete home interiors, compare top-rated professionals and start with confidence.', 'text'),
  ('home.hero.assets', '["https://images.unsplash.com/photo-1616137466211-f939a420be84?auto=format&fit=crop&w=1800&q=80","https://images.unsplash.com/photo-1600607688969-a5bfcd646154?auto=format&fit=crop&w=1800&q=80"]', 'json'),
  ('home.lead.title', 'Get Free Design Consultation', 'text'),
  ('home.lead.step1_label', 'Choose your city', 'text'),
  ('home.lead.step2_label', 'What do you want to design?', 'text'),
  ('home.lead.step3_label', 'Share contact details', 'text'),
  ('home.lead.step_next', 'Next', 'text'),
  ('home.lead.step_prev', 'Back', 'text'),
  ('home.lead.submit', 'Request Callback', 'text'),
  ('home.lead.success', 'Thank you. Our team will call you shortly.', 'text'),
  ('home.aggregators.title', 'Top Aggregators', 'text'),
  ('home.aggregators.subtitle', 'Compare verified professionals and choose what fits your vision.', 'text'),
  ('home.services.title', 'Services', 'text'),
  ('home.services.items', '[{"key":"kitchen","title":"Kitchen","description":"Smart layouts, premium finishes, durable modules.","icon":"🍽️","image":"https://images.unsplash.com/photo-1556912167-f556f1f39fdf?auto=format&fit=crop&w=1200&q=80"},{"key":"wardrobe","title":"Wardrobe","description":"Tailored storage for every room.","icon":"🗄️","image":"https://images.unsplash.com/photo-1616594039964-3dbbb0bd2e8f?auto=format&fit=crop&w=1200&q=80"},{"key":"full_home","title":"Full Home","description":"End-to-end interiors with single-point accountability.","icon":"🏠","image":"https://images.unsplash.com/photo-1615529182904-14819c35db37?auto=format&fit=crop&w=1200&q=80"}]', 'json'),
  ('home.testimonials.title', 'Client Testimonials', 'text'),
  ('home.testimonials.items', '[{"name":"Priya S","text":"Excellent planning and execution. The handover quality was premium.","location":"Gurgaon","image":"https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=600"},{"name":"Vikas A","text":"Great design team and responsive project updates.","location":"Noida","image":"https://images.pexels.com/photos/2379004/pexels-photo-2379004.jpeg?auto=compress&cs=tinysrgb&w=600"},{"name":"Karan M","text":"We loved the modular kitchen and wardrobe detailing.","location":"Delhi","image":"https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=600"}]', 'json'),
  ('home.brands.title', 'Brands We Use', 'text'),
  ('home.brands.logos', '[{"name":"Hafele","url":"https://upload.wikimedia.org/wikipedia/commons/8/80/H%C3%A4fele_Logo.svg"},{"name":"Hettich","url":"https://upload.wikimedia.org/wikipedia/commons/6/66/Hettich_logo.svg"},{"name":"Asian Paints","url":"https://upload.wikimedia.org/wikipedia/commons/b/ba/Asian_Paints_Logo.svg"},{"name":"Kajaria","url":"https://upload.wikimedia.org/wikipedia/commons/3/31/Kajaria_Logo.svg"}]', 'json'),
  ('home.trust.title', 'Why Trust Us', 'text'),
  ('home.trust.items', '["Verified professionals and transparent pricing","Dedicated project support","Quality checks at every stage","On-time milestone tracking"]', 'json'),
  ('home.usp.title', 'Our USP', 'text'),
  ('home.usp.items', '["Centralized discovery + lead management","Data-driven cost estimator","Verified expert network","Content-managed growth architecture"]', 'json'),
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
  ('ui.name', 'Name', 'text'),
  ('ui.phone', 'Phone', 'text'),
  ('ui.city', 'City', 'text'),
  ('ui.requirement', 'Requirement', 'text'),
  ('ui.society_area', 'Society / Area', 'text'),
  ('ui.budget', 'Budget', 'text'),
  ('portfolio.hero.title', 'Project Portfolio Details', 'text'),
  ('portfolio.video.title', 'Project Video', 'text'),
  ('portfolio.more_projects', 'More Projects by this Professional', 'text'),
  ('portfolio.design_style', 'Design Style', 'text'),
  ('portfolio.team_size', 'Team Size', 'text'),
  ('portfolio.warranty', 'Warranty', 'text')
ON DUPLICATE KEY UPDATE content_value = VALUES(content_value), content_type = VALUES(content_type);
