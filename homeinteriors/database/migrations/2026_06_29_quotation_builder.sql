SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS quotation_packages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL UNIQUE,
  description TEXT,
  material_grade VARCHAR(80) DEFAULT NULL,
  hardware_level VARCHAR(120) DEFAULT NULL,
  finish_level VARCHAR(120) DEFAULT NULL,
  warranty_years INT NOT NULL DEFAULT 1,
  design_support VARCHAR(255) DEFAULT NULL,
  supervision_level VARCHAR(255) DEFAULT NULL,
  drawings_2d_count INT NOT NULL DEFAULT 0,
  views_3d_count INT NOT NULL DEFAULT 0,
  revision_count INT NOT NULL DEFAULT 0,
  timeline_range VARCHAR(120) DEFAULT NULL,
  default_margin_percentage DECIMAL(8,2) NOT NULL DEFAULT 25,
  default_design_fee_percentage DECIMAL(8,2) NOT NULL DEFAULT 3,
  default_project_management_fee_percentage DECIMAL(8,2) NOT NULL DEFAULT 5,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_quote_packages_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotation_rate_cards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  city VARCHAR(120) NOT NULL,
  package_id INT DEFAULT NULL,
  designer_id INT DEFAULT NULL,
  category VARCHAR(120) NOT NULL,
  item_name VARCHAR(190) NOT NULL,
  material_grade VARCHAR(80) DEFAULT NULL,
  material VARCHAR(120) DEFAULT NULL,
  finish VARCHAR(120) DEFAULT NULL,
  brand VARCHAR(120) DEFAULT NULL,
  unit_type VARCHAR(80) NOT NULL DEFAULT 'Per unit',
  base_rate DECIMAL(12,2) NOT NULL DEFAULT 0,
  min_rate DECIMAL(12,2) NOT NULL DEFAULT 0,
  max_rate DECIMAL(12,2) NOT NULL DEFAULT 0,
  vendor_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  client_selling_rate DECIMAL(12,2) NOT NULL DEFAULT 0,
  margin_percentage DECIMAL(8,2) NOT NULL DEFAULT 0,
  gst_percentage DECIMAL(8,2) NOT NULL DEFAULT 18,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  effective_from DATE DEFAULT NULL,
  effective_to DATE DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_quote_rate_package FOREIGN KEY (package_id) REFERENCES quotation_packages(id) ON DELETE SET NULL,
  CONSTRAINT fk_quote_rate_designer FOREIGN KEY (designer_id) REFERENCES pros(id) ON DELETE SET NULL,
  INDEX idx_quote_rate_lookup (city, category, item_name, is_active),
  INDEX idx_quote_rate_package (package_id),
  INDEX idx_quote_rate_designer (designer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proposal_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  type VARCHAR(120) NOT NULL DEFAULT 'Full Home',
  cover_title VARCHAR(255) DEFAULT NULL,
  welcome_note TEXT,
  inclusions TEXT,
  exclusions TEXT,
  terms TEXT,
  payment_schedule_json JSON DEFAULT NULL,
  warranty_text TEXT,
  footer_note TEXT,
  logo_url VARCHAR(500) DEFAULT NULL,
  accent_color VARCHAR(20) NOT NULL DEFAULT '#0d2438',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_proposal_template_name_type (name, type),
  INDEX idx_proposal_templates_active (is_active, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotation_settings (
  setting_key VARCHAR(160) PRIMARY KEY,
  setting_value LONGTEXT NOT NULL,
  setting_type ENUM('text','number','json','html') NOT NULL DEFAULT 'text',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quote_number VARCHAR(60) NOT NULL UNIQUE,
  revision_number INT NOT NULL DEFAULT 0,
  parent_quote_id INT DEFAULT NULL,
  lead_id INT DEFAULT NULL,
  client_name VARCHAR(255) NOT NULL,
  client_phone VARCHAR(30) NOT NULL,
  client_email VARCHAR(255) DEFAULT NULL,
  city VARCHAR(120) NOT NULL,
  locality VARCHAR(190) DEFAULT NULL,
  society_name VARCHAR(255) DEFAULT NULL,
  property_type VARCHAR(80) NOT NULL,
  bhk VARCHAR(40) DEFAULT NULL,
  carpet_area DECIMAL(12,2) DEFAULT NULL,
  builtup_area DECIMAL(12,2) DEFAULT NULL,
  possession_status VARCHAR(80) DEFAULT NULL,
  expected_start_date DATE DEFAULT NULL,
  expected_handover_date DATE DEFAULT NULL,
  budget_range VARCHAR(120) DEFAULT NULL,
  design_style VARCHAR(120) DEFAULT NULL,
  scope_type VARCHAR(120) DEFAULT NULL,
  package_id INT DEFAULT NULL,
  template_id INT DEFAULT NULL,
  designer_id INT DEFAULT NULL,
  assigned_to_user_id INT DEFAULT NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  design_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  project_management_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  site_visit_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  discount_percentage DECIMAL(8,2) NOT NULL DEFAULT 0,
  gst_percentage DECIMAL(8,2) NOT NULL DEFAULT 18,
  gst_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  final_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  vendor_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  margin_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  margin_percentage DECIMAL(8,2) NOT NULL DEFAULT 0,
  platform_commission DECIMAL(12,2) NOT NULL DEFAULT 0,
  payment_schedule_json JSON DEFAULT NULL,
  status ENUM('draft','ready_for_review','sent_to_client','viewed_by_client','revision_requested','revised','accepted','rejected','expired','converted_to_project') NOT NULL DEFAULT 'draft',
  proposal_token VARCHAR(96) NOT NULL UNIQUE,
  valid_until DATE DEFAULT NULL,
  internal_notes TEXT,
  client_notes TEXT,
  created_by INT DEFAULT NULL,
  updated_by INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  sent_at DATETIME DEFAULT NULL,
  viewed_at DATETIME DEFAULT NULL,
  accepted_at DATETIME DEFAULT NULL,
  rejected_at DATETIME DEFAULT NULL,
  CONSTRAINT fk_quote_parent FOREIGN KEY (parent_quote_id) REFERENCES quotations(id) ON DELETE SET NULL,
  CONSTRAINT fk_quote_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
  CONSTRAINT fk_quote_package FOREIGN KEY (package_id) REFERENCES quotation_packages(id) ON DELETE SET NULL,
  CONSTRAINT fk_quote_template FOREIGN KEY (template_id) REFERENCES proposal_templates(id) ON DELETE SET NULL,
  CONSTRAINT fk_quote_designer FOREIGN KEY (designer_id) REFERENCES pros(id) ON DELETE SET NULL,
  CONSTRAINT fk_quote_assigned_user FOREIGN KEY (assigned_to_user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_quotes_status (status),
  INDEX idx_quotes_city (city),
  INDEX idx_quotes_lead (lead_id),
  INDEX idx_quotes_designer (designer_id),
  INDEX idx_quotes_updated (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotation_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quotation_id INT NOT NULL,
  room_name VARCHAR(120) NOT NULL,
  category VARCHAR(120) NOT NULL,
  item_name VARCHAR(190) NOT NULL,
  description TEXT,
  material VARCHAR(120) DEFAULT NULL,
  finish VARCHAR(120) DEFAULT NULL,
  brand VARCHAR(120) DEFAULT NULL,
  unit_type VARCHAR(80) NOT NULL DEFAULT 'Per unit',
  quantity DECIMAL(12,2) NOT NULL DEFAULT 1,
  length DECIMAL(12,2) NOT NULL DEFAULT 0,
  width DECIMAL(12,2) NOT NULL DEFAULT 0,
  height DECIMAL(12,2) NOT NULL DEFAULT 0,
  calculated_area DECIMAL(12,2) NOT NULL DEFAULT 0,
  rate DECIMAL(12,2) NOT NULL DEFAULT 0,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  gst_percentage DECIMAL(8,2) NOT NULL DEFAULT 18,
  gst_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  vendor_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  margin_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  include_in_proposal TINYINT(1) NOT NULL DEFAULT 1,
  is_manual_override TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_quote_items_quote FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE,
  INDEX idx_quote_items_quote (quotation_id),
  INDEX idx_quote_items_room (room_name),
  INDEX idx_quote_items_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quotation_activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quotation_id INT NOT NULL,
  action VARCHAR(120) NOT NULL,
  old_status VARCHAR(80) DEFAULT NULL,
  new_status VARCHAR(80) DEFAULT NULL,
  notes TEXT,
  performed_by INT DEFAULT NULL,
  performed_by_type VARCHAR(80) NOT NULL DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_quote_activity_quote FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE,
  INDEX idx_quote_activity_quote (quotation_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proposal_views (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quotation_id INT NOT NULL,
  proposal_token VARCHAR(96) NOT NULL,
  viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  ip_address VARCHAR(80) DEFAULT NULL,
  user_agent VARCHAR(500) DEFAULT NULL,
  CONSTRAINT fk_proposal_views_quote FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE,
  INDEX idx_proposal_views_token (proposal_token),
  INDEX idx_proposal_views_quote (quotation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO quotation_packages (name, slug, description, material_grade, hardware_level, finish_level, warranty_years, design_support, supervision_level, drawings_2d_count, views_3d_count, revision_count, timeline_range, default_margin_percentage, default_design_fee_percentage, default_project_management_fee_percentage, is_active, sort_order)
VALUES
('Basic / Essential', 'essential', 'Value-led interiors for rental, starter homes, and compact execution scopes.', 'Economy', 'Local / Ebco equivalent', 'Laminate', 1, 'Layout and 2D planning', 'Milestone supervision', 2, 1, 1, '30-45 days', 22, 2, 4, 1, 10),
('Standard', 'standard', 'Balanced materials, branded hardware, and practical finish options for family homes.', 'Standard', 'Ebco / Hettich standard', 'Laminate + acrylic highlights', 3, '2D planning and limited 3D support', 'Weekly site supervision', 4, 2, 2, '45-60 days', 25, 3, 5, 1, 20),
('Premium', 'premium', 'Premium materials, better hardware, richer finishes, and stronger design supervision.', 'Premium', 'Hettich / Hafele', 'Acrylic / PU / veneer mix', 5, '2D, 3D, material and styling support', 'Dedicated project supervision', 6, 4, 3, '60-75 days', 30, 4, 6, 1, 30),
('Luxury', 'luxury', 'Bespoke luxury package for villas, premium apartments, and high-touch turnkey work.', 'Luxury', 'Hafele premium', 'PU / veneer / stone / glass mix', 7, 'Full design studio support', 'Dedicated senior supervision', 8, 6, 4, '75-120 days', 35, 5, 7, 1, 40)
ON DUPLICATE KEY UPDATE description=VALUES(description), material_grade=VALUES(material_grade), hardware_level=VALUES(hardware_level), finish_level=VALUES(finish_level), warranty_years=VALUES(warranty_years), updated_at=NOW();

INSERT INTO proposal_templates (name, type, cover_title, welcome_note, inclusions, exclusions, terms, payment_schedule_json, warranty_text, footer_note, accent_color, is_active)
VALUES
('Full Home Interior Proposal', 'Full Home', 'Complete Home Interior Proposal', 'Thank you for sharing your home interior requirement. This proposal brings together the selected scope, package, timeline, and investment for your home.', 'Modular furniture\nSelected false ceiling and lighting\nStandard installation\nSite supervision as per package\nStandard warranty support', 'Civil changes not mentioned\nAppliances unless selected\nLoose furniture unless selected\nGovernment approvals\nStructural changes', 'Quote validity is limited to the validity date shown. Work starts after booking payment and design freeze. Any change after design freeze may affect cost and timeline.', '[{\"label\":\"Booking amount\",\"percentage\":10},{\"label\":\"After design freeze\",\"percentage\":40},{\"label\":\"Before material dispatch\",\"percentage\":40},{\"label\":\"Before handover\",\"percentage\":10}]', 'Warranty applies as per package tier and excludes misuse, seepage, third-party damage, and unapproved modifications.', 'HomeInteriors360 | Delhi NCR interior design aggregator', '#0d2438', 1),
('Modular Kitchen Proposal', 'Kitchen only', 'Modular Kitchen Proposal', 'This kitchen proposal is prepared around your layout, storage needs, finish choice, and package tier.', 'Kitchen cabinets\nCountertop if selected\nHardware as per package\nInstallation\nWarranty support', 'Appliances unless selected\nPlumbing changes not mentioned\nCivil work not mentioned\nAdditional electrical points', 'Quote is valid until the validity date. Final measurements at site may change quantities and cost.', '[{\"label\":\"Booking amount\",\"percentage\":20},{\"label\":\"After design freeze\",\"percentage\":40},{\"label\":\"Before dispatch\",\"percentage\":30},{\"label\":\"Before handover\",\"percentage\":10}]', 'Warranty applies to modular kitchen work as per package tier.', 'HomeInteriors360 modular kitchen proposal', '#8b5e34', 1),
('Wardrobe Proposal', 'Wardrobe only', 'Wardrobe Interior Proposal', 'This wardrobe proposal covers selected wardrobe modules, finish, hardware, and installation support.', 'Wardrobe carcass and shutters\nHardware as per package\nInstallation\nWarranty support', 'Civil work\nWall repairs\nLoose furniture\nElectrical changes', 'Final cost depends on site measurement, finish selection, and hardware selection.', '[{\"label\":\"Booking amount\",\"percentage\":20},{\"label\":\"Design freeze\",\"percentage\":40},{\"label\":\"Before dispatch\",\"percentage\":30},{\"label\":\"Before handover\",\"percentage\":10}]', 'Warranty applies to selected wardrobe components as per package.', 'HomeInteriors360 wardrobe proposal', '#234236', 1)
ON DUPLICATE KEY UPDATE welcome_note=VALUES(welcome_note), terms=VALUES(terms), updated_at=NOW();

INSERT INTO quotation_settings (setting_key, setting_value, setting_type) VALUES
('default_gst_percentage', '18', 'number'),
('default_proposal_validity_days', '15', 'number'),
('default_design_fee_percentage', '3', 'number'),
('default_project_management_fee_percentage', '5', 'number'),
('default_platform_commission_percentage', '5', 'number'),
('default_city', 'Gurgaon', 'text'),
('default_package_slug', 'standard', 'text'),
('support_phone', '+91-9540573661', 'text'),
('support_email', 'jitender@homeinteriors360.com', 'text'),
('company_address', 'Delhi NCR', 'text'),
('default_payment_schedule', '[{\"label\":\"Booking amount\",\"percentage\":10},{\"label\":\"After design freeze\",\"percentage\":40},{\"label\":\"Before material dispatch\",\"percentage\":40},{\"label\":\"Before handover\",\"percentage\":10}]', 'json'),
('default_whatsapp_message', 'Hello [Client Name], Thank you for sharing your home interior requirement with HomeInteriors360. Your proposal for [Project Location] is ready. View it here: [Proposal Link] Quote Value: ₹[Final Amount] Valid till: [Validity Date]', 'text'),
('default_quote_terms', 'Quote is valid until the validity date. Final dimensions, site conditions, material availability, and approved scope can affect price and timeline.', 'html')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), setting_type=VALUES(setting_type);

INSERT INTO quotation_rate_cards (city, package_id, category, item_name, material_grade, material, finish, brand, unit_type, base_rate, min_rate, max_rate, vendor_cost, client_selling_rate, margin_percentage, gst_percentage, is_active, effective_from)
SELECT city_name, p.id, rc.category, rc.item_name, p.material_grade, rc.material, rc.finish, rc.brand, rc.unit_type,
       rc.base_rate * city_multiplier, rc.min_rate * city_multiplier, rc.max_rate * city_multiplier, rc.vendor_cost * city_multiplier, rc.client_rate * city_multiplier,
       p.default_margin_percentage, 18, 1, CURRENT_DATE
FROM quotation_packages p
JOIN (
  SELECT 'Modular kitchen' category, 'Base and wall cabinets' item_name, 'BWP plywood' material, 'Laminate' finish, 'Hettich hardware' brand, 'Per running ft' unit_type, 18500 base_rate, 15000 min_rate, 28000 max_rate, 13500 vendor_cost, 18500 client_rate
  UNION ALL SELECT 'Wardrobe', 'Hinged wardrobe', 'HDHMR', 'Laminate', 'Ebco hardware', 'Per sq ft', 1450, 1100, 2600, 950, 1450
  UNION ALL SELECT 'TV unit', 'TV console and panel', 'MDF', 'Laminate', 'Local hardware', 'Per unit', 65000, 35000, 180000, 43000, 65000
  UNION ALL SELECT 'False ceiling', 'Gypsum false ceiling', 'Gypsum', 'Paint finish', 'Standard', 'Per sq ft', 155, 120, 280, 95, 155
  UNION ALL SELECT 'Painting', 'Premium interior painting', 'Putty + paint', 'Matt finish', 'Asian Paints', 'Per sq ft', 42, 28, 90, 25, 42
  UNION ALL SELECT 'Electrical work', 'Electrical point', 'Copper wire', 'Modular switch', 'Anchor / equivalent', 'Per point', 1250, 850, 2200, 760, 1250
  UNION ALL SELECT 'Pooja unit', 'Wall mounted pooja unit', 'BWP plywood', 'Laminate / veneer', 'Hettich hardware', 'Per unit', 75000, 42000, 220000, 50000, 75000
) rc
JOIN (
  SELECT 'Gurgaon' city_name, 1.00 city_multiplier
  UNION ALL SELECT 'Delhi', 1.03
  UNION ALL SELECT 'Noida', 0.98
  UNION ALL SELECT 'Greater Noida', 0.95
  UNION ALL SELECT 'Ghaziabad', 0.94
  UNION ALL SELECT 'Faridabad', 0.96
) c
WHERE NOT EXISTS (
  SELECT 1 FROM quotation_rate_cards qrc
  WHERE qrc.city=c.city_name AND qrc.package_id=p.id AND qrc.category=rc.category AND qrc.item_name=rc.item_name
);
