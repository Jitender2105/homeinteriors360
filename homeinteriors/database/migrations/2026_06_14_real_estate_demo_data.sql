INSERT INTO real_estate_projects (
  slug, project_name, listing_for, property_type, project_status, builder_name, rera_number, possession_date,
  address, locality, city, state, pincode, short_description, description, price_min, price_max, rent_min, rent_max,
  price_per_sqft, area_min, area_max, total_units, total_towers, total_area_acres, video_url,
  amenities_json, highlights_json, nearby_json, meta_title, meta_description, is_featured, is_active
) VALUES (
  'verdant-residences-sector-65-gurgaon',
  'Verdant Residences',
  'both',
  'Luxury Apartment',
  'Under Construction',
  'Verdant Developers',
  'HRERA-PKL-GGM-2026-001',
  '2028-03-31',
  'Golf Course Extension Road',
  'Sector 65',
  'Gurgaon',
  'Haryana',
  '122018',
  'Premium 3 and 4 BHK residences with landscaped courts, private club facilities, and direct access to Golf Course Extension Road.',
  'Verdant Residences is a low-density residential development planned around landscaped greens, generous apartment layouts, and practical daily amenities. The project includes multiple home configurations, resident recreation spaces, controlled access, and convenient connectivity to schools, offices, retail, and healthcare across Gurgaon.',
  24500000,
  41000000,
  95000,
  160000,
  14500,
  1690,
  2850,
  420,
  5,
  8.50,
  'https://www.youtube.com/embed/ScMzIvxBSi4',
  JSON_ARRAY('Clubhouse', 'Swimming Pool', 'Fitness Centre', 'Kids Play Area', 'Landscaped Gardens', '24x7 Security', 'Power Backup', 'Visitor Parking'),
  JSON_ARRAY('Low-density planning with five residential towers', 'Large balconies and efficient apartment layouts', 'Club and recreation facilities within the community', 'Direct access to Golf Course Extension Road'),
  JSON_ARRAY('Rapid Metro - 12 minutes', 'International school - 8 minutes', 'Multi-speciality hospital - 10 minutes', 'Sector 65 retail hub - 5 minutes'),
  'Verdant Residences Sector 65 Gurgaon | Price & Floor Plans',
  'Explore Verdant Residences in Sector 65 Gurgaon with prices, 3 and 4 BHK floor plans, photos, amenities, location, inventory, and enquiry details.',
  1,
  1
) ON DUPLICATE KEY UPDATE
  project_name=VALUES(project_name),
  updated_at=CURRENT_TIMESTAMP;

SET @property_project_id = (SELECT id FROM real_estate_projects WHERE slug='verdant-residences-sector-65-gurgaon' LIMIT 1);

DELETE FROM real_estate_floor_plans WHERE project_id=@property_project_id;
DELETE FROM real_estate_media WHERE project_id=@property_project_id;
DELETE FROM real_estate_units WHERE project_id=@property_project_id;

INSERT INTO real_estate_units (
  project_id, unit_name, bhk_type, unit_type, carpet_area, builtup_area, balconies, bathrooms,
  furnishing, sale_price, monthly_rent, maintenance_amount, available_units, is_active, sort_order
) VALUES
(@property_project_id, '3 BHK Residence', '3 BHK', 'Apartment', 1320, 1690, 3, 3, 'Semi-furnished', 24500000, 95000, 8500, 18, 1, 1),
(@property_project_id, '3 BHK Large Residence', '3 BHK', 'Apartment', 1580, 2050, 3, 4, 'Semi-furnished', 29800000, 120000, 10200, 11, 1, 2),
(@property_project_id, '4 BHK Residence', '4 BHK', 'Apartment', 2200, 2850, 4, 5, 'Semi-furnished', 41000000, 160000, 13800, 7, 1, 3);

INSERT INTO real_estate_media (project_id, media_type, media_url, title, category, is_cover, sort_order) VALUES
(@property_project_id, 'image', 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1800&q=85', 'Living room', 'Interiors', 1, 1),
(@property_project_id, 'image', 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1400&q=85', 'Project exterior', 'Exterior', 0, 2),
(@property_project_id, 'image', 'https://images.unsplash.com/photo-1600566753190-17f0baa2a6c3?auto=format&fit=crop&w=1400&q=85', 'Dining area', 'Interiors', 0, 3),
(@property_project_id, 'image', 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1400&q=85', 'Bedroom', 'Interiors', 0, 4),
(@property_project_id, 'image', 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=1400&q=85', 'Landscape and pool', 'Amenities', 0, 5);

INSERT INTO real_estate_floor_plans (project_id, title, image_url, area_label, price_label, sort_order) VALUES
(@property_project_id, '3 BHK Residence', 'https://images.unsplash.com/photo-1554469384-e58fac16e23a?auto=format&fit=crop&w=1200&q=80', '1,690 sq.ft.', '₹2.45 Cr onwards', 1),
(@property_project_id, '4 BHK Residence', 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=1200&q=80', '2,850 sq.ft.', '₹4.10 Cr onwards', 2);
