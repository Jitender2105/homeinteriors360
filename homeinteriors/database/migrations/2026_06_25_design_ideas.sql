CREATE TABLE IF NOT EXISTS design_ideas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(255) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  location VARCHAR(255) DEFAULT NULL,
  city VARCHAR(120) DEFAULT NULL,
  state VARCHAR(120) DEFAULT NULL,
  type VARCHAR(120) NOT NULL,
  color VARCHAR(120) DEFAULT NULL,
  style VARCHAR(120) DEFAULT NULL,
  layout VARCHAR(120) DEFAULT NULL,
  length_ft DECIMAL(8,2) DEFAULT NULL,
  breadth_ft DECIMAL(8,2) DEFAULT NULL,
  height_ft DECIMAL(8,2) DEFAULT NULL,
  budget_min DECIMAL(12,2) DEFAULT NULL,
  budget_max DECIMAL(12,2) DEFAULT NULL,
  short_description TEXT,
  description LONGTEXT,
  image_url VARCHAR(500) DEFAULT NULL,
  gallery_json JSON DEFAULT NULL,
  tags_json JSON DEFAULT NULL,
  meta_title VARCHAR(255) DEFAULT NULL,
  meta_description VARCHAR(500) DEFAULT NULL,
  is_featured BOOLEAN NOT NULL DEFAULT FALSE,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_design_ideas_type (type, is_active),
  INDEX idx_design_ideas_color (color),
  INDEX idx_design_ideas_city (city),
  INDEX idx_design_ideas_style (style),
  INDEX idx_design_ideas_featured (is_featured, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS design_idea_aliases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(255) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  subtitle TEXT,
  hero_image VARCHAR(500) DEFAULT NULL,
  intro_content LONGTEXT,
  outro_content LONGTEXT,
  filter_type VARCHAR(120) DEFAULT NULL,
  filter_color VARCHAR(120) DEFAULT NULL,
  filter_city VARCHAR(120) DEFAULT NULL,
  filter_state VARCHAR(120) DEFAULT NULL,
  filter_style VARCHAR(120) DEFAULT NULL,
  filter_layout VARCHAR(120) DEFAULT NULL,
  meta_title VARCHAR(255) DEFAULT NULL,
  meta_description VARCHAR(500) DEFAULT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_design_alias_active (is_active),
  INDEX idx_design_alias_filters (filter_type, filter_color, filter_city, filter_state, filter_style)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS design_idea_leads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  design_idea_id INT DEFAULT NULL,
  alias_id INT DEFAULT NULL,
  name VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(255) DEFAULT NULL,
  city VARCHAR(120) DEFAULT NULL,
  requirement VARCHAR(255) DEFAULT NULL,
  budget VARCHAR(120) DEFAULT NULL,
  message TEXT,
  consent BOOLEAN NOT NULL DEFAULT FALSE,
  source VARCHAR(80) NOT NULL DEFAULT 'design_ideas',
  status ENUM('new','contacted','qualified','closed') NOT NULL DEFAULT 'new',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_design_lead_idea FOREIGN KEY (design_idea_id) REFERENCES design_ideas(id) ON DELETE SET NULL,
  CONSTRAINT fk_design_lead_alias FOREIGN KEY (alias_id) REFERENCES design_idea_aliases(id) ON DELETE SET NULL,
  INDEX idx_design_leads_status (status),
  INDEX idx_design_leads_idea (design_idea_id),
  INDEX idx_design_leads_alias (alias_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS design_idea_sections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  section_key VARCHAR(120) NOT NULL UNIQUE,
  title VARCHAR(255) NOT NULL,
  subtitle TEXT,
  section_type ENUM('hero_tiles','category_grid','color_grid','style_grid','unit_grid','trending','lead_form','tool_cards','content') NOT NULL DEFAULT 'category_grid',
  items_json JSON DEFAULT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_design_sections_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS url_aliases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  path VARCHAR(255) NOT NULL UNIQUE,
  page_type VARCHAR(80) NOT NULL DEFAULT 'page',
  entity_table VARCHAR(80) DEFAULT NULL,
  entity_id INT DEFAULT NULL,
  source VARCHAR(80) NOT NULL DEFAULT 'manual',
  meta_title VARCHAR(255) DEFAULT NULL,
  meta_description VARCHAR(500) DEFAULT NULL,
  h1 VARCHAR(255) DEFAULT NULL,
  content_html LONGTEXT,
  image_url VARCHAR(500) DEFAULT NULL,
  canonical_url VARCHAR(500) DEFAULT NULL,
  robots VARCHAR(160) DEFAULT NULL,
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_url_aliases_active (is_active),
  INDEX idx_url_aliases_entity (entity_table, entity_id),
  INDEX idx_url_aliases_page_type (page_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO design_ideas (
  slug, name, location, city, state, type, color, style, layout, length_ft, breadth_ft, height_ft,
  budget_min, budget_max, short_description, description, image_url, gallery_json, tags_json,
  meta_title, meta_description, is_featured, is_active
) VALUES
('warm-modular-kitchen-gurgaon', 'Warm Modular Kitchen with Breakfast Counter', 'Sector 65', 'Gurgaon', 'Haryana', 'Kitchen', 'Wood + White', 'Modern', 'L-shaped', 12, 10, 9.5, 650000, 1100000, 'A warm L-shaped kitchen with tall storage, quartz counter, and compact breakfast seating.', 'This kitchen idea is suited for premium apartments where storage, appliance planning, and a clean breakfast counter are equally important. Use it as a reference for modern modular kitchens with warm wood, white shutters, and practical task lighting.', 'https://images.unsplash.com/photo-1600489000022-c2086d79f9d4?auto=format&fit=crop&w=1400&q=85', JSON_ARRAY('https://images.unsplash.com/photo-1600489000022-c2086d79f9d4?auto=format&fit=crop&w=1400&q=85','https://images.unsplash.com/photo-1556911220-bff31c812dba?auto=format&fit=crop&w=1400&q=85'), JSON_ARRAY('modular kitchen','breakfast counter','quartz','tall storage'), 'Warm Modular Kitchen Design in Gurgaon | HomeInteriors360', 'Explore a warm modular kitchen design with dimensions, colours, layout, budget range, photos, and quote request.', 1, 1),
('sage-green-bedroom-delhi', 'Sage Green Bedroom with Soft Lighting', 'South Delhi', 'Delhi', 'Delhi', 'Bedroom', 'Sage Green', 'Contemporary', 'Queen bed wall', 14, 12, 10, 450000, 850000, 'A calming bedroom idea with green walls, layered lighting, and built-in bedside storage.', 'This bedroom design idea uses a soothing green palette, textured furnishings, and functional storage to make a compact room feel premium without looking crowded.', 'https://images.unsplash.com/photo-1616594039964-ae9021a400a0?auto=format&fit=crop&w=1400&q=85', JSON_ARRAY('https://images.unsplash.com/photo-1616594039964-ae9021a400a0?auto=format&fit=crop&w=1400&q=85'), JSON_ARRAY('bedroom','sage green','soft lighting','storage'), 'Sage Green Bedroom Design Idea | HomeInteriors360', 'See a sage green bedroom design idea with photos, size, colour palette, and quotation request.', 1, 1),
('neutral-living-room-noida', 'Neutral Living Room with TV Wall', 'Sector 150', 'Noida', 'Uttar Pradesh', 'Living Room', 'Neutral', 'Minimal Luxury', 'TV wall', 18, 14, 10, 550000, 1200000, 'A soft neutral living room with a premium TV wall, layered seating, and natural light.', 'This living room idea is made for apartments needing an elegant TV unit, warm material palette, and comfortable seating arrangement.', 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1400&q=85', JSON_ARRAY('https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1400&q=85'), JSON_ARRAY('living room','tv unit','neutral','minimal luxury'), 'Neutral Living Room Design Idea | HomeInteriors360', 'Browse a neutral living room design idea with layout, dimensions, colours, and quote form.', 1, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name), updated_at=CURRENT_TIMESTAMP;

INSERT INTO design_ideas (
  slug, name, location, city, state, type, color, style, layout, length_ft, breadth_ft, height_ft,
  budget_min, budget_max, short_description, description, image_url, gallery_json, tags_json,
  meta_title, meta_description, is_featured, is_active
) VALUES
('parallel-white-kitchen-delhi', 'Parallel White Kitchen with Tall Pantry', 'Greater Kailash', 'Delhi', 'Delhi', 'Kitchen', 'White', 'Modern Minimal', 'Parallel', 11, 8, 9.5, 520000, 950000, 'A compact parallel kitchen with bright cabinetry, appliance tower, and tall pantry storage.', 'This parallel modular kitchen design is built for efficient cooking flow with two clear counters, integrated appliances, and a bright white palette that keeps compact apartments feeling open.', 'https://images.unsplash.com/photo-1556911220-bff31c812dba?auto=format&fit=crop&w=1400&q=85', JSON_ARRAY('https://images.unsplash.com/photo-1556911220-bff31c812dba?auto=format&fit=crop&w=1400&q=85'), JSON_ARRAY('parallel kitchen','white kitchen','tall pantry','modern minimal'), 'Parallel White Kitchen Design | HomeInteriors360', 'Explore a parallel white modular kitchen design with pantry, dimensions, budget and quote capture.', 1, 1),
('island-kitchen-noida', 'Open Island Kitchen in Neutral Palette', 'Sector 128', 'Noida', 'Uttar Pradesh', 'Kitchen', 'Neutral', 'Classic Contemporary', 'Island', 16, 12, 10, 900000, 1600000, 'An open island kitchen for larger homes with a sociable counter, hidden storage, and warm lighting.', 'This island kitchen design works well for open-plan apartments and villas where the kitchen also becomes a breakfast, hosting, and family interaction zone.', 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=1400&q=85', JSON_ARRAY('https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?auto=format&fit=crop&w=1400&q=85'), JSON_ARRAY('island kitchen','open kitchen','neutral palette','storage'), 'Open Island Kitchen Design | HomeInteriors360', 'Browse an open island kitchen design with photos, layout, colour palette, dimensions and quote form.', 1, 1),
('luxury-bedroom-gurgaon', 'Luxury Bedroom with Upholstered Headboard', 'Golf Course Road', 'Gurgaon', 'Haryana', 'Bedroom', 'Neutral', 'Classic Contemporary', 'King bed wall', 15, 13, 10, 650000, 1250000, 'A premium bedroom design with upholstered headboard, side lighting, and elegant wardrobe planning.', 'This bedroom interior balances a soft neutral palette with premium textures, warm lighting, and storage that keeps the room calm and uncluttered.', 'https://images.unsplash.com/photo-1618221118493-9cfa1a1c00da?auto=format&fit=crop&w=1400&q=85', JSON_ARRAY('https://images.unsplash.com/photo-1618221118493-9cfa1a1c00da?auto=format&fit=crop&w=1400&q=85'), JSON_ARRAY('luxury bedroom','upholstered headboard','wardrobe','neutral'), 'Luxury Bedroom Design Idea | HomeInteriors360', 'See a luxury bedroom design with headboard, wardrobe planning, dimensions, budget and quote capture.', 1, 1),
('compact-bedroom-noida', 'Compact Bedroom with Wardrobe and Study Nook', 'Sector 78', 'Noida', 'Uttar Pradesh', 'Bedroom', 'Wood + White', 'Modern Minimal', 'Wardrobe wall', 12, 10, 9.5, 350000, 700000, 'A compact bedroom idea with wardrobe, study nook, and smart storage around a queen bed.', 'This small bedroom design uses a clean palette and built-in furniture to combine sleeping, storage, and study needs without crowding the room.', 'https://images.unsplash.com/photo-1617325247661-675ab4b64b26?auto=format&fit=crop&w=1400&q=85', JSON_ARRAY('https://images.unsplash.com/photo-1617325247661-675ab4b64b26?auto=format&fit=crop&w=1400&q=85'), JSON_ARRAY('compact bedroom','study nook','wardrobe','storage'), 'Compact Bedroom Design with Study Nook | HomeInteriors360', 'Explore a compact bedroom with wardrobe, study nook, storage, dimensions and quote form.', 1, 1),
('modern-living-room-delhi', 'Modern Living Room with Curved Seating', 'Vasant Kunj', 'Delhi', 'Delhi', 'Living Room', 'Grey', 'Modern Minimal', 'Seating layout', 19, 13, 10, 700000, 1350000, 'A modern living room with curved seating, layered lighting, and a sleek entertainment wall.', 'This living room design is suited for families who want an uncluttered entertainment wall, flexible seating, and a premium but relaxed visual language.', 'https://images.unsplash.com/photo-1600210491892-03d54c0aaf87?auto=format&fit=crop&w=1400&q=85', JSON_ARRAY('https://images.unsplash.com/photo-1600210491892-03d54c0aaf87?auto=format&fit=crop&w=1400&q=85'), JSON_ARRAY('living room','curved seating','grey palette','tv wall'), 'Modern Living Room Design Idea | HomeInteriors360', 'Browse a modern living room design with curved seating, TV wall, dimensions, budget and quote capture.', 1, 1),
('warm-living-room-gurgaon', 'Warm Living Room with Wooden Ceiling Detail', 'DLF Phase 5', 'Gurgaon', 'Haryana', 'Living Room', 'Wood + Neutral', 'Indian Modern', 'TV wall', 20, 15, 10, 850000, 1500000, 'A warm living room with wooden ceiling detail, TV wall, and natural material accents.', 'This living room concept brings a warm Indian modern character using wood, soft upholstery, layered lights, and a functional TV wall.', 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1400&q=85', JSON_ARRAY('https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1400&q=85'), JSON_ARRAY('living room','wood ceiling','indian modern','tv unit'), 'Warm Living Room Design with Wooden Detail | HomeInteriors360', 'Explore a warm living room design with wooden ceiling detail, TV wall, dimensions and quote form.', 1, 1)
ON DUPLICATE KEY UPDATE name=VALUES(name), type=VALUES(type), color=VALUES(color), style=VALUES(style), layout=VALUES(layout), image_url=VALUES(image_url), updated_at=CURRENT_TIMESTAMP;

INSERT INTO design_idea_aliases (
  slug, title, subtitle, hero_image, intro_content, outro_content, filter_type, filter_color, filter_city, filter_state, filter_style, filter_layout, meta_title, meta_description, is_active
) VALUES
('kitchen-designs', 'Kitchen Design Ideas', 'Explore modular kitchen layouts, colours, dimensions, storage plans, and quotation-ready ideas for Indian homes.', 'https://images.unsplash.com/photo-1600489000022-c2086d79f9d4?auto=format&fit=crop&w=1600&q=85', 'Kitchen designs should balance workflow, storage, appliance planning, finish selection, and available space. Browse ideas by colour, layout, city, and style before requesting a quote.', 'Shortlist kitchen references you like, save favourites, and request a quotation with your preferred layout and budget.', 'Kitchen', NULL, NULL, NULL, NULL, NULL, 'Kitchen Design Ideas with Photos, Layouts and Prices | HomeInteriors360', 'Browse modular kitchen design ideas with photos, colours, dimensions, layouts, city filters, favourites, and quote requests.', 1),
('bedroom-designs', 'Bedroom Design Ideas', 'Browse bedroom interiors by colour, city, size, style, and storage needs.', 'https://images.unsplash.com/photo-1616594039964-ae9021a400a0?auto=format&fit=crop&w=1600&q=85', 'Bedroom interiors need comfort, storage, soft lighting, and a palette that still feels personal. Use these ideas to compare mood, dimensions, and budget bands.', 'Pick a bedroom idea, save it to favourites, and request a design quote from HomeInteriors360.', 'Bedroom', NULL, NULL, NULL, NULL, NULL, 'Bedroom Design Ideas with Photos | HomeInteriors360', 'Explore bedroom design ideas with photos, dimensions, colours, storage concepts, and quote capture.', 1),
('living-room-designs', 'Living Room Design Ideas', 'Explore TV units, seating layouts, colour palettes, and premium living room concepts.', 'https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1600&q=85', 'Living rooms carry daily comfort and first impressions. Browse ideas by colour, style, city, and layout before planning the execution.', 'Save favourite living room references and request a detailed quote.', 'Living Room', NULL, NULL, NULL, NULL, NULL, 'Living Room Design Ideas | HomeInteriors360', 'Browse living room design ideas with photos, TV wall concepts, dimensions, colours, and quote capture.', 1),
('dining-room-designs', 'Dining Room Design Ideas', 'Explore dining room layouts, crockery units, lighting, seating, and premium dining concepts.', 'https://images.unsplash.com/photo-1617104551722-3b2d51366400?auto=format&fit=crop&w=1600&q=85', 'Dining rooms need comfortable circulation, lighting, table sizing, and smart storage. Browse dining references before requesting a quote.', 'Shortlist dining room references and request a detailed quote.', 'Dining Room', NULL, NULL, NULL, NULL, NULL, 'Dining Room Design Ideas | HomeInteriors360', 'Browse dining room design ideas with photos, layouts, colours, storage, and quote capture.', 1),
('bathroom-designs', 'Bathroom Design Ideas', 'Explore bathroom layouts, vanities, lighting, storage, finishes, and premium bath concepts.', 'https://images.unsplash.com/photo-1620626011761-996317b8d101?auto=format&fit=crop&w=1600&q=85', 'Bathroom interiors need careful planning for wet zones, storage, lighting, fittings, ventilation, and easy maintenance.', 'Shortlist bathroom references and request a detailed quote.', 'Bathroom', NULL, NULL, NULL, NULL, NULL, 'Bathroom Design Ideas | HomeInteriors360', 'Browse bathroom design ideas with photos, layouts, colours, storage, and quote capture.', 1),
('home-office-designs', 'Home Office Design Ideas', 'Explore home office layouts, desks, lighting, storage, and compact work-from-home concepts.', 'https://images.unsplash.com/photo-1593476550610-87baa860004a?auto=format&fit=crop&w=1600&q=85', 'Home office interiors should balance focus, ergonomics, cable management, lighting, and storage in the available space.', 'Shortlist home office references and request a detailed quote.', 'Home Office', NULL, NULL, NULL, NULL, NULL, 'Home Office Design Ideas | HomeInteriors360', 'Browse home office design ideas with photos, layouts, colours, storage, and quote capture.', 1)
ON DUPLICATE KEY UPDATE title=VALUES(title), updated_at=CURRENT_TIMESTAMP;

INSERT INTO design_idea_sections (section_key, title, subtitle, section_type, items_json, sort_order, is_active) VALUES
('browse_by_rooms', 'Browse by rooms', 'Choose the room you want to transform first.', 'category_grid', JSON_ARRAY(
  JSON_OBJECT('title','Living Room','href','/design-ideas/living-room-designs','image','https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=900&q=85','label','Know more'),
  JSON_OBJECT('title','Kitchen','href','/design-ideas/kitchen-designs','image','https://images.unsplash.com/photo-1600489000022-c2086d79f9d4?auto=format&fit=crop&w=900&q=85','label','Know more'),
  JSON_OBJECT('title','Bedroom','href','/design-ideas/bedroom-designs','image','https://images.unsplash.com/photo-1616594039964-ae9021a400a0?auto=format&fit=crop&w=900&q=85','label','Know more'),
  JSON_OBJECT('title','Dining Room','href','/design-ideas/dining-room-designs','image','https://images.unsplash.com/photo-1617104551722-3b2d51366400?auto=format&fit=crop&w=900&q=85','label','Know more'),
  JSON_OBJECT('title','Home Office','href','/design-ideas/home-office-designs','image','https://images.unsplash.com/photo-1593476550610-87baa860004a?auto=format&fit=crop&w=900&q=85','label','Know more'),
  JSON_OBJECT('title','Bathroom','href','/design-ideas/bathroom-designs','image','https://images.unsplash.com/photo-1620626011761-996317b8d101?auto=format&fit=crop&w=900&q=85','label','Know more')
), 10, 1),
('browse_by_colours', 'Browse by colours', 'Explore shades to bring your space to life.', 'color_grid', JSON_ARRAY(
  JSON_OBJECT('title','Blues & Purples','href','/design-ideas?color=Blue','image','https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=800&q=85'),
  JSON_OBJECT('title','Yellows & Greens','href','/design-ideas?color=Green','image','https://images.unsplash.com/photo-1615874694520-474822394e73?auto=format&fit=crop&w=800&q=85'),
  JSON_OBJECT('title','Reds & Pinks','href','/design-ideas?color=Pink','image','https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=800&q=85'),
  JSON_OBJECT('title','Sophisticated Greys','href','/design-ideas?color=Grey','image','https://images.unsplash.com/photo-1600566753151-384129cf4e3e?auto=format&fit=crop&w=800&q=85'),
  JSON_OBJECT('title','Versatile Neutrals','href','/design-ideas?color=Neutral','image','https://images.unsplash.com/photo-1600210492486-724fe5c67fb0?auto=format&fit=crop&w=800&q=85')
), 20, 1),
('browse_by_style', 'Browse by style', 'Find a design language that matches your home.', 'style_grid', JSON_ARRAY(
  JSON_OBJECT('title','Modern Minimal','href','/design-ideas?style=Modern','image','https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=800&q=85'),
  JSON_OBJECT('title','Classic Contemporary','href','/design-ideas?style=Contemporary','image','https://images.unsplash.com/photo-1616486701797-1224e5225f34?auto=format&fit=crop&w=800&q=85'),
  JSON_OBJECT('title','Eclectic','href','/design-ideas?style=Eclectic','image','https://images.unsplash.com/photo-1615529162924-f8605388461d?auto=format&fit=crop&w=800&q=85'),
  JSON_OBJECT('title','Mid-Century Modern','href','/design-ideas?style=Mid-Century%20Modern','image','https://images.unsplash.com/photo-1602872030490-4a484a7b3ba6?auto=format&fit=crop&w=800&q=85'),
  JSON_OBJECT('title','Indian Modern','href','/design-ideas?style=Indian%20Modern','image','https://images.unsplash.com/photo-1583847268964-b28dc8f51f92?auto=format&fit=crop&w=800&q=85'),
  JSON_OBJECT('title','Boho Chic','href','/design-ideas?style=Boho','image','https://images.unsplash.com/photo-1617103996702-96ff29b1c467?auto=format&fit=crop&w=800&q=85')
), 30, 1),
('browse_by_unit', 'Browse by unit', 'Explore functional elements that complete the room.', 'unit_grid', JSON_ARRAY(
  JSON_OBJECT('title','TV unit','href','/design-ideas?layout=TV%20wall','image','https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=800&q=85'),
  JSON_OBJECT('title','Wardrobe','href','/design-ideas?type=Wardrobe','image','https://images.unsplash.com/photo-1558997519-83ea9252edf8?auto=format&fit=crop&w=800&q=85'),
  JSON_OBJECT('title','Crockery unit','href','/design-ideas?layout=Crockery%20unit','image','https://images.unsplash.com/photo-1616047006789-b7af5afb8c20?auto=format&fit=crop&w=800&q=85'),
  JSON_OBJECT('title','Doors','href','/design-ideas?type=Doors','image','https://images.unsplash.com/photo-1600607688969-a5bfcd646154?auto=format&fit=crop&w=800&q=85'),
  JSON_OBJECT('title','Windows','href','/design-ideas?type=Windows','image','https://images.unsplash.com/photo-1600566752355-35792bedcfea?auto=format&fit=crop&w=800&q=85')
), 40, 1),
('trending_ideas', 'Discover trending ideas', 'Fresh references homeowners are shortlisting now.', 'trending', JSON_ARRAY(
  JSON_OBJECT('title','Warm modular kitchen with breakfast counter','href','/design-ideas/idea/warm-modular-kitchen-gurgaon','image','https://images.unsplash.com/photo-1600489000022-c2086d79f9d4?auto=format&fit=crop&w=1200&q=85','cta','Book free site visit'),
  JSON_OBJECT('title','Sage green bedroom with soft lighting','href','/design-ideas/idea/sage-green-bedroom-delhi','image','https://images.unsplash.com/photo-1616594039964-ae9021a400a0?auto=format&fit=crop&w=1200&q=85','cta','Book free site visit'),
  JSON_OBJECT('title','Neutral living room with TV wall','href','/design-ideas/idea/neutral-living-room-noida','image','https://images.unsplash.com/photo-1600607687920-4e2a09cf159d?auto=format&fit=crop&w=1200&q=85','cta','Book free site visit')
), 50, 1),
('planning_tools', 'Plan precisely, shortlist smartly', 'Use our tools to plan, price, and style your home.', 'tool_cards', JSON_ARRAY(
  JSON_OBJECT('title','Design your dream, within budget','subtitle','Estimate a full home interior budget before you begin.','href','/cost-calculator','cta','Calculate now','image','https://images.unsplash.com/photo-1600210491892-03d54c0aaf87?auto=format&fit=crop&w=900&q=85','badge','Loved by 10K+ users'),
  JSON_OBJECT('title','Know your kitchen cost instantly','subtitle','Share kitchen size and finish preference to get a quick estimate.','href','/cost-calculator','cta','Calculate now','image','https://images.unsplash.com/photo-1556911220-bff31c812dba?auto=format&fit=crop&w=900&q=85','badge','Calculated by 10K+ users'),
  JSON_OBJECT('title','Explore your style. Get inspired.','subtitle','Browse room, colour, style, and unit references in one place.','href','/design-ideas','cta','Get started','image','https://images.unsplash.com/photo-1618220179428-22790b461013?auto=format&fit=crop&w=900&q=85','badge','Loved by homeowners')
), 70, 1)
ON DUPLICATE KEY UPDATE title=VALUES(title), subtitle=VALUES(subtitle), section_type=VALUES(section_type), items_json=VALUES(items_json), sort_order=VALUES(sort_order), is_active=VALUES(is_active), updated_at=CURRENT_TIMESTAMP;

INSERT INTO url_aliases (path, page_type, source, meta_title, meta_description, h1, canonical_url, is_active) VALUES
('/', 'home', 'system', 'Buy or Rent Homes & Hire Interior Designers | HomeInteriors360', 'Buy or rent homes and residential projects with photos, prices, layouts, and amenities, then hire verified interior designers for your home.', 'Buy or Rent Homes & Hire Interior Designers', '/', 1),
('/design-ideas', 'design_idea_index', 'system', 'Interior Design Ideas with Photos, Colours and Quotes | HomeInteriors360', 'Browse dynamic interior design ideas by room type, colour, city, style, layout, and dimensions. Save favourites and request a quotation.', 'Interior Design Ideas', '/design-ideas', 1),
('/professionals', 'professionals_index', 'system', 'Find Professionals', 'Browse verified architects, designers, and contractors with filters for city, work type, budget, experience, and rating.', 'Find Professionals', '/professionals', 1),
('/properties', 'property_index', 'system', 'Flats and Residential Projects for Sale | HomeInteriors360', 'Browse flats and residential projects for sale with photos, videos, floor plans, prices, amenities, inventory, and location details.', 'Buy or Rent Property', '/properties', 1),
('/home-interior-hire-a-designer', 'lead_page', 'system', 'Hire a Home Interior Designer | HomeInteriors360', 'Hire a home interior designer through HomeInteriors360. Compare verified interior designers, architects, and aggregator partners for full home interiors, kitchens, wardrobes, and renovation.', 'Hire a Home Interior Designer', '/home-interior-hire-a-designer', 1),
('/lead-marketplace', 'lead_marketplace', 'system', 'Buy Filtered Interior Design Leads | HomeInteriors360', 'Buy verified homeowner lead packages by city, society, budget, work type, and date range.', 'Buy Filtered Interior Design Leads', '/lead-marketplace', 1),
('/pricing', 'pricing', 'system', 'Pricing for Architects & Interior Designers', 'Choose lead purchase or managed account plans for architects and interior designers, with sales registration and growth support.', 'Pricing for Architects & Interior Designers', '/pricing', 1)
ON DUPLICATE KEY UPDATE page_type=VALUES(page_type), source=VALUES(source), updated_at=CURRENT_TIMESTAMP;

INSERT INTO url_aliases (path, page_type, entity_table, entity_id, source, meta_title, meta_description, h1, content_html, image_url, canonical_url, is_active)
SELECT CONCAT('/design-ideas/', slug), 'design_idea_alias', 'design_idea_aliases', id, 'design_idea_alias',
       meta_title, meta_description, title, CONCAT(COALESCE(intro_content, ''), '\n\n', COALESCE(outro_content, '')), hero_image, CONCAT('/design-ideas/', slug), is_active
FROM design_idea_aliases
ON DUPLICATE KEY UPDATE page_type=VALUES(page_type), entity_table=VALUES(entity_table), entity_id=VALUES(entity_id), source=VALUES(source), meta_title=COALESCE(url_aliases.meta_title, VALUES(meta_title)), meta_description=COALESCE(url_aliases.meta_description, VALUES(meta_description)), h1=COALESCE(url_aliases.h1, VALUES(h1)), image_url=COALESCE(url_aliases.image_url, VALUES(image_url)), canonical_url=VALUES(canonical_url), is_active=VALUES(is_active), updated_at=CURRENT_TIMESTAMP;

INSERT INTO url_aliases (path, page_type, entity_table, entity_id, source, meta_title, meta_description, h1, content_html, image_url, canonical_url, is_active)
SELECT CONCAT('/design-ideas/idea/', slug), 'design_idea_detail', 'design_ideas', id, 'design_idea',
       meta_title, meta_description, name, description, image_url, CONCAT('/design-ideas/idea/', slug), is_active
FROM design_ideas
ON DUPLICATE KEY UPDATE page_type=VALUES(page_type), entity_table=VALUES(entity_table), entity_id=VALUES(entity_id), source=VALUES(source), meta_title=COALESCE(url_aliases.meta_title, VALUES(meta_title)), meta_description=COALESCE(url_aliases.meta_description, VALUES(meta_description)), h1=COALESCE(url_aliases.h1, VALUES(h1)), image_url=COALESCE(url_aliases.image_url, VALUES(image_url)), canonical_url=VALUES(canonical_url), is_active=VALUES(is_active), updated_at=CURRENT_TIMESTAMP;

INSERT INTO url_aliases (path, page_type, entity_table, entity_id, source, meta_title, meta_description, h1, content_html, image_url, canonical_url, is_active)
SELECT CONCAT('/professionals/', slug), 'professional_detail', 'pros', id, 'professional',
       CONCAT(full_name, ' | HomeInteriors360'), specialization, full_name, bio, profile_pic, CONCAT('/professionals/', slug), is_active
FROM pros
ON DUPLICATE KEY UPDATE page_type=VALUES(page_type), entity_table=VALUES(entity_table), entity_id=VALUES(entity_id), source=VALUES(source), canonical_url=VALUES(canonical_url), is_active=VALUES(is_active), updated_at=CURRENT_TIMESTAMP;

INSERT INTO url_aliases (path, page_type, entity_table, entity_id, source, meta_title, meta_description, h1, content_html, image_url, canonical_url, is_active)
SELECT CONCAT('/property/', slug), 'property_detail', 'real_estate_projects', id, 'property_project',
       COALESCE(NULLIF(meta_title, ''), CONCAT(project_name, ' | Price, Floor Plans & Photos')), COALESCE(NULLIF(meta_description, ''), short_description), project_name, description,
       (SELECT media_url FROM real_estate_media m WHERE m.project_id=real_estate_projects.id AND m.media_type='image' ORDER BY m.is_cover DESC, m.sort_order ASC LIMIT 1),
       CONCAT('/property/', slug), is_active
FROM real_estate_projects
ON DUPLICATE KEY UPDATE page_type=VALUES(page_type), entity_table=VALUES(entity_table), entity_id=VALUES(entity_id), source=VALUES(source), canonical_url=VALUES(canonical_url), is_active=VALUES(is_active), updated_at=CURRENT_TIMESTAMP;

INSERT INTO url_aliases (path, page_type, entity_table, entity_id, source, meta_title, meta_description, h1, content_html, image_url, canonical_url, is_active)
SELECT CONCAT('/portfolio/', slug), 'portfolio_detail', 'projects', id, 'portfolio',
       CONCAT(project_name, ' | HomeInteriors360'), project_description, project_name, project_description,
       JSON_UNQUOTE(JSON_EXTRACT(media_json, '$[0]')), CONCAT('/portfolio/', slug), 1
FROM projects
ON DUPLICATE KEY UPDATE page_type=VALUES(page_type), entity_table=VALUES(entity_table), entity_id=VALUES(entity_id), source=VALUES(source), canonical_url=VALUES(canonical_url), is_active=VALUES(is_active), updated_at=CURRENT_TIMESTAMP;
