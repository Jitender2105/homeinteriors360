<?php

declare(strict_types=1);

final class SiteRepository
{
    private static ?array $contentCache = null;

    private static function professionalSelectSql(string $alias = 'pros'): string
    {
        return $alias . '.*,
                COALESCE(' . $alias . '.verification_status_code, IF(' . $alias . '.verification_status=1, "PROFESSIONAL_VERIFIED", "UNVERIFIED")) AS trust_verification_status,
                COALESCE(' . $alias . '.listing_tier, IF(' . $alias . '.is_premium=1, "PAID", "FREE")) AS trust_listing_tier,
                COALESCE(' . $alias . '.accepting_leads, 1) AS trust_accepting_leads';
    }

    private static function parseJsonArray(mixed $value): array
    {
        if (is_array($value)) {
            $values = [];
            foreach ($value as $item) {
                if (is_array($item)) {
                    array_push($values, ...self::parseJsonArray($item));
                    continue;
                }

                $item = trim((string)$item);
                if ($item === '') {
                    continue;
                }

                $decodedItem = json_decode($item, true);
                if (is_array($decodedItem)) {
                    array_push($values, ...self::parseJsonArray($decodedItem));
                    continue;
                }

                $values[] = $item;
            }
            return array_values($values);
        }
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return [];
            }

            $decoded = json_decode($trimmed, true);
            if (is_array($decoded)) {
                return self::parseJsonArray($decoded);
            }
            if (is_string($decoded) && $decoded !== $trimmed) {
                return self::parseJsonArray($decoded);
            }

            if (str_contains($trimmed, "\n")) {
                return array_values(array_filter(array_map('trim', preg_split('/\R+/', $trimmed) ?: []), static fn(string $v): bool => $v !== ''));
            }
            if (str_contains($trimmed, ',')) {
                return array_values(array_filter(array_map('trim', explode(',', $trimmed)), static fn(string $v): bool => $v !== ''));
            }
        }
        return [];
    }

    private static function parseTextLines(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('strval', $value), static fn(string $v): bool => trim($v) !== ''));
        }
        $text = trim((string)$value);
        if ($text === '') {
            return [];
        }
        if (str_contains($text, "\n")) {
            return array_values(array_filter(array_map('trim', preg_split('/\R+/', $text) ?: []), static fn(string $v): bool => $v !== ''));
        }
        return array_values(array_filter(array_map('trim', explode('|', $text)), static fn(string $v): bool => $v !== ''));
    }

    public static function professionalStandardOptions(): array
    {
        return [
            'roles' => ['Architect', 'Designer', 'Contractor'],
            'cities' => ['Gurgaon', 'Delhi', 'Noida', 'Greater Noida', 'Ghaziabad', 'Faridabad'],
            'service_regions' => ['Delhi NCR', 'Gurgaon', 'Delhi', 'Noida', 'Greater Noida', 'Ghaziabad', 'Faridabad', 'Manesar', 'Sohna Road', 'Dwarka Expressway'],
            'work_types' => ['Full Home Interior', 'Modular Kitchen', 'Wardrobe', 'Renovation', 'False Ceiling', 'Painting', 'Lighting', 'Furniture', 'Bathroom', 'Pooja Room', 'Office Interiors', 'Commercial Interiors', 'Turnkey Interiors', 'Civil Work'],
            'specializations' => ['Residential Interiors', 'Luxury Interiors', 'Modular Interiors', 'Budget Interiors', 'Space Planning', '3D Design', 'Renovation', 'Kitchen Specialist', 'Wardrobe Specialist', 'Furniture Design', 'Turnkey Execution', 'Vaastu Interior Planning'],
            'portfolio_areas' => ['Living Room', 'Dining Area', 'Modular Kitchen', 'Master Bedroom', 'Bedroom', 'Kids Room', 'Guest Bedroom', 'Wardrobe', 'Bathroom', 'Pooja Room', 'Study Room', 'Balcony', 'Full Home', 'Office', 'Retail Space'],
            'bhk_types' => ['1BHK', '2BHK', '3BHK', '4BHK', 'Villa', 'Commercial'],
            'materials' => ['MDF', 'HDHMR', 'BWP Plywood', 'BWR Plywood', 'Marine Plywood', 'Laminate', 'Acrylic', 'PU Finish', 'Veneer', 'Duco', 'Glass', 'Quartz', 'Granite', 'Marble', 'Hettich Hardware', 'Hafele Hardware', 'Ebco Hardware', 'Local Hardware'],
            'offerings' => ['Full Home Interiors', 'Modular Kitchen', 'Wardrobes', 'TV Units', 'False Ceiling', 'Painting', 'Lighting', 'Civil Work', 'Furniture', 'Pooja Units', 'Smart Home', 'Decor', 'Site Supervision'],
            'design_styles' => ['Modern', 'Minimal', 'Luxury', 'Scandinavian', 'Indian', 'Contemporary', 'Boho', 'Japandi', 'Classic', 'Traditional', 'Industrial', 'Art Deco', 'Coastal'],
            'languages' => ['Hindi', 'English', 'Punjabi', 'Haryanvi'],
            'certifications' => ['Architecture Degree', 'Interior Design Diploma', 'Modular Kitchen Certification', 'Vastu Consultant', 'Project Management Certification', 'Brand Authorized Partner'],
            'verification_statuses' => ['UNVERIFIED', 'PHONE_VERIFIED', 'IDENTITY_VERIFIED', 'BUSINESS_VERIFIED', 'PROFESSIONAL_VERIFIED'],
            'listing_tiers' => ['FREE', 'PAID', 'SPONSORED'],
            'portfolio_moderation_statuses' => ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED'],
            'portfolio_verification_statuses' => ['UNVERIFIED', 'ADMIN_REVIEWED', 'CLIENT_CONFIRMED', 'PROFESSIONAL_VERIFIED'],
        ];
    }

    public static function allContent(): array
    {
        if (self::$contentCache !== null) {
            return self::$contentCache;
        }

        $rows = Database::query('SELECT key_name, content_value, content_type FROM site_content ORDER BY key_name');
        $map = [];
        foreach ($rows as $row) {
            $value = $row['content_value'];
            if (($row['content_type'] ?? 'text') === 'json') {
                $decoded = json_decode((string)$value, true);
                $value = $decoded === null ? [] : $decoded;
            }
            $map[$row['key_name']] = $value;
        }

        self::$contentCache = $map;
        return $map;
    }

    public static function content(string $key, mixed $fallback = ''): mixed
    {
        $all = self::allContent();
        return $all[$key] ?? $fallback;
    }

    private static function slugToLabel(string $slug): string
    {
        $slug = trim($slug, '-');
        if ($slug === '') {
            return '';
        }
        $slug = str_replace(['-and-', '-or-'], ' / ', $slug);
        $slug = str_replace('-', ' ', $slug);
        $slug = preg_replace('/\s+/', ' ', $slug) ?? $slug;
        return ucwords(trim($slug));
    }

    public static function resolveDirectoryAlias(string $alias): ?array
    {
        $slug = strtolower(trim($alias, '/'));
        $slug = preg_replace('/-+/', '-', $slug) ?? $slug;
        if ($slug === '') {
            return null;
        }

        $filters = [];
        $title = '';
        $subtitle = 'Browse matching professionals and shortlist the right expert.';

        if (preg_match('/-in-([a-z0-9-]+)$/', $slug, $match)) {
            $filters['city'] = self::slugToLabel((string)$match[1]);
            $slug = substr($slug, 0, -strlen($match[0]));
        }

        $workMap = [
            'full-home' => 'Full Home',
            'kitchen' => 'Kitchen',
            'wardrobe' => 'Wardrobe',
            'renovation' => 'Renovation',
        ];
        foreach ($workMap as $needle => $label) {
            if (str_contains($slug, $needle)) {
                $filters['work_type'] = $label;
                break;
            }
        }

        if (str_contains($slug, 'architect-interior-designer') || str_contains($slug, 'architect-and-interior-designer')) {
            $title = 'Architect / Interior Designer';
        } elseif (str_contains($slug, 'architect')) {
            $title = 'Architect';
            $filters['role'] = 'Architect';
        } elseif (str_contains($slug, 'interior-designer') || str_contains($slug, 'designer')) {
            $title = 'Interior Designer';
            $filters['role'] = 'Designer';
        }

        if ($title === '' && isset($filters['work_type'])) {
            $title = $filters['work_type'] . ' Interior Designer';
        }
        if ($title === 'Interior Designer' && isset($filters['work_type'])) {
            $title = $filters['work_type'] . ' Interior Designer';
        }
        if ($title === '') {
            return null;
        }

        if (!empty($filters['city'])) {
            $title .= ' in ' . $filters['city'];
            $subtitle = 'See verified ' . strtolower((string)($filters['work_type'] ?? 'interior')) . ' professionals in ' . $filters['city'] . '.';
        } elseif (!empty($filters['work_type'])) {
            $subtitle = 'See professionals focused on ' . $filters['work_type'] . '.';
        }

        return [
            'slug' => $slug,
            'title' => $title,
            'subtitle' => $subtitle,
            'filters' => $filters,
        ];
    }

    public static function homepagePayload(): array
    {
        return [
            'hero_assets' => self::content('home.hero.assets', []),
            'services' => self::content('home.services.items', []),
            'testimonials' => self::content('home.testimonials.items', []),
            'brands' => self::content('home.brands.logos', []),
            'trust_points' => self::content('home.trust.items', []),
            'usp_points' => self::content('home.usp.items', []),
            'top_pros' => Database::query(
                "SELECT id, full_name, slug, role, specialization, profile_pic, rating, verification_status, verification_status_code, is_premium, listing_tier, accepting_leads, years_experience, city, primary_work_type, primary_work_area,
                    COALESCE(NULLIF(projects_delivered,0), (SELECT COUNT(*) FROM projects px WHERE px.pro_id=pros.id AND COALESCE(px.moderation_status, 'APPROVED') = 'APPROVED')) AS projects_delivered
                 FROM pros
                 WHERE is_active = 1
                 ORDER BY verification_status DESC, rating DESC, updated_at DESC
                 LIMIT 12"
            ),
            'featured_projects' => Database::query(
                "SELECT p.id, p.slug, p.project_name, p.project_description, p.total_cost, p.location, p.work_type, p.area_of_work, p.media_json,
                        pr.full_name AS pro_name, pr.slug AS pro_slug, pr.profile_pic AS pro_profile_pic, pr.city AS pro_city
                 FROM projects p
                 JOIN pros pr ON pr.id = p.pro_id
                 WHERE pr.is_active = 1 AND COALESCE(p.moderation_status, 'APPROVED') = 'APPROVED'
                 ORDER BY COALESCE(p.is_featured, 0) DESC, p.year_completed DESC, p.created_at DESC
                 LIMIT 8"
            ),
            'featured_properties' => self::listRealEstateProjects(['listing_for' => 'buy']),
            'property_filters' => self::realEstateFilterOptions(),
            'city_options' => self::cityOptions(),
            'requirement_options' => self::requirementOptions(),
        ];
    }

    public static function cityOptions(): array
    {
        $rows = Database::query("SELECT DISTINCT city FROM pros WHERE is_active = 1 AND city IS NOT NULL AND city <> '' ORDER BY city");
        $cities = array_map(static fn(array $row): string => (string)$row['city'], $rows);
        return array_values(array_unique(array_merge(['Gurgaon', 'Delhi', 'Noida'], $cities)));
    }

    public static function requirementOptions(): array
    {
        $rows = Database::query("SELECT DISTINCT work_type FROM projects WHERE work_type IS NOT NULL AND work_type <> '' ORDER BY work_type ASC");
        $options = array_map(static fn(array $r): string => (string)$r['work_type'], $rows);
        return $options ?: ['Kitchen', 'Wardrobe', 'Full Home'];
    }

    public static function projectBudgetRanges(): array
    {
        $fallback = [
            'Below ₹3 lakh',
            '₹3-5 lakh',
            '₹5-8 lakh',
            '₹8-12 lakh',
            '₹12-20 lakh',
            '₹20-35 lakh',
            '₹35-50 lakh',
            '₹50 lakh+',
            'Not Decided',
        ];

        try {
            $rows = Database::query('SELECT label FROM project_budget_ranges WHERE is_active=1 ORDER BY sort_order ASC, id ASC');
            $options = array_map(static fn(array $row): string => (string)$row['label'], $rows);
            return $options ?: $fallback;
        } catch (Throwable) {
            return $fallback;
        }
    }

    public static function societyOptions(string $query = '', ?string $city = null, int $limit = 250): array
    {
        $where = ['is_active = 1'];
        $params = [];
        $query = trim($query);
        $city = trim((string)$city);

        if ($query !== '') {
            $where[] = 'name LIKE ?';
            $params[] = '%' . $query . '%';
        }
        if ($city !== '') {
            $where[] = '(city = ? OR city = "")';
            $params[] = $city;
        }

        $limit = max(1, min(500, $limit));
        return Database::query(
            'SELECT id, name, city FROM societies WHERE ' . implode(' AND ', $where) . ' ORDER BY city = "" ASC, name ASC LIMIT ' . $limit,
            $params
        );
    }

    public static function proFilterOptions(): array
    {
        return [
            'roles' => array_map(static fn(array $r): string => (string)$r['role'], Database::query("SELECT DISTINCT role FROM pros WHERE is_active=1 ORDER BY role")),
            'cities' => self::cityOptions(),
            'work_types' => array_map(static fn(array $r): string => (string)$r['primary_work_type'], Database::query("SELECT DISTINCT primary_work_type FROM pros WHERE is_active=1 AND primary_work_type IS NOT NULL AND primary_work_type <> '' ORDER BY primary_work_type")),
            'work_areas' => array_map(static fn(array $r): string => (string)$r['primary_work_area'], Database::query("SELECT DISTINCT primary_work_area FROM pros WHERE is_active=1 AND primary_work_area IS NOT NULL AND primary_work_area <> '' ORDER BY primary_work_area")),
        ];
    }

    private static function attachPortfolioPreviews(array $pros): array
    {
        if ($pros === []) {
            return [];
        }

        $ids = array_map(static fn(array $pro): int => (int)$pro['id'], $pros);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = Database::query(
            "SELECT pro_id, slug, project_name, location, work_type, area_of_work, media_json
             FROM projects
	             WHERE pro_id IN ({$placeholders}) AND COALESCE(moderation_status, 'APPROVED') = 'APPROVED'
             ORDER BY year_completed DESC, id DESC",
            $ids
        );

        $grouped = [];
        foreach ($rows as $row) {
            $proId = (int)$row['pro_id'];
            $grouped[$proId] ??= [];
            if (count($grouped[$proId]) >= 4) {
                continue;
            }
            $row['media_json'] = self::parseJsonArray($row['media_json'] ?? '[]');
            $grouped[$proId][] = $row;
        }

        foreach ($pros as &$pro) {
            $pro['portfolio_previews'] = $grouped[(int)$pro['id']] ?? [];
        }
        unset($pro);

        return $pros;
    }

    public static function listPros(array $filters = []): array
    {
        $where = ['pros.is_active = 1'];
        $params = [];

        if (!empty($filters['role'])) {
            $where[] = 'pros.role = ?';
            $params[] = $filters['role'];
        }
        if (!empty($filters['city'])) {
            $where[] = 'pros.city = ?';
            $params[] = $filters['city'];
        }
        if (!empty($filters['work_type'])) {
            $where[] = 'pros.primary_work_type = ?';
            $params[] = $filters['work_type'];
        }
        if (!empty($filters['work_area'])) {
            $where[] = 'pros.primary_work_area = ?';
            $params[] = $filters['work_area'];
        }
        if (isset($filters['budget_min']) && $filters['budget_min'] !== '') {
            $where[] = 'pros.starting_price >= ?';
            $params[] = (float)$filters['budget_min'];
        }
        if (isset($filters['budget_max']) && $filters['budget_max'] !== '') {
            $where[] = 'pros.starting_price <= ?';
            $params[] = (float)$filters['budget_max'];
        }
        if (isset($filters['experience_min']) && $filters['experience_min'] !== '') {
            $where[] = 'pros.years_experience >= ?';
            $params[] = (int)$filters['experience_min'];
        }
        if (isset($filters['projects_min']) && $filters['projects_min'] !== '') {
            $where[] = 'COALESCE(NULLIF(pros.projects_delivered,0), prj.project_count, 0) >= ?';
            $params[] = (int)$filters['projects_min'];
        }
        if (isset($filters['rating_min']) && $filters['rating_min'] !== '') {
            $where[] = 'pros.rating >= ?';
            $params[] = (float)$filters['rating_min'];
        }

        $sortMap = [
            'rating_desc' => 'pros.rating DESC',
            'experience_desc' => 'pros.years_experience DESC',
            'projects_desc' => 'projects_delivered DESC',
            'price_asc' => 'pros.starting_price ASC',
            'price_desc' => 'pros.starting_price DESC',
            'newest' => 'pros.created_at DESC',
        ];
        $sortBy = (string)($filters['sort_by'] ?? 'rating_desc');
        $orderBy = $sortMap[$sortBy] ?? 'pros.verification_status DESC, pros.rating DESC, pros.updated_at DESC';

        $sql = "SELECT pros.id, pros.full_name, pros.slug, pros.role, pros.profile_description, pros.specialization, pros.profile_pic, pros.cover_photo,
                       pros.verification_status, pros.verification_status_code, pros.is_premium, pros.listing_tier, pros.accepting_leads,
                       pros.rating, pros.years_experience, pros.starting_price, pros.min_project_value, pros.max_project_value,
                       pros.city, pros.primary_work_type, pros.primary_work_area,
                       COALESCE(NULLIF(pros.projects_delivered,0), prj.project_count, 0) AS projects_delivered
                FROM pros
                LEFT JOIN (
	                  SELECT pro_id, COUNT(*) AS project_count FROM projects WHERE COALESCE(moderation_status, 'APPROVED') = 'APPROVED' GROUP BY pro_id
                ) prj ON prj.pro_id = pros.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY {$orderBy}";

        return self::attachPortfolioPreviews(Database::query($sql, $params));
    }

    public static function getProBySlug(string $slug): ?array
    {
	        $pro = Database::one('SELECT * FROM pros WHERE slug = ? AND is_active = 1', [$slug]);
        if (!$pro) {
            return null;
        }

        $pro['service_areas'] = self::parseJsonArray($pro['service_areas'] ?? '[]');
        $pro['offerings_json'] = self::parseJsonArray($pro['offerings_json'] ?? '[]');
        $pro['materials_json'] = self::parseJsonArray($pro['materials_json'] ?? '[]');
        $pro['design_styles_json'] = self::parseJsonArray($pro['design_styles_json'] ?? '[]');
        $pro['languages_json'] = self::parseJsonArray($pro['languages_json'] ?? '[]');
        $pro['certifications_json'] = self::parseJsonArray($pro['certifications_json'] ?? '[]');
        $pro['process_steps_json'] = self::parseTextLines($pro['process_steps_json'] ?? '[]');
        $pro['awards_json'] = self::parseTextLines($pro['awards_json'] ?? '[]');
        $pro['faq_json'] = self::parseTextLines($pro['faq_json'] ?? '[]');

        return $pro;
    }

    public static function proProfileData(int $proId): array
    {
        $projects = Database::query(
	            'SELECT id, slug, project_name, project_description, total_cost, bhk_type, year_completed, timeline_months, project_duration_label,
	                    location, work_type, area_of_work, materials_json, media_json, video_url, design_style, team_size, warranty_years,
	                    testimonial_client_name, testimonial_text, testimonial_rating, moderation_status, project_verification_status, is_featured
	             FROM projects WHERE pro_id = ? AND COALESCE(moderation_status, "APPROVED") = "APPROVED" ORDER BY COALESCE(is_featured,0) DESC, year_completed DESC, id DESC',
            [$proId]
        );

        foreach ($projects as &$project) {
            $project['media_json'] = self::parseJsonArray($project['media_json'] ?? '[]');
            $project['materials_json'] = self::parseJsonArray($project['materials_json'] ?? '[]');
        }
        unset($project);

        $reviews = Database::query(
            'SELECT id, client_name, rating, review_text, verified_purchase, work_type, area_of_work, materials_highlight, photos_json, created_at
             FROM reviews WHERE pro_id = ? ORDER BY created_at DESC',
            [$proId]
        );

        foreach ($reviews as &$review) {
            $review['photos_json'] = self::parseJsonArray($review['photos_json'] ?? '[]');
        }
        unset($review);

        return [
            'projects' => $projects,
            'reviews' => $reviews,
        ];
    }

    public static function pricingReviews(int $limit = 4): array
    {
        $limit = max(1, (int)$limit);
        $rows = Database::query(
            'SELECT r.id, r.client_name, r.rating, r.review_text, r.verified_purchase, r.work_type, r.area_of_work, r.materials_highlight, r.photos_json, r.created_at,
                    pr.full_name AS pro_name, pr.slug AS pro_slug, pr.city AS pro_city, pr.role AS pro_role, pr.profile_pic AS pro_profile_pic, pr.rating AS pro_rating
             FROM reviews r
             JOIN pros pr ON pr.id = r.pro_id
             WHERE pr.is_active = 1 AND pr.role IN ("Architect","Designer")
             ORDER BY pr.is_premium DESC, r.verified_purchase DESC, r.rating DESC, r.created_at DESC
             LIMIT ' . $limit
        );

        foreach ($rows as &$row) {
            $row['photos_json'] = self::parseJsonArray($row['photos_json'] ?? '[]');
        }
        unset($row);

        return $rows;
    }

    public static function getProjectBySlug(string $slug): ?array
    {
        $row = Database::one(
            'SELECT p.*, pr.id AS pro_id, pr.full_name AS pro_name, pr.slug AS pro_slug, pr.profile_pic AS pro_profile_pic,
                    pr.role AS pro_role, pr.city AS pro_city, pr.primary_work_type AS pro_work_type, pr.primary_work_area AS pro_work_area
             FROM projects p
             JOIN pros pr ON pr.id = p.pro_id
	             WHERE p.slug = ? AND pr.is_active = 1 AND COALESCE(p.moderation_status, "APPROVED") = "APPROVED"',
            [$slug]
        );

        if (!$row) {
            return null;
        }

        $row['media_json'] = self::parseJsonArray($row['media_json'] ?? '[]');
        $row['materials_json'] = self::parseJsonArray($row['materials_json'] ?? '[]');

        return $row;
    }

    public static function listOtherProjectsByPro(int $proId, string $excludeSlug): array
    {
        $rows = Database::query(
            'SELECT id, slug, project_name, total_cost, location, work_type, area_of_work, media_json
             FROM projects
	             WHERE pro_id = ? AND slug <> ? AND COALESCE(moderation_status, "APPROVED") = "APPROVED"
	             ORDER BY COALESCE(is_featured,0) DESC, year_completed DESC, id DESC
             LIMIT 6',
            [$proId, $excludeSlug]
        );

        foreach ($rows as &$row) {
            $row['media_json'] = self::parseJsonArray($row['media_json'] ?? '[]');
        }
        unset($row);

        return $rows;
    }

    public static function listProfessionalsForAdmin(): array
    {
        return Database::query(
            'SELECT pros.*, COALESCE(NULLIF(pros.projects_delivered,0), prj.project_count, 0) AS projects_delivered_computed
             FROM pros
             LEFT JOIN (SELECT pro_id, COUNT(*) AS project_count FROM projects GROUP BY pro_id) prj ON prj.pro_id = pros.id
             ORDER BY pros.is_premium DESC, pros.created_at DESC'
        );
    }

    public static function createProfessional(array $data): int
    {
        $verificationStatusCode = self::normalizeProfessionalVerificationStatus((string)($data['verification_status_code'] ?? ''));
        $listingTier = self::normalizeListingTier((string)($data['listing_tier'] ?? ''));
        $id = Database::exec(
            'INSERT INTO pros (
                full_name, slug, profile_pic, cover_photo, role, profile_description, specialization, primary_work_type, primary_work_area,
                verification_status, verification_status_code, is_premium, listing_tier, accepting_leads, suspension_reason, verification_notes,
                rating, years_experience, projects_delivered, starting_price, min_project_value, max_project_value,
                consultation_fee, city, office_address, phone, email, website_url, founded_year, team_size, office_hours, client_count,
                service_summary, service_areas, materials_json, design_styles_json, languages_json, certifications_json, process_steps_json,
                awards_json, faq_json, response_time_hours, bio, why_work_with_me, offerings_json, google_business_url, is_active
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $data['full_name'],
                $data['slug'],
                $data['profile_pic'] ?? null,
                $data['cover_photo'] ?? null,
                $data['role'] ?? 'Designer',
                $data['profile_description'] ?? null,
                $data['specialization'] ?? null,
                $data['primary_work_type'] ?? null,
                $data['primary_work_area'] ?? null,
                $verificationStatusCode === 'PROFESSIONAL_VERIFIED' ? 1 : (!empty($data['verification_status']) ? 1 : 0),
                $verificationStatusCode,
                in_array($listingTier, ['PAID', 'SPONSORED'], true) ? 1 : (!empty($data['is_premium']) ? 1 : 0),
                $listingTier,
                isset($data['accepting_leads']) ? (int)(bool)$data['accepting_leads'] : 1,
                $data['suspension_reason'] ?? null,
                $data['verification_notes'] ?? null,
                isset($data['rating']) ? (float)$data['rating'] : 0,
                isset($data['years_experience']) ? (int)$data['years_experience'] : 0,
                isset($data['projects_delivered']) ? (int)$data['projects_delivered'] : 0,
                isset($data['starting_price']) ? (float)$data['starting_price'] : 0,
                isset($data['min_project_value']) ? (float)$data['min_project_value'] : null,
                isset($data['max_project_value']) ? (float)$data['max_project_value'] : null,
                isset($data['consultation_fee']) ? (float)$data['consultation_fee'] : null,
                $data['city'] ?? null,
                $data['office_address'] ?? null,
                $data['phone'] ?? null,
                $data['email'] ?? null,
                $data['website_url'] ?? null,
                isset($data['founded_year']) && $data['founded_year'] !== '' ? (int)$data['founded_year'] : null,
                isset($data['team_size']) && $data['team_size'] !== '' ? (int)$data['team_size'] : null,
                $data['office_hours'] ?? null,
                isset($data['client_count']) && $data['client_count'] !== '' ? (int)$data['client_count'] : null,
                $data['service_summary'] ?? null,
                json_encode(self::parseJsonArray($data['service_areas'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['materials_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['design_styles_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['languages_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['certifications_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseTextLines($data['process_steps_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseTextLines($data['awards_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseTextLines($data['faq_json'] ?? []), JSON_UNESCAPED_UNICODE),
                isset($data['response_time_hours']) ? (int)$data['response_time_hours'] : null,
                $data['bio'] ?? null,
                $data['why_work_with_me'] ?? null,
                json_encode(self::parseJsonArray($data['offerings_json'] ?? []), JSON_UNESCAPED_UNICODE),
                $data['google_business_url'] ?? null,
                isset($data['is_active']) ? (int)(bool)$data['is_active'] : 1,
            ]
        );
        self::syncProfessionalUrlAlias($id);
        return $id;
    }

    public static function updateProfessional(int $id, array $data): void
    {
        $current = Database::one('SELECT verification_status_code FROM pros WHERE id=?', [$id]);
        $verificationStatusCode = self::normalizeProfessionalVerificationStatus((string)($data['verification_status_code'] ?? ''));
        $listingTier = self::normalizeListingTier((string)($data['listing_tier'] ?? ''));
        Database::exec(
            'UPDATE pros SET
                full_name=?, slug=?, profile_pic=?, cover_photo=?, role=?, profile_description=?, specialization=?, primary_work_type=?, primary_work_area=?,
                verification_status=?, verification_status_code=?, is_premium=?, listing_tier=?, accepting_leads=?, suspension_reason=?, verification_notes=?,
                rating=?, years_experience=?, projects_delivered=?, starting_price=?, min_project_value=?, max_project_value=?, consultation_fee=?, city=?,
                office_address=?, phone=?, email=?, website_url=?, founded_year=?, team_size=?, office_hours=?, client_count=?, service_summary=?,
                service_areas=?, materials_json=?, design_styles_json=?, languages_json=?, certifications_json=?, process_steps_json=?, awards_json=?, faq_json=?, response_time_hours=?,
                bio=?, why_work_with_me=?, offerings_json=?, google_business_url=?, is_active=?, updated_at=NOW()
             WHERE id=?',
            [
                $data['full_name'],
                $data['slug'],
                $data['profile_pic'] ?? null,
                $data['cover_photo'] ?? null,
                $data['role'] ?? 'Designer',
                $data['profile_description'] ?? null,
                $data['specialization'] ?? null,
                $data['primary_work_type'] ?? null,
                $data['primary_work_area'] ?? null,
                $verificationStatusCode === 'PROFESSIONAL_VERIFIED' ? 1 : (!empty($data['verification_status']) ? 1 : 0),
                $verificationStatusCode,
                in_array($listingTier, ['PAID', 'SPONSORED'], true) ? 1 : (!empty($data['is_premium']) ? 1 : 0),
                $listingTier,
                isset($data['accepting_leads']) ? (int)(bool)$data['accepting_leads'] : 1,
                $data['suspension_reason'] ?? null,
                $data['verification_notes'] ?? null,
                isset($data['rating']) ? (float)$data['rating'] : 0,
                isset($data['years_experience']) ? (int)$data['years_experience'] : 0,
                isset($data['projects_delivered']) ? (int)$data['projects_delivered'] : 0,
                isset($data['starting_price']) ? (float)$data['starting_price'] : 0,
                isset($data['min_project_value']) ? (float)$data['min_project_value'] : null,
                isset($data['max_project_value']) ? (float)$data['max_project_value'] : null,
                isset($data['consultation_fee']) ? (float)$data['consultation_fee'] : null,
                $data['city'] ?? null,
                $data['office_address'] ?? null,
                $data['phone'] ?? null,
                $data['email'] ?? null,
                $data['website_url'] ?? null,
                isset($data['founded_year']) && $data['founded_year'] !== '' ? (int)$data['founded_year'] : null,
                isset($data['team_size']) && $data['team_size'] !== '' ? (int)$data['team_size'] : null,
                $data['office_hours'] ?? null,
                isset($data['client_count']) && $data['client_count'] !== '' ? (int)$data['client_count'] : null,
                $data['service_summary'] ?? null,
                json_encode(self::parseJsonArray($data['service_areas'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['materials_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['design_styles_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['languages_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['certifications_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseTextLines($data['process_steps_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseTextLines($data['awards_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseTextLines($data['faq_json'] ?? []), JSON_UNESCAPED_UNICODE),
                isset($data['response_time_hours']) ? (int)$data['response_time_hours'] : null,
                $data['bio'] ?? null,
                $data['why_work_with_me'] ?? null,
                json_encode(self::parseJsonArray($data['offerings_json'] ?? []), JSON_UNESCAPED_UNICODE),
                $data['google_business_url'] ?? null,
                isset($data['is_active']) ? (int)(bool)$data['is_active'] : 1,
                $id,
            ]
        );
        if (($current['verification_status_code'] ?? null) !== $verificationStatusCode) {
            self::logProfessionalVerification($id, (string)($current['verification_status_code'] ?? ''), $verificationStatusCode, $listingTier, (string)($data['verification_notes'] ?? ''), isset($data['updated_by']) ? (int)$data['updated_by'] : null);
        }
        self::syncProfessionalUrlAlias($id);
    }

    private static function normalizeProfessionalVerificationStatus(string $status): string
    {
        $status = strtoupper(trim($status));
        $allowed = ['UNVERIFIED', 'PHONE_VERIFIED', 'IDENTITY_VERIFIED', 'BUSINESS_VERIFIED', 'PROFESSIONAL_VERIFIED'];
        return in_array($status, $allowed, true) ? $status : 'UNVERIFIED';
    }

    private static function normalizeListingTier(string $tier): string
    {
        $tier = strtoupper(trim($tier));
        return in_array($tier, ['FREE', 'PAID', 'SPONSORED'], true) ? $tier : 'FREE';
    }

    private static function logProfessionalVerification(int $proId, string $oldStatus, string $newStatus, string $listingTier, string $notes = '', ?int $userId = null): void
    {
        try {
            Database::exec(
                'INSERT INTO professional_verification_logs (pro_id, old_status, new_status, listing_tier, notes, performed_by) VALUES (?, ?, ?, ?, ?, ?)',
                [$proId, $oldStatus ?: null, $newStatus, $listingTier, $notes ?: null, $userId]
            );
        } catch (Throwable) {
            // Logging should not block profile saves during phased migrations.
        }
    }

    public static function syncProfessionalUrlAlias(int $id): void
    {
        $pro = Database::one('SELECT * FROM pros WHERE id=?', [$id]);
        if (!$pro) {
            return;
        }
        $path = '/professionals/' . (string)$pro['slug'];
        self::syncUrlAlias([
            'path' => $path,
            'page_type' => 'professional_detail',
            'entity_table' => 'pros',
            'entity_id' => (int)$pro['id'],
            'source' => 'professional',
            'meta_title' => (string)$pro['full_name'] . ' | HomeInteriors360',
            'meta_description' => (string)($pro['specialization'] ?: $pro['profile_description'] ?: $pro['bio']),
            'h1' => (string)$pro['full_name'],
            'content_html' => (string)($pro['bio'] ?: $pro['profile_description']),
            'image_url' => (string)($pro['profile_pic'] ?? ''),
            'canonical_url' => $path,
            'is_active' => !empty($pro['is_active']) ? 1 : 0,
        ]);
    }

    public static function deleteProfessional(int $id): void
    {
        Database::exec('DELETE FROM pros WHERE id = ?', [$id]);
    }

    public static function listPortfolioForAdmin(?int $proId = null): array
    {
        if ($proId) {
            return Database::query(
                'SELECT p.*, pr.full_name AS pro_name, pr.slug AS pro_slug FROM projects p JOIN pros pr ON pr.id=p.pro_id WHERE p.pro_id = ? ORDER BY p.created_at DESC',
                [$proId]
            );
        }
        return Database::query(
            'SELECT p.*, pr.full_name AS pro_name, pr.slug AS pro_slug FROM projects p JOIN pros pr ON pr.id=p.pro_id ORDER BY p.created_at DESC'
        );
    }

    public static function createPortfolio(array $data): int
    {
        $moderationStatus = self::normalizePortfolioModerationStatus((string)($data['moderation_status'] ?? 'APPROVED'));
        $verificationStatus = self::normalizePortfolioVerificationStatus((string)($data['project_verification_status'] ?? 'UNVERIFIED'));
        $id = Database::exec(
            'INSERT INTO projects (
                pro_id, slug, project_name, project_description, total_cost, bhk_type, year_completed, timeline_months, project_duration_label,
                location, work_type, area_of_work, materials_json, media_json, video_url, design_style, team_size, warranty_years,
                testimonial_client_name, testimonial_text, testimonial_rating, moderation_status, project_verification_status, is_featured, moderation_notes
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                (int)$data['pro_id'],
                $data['slug'],
                $data['project_name'],
                $data['project_description'] ?? null,
                isset($data['total_cost']) ? (float)$data['total_cost'] : 0,
                $data['bhk_type'] ?? '2BHK',
                $data['year_completed'] ?: null,
                isset($data['timeline_months']) ? (int)$data['timeline_months'] : 0,
                $data['project_duration_label'] ?? null,
                $data['location'] ?? null,
                $data['work_type'] ?? null,
                $data['area_of_work'] ?? null,
                json_encode(self::parseJsonArray($data['materials_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['media_json'] ?? []), JSON_UNESCAPED_UNICODE),
                $data['video_url'] ?? null,
                $data['design_style'] ?? null,
                isset($data['team_size']) ? (int)$data['team_size'] : null,
                isset($data['warranty_years']) ? (int)$data['warranty_years'] : null,
                $data['testimonial_client_name'] ?? null,
                $data['testimonial_text'] ?? null,
                isset($data['testimonial_rating']) ? (int)$data['testimonial_rating'] : null,
                $moderationStatus,
                $verificationStatus,
                !empty($data['is_featured']) ? 1 : 0,
                $data['moderation_notes'] ?? null,
            ]
        );
        self::syncPortfolioUrlAlias($id);
        return $id;
    }

    public static function updatePortfolio(int $id, array $data): void
    {
        $moderationStatus = self::normalizePortfolioModerationStatus((string)($data['moderation_status'] ?? 'APPROVED'));
        $verificationStatus = self::normalizePortfolioVerificationStatus((string)($data['project_verification_status'] ?? 'UNVERIFIED'));
        Database::exec(
            'UPDATE projects SET
                pro_id=?, slug=?, project_name=?, project_description=?, total_cost=?, bhk_type=?, year_completed=?, timeline_months=?, project_duration_label=?,
                location=?, work_type=?, area_of_work=?, materials_json=?, media_json=?, video_url=?, design_style=?, team_size=?, warranty_years=?,
                testimonial_client_name=?, testimonial_text=?, testimonial_rating=?, moderation_status=?, project_verification_status=?, is_featured=?, moderation_notes=?, updated_at=NOW()
             WHERE id=?',
            [
                (int)$data['pro_id'],
                $data['slug'],
                $data['project_name'],
                $data['project_description'] ?? null,
                isset($data['total_cost']) ? (float)$data['total_cost'] : 0,
                $data['bhk_type'] ?? '2BHK',
                $data['year_completed'] ?: null,
                isset($data['timeline_months']) ? (int)$data['timeline_months'] : 0,
                $data['project_duration_label'] ?? null,
                $data['location'] ?? null,
                $data['work_type'] ?? null,
                $data['area_of_work'] ?? null,
                json_encode(self::parseJsonArray($data['materials_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['media_json'] ?? []), JSON_UNESCAPED_UNICODE),
                $data['video_url'] ?? null,
                $data['design_style'] ?? null,
                isset($data['team_size']) ? (int)$data['team_size'] : null,
                isset($data['warranty_years']) ? (int)$data['warranty_years'] : null,
                $data['testimonial_client_name'] ?? null,
                $data['testimonial_text'] ?? null,
                isset($data['testimonial_rating']) ? (int)$data['testimonial_rating'] : null,
                $moderationStatus,
                $verificationStatus,
                !empty($data['is_featured']) ? 1 : 0,
                $data['moderation_notes'] ?? null,
                $id,
            ]
        );
        self::syncPortfolioUrlAlias($id);
    }

    private static function normalizePortfolioModerationStatus(string $status): string
    {
        $status = strtoupper(trim($status));
        return in_array($status, ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED'], true) ? $status : 'SUBMITTED';
    }

    private static function normalizePortfolioVerificationStatus(string $status): string
    {
        $status = strtoupper(trim($status));
        return in_array($status, ['UNVERIFIED', 'ADMIN_REVIEWED', 'CLIENT_CONFIRMED', 'PROFESSIONAL_VERIFIED'], true) ? $status : 'UNVERIFIED';
    }

    public static function syncPortfolioUrlAlias(int $id): void
    {
        $project = Database::one('SELECT * FROM projects WHERE id=?', [$id]);
        if (!$project) {
            return;
        }
        $media = self::parseJsonArray($project['media_json'] ?? []);
        $path = '/portfolio/' . (string)$project['slug'];
        self::syncUrlAlias([
            'path' => $path,
            'page_type' => 'portfolio_detail',
            'entity_table' => 'projects',
            'entity_id' => (int)$project['id'],
            'source' => 'portfolio',
            'meta_title' => (string)$project['project_name'] . ' | HomeInteriors360',
            'meta_description' => (string)$project['project_description'],
            'h1' => (string)$project['project_name'],
            'content_html' => (string)$project['project_description'],
            'image_url' => (string)($media[0] ?? ''),
            'canonical_url' => $path,
            'is_active' => ((string)($project['moderation_status'] ?? 'APPROVED') === 'APPROVED') ? 1 : 0,
        ]);
    }

    public static function deletePortfolio(int $id): void
    {
        Database::exec('DELETE FROM projects WHERE id=?', [$id]);
    }

    public static function professionalOptions(): array
    {
        return Database::query('SELECT id, full_name, slug FROM pros ORDER BY full_name ASC');
    }

    public static function listDesignerAccounts(): array
    {
        return Database::query(
            "SELECT u.id, u.username, u.email, u.role, u.pro_id, u.is_active, u.created_at, u.updated_at,
                    p.full_name AS professional_name, p.city AS professional_city, p.slug AS professional_slug
             FROM users u
             LEFT JOIN pros p ON p.id = u.pro_id
             WHERE u.role = 'designer'
             ORDER BY u.updated_at DESC, u.id DESC"
        );
    }

    public static function saveDesignerAccount(array $data, ?int $id = null): int
    {
        $username = trim((string)($data['username'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $password = (string)($data['password'] ?? '');
        $proId = (int)($data['pro_id'] ?? 0);
        $isActive = !empty($data['is_active']) ? 1 : 0;

        if ($username === '' || $proId <= 0) {
            throw new InvalidArgumentException('Username and professional profile are required.');
        }
        if (!Database::one('SELECT id FROM pros WHERE id=?', [$proId])) {
            throw new InvalidArgumentException('Selected professional profile was not found.');
        }
        $duplicate = Database::one(
            'SELECT id FROM users WHERE username=? AND (? IS NULL OR id<>?)',
            [$username, $id, $id]
        );
        if ($duplicate) {
            throw new InvalidArgumentException('Username already exists.');
        }

        if ($id !== null) {
            if (!Database::one("SELECT id FROM users WHERE id=? AND role='designer'", [$id])) {
                throw new InvalidArgumentException('Designer account not found.');
            }
            $sets = ['username=?', 'email=?', 'pro_id=?', 'is_active=?', 'role=\'designer\''];
            $values = [$username, $email !== '' ? $email : null, $proId, $isActive];
            if ($password !== '') {
                if (strlen($password) < 8) {
                    throw new InvalidArgumentException('Password must be at least 8 characters.');
                }
                $sets[] = 'password_hash=?';
                $values[] = password_hash($password, PASSWORD_DEFAULT);
            }
            $values[] = $id;
            Database::exec('UPDATE users SET ' . implode(', ', $sets) . ', updated_at=NOW() WHERE id=?', $values);
            return $id;
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters.');
        }
        return Database::exec(
            "INSERT INTO users (username, password_hash, email, role, pro_id, is_active) VALUES (?, ?, ?, 'designer', ?, ?)",
            [$username, password_hash($password, PASSWORD_DEFAULT), $email !== '' ? $email : null, $proId, $isActive]
        );
    }

    public static function registerInteriorDesigner(array $data): array
    {
        $name = trim((string)($data['full_name'] ?? $data['name'] ?? ''));
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $phone = preg_replace('/\D+/', '', (string)($data['phone'] ?? '')) ?? '';
        $password = (string)($data['password'] ?? '');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($phone) < 10 || strlen($password) < 8) {
            throw new InvalidArgumentException('Name, valid email, 10 digit mobile, and minimum 8 character password are required.');
        }
        if (Database::one('SELECT id FROM users WHERE email=? OR username=?', [$email, $email])) {
            throw new InvalidArgumentException('A designer login already exists for this email.');
        }

        $slug = self::uniqueProSlug(slugify((string)($data['slug'] ?? $name . '-' . substr($phone, -4))));
        $profileData = array_merge($data, [
            'full_name' => $name,
            'slug' => $slug,
            'role' => 'Designer',
            'phone' => $phone,
            'email' => $email,
            'verification_status' => 0,
            'verification_status_code' => 'UNVERIFIED',
            'is_premium' => 0,
            'listing_tier' => 'FREE',
            'accepting_leads' => 1,
            'is_active' => 0,
        ]);
        $proId = self::createProfessional($profileData);
        $userId = Database::exec(
            "INSERT INTO users (username, password_hash, email, role, pro_id, is_active) VALUES (?, ?, ?, 'designer', ?, 1)",
            [$email, password_hash($password, PASSWORD_DEFAULT), $email, $proId]
        );
        $buyer = self::createOrLoginDesignerBuyer([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
        ]);

        return [
            'pro_id' => $proId,
            'user_id' => $userId,
            'buyer' => $buyer,
            'user' => Database::one('SELECT id, username, email, role, pro_id FROM users WHERE id=?', [$userId]) ?: [],
        ];
    }

    public static function createDesignerPortfolio(array $user, array $data): int
    {
        if (empty($user['pro_id'])) {
            throw new InvalidArgumentException('Designer profile is required.');
        }
        $data['pro_id'] = (int)$user['pro_id'];
        if (empty($data['project_name'])) {
            throw new InvalidArgumentException('Project name is required.');
        }
        $data['slug'] = self::uniquePortfolioSlug(slugify((string)($data['slug'] ?? $data['project_name'] . '-' . substr((string)$user['pro_id'], -3))));
        $data['moderation_status'] = 'SUBMITTED';
        $data['project_verification_status'] = 'UNVERIFIED';
        $data['is_featured'] = 0;
        return self::createPortfolio($data);
    }

    public static function designerBuyerForUser(array $user): ?array
    {
        $email = strtolower(trim((string)($user['email'] ?? '')));
        if ($email === '') {
            return null;
        }
        return Database::one('SELECT * FROM lead_buyers WHERE email=? AND is_active=1 ORDER BY id DESC LIMIT 1', [$email]);
    }

    public static function createOrLoginDesignerBuyer(array $data): array
    {
        $phone = preg_replace('/\D+/', '', (string)($data['phone'] ?? '')) ?? '';
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $password = (string)($data['password'] ?? '');
        if (strlen($phone) < 10 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            throw new InvalidArgumentException('Valid email, 10 digit mobile and 6 character password are required.');
        }
        $existing = Database::one('SELECT * FROM lead_buyers WHERE (phone = ? OR email = ?) AND is_active = 1 ORDER BY id DESC LIMIT 1', [$phone, $email]);
        if ($existing) {
            return $existing;
        }
        $id = Database::exec(
            'INSERT INTO lead_buyers (name, email, phone, password_hash) VALUES (?, ?, ?, ?)',
            [
                trim((string)($data['name'] ?? 'Interior Designer')),
                $email,
                $phone,
                password_hash($password, PASSWORD_DEFAULT),
            ]
        );
        return Database::one('SELECT * FROM lead_buyers WHERE id = ?', [$id]) ?? [];
    }

    public static function calculateEstimate(string $floorPlan, string $packageTier, array $rooms): float
    {
        $planBase = [
            '1BHK' => 280000,
            '2BHK' => 420000,
            '3BHK' => 620000,
            '4BHK' => 850000,
        ];

        $packageMultiplier = [
            'Essential' => 1.0,
            'Premium' => 1.35,
            'Luxury' => 1.8,
        ];

        $roomCosts = [
            'Living Room' => 90000,
            'Kitchen' => 140000,
            'Master Bedroom' => 110000,
            'Bedroom 2' => 90000,
            'Bedroom 3' => 90000,
            'Bathroom' => 60000,
            'Pooja Unit' => 35000,
        ];

        $base = $planBase[$floorPlan] ?? $planBase['2BHK'];
        $mult = $packageMultiplier[$packageTier] ?? $packageMultiplier['Essential'];
        $roomsTotal = 0.0;

        foreach ($rooms as $room) {
            $roomsTotal += (float)($roomCosts[$room] ?? 50000);
        }

        return round(($base + $roomsTotal) * $mult, 2);
    }

    public static function createLead(array $data): int
    {
        return Database::exec(
            'INSERT INTO leads (name, phone, city, society_area, budget, requirement, pro_id, plan_type, source, status, floor_plan, package_tier, rooms_json, estimate)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['name'],
                $data['phone'],
                $data['city'],
                $data['society_area'] ?? null,
                $data['budget'] ?? null,
                $data['requirement'],
                isset($data['pro_id']) ? (int)$data['pro_id'] : null,
                $data['plan_type'] ?? null,
                $data['source'] ?? 'homepage',
                $data['status'] ?? 'new',
                $data['floor_plan'] ?? null,
                $data['package_tier'] ?? null,
                isset($data['rooms']) ? json_encode($data['rooms'], JSON_UNESCAPED_UNICODE) : null,
                isset($data['estimate']) ? (float)$data['estimate'] : null,
            ]
        );
    }

    public static function createProjectRequirement(array $data, array $files = []): array
    {
        $name = trim((string)($data['name'] ?? ''));
        $mobile = preg_replace('/\D+/', '', (string)($data['mobile'] ?? $data['phone'] ?? '')) ?? '';
        $email = trim((string)($data['email'] ?? ''));
        $city = self::normalizeNcrCity((string)($data['city'] ?? ''));
        $services = self::parseJsonArray($data['services'] ?? $data['requirement'] ?? []);
        if ($services === []) {
            $services = ['Free Design Consultation'];
        }
        if ($name === '' || strlen($mobile) < 10 || $city === '') {
            throw new InvalidArgumentException('Name, mobile and city are required.');
        }
        if (empty($data['contact_share_consent'])) {
            throw new InvalidArgumentException('Consent is required before sharing your requirement with professionals.');
        }

        $locality = trim((string)($data['locality'] ?? ''));
        $society = trim((string)($data['society_name'] ?? $data['society_area'] ?? ''));
        $propertyType = trim((string)($data['property_type'] ?? ''));
        $bhk = trim((string)($data['bhk'] ?? ''));
        $areaSqft = (int)($data['area_sqft'] ?? 0);
        $propertyStatus = trim((string)($data['property_status'] ?? ''));
        $timeline = trim((string)($data['timeline'] ?? ''));
        $budget = trim((string)($data['budget_range'] ?? $data['budget'] ?? ''));
        $quality = self::projectRequirementQuality([
            'mobile' => $mobile,
            'city' => $city,
            'services' => $services,
            'budget' => $budget,
            'property_type' => $propertyType,
            'timeline' => $timeline,
            'bhk' => $bhk,
            'area_sqft' => $areaSqft,
        ]);

        $legacyLeadId = self::createLead([
            'name' => $name,
            'phone' => $mobile,
            'city' => $city,
            'society_area' => trim($society !== '' ? $society : $locality),
            'budget' => $budget,
            'requirement' => implode(', ', $services),
            'source' => (string)($data['source'] ?? 'homepage'),
            'status' => 'new',
        ]);

        $requirementId = Database::exec(
            'INSERT INTO project_requirements (
                lead_id, name, mobile, email, city, locality, society_name, pincode, property_type, bhk, area_sqft,
                property_status, timeline, services_json, budget_range, style_preference, preferred_contact_method,
                preferred_consultation_time, notes, source, lead_quality, otp_verified, contact_share_consent,
                marketing_consent, status
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [
                $legacyLeadId,
                $name,
                $mobile,
                $email !== '' ? $email : null,
                $city,
                $locality !== '' ? $locality : null,
                $society !== '' ? $society : null,
                trim((string)($data['pincode'] ?? '')) ?: null,
                $propertyType !== '' ? $propertyType : null,
                $bhk !== '' ? $bhk : null,
                $areaSqft > 0 ? $areaSqft : null,
                $propertyStatus !== '' ? $propertyStatus : null,
                $timeline !== '' ? $timeline : null,
                json_encode($services, JSON_UNESCAPED_UNICODE),
                $budget !== '' ? $budget : null,
                trim((string)($data['style_preference'] ?? '')) ?: null,
                trim((string)($data['preferred_contact_method'] ?? '')) ?: null,
                trim((string)($data['preferred_consultation_time'] ?? '')) ?: null,
                trim((string)($data['notes'] ?? '')) ?: null,
                (string)($data['source'] ?? 'homepage'),
                $quality,
                0,
                !empty($data['contact_share_consent']) ? 1 : 0,
                !empty($data['marketing_consent']) || !empty($data['lead_consent']) ? 1 : 0,
                'verification_pending',
            ]
        );

        foreach ($files as $file) {
            if (empty($file['url'])) {
                continue;
            }
            Database::exec(
                'INSERT INTO project_requirement_files (requirement_id, file_type, file_url, original_name, mime_type, file_size)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $requirementId,
                    self::normalizeRequirementFileType((string)($file['file_type'] ?? 'other')),
                    (string)$file['url'],
                    $file['original_name'] ?? null,
                    $file['mime_type'] ?? null,
                    isset($file['file_size']) ? (int)$file['file_size'] : null,
                ]
            );
        }

        $otpRequestId = self::createProjectRequirementOtp($requirementId, $mobile);

	        return [
	            'id' => $requirementId,
	            'lead_id' => $legacyLeadId,
	            'lead_quality' => $quality,
	            'otp_request_id' => $otpRequestId,
	        ];
	    }

    public static function updateProjectRequirement(int $requirementId, array $data, array $files = []): array
    {
        $row = Database::one('SELECT * FROM project_requirements WHERE id=?', [$requirementId]);
        if (!$row) {
            throw new InvalidArgumentException('Project requirement was not found.');
        }
        $mobile = preg_replace('/\D+/', '', (string)($data['mobile'] ?? $data['phone'] ?? $row['mobile'] ?? '')) ?? '';
        if ($mobile === '' || substr($mobile, -10) !== substr((string)$row['mobile'], -10)) {
            throw new InvalidArgumentException('Mobile number does not match the saved consultation request.');
        }

        $city = self::normalizeNcrCity((string)($data['city'] ?? $row['city'] ?? ''));
        $services = self::parseJsonArray($data['services'] ?? $data['requirement'] ?? []);
        if ($services === []) {
            $services = self::parseJsonArray($row['services_json'] ?? []);
        }
        if ($services === []) {
            throw new InvalidArgumentException('Please select at least one service.');
        }

        $locality = trim((string)($data['locality'] ?? $row['locality'] ?? ''));
        $society = trim((string)($data['society_name'] ?? $data['society_area'] ?? $row['society_name'] ?? ''));
        $propertyType = trim((string)($data['property_type'] ?? $row['property_type'] ?? ''));
        $bhk = trim((string)($data['bhk'] ?? $row['bhk'] ?? ''));
        $areaSqft = (int)($data['area_sqft'] ?? $row['area_sqft'] ?? 0);
        $propertyStatus = trim((string)($data['property_status'] ?? $row['property_status'] ?? ''));
        $timeline = trim((string)($data['timeline'] ?? $row['timeline'] ?? ''));
        $budget = trim((string)($data['budget_range'] ?? $data['budget'] ?? $row['budget_range'] ?? ''));
        $quality = self::projectRequirementQuality([
            'mobile' => $mobile,
            'city' => $city,
            'services' => $services,
            'budget' => $budget,
            'property_type' => $propertyType,
            'timeline' => $timeline,
            'bhk' => $bhk,
            'area_sqft' => $areaSqft,
        ]);

        Database::exec(
            'UPDATE project_requirements SET
                email=?, city=?, locality=?, society_name=?, pincode=?, property_type=?, bhk=?, area_sqft=?,
                property_status=?, timeline=?, services_json=?, budget_range=?, style_preference=?, preferred_contact_method=?,
                preferred_consultation_time=?, notes=?, source=?, lead_quality=?, contact_share_consent=?, marketing_consent=?, status=?, updated_at=NOW()
             WHERE id=?',
            [
                trim((string)($data['email'] ?? $row['email'] ?? '')) ?: null,
                $city,
                $locality !== '' ? $locality : null,
                $society !== '' ? $society : null,
                trim((string)($data['pincode'] ?? $row['pincode'] ?? '')) ?: null,
                $propertyType !== '' ? $propertyType : null,
                $bhk !== '' ? $bhk : null,
                $areaSqft > 0 ? $areaSqft : null,
                $propertyStatus !== '' ? $propertyStatus : null,
                $timeline !== '' ? $timeline : null,
                json_encode($services, JSON_UNESCAPED_UNICODE),
                $budget !== '' ? $budget : null,
                trim((string)($data['style_preference'] ?? $row['style_preference'] ?? '')) ?: null,
                trim((string)($data['preferred_contact_method'] ?? $row['preferred_contact_method'] ?? '')) ?: null,
                trim((string)($data['preferred_consultation_time'] ?? $row['preferred_consultation_time'] ?? '')) ?: null,
                trim((string)($data['notes'] ?? $row['notes'] ?? '')) ?: null,
                (string)($data['source'] ?? $row['source'] ?? 'homepage'),
                $quality,
                !empty($data['contact_share_consent']) || !empty($row['contact_share_consent']) ? 1 : 0,
                !empty($data['marketing_consent']) || !empty($data['lead_consent']) || !empty($row['marketing_consent']) ? 1 : 0,
                'verification_pending',
                $requirementId,
            ]
        );

        foreach ($files as $file) {
            if (empty($file['url'])) {
                continue;
            }
            Database::exec(
                'INSERT INTO project_requirement_files (requirement_id, file_type, file_url, original_name, mime_type, file_size)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $requirementId,
                    self::normalizeRequirementFileType((string)($file['file_type'] ?? 'other')),
                    (string)$file['url'],
                    $file['original_name'] ?? null,
                    $file['mime_type'] ?? null,
                    isset($file['file_size']) ? (int)$file['file_size'] : null,
                ]
            );
        }

        if (!empty($row['lead_id'])) {
            Database::exec(
                'UPDATE leads SET city=?, society_area=?, budget=?, requirement=?, source=?, status="new", updated_at=NOW() WHERE id=?',
                [
                    $city,
                    trim($society !== '' ? $society : $locality),
                    $budget !== '' ? $budget : null,
                    implode(', ', $services),
                    (string)($data['source'] ?? $row['source'] ?? 'homepage'),
                    (int)$row['lead_id'],
                ]
            );
        }

        return [
            'id' => $requirementId,
            'lead_id' => (int)($row['lead_id'] ?? 0),
            'lead_quality' => $quality,
        ];
    }

    public static function verifyProjectRequirementOtp(int $requirementId, string $otp): void
    {
        $otp = preg_replace('/\D+/', '', $otp) ?? '';
        if ($requirementId <= 0 || strlen($otp) !== 6) {
            throw new InvalidArgumentException('Valid requirement and 6 digit OTP are required.');
        }
        $row = Database::one(
            'SELECT * FROM project_requirement_otps WHERE requirement_id=? AND verified_at IS NULL ORDER BY id DESC LIMIT 1',
            [$requirementId]
        );
        if (!$row) {
            throw new InvalidArgumentException('OTP request not found.');
        }
        if ((int)$row['attempts'] >= 5) {
            throw new InvalidArgumentException('Too many OTP attempts.');
        }
        if (strtotime((string)$row['expires_at']) < time()) {
            throw new InvalidArgumentException('OTP has expired.');
        }
        if (!password_verify($otp, (string)$row['otp_hash'])) {
            Database::exec('UPDATE project_requirement_otps SET attempts=attempts+1, updated_at=NOW() WHERE id=?', [(int)$row['id']]);
            throw new InvalidArgumentException('Invalid OTP.');
        }
        Database::exec('UPDATE project_requirement_otps SET verified_at=NOW(), updated_at=NOW() WHERE id=?', [(int)$row['id']]);
        Database::exec(
            'UPDATE project_requirements SET otp_verified=1, lead_quality=IF(lead_quality="basic","verified",lead_quality), status="verified", updated_at=NOW() WHERE id=?',
            [$requirementId]
        );
    }

    private static function createProjectRequirementOtp(int $requirementId, string $mobile): int
    {
        $otp = (string)random_int(100000, 999999);
        return Database::exec(
            'INSERT INTO project_requirement_otps (requirement_id, mobile, otp_hash, expires_at) VALUES (?, ?, ?, ?)',
            [$requirementId, $mobile, password_hash($otp, PASSWORD_DEFAULT), date('Y-m-d H:i:s', time() + 10 * 60)]
        );
    }

    private static function projectRequirementQuality(array $data): string
    {
        $hasVerifiedBasics = !empty($data['mobile']) && !empty($data['city']) && !empty($data['services']) && !empty($data['budget']);
        $hasMeaningfulProject = !empty($data['property_type']) && !empty($data['timeline']) && (!empty($data['bhk']) || (int)($data['area_sqft'] ?? 0) > 0);
        return $hasVerifiedBasics && $hasMeaningfulProject ? 'qualified' : 'basic';
    }

    private static function normalizeRequirementFileType(string $type): string
    {
        $type = strtolower(trim(str_replace([' ', '-'], '_', $type)));
        return in_array($type, ['floor_plan', 'site_photo', 'inspiration_image', 'existing_quotation', 'other'], true) ? $type : 'other';
    }

    private static function normalizeNcrCity(string $city): string
    {
        $city = trim($city);
        if ($city === '') {
            return '';
        }
        $normalized = strtolower(str_replace([' ', '-'], '', $city));
        if (in_array($normalized, ['gurugram', 'gurgaon'], true)) {
            return 'Gurgaon';
        }
        return $city;
    }

    public static function aiVisualizerStyles(): array
    {
        try {
            return Database::query('SELECT style_key, name, description, materials_palette, signature_elements FROM ai_visualizer_styles WHERE is_active=1 ORDER BY sort_order ASC, name ASC');
        } catch (Throwable) {
            return self::defaultAiVisualizerStyles();
        }
    }

    public static function createAiVisualizerRender(array $data, string $originalImageUrl): array
    {
        $name = trim((string)($data['name'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string)($data['phone'] ?? '')) ?? '';
        $city = trim((string)($data['city'] ?? ''));
        $roomType = trim((string)($data['room_type'] ?? ''));
        $styleKey = trim((string)($data['style'] ?? $data['style_key'] ?? ''));
        $budgetTier = trim((string)($data['budget_tier'] ?? 'mid-range'));
        if ($name === '' || strlen($phone) < 10 || $city === '' || $roomType === '' || $styleKey === '' || empty($data['consent'])) {
            throw new InvalidArgumentException('Name, phone, city, room type, style, and consent are required.');
        }
        if (!in_array($budgetTier, ['economy', 'mid-range', 'premium', 'luxury'], true)) {
            $budgetTier = 'mid-range';
        }

        $prompt = self::buildAiVisualizerPrompt([
            'room_type' => $roomType,
            'style' => $styleKey,
            'budget_tier' => $budgetTier,
            'detected_elements' => trim((string)($data['detected_elements'] ?? 'uploaded room photo with visible existing layout, furniture, walls, windows, doors, ceiling, flooring, and camera angle')),
            'freeform_notes' => trim((string)($data['freeform_notes'] ?? '')),
        ]);
        $negativePrompt = self::aiVisualizerNegativePrompt();
        $renderedImageUrl = null;
        $providerResponse = null;
        $status = 'prompt_ready';

        try {
            $provider = self::callAiVisualizerProvider($originalImageUrl, $prompt, $negativePrompt, $data);
            if (!empty($provider['rendered_image_url'])) {
                $renderedImageUrl = (string)$provider['rendered_image_url'];
                $status = 'generated';
            }
            $providerResponse = json_encode($provider, JSON_UNESCAPED_UNICODE);
        } catch (Throwable $e) {
            $status = 'failed';
            $providerResponse = json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }

        $requirement = 'AI Room Visualizer: ' . $roomType . ' · ' . $styleKey . ' · ' . $budgetTier;
        if (!empty($data['freeform_notes'])) {
            $requirement .= ' · Notes: ' . trim((string)$data['freeform_notes']);
        }
        $leadId = self::createLead([
            'name' => $name,
            'phone' => $phone,
            'city' => $city,
            'society_area' => trim((string)($data['locality'] ?? '')),
            'budget' => $budgetTier,
            'requirement' => $requirement,
            'plan_type' => 'AI Room Visualizer',
            'source' => 'ai_room_visualizer',
            'rooms' => [$roomType],
        ]);

        $renderId = Database::exec(
            'INSERT INTO ai_visualizer_renders (lead_id, name, phone, email, city, locality, room_type, style_key, budget_tier, detected_elements, freeform_notes, original_image_url, rendered_image_url, prompt, negative_prompt, generation_status, provider_response)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $leadId,
                $name,
                $phone,
                trim((string)($data['email'] ?? '')) ?: null,
                $city,
                trim((string)($data['locality'] ?? '')) ?: null,
                $roomType,
                $styleKey,
                $budgetTier,
                trim((string)($data['detected_elements'] ?? '')) ?: null,
                trim((string)($data['freeform_notes'] ?? '')) ?: null,
                $originalImageUrl,
                $renderedImageUrl,
                $prompt,
                $negativePrompt,
                $status,
                $providerResponse,
            ]
        );

        return [
            'id' => $renderId,
            'lead_id' => $leadId,
            'original_image_url' => $originalImageUrl,
            'rendered_image_url' => $renderedImageUrl,
            'prompt' => $prompt,
            'negative_prompt' => $negativePrompt,
            'generation_status' => $status,
        ];
    }

    public static function buildAiVisualizerPrompt(array $input): string
    {
        $style = self::aiVisualizerStyleByKey((string)($input['style'] ?? 'modern'));
        $budget = [
            'economy' => 'laminate finishes, ready-made furniture, cost-efficient materials, clean simple execution',
            'mid-range' => 'engineered wood finishes, semi-custom furniture, quality modular fittings',
            'premium' => 'solid wood and veneer finishes, custom-built furniture, designer lighting fixtures',
            'luxury' => 'natural stone and premium veneer finishes, bespoke furniture, statement designer fixtures, high-end imported materials',
        ][(string)($input['budget_tier'] ?? 'mid-range')] ?? 'engineered wood finishes, semi-custom furniture, quality modular fittings';
        $roomType = strtolower(trim((string)($input['room_type'] ?? 'living room')));
        $roomFocus = self::aiVisualizerRoomFocus($roomType);
        $notes = trim((string)($input['freeform_notes'] ?? ''));
        $detected = trim((string)($input['detected_elements'] ?? 'existing room layout'));
        $prompt = 'Redesign this ' . $roomType . ' in a ' . strtolower((string)$style['name']) . ' style while preserving the exact architecture, wall positions, windows, doors, ceiling height, room dimensions, and camera angle from the source photo. Keep these existing layout facts intact: ' . $detected . '. Use ' . $style['materials_palette'] . ', with ' . $style['signature_elements'] . '. Apply ' . $budget . '. ' . $roomFocus . '.';
        if ($notes !== '') {
            $prompt .= ' Honor this homeowner preference where possible: ' . $notes . '.';
        }
        $prompt .= ' photorealistic interior photography, natural daylight, same camera angle and room dimensions as source image, ultra-detailed textures, 8k, architectural digest style';
        return $prompt;
    }

    public static function aiVisualizerNegativePrompt(): string
    {
        return 'distorted room proportions, changed wall positions, moved windows or doors, warped ceiling height, extra rooms, people, text, watermark, logo, blurry, low resolution, unrealistic lighting, cartoonish, over-saturated colors, furniture floating or clipping through walls';
    }

    private static function aiVisualizerStyleByKey(string $key): array
    {
        foreach (self::aiVisualizerStyles() as $style) {
            if ((string)$style['style_key'] === $key) {
                return $style;
            }
        }
        return self::defaultAiVisualizerStyles()[0];
    }

    private static function aiVisualizerRoomFocus(string $roomType): string
    {
        return match ($roomType) {
            'bedroom' => 'focus on bed placement, wardrobe design, headboard wall, ambient lighting',
            'kitchen' => 'focus on cabinetry, countertop material, backsplash, hardware, under-cabinet lighting and preserve plumbing and appliance locations',
            'bathroom' => 'focus on tiling, vanity design, fixtures, mirror, lighting and preserve plumbing fixture locations',
            'full home', 'open layout', 'full home / open layout' => 'apply a consistent material and color language across all visible zones while preserving each zone function',
            default => 'focus on seating arrangement, TV/media wall, lighting layers, rug and coffee table',
        };
    }

    private static function callAiVisualizerProvider(string $originalImageUrl, string $prompt, string $negativePrompt, array $data): array
    {
        $webhook = trim((string)(getenv('AI_ROOM_VISUALIZER_WEBHOOK_URL') ?: ''));
        if ($webhook !== '') {
            return self::callAiVisualizerWebhook($webhook, $originalImageUrl, $prompt, $negativePrompt, $data);
        }

        $openAiKey = trim((string)(getenv('OPENAI_API_KEY') ?: ''));
        if ($openAiKey !== '') {
            return self::callOpenAiVisualizerProvider($openAiKey, $originalImageUrl, $prompt, $negativePrompt, $data);
        }

        return ['configured' => false, 'message' => 'Image generation provider is not configured yet.'];
    }

    private static function callAiVisualizerWebhook(string $webhook, string $originalImageUrl, string $prompt, string $negativePrompt, array $data): array
    {
        $payload = [
            'original_image_url' => absoluteUrl($originalImageUrl),
            'prompt' => $prompt,
            'negative_prompt' => $negativePrompt,
            'room_type' => $data['room_type'] ?? '',
            'style' => $data['style'] ?? '',
            'budget_tier' => $data['budget_tier'] ?? '',
            'mode' => 'image-to-image',
            'denoise_strength' => 0.58,
            'variations' => 2,
        ];
        $ch = curl_init($webhook);
        if (!$ch) {
            throw new RuntimeException('Unable to initialize visualizer provider request.');
        }
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 45,
        ]);
        $response = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($response === false || $status < 200 || $status >= 300) {
            throw new RuntimeException('Visualizer provider failed' . ($error ? ': ' . $error : '.'));
        }
        $decoded = json_decode((string)$response, true);
        return is_array($decoded) ? $decoded : ['raw_response' => (string)$response];
    }

    private static function callOpenAiVisualizerProvider(string $apiKey, string $originalImageUrl, string $prompt, string $negativePrompt, array $data): array
    {
        $relativePath = parse_url($originalImageUrl, PHP_URL_PATH) ?: $originalImageUrl;
        $sourcePath = rtrim(appPublicRoot(), '/') . '/' . ltrim($relativePath, '/');
        if (!is_file($sourcePath)) {
            throw new RuntimeException('Uploaded room image was not found for AI rendering.');
        }

        $mime = function_exists('mime_content_type') ? (string)mime_content_type($sourcePath) : 'image/jpeg';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            $mime = 'image/jpeg';
        }

        $model = trim((string)(getenv('OPENAI_IMAGE_MODEL') ?: 'gpt-image-2'));
        $generationPrompt = $prompt . '. Avoid: ' . $negativePrompt . '.';
        $postFields = [
            'model' => $model,
            'prompt' => $generationPrompt,
            'quality' => trim((string)(getenv('OPENAI_IMAGE_QUALITY') ?: 'medium')),
            'size' => trim((string)(getenv('OPENAI_IMAGE_SIZE') ?: '1024x1024')),
            'image[]' => new CURLFile($sourcePath, $mime, basename($sourcePath)),
        ];

        $ch = curl_init('https://api.openai.com/v1/images/edits');
        if (!$ch) {
            throw new RuntimeException('Unable to initialize OpenAI visualizer request.');
        }
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $apiKey],
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_TIMEOUT => 120,
        ]);
        $response = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $status < 200 || $status >= 300) {
            $decodedError = json_decode((string)$response, true);
            $message = $decodedError['error']['message'] ?? $error ?: 'OpenAI image generation failed.';
            throw new RuntimeException((string)$message);
        }

        $decoded = json_decode((string)$response, true);
        $base64 = $decoded['data'][0]['b64_json'] ?? null;
        if (!is_string($base64) || $base64 === '') {
            return is_array($decoded) ? $decoded : ['raw_response' => (string)$response];
        }

        $imageBytes = base64_decode($base64, true);
        if ($imageBytes === false) {
            throw new RuntimeException('OpenAI returned an unreadable image.');
        }

        $dir = ensureUploadDir('ai-room-visualizer/renders');
        $filename = 'render-' . bin2hex(random_bytes(8)) . '.png';
        $target = rtrim($dir, '/') . '/' . $filename;
        if (file_put_contents($target, $imageBytes) === false) {
            throw new RuntimeException('Unable to save generated room render.');
        }

        $safeResponse = is_array($decoded) ? $decoded : [];
        unset($safeResponse['data'][0]['b64_json']);

        return [
            'configured' => true,
            'provider' => 'openai',
            'model' => $model,
            'rendered_image_url' => publicUploadPath('ai-room-visualizer/renders', $filename),
            'response' => $safeResponse,
        ];
    }

    private static function defaultAiVisualizerStyles(): array
    {
        return [
            ['style_key' => 'modern', 'name' => 'Modern', 'description' => 'Clean geometry, low visual clutter', 'materials_palette' => 'matte white/grey walls, walnut wood, black metal accents, glass', 'signature_elements' => 'handleless cabinetry, recessed lighting, statement pendant light'],
            ['style_key' => 'minimalist', 'name' => 'Minimalist', 'description' => 'Maximum restraint, function-first', 'materials_palette' => 'whites, off-whites, light oak, single accent color', 'signature_elements' => 'hidden storage, no ornamentation, negative space'],
            ['style_key' => 'contemporary_luxury', 'name' => 'Contemporary Luxury', 'description' => 'Elevated, curated, statement pieces', 'materials_palette' => 'marble, brushed gold/brass, deep neutrals, velvet', 'signature_elements' => 'statement chandelier, large-format tiles, curated art wall'],
        ];
    }

    public static function listLeads(array $filters = []): array
    {
        $where = [];
        $params = [];
        if (!empty($filters['designer_id'])) {
            $where[] = 'l.pro_id = ?';
            $params[] = (int)$filters['designer_id'];
        }
        $sql = 'SELECT l.*, p.full_name AS pro_name
                FROM leads l
                LEFT JOIN pros p ON p.id = l.pro_id';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        return Database::query($sql . ' ORDER BY l.created_at DESC LIMIT 500', $params);
    }

    public static function updateLeadStatus(int $leadId, string $status): void
    {
        Database::exec('UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ?', [$status, $leadId]);
    }

    public static function adminCounts(): array
    {
        return [
            'pros' => (int)(Database::one('SELECT COUNT(*) AS c FROM pros WHERE is_active = 1')['c'] ?? 0),
            'verified_pros' => (int)(Database::one('SELECT COUNT(*) AS c FROM pros WHERE is_active = 1 AND verification_status = 1')['c'] ?? 0),
            'leads' => (int)(Database::one('SELECT COUNT(*) AS c FROM leads')['c'] ?? 0),
            'new_leads' => (int)(Database::one("SELECT COUNT(*) AS c FROM leads WHERE status = 'new'")['c'] ?? 0),
            'property_projects' => (int)(Database::one('SELECT COUNT(*) AS c FROM real_estate_projects WHERE is_active = 1')['c'] ?? 0),
            'property_enquiries' => (int)(Database::one("SELECT COUNT(*) AS c FROM property_enquiries WHERE status = 'new'")['c'] ?? 0),
        ];
    }

    public static function setProVerification(int $proId, bool $isVerified): void
    {
        Database::exec(
            'UPDATE pros SET verification_status = ?, verification_status_code = ?, updated_at = NOW() WHERE id = ?',
            [$isVerified ? 1 : 0, $isVerified ? 'PROFESSIONAL_VERIFIED' : 'UNVERIFIED', $proId]
        );
    }

    public static function contentList(): array
    {
        return Database::query('SELECT id, key_name, content_value, content_type, updated_at FROM site_content ORDER BY key_name');
    }

    public static function upsertContent(string $keyName, string $contentValue, string $contentType = 'text'): void
    {
        Database::exec(
            'INSERT INTO site_content (key_name, content_value, content_type)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE content_value = VALUES(content_value), content_type = VALUES(content_type), updated_at = NOW()',
            [$keyName, $contentValue, $contentType]
        );
        self::$contentCache = null;
    }

    public static function leadMarketplaceCounts(string $dateFilter = 'all_time', ?string $startDate = null, ?string $endDate = null): array
    {
        $leads = self::leadRowsForDateFilter($dateFilter, $startDate, $endDate);
        $definitions = self::leadFilterDefinitions($leads);
        $cards = [];
        foreach ($definitions as $definition) {
            $matching = array_values(array_filter($leads, static fn(array $lead): bool => self::leadMatchesCriteria($lead, $definition['criteria'])));
            $count = count($matching);
            if ($count <= 0) {
                continue;
            }
            $price = self::leadPriceForCount($count, false);
            $sampleLeads = array_map(
                static fn(array $lead): array => self::publicSampleLead($lead),
                self::sampleMatchingLeads($matching, 3)
            );
            $cards[] = [
                'id' => hash('sha256', $definition['name'] . json_encode($definition['criteria']) . $dateFilter . $startDate . $endDate),
                'section' => $definition['section'],
                'filter_name' => $definition['name'],
                'criteria' => $definition['criteria'],
                'date_filter' => $dateFilter,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'lead_count' => $count,
                'price_total' => $price['total'],
                'pricing' => $price,
                'sample_leads' => $sampleLeads,
            ];
        }
        return $cards;
    }

    private static function sampleMatchingLeads(array $leads, int $limit = 3): array
    {
        $leads = array_values($leads);
        if (count($leads) <= $limit) {
            return $leads;
        }
        shuffle($leads);
        return array_slice($leads, 0, $limit);
    }

    private static function publicSampleLead(array $lead): array
    {
        return [
            'name' => (string)($lead['name'] ?? ''),
            'phone' => self::maskPhone((string)($lead['phone'] ?? '')),
            'city' => (string)($lead['city'] ?? ''),
            'society_area' => (string)($lead['society_area'] ?? ''),
            'requirement' => (string)($lead['requirement'] ?? ''),
            'budget' => (string)($lead['budget'] ?? ''),
        ];
    }

    private static function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\\D+/', '', $phone) ?? '';
        $length = strlen($digits);
        if ($length <= 4) {
            return $length > 0 ? str_repeat('*', $length) : 'Masked';
        }
        $prefix = substr($digits, 0, min(2, $length));
        $suffix = substr($digits, -2);
        return $prefix . str_repeat('*', max(4, $length - 4)) . $suffix;
    }

    public static function leadPriceForCount(int $count, bool $firstTimeEligible = true): array
    {
        $free = $firstTimeEligible ? min($count, 10) : 0;
        $payable = max($count - $free, 0);
        $first = min($payable, 100);
        $second = min(max($payable - 100, 0), 900);
        $third = max($payable - 1000, 0);
        $lines = [];
        if ($free > 0) {
            $lines[] = ['label' => 'First-time offer', 'count' => $free, 'rate' => 0, 'amount' => 0];
        }
        if ($first > 0) {
            $lines[] = ['label' => 'First 100 leads', 'count' => $first, 'rate' => 100, 'amount' => $first * 100];
        }
        if ($second > 0) {
            $lines[] = ['label' => '101 to 1000 leads', 'count' => $second, 'rate' => 80, 'amount' => $second * 80];
        }
        if ($third > 0) {
            $lines[] = ['label' => 'Above 1000 leads', 'count' => $third, 'rate' => 60, 'amount' => $third * 60];
        }
        return [
            'total' => array_sum(array_column($lines, 'amount')),
            'free_leads' => $free,
            'first_time_eligible' => $firstTimeEligible,
            'lines' => $lines,
        ];
    }

    public static function normalizeLeadCartItem(array $item, bool $firstTimeEligible = true): array
    {
        $criteria = is_array($item['criteria'] ?? null) ? $item['criteria'] : [];
        $dateFilter = (string)($item['date_filter'] ?? 'all_time');
        $startDate = isset($item['start_date']) ? (string)$item['start_date'] : null;
        $endDate = isset($item['end_date']) ? (string)$item['end_date'] : null;
        $availableCount = self::countLeadsForCriteria($criteria, $dateFilter, $startDate, $endDate);
        $requestedCount = isset($item['selected_count']) ? (int)$item['selected_count'] : (isset($item['lead_count']) ? (int)$item['lead_count'] : $availableCount);
        $count = $availableCount > 0 ? max(1, min($requestedCount, $availableCount)) : 0;
        $price = self::leadPriceForCount($count, $firstTimeEligible);
        $packageId = hash('sha256', json_encode([$criteria, $dateFilter, $startDate, $endDate]));
        return [
            'id' => hash('sha256', json_encode([$criteria, $dateFilter, $startDate, $endDate, $count])),
            'package_id' => $packageId,
            'filter_name' => (string)($item['filter_name'] ?? self::criteriaLabel($criteria)),
            'criteria' => $criteria,
            'date_filter' => $dateFilter,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'lead_count' => $count,
            'available_lead_count' => $availableCount,
            'selected_count' => $count,
            'price_total' => $price['total'],
            'pricing' => $price,
        ];
    }

    public static function leadCartSummary(array $cart, ?string $couponCode = null, bool $firstTimeEligible = true): array
    {
        $items = [];
        $seenLeadKeys = [];
        $uniqueLeadCount = 0;
        foreach ($cart as $cartItem) {
            $normalized = self::normalizeLeadCartItem(is_array($cartItem) ? $cartItem : [], false);
            $requestedCount = (int)$normalized['selected_count'];
            $matching = self::matchingLeadRows(
                $normalized['criteria'],
                $normalized['date_filter'],
                $normalized['start_date'],
                $normalized['end_date']
            );
            $leadIds = [];
            foreach (array_slice($matching, 0, $requestedCount) as $lead) {
                $uniqueKey = self::leadUniqueKey($lead);
                if (isset($seenLeadKeys[$uniqueKey])) {
                    continue;
                }
                $seenLeadKeys[$uniqueKey] = true;
                $leadIds[] = (int)$lead['id'];
            }
            $previousTotal = self::leadPriceForCount($uniqueLeadCount, $firstTimeEligible)['total'];
            $uniqueLeadCount += count($leadIds);
            $runningTotal = self::leadPriceForCount($uniqueLeadCount, $firstTimeEligible)['total'];
            $itemAmount = max(0, $runningTotal - $previousTotal);
            $normalized['requested_count'] = $requestedCount;
            $normalized['lead_count'] = count($leadIds);
            $normalized['unique_lead_count'] = count($leadIds);
            $normalized['duplicate_count'] = max(0, $requestedCount - count($leadIds));
            $normalized['lead_ids'] = $leadIds;
            $normalized['price_total'] = $itemAmount;
            $normalized['pricing'] = [
                'total' => $itemAmount,
                'lines' => [[
                    'label' => 'Unique leads',
                    'count' => count($leadIds),
                    'rate' => count($leadIds) > 0 ? round($itemAmount / count($leadIds), 2) : 0,
                    'amount' => $itemAmount,
                ]],
            ];
            $items[] = $normalized;
        }
        $cartPricing = self::leadPriceForCount($uniqueLeadCount, $firstTimeEligible);
        $subtotal = (float)$cartPricing['total'];
        $leadCount = $uniqueLeadCount;
        $coupon = null;
        $discount = 0.0;
        if ($couponCode) {
            $coupon = self::validateLeadCoupon($couponCode, $subtotal, $leadCount);
            $discount = (float)$coupon['discount_amount'];
        }
        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'lead_count' => $leadCount,
            'unique_lead_count' => $uniqueLeadCount,
            'pricing' => $cartPricing,
            'coupon' => $coupon,
            'discount_amount' => $discount,
            'grand_total' => max($subtotal - $discount, 0),
            'first_time_eligible' => $firstTimeEligible,
        ];
    }

    private static function matchingLeadRows(array $criteria, string $dateFilter, ?string $startDate = null, ?string $endDate = null): array
    {
        $leads = self::leadRowsForDateFilter($dateFilter, $startDate, $endDate);
        return array_values(array_filter($leads, static fn(array $lead): bool => self::leadMatchesCriteria($lead, $criteria)));
    }

    private static function leadUniqueKey(array $lead): string
    {
        $phone = preg_replace('/\\D+/', '', (string)($lead['phone'] ?? '')) ?? '';
        return $phone !== '' ? 'phone:' . $phone : 'id:' . (int)($lead['id'] ?? 0);
    }

    public static function buyerFirstTimeLeadOfferEligible(int $buyerId): bool
    {
        $row = Database::one('SELECT COUNT(*) AS c FROM lead_purchases WHERE buyer_id=? AND payment_status="paid"', [$buyerId]);
        return (int)($row['c'] ?? 0) === 0;
    }

    public static function validateLeadCoupon(string $code, float $subtotal, int $leadCount): array
    {
        $coupon = Database::one('SELECT * FROM lead_coupons WHERE UPPER(code)=UPPER(?) AND is_active=1', [trim($code)]);
        if (!$coupon) {
            throw new InvalidArgumentException('Invalid coupon code.');
        }
        $today = date('Y-m-d');
        if (!empty($coupon['valid_from']) && $today < (string)$coupon['valid_from']) {
            throw new InvalidArgumentException('Coupon is not active yet.');
        }
        if (!empty($coupon['valid_to']) && $today > (string)$coupon['valid_to']) {
            throw new InvalidArgumentException('Coupon has expired.');
        }
        if ($coupon['usage_limit'] !== null && (int)$coupon['usage_limit'] > 0 && (int)$coupon['used_count'] >= (int)$coupon['usage_limit']) {
            throw new InvalidArgumentException('Coupon usage limit reached.');
        }
        if ($coupon['min_leads'] !== null && $leadCount < (int)$coupon['min_leads']) {
            throw new InvalidArgumentException('Coupon requires at least ' . (int)$coupon['min_leads'] . ' leads.');
        }
        if ($coupon['max_leads'] !== null && (int)$coupon['max_leads'] > 0 && $leadCount > (int)$coupon['max_leads']) {
            throw new InvalidArgumentException('Coupon is valid up to ' . (int)$coupon['max_leads'] . ' leads.');
        }
        if ($coupon['min_order_amount'] !== null && $subtotal < (float)$coupon['min_order_amount']) {
            throw new InvalidArgumentException('Coupon requires minimum order of ₹' . number_format((float)$coupon['min_order_amount'], 0));
        }
        $discount = (string)$coupon['discount_type'] === 'flat'
            ? (float)$coupon['discount_value']
            : $subtotal * ((float)$coupon['discount_value'] / 100);
        if ($coupon['max_discount_amount'] !== null && (float)$coupon['max_discount_amount'] > 0) {
            $discount = min($discount, (float)$coupon['max_discount_amount']);
        }
        $discount = min($discount, $subtotal);
        $coupon['discount_amount'] = round($discount, 2);
        return $coupon;
    }

    public static function publicLeadCoupons(): array
    {
        return Database::query(
            'SELECT id, code, title, description, discount_type, discount_value, min_leads, max_leads, min_order_amount, max_discount_amount, valid_to
             FROM lead_coupons
             WHERE show_on_frontend=1 AND is_active=1
               AND (valid_from IS NULL OR valid_from <= CURDATE())
               AND (valid_to IS NULL OR valid_to >= CURDATE())
               AND (usage_limit IS NULL OR usage_limit = 0 OR used_count < usage_limit)
             ORDER BY discount_value DESC, id DESC'
        );
    }

    public static function listLeadCoupons(): array
    {
        return Database::query('SELECT * FROM lead_coupons ORDER BY created_at DESC');
    }

    public static function saveLeadCoupon(array $data, ?int $id = null): int
    {
        $code = strtoupper(trim((string)($data['code'] ?? '')));
        if ($code === '' || empty($data['title'])) {
            throw new InvalidArgumentException('Coupon code and title are required.');
        }
        $values = [
            $code,
            trim((string)$data['title']),
            (string)($data['description'] ?? ''),
            in_array(($data['discount_type'] ?? 'percentage'), ['percentage', 'flat'], true) ? (string)$data['discount_type'] : 'percentage',
            (float)($data['discount_value'] ?? 0),
            ($data['min_leads'] ?? '') !== '' ? (int)$data['min_leads'] : null,
            ($data['max_leads'] ?? '') !== '' ? (int)$data['max_leads'] : null,
            ($data['min_order_amount'] ?? '') !== '' ? (float)$data['min_order_amount'] : null,
            ($data['max_discount_amount'] ?? '') !== '' ? (float)$data['max_discount_amount'] : null,
            ($data['valid_from'] ?? '') !== '' ? (string)$data['valid_from'] : null,
            ($data['valid_to'] ?? '') !== '' ? (string)$data['valid_to'] : null,
            ($data['usage_limit'] ?? '') !== '' ? (int)$data['usage_limit'] : null,
            !empty($data['show_on_frontend']) ? 1 : 0,
            !empty($data['is_active']) ? 1 : 0,
        ];
        if ($id) {
            Database::exec(
                'UPDATE lead_coupons SET code=?, title=?, description=?, discount_type=?, discount_value=?, min_leads=?, max_leads=?, min_order_amount=?, max_discount_amount=?, valid_from=?, valid_to=?, usage_limit=?, show_on_frontend=?, is_active=?, updated_at=NOW() WHERE id=?',
                [...$values, $id]
            );
            return $id;
        }
        return Database::exec(
            'INSERT INTO lead_coupons (code, title, description, discount_type, discount_value, min_leads, max_leads, min_order_amount, max_discount_amount, valid_from, valid_to, usage_limit, show_on_frontend, is_active) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            $values
        );
    }

    public static function deleteLeadCoupon(int $id): void
    {
        Database::exec('DELETE FROM lead_coupons WHERE id=?', [$id]);
    }

    public static function countLeadsForCriteria(array $criteria, string $dateFilter, ?string $startDate = null, ?string $endDate = null): int
    {
        $leads = self::leadRowsForDateFilter($dateFilter, $startDate, $endDate);
        return count(array_filter($leads, static fn(array $lead): bool => self::leadMatchesCriteria($lead, $criteria)));
    }

    public static function createOrLoginBuyer(array $data): array
    {
        $phone = preg_replace('/\D+/', '', (string)($data['phone'] ?? '')) ?? '';
        $email = trim((string)($data['email'] ?? ''));
        $password = (string)($data['password'] ?? '');
        if (strlen($phone) < 10 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            throw new InvalidArgumentException('Valid email, 10 digit mobile and 6 character password are required.');
        }
        $existing = Database::one('SELECT * FROM lead_buyers WHERE phone = ? AND is_active = 1', [$phone]);
        if ($existing) {
            if (!password_verify($password, (string)$existing['password_hash'])) {
                throw new InvalidArgumentException('Mobile already exists. Please login with the correct password.');
            }
            return $existing;
        }
        $id = Database::exec(
            'INSERT INTO lead_buyers (name, email, phone, password_hash) VALUES (?, ?, ?, ?)',
            [
                trim((string)($data['name'] ?? 'Buyer')),
                $email,
                $phone,
                password_hash($password, PASSWORD_DEFAULT),
            ]
        );
        return Database::one('SELECT * FROM lead_buyers WHERE id = ?', [$id]) ?? [];
    }

    public static function loginBuyer(string $phone, string $password): ?array
    {
        $cleanPhone = preg_replace('/\D+/', '', $phone) ?? '';
        $buyer = Database::one('SELECT * FROM lead_buyers WHERE phone = ? AND is_active = 1', [$cleanPhone]);
        if (!$buyer || !password_verify($password, (string)$buyer['password_hash'])) {
            return null;
        }
        return $buyer;
    }

    public static function createLeadPurchaseOrder(int $buyerId, array $cart, string $orderId, float $amount, ?string $couponCode = null, float $discount = 0): int
    {
        return Database::exec(
            'INSERT INTO lead_purchases (buyer_id, razorpay_order_id, coupon_code, discount_amount, amount_total, currency, payment_status, cart_json)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [$buyerId, $orderId, $couponCode, $discount, $amount, defined('RAZORPAY_CURRENCY') ? RAZORPAY_CURRENCY : 'INR', 'pending', json_encode($cart, JSON_UNESCAPED_UNICODE)]
        );
    }

    public static function markLeadPurchasePaid(string $orderId, int $buyerId, string $paymentId, string $signature): void
    {
        $purchase = Database::one('SELECT * FROM lead_purchases WHERE razorpay_order_id = ? AND buyer_id = ?', [$orderId, $buyerId]);
        if (!$purchase) {
            throw new RuntimeException('Payment order mismatch.');
        }
        if (($purchase['payment_status'] ?? '') !== 'paid') {
            Database::exec(
                'UPDATE lead_purchases SET razorpay_payment_id=?, razorpay_signature=?, payment_status="paid", paid_at=NOW(), updated_at=NOW() WHERE id=?',
                [$paymentId, $signature, (int)$purchase['id']]
            );
            if (!empty($purchase['coupon_code'])) {
                Database::exec('UPDATE lead_coupons SET used_count = used_count + 1, updated_at=NOW() WHERE UPPER(code)=UPPER(?)', [(string)$purchase['coupon_code']]);
            }
            $cart = json_decode((string)($purchase['cart_json'] ?? '[]'), true);
            if (!is_array($cart)) {
                $cart = [];
            }
            foreach ($cart as $item) {
                $normalized = self::normalizeLeadCartItem(is_array($item) ? $item : []);
                $leadIds = array_values(array_filter(array_map('intval', (array)($item['lead_ids'] ?? []))));
                $uniqueLeadCount = count($leadIds);
                $itemAmount = (float)($item['price_total'] ?? 0);
                $itemPricing = is_array($item['pricing'] ?? null) ? $item['pricing'] : ['total' => $itemAmount, 'lines' => []];
                Database::exec(
                    'INSERT INTO lead_purchase_items (purchase_id, buyer_id, filter_name, filter_json, date_filter, leads_count, amount_total, pricing_json)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                    [
                        (int)$purchase['id'],
                        $buyerId,
                        $normalized['filter_name'],
                        json_encode([
                            'criteria' => $normalized['criteria'],
                            'start_date' => $normalized['start_date'],
                            'end_date' => $normalized['end_date'],
                            'lead_ids' => $leadIds,
                        ], JSON_UNESCAPED_UNICODE),
                        $normalized['date_filter'],
                        $uniqueLeadCount,
                        $itemAmount,
                        json_encode($itemPricing, JSON_UNESCAPED_UNICODE),
                    ]
                );
            }
        }
    }

    public static function createFreeLeadPurchase(int $buyerId, array $cart, ?string $couponCode = null, float $discount = 0): string
    {
        $orderId = 'free_' . $buyerId . '_' . time() . '_' . bin2hex(random_bytes(3));
        self::createLeadPurchaseOrder($buyerId, $cart, $orderId, 0, $couponCode, $discount);
        self::markLeadPurchasePaid($orderId, $buyerId, 'free_checkout', 'free_checkout');
        return $orderId;
    }

    public static function buyerPurchases(int $buyerId): array
    {
        return Database::query(
            'SELECT i.*, p.amount_total AS purchase_total, p.razorpay_payment_id, p.payment_status, p.created_at AS purchase_date
             FROM lead_purchase_items i
             JOIN lead_purchases p ON p.id = i.purchase_id
             WHERE i.buyer_id = ? AND p.payment_status = "paid"
             ORDER BY i.created_at DESC',
            [$buyerId]
        );
    }

    public static function purchasedLeadRows(int $buyerId, int $itemId): array
    {
        $item = Database::one(
            'SELECT i.* FROM lead_purchase_items i JOIN lead_purchases p ON p.id=i.purchase_id WHERE i.id=? AND i.buyer_id=? AND p.payment_status="paid"',
            [$itemId, $buyerId]
        );
        if (!$item) {
            throw new RuntimeException('Purchased lead package not found.');
        }
        $filter = json_decode((string)$item['filter_json'], true);
        $leadIds = array_values(array_filter(array_map('intval', (array)($filter['lead_ids'] ?? []))));
        if ($leadIds !== []) {
            $placeholders = implode(',', array_fill(0, count($leadIds), '?'));
            $rows = Database::query(
                'SELECT id, name, phone, city, society_area, budget, requirement, source, status, estimate, created_at FROM leads WHERE id IN (' . $placeholders . ')',
                $leadIds
            );
            $byId = [];
            foreach ($rows as $row) {
                $byId[(int)$row['id']] = $row;
            }
            return array_values(array_filter(array_map(static fn(int $id): ?array => $byId[$id] ?? null, $leadIds)));
        }
        $criteria = is_array($filter['criteria'] ?? null) ? $filter['criteria'] : [];
        $leads = self::leadRowsForDateFilter((string)($item['date_filter'] ?? 'all_time'), $filter['start_date'] ?? null, $filter['end_date'] ?? null);
        $matching = array_values(array_filter($leads, static fn(array $lead): bool => self::leadMatchesCriteria($lead, $criteria)));
        return array_slice($matching, 0, max(0, (int)($item['leads_count'] ?? count($matching))));
    }

    private static function leadRowsForDateFilter(string $dateFilter, ?string $startDate = null, ?string $endDate = null): array
    {
        $where = [];
        $params = [];
        if ($dateFilter === 'today') {
            $where[] = 'DATE(created_at) = CURDATE()';
        } elseif ($dateFilter === 'last_7_days') {
            $where[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
        } elseif ($dateFilter === 'last_30_days') {
            $where[] = 'created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
        } elseif ($dateFilter === 'custom' && $startDate && $endDate) {
            $where[] = 'DATE(created_at) BETWEEN ? AND ?';
            $params[] = $startDate;
            $params[] = $endDate;
        }
        $sql = 'SELECT id, name, phone, city, society_area, budget, requirement, source, status, estimate, created_at FROM leads';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC';
        return Database::query($sql, $params);
    }

    private static function leadFilterDefinitions(array $leads): array
    {
        $cities = array_values(array_unique(array_filter(array_map(static fn(array $l): string => (string)$l['city'], $leads)))) ?: ['Gurgaon', 'Delhi', 'Noida'];
        $societies = array_slice(array_values(array_unique(array_filter(array_map(static fn(array $l): string => (string)($l['society_area'] ?? ''), $leads)))), 0, 12);
        $workTypes = ['Full home', 'Kitchen', 'Wardrobe', 'Bathroom', 'Renovation'];
        $budgetRanges = [
            'under_10_lakh' => 'Under 10 lakh',
            '10_20_lakh' => '10-20 lakh',
            'above_20_lakh' => 'Above 20 lakh',
        ];
        $defs = [];
        foreach ($cities as $city) {
            $defs[] = ['section' => 'City', 'name' => $city, 'criteria' => ['city' => $city]];
            foreach ($budgetRanges as $key => $label) {
                $defs[] = ['section' => 'City + Budget', 'name' => $city . ' ' . strtolower($label), 'criteria' => ['city' => $city, 'budget_range' => $key]];
            }
            foreach ($workTypes as $type) {
                $defs[] = ['section' => 'City + Type of Work', 'name' => $city . ' ' . strtolower($type), 'criteria' => ['city' => $city, 'work_type' => $type]];
            }
        }
        foreach ($societies as $society) {
            $defs[] = ['section' => 'Society', 'name' => $society, 'criteria' => ['society' => $society]];
            foreach ($cities as $city) {
                $defs[] = ['section' => 'City + Society', 'name' => $city . ' + ' . $society, 'criteria' => ['city' => $city, 'society' => $society]];
            }
            foreach ($workTypes as $type) {
                $defs[] = ['section' => 'Society + Type of Work', 'name' => $society . ' ' . strtolower($type), 'criteria' => ['society' => $society, 'work_type' => $type]];
            }
        }
        foreach ($workTypes as $type) {
            $defs[] = ['section' => 'Type of Work', 'name' => $type, 'criteria' => ['work_type' => $type]];
        }
        foreach ($cities as $city) {
            foreach ($budgetRanges as $key => $label) {
                foreach ($workTypes as $type) {
                    $defs[] = ['section' => 'City + Budget + Type', 'name' => $city . ' ' . strtolower($label) . ' ' . strtolower($type), 'criteria' => ['city' => $city, 'budget_range' => $key, 'work_type' => $type]];
                }
            }
        }
        return $defs;
    }

    private static function leadMatchesCriteria(array $lead, array $criteria): bool
    {
        if (!empty($criteria['city']) && strcasecmp((string)$lead['city'], (string)$criteria['city']) !== 0) {
            return false;
        }
        if (!empty($criteria['society']) && strcasecmp((string)($lead['society_area'] ?? ''), (string)$criteria['society']) !== 0) {
            return false;
        }
        if (!empty($criteria['work_type']) && !self::leadRequirementHasWorkType((string)$lead['requirement'], (string)$criteria['work_type'])) {
            return false;
        }
        if (!empty($criteria['budget_range']) && !self::leadBudgetInRange((string)($lead['budget'] ?? ''), (string)$criteria['budget_range'])) {
            return false;
        }
        return true;
    }

    private static function leadRequirementHasWorkType(string $requirement, string $type): bool
    {
        $haystack = strtolower($requirement);
        $needle = strtolower($type);
        if ($needle === 'full home') {
            return str_contains($haystack, 'full home') || str_contains($haystack, 'complete home');
        }
        return str_contains($haystack, $needle);
    }

    private static function leadBudgetInRange(string $budget, string $range): bool
    {
        $amount = self::budgetToRupees($budget);
        if ($amount <= 0) {
            return false;
        }
        if ($range === 'under_10_lakh') {
            return $amount < 1000000;
        }
        if ($range === '10_20_lakh') {
            return $amount >= 1000000 && $amount <= 2000000;
        }
        if ($range === 'above_20_lakh') {
            return $amount > 2000000;
        }
        return true;
    }

    private static function budgetToRupees(string $budget): float
    {
        $value = strtolower(str_replace([',', '₹', 'rs.', 'rs'], '', $budget));
        if (!preg_match('/([0-9]+(?:\\.[0-9]+)?)/', $value, $m)) {
            return 0;
        }
        $num = (float)$m[1];
        if (str_contains($value, 'cr')) {
            return $num * 10000000;
        }
        if (str_contains($value, 'lakh') || str_contains($value, 'lac') || str_contains($value, 'l')) {
            return $num * 100000;
        }
        return $num;
    }

    private static function criteriaLabel(array $criteria): string
    {
        $parts = [];
        foreach (['city', 'society', 'budget_range', 'work_type'] as $key) {
            if (!empty($criteria[$key])) {
                $parts[] = str_replace('_', ' ', (string)$criteria[$key]);
            }
        }
        return $parts ? implode(' + ', $parts) : 'Lead package';
    }

    public static function realEstateFilterOptions(): array
    {
        return [
            'cities' => array_map(
                static fn(array $row): string => (string)$row['city'],
                Database::query("SELECT DISTINCT city FROM real_estate_projects WHERE is_active=1 AND city<>'' ORDER BY city")
            ),
            'localities' => array_map(
                static fn(array $row): string => (string)$row['locality'],
                Database::query("SELECT DISTINCT locality FROM real_estate_projects WHERE is_active=1 AND locality IS NOT NULL AND locality<>'' ORDER BY locality")
            ),
            'property_types' => array_map(
                static fn(array $row): string => (string)$row['property_type'],
                Database::query("SELECT DISTINCT property_type FROM real_estate_projects WHERE is_active=1 AND property_type<>'' ORDER BY property_type")
            ),
            'bhk_types' => array_map(
                static fn(array $row): string => (string)$row['bhk_type'],
                Database::query("SELECT DISTINCT bhk_type FROM real_estate_units WHERE is_active=1 AND bhk_type IS NOT NULL AND bhk_type<>'' ORDER BY bhk_type")
            ),
        ];
    }

    public static function listRealEstateProjects(array $filters = [], bool $admin = false): array
    {
        $where = [];
        $params = [];
        if (!$admin) {
            $where[] = 'p.is_active=1';
        }
        $listingFor = trim((string)($filters['listing_for'] ?? ''));
        if (in_array($listingFor, ['buy', 'rent'], true)) {
            $where[] = '(p.listing_for=? OR p.listing_for="both")';
            $params[] = $listingFor;
        }
        foreach (['city', 'locality', 'property_type', 'project_status'] as $field) {
            $value = trim((string)($filters[$field] ?? ''));
            if ($value !== '') {
                $where[] = 'p.' . $field . '=?';
                $params[] = $value;
            }
        }
        $bhk = trim((string)($filters['bhk_type'] ?? ''));
        if ($bhk !== '') {
            $where[] = 'EXISTS (SELECT 1 FROM real_estate_units bu WHERE bu.project_id=p.id AND bu.is_active=1 AND bu.bhk_type=?)';
            $params[] = $bhk;
        }
        $keyword = trim((string)($filters['q'] ?? ''));
        if ($keyword !== '') {
            $where[] = '(p.project_name LIKE ? OR p.builder_name LIKE ? OR p.locality LIKE ? OR p.city LIKE ?)';
            $search = '%' . $keyword . '%';
            array_push($params, $search, $search, $search, $search);
        }
        $priceColumn = $listingFor === 'rent' ? 'rent_min' : 'price_min';
        if (isset($filters['price_min']) && (float)$filters['price_min'] > 0) {
            $where[] = 'p.' . $priceColumn . '>=?';
            $params[] = (float)$filters['price_min'];
        }
        if (isset($filters['price_max']) && (float)$filters['price_max'] > 0) {
            $where[] = 'p.' . $priceColumn . '<=?';
            $params[] = (float)$filters['price_max'];
        }

        $order = match ((string)($filters['sort'] ?? '')) {
            'price_low' => 'p.' . $priceColumn . ' ASC',
            'price_high' => 'p.' . $priceColumn . ' DESC',
            'possession' => 'p.possession_date ASC',
            default => 'p.is_featured DESC, p.updated_at DESC',
        };
        $sql = 'SELECT p.*,
                       (SELECT m.media_url FROM real_estate_media m WHERE m.project_id=p.id AND m.media_type="image" ORDER BY m.is_cover DESC, m.sort_order, m.id LIMIT 1) AS cover_image,
                       (SELECT m.media_url FROM real_estate_media m WHERE m.project_id=p.id AND m.media_type="image" AND LOWER(m.category)="exterior" ORDER BY m.sort_order, m.id LIMIT 1) AS exterior_image,
                       (SELECT GROUP_CONCAT(DISTINCT u.bhk_type ORDER BY u.sort_order SEPARATOR ", ") FROM real_estate_units u WHERE u.project_id=p.id AND u.is_active=1) AS configurations,
                       (SELECT COUNT(*) FROM real_estate_units u WHERE u.project_id=p.id AND u.is_active=1) AS unit_count
                FROM real_estate_projects p';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY ' . $order;
        return Database::query($sql, $params);
    }

    public static function getRealEstateProject(int|string $identifier, bool $admin = false): ?array
    {
        $field = is_int($identifier) || ctype_digit((string)$identifier) ? 'id' : 'slug';
        $sql = 'SELECT * FROM real_estate_projects WHERE ' . $field . '=?';
        if (!$admin) {
            $sql .= ' AND is_active=1';
        }
        $project = Database::one($sql, [$identifier]);
        if (!$project) {
            return null;
        }
        foreach (['amenities_json', 'highlights_json', 'nearby_json'] as $fieldName) {
            $project[$fieldName] = self::parseJsonArray($project[$fieldName] ?? '[]');
        }
        $project['units'] = Database::query(
            'SELECT * FROM real_estate_units WHERE project_id=?' . ($admin ? '' : ' AND is_active=1') . ' ORDER BY sort_order, sale_price, monthly_rent, id',
            [(int)$project['id']]
        );
        $project['media'] = Database::query(
            'SELECT * FROM real_estate_media WHERE project_id=? ORDER BY is_cover DESC, sort_order, id',
            [(int)$project['id']]
        );
        $project['floor_plans'] = Database::query(
            'SELECT fp.*, u.unit_name FROM real_estate_floor_plans fp LEFT JOIN real_estate_units u ON u.id=fp.unit_id WHERE fp.project_id=? ORDER BY fp.sort_order, fp.id',
            [(int)$project['id']]
        );
        return $project;
    }

    public static function saveRealEstateProject(array $data, ?int $id = null): int
    {
        $fields = [
            'slug', 'project_name', 'listing_for', 'property_type', 'project_status', 'builder_name', 'rera_number',
            'possession_date', 'address', 'locality', 'city', 'state', 'pincode', 'latitude', 'longitude',
            'short_description', 'description', 'price_min', 'price_max', 'rent_min', 'rent_max', 'price_per_sqft',
            'area_min', 'area_max', 'total_units', 'total_towers', 'total_area_acres', 'video_url', 'brochure_url',
            'amenities_json', 'highlights_json', 'nearby_json', 'meta_title', 'meta_description', 'is_featured', 'is_active',
        ];
        $jsonFields = ['amenities_json', 'highlights_json', 'nearby_json'];
        $booleanFields = ['is_featured', 'is_active'];
        $values = [];
        foreach ($fields as $field) {
            if (in_array($field, $jsonFields, true)) {
                $values[] = json_encode(self::parseJsonArray($data[$field] ?? []), JSON_UNESCAPED_UNICODE);
            } elseif (in_array($field, $booleanFields, true)) {
                $values[] = !empty($data[$field]) ? 1 : 0;
            } else {
                $value = $data[$field] ?? null;
                $values[] = $value === '' ? null : $value;
            }
        }

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            if ($id) {
                $sets = implode(', ', array_map(static fn(string $field): string => $field . '=?', $fields));
                Database::exec('UPDATE real_estate_projects SET ' . $sets . ' WHERE id=?', [...$values, $id]);
                $projectId = $id;
            } else {
                $columns = implode(', ', $fields);
                $placeholders = implode(', ', array_fill(0, count($fields), '?'));
                $projectId = Database::exec('INSERT INTO real_estate_projects (' . $columns . ') VALUES (' . $placeholders . ')', $values);
            }

            self::replaceRealEstateUnits($projectId, self::decodeStructuredRows($data['units_json'] ?? []));
            self::replaceRealEstateMedia($projectId, self::decodeStructuredRows($data['media_json'] ?? []));
            self::replaceRealEstateFloorPlans($projectId, self::decodeStructuredRows($data['floor_plans_json'] ?? []));
            $pdo->commit();
            self::syncRealEstateProjectUrlAlias($projectId);
            return $projectId;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function syncRealEstateProjectUrlAlias(int $id): void
    {
        $project = Database::one('SELECT * FROM real_estate_projects WHERE id=?', [$id]);
        if (!$project) {
            return;
        }
        $media = Database::one("SELECT media_url FROM real_estate_media WHERE project_id=? AND media_type='image' ORDER BY is_cover DESC, sort_order ASC LIMIT 1", [$id]);
        $path = '/property/' . (string)$project['slug'];
        self::syncUrlAlias([
            'path' => $path,
            'page_type' => 'property_detail',
            'entity_table' => 'real_estate_projects',
            'entity_id' => (int)$project['id'],
            'source' => 'property_project',
            'meta_title' => (string)($project['meta_title'] ?: $project['project_name'] . ' | Price, Floor Plans & Photos'),
            'meta_description' => (string)($project['meta_description'] ?: $project['short_description']),
            'h1' => (string)$project['project_name'],
            'content_html' => (string)$project['description'],
            'image_url' => (string)($media['media_url'] ?? ''),
            'canonical_url' => $path,
            'is_active' => !empty($project['is_active']) ? 1 : 0,
        ]);
    }

    public static function decodeStructuredRows(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter($value, 'is_array'));
        }
        $decoded = json_decode((string)$value, true);
        return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
    }

    private static function replaceRealEstateUnits(int $projectId, array $rows): void
    {
        Database::exec('DELETE FROM real_estate_units WHERE project_id=?', [$projectId]);
        foreach ($rows as $index => $row) {
            $name = trim((string)($row['unit_name'] ?? ''));
            if ($name === '') {
                continue;
            }
            Database::exec(
                'INSERT INTO real_estate_units
                 (project_id, unit_name, bhk_type, unit_type, carpet_area, builtup_area, balconies, bathrooms, furnishing, sale_price, monthly_rent, maintenance_amount, available_units, is_active, sort_order)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $projectId, $name, $row['bhk_type'] ?? null, $row['unit_type'] ?? null,
                    self::nullableNumber($row['carpet_area'] ?? null), self::nullableNumber($row['builtup_area'] ?? null),
                    self::nullableNumber($row['balconies'] ?? null), self::nullableNumber($row['bathrooms'] ?? null),
                    $row['furnishing'] ?? null, (float)($row['sale_price'] ?? 0), (float)($row['monthly_rent'] ?? 0),
                    (float)($row['maintenance_amount'] ?? 0), (int)($row['available_units'] ?? 0),
                    array_key_exists('is_active', $row) ? (!empty($row['is_active']) ? 1 : 0) : 1,
                    (int)($row['sort_order'] ?? $index),
                ]
            );
        }
    }

    private static function replaceRealEstateMedia(int $projectId, array $rows): void
    {
        Database::exec('DELETE FROM real_estate_media WHERE project_id=?', [$projectId]);
        foreach ($rows as $index => $row) {
            $url = trim((string)($row['media_url'] ?? ''));
            if ($url === '') {
                continue;
            }
            Database::exec(
                'INSERT INTO real_estate_media (project_id, media_type, media_url, title, category, is_cover, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $projectId, ($row['media_type'] ?? 'image') === 'video' ? 'video' : 'image', $url,
                    $row['title'] ?? null, $row['category'] ?? null, !empty($row['is_cover']) ? 1 : 0,
                    (int)($row['sort_order'] ?? $index),
                ]
            );
        }
    }

    private static function replaceRealEstateFloorPlans(int $projectId, array $rows): void
    {
        Database::exec('DELETE FROM real_estate_floor_plans WHERE project_id=?', [$projectId]);
        foreach ($rows as $index => $row) {
            $title = trim((string)($row['title'] ?? ''));
            $url = trim((string)($row['image_url'] ?? ''));
            if ($title === '' || $url === '') {
                continue;
            }
            Database::exec(
                'INSERT INTO real_estate_floor_plans (project_id, unit_id, title, image_url, area_label, price_label, sort_order) VALUES (?, NULL, ?, ?, ?, ?, ?)',
                [$projectId, $title, $url, $row['area_label'] ?? null, $row['price_label'] ?? null, (int)($row['sort_order'] ?? $index)]
            );
        }
    }

    private static function nullableNumber(mixed $value): int|float|null
    {
        return $value === null || $value === '' ? null : (float)$value;
    }

    public static function deleteRealEstateProject(int $id): void
    {
        Database::exec('DELETE FROM real_estate_projects WHERE id=?', [$id]);
    }

    public static function createPropertyEnquiry(array $data): int
    {
        $name = trim((string)($data['name'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string)($data['phone'] ?? '')) ?? '';
        $projectId = (int)($data['project_id'] ?? 0);
        if ($name === '' || strlen($phone) < 10 || $projectId < 1 || empty($data['consent'])) {
            throw new InvalidArgumentException('Name, valid phone number, project, and consent are required.');
        }
        return Database::exec(
            'INSERT INTO property_enquiries (project_id, unit_id, name, phone, email, requirement, message, consent, source)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)',
            [
                $projectId, !empty($data['unit_id']) ? (int)$data['unit_id'] : null, $name, $phone,
                trim((string)($data['email'] ?? '')) ?: null,
                ($data['requirement'] ?? 'buy') === 'rent' ? 'rent' : 'buy',
                trim((string)($data['message'] ?? '')) ?: null,
                trim((string)($data['source'] ?? 'project_detail')) ?: 'project_detail',
            ]
        );
    }

    public static function listPropertyEnquiries(): array
    {
        return Database::query(
            'SELECT e.*, p.project_name, p.city, p.locality, u.unit_name
             FROM property_enquiries e
             JOIN real_estate_projects p ON p.id=e.project_id
             LEFT JOIN real_estate_units u ON u.id=e.unit_id
             ORDER BY e.created_at DESC'
        );
    }

    public static function updatePropertyEnquiryStatus(int $id, string $status): void
    {
        if (!in_array($status, ['new', 'contacted', 'qualified', 'closed'], true)) {
            throw new InvalidArgumentException('Invalid enquiry status.');
        }
        Database::exec('UPDATE property_enquiries SET status=? WHERE id=?', [$status, $id]);
    }

    public static function normalizeUrlAliasPath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '/';
        }
        $path = parse_url($path, PHP_URL_PATH) ?: $path;
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public static function getUrlAliasByPath(string $path, bool $admin = false): ?array
    {
        try {
            $sql = 'SELECT * FROM url_aliases WHERE path=?';
            if (!$admin) {
                $sql .= ' AND is_active=1';
            }
            return Database::one($sql, [self::normalizeUrlAliasPath($path)]);
        } catch (Throwable) {
            return null;
        }
    }

    public static function listUrlAliases(): array
    {
        try {
            return Database::query('SELECT * FROM url_aliases ORDER BY updated_at DESC, path ASC');
        } catch (Throwable) {
            return [];
        }
    }

    public static function getUrlAlias(int $id): ?array
    {
        return Database::one('SELECT * FROM url_aliases WHERE id=?', [$id]);
    }

    public static function saveUrlAlias(array $data, ?int $id = null): int
    {
        $path = self::normalizeUrlAliasPath((string)($data['path'] ?? ''));
        if ($path === '') {
            throw new InvalidArgumentException('URL path is required.');
        }
        $fields = [
            'path', 'page_type', 'entity_table', 'entity_id', 'source', 'meta_title', 'meta_description',
            'h1', 'content_html', 'image_url', 'canonical_url', 'robots', 'is_active',
        ];
        $values = [];
        foreach ($fields as $field) {
            if ($field === 'path') {
                $values[] = $path;
            } elseif ($field === 'entity_id') {
                $values[] = !empty($data[$field]) ? (int)$data[$field] : null;
            } elseif ($field === 'is_active') {
                $values[] = !empty($data[$field]) ? 1 : 0;
            } else {
                $value = $data[$field] ?? null;
                if ($field === 'page_type' && trim((string)$value) === '') {
                    $value = 'page';
                }
                if ($field === 'source' && trim((string)$value) === '') {
                    $value = 'manual';
                }
                $values[] = $value === '' ? null : $value;
            }
        }
        if ($id) {
            $sets = implode(', ', array_map(static fn(string $field): string => $field . '=?', $fields));
            Database::exec('UPDATE url_aliases SET ' . $sets . ' WHERE id=?', [...$values, $id]);
            return $id;
        }
        return Database::exec('INSERT INTO url_aliases (' . implode(', ', $fields) . ') VALUES (' . implode(', ', array_fill(0, count($fields), '?')) . ')', $values);
    }

    public static function deleteUrlAlias(int $id): void
    {
        Database::exec('DELETE FROM url_aliases WHERE id=?', [$id]);
    }

    public static function syncUrlAlias(array $data): void
    {
        try {
            $path = self::normalizeUrlAliasPath((string)($data['path'] ?? ''));
            if ($path === '') {
                return;
            }
            $existing = self::getUrlAliasByPath($path, true);
            if ($existing) {
                $merged = $existing;
                foreach ($data as $key => $value) {
                    if (in_array($key, ['page_type', 'entity_table', 'entity_id', 'source', 'canonical_url', 'is_active'], true)) {
                        $merged[$key] = $value;
                    } elseif (($existing[$key] ?? null) === null || (string)($existing[$key] ?? '') === '') {
                        $merged[$key] = $value;
                    }
                }
                self::saveUrlAlias($merged, (int)$existing['id']);
                return;
            }
            self::saveUrlAlias(array_merge(['is_active' => 1, 'source' => 'system'], $data));
        } catch (Throwable) {
            return;
        }
    }

    public static function listDesignIdeaSections(bool $admin = false): array
    {
        $sql = 'SELECT * FROM design_idea_sections';
        if (!$admin) {
            $sql .= ' WHERE is_active=1';
        }
        $sql .= ' ORDER BY sort_order ASC, id ASC';
        $rows = Database::query($sql);
        foreach ($rows as &$row) {
            $row['items_json'] = self::decodeStructuredRows($row['items_json'] ?? '[]');
        }
        unset($row);
        return $rows;
    }

    public static function getDesignIdeaSection(int|string $identifier, bool $admin = false): ?array
    {
        $field = is_int($identifier) || ctype_digit((string)$identifier) ? 'id' : 'section_key';
        $sql = 'SELECT * FROM design_idea_sections WHERE ' . $field . '=?';
        if (!$admin) {
            $sql .= ' AND is_active=1';
        }
        $row = Database::one($sql, [$identifier]);
        if (!$row) {
            return null;
        }
        $row['items_json'] = self::decodeStructuredRows($row['items_json'] ?? '[]');
        return $row;
    }

    public static function saveDesignIdeaSection(array $data, ?int $id = null): int
    {
        $allowedTypes = ['hero_tiles', 'category_grid', 'color_grid', 'style_grid', 'unit_grid', 'trending', 'lead_form', 'tool_cards', 'content'];
        $sectionType = trim((string)($data['section_type'] ?? 'category_grid'));
        if (!in_array($sectionType, $allowedTypes, true)) {
            throw new InvalidArgumentException('Invalid section type.');
        }
        $fields = ['section_key', 'title', 'subtitle', 'section_type', 'items_json', 'sort_order', 'is_active'];
        $values = [];
        foreach ($fields as $field) {
            if ($field === 'items_json') {
                $items = self::decodeStructuredRows($data[$field] ?? []);
                $values[] = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } elseif ($field === 'is_active') {
                $values[] = !empty($data[$field]) ? 1 : 0;
            } elseif ($field === 'sort_order') {
                $values[] = (int)($data[$field] ?? 0);
            } else {
                $value = $field === 'section_type' ? $sectionType : ($data[$field] ?? null);
                $values[] = $value === '' ? null : $value;
            }
        }
        if ($id) {
            $sets = implode(', ', array_map(static fn(string $field): string => $field . '=?', $fields));
            Database::exec('UPDATE design_idea_sections SET ' . $sets . ' WHERE id=?', [...$values, $id]);
            return $id;
        }
        return Database::exec('INSERT INTO design_idea_sections (' . implode(', ', $fields) . ') VALUES (' . implode(', ', array_fill(0, count($fields), '?')) . ')', $values);
    }

    public static function deleteDesignIdeaSection(int $id): void
    {
        Database::exec('DELETE FROM design_idea_sections WHERE id=?', [$id]);
    }

    public static function designIdeaFilterOptions(): array
    {
        return [
            'types' => self::distinctDesignIdeaField('type'),
            'colors' => self::distinctDesignIdeaField('color'),
            'cities' => self::distinctDesignIdeaField('city'),
            'states' => self::distinctDesignIdeaField('state'),
            'styles' => self::distinctDesignIdeaField('style'),
            'layouts' => self::distinctDesignIdeaField('layout'),
        ];
    }

    private static function distinctDesignIdeaField(string $field): array
    {
        return array_map(
            static fn(array $row): string => (string)$row[$field],
            Database::query('SELECT DISTINCT ' . $field . ' FROM design_ideas WHERE is_active=1 AND ' . $field . ' IS NOT NULL AND ' . $field . '<>"" ORDER BY ' . $field)
        );
    }

    public static function listDesignIdeaAliases(bool $admin = false): array
    {
        $sql = 'SELECT * FROM design_idea_aliases';
        if (!$admin) {
            $sql .= ' WHERE is_active=1';
        }
        $sql .= ' ORDER BY updated_at DESC, title ASC';
        return Database::query($sql);
    }

    public static function getDesignIdeaAlias(string|int $identifier, bool $admin = false): ?array
    {
        $field = is_int($identifier) || ctype_digit((string)$identifier) ? 'id' : 'slug';
        $sql = 'SELECT * FROM design_idea_aliases WHERE ' . $field . '=?';
        if (!$admin) {
            $sql .= ' AND is_active=1';
        }
        return Database::one($sql, [$identifier]);
    }

    public static function matchDesignIdeaAliasForFilters(array $filters): ?array
    {
        $filterFields = [
            'type' => 'filter_type',
            'color' => 'filter_color',
            'city' => 'filter_city',
            'state' => 'filter_state',
            'style' => 'filter_style',
            'layout' => 'filter_layout',
        ];
        $selected = [];
        foreach ($filterFields as $filterKey => $column) {
            $value = trim((string)($filters[$filterKey] ?? ''));
            if ($value !== '') {
                $selected[$filterKey] = mb_strtolower($value);
            }
        }
        if (!$selected) {
            return null;
        }

        $bestAlias = null;
        $bestScore = 0;
        foreach (self::listDesignIdeaAliases() as $alias) {
            $score = 0;
            $matches = true;
            foreach ($filterFields as $filterKey => $column) {
                $aliasValue = trim((string)($alias[$column] ?? ''));
                if ($aliasValue === '') {
                    continue;
                }
                if (($selected[$filterKey] ?? '') !== mb_strtolower($aliasValue)) {
                    $matches = false;
                    break;
                }
                $score++;
            }
            if ($matches && $score > $bestScore) {
                $bestAlias = $alias;
                $bestScore = $score;
            }
        }

        return $bestAlias;
    }

    public static function listDesignIdeas(array $filters = [], bool $admin = false): array
    {
        $where = [];
        $params = [];
        if (!$admin) {
            $where[] = 'is_active=1';
        }
        foreach (['type', 'color', 'city', 'state', 'style', 'layout'] as $field) {
            $value = trim((string)($filters[$field] ?? ''));
            if ($value !== '') {
                $where[] = $field . '=?';
                $params[] = $value;
            }
        }
        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(name LIKE ? OR location LIKE ? OR city LIKE ? OR type LIKE ? OR color LIKE ? OR style LIKE ?)';
            $search = '%' . $q . '%';
            array_push($params, $search, $search, $search, $search, $search, $search);
        }
        $sql = 'SELECT * FROM design_ideas';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY is_featured DESC, updated_at DESC, id DESC';
        $rows = Database::query($sql, $params);
        foreach ($rows as &$row) {
            $row = self::normalizeDesignIdeaRow($row);
        }
        unset($row);
        return $rows;
    }

    public static function designIdeaAliasPage(string $slug): ?array
    {
        $alias = self::getDesignIdeaAlias($slug);
        if (!$alias) {
            return null;
        }
        $filters = array_filter([
            'type' => $alias['filter_type'] ?? null,
            'color' => $alias['filter_color'] ?? null,
            'city' => $alias['filter_city'] ?? null,
            'state' => $alias['filter_state'] ?? null,
            'style' => $alias['filter_style'] ?? null,
            'layout' => $alias['filter_layout'] ?? null,
        ], static fn(mixed $value): bool => $value !== null && $value !== '');
        return [
            'alias' => $alias,
            'filters' => $filters,
            'ideas' => self::listDesignIdeas(array_merge($filters, [
                'q' => $_GET['q'] ?? '',
                'color' => $_GET['color'] ?? ($filters['color'] ?? ''),
                'city' => $_GET['city'] ?? ($filters['city'] ?? ''),
                'state' => $_GET['state'] ?? ($filters['state'] ?? ''),
                'style' => $_GET['style'] ?? ($filters['style'] ?? ''),
                'layout' => $_GET['layout'] ?? ($filters['layout'] ?? ''),
            ])),
        ];
    }

    public static function getDesignIdea(string|int $identifier, bool $admin = false): ?array
    {
        $field = is_int($identifier) || ctype_digit((string)$identifier) ? 'id' : 'slug';
        $sql = 'SELECT * FROM design_ideas WHERE ' . $field . '=?';
        if (!$admin) {
            $sql .= ' AND is_active=1';
        }
        $row = Database::one($sql, [$identifier]);
        return $row ? self::normalizeDesignIdeaRow($row) : null;
    }

    private static function normalizeDesignIdeaRow(array $row): array
    {
        $row['gallery_json'] = self::parseJsonArray($row['gallery_json'] ?? '[]');
        $row['tags_json'] = self::parseJsonArray($row['tags_json'] ?? '[]');
        return $row;
    }

    public static function saveDesignIdea(array $data, ?int $id = null): int
    {
        $fields = [
            'slug', 'name', 'location', 'city', 'state', 'type', 'color', 'style', 'layout',
            'length_ft', 'breadth_ft', 'height_ft', 'budget_min', 'budget_max',
            'short_description', 'description', 'image_url', 'gallery_json', 'tags_json',
            'meta_title', 'meta_description', 'is_featured', 'is_active',
        ];
        $values = [];
        foreach ($fields as $field) {
            if (in_array($field, ['gallery_json', 'tags_json'], true)) {
                $values[] = json_encode(self::parseJsonArray($data[$field] ?? []), JSON_UNESCAPED_UNICODE);
            } elseif (in_array($field, ['is_featured', 'is_active'], true)) {
                $values[] = !empty($data[$field]) ? 1 : 0;
            } else {
                $value = $data[$field] ?? null;
                $values[] = $value === '' ? null : $value;
            }
        }
        if ($id) {
            $sets = implode(', ', array_map(static fn(string $field): string => $field . '=?', $fields));
            Database::exec('UPDATE design_ideas SET ' . $sets . ' WHERE id=?', [...$values, $id]);
            self::syncDesignIdeaUrlAliases($id);
            return $id;
        }
        $newId = Database::exec('INSERT INTO design_ideas (' . implode(', ', $fields) . ') VALUES (' . implode(', ', array_fill(0, count($fields), '?')) . ')', $values);
        self::syncDesignIdeaUrlAliases($newId);
        return $newId;
    }

    public static function syncDesignIdeaUrlAliases(int $ideaId): void
    {
        $idea = self::getDesignIdea($ideaId, true);
        if (!$idea) {
            return;
        }
        $detailPath = '/design-ideas/idea/' . (string)$idea['slug'];
        self::syncUrlAlias([
            'path' => $detailPath,
            'page_type' => 'design_idea_detail',
            'entity_table' => 'design_ideas',
            'entity_id' => (int)$idea['id'],
            'source' => 'design_idea',
            'meta_title' => $idea['meta_title'] ?: $idea['name'] . ' | HomeInteriors360',
            'meta_description' => $idea['meta_description'] ?: $idea['short_description'],
            'h1' => $idea['name'],
            'content_html' => $idea['description'],
            'image_url' => $idea['image_url'],
            'canonical_url' => $detailPath,
            'is_active' => !empty($idea['is_active']) ? 1 : 0,
        ]);

        $type = trim((string)($idea['type'] ?? ''));
        if ($type !== '') {
            $listingSlug = slugify($type) . '-designs';
            $existingAlias = self::getDesignIdeaAlias($listingSlug, true);
            if (!$existingAlias) {
                self::saveDesignIdeaAlias([
                    'slug' => $listingSlug,
                    'title' => $type . ' Design Ideas',
                    'subtitle' => 'Explore ' . strtolower($type) . ' design ideas with photos, colours, layouts, dimensions and quote capture.',
                    'hero_image' => $idea['image_url'],
                    'intro_content' => $type . ' interiors should balance style, storage, layout, lighting, and budget. Browse references before requesting a quote.',
                    'outro_content' => 'Shortlist ' . strtolower($type) . ' references you like and request a detailed quotation.',
                    'filter_type' => $type,
                    'meta_title' => $type . ' Design Ideas | HomeInteriors360',
                    'meta_description' => 'Browse ' . strtolower($type) . ' design ideas with photos, dimensions, colours, layouts and quote capture.',
                    'is_active' => 1,
                ]);
            } else {
                self::syncDesignIdeaAliasUrl((int)$existingAlias['id']);
            }
        }
    }

    public static function deleteDesignIdea(int $id): void
    {
        Database::exec('DELETE FROM design_ideas WHERE id=?', [$id]);
    }

    public static function saveDesignIdeaAlias(array $data, ?int $id = null): int
    {
        $fields = [
            'slug', 'title', 'subtitle', 'hero_image', 'intro_content', 'outro_content',
            'filter_type', 'filter_color', 'filter_city', 'filter_state', 'filter_style', 'filter_layout',
            'meta_title', 'meta_description', 'is_active',
        ];
        $values = [];
        foreach ($fields as $field) {
            if ($field === 'is_active') {
                $values[] = !empty($data[$field]) ? 1 : 0;
            } else {
                $value = $data[$field] ?? null;
                $values[] = $value === '' ? null : $value;
            }
        }
        if ($id) {
            $sets = implode(', ', array_map(static fn(string $field): string => $field . '=?', $fields));
            Database::exec('UPDATE design_idea_aliases SET ' . $sets . ' WHERE id=?', [...$values, $id]);
            self::syncDesignIdeaAliasUrl($id);
            return $id;
        }
        $newId = Database::exec('INSERT INTO design_idea_aliases (' . implode(', ', $fields) . ') VALUES (' . implode(', ', array_fill(0, count($fields), '?')) . ')', $values);
        self::syncDesignIdeaAliasUrl($newId);
        return $newId;
    }

    public static function syncDesignIdeaAliasUrl(int $aliasId): void
    {
        $alias = self::getDesignIdeaAlias($aliasId, true);
        if (!$alias) {
            return;
        }
        $path = '/design-ideas/' . (string)$alias['slug'];
        self::syncUrlAlias([
            'path' => $path,
            'page_type' => 'design_idea_alias',
            'entity_table' => 'design_idea_aliases',
            'entity_id' => (int)$alias['id'],
            'source' => 'design_idea_alias',
            'meta_title' => $alias['meta_title'] ?: $alias['title'] . ' | HomeInteriors360',
            'meta_description' => $alias['meta_description'] ?: $alias['subtitle'],
            'h1' => $alias['title'],
            'content_html' => trim((string)($alias['intro_content'] ?? '') . "\n\n" . (string)($alias['outro_content'] ?? '')),
            'image_url' => $alias['hero_image'],
            'canonical_url' => $path,
            'is_active' => !empty($alias['is_active']) ? 1 : 0,
        ]);
    }

    public static function deleteDesignIdeaAlias(int $id): void
    {
        Database::exec('DELETE FROM design_idea_aliases WHERE id=?', [$id]);
    }

    public static function createDesignIdeaLead(array $data): int
    {
        $name = trim((string)($data['name'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string)($data['phone'] ?? '')) ?? '';
        if ($name === '' || strlen($phone) < 10 || empty($data['consent'])) {
            throw new InvalidArgumentException('Name, valid phone number, and consent are required.');
        }
        return Database::exec(
            'INSERT INTO design_idea_leads (design_idea_id, alias_id, name, phone, email, city, requirement, budget, message, consent, source)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)',
            [
                !empty($data['design_idea_id']) ? (int)$data['design_idea_id'] : null,
                !empty($data['alias_id']) ? (int)$data['alias_id'] : null,
                $name,
                $phone,
                trim((string)($data['email'] ?? '')) ?: null,
                trim((string)($data['city'] ?? '')) ?: null,
                trim((string)($data['requirement'] ?? 'Design idea quote')) ?: 'Design idea quote',
                trim((string)($data['budget'] ?? '')) ?: null,
                trim((string)($data['message'] ?? '')) ?: null,
                trim((string)($data['source'] ?? 'design_ideas')) ?: 'design_ideas',
            ]
        );
    }

    public static function listDesignIdeaLeads(): array
    {
        return Database::query(
            'SELECT l.*, i.name AS idea_name, a.title AS alias_title
             FROM design_idea_leads l
             LEFT JOIN design_ideas i ON i.id=l.design_idea_id
             LEFT JOIN design_idea_aliases a ON a.id=l.alias_id
             ORDER BY l.created_at DESC'
        );
    }

    public static function updateDesignIdeaLeadStatus(int $id, string $status): void
    {
        if (!in_array($status, ['new', 'contacted', 'qualified', 'closed'], true)) {
            throw new InvalidArgumentException('Invalid lead status.');
        }
        Database::exec('UPDATE design_idea_leads SET status=? WHERE id=?', [$status, $id]);
    }

    public static function quotationSettings(): array
    {
        $settings = [];
        try {
            $rows = Database::query('SELECT setting_key, setting_value, setting_type FROM quotation_settings');
        } catch (Throwable) {
            return [
                'default_gst_percentage' => 18,
                'default_proposal_validity_days' => 15,
                'default_design_fee_percentage' => 3,
                'default_project_management_fee_percentage' => 5,
                'default_platform_commission_percentage' => 5,
                'default_payment_schedule' => [
                    ['label' => 'Booking amount', 'percentage' => 10],
                    ['label' => 'After design freeze', 'percentage' => 40],
                    ['label' => 'Before material dispatch', 'percentage' => 40],
                    ['label' => 'Before handover', 'percentage' => 10],
                ],
            ];
        }
        foreach ($rows as $row) {
            $value = $row['setting_value'];
            if (($row['setting_type'] ?? 'text') === 'json') {
                $decoded = json_decode((string)$value, true);
                $value = is_array($decoded) ? $decoded : [];
            } elseif (($row['setting_type'] ?? 'text') === 'number') {
                $value = (float)$value;
            }
            $settings[(string)$row['setting_key']] = $value;
        }
        return $settings;
    }

    public static function saveQuotationSettings(array $data): void
    {
        $allowed = [
            'default_gst_percentage' => 'number',
            'default_proposal_validity_days' => 'number',
            'default_design_fee_percentage' => 'number',
            'default_project_management_fee_percentage' => 'number',
            'default_platform_commission_percentage' => 'number',
            'default_city' => 'text',
            'default_package_slug' => 'text',
            'support_phone' => 'text',
            'support_email' => 'text',
            'company_address' => 'text',
            'default_payment_schedule' => 'json',
            'default_whatsapp_message' => 'text',
            'default_quote_terms' => 'html',
        ];
        foreach ($allowed as $key => $type) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            $value = $type === 'json'
                ? json_encode(self::decodeStructuredRows($data[$key]), JSON_UNESCAPED_UNICODE)
                : (string)$data[$key];
            Database::exec(
                'INSERT INTO quotation_settings (setting_key, setting_value, setting_type) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value), setting_type=VALUES(setting_type)',
                [$key, $value, $type]
            );
        }
    }

    public static function quotationPackages(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM quotation_packages';
        if ($activeOnly) {
            $sql .= ' WHERE is_active=1';
        }
        return Database::query($sql . ' ORDER BY sort_order ASC, id ASC');
    }

    public static function saveQuotationPackage(array $data, ?int $id = null): int
    {
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Package name is required.');
        }
        $fields = [
            'name', 'slug', 'description', 'material_grade', 'hardware_level', 'finish_level', 'warranty_years',
            'design_support', 'supervision_level', 'drawings_2d_count', 'views_3d_count', 'revision_count',
            'timeline_range', 'default_margin_percentage', 'default_design_fee_percentage',
            'default_project_management_fee_percentage', 'is_active', 'sort_order',
        ];
        $values = [];
        foreach ($fields as $field) {
            if ($field === 'slug') {
                $values[] = slugify((string)($data['slug'] ?? $name));
            } elseif (in_array($field, ['warranty_years', 'drawings_2d_count', 'views_3d_count', 'revision_count', 'sort_order'], true)) {
                $values[] = (int)($data[$field] ?? 0);
            } elseif (in_array($field, ['default_margin_percentage', 'default_design_fee_percentage', 'default_project_management_fee_percentage'], true)) {
                $values[] = (float)($data[$field] ?? 0);
            } elseif ($field === 'is_active') {
                $values[] = !empty($data[$field]) ? 1 : 0;
            } else {
                $values[] = ($data[$field] ?? '') === '' ? null : $data[$field];
            }
        }
        if ($id) {
            Database::exec('UPDATE quotation_packages SET ' . implode(', ', array_map(static fn(string $field): string => $field . '=?', $fields)) . ' WHERE id=?', [...$values, $id]);
            return $id;
        }
        return Database::exec('INSERT INTO quotation_packages (' . implode(', ', $fields) . ') VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')', $values);
    }

    public static function quotationRateCards(array $filters = []): array
    {
        $where = [];
        $params = [];
        $designerId = (int)($filters['designer_id'] ?? 0);
        if ($designerId > 0) {
            $where[] = '(r.designer_id = ? OR r.designer_id IS NULL)';
            $params[] = $designerId;
        }
        foreach (['city', 'category', 'package_id'] as $field) {
            $value = trim((string)($filters[$field] ?? ''));
            if ($value !== '') {
                $where[] = 'r.' . $field . '=?';
                $params[] = $value;
            }
        }
        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(r.item_name LIKE ? OR r.category LIKE ? OR r.material LIKE ? OR r.brand LIKE ?)';
            array_push($params, '%' . $q . '%', '%' . $q . '%', '%' . $q . '%', '%' . $q . '%');
        }
        $sql = 'SELECT r.*, p.name AS package_name, pro.full_name AS designer_name
                FROM quotation_rate_cards r
                LEFT JOIN quotation_packages p ON p.id=r.package_id
                LEFT JOIN pros pro ON pro.id=r.designer_id';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        return Database::query($sql . ' ORDER BY CASE WHEN r.designer_id IS NULL THEN 1 ELSE 0 END ASC, r.city ASC, p.sort_order ASC, r.category ASC, r.item_name ASC LIMIT 800', $params);
    }

    public static function saveQuotationRateCard(array $data, ?int $id = null): int
    {
        foreach (['city', 'category', 'item_name'] as $required) {
            if (trim((string)($data[$required] ?? '')) === '') {
                throw new InvalidArgumentException('City, category and item name are required.');
            }
        }
        $fields = [
            'city', 'package_id', 'designer_id', 'category', 'item_name', 'material_grade', 'material', 'finish', 'brand',
            'unit_type', 'base_rate', 'min_rate', 'max_rate', 'vendor_cost', 'client_selling_rate',
            'margin_percentage', 'gst_percentage', 'is_active', 'effective_from', 'effective_to',
        ];
        $values = [];
        foreach ($fields as $field) {
            if (in_array($field, ['package_id', 'designer_id'], true)) {
                $values[] = !empty($data[$field]) ? (int)$data[$field] : null;
            } elseif (in_array($field, ['base_rate', 'min_rate', 'max_rate', 'vendor_cost', 'client_selling_rate', 'margin_percentage', 'gst_percentage'], true)) {
                $values[] = max(0.0, (float)($data[$field] ?? 0));
            } elseif ($field === 'is_active') {
                $values[] = !empty($data[$field]) ? 1 : 0;
            } else {
                $values[] = ($data[$field] ?? '') === '' ? null : $data[$field];
            }
        }
        if ($id) {
            Database::exec('UPDATE quotation_rate_cards SET ' . implode(', ', array_map(static fn(string $field): string => $field . '=?', $fields)) . ' WHERE id=?', [...$values, $id]);
            return $id;
        }
        return Database::exec('INSERT INTO quotation_rate_cards (' . implode(', ', $fields) . ') VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')', $values);
    }

    public static function proposalTemplates(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM proposal_templates';
        if ($activeOnly) {
            $sql .= ' WHERE is_active=1';
        }
        $rows = Database::query($sql . ' ORDER BY is_active DESC, type ASC, name ASC');
        foreach ($rows as &$row) {
            $row['payment_schedule'] = self::decodeStructuredRows($row['payment_schedule_json'] ?? []);
        }
        unset($row);
        return $rows;
    }

    public static function saveProposalTemplate(array $data, ?int $id = null): int
    {
        if (trim((string)($data['name'] ?? '')) === '') {
            throw new InvalidArgumentException('Template name is required.');
        }
        $fields = [
            'name', 'type', 'cover_title', 'welcome_note', 'inclusions', 'exclusions', 'terms',
            'payment_schedule_json', 'warranty_text', 'footer_note', 'logo_url', 'accent_color', 'is_active',
        ];
        $values = [];
        foreach ($fields as $field) {
            if ($field === 'payment_schedule_json') {
                $values[] = json_encode(self::decodeStructuredRows($data[$field] ?? $data['payment_schedule'] ?? []), JSON_UNESCAPED_UNICODE);
            } elseif ($field === 'is_active') {
                $values[] = !empty($data[$field]) ? 1 : 0;
            } else {
                $values[] = ($data[$field] ?? '') === '' ? null : $data[$field];
            }
        }
        if ($id) {
            Database::exec('UPDATE proposal_templates SET ' . implode(', ', array_map(static fn(string $field): string => $field . '=?', $fields)) . ' WHERE id=?', [...$values, $id]);
            return $id;
        }
        return Database::exec('INSERT INTO proposal_templates (' . implode(', ', $fields) . ') VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')', $values);
    }

    public static function quotationDashboardStats(): array
    {
        try {
            $base = Database::one(
                "SELECT COUNT(*) total_quotes, COALESCE(SUM(final_amount),0) total_value,
                        COALESCE(SUM(CASE WHEN status='accepted' THEN final_amount ELSE 0 END),0) accepted_value,
                        COALESCE(SUM(CASE WHEN status IN ('draft','ready_for_review','sent_to_client','viewed_by_client','revision_requested','revised') THEN final_amount ELSE 0 END),0) pending_value,
                        COALESCE(AVG(final_amount),0) average_quote
                 FROM quotations"
            ) ?: [];
            $accepted = (int)(Database::one("SELECT COUNT(*) c FROM quotations WHERE status='accepted'")['c'] ?? 0);
            $total = (int)($base['total_quotes'] ?? 0);
            return [
                'total_quotes' => $total,
                'total_value' => (float)($base['total_value'] ?? 0),
                'accepted_value' => (float)($base['accepted_value'] ?? 0),
                'pending_value' => (float)($base['pending_value'] ?? 0),
                'average_quote' => (float)($base['average_quote'] ?? 0),
                'conversion_rate' => $total > 0 ? round($accepted * 100 / $total, 1) : 0,
                'by_city' => Database::query('SELECT city, COUNT(*) c, COALESCE(SUM(final_amount),0) value FROM quotations GROUP BY city ORDER BY c DESC LIMIT 8'),
                'by_package' => Database::query('SELECT COALESCE(p.name, "No package") package_name, COUNT(*) c FROM quotations q LEFT JOIN quotation_packages p ON p.id=q.package_id GROUP BY package_name ORDER BY c DESC LIMIT 8'),
                'expiring_soon' => Database::query("SELECT id, quote_number, client_name, valid_until, final_amount FROM quotations WHERE status NOT IN ('accepted','rejected','expired','converted_to_project') AND valid_until BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY) ORDER BY valid_until ASC LIMIT 8"),
            ];
        } catch (Throwable) {
            return ['total_quotes' => 0, 'total_value' => 0, 'accepted_value' => 0, 'pending_value' => 0, 'average_quote' => 0, 'conversion_rate' => 0, 'by_city' => [], 'by_package' => [], 'expiring_soon' => []];
        }
    }

    public static function quotationStatsFromRows(array $rows): array
    {
        $total = count($rows);
        $totalValue = 0.0;
        $acceptedValue = 0.0;
        $pendingValue = 0.0;
        $acceptedCount = 0;
        foreach ($rows as $row) {
            $amount = (float)($row['final_amount'] ?? 0);
            $totalValue += $amount;
            if (($row['status'] ?? '') === 'accepted') {
                $acceptedValue += $amount;
                $acceptedCount++;
            } elseif (in_array((string)($row['status'] ?? ''), ['draft', 'ready_for_review', 'sent_to_client', 'viewed_by_client', 'revision_requested', 'revised'], true)) {
                $pendingValue += $amount;
            }
        }
        return [
            'total_quotes' => $total,
            'total_value' => $totalValue,
            'accepted_value' => $acceptedValue,
            'pending_value' => $pendingValue,
            'average_quote' => $total > 0 ? $totalValue / $total : 0,
            'conversion_rate' => $total > 0 ? round($acceptedCount * 100 / $total, 1) : 0,
        ];
    }

    public static function listQuotations(array $filters = []): array
    {
        $where = [];
        $params = [];
        foreach (['status', 'city', 'designer_id', 'package_id', 'property_type'] as $field) {
            $value = trim((string)($filters[$field] ?? ''));
            if ($value !== '') {
                $where[] = 'q.' . $field . '=?';
                $params[] = $value;
            }
        }
        $search = trim((string)($filters['q'] ?? ''));
        if ($search !== '') {
            $where[] = '(q.client_name LIKE ? OR q.client_phone LIKE ? OR q.quote_number LIKE ? OR q.society_name LIKE ? OR p.full_name LIKE ?)';
            array_push($params, '%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%');
        }
        $sql = 'SELECT q.*, l.name AS lead_name, l.source AS lead_source, p.full_name AS designer_name, pkg.name AS package_name
                FROM quotations q
                LEFT JOIN leads l ON l.id=q.lead_id
                LEFT JOIN pros p ON p.id=q.designer_id
                LEFT JOIN quotation_packages pkg ON pkg.id=q.package_id';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        return Database::query($sql . ' ORDER BY q.updated_at DESC LIMIT 500', $params);
    }

    public static function userCanAccessQuotation(array $user, int $quoteId): bool
    {
        if (Auth::isAdmin($user)) {
            return true;
        }
        if (!Auth::isDesigner($user) || empty($user['pro_id'])) {
            return false;
        }
        $row = Database::one('SELECT id FROM quotations WHERE id=? AND designer_id=?', [$quoteId, (int)$user['pro_id']]);
        return (bool)$row;
    }

    public static function designerDashboardStats(int $designerId): array
    {
        return [
            'leads' => (int)(Database::one('SELECT COUNT(*) c FROM leads WHERE pro_id=?', [$designerId])['c'] ?? 0),
            'new_leads' => (int)(Database::one("SELECT COUNT(*) c FROM leads WHERE pro_id=? AND status='new'", [$designerId])['c'] ?? 0),
            'quotations' => (int)(Database::one('SELECT COUNT(*) c FROM quotations WHERE designer_id=?', [$designerId])['c'] ?? 0),
            'accepted_value' => (float)(Database::one("SELECT COALESCE(SUM(final_amount),0) v FROM quotations WHERE designer_id=? AND status='accepted'", [$designerId])['v'] ?? 0),
        ];
    }

    public static function designerProfileChecklist(int $designerId): array
    {
        $pro = Database::one('SELECT * FROM pros WHERE id=?', [$designerId]) ?: [];
        $portfolioCount = (int)(Database::one('SELECT COUNT(*) c FROM projects WHERE pro_id=?', [$designerId])['c'] ?? 0);
        $approvedPortfolioCount = (int)(Database::one("SELECT COUNT(*) c FROM projects WHERE pro_id=? AND COALESCE(moderation_status, 'APPROVED')='APPROVED'", [$designerId])['c'] ?? 0);
        $required = [
            'Firm / professional name' => trim((string)($pro['full_name'] ?? '')) !== '',
            'City and location' => trim((string)($pro['city'] ?? '')) !== '' && trim((string)($pro['office_address'] ?? '')) !== '',
            'Logo / profile image' => trim((string)($pro['profile_pic'] ?? '')) !== '',
            'Work type and specialization' => trim((string)($pro['primary_work_type'] ?? '')) !== '' && trim((string)($pro['specialization'] ?? '')) !== '',
            'Experience and starting price' => (int)($pro['years_experience'] ?? 0) > 0 && (float)($pro['starting_price'] ?? 0) > 0,
            'At least one portfolio work' => $portfolioCount > 0,
            'Admin-approved portfolio work' => $approvedPortfolioCount > 0,
        ];
        $missing = array_keys(array_filter($required, static fn(bool $done): bool => !$done));
        $verificationStatus = (string)($pro['verification_status_code'] ?? ((int)($pro['verification_status'] ?? 0) === 1 ? 'PROFESSIONAL_VERIFIED' : 'UNVERIFIED'));
        return [
            'completion_percent' => (int)round(((count($required) - count($missing)) / max(count($required), 1)) * 100),
            'missing' => $missing,
            'portfolio_count' => $portfolioCount,
            'approved_portfolio_count' => $approvedPortfolioCount,
            'profile_active' => !empty($pro['is_active']),
            'accepting_leads' => !isset($pro['accepting_leads']) || (int)$pro['accepting_leads'] === 1,
            'verification_status' => $verificationStatus,
            'listing_tier' => (string)($pro['listing_tier'] ?? (!empty($pro['is_premium']) ? 'PAID' : 'FREE')),
        ];
    }

    public static function designerSubscriptionStatus(array $user): array
    {
        $registration = Database::one(
            'SELECT * FROM designer_feature_registrations
             WHERE payment_status="paid" AND (user_id=? OR pro_id=?)
             ORDER BY paid_at DESC, id DESC
             LIMIT 1',
            [(int)($user['id'] ?? 0), (int)($user['pro_id'] ?? 0)]
        );
        if (!$registration || empty($registration['paid_at'])) {
            return [
                'has_subscription' => false,
                'is_expired' => false,
                'show_warning' => false,
                'days_left' => null,
                'expires_at' => null,
                'message' => 'Subscription status is managed by admin.',
            ];
        }

        $expiresAt = date('Y-m-d H:i:s', strtotime((string)$registration['paid_at'] . ' +30 days'));
        $today = new DateTimeImmutable('today');
        $expiryDay = new DateTimeImmutable(substr($expiresAt, 0, 10));
        $daysLeft = (int)$today->diff($expiryDay)->format('%r%a');
        $isExpired = $daysLeft < 0;
        return [
            'has_subscription' => true,
            'is_expired' => $isExpired,
            'show_warning' => !$isExpired && $daysLeft <= 3,
            'days_left' => max($daysLeft, 0),
            'expires_at' => $expiresAt,
            'message' => $isExpired
                ? 'Your subscription has expired. Renew to create new quotations.'
                : ($daysLeft <= 3 ? $daysLeft . ' day' . ($daysLeft === 1 ? '' : 's') . ' left in your subscription.' : 'Subscription active.'),
        ];
    }

    public static function designerCanCreateQuotation(array $user): bool
    {
        return !self::designerSubscriptionStatus($user)['is_expired'];
    }

    public static function getQuotation(int $id): ?array
    {
        $quote = Database::one(
            'SELECT q.*, l.name AS lead_name, l.requirement AS lead_requirement, l.source AS lead_source, p.full_name AS designer_name,
                    p.slug AS designer_slug, p.profile_pic AS designer_profile_pic, p.city AS designer_city, p.role AS designer_role,
                    p.years_experience, p.projects_delivered, p.rating, p.starting_price, p.specialization, p.profile_description,
                    pkg.name AS package_name, pkg.slug AS package_slug, pkg.description AS package_description, pkg.material_grade,
                    pkg.hardware_level, pkg.finish_level, pkg.warranty_years, pkg.design_support, pkg.supervision_level,
                    pkg.drawings_2d_count, pkg.views_3d_count, pkg.revision_count, pkg.timeline_range,
                    t.name AS template_name, t.cover_title, t.welcome_note, t.inclusions, t.exclusions, t.terms,
                    t.warranty_text, t.footer_note, t.logo_url, t.accent_color
             FROM quotations q
             LEFT JOIN leads l ON l.id=q.lead_id
             LEFT JOIN pros p ON p.id=q.designer_id
             LEFT JOIN quotation_packages pkg ON pkg.id=q.package_id
             LEFT JOIN proposal_templates t ON t.id=q.template_id
             WHERE q.id=?',
            [$id]
        );
        if (!$quote) {
            return null;
        }
        $quote['items'] = Database::query('SELECT * FROM quotation_items WHERE quotation_id=? ORDER BY sort_order ASC, id ASC', [$id]);
        $quote['activity'] = Database::query('SELECT a.*, u.username FROM quotation_activity_logs a LEFT JOIN users u ON u.id=a.performed_by WHERE a.quotation_id=? ORDER BY a.created_at DESC', [$id]);
        $quote['revisions'] = Database::query('SELECT id, quote_number, revision_number, status, final_amount, updated_at FROM quotations WHERE id=? OR parent_quote_id=? ORDER BY revision_number ASC, id ASC', [(int)($quote['parent_quote_id'] ?: $id), (int)($quote['parent_quote_id'] ?: $id)]);
        $quote['payment_schedule'] = self::decodeStructuredRows($quote['payment_schedule_json'] ?? []);
        $quote['portfolio'] = $quote['designer_id'] ? self::designerProposalPortfolio((int)$quote['designer_id']) : [];
        return $quote;
    }

    public static function getQuotationByToken(string $token): ?array
    {
        $row = Database::one('SELECT id FROM quotations WHERE proposal_token=?', [$token]);
        return $row ? self::getQuotation((int)$row['id']) : null;
    }

    public static function publicProposalData(string $token, bool $trackView = false): ?array
    {
        $quote = self::getQuotationByToken($token);
        if (!$quote) {
            return null;
        }
        if ($trackView) {
            Database::exec('INSERT INTO proposal_views (quotation_id, proposal_token, ip_address, user_agent) VALUES (?, ?, ?, ?)', [
                (int)$quote['id'],
                $token,
                substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 80),
                substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500),
            ]);
            if (!in_array($quote['status'], ['accepted', 'rejected', 'expired', 'converted_to_project'], true)) {
                self::updateQuotationStatus((int)$quote['id'], 'viewed_by_client', null, 'Viewed by client');
                $quote = self::getQuotation((int)$quote['id']) ?: $quote;
            }
        }
        unset($quote['vendor_cost'], $quote['margin_amount'], $quote['margin_percentage'], $quote['platform_commission'], $quote['internal_notes']);
        foreach ($quote['items'] as &$item) {
            unset($item['vendor_cost'], $item['margin_amount']);
        }
        unset($item);
        return $quote;
    }

    private static function designerProposalPortfolio(int $designerId): array
    {
        $rows = Database::query('SELECT project_name, slug, location, work_type, area_of_work, design_style, media_json FROM projects WHERE pro_id=? ORDER BY year_completed DESC, id DESC LIMIT 6', [$designerId]);
        foreach ($rows as &$row) {
            $row['media'] = self::parseJsonArray($row['media_json'] ?? []);
        }
        unset($row);
        return $rows;
    }

    public static function prefillQuotationFromLead(int $leadId): ?array
    {
        $lead = Database::one('SELECT * FROM leads WHERE id=?', [$leadId]);
        if (!$lead) {
            return null;
        }
        return [
            'lead_id' => (int)$lead['id'],
            'client_name' => $lead['name'],
            'client_phone' => $lead['phone'],
            'city' => $lead['city'],
            'society_name' => $lead['society_area'],
            'budget_range' => $lead['budget'],
            'scope_type' => $lead['requirement'],
            'designer_id' => $lead['pro_id'],
            'bhk' => $lead['floor_plan'],
            'client_notes' => $lead['requirement'],
        ];
    }

    public static function saveQuotation(array $data, ?int $id = null, ?int $userId = null): int
    {
        $settings = self::quotationSettings();
        $items = self::decodeStructuredRows($data['items_json'] ?? $data['items'] ?? []);
        if ($items === []) {
            throw new InvalidArgumentException('At least one room item is required.');
        }
        foreach (['client_name', 'client_phone', 'city', 'property_type', 'package_id'] as $field) {
            if (trim((string)($data[$field] ?? '')) === '') {
                throw new InvalidArgumentException(ucwords(str_replace('_', ' ', $field)) . ' is required.');
            }
        }
        $package = Database::one('SELECT * FROM quotation_packages WHERE id=?', [(int)$data['package_id']]);
        $data['design_fee_percentage'] = (float)($data['design_fee_percentage'] ?? $package['default_design_fee_percentage'] ?? $settings['default_design_fee_percentage'] ?? 3);
        $data['project_management_fee_percentage'] = (float)($data['project_management_fee_percentage'] ?? $package['default_project_management_fee_percentage'] ?? $settings['default_project_management_fee_percentage'] ?? 5);
        $data['platform_commission_percentage'] = (float)($data['platform_commission_percentage'] ?? $settings['default_platform_commission_percentage'] ?? 5);
        $data['payment_schedule'] = self::decodeStructuredRows($data['payment_schedule_json'] ?? $data['payment_schedule'] ?? $settings['default_payment_schedule'] ?? []);
        $totals = QuotationCalculator::calculateQuote($data, $items, $settings);
        if ($totals['final_amount'] < 0) {
            throw new InvalidArgumentException('Final quote amount cannot be negative.');
        }

        $validUntil = trim((string)($data['valid_until'] ?? ''));
        if ($validUntil === '') {
            $validUntil = date('Y-m-d', strtotime('+' . (int)($settings['default_proposal_validity_days'] ?? 15) . ' days'));
        }
        if (strtotime($validUntil) < strtotime(date('Y-m-d'))) {
            throw new InvalidArgumentException('Valid until date cannot be before today.');
        }

        $fields = [
            'lead_id', 'client_name', 'client_phone', 'client_email', 'city', 'locality', 'society_name',
            'property_type', 'bhk', 'carpet_area', 'builtup_area', 'possession_status', 'expected_start_date',
            'expected_handover_date', 'budget_range', 'design_style', 'scope_type', 'package_id', 'template_id',
            'designer_id', 'assigned_to_user_id', 'subtotal', 'design_fee', 'project_management_fee',
            'site_visit_fee', 'discount_amount', 'discount_percentage', 'gst_percentage', 'gst_amount',
            'final_amount', 'vendor_cost', 'margin_amount', 'margin_percentage', 'platform_commission',
            'payment_schedule_json', 'status', 'valid_until', 'internal_notes', 'client_notes', 'updated_by',
        ];
        $values = [
            !empty($data['lead_id']) ? (int)$data['lead_id'] : null,
            trim((string)$data['client_name']),
            trim((string)$data['client_phone']),
            trim((string)($data['client_email'] ?? '')) ?: null,
            trim((string)$data['city']),
            trim((string)($data['locality'] ?? '')) ?: null,
            trim((string)($data['society_name'] ?? '')) ?: null,
            trim((string)$data['property_type']),
            trim((string)($data['bhk'] ?? '')) ?: null,
            ($data['carpet_area'] ?? '') !== '' ? (float)$data['carpet_area'] : null,
            ($data['builtup_area'] ?? '') !== '' ? (float)$data['builtup_area'] : null,
            trim((string)($data['possession_status'] ?? '')) ?: null,
            ($data['expected_start_date'] ?? '') ?: null,
            ($data['expected_handover_date'] ?? '') ?: null,
            trim((string)($data['budget_range'] ?? '')) ?: null,
            trim((string)($data['design_style'] ?? '')) ?: null,
            trim((string)($data['scope_type'] ?? '')) ?: null,
            (int)$data['package_id'],
            !empty($data['template_id']) ? (int)$data['template_id'] : null,
            !empty($data['designer_id']) ? (int)$data['designer_id'] : null,
            !empty($data['assigned_to_user_id']) ? (int)$data['assigned_to_user_id'] : null,
            $totals['subtotal'],
            $totals['design_fee'],
            $totals['project_management_fee'],
            $totals['site_visit_fee'],
            $totals['discount_amount'],
            max(0.0, (float)($data['discount_percentage'] ?? 0)),
            max(0.0, (float)($data['gst_percentage'] ?? $settings['default_gst_percentage'] ?? 18)),
            $totals['gst_amount'],
            $totals['final_amount'],
            $totals['vendor_cost'],
            $totals['margin_amount'],
            $totals['margin_percentage'],
            $totals['platform_commission'],
            json_encode($totals['payment_schedule'], JSON_UNESCAPED_UNICODE),
            self::normalizeQuotationStatus((string)($data['status'] ?? 'draft')),
            $validUntil,
            trim((string)($data['internal_notes'] ?? '')) ?: null,
            trim((string)($data['client_notes'] ?? '')) ?: null,
            $userId,
        ];

        if ($id) {
            Database::exec('UPDATE quotations SET ' . implode(', ', array_map(static fn(string $field): string => $field . '=?', $fields)) . ' WHERE id=?', [...$values, $id]);
            Database::exec('DELETE FROM quotation_items WHERE quotation_id=?', [$id]);
            $quoteId = $id;
            self::logQuotationActivity($quoteId, 'updated', null, self::normalizeQuotationStatus((string)($data['status'] ?? 'draft')), 'Quotation updated', $userId);
        } else {
            $quoteNumber = self::nextQuoteNumber();
            $token = self::newProposalToken();
            $insertFields = array_merge(['quote_number', 'proposal_token', 'created_by'], $fields);
            $quoteId = Database::exec(
                'INSERT INTO quotations (' . implode(', ', $insertFields) . ') VALUES (' . implode(',', array_fill(0, count($insertFields), '?')) . ')',
                [$quoteNumber, $token, $userId, ...$values]
            );
            self::logQuotationActivity($quoteId, 'created', null, (string)($data['status'] ?? 'draft'), 'Quotation created', $userId);
        }

        $sort = 0;
        foreach ($totals['items'] as $item) {
            self::insertQuotationItem($quoteId, $item, $sort++);
        }
        if (!empty($data['lead_id'])) {
            self::updateLeadStatusSafe((int)$data['lead_id'], 'Quote created');
        }
        return $quoteId;
    }

    public static function createDesignerFeatureRegistration(array $data): int
    {
        $name = trim((string)($data['name'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string)($data['phone'] ?? '')) ?? '';
        if ($name === '' || strlen($phone) < 10 || empty($data['consent'])) {
            throw new InvalidArgumentException('Name, valid phone number, and consent are required.');
        }
        return Database::exec(
            'INSERT INTO designer_feature_registrations (name, phone, email, city, company_name, message, consent) VALUES (?, ?, ?, ?, ?, ?, 1)',
            [
                $name,
                $phone,
                trim((string)($data['email'] ?? '')) ?: null,
                trim((string)($data['city'] ?? '')) ?: null,
                trim((string)($data['company_name'] ?? '')) ?: null,
                trim((string)($data['message'] ?? '')) ?: null,
            ]
        );
    }

    public static function requestMobileProfessionalOtp(array $data): array
    {
        $name = trim((string)($data['name'] ?? ''));
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $phone = preg_replace('/\D+/', '', (string)($data['phone'] ?? '')) ?? '';
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($phone) < 10) {
            throw new InvalidArgumentException('Name, valid email, and valid mobile number are required.');
        }

        $pro = Database::one('SELECT id FROM pros WHERE email=? OR phone=? ORDER BY id DESC LIMIT 1', [$email, $phone]);
        $proId = $pro ? (int)$pro['id'] : self::createMobileProfessionalProfile($name, $email, $phone);
        $otp = (string)random_int(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', time() + 10 * 60);
        $otpId = Database::exec(
            'INSERT INTO mobile_professional_otps (pro_id, name, email, phone, otp_hash, expires_at) VALUES (?, ?, ?, ?, ?, ?)',
            [$proId, $name, $email, $phone, password_hash($otp, PASSWORD_DEFAULT), $expiresAt]
        );
        self::sendMobileProfessionalOtpEmail($email, $name, $otp);

        return [
            'otp_request_id' => $otpId,
            'pro_id' => $proId,
            'expires_in_seconds' => 600,
            'message' => 'OTP sent to your email address.',
        ];
    }

    public static function verifyMobileProfessionalOtp(array $data): array
    {
        $requestId = (int)($data['otp_request_id'] ?? 0);
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $otp = preg_replace('/\D+/', '', (string)($data['otp'] ?? '')) ?? '';
        if ($requestId <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($otp) !== 6) {
            throw new InvalidArgumentException('OTP request, email, and 6 digit OTP are required.');
        }

        $row = Database::one('SELECT * FROM mobile_professional_otps WHERE id=? AND email=? ORDER BY id DESC LIMIT 1', [$requestId, $email]);
        if (!$row) {
            throw new InvalidArgumentException('OTP request not found.');
        }
        if (!empty($row['verified_at'])) {
            $user = Database::one('SELECT id, username, email, role, pro_id FROM users WHERE id=?', [(int)$row['user_id']]);
            if ($user) {
                return $user;
            }
        }
        if (strtotime((string)$row['expires_at']) < time()) {
            throw new InvalidArgumentException('OTP has expired. Please request a new OTP.');
        }
        if ((int)$row['attempts'] >= 5) {
            throw new InvalidArgumentException('Too many incorrect OTP attempts. Please request a new OTP.');
        }
        if (!password_verify($otp, (string)$row['otp_hash'])) {
            Database::exec('UPDATE mobile_professional_otps SET attempts=attempts+1, updated_at=NOW() WHERE id=?', [(int)$row['id']]);
            throw new InvalidArgumentException('Incorrect OTP.');
        }

        $proId = (int)$row['pro_id'];
        if ($proId <= 0) {
            $proId = self::createMobileProfessionalProfile((string)$row['name'], (string)$row['email'], (string)$row['phone']);
        }
        $user = Database::one('SELECT id, username, email, role, pro_id FROM users WHERE pro_id=? AND role="designer" ORDER BY id DESC LIMIT 1', [$proId]);
        if (!$user) {
            $userId = self::createMobileProfessionalUser((string)$row['phone'], (string)$row['email'], $proId);
            $user = Database::one('SELECT id, username, email, role, pro_id FROM users WHERE id=?', [$userId]);
        }
        Database::exec(
            'UPDATE mobile_professional_otps SET user_id=?, pro_id=?, verified_at=NOW(), updated_at=NOW() WHERE id=?',
            [(int)($user['id'] ?? 0), $proId, (int)$row['id']]
        );

        return $user ?: [];
    }

    private static function createMobileProfessionalProfile(string $name, string $email, string $phone): int
    {
        $slug = self::uniqueProSlug(slugify($name . '-' . substr($phone, -4)));
        $id = Database::exec(
            'INSERT INTO pros (full_name, slug, role, phone, email, profile_description, service_summary, is_active, verification_status)
             VALUES (?, ?, "Designer", ?, ?, ?, ?, 0, 0)',
            [
                $name,
                $slug,
                $phone,
                $email,
                'Mobile app professional registration. Profile is pending onboarding.',
                'Pending onboarding for HomeInteriors360 professional dashboard.',
            ]
        );
        self::syncProfessionalUrlAlias($id);
        return $id;
    }

    private static function createMobileProfessionalUser(string $phone, string $email, int $proId): int
    {
        $base = substr(preg_replace('/\D+/', '', $phone) ?: 'designer', -10);
        $username = self::uniqueUsername($base ?: 'designer');
        return Database::exec(
            "INSERT INTO users (username, password_hash, email, role, pro_id, is_active) VALUES (?, ?, ?, 'designer', ?, 1)",
            [$username, password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT), $email, $proId]
        );
    }

    private static function sendMobileProfessionalOtpEmail(string $email, string $name, string $otp): void
    {
        $subject = 'Your HomeInteriors360 professional login OTP';
        $body = "Hello {$name},\n\nYour HomeInteriors360 professional account OTP is {$otp}.\n\nThis OTP is valid for 10 minutes. If you did not request this, please ignore this email.\n\nHomeInteriors360";
        $headers = [
            'From: HomeInteriors360 <noreply@homeinteriors360.com>',
            'Reply-To: noreply@homeinteriors360.com',
            'Content-Type: text/plain; charset=UTF-8',
        ];
        @mail($email, $subject, $body, implode("\r\n", $headers));
    }

    public static function createDesignerSubscriptionOrder(array $data): array
    {
        $name = trim((string)($data['name'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string)($data['phone'] ?? '')) ?? '';
        $password = (string)($data['password'] ?? '');
        if ($name === '' || strlen($phone) < 10 || empty($data['consent']) || strlen($password) < 8) {
            throw new InvalidArgumentException('Name, valid phone, consent, and minimum 8 character password are required.');
        }

        $order = razorpayCreateOrder(39900, 'designer_' . substr($phone, -10) . '_' . time(), [
            'module' => 'designer_subscription',
            'plan' => 'quotation_builder',
            'phone' => $phone,
        ]);

        $id = Database::exec(
            'INSERT INTO designer_feature_registrations (name, phone, email, city, company_name, message, consent, password_hash, razorpay_order_id, payment_status)
             VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, "pending")',
            [
                $name,
                $phone,
                trim((string)($data['email'] ?? '')) ?: null,
                trim((string)($data['city'] ?? '')) ?: null,
                trim((string)($data['company_name'] ?? '')) ?: null,
                trim((string)($data['message'] ?? '')) ?: null,
                password_hash($password, PASSWORD_DEFAULT),
                (string)$order['id'],
            ]
        );

        return [
            'registration_id' => $id,
            'order_id' => (string)$order['id'],
            'amount' => (int)($order['amount'] ?? 39900),
            'currency' => (string)($order['currency'] ?? 'INR'),
            'key_id' => defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : '',
        ];
    }

    public static function activateDesignerSubscription(string $orderId, string $paymentId, string $signature): array
    {
        if (!razorpaySignatureIsValid($orderId, $paymentId, $signature)) {
            throw new InvalidArgumentException('Payment signature mismatch.');
        }
        $registration = Database::one('SELECT * FROM designer_feature_registrations WHERE razorpay_order_id=?', [$orderId]);
        if (!$registration) {
            throw new InvalidArgumentException('Designer subscription order not found.');
        }
        if ((string)($registration['payment_status'] ?? '') === 'paid' && !empty($registration['user_id'])) {
            return Database::one('SELECT id, username, email, role, pro_id FROM users WHERE id=?', [(int)$registration['user_id']]) ?: [];
        }

        $proId = !empty($registration['pro_id']) ? (int)$registration['pro_id'] : self::createDesignerProFromRegistration($registration);
        $userId = !empty($registration['user_id']) ? (int)$registration['user_id'] : self::createDesignerUserFromRegistration($registration, $proId);
        Database::exec(
            'UPDATE designer_feature_registrations SET payment_status="paid", status="converted", razorpay_payment_id=?, razorpay_signature=?, pro_id=?, user_id=?, paid_at=NOW(), updated_at=NOW() WHERE id=?',
            [$paymentId, $signature, $proId, $userId, (int)$registration['id']]
        );
        return Database::one('SELECT id, username, email, role, pro_id FROM users WHERE id=?', [$userId]) ?: [];
    }

    private static function createDesignerProFromRegistration(array $registration): int
    {
        $name = trim((string)$registration['name']);
        $slugBase = slugify($name . '-' . substr((string)$registration['phone'], -4));
        $slug = self::uniqueProSlug($slugBase);
        return Database::exec(
            'INSERT INTO pros (full_name, slug, role, city, phone, email, profile_description, service_summary, is_active, verification_status)
             VALUES (?, ?, "Designer", ?, ?, ?, ?, ?, 1, 0)',
            [
                $name,
                $slug,
                $registration['city'] ?: null,
                $registration['phone'],
                $registration['email'] ?: null,
                'Registered for HomeInteriors360 Quotation Builder + Proposal Generator.',
                $registration['company_name'] ?: 'Interior design quotation and proposal services',
            ]
        );
    }

    private static function createDesignerUserFromRegistration(array $registration, int $proId): int
    {
        $base = preg_replace('/\D+/', '', (string)$registration['phone']) ?: slugify((string)$registration['name']);
        $username = self::uniqueUsername(substr($base, -10) ?: $base);
        return Database::exec(
            "INSERT INTO users (username, password_hash, email, role, pro_id, is_active) VALUES (?, ?, ?, 'designer', ?, 1)",
            [
                $username,
                (string)$registration['password_hash'],
                $registration['email'] ?: null,
                $proId,
            ]
        );
    }

    private static function uniqueProSlug(string $base): string
    {
        $slug = $base;
        $i = 2;
        while (Database::one('SELECT id FROM pros WHERE slug=?', [$slug])) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private static function uniquePortfolioSlug(string $base): string
    {
        $slug = $base ?: 'portfolio-' . time();
        $i = 2;
        while (Database::one('SELECT id FROM projects WHERE slug=?', [$slug])) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    private static function uniqueUsername(string $base): string
    {
        $username = $base;
        $i = 2;
        while (Database::one('SELECT id FROM users WHERE username=?', [$username])) {
            $username = $base . $i++;
        }
        return $username;
    }

    private static function insertQuotationItem(int $quoteId, array $item, int $sortOrder): void
    {
        Database::exec(
            'INSERT INTO quotation_items (quotation_id, room_name, category, item_name, description, material, finish, brand, unit_type, quantity, length, width, height, calculated_area, rate, amount, discount_amount, gst_percentage, gst_amount, vendor_cost, margin_amount, include_in_proposal, is_manual_override, sort_order, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $quoteId,
                trim((string)($item['room_name'] ?? 'Room')) ?: 'Room',
                trim((string)($item['category'] ?? 'Other')) ?: 'Other',
                trim((string)($item['item_name'] ?? 'Interior item')) ?: 'Interior item',
                trim((string)($item['description'] ?? '')) ?: null,
                trim((string)($item['material'] ?? '')) ?: null,
                trim((string)($item['finish'] ?? '')) ?: null,
                trim((string)($item['brand'] ?? '')) ?: null,
                trim((string)($item['unit_type'] ?? 'Per unit')) ?: 'Per unit',
                max(0.01, (float)($item['quantity'] ?? 1)),
                max(0.0, (float)($item['length'] ?? 0)),
                max(0.0, (float)($item['width'] ?? 0)),
                max(0.0, (float)($item['height'] ?? 0)),
                max(0.0, (float)($item['calculated_area'] ?? 0)),
                max(0.0, (float)($item['rate'] ?? 0)),
                max(0.0, (float)($item['amount'] ?? 0)),
                max(0.0, (float)($item['discount_amount'] ?? 0)),
                max(0.0, (float)($item['gst_percentage'] ?? 18)),
                max(0.0, (float)($item['gst_amount'] ?? 0)),
                max(0.0, (float)($item['vendor_cost'] ?? 0)),
                (float)($item['margin_amount'] ?? 0),
                !empty($item['include_in_proposal']) || !array_key_exists('include_in_proposal', $item) ? 1 : 0,
                !empty($item['is_manual_override']) ? 1 : 0,
                $sortOrder,
                trim((string)($item['notes'] ?? '')) ?: null,
            ]
        );
    }

    public static function duplicateQuotation(int $id, ?int $userId = null, bool $asRevision = false): int
    {
        $quote = self::getQuotation($id);
        if (!$quote) {
            throw new InvalidArgumentException('Quotation not found.');
        }
        $parentId = (int)($quote['parent_quote_id'] ?: $quote['id']);
        $revision = $asRevision ? (int)(Database::one('SELECT COALESCE(MAX(revision_number),0)+1 AS n FROM quotations WHERE id=? OR parent_quote_id=?', [$parentId, $parentId])['n'] ?? 1) : 0;
        $newQuoteNumber = $asRevision ? preg_replace('/-R\d+$/', '', (string)$quote['quote_number']) . '-R' . $revision : self::nextQuoteNumber();
        $fields = [
            'quote_number', 'revision_number', 'parent_quote_id', 'lead_id', 'client_name', 'client_phone', 'client_email', 'city', 'locality', 'society_name',
            'property_type', 'bhk', 'carpet_area', 'builtup_area', 'possession_status', 'expected_start_date', 'expected_handover_date',
            'budget_range', 'design_style', 'scope_type', 'package_id', 'template_id', 'designer_id', 'assigned_to_user_id', 'subtotal',
            'design_fee', 'project_management_fee', 'site_visit_fee', 'discount_amount', 'discount_percentage', 'gst_percentage',
            'gst_amount', 'final_amount', 'vendor_cost', 'margin_amount', 'margin_percentage', 'platform_commission',
            'payment_schedule_json', 'status', 'proposal_token', 'valid_until', 'internal_notes', 'client_notes', 'created_by', 'updated_by',
        ];
        $values = [];
        foreach ($fields as $field) {
            if ($field === 'quote_number') {
                $values[] = $newQuoteNumber;
            } elseif ($field === 'revision_number') {
                $values[] = $revision;
            } elseif ($field === 'parent_quote_id') {
                $values[] = $asRevision ? $parentId : null;
            } elseif ($field === 'status') {
                $values[] = $asRevision ? 'revised' : 'draft';
            } elseif ($field === 'proposal_token') {
                $values[] = self::newProposalToken();
            } elseif ($field === 'created_by' || $field === 'updated_by') {
                $values[] = $userId;
            } else {
                $values[] = $quote[$field] ?? null;
            }
        }
        $newId = Database::exec('INSERT INTO quotations (' . implode(', ', $fields) . ') VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')', $values);
        foreach ($quote['items'] as $index => $item) {
            self::insertQuotationItem($newId, $item, $index);
        }
        self::logQuotationActivity($newId, $asRevision ? 'revision_created' : 'duplicated', $quote['status'] ?? null, $asRevision ? 'revised' : 'draft', 'Created from ' . $quote['quote_number'], $userId);
        return $newId;
    }

    public static function updateQuotationStatus(int $id, string $status, ?int $userId = null, string $notes = ''): void
    {
        $quote = Database::one('SELECT status, lead_id FROM quotations WHERE id=?', [$id]);
        if (!$quote) {
            throw new InvalidArgumentException('Quotation not found.');
        }
        $newStatus = self::normalizeQuotationStatus($status);
        $timestampField = [
            'sent_to_client' => 'sent_at',
            'viewed_by_client' => 'viewed_at',
            'accepted' => 'accepted_at',
            'rejected' => 'rejected_at',
        ][$newStatus] ?? null;
        $sql = 'UPDATE quotations SET status=?, updated_by=?';
        $params = [$newStatus, $userId];
        if ($timestampField) {
            $sql .= ', ' . $timestampField . '=NOW()';
        }
        $sql .= ' WHERE id=?';
        $params[] = $id;
        Database::exec($sql, $params);
        self::logQuotationActivity($id, 'status_changed', (string)$quote['status'], $newStatus, $notes, $userId);
        if (!empty($quote['lead_id'])) {
            self::updateLeadStatusSafe((int)$quote['lead_id'], ucwords(str_replace('_', ' ', $newStatus)));
        }
    }

    public static function deleteQuotation(int $id): void
    {
        Database::exec('DELETE FROM quotations WHERE id=?', [$id]);
    }

    private static function normalizeQuotationStatus(string $status): string
    {
        $status = strtolower(trim(str_replace([' ', '-'], '_', $status)));
        $allowed = ['draft', 'ready_for_review', 'sent_to_client', 'viewed_by_client', 'revision_requested', 'revised', 'accepted', 'rejected', 'expired', 'converted_to_project'];
        if (!in_array($status, $allowed, true)) {
            throw new InvalidArgumentException('Invalid quotation status.');
        }
        return $status;
    }

    private static function nextQuoteNumber(): string
    {
        $year = date('Y');
        $row = Database::one("SELECT COUNT(*) + 1 AS n FROM quotations WHERE quote_number LIKE ?", ['QTN-' . $year . '-%']);
        return 'QTN-' . $year . '-' . str_pad((string)(int)($row['n'] ?? 1), 4, '0', STR_PAD_LEFT) . '-R0';
    }

    private static function newProposalToken(): string
    {
        do {
            $token = bin2hex(random_bytes(24));
            $exists = Database::one('SELECT id FROM quotations WHERE proposal_token=?', [$token]);
        } while ($exists);
        return $token;
    }

    private static function logQuotationActivity(int $quoteId, string $action, ?string $oldStatus, ?string $newStatus, string $notes = '', ?int $userId = null): void
    {
        Database::exec(
            'INSERT INTO quotation_activity_logs (quotation_id, action, old_status, new_status, notes, performed_by, performed_by_type) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$quoteId, $action, $oldStatus, $newStatus, $notes ?: null, $userId, $userId ? 'admin' : 'system']
        );
    }

    private static function updateLeadStatusSafe(int $leadId, string $note): void
    {
        $status = str_contains(strtolower($note), 'accepted') || str_contains(strtolower($note), 'converted') ? 'converted' : 'contacted';
        try {
            Database::exec('UPDATE leads SET status=?, updated_at=NOW() WHERE id=?', [$status, $leadId]);
        } catch (Throwable) {
            // Lead status enum differs in older installs; quotation activity remains the durable log.
        }
    }
}
