-- Replace seeded dummy imagery with a single default placeholder image
SET NAMES utf8mb4;

SET @default_image := 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR6Q82WISxpWPp5dHBTWHypFOZbRTvc0ST0xQ&s';

UPDATE pros
SET profile_pic = @default_image,
    cover_photo = @default_image
WHERE city = 'Gurgaon';

UPDATE projects
SET media_json = JSON_ARRAY(@default_image, @default_image, @default_image, @default_image)
WHERE pro_id IN (SELECT id FROM pros WHERE city = 'Gurgaon');

UPDATE reviews
SET photos_json = JSON_ARRAY(@default_image)
WHERE pro_id IN (SELECT id FROM pros WHERE city = 'Gurgaon');
