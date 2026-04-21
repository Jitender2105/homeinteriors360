-- HomeInteriors360: Core Database & Global Architecture
-- Apply this SQL in the target database selected by your connection.

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
  specialization VARCHAR(255) DEFAULT NULL,
  verification_status BOOLEAN NOT NULL DEFAULT FALSE,
  rating DECIMAL(3,2) NOT NULL DEFAULT 0,
  years_experience INT NOT NULL DEFAULT 0,
  starting_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  city VARCHAR(120) DEFAULT NULL,
  service_areas JSON DEFAULT NULL,
  bio TEXT,
  why_work_with_me TEXT,
  offerings_json JSON DEFAULT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_pro_role (role),
  INDEX idx_pro_city (city),
  INDEX idx_pro_verified (verification_status),
  INDEX idx_pro_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pro_id INT NOT NULL,
  project_name VARCHAR(255) NOT NULL,
  total_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  bhk_type ENUM('1BHK','2BHK','3BHK','4BHK','Villa','Commercial') NOT NULL DEFAULT '2BHK',
  year_completed YEAR DEFAULT NULL,
  timeline_months INT NOT NULL DEFAULT 0,
  location VARCHAR(255) DEFAULT NULL,
  work_type VARCHAR(120) DEFAULT NULL,
  media_json JSON DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_projects_pro FOREIGN KEY (pro_id) REFERENCES pros(id) ON DELETE CASCADE,
  INDEX idx_projects_pro (pro_id),
  INDEX idx_projects_location (location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pro_id INT NOT NULL,
  client_name VARCHAR(255) NOT NULL,
  rating TINYINT NOT NULL DEFAULT 5,
  review_text TEXT NOT NULL,
  verified_purchase BOOLEAN NOT NULL DEFAULT FALSE,
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

INSERT INTO pros (full_name, slug, profile_pic, cover_photo, role, specialization, verification_status, rating, years_experience, starting_price, city, service_areas, bio, why_work_with_me, offerings_json, is_active)
VALUES
  (
    'Aarav Design Studio',
    'aarav-design-studio',
    'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1400&q=80',
    'Designer',
    'Luxury Residential Interiors',
    1,
    4.8,
    11,
    450000,
    'Gurgaon',
    JSON_ARRAY('Delhi', 'Gurgaon', 'Noida'),
    'We design warm, functional and high-performance homes for modern families.',
    'Transparent costing, execution-ready drawings, and strong site governance.',
    JSON_ARRAY('Modular Kitchen', 'Wardrobes', 'Living Room Styling', 'Turnkey Full Home'),
    1
  ),
  (
    'Niva Architects',
    'niva-architects',
    'https://images.unsplash.com/photo-1504593811423-6dd665756598?auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1493666438817-866a91353ca9?auto=format&fit=crop&w=1400&q=80',
    'Architect',
    'Space Planning & Renovation',
    0,
    4.5,
    8,
    350000,
    'Delhi',
    JSON_ARRAY('Delhi', 'Noida'),
    'Design-first architecture with practical construction detailing.',
    'Strong planning discipline and seamless collaboration with contractors.',
    JSON_ARRAY('Renovation Design', 'Civil Layouts', 'Lighting Planning'),
    1
  ),
  (
    'CraftBuild Contractors',
    'craftbuild-contractors',
    'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=600&q=80',
    'https://images.unsplash.com/photo-1600121848594-d8644e57abab?auto=format&fit=crop&w=1400&q=80',
    'Contractor',
    'End-to-End Turnkey Execution',
    1,
    4.7,
    13,
    550000,
    'Noida',
    JSON_ARRAY('Delhi', 'Gurgaon', 'Noida'),
    'Execution specialists for premium apartment and villa interiors.',
    'Quality control checkpoints and milestone-based progress tracking.',
    JSON_ARRAY('Civil Work', 'False Ceiling', 'Electrical & Plumbing', 'Turnkey Interiors'),
    1
  );

INSERT INTO projects (pro_id, project_name, total_cost, bhk_type, year_completed, timeline_months, location, work_type, media_json)
VALUES
  (1, 'Aria Residences 3BHK', 1850000, '3BHK', 2025, 5, 'Sector 56, Gurgaon', 'Full Home', JSON_ARRAY('https://images.unsplash.com/photo-1615529182904-14819c35db37?auto=format&fit=crop&w=1200&q=80', 'https://images.unsplash.com/photo-1600607687644-c7171b42498f?auto=format&fit=crop&w=1200&q=80')),
  (1, 'DLF Penthouse Kitchen', 680000, '2BHK', 2024, 2, 'DLF Phase 3, Gurgaon', 'Kitchen', JSON_ARRAY('https://images.unsplash.com/photo-1556912167-f556f1f39fdf?auto=format&fit=crop&w=1200&q=80')),
  (2, 'South Delhi Renovation', 1250000, '4BHK', 2025, 6, 'Greater Kailash, Delhi', 'Full Home', JSON_ARRAY('https://images.unsplash.com/photo-1616486029423-aaa4789e8c9a?auto=format&fit=crop&w=1200&q=80')),
  (3, 'Noida Luxury Wardrobes', 520000, '3BHK', 2024, 2, 'Sector 137, Noida', 'Wardrobe', JSON_ARRAY('https://images.unsplash.com/photo-1616594039964-3dbbb0bd2e8f?auto=format&fit=crop&w=1200&q=80'));

INSERT INTO reviews (pro_id, client_name, rating, review_text, verified_purchase, photos_json)
VALUES
  (1, 'Rohit Mehra', 5, 'Flawless execution and very clear communication throughout.', 1, JSON_ARRAY('https://images.unsplash.com/photo-1600210491892-03d54c0aaf87?auto=format&fit=crop&w=800&q=80')),
  (1, 'Neha Bansal', 5, 'The team understood our lifestyle and delivered exactly what we wanted.', 1, JSON_ARRAY()),
  (2, 'Sanjana Arora', 4, 'Strong design ideas and timely drawings made decisions faster.', 0, JSON_ARRAY()),
  (3, 'Akash Verma', 5, 'Great workmanship and site supervision.', 1, JSON_ARRAY());

INSERT INTO site_content (key_name, content_value, content_type)
VALUES
  ('seo.home.title', 'HomeInteriors360 | Premium Interior Design in Delhi NCR', 'text'),
  ('seo.home.description', 'Find verified architects, interior designers, and contractors for your home project.', 'text'),
  ('seo.directory.title', 'Find Professionals | HomeInteriors360', 'text'),
  ('seo.profile.title_suffix', 'Profile | HomeInteriors360', 'text'),
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
  ('home.services.items', '[{"key":"kitchen","title":"Kitchen","description":"Smart layouts, premium finishes, durable modules.","icon":"🍽️"},{"key":"wardrobe","title":"Wardrobe","description":"Tailored storage for every room.","icon":"🗄️"},{"key":"full_home","title":"Full Home","description":"End-to-end interiors with single-point accountability.","icon":"🏠"}]', 'json'),

  ('home.testimonials.title', 'Client Testimonials', 'text'),
  ('home.testimonials.items', '[{"name":"Priya S","text":"Excellent planning and execution. The handover quality was premium.","location":"Gurgaon"},{"name":"Vikas A","text":"Great design team and responsive project updates.","location":"Noida"},{"name":"Karan M","text":"We loved the modular kitchen and wardrobe detailing.","location":"Delhi"}]', 'json'),

  ('home.brands.title', 'Brands We Use', 'text'),
  ('home.brands.logos', '[{"name":"Hafele","url":"https://upload.wikimedia.org/wikipedia/commons/8/80/H%C3%A4fele_Logo.svg"},{"name":"Hettich","url":"https://upload.wikimedia.org/wikipedia/commons/6/66/Hettich_logo.svg"},{"name":"Asian Paints","url":"https://upload.wikimedia.org/wikipedia/commons/b/ba/Asian_Paints_Logo.svg"},{"name":"Kajaria","url":"https://upload.wikimedia.org/wikipedia/commons/3/31/Kajaria_Logo.svg"}]', 'json'),

  ('home.trust.title', 'Why Trust Us', 'text'),
  ('home.trust.items', '["Verified professionals and transparent pricing","Dedicated project support","Quality checks at every stage","On-time milestone tracking"]', 'json'),
  ('home.usp.title', 'Our USP', 'text'),
  ('home.usp.items', '["Centralized discovery + lead management","Data-driven cost estimator","Verified expert network","Content-managed growth architecture"]', 'json'),

  ('directory.title', 'Find Professionals', 'text'),
  ('directory.subtitle', 'Filter by profession, budget, and city to shortlist instantly.', 'text'),
  ('directory.filter.role', 'Profession', 'text'),
  ('directory.filter.city', 'City', 'text'),
  ('directory.filter.budget', 'Budget Range', 'text'),
  ('directory.empty', 'No professionals found for current filters.', 'text'),
  ('directory.cta', 'View Profile', 'text'),
  ('directory.verified', 'Verified', 'text'),
  ('directory.starting_from', 'Starting Price', 'text'),
  ('directory.experience', 'Years of Experience', 'text'),

  ('profile.cta', 'Request Quote', 'text'),
  ('profile.portfolio.title', 'Portfolio', 'text'),
  ('profile.reviews.title', 'Customer Reviews', 'text'),
  ('profile.expertise.title', 'Expertise & Offerings', 'text'),
  ('profile.verified_purchase', 'Verified Purchase', 'text'),
  ('profile.timeline', 'Timeline', 'text'),
  ('profile.amount_spent', 'Total Amount Spent', 'text'),
  ('profile.area', 'Area of Service', 'text'),

  ('calculator.title', 'Design Cost Calculator', 'text'),
  ('calculator.subtitle', 'Answer 4 quick questions to get your starting estimate.', 'text'),
  ('calculator.step1', 'Select Floor Plan', 'text'),
  ('calculator.step2', 'Select Package', 'text'),
  ('calculator.step3', 'Select Rooms', 'text'),
  ('calculator.step4', 'Contact Details', 'text'),
  ('calculator.submit', 'Get Estimate', 'text'),
  ('calculator.result_prefix', 'Starting From', 'text'),

  ('ui.name', 'Name', 'text'),
  ('ui.phone', 'Phone', 'text'),
  ('ui.city', 'City', 'text'),
  ('ui.requirement', 'Requirement', 'text'),

  ('admin.title', 'Admin Dashboard', 'text'),
  ('admin.content.title', 'Content Manager', 'text'),
  ('admin.leads.title', 'Lead Tracker', 'text'),
  ('admin.pros.title', 'Pro Verification', 'text'),
  ('admin.login.title', 'Admin Login', 'text')
ON DUPLICATE KEY UPDATE content_value = VALUES(content_value), content_type = VALUES(content_type);
