INSERT INTO site_content (key_name, content_value, content_type)
VALUES
  ('seo.home.title', 'Buy or Rent Homes & Hire Interior Designers | HomeInteriors360', 'text'),
  ('seo.home.description', 'Buy or rent homes and residential projects with photos, prices, layouts, and amenities, then hire verified interior designers for your home.', 'text')
ON DUPLICATE KEY UPDATE
  content_value=VALUES(content_value),
  content_type=VALUES(content_type);
