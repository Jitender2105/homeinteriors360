<?php

declare(strict_types=1);

final class SiteRepository
{
    private static ?array $contentCache = null;

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
                "SELECT id, full_name, slug, role, specialization, profile_pic, rating, verification_status, years_experience, city, primary_work_type, primary_work_area
                 FROM pros
                 WHERE is_active = 1
                 ORDER BY verification_status DESC, rating DESC, updated_at DESC
                 LIMIT 12"
            ),
            'city_options' => self::cityOptions(),
            'requirement_options' => self::requirementOptions(),
        ];
    }

    public static function cityOptions(): array
    {
        $rows = Database::query("SELECT DISTINCT city FROM pros WHERE is_active = 1 AND city IS NOT NULL AND city <> '' ORDER BY city");
        if ($rows === []) {
            return ['Delhi', 'Gurgaon', 'Noida'];
        }
        return array_map(static fn(array $row): string => (string)$row['city'], $rows);
    }

    public static function requirementOptions(): array
    {
        $rows = Database::query("SELECT DISTINCT work_type FROM projects WHERE work_type IS NOT NULL AND work_type <> '' ORDER BY work_type ASC");
        $options = array_map(static fn(array $r): string => (string)$r['work_type'], $rows);
        if ($options === []) {
            return ['Kitchen', 'Wardrobe', 'Full Home'];
        }
        return $options;
    }

    public static function proFilterOptions(): array
    {
        return [
            'roles' => array_map(static fn(array $r): string => (string)$r['role'], Database::query("SELECT DISTINCT role FROM pros WHERE is_active=1 ORDER BY role")),
            'cities' => array_map(static fn(array $r): string => (string)$r['city'], Database::query("SELECT DISTINCT city FROM pros WHERE is_active=1 AND city IS NOT NULL AND city <> '' ORDER BY city")),
            'work_types' => array_map(static fn(array $r): string => (string)$r['primary_work_type'], Database::query("SELECT DISTINCT primary_work_type FROM pros WHERE is_active=1 AND primary_work_type IS NOT NULL AND primary_work_type <> '' ORDER BY primary_work_type")),
            'work_areas' => array_map(static fn(array $r): string => (string)$r['primary_work_area'], Database::query("SELECT DISTINCT primary_work_area FROM pros WHERE is_active=1 AND primary_work_area IS NOT NULL AND primary_work_area <> '' ORDER BY primary_work_area")),
        ];
    }

    public static function listPros(array $filters = []): array
    {
        $where = ['is_active = 1'];
        $params = [];

        if (!empty($filters['role'])) {
            $where[] = 'role = ?';
            $params[] = $filters['role'];
        }
        if (!empty($filters['city'])) {
            $where[] = 'city = ?';
            $params[] = $filters['city'];
        }
        if (!empty($filters['work_type'])) {
            $where[] = 'primary_work_type = ?';
            $params[] = $filters['work_type'];
        }
        if (!empty($filters['work_area'])) {
            $where[] = 'primary_work_area = ?';
            $params[] = $filters['work_area'];
        }
        if (isset($filters['budget_min']) && $filters['budget_min'] !== '') {
            $where[] = 'starting_price >= ?';
            $params[] = (float)$filters['budget_min'];
        }
        if (isset($filters['budget_max']) && $filters['budget_max'] !== '') {
            $where[] = 'starting_price <= ?';
            $params[] = (float)$filters['budget_max'];
        }

        $sql = "SELECT id, full_name, slug, role, profile_description, specialization, profile_pic, cover_photo, verification_status, rating,
                       years_experience, starting_price, min_project_value, max_project_value, city, primary_work_type, primary_work_area
                FROM pros
                WHERE " . implode(' AND ', $where) . "
                ORDER BY verification_status DESC, rating DESC, updated_at DESC";

        return Database::query($sql, $params);
    }

    public static function getProBySlug(string $slug): ?array
    {
        $pro = Database::one('SELECT * FROM pros WHERE slug = ? AND is_active = 1', [$slug]);
        if (!$pro) {
            return null;
        }

        $pro['service_areas'] = json_decode((string)($pro['service_areas'] ?? '[]'), true) ?: [];
        $pro['offerings_json'] = json_decode((string)($pro['offerings_json'] ?? '[]'), true) ?: [];
        $pro['materials_json'] = json_decode((string)($pro['materials_json'] ?? '[]'), true) ?: [];
        $pro['design_styles_json'] = json_decode((string)($pro['design_styles_json'] ?? '[]'), true) ?: [];
        $pro['languages_json'] = json_decode((string)($pro['languages_json'] ?? '[]'), true) ?: [];
        $pro['certifications_json'] = json_decode((string)($pro['certifications_json'] ?? '[]'), true) ?: [];

        return $pro;
    }

    public static function proProfileData(int $proId): array
    {
        $projects = Database::query(
            'SELECT id, slug, project_name, project_description, total_cost, bhk_type, year_completed, timeline_months, project_duration_label,
                    location, work_type, area_of_work, materials_json, media_json, video_url, design_style, team_size, warranty_years,
                    testimonial_client_name, testimonial_text, testimonial_rating
             FROM projects WHERE pro_id = ? ORDER BY year_completed DESC, id DESC',
            [$proId]
        );

        foreach ($projects as &$project) {
            $project['media_json'] = json_decode((string)($project['media_json'] ?? '[]'), true) ?: [];
            $project['materials_json'] = json_decode((string)($project['materials_json'] ?? '[]'), true) ?: [];
        }
        unset($project);

        $reviews = Database::query(
            'SELECT id, client_name, rating, review_text, verified_purchase, work_type, area_of_work, materials_highlight, photos_json, created_at
             FROM reviews WHERE pro_id = ? ORDER BY created_at DESC',
            [$proId]
        );

        foreach ($reviews as &$review) {
            $review['photos_json'] = json_decode((string)($review['photos_json'] ?? '[]'), true) ?: [];
        }
        unset($review);

        return [
            'projects' => $projects,
            'reviews' => $reviews,
        ];
    }

    public static function getProjectBySlug(string $slug): ?array
    {
        $row = Database::one(
            'SELECT p.*, pr.id AS pro_id, pr.full_name AS pro_name, pr.slug AS pro_slug, pr.profile_pic AS pro_profile_pic,
                    pr.role AS pro_role, pr.city AS pro_city, pr.primary_work_type AS pro_work_type, pr.primary_work_area AS pro_work_area
             FROM projects p
             JOIN pros pr ON pr.id = p.pro_id
             WHERE p.slug = ? AND pr.is_active = 1',
            [$slug]
        );

        if (!$row) {
            return null;
        }

        $row['media_json'] = json_decode((string)($row['media_json'] ?? '[]'), true) ?: [];
        $row['materials_json'] = json_decode((string)($row['materials_json'] ?? '[]'), true) ?: [];

        return $row;
    }

    public static function listOtherProjectsByPro(int $proId, string $excludeSlug): array
    {
        $rows = Database::query(
            'SELECT id, slug, project_name, total_cost, location, work_type, area_of_work, media_json
             FROM projects
             WHERE pro_id = ? AND slug <> ?
             ORDER BY year_completed DESC, id DESC
             LIMIT 6',
            [$proId, $excludeSlug]
        );

        foreach ($rows as &$row) {
            $row['media_json'] = json_decode((string)($row['media_json'] ?? '[]'), true) ?: [];
        }
        unset($row);

        return $rows;
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
            'INSERT INTO leads (name, phone, city, requirement, pro_id, source, status, floor_plan, package_tier, rooms_json, estimate)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['name'],
                $data['phone'],
                $data['city'],
                $data['requirement'],
                isset($data['pro_id']) ? (int)$data['pro_id'] : null,
                $data['source'] ?? 'homepage',
                $data['status'] ?? 'new',
                $data['floor_plan'] ?? null,
                $data['package_tier'] ?? null,
                isset($data['rooms']) ? json_encode($data['rooms'], JSON_UNESCAPED_UNICODE) : null,
                isset($data['estimate']) ? (float)$data['estimate'] : null,
            ]
        );
    }

    public static function listLeads(): array
    {
        return Database::query(
            'SELECT l.*, p.full_name AS pro_name
             FROM leads l
             LEFT JOIN pros p ON p.id = l.pro_id
             ORDER BY l.created_at DESC LIMIT 500'
        );
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
        ];
    }

    public static function setProVerification(int $proId, bool $isVerified): void
    {
        Database::exec('UPDATE pros SET verification_status = ?, updated_at = NOW() WHERE id = ?', [$isVerified ? 1 : 0, $proId]);
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
}
