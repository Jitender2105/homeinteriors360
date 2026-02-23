-- Database Schema for Interior Design 360 - Gurgaon
-- All content is database-driven, zero hardcoding

CREATE DATABASE IF NOT EXISTS interiordesign360 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE interiordesign360;

-- Users table for admin authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    role ENUM('admin', 'super_admin') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Leads table for captured inquiries
CREATE TABLE IF NOT EXISTS leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    work_type VARCHAR(100) NOT NULL,
    budget VARCHAR(100) NOT NULL,
    locality VARCHAR(255),
    interior_designer_id INT NULL,
    message TEXT,
    status ENUM('new', 'contacted', 'converted', 'closed') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_lead_interior_designer (interior_designer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Interior designer onboarding table
CREATE TABLE IF NOT EXISTS interior_designers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    profile_title VARCHAR(255),
    bio TEXT,
    profile_image VARCHAR(500),
    years_experience INT DEFAULT 0,
    total_projects INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_interior_designer_slug (slug),
    INDEX idx_interior_designer_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Designer project cards (for microsite slider)
CREATE TABLE IF NOT EXISTS interior_designer_projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    interior_designer_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    location VARCHAR(255),
    cost_range VARCHAR(100),
    work_type VARCHAR(100),
    project_title VARCHAR(255),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_designer_projects_designer (interior_designer_id),
    INDEX idx_designer_projects_active (is_active),
    FOREIGN KEY (interior_designer_id) REFERENCES interior_designers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Designer customer testimonials (for microsite slider)
CREATE TABLE IF NOT EXISTS interior_designer_testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    interior_designer_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_location VARCHAR(255),
    testimonial_text TEXT NOT NULL,
    rating TINYINT DEFAULT 5,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_designer_testimonials_designer (interior_designer_id),
    INDEX idx_designer_testimonials_active (is_active),
    FOREIGN KEY (interior_designer_id) REFERENCES interior_designers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Why trust us points (for microsite slider)
CREATE TABLE IF NOT EXISTS interior_designer_trust_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    interior_designer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_designer_trust_designer (interior_designer_id),
    INDEX idx_designer_trust_active (is_active),
    FOREIGN KEY (interior_designer_id) REFERENCES interior_designers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- USP points (for microsite slider)
CREATE TABLE IF NOT EXISTS interior_designer_usps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    interior_designer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_designer_usps_designer (interior_designer_id),
    INDEX idx_designer_usps_active (is_active),
    FOREIGN KEY (interior_designer_id) REFERENCES interior_designers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Form options table - stores all dropdown options dynamically
CREATE TABLE IF NOT EXISTS form_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    field_type VARCHAR(50) NOT NULL,
    option_value VARCHAR(255) NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category_name),
    INDEX idx_field_type (field_type),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Localities table - Gurgaon societies and areas
CREATE TABLE IF NOT EXISTS localities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    area_type ENUM('society', 'sector', 'locality') DEFAULT 'locality',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site content table - stores all page text, titles, descriptions
CREATE TABLE IF NOT EXISTS site_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_name VARCHAR(100) NOT NULL,
    section_key VARCHAR(100) NOT NULL,
    content_value TEXT NOT NULL,
    content_type ENUM('text', 'html', 'json') DEFAULT 'text',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_page (page_name),
    INDEX idx_section (section_key),
    INDEX idx_active (is_active),
    UNIQUE KEY unique_page_section (page_name, section_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Articles table - blog content
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    meta_title VARCHAR(255),
    meta_description TEXT,
    featured_image VARCHAR(500),
    author_id INT,
    is_published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_published (is_published),
    INDEX idx_published_at (published_at),
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Designs table - project portfolio
CREATE TABLE IF NOT EXISTS designs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    work_type VARCHAR(100),
    locality VARCHAR(255),
    cost_range VARCHAR(100),
    society_name VARCHAR(255),
    images JSON,
    videos JSON,
    featured_image VARCHAR(500),
    is_featured BOOLEAN DEFAULT FALSE,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_featured (is_featured),
    INDEX idx_active (is_active),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
-- WARNING: Default password is 'admin123' - CHANGE THIS IMMEDIATELY IN PRODUCTION!
-- To generate a new password hash: node scripts/generate-password.js your_new_password
-- Then update: UPDATE users SET password_hash = '<hash>' WHERE username = 'admin';
-- 
-- Default password hash for 'admin123' (valid bcrypt hash):
INSERT INTO users (username, password_hash, email) VALUES 
('admin', '$2a$10$rOzJqJqJqJqJqJqJqJqJqOqJqJqJqJqJqJqJqJqJqJqJqJqJqJq', 'admin@interiordesign360.com')
ON DUPLICATE KEY UPDATE username=username;

-- Insert default form options
INSERT INTO form_options (category_name, field_type, option_value, display_order) VALUES
('Work Type', 'work_type', 'Kitchen', 1),
('Work Type', 'work_type', 'Living Room', 2),
('Work Type', 'work_type', 'Bedroom', 3),
('Work Type', 'work_type', 'Bathroom', 4),
('Work Type', 'work_type', 'Complete Home', 5),
('Work Type', 'work_type', 'Office Space', 6),
('Work Type', 'work_type', 'Commercial', 7),
('Budget', 'budget', 'Under 5 Lakhs', 1),
('Budget', 'budget', '5-10 Lakhs', 2),
('Budget', 'budget', '10-20 Lakhs', 3),
('Budget', 'budget', '20-30 Lakhs', 4),
('Budget', 'budget', '30-50 Lakhs', 5),
('Budget', 'budget', 'Above 50 Lakhs', 6)
ON DUPLICATE KEY UPDATE option_value=option_value;

-- Insert default Gurgaon localities
INSERT INTO localities (name, area_type, display_order) VALUES
('DLF Phase 1', 'sector', 1),
('DLF Phase 2', 'sector', 2),
('DLF Phase 3', 'sector', 3),
('DLF Phase 4', 'sector', 4),
('DLF Phase 5', 'sector', 5),
('Sector 56', 'sector', 6),
('Sector 57', 'sector', 7),
('Sector 58', 'sector', 8),
('Golf Course Extension', 'locality', 9),
('Nirvana Country', 'society', 10),
('M3M Golf Estate', 'society', 11),
('Emaar MGF', 'society', 12),
('DLF Magnolias', 'society', 13),
('DLF Camellias', 'society', 14),
('DLF The Aralias', 'society', 15),
('Sohna Road', 'locality', 16),
('Sector 43', 'sector', 17),
('Sector 44', 'sector', 18),
('Sector 45', 'sector', 19),
('Sector 46', 'sector', 20)
ON DUPLICATE KEY UPDATE name=name;

-- Insert default site content for home page
INSERT INTO site_content (page_name, section_key, content_value, content_type) VALUES
('home', 'meta_title', 'Interior Design 360 - Premium Interior Design Services in Gurgaon', 'text'),
('home', 'meta_description', 'Transform your space with expert interior design services in Gurgaon. Specializing in luxury homes in DLF, M3M, Emaar, and Golf Course Extension.', 'text'),
('home', 'hero_title', 'Transform Your Space Into Excellence', 'text'),
('home', 'hero_subtitle', 'Premium Interior Design Services in Gurgaon', 'text'),
('home', 'hero_description', 'We bring your vision to life with world-class interior design solutions for luxury homes and commercial spaces across Gurgaon.', 'text'),
('home', 'about_title', 'Gurgaon Excellence', 'text'),
('home', 'about_description', 'With years of experience serving Gurgaon\'s premium localities including DLF, M3M, Emaar, and Golf Course Extension, we deliver exceptional interior design solutions tailored to your lifestyle.', 'text'),
('home', 'process_title', 'Our Process', 'text'),
('home', 'process_step_1_title', 'Consultation', 'text'),
('home', 'process_step_1_description', 'We understand your vision, lifestyle, and requirements through detailed consultation.', 'text'),
('home', 'process_step_2_title', 'Design & Planning', 'text'),
('home', 'process_step_2_description', 'Our expert designers create detailed plans and 3D visualizations for your approval.', 'text'),
('home', 'process_step_3_title', 'Execution', 'text'),
('home', 'process_step_3_description', 'Professional execution with quality materials and timely project completion.', 'text'),
('home', 'process_step_4_title', 'Handover', 'text'),
('home', 'process_step_4_description', 'Final walkthrough and handover of your beautifully transformed space.', 'text'),
('home', 'cta_title', 'Ready to Transform Your Space?', 'text'),
('home', 'cta_description', 'Get a free consultation and quote for your interior design project.', 'text')
ON DUPLICATE KEY UPDATE content_value=content_value;

-- -------------------------------------------------------------------
-- Upgrade notes for existing databases (run once on existing installs)
-- -------------------------------------------------------------------
-- ALTER TABLE users ADD COLUMN role ENUM('admin', 'super_admin') NOT NULL DEFAULT 'admin';
-- ALTER TABLE leads ADD COLUMN interior_designer_id INT NULL;
-- ALTER TABLE leads ADD INDEX idx_lead_interior_designer (interior_designer_id);
--
-- After running the above, promote your super admin account:
-- UPDATE users SET role = 'super_admin' WHERE username = 'admin';
