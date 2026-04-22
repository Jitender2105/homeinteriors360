-- HomeInteriors360: Professional Profiles + Portfolio Architecture

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS projects;
DROP TABLE IF EXISTS leads;
DROP TABLE IF EXISTS pros;
DROP TABLE IF EXISTS site_content;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  email VARCHAR(255) DEFAULT NULL,
  role ENUM('admin','super_admin') NOT NULL DEFAULT 'admin',
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  profile_pic VARCHAR(500) DEFAULT NULL,
  cover_photo VARCHAR(500) DEFAULT NULL,
  role ENUM('Architect','Designer','Contractor') NOT NULL,
  profile_description TEXT,
  specialization VARCHAR(255) DEFAULT NULL,
  primary_work_type VARCHAR(120) DEFAULT NULL,
  primary_work_area VARCHAR(120) DEFAULT NULL,
  verification_status BOOLEAN NOT NULL DEFAULT FALSE,
  is_premium BOOLEAN NOT NULL DEFAULT FALSE,
  rating DECIMAL(3,2) NOT NULL DEFAULT 0,
  years_experience INT NOT NULL DEFAULT 0,
  projects_delivered INT NOT NULL DEFAULT 0,
  starting_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  min_project_value DECIMAL(12,2) DEFAULT NULL,
  max_project_value DECIMAL(12,2) DEFAULT NULL,
  consultation_fee DECIMAL(12,2) DEFAULT NULL,
  city VARCHAR(120) DEFAULT NULL,
  service_areas JSON DEFAULT NULL,
  materials_json JSON DEFAULT NULL,
  design_styles_json JSON DEFAULT NULL,
  languages_json JSON DEFAULT NULL,
  certifications_json JSON DEFAULT NULL,
  response_time_hours INT DEFAULT NULL,
  bio TEXT,
  why_work_with_me TEXT,
  offerings_json JSON DEFAULT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_pro_role (role),
  INDEX idx_pro_city (city),
  INDEX idx_pro_primary_work_type (primary_work_type),
  INDEX idx_pro_primary_work_area (primary_work_area),
  INDEX idx_pro_verified (verification_status),
  INDEX idx_pro_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pro_id INT NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  project_name VARCHAR(255) NOT NULL,
  project_description TEXT,
  total_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  bhk_type ENUM('1BHK','2BHK','3BHK','4BHK','Villa','Commercial') NOT NULL DEFAULT '2BHK',
  year_completed YEAR DEFAULT NULL,
  timeline_months INT NOT NULL DEFAULT 0,
  project_duration_label VARCHAR(120) DEFAULT NULL,
  location VARCHAR(255) DEFAULT NULL,
  work_type VARCHAR(120) DEFAULT NULL,
  area_of_work VARCHAR(120) DEFAULT NULL,
  materials_json JSON DEFAULT NULL,
  media_json JSON DEFAULT NULL,
  video_url VARCHAR(500) DEFAULT NULL,
  design_style VARCHAR(120) DEFAULT NULL,
  team_size INT DEFAULT NULL,
  warranty_years INT DEFAULT NULL,
  testimonial_client_name VARCHAR(255) DEFAULT NULL,
  testimonial_text TEXT,
  testimonial_rating TINYINT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_projects_pro FOREIGN KEY (pro_id) REFERENCES pros(id) ON DELETE CASCADE,
  INDEX idx_projects_pro (pro_id),
  INDEX idx_projects_location (location),
  INDEX idx_projects_work_type (work_type),
  INDEX idx_projects_area_of_work (area_of_work)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pro_id INT NOT NULL,
  client_name VARCHAR(255) NOT NULL,
  rating TINYINT NOT NULL DEFAULT 5,
  review_text TEXT NOT NULL,
  verified_purchase BOOLEAN NOT NULL DEFAULT FALSE,
  work_type VARCHAR(120) DEFAULT NULL,
  area_of_work VARCHAR(120) DEFAULT NULL,
  materials_highlight VARCHAR(255) DEFAULT NULL,
  photos_json JSON DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reviews_pro FOREIGN KEY (pro_id) REFERENCES pros(id) ON DELETE CASCADE,
  INDEX idx_reviews_pro (pro_id),
  INDEX idx_reviews_verified (verified_purchase)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE site_content (
  id INT AUTO_INCREMENT PRIMARY KEY,
  key_name VARCHAR(190) NOT NULL UNIQUE,
  content_value LONGTEXT NOT NULL,
  content_type ENUM('text','json','html') NOT NULL DEFAULT 'text',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_site_content_key (key_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE leads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  city VARCHAR(120) NOT NULL,
  society_area VARCHAR(255) DEFAULT NULL,
  budget VARCHAR(120) DEFAULT NULL,
  requirement TEXT NOT NULL,
  pro_id INT DEFAULT NULL,
  source ENUM('homepage','profile','calculator') NOT NULL DEFAULT 'homepage',
  status ENUM('new','contacted','converted') NOT NULL DEFAULT 'new',
  floor_plan ENUM('1BHK','2BHK','3BHK','4BHK') DEFAULT NULL,
  package_tier ENUM('Essential','Premium','Luxury') DEFAULT NULL,
  rooms_json JSON DEFAULT NULL,
  estimate DECIMAL(12,2) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_leads_pro FOREIGN KEY (pro_id) REFERENCES pros(id) ON DELETE SET NULL,
  INDEX idx_leads_status (status),
  INDEX idx_leads_city (city),
  INDEX idx_leads_source (source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (username, password_hash, email, role, is_active)
VALUES
  ('admin', '$2y$12$Pvz8JNRFpPwoAdeEEn2/gO7ujmJNBng4yu.WFM2e3JGsw1XI8QQDa', 'admin@homeinteriors360.com', 'super_admin', 1)
ON DUPLICATE KEY UPDATE email = VALUES(email), role = VALUES(role), is_active = VALUES(is_active);

INSERT INTO pros (
  full_name, slug, profile_pic, cover_photo, role, profile_description, specialization,
  primary_work_type, primary_work_area, verification_status, is_premium, rating, years_experience, projects_delivered,
  starting_price, min_project_value, max_project_value, consultation_fee, city, service_areas,
  materials_json, design_styles_json, languages_json, certifications_json, response_time_hours,
  bio, why_work_with_me, offerings_json, is_active
)
VALUES
(
  'Ananya Sharma Interiors',
  'ananya-sharma-interiors',
  'https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=800',
  'https://images.unsplash.com/photo-1616137466211-f939a420be84?auto=format&fit=crop&w=1600&q=80',
  'Designer',
  'Premium turnkey interiors with deep focus on Indian family lifestyles and practical luxury.',
  'Residential Turnkey Designer',
  'Full Home',
  'Apartments',
  1,
  1,
  4.9,
  12,
  64,
  550000,
  450000,
  3500000,
  2500,
  'Gurgaon',
  JSON_ARRAY('Delhi', 'Gurgaon', 'Noida'),
  JSON_ARRAY('BWP Plywood', 'Hafele Hardware', 'Acrylic Laminate', 'Quartz Countertop'),
  JSON_ARRAY('Modern Indian', 'Contemporary', 'Minimal Luxe'),
  JSON_ARRAY('Hindi', 'English'),
  JSON_ARRAY('ISO Process Partner', 'Hafele Certified Installer Network'),
  3,
  'We create homes that balance aesthetic clarity, storage logic, and long-term durability.',
  'Single-point accountability, transparent costing, and weekly progress reviews.',
  JSON_ARRAY('Modular Kitchen', 'Wardrobes', 'TV Unit', 'False Ceiling', 'Full Home Interiors'),
  1
),
(
  'Raghav Menon Architects',
  'raghav-menon-architects',
  'https://images.pexels.com/photos/2379004/pexels-photo-2379004.jpeg?auto=compress&cs=tinysrgb&w=800',
  'https://images.unsplash.com/photo-1493666438817-866a91353ca9?auto=format&fit=crop&w=1600&q=80',
  'Architect',
  'Architecture-led interiors for villas and large format residences.',
  'Architecture + Interior Integration',
  'Renovation',
  'Villa',
  1,
  1,
  4.7,
  15,
  41,
  850000,
  700000,
  5500000,
  4000,
  'Delhi',
  JSON_ARRAY('Delhi', 'Noida', 'Gurgaon'),
  JSON_ARRAY('Natural Veneer', 'PU Finish', 'Engineered Wood', 'Imported Marble'),
  JSON_ARRAY('Contemporary', 'Neo-classical', 'Warm Modern'),
  JSON_ARRAY('Hindi', 'English', 'Malayalam'),
  JSON_ARRAY('COA Registered Practice', 'IGBC Trained Team'),
  5,
  'We design architecture and interiors as one system for stronger flow and better quality.',
  'Precision planning, disciplined detailing, and high-end finish control.',
  JSON_ARRAY('Space Planning', 'Renovation', 'Civil Layout', 'Lighting Design', 'Turnkey Build Coordination'),
  1
),
(
  'Vikas Bedi Contracting',
  'vikas-bedi-contracting',
  'https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=800',
  'https://images.unsplash.com/photo-1600121848594-d8644e57abab?auto=format&fit=crop&w=1600&q=80',
  'Contractor',
  'Execution-first contractor with strong civil, MEP, and modular coordination.',
  'Turnkey Contractor',
  'Execution',
  'Commercial',
  1,
  0,
  4.6,
  14,
  89,
  400000,
  300000,
  4000000,
  1800,
  'Noida',
  JSON_ARRAY('Noida', 'Delhi', 'Gurgaon'),
  JSON_ARRAY('Greenply', 'CenturyPly', 'Asian Paints', 'Kajaria'),
  JSON_ARRAY('Practical Modern', 'Industrial', 'Budget Premium'),
  JSON_ARRAY('Hindi', 'English', 'Punjabi'),
  JSON_ARRAY('Site Safety Certified Supervisors'),
  6,
  'We execute interiors with strict supervision, quality checkpoints, and practical scheduling.',
  'Daily updates, milestone billing, and on-site accountability till handover.',
  JSON_ARRAY('Civil Work', 'Electrical', 'Plumbing', 'Modular Installation', 'Paint & Polish'),
  1
);

INSERT INTO projects (
  pro_id, slug, project_name, project_description, total_cost, bhk_type, year_completed,
  timeline_months, project_duration_label, location, work_type, area_of_work, materials_json,
  media_json, video_url, design_style, team_size, warranty_years,
  testimonial_client_name, testimonial_text, testimonial_rating
)
VALUES
(
  1,
  'dlf-camellias-4bhk-turnkey',
  'DLF Camellias 4BHK Turnkey',
  'Complete 4BHK transformation with bespoke storage, Italian-themed kitchen finishes, and layered lighting.',
  4200000,
  '4BHK',
  2025,
  7,
  '7 months',
  'DLF Camellias, Gurgaon',
  'Full Home',
  'Apartments',
  JSON_ARRAY('BWP Plywood', 'Acrylic Shutters', 'Hafele Fittings', 'Quartz Countertop'),
  JSON_ARRAY(
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1616594039964-3dbbb0bd2e8f?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1615529182904-14819c35db37?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1616046229478-9901c5536a45?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1500&q=80'
  ),
  'https://www.youtube.com/embed/ScMzIvxBSi4',
  'Modern Indian',
  18,
  10,
  'Ritika Arora',
  'Execution quality and detailing exceeded expectations. The site updates were always transparent.',
  5
),
(
  1,
  'sector56-kitchen-wardrobe-upgrade',
  'Sector 56 Kitchen + Wardrobe Upgrade',
  'Targeted renovation for kitchen workflow and bedroom storage with better ergonomics and finish quality.',
  980000,
  '3BHK',
  2024,
  3,
  '12 weeks',
  'Sector 56, Gurgaon',
  'Kitchen',
  'Apartments',
  JSON_ARRAY('Marine Plywood', 'PU Paint', 'Hettich Channels'),
  JSON_ARRAY(
    'https://images.unsplash.com/photo-1556912167-f556f1f39fdf?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1560185127-6ed189bf02f4?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1600489000022-c2086d79f9d4?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?auto=format&fit=crop&w=1500&q=80'
  ),
  NULL,
  'Contemporary',
  9,
  5,
  'Manish Jain',
  'Great storage planning and a very neat finish in both kitchen and wardrobes.',
  5
),
(
  2,
  'greater-kailash-villa-renovation',
  'Greater Kailash Villa Renovation',
  'Architecture-led renovation with façade refresh, interior remodelling, and premium material layering.',
  6100000,
  'Villa',
  2025,
  9,
  '9 months',
  'Greater Kailash, Delhi',
  'Renovation',
  'Villa',
  JSON_ARRAY('Natural Veneer', 'Imported Marble', 'Solid Wood Doors'),
  JSON_ARRAY(
    'https://images.unsplash.com/photo-1616486029423-aaa4789e8c9a?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1600607687644-c7171b42498f?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1616627459589-8f74289e2f79?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=1500&q=80'
  ),
  NULL,
  'Neo-classical',
  24,
  12,
  'S. Narang',
  'From design approvals to execution sequencing, everything stayed very structured.',
  5
),
(
  3,
  'noida-corporate-execution-package',
  'Noida Corporate Office Execution',
  'Fast-paced commercial office fit-out with MEP coordination and robust site governance.',
  2800000,
  'Commercial',
  2024,
  4,
  '16 weeks',
  'Sector 62, Noida',
  'Execution',
  'Commercial',
  JSON_ARRAY('Gypsum Board', 'Modular Workstations', 'Commercial Grade Laminate'),
  JSON_ARRAY(
    'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1500&q=80'
  ),
  NULL,
  'Industrial',
  15,
  3,
  'Arpit Khanna',
  'Clear daily supervision and dependable delivery against our timeline.',
  4
);

INSERT INTO reviews (pro_id, client_name, rating, review_text, verified_purchase, work_type, area_of_work, materials_highlight, photos_json)
VALUES
  (1, 'Rohit Mehra', 5, 'Excellent design clarity and very disciplined execution management.', 1, 'Full Home', 'Apartments', 'Acrylic + Quartz', JSON_ARRAY('https://images.unsplash.com/photo-1600210491892-03d54c0aaf87?auto=format&fit=crop&w=800&q=80')),
  (1, 'Neha Bansal', 5, 'Great balance between aesthetics and storage practicality.', 1, 'Kitchen', 'Apartments', 'Hafele + Marine Ply', JSON_ARRAY()),
  (2, 'Sanjana Arora', 4, 'Strong architecture-led planning and better site sequencing.', 0, 'Renovation', 'Villa', 'Natural Veneer', JSON_ARRAY()),
  (3, 'Akash Verma', 5, 'Very dependable contractor team and transparent milestone billing.', 1, 'Execution', 'Commercial', 'CenturyPly + Kajaria', JSON_ARRAY());

INSERT INTO site_content (key_name, content_value, content_type)
VALUES
  ('seo.home.title', 'HomeInteriors360 | Premium Interior Design in Delhi NCR', 'text'),
  ('seo.home.description', 'Find verified architects, interior designers, and contractors for your home project.', 'text'),
  ('seo.directory.title', 'Find Professionals | HomeInteriors360', 'text'),
  ('seo.profile.title_suffix', 'Profile | HomeInteriors360', 'text'),
  ('seo.portfolio.title_suffix', 'Portfolio | HomeInteriors360', 'text'),
  ('seo.calculator.title', 'Design Cost Calculator | HomeInteriors360', 'text'),

  ('nav.home', 'Home', 'text'),
  ('nav.directory', 'Find Professionals', 'text'),
  ('nav.calculator', 'Cost Calculator', 'text'),
  ('nav.admin', 'Admin', 'text'),

  ('footer.tagline', 'Designed for modern homes across Delhi NCR', 'text'),
  ('footer.copy', 'HomeInteriors360. All rights reserved.', 'text'),

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

  ('directory.title', 'Find Professionals', 'text'),
  ('directory.subtitle', 'Filter by profession, budget, work type, and area to shortlist instantly.', 'text'),
  ('directory.filter.role', 'Profession', 'text'),
  ('directory.filter.city', 'City', 'text'),
  ('directory.filter.budget', 'Budget Range', 'text'),
  ('directory.filter.work_type', 'Type of Work', 'text'),
  ('directory.filter.work_area', 'Area of Work', 'text'),
  ('directory.empty', 'No professionals found for current filters.', 'text'),
  ('directory.cta', 'View Profile', 'text'),
  ('directory.verified', 'Verified', 'text'),
  ('directory.starting_from', 'Starting Price', 'text'),
  ('directory.experience', 'Years of Experience', 'text'),

  ('profile.cta', 'Request Quote', 'text'),
  ('profile.lead.title', 'Get Project Proposal from this Professional', 'text'),
  ('profile.portfolio.title', 'Portfolio', 'text'),
  ('profile.reviews.title', 'Customer Reviews', 'text'),
  ('profile.expertise.title', 'Expertise & Offerings', 'text'),
  ('profile.materials.title', 'Preferred Materials', 'text'),
  ('profile.work_type', 'Type of Work', 'text'),
  ('profile.work_area', 'Area of Work', 'text'),
  ('profile.response_time', 'Average Response Time', 'text'),
  ('profile.languages', 'Languages', 'text'),
  ('profile.verified_purchase', 'Verified Purchase', 'text'),
  ('profile.timeline', 'Timeline', 'text'),
  ('profile.amount_spent', 'Total Amount Spent', 'text'),
  ('profile.area', 'Area of Service', 'text'),
  ('profile.materials_used', 'Materials Used', 'text'),
  ('profile.project_details', 'View Project Details', 'text'),

  ('portfolio.hero.title', 'Project Portfolio Details', 'text'),
  ('portfolio.video.title', 'Project Video', 'text'),
  ('portfolio.more_projects', 'More Projects by this Professional', 'text'),
  ('portfolio.design_style', 'Design Style', 'text'),
  ('portfolio.team_size', 'Team Size', 'text'),
  ('portfolio.warranty', 'Warranty', 'text'),

  ('ui.name', 'Name', 'text'),
  ('ui.phone', 'Phone', 'text'),
  ('ui.city', 'City', 'text'),
  ('ui.requirement', 'Requirement', 'text'),
  ('ui.society_area', 'Society / Area', 'text'),
  ('ui.budget', 'Budget', 'text')
ON DUPLICATE KEY UPDATE content_value = VALUES(content_value), content_type = VALUES(content_type);
