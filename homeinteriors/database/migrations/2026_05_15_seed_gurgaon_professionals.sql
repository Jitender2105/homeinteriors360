-- Seed Gurgaon professionals and remove old demo profiles
SET NAMES utf8mb4;

DELETE FROM pros
WHERE slug IN (
  'ananya-sharma-interiors',
  'raghav-menon-architects',
  'vikas-bedi-contracting',
  'neev-interiors-gurgaon',
  'tribuz-interiors-gurgaon',
  'silqe-design-studio-gurgaon',
  'classygaze-interiors-gurgaon',
  'ss-interior-solution-gurgaon',
  'pahal-design-studio-gurgaon',
  'archone-studio-gurgaon',
  'melange-by-sangeeta-kapoor-gurgaon'
);

INSERT INTO pros (
  full_name, slug, profile_pic, cover_photo, role, profile_description, specialization,
  primary_work_type, primary_work_area, verification_status, is_premium, rating, years_experience, projects_delivered,
  starting_price, min_project_value, max_project_value, consultation_fee, city, service_areas,
  materials_json, design_styles_json, languages_json, certifications_json, response_time_hours,
  bio, why_work_with_me, offerings_json, is_active
)
VALUES
(
  'Neev Interiors',
  'neev-interiors-gurgaon',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'Designer',
  'Luxury home interiors with practical space planning for Gurgaon apartments and villas.',
  'Luxury Residential Interiors',
  'Full Home',
  'Apartments',
  1,
  1,
  4.8,
  11,
  58,
  600000,
  500000,
  4200000,
  3000,
  'Gurgaon',
  JSON_ARRAY('Golf Course Road', 'DLF Phase 1', 'Sohna Road', 'Sector 57'),
  JSON_ARRAY('BWP Plywood', 'Acrylic Finish', 'Hafele Hardware', 'Quartz', 'PU Paint'),
  JSON_ARRAY('Modern', 'Minimal Luxe', 'Warm Contemporary'),
  JSON_ARRAY('Hindi', 'English'),
  JSON_ARRAY('Premium Modular Partner', 'Space Planning Certified'),
  3,
  'We build premium homes around storage, light, and daily use patterns.',
  'Design clarity, site discipline, and a polished handover experience.',
  JSON_ARRAY('Full Home Interiors', 'Wardrobes', 'Kitchen', 'Lighting', 'False Ceiling'),
  1
),
(
  'Tribuz Interiors',
  'tribuz-interiors-gurgaon',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'Designer',
  'Thoughtfully curated spaces that balance modern sophistication with functional planning.',
  'Luxury Home & Office Design',
  'Full Home',
  'Apartments',
  1,
  1,
  4.7,
  9,
  44,
  550000,
  450000,
  3800000,
  2500,
  'Gurgaon',
  JSON_ARRAY('Golf Course Extension', 'Sector 66', 'DLF Phase 5', 'Sohna Road'),
  JSON_ARRAY('Marine Plywood', 'PU Finish', 'Hafele', 'Natural Veneer'),
  JSON_ARRAY('Contemporary', 'Luxury Modern', 'Minimal'),
  JSON_ARRAY('Hindi', 'English'),
  JSON_ARRAY('Turnkey Delivery Process', 'Interior Workflow Planning'),
  4,
  'We keep the process transparent and the design language clean.',
  'Smart layouts, premium detailing, and consistent communication.',
  JSON_ARRAY('Residential Interiors', 'Office Interiors', 'Wardrobes', 'Modular Kitchens'),
  1
),
(
  'Silqe Design Studio',
  'silqe-design-studio-gurgaon',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'Architect',
  'High-end turnkey homes across Gurgaon’s premium neighborhoods.',
  'Turnkey Luxury Interiors',
  'Renovation',
  'Villa',
  1,
  1,
  4.9,
  13,
  67,
  700000,
  650000,
  5200000,
  4000,
  'Gurgaon',
  JSON_ARRAY('Golf Course Road', 'DLF Phase 4', 'DLF Phase 5', 'Sushant Lok'),
  JSON_ARRAY('Imported Veneer', 'Quartzite', 'Microtopping', 'Solid Surface'),
  JSON_ARRAY('Modern Luxe', 'Arched Contemporary', 'Soft Minimal'),
  JSON_ARRAY('Hindi', 'English'),
  JSON_ARRAY('Architecture + Interiors Integration', 'Turnkey Project Management'),
  2,
  'We design high-touch luxury homes with disciplined detailing.',
  'Architectural planning, material curation, and sharp execution control.',
  JSON_ARRAY('Villas', 'Penthouses', 'Renovation', 'Custom Furniture', 'Lighting'),
  1
),
(
  'ClassyGaze Interiors',
  'classygaze-interiors-gurgaon',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'Designer',
  'Residential and workplace interiors focused on comfort, usability, and a premium finish.',
  'Home & Workplace Design',
  'Full Home',
  'Commercial',
  1,
  0,
  4.6,
  8,
  33,
  420000,
  300000,
  2800000,
  1800,
  'Gurgaon',
  JSON_ARRAY('Sector 48', 'Udyog Vihar', 'Golf Course Extension', 'Sohna Road'),
  JSON_ARRAY('BWP Plywood', 'Acrylic Shutters', 'PU Paint', 'Quartz'),
  JSON_ARRAY('Modern', 'Contemporary', 'Warm Neutral'),
  JSON_ARRAY('Hindi', 'English'),
  JSON_ARRAY('Space Optimization Focus', 'Material Coordination'),
  5,
  'We create comfortable spaces that are practical for daily use and long-term maintenance.',
  'Responsive design development, faster handovers, and cleaner finish checks.',
  JSON_ARRAY('Residential Design', 'Office Interiors', 'Kitchen', 'Wardrobe'),
  1
),
(
  'SS Interior Solution',
  'ss-interior-solution-gurgaon',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'Contractor',
  'Turnkey residential execution with modular and civil coordination in Gurgaon.',
  'Modular + Turnkey Execution',
  'Kitchen',
  'Apartments',
  1,
  0,
  4.5,
  16,
  96,
  280000,
  220000,
  2600000,
  1500,
  'Gurgaon',
  JSON_ARRAY('Sector 46', 'Sector 56', 'Sohna Road', 'Golf Course Road'),
  JSON_ARRAY('Greenply', 'CenturyPly', 'Hettich', 'Asian Paints'),
  JSON_ARRAY('Practical Modern', 'Budget Premium', 'Functional'),
  JSON_ARRAY('Hindi', 'English'),
  JSON_ARRAY('Execution Team', 'On-Site Supervision'),
  6,
  'We manage execution with discipline and reliable milestone control.',
  'Site coordination, quality checks, and practical material choices.',
  JSON_ARRAY('Civil', 'Electrical', 'Plumbing', 'Modular Kitchen', 'Wardrobes'),
  1
),
(
  'Pahal Design Studio',
  'pahal-design-studio-gurgaon',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'Designer',
  'A design-first studio making compact Gurgaon homes feel larger and more composed.',
  'Space Efficient Interiors',
  'Wardrobe',
  'Apartments',
  1,
  0,
  4.4,
  7,
  29,
  380000,
  250000,
  2200000,
  1200,
  'Gurgaon',
  JSON_ARRAY('Sector 67', 'Sector 56', 'DLF Phase 3', 'Sushant Lok'),
  JSON_ARRAY('Laminate', 'Marine Plywood', 'PU Paint', 'Soft Close Hardware'),
  JSON_ARRAY('Minimal', 'Modern', 'Scandinavian'),
  JSON_ARRAY('Hindi', 'English'),
  JSON_ARRAY('Compact Home Specialist', 'Custom Storage Planning'),
  5,
  'We focus on compact homes that still feel calm, bright, and premium.',
  'Better storage planning and layouts that are easy to live with.',
  JSON_ARRAY('Wardrobes', 'Compact Kitchens', 'TV Units', 'Study Units'),
  1
),
(
  'Archone Studio',
  'archone-studio-gurgaon',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'Architect',
  'Custom luxury interiors with architecture-led planning for modern Gurgaon homes.',
  'Custom Luxury Interiors',
  'Renovation',
  'Villa',
  1,
  1,
  4.8,
  14,
  52,
  680000,
  550000,
  4800000,
  3500,
  'Gurgaon',
  JSON_ARRAY('Golf Course Road', 'DLF Phase 2', 'Sector 57', 'Sushant Lok'),
  JSON_ARRAY('Veneer', 'Quartz', 'Hafele', 'PU Finish'),
  JSON_ARRAY('Contemporary', 'Luxury', 'Warm Minimal'),
  JSON_ARRAY('Hindi', 'English'),
  JSON_ARRAY('Architecture + Interior Consultancy', 'Luxury Detailing'),
  4,
  'We combine architectural clarity with warm luxury interiors.',
  'Strong concept direction, careful detailing, and premium finish control.',
  JSON_ARRAY('Villas', 'Luxury Homes', 'Renovation', 'Kitchen', 'Wardrobes'),
  1
),
(
  'Melange by Sangeeta Kapoor',
  'melange-by-sangeeta-kapoor-gurgaon',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
  'Designer',
  'Luxury interiors for contemporary Gurgaon residences with bespoke styling and polished execution.',
  'Luxury Interior Design Studio',
  'Full Home',
  'Apartments',
  1,
  1,
  4.9,
  12,
  61,
  750000,
  650000,
  5500000,
  5000,
  'Gurgaon',
  JSON_ARRAY('DLF City Phase 1', 'Golf Course Extension', 'Sohna Road', 'Sector 42'),
  JSON_ARRAY('Bespoke Veneer', 'Natural Stone', 'Hafele', 'Luxury Fabrics'),
  JSON_ARRAY('Modern Luxury', 'Warm Contemporary', 'Artistic'),
  JSON_ARRAY('Hindi', 'English'),
  JSON_ARRAY('Bespoke Styling', 'Luxury Client Experience'),
  2,
  'We create highly personalized homes with a boutique design touch.',
  'Tailored concept boards, curated materials, and strong visual storytelling.',
  JSON_ARRAY('Luxury Homes', 'Styling', 'Full Home Interiors', 'Wardrobes', 'Kitchen'),
  1
)
ON DUPLICATE KEY UPDATE
  profile_pic = VALUES(profile_pic),
  cover_photo = VALUES(cover_photo),
  role = VALUES(role),
  profile_description = VALUES(profile_description),
  specialization = VALUES(specialization),
  primary_work_type = VALUES(primary_work_type),
  primary_work_area = VALUES(primary_work_area),
  verification_status = VALUES(verification_status),
  is_premium = VALUES(is_premium),
  rating = VALUES(rating),
  years_experience = VALUES(years_experience),
  projects_delivered = VALUES(projects_delivered),
  starting_price = VALUES(starting_price),
  min_project_value = VALUES(min_project_value),
  max_project_value = VALUES(max_project_value),
  consultation_fee = VALUES(consultation_fee),
  city = VALUES(city),
  service_areas = VALUES(service_areas),
  materials_json = VALUES(materials_json),
  design_styles_json = VALUES(design_styles_json),
  languages_json = VALUES(languages_json),
  certifications_json = VALUES(certifications_json),
  response_time_hours = VALUES(response_time_hours),
  bio = VALUES(bio),
  why_work_with_me = VALUES(why_work_with_me),
  offerings_json = VALUES(offerings_json),
  is_active = VALUES(is_active),
  updated_at = NOW();

INSERT INTO projects (
  pro_id, slug, project_name, project_description, total_cost, bhk_type, year_completed,
  timeline_months, project_duration_label, location, work_type, area_of_work, materials_json,
  media_json, video_url, design_style, team_size, warranty_years,
  testimonial_client_name, testimonial_text, testimonial_rating
)
SELECT p.id, 'neev-interiors-golf-course-road-3bhk', 'Golf Course Road 3BHK Full Home',
  'A balanced full-home transformation with layered lighting, storage planning, and warm material tones.',
  2850000, '3BHK', 2025, 6, '6 months', 'Golf Course Road, Gurgaon', 'Full Home', 'Apartments',
  JSON_ARRAY('BWP Plywood', 'Acrylic Finish', 'Quartz', 'Hafele Hardware'),
  JSON_ARRAY(
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1615529182904-14819c35db37?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1500&q=80',
    'https://images.pexels.com/photos/6585750/pexels-photo-6585750.jpeg?auto=compress&cs=tinysrgb&w=1500'
  ), NULL, 'Modern', 12, 8, 'Ankit Verma', 'The layout feels much larger and the finish quality is excellent.', 5
FROM pros p WHERE p.slug = 'neev-interiors-gurgaon'
ON DUPLICATE KEY UPDATE
  project_name = VALUES(project_name), project_description = VALUES(project_description), total_cost = VALUES(total_cost),
  bhk_type = VALUES(bhk_type), year_completed = VALUES(year_completed), timeline_months = VALUES(timeline_months),
  project_duration_label = VALUES(project_duration_label), location = VALUES(location), work_type = VALUES(work_type),
  area_of_work = VALUES(area_of_work), materials_json = VALUES(materials_json), media_json = VALUES(media_json),
  video_url = VALUES(video_url), design_style = VALUES(design_style), team_size = VALUES(team_size),
  warranty_years = VALUES(warranty_years), testimonial_client_name = VALUES(testimonial_client_name),
  testimonial_text = VALUES(testimonial_text), testimonial_rating = VALUES(testimonial_rating), updated_at = NOW();

INSERT INTO projects (
  pro_id, slug, project_name, project_description, total_cost, bhk_type, year_completed,
  timeline_months, project_duration_label, location, work_type, area_of_work, materials_json,
  media_json, video_url, design_style, team_size, warranty_years,
  testimonial_client_name, testimonial_text, testimonial_rating
)
SELECT p.id, 'tribuz-interiors-dlf-phase-5-luxe-4bhk', 'DLF Phase 5 Luxe 4BHK',
  'Luxury apartment design with custom wardrobes, calm neutral tones, and a highly coordinated execution plan.',
  3650000, '4BHK', 2025, 7, '7 months', 'DLF Phase 5, Gurgaon', 'Full Home', 'Apartments',
  JSON_ARRAY('Marine Plywood', 'PU Finish', 'Quartzite', 'Hafele Fittings'),
  JSON_ARRAY(
    'https://images.unsplash.com/photo-1493666438817-866a91353ca9?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1615874959474-d609969a20ed?auto=format&fit=crop&w=1500&q=80',
    'https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg?auto=compress&cs=tinysrgb&w=1500'
  ), 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'Luxury Contemporary', 15, 10, 'Rohit Sethi', 'Their planning and follow-through were excellent from concept to handover.', 5
FROM pros p WHERE p.slug = 'tribuz-interiors-gurgaon'
ON DUPLICATE KEY UPDATE
  project_name = VALUES(project_name), project_description = VALUES(project_description), total_cost = VALUES(total_cost),
  bhk_type = VALUES(bhk_type), year_completed = VALUES(year_completed), timeline_months = VALUES(timeline_months),
  project_duration_label = VALUES(project_duration_label), location = VALUES(location), work_type = VALUES(work_type),
  area_of_work = VALUES(area_of_work), materials_json = VALUES(materials_json), media_json = VALUES(media_json),
  video_url = VALUES(video_url), design_style = VALUES(design_style), team_size = VALUES(team_size),
  warranty_years = VALUES(warranty_years), testimonial_client_name = VALUES(testimonial_client_name),
  testimonial_text = VALUES(testimonial_text), testimonial_rating = VALUES(testimonial_rating), updated_at = NOW();

INSERT INTO projects (
  pro_id, slug, project_name, project_description, total_cost, bhk_type, year_completed,
  timeline_months, project_duration_label, location, work_type, area_of_work, materials_json,
  media_json, video_url, design_style, team_size, warranty_years,
  testimonial_client_name, testimonial_text, testimonial_rating
)
SELECT p.id, 'silqe-design-studio-sohna-road-villa', 'Sohna Road Villa Renovation',
  'A villa refresh with improved circulation, premium surfaces, and cleaner architectural detailing.',
  6100000, 'Villa', 2025, 9, '9 months', 'Sohna Road, Gurgaon', 'Renovation', 'Villa',
  JSON_ARRAY('Natural Veneer', 'Quartzite', 'Microtopping', 'Solid Surface'),
  JSON_ARRAY(
    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
    'https://images.unsplash.com/photo-1616486029423-aaa4789e8c9a?auto=format&fit=crop&w=1500&q=80',
    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
    'https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg?auto=compress&cs=tinysrgb&w=1500'
  ), NULL, 'Modern Luxe', 18, 12, 'Nisha Mehta', 'The villa feels refined and much more functional than before.', 5
FROM pros p WHERE p.slug = 'silqe-design-studio-gurgaon'
ON DUPLICATE KEY UPDATE
  project_name = VALUES(project_name), project_description = VALUES(project_description), total_cost = VALUES(total_cost),
  bhk_type = VALUES(bhk_type), year_completed = VALUES(year_completed), timeline_months = VALUES(timeline_months),
  project_duration_label = VALUES(project_duration_label), location = VALUES(location), work_type = VALUES(work_type),
  area_of_work = VALUES(area_of_work), materials_json = VALUES(materials_json), media_json = VALUES(media_json),
  video_url = VALUES(video_url), design_style = VALUES(design_style), team_size = VALUES(team_size),
  warranty_years = VALUES(warranty_years), testimonial_client_name = VALUES(testimonial_client_name),
  testimonial_text = VALUES(testimonial_text), testimonial_rating = VALUES(testimonial_rating), updated_at = NOW();

INSERT INTO projects (
  pro_id, slug, project_name, project_description, total_cost, bhk_type, year_completed,
  timeline_months, project_duration_label, location, work_type, area_of_work, materials_json,
  media_json, video_url, design_style, team_size, warranty_years,
  testimonial_client_name, testimonial_text, testimonial_rating
)
SELECT p.id, 'classygaze-sector48-workplace-residence', 'Sector 48 Workspace + Residence',
  'Dual-use interior planning for a compact home office and residence with cleaner zoning.',
  1450000, 'Commercial', 2024, 4, '4 months', 'Sector 48, Gurgaon', 'Commercial', 'Commercial',
  JSON_ARRAY('BWP Plywood', 'PU Paint', 'Quartz', 'Glass Partition'),
  JSON_ARRAY(
    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
    'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1500&q=80',
    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s',
    'https://images.unsplash.com/photo-1455390582262-044cdead277a?auto=format&fit=crop&w=1500&q=80'
  ), NULL, 'Modern Functional', 8, 5, 'Aditya Khanna', 'Very practical space planning and a clean finishing standard.', 4
FROM pros p WHERE p.slug = 'classygaze-interiors-gurgaon'
ON DUPLICATE KEY UPDATE
  project_name = VALUES(project_name), project_description = VALUES(project_description), total_cost = VALUES(total_cost),
  bhk_type = VALUES(bhk_type), year_completed = VALUES(year_completed), timeline_months = VALUES(timeline_months),
  project_duration_label = VALUES(project_duration_label), location = VALUES(location), work_type = VALUES(work_type),
  area_of_work = VALUES(area_of_work), materials_json = VALUES(materials_json), media_json = VALUES(media_json),
  video_url = VALUES(video_url), design_style = VALUES(design_style), team_size = VALUES(team_size),
  warranty_years = VALUES(warranty_years), testimonial_client_name = VALUES(testimonial_client_name),
  testimonial_text = VALUES(testimonial_text), testimonial_rating = VALUES(testimonial_rating), updated_at = NOW();

INSERT INTO projects (
  pro_id, slug, project_name, project_description, total_cost, bhk_type, year_completed,
  timeline_months, project_duration_label, location, work_type, area_of_work, materials_json,
  media_json, video_url, design_style, team_size, warranty_years,
  testimonial_client_name, testimonial_text, testimonial_rating
)
SELECT p.id, 'ss-interior-solution-sector56-kitchen-wardrobe', 'Sector 56 Kitchen + Wardrobe Upgrade',
  'Targeted kitchen and wardrobe work with durable materials and quick execution.',
  980000, '3BHK', 2024, 3, '12 weeks', 'Sector 56, Gurgaon', 'Kitchen', 'Apartments',
  JSON_ARRAY('Marine Plywood', 'PU Paint', 'Hettich Channels'),
  JSON_ARRAY(
    'https://images.unsplash.com/photo-1556912167-f556f1f39fdf?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1560185127-6ed189bf02f4?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1600489000022-c2086d79f9d4?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1600573472550-8090b5e0745e?auto=format&fit=crop&w=1500&q=80'
  ), NULL, 'Contemporary', 9, 5, 'Manish Jain', 'Great storage planning and very neat finish work.', 5
FROM pros p WHERE p.slug = 'ss-interior-solution-gurgaon'
ON DUPLICATE KEY UPDATE
  project_name = VALUES(project_name), project_description = VALUES(project_description), total_cost = VALUES(total_cost),
  bhk_type = VALUES(bhk_type), year_completed = VALUES(year_completed), timeline_months = VALUES(timeline_months),
  project_duration_label = VALUES(project_duration_label), location = VALUES(location), work_type = VALUES(work_type),
  area_of_work = VALUES(area_of_work), materials_json = VALUES(materials_json), media_json = VALUES(media_json),
  video_url = VALUES(video_url), design_style = VALUES(design_style), team_size = VALUES(team_size),
  warranty_years = VALUES(warranty_years), testimonial_client_name = VALUES(testimonial_client_name),
  testimonial_text = VALUES(testimonial_text), testimonial_rating = VALUES(testimonial_rating), updated_at = NOW();

INSERT INTO projects (
  pro_id, slug, project_name, project_description, total_cost, bhk_type, year_completed,
  timeline_months, project_duration_label, location, work_type, area_of_work, materials_json,
  media_json, video_url, design_style, team_size, warranty_years,
  testimonial_client_name, testimonial_text, testimonial_rating
)
SELECT p.id, 'pahal-design-studio-sector67-2bhk', 'Sector 67 Space-Smart 2BHK',
  'Compact home optimized with integrated wardrobe design, study units, and cleaner circulation.',
  1250000, '2BHK', 2024, 4, '4 months', 'Sector 67, Gurgaon', 'Wardrobe', 'Apartments',
  JSON_ARRAY('Laminate', 'Marine Plywood', 'Soft Close Hardware'),
  JSON_ARRAY(
    'https://images.unsplash.com/photo-1616486029423-aaa4789e8c9a?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1500&q=80',
    'https://images.pexels.com/photos/3184325/pexels-photo-3184325.jpeg?auto=compress&cs=tinysrgb&w=1500',
    'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s'
  ), NULL, 'Minimal', 6, 3, 'Sakshi Jain', 'The compact apartment feels much better organized now.', 4
FROM pros p WHERE p.slug = 'pahal-design-studio-gurgaon'
ON DUPLICATE KEY UPDATE
  project_name = VALUES(project_name), project_description = VALUES(project_description), total_cost = VALUES(total_cost),
  bhk_type = VALUES(bhk_type), year_completed = VALUES(year_completed), timeline_months = VALUES(timeline_months),
  project_duration_label = VALUES(project_duration_label), location = VALUES(location), work_type = VALUES(work_type),
  area_of_work = VALUES(area_of_work), materials_json = VALUES(materials_json), media_json = VALUES(media_json),
  video_url = VALUES(video_url), design_style = VALUES(design_style), team_size = VALUES(team_size),
  warranty_years = VALUES(warranty_years), testimonial_client_name = VALUES(testimonial_client_name),
  testimonial_text = VALUES(testimonial_text), testimonial_rating = VALUES(testimonial_rating), updated_at = NOW();

INSERT INTO projects (
  pro_id, slug, project_name, project_description, total_cost, bhk_type, year_completed,
  timeline_months, project_duration_label, location, work_type, area_of_work, materials_json,
  media_json, video_url, design_style, team_size, warranty_years,
  testimonial_client_name, testimonial_text, testimonial_rating
)
SELECT p.id, 'archone-dlf-phase-2-villa-luxury', 'DLF Phase 2 Luxury Villa',
  'Architecture-led villa work with premium detailing, strong material continuity, and a tailored layout.',
  7200000, 'Villa', 2025, 10, '10 months', 'DLF Phase 2, Gurgaon', 'Renovation', 'Villa',
  JSON_ARRAY('Veneer', 'Quartz', 'Hafele', 'PU Finish'),
  JSON_ARRAY(
    'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1615874959474-d609969a20ed?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1600210492486-724fe5c67fb2?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1600121848594-d8644e57abab?auto=format&fit=crop&w=1500&q=80'
  ), 'https://www.youtube.com/embed/ScMzIvxBSi4', 'Luxury', 20, 12, 'Kunal Arora', 'Strong architecture planning and premium finish control throughout.', 5
FROM pros p WHERE p.slug = 'archone-studio-gurgaon'
ON DUPLICATE KEY UPDATE
  project_name = VALUES(project_name), project_description = VALUES(project_description), total_cost = VALUES(total_cost),
  bhk_type = VALUES(bhk_type), year_completed = VALUES(year_completed), timeline_months = VALUES(timeline_months),
  project_duration_label = VALUES(project_duration_label), location = VALUES(location), work_type = VALUES(work_type),
  area_of_work = VALUES(area_of_work), materials_json = VALUES(materials_json), media_json = VALUES(media_json),
  video_url = VALUES(video_url), design_style = VALUES(design_style), team_size = VALUES(team_size),
  warranty_years = VALUES(warranty_years), testimonial_client_name = VALUES(testimonial_client_name),
  testimonial_text = VALUES(testimonial_text), testimonial_rating = VALUES(testimonial_rating), updated_at = NOW();

INSERT INTO projects (
  pro_id, slug, project_name, project_description, total_cost, bhk_type, year_completed,
  timeline_months, project_duration_label, location, work_type, area_of_work, materials_json,
  media_json, video_url, design_style, team_size, warranty_years,
  testimonial_client_name, testimonial_text, testimonial_rating
)
SELECT p.id, 'melange-dlf-city-phase-1-premium-4bhk', 'DLF City Phase 1 Premium 4BHK',
  'A bespoke home with expressive materials, balanced lighting, and elegant custom furniture.',
  5400000, '4BHK', 2025, 8, '8 months', 'DLF City Phase 1, Gurgaon', 'Full Home', 'Apartments',
  JSON_ARRAY('Bespoke Veneer', 'Natural Stone', 'Hafele', 'Luxury Fabrics'),
  JSON_ARRAY(
    'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1556911220-bff31c812dba?auto=format&fit=crop&w=1500&q=80',
    'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1500&q=80',
    'https://images.pexels.com/photos/8867439/pexels-photo-8867439.jpeg?auto=compress&cs=tinysrgb&w=1500'
  ), NULL, 'Modern Luxury', 16, 10, 'Megha Nair', 'They delivered a beautiful home and kept us informed at each stage.', 5
FROM pros p WHERE p.slug = 'melange-by-sangeeta-kapoor-gurgaon'
ON DUPLICATE KEY UPDATE
  project_name = VALUES(project_name), project_description = VALUES(project_description), total_cost = VALUES(total_cost),
  bhk_type = VALUES(bhk_type), year_completed = VALUES(year_completed), timeline_months = VALUES(timeline_months),
  project_duration_label = VALUES(project_duration_label), location = VALUES(location), work_type = VALUES(work_type),
  area_of_work = VALUES(area_of_work), materials_json = VALUES(materials_json), media_json = VALUES(media_json),
  video_url = VALUES(video_url), design_style = VALUES(design_style), team_size = VALUES(team_size),
  warranty_years = VALUES(warranty_years), testimonial_client_name = VALUES(testimonial_client_name),
  testimonial_text = VALUES(testimonial_text), testimonial_rating = VALUES(testimonial_rating), updated_at = NOW();

DELETE FROM pros
WHERE slug IN ('ananya-sharma-interiors', 'raghav-menon-architects', 'vikas-bedi-contracting');
