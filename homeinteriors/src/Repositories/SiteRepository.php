<?php

declare(strict_types=1);

final class SiteRepository
{
    private static ?array $contentCache = null;

    private static function parseJsonArray(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('strval', $value), static fn(string $v): bool => trim($v) !== ''));
        }
        $decoded = json_decode((string)$value, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map('strval', $decoded), static fn(string $v): bool => trim($v) !== ''));
        }
        return [];
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
                "SELECT id, full_name, slug, role, specialization, profile_pic, rating, verification_status, years_experience, city, primary_work_type, primary_work_area,
                        COALESCE(NULLIF(projects_delivered,0), (SELECT COUNT(*) FROM projects px WHERE px.pro_id=pros.id)) AS projects_delivered
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
        return array_map(static fn(array $row): string => (string)$row['city'], $rows);
    }

    public static function requirementOptions(): array
    {
        $rows = Database::query("SELECT DISTINCT work_type FROM projects WHERE work_type IS NOT NULL AND work_type <> '' ORDER BY work_type ASC");
        $options = array_map(static fn(array $r): string => (string)$r['work_type'], $rows);
        return $options ?: ['Kitchen', 'Wardrobe', 'Full Home'];
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
                       pros.verification_status, pros.rating, pros.years_experience, pros.starting_price, pros.min_project_value, pros.max_project_value,
                       pros.city, pros.primary_work_type, pros.primary_work_area,
                       COALESCE(NULLIF(pros.projects_delivered,0), prj.project_count, 0) AS projects_delivered
                FROM pros
                LEFT JOIN (
                  SELECT pro_id, COUNT(*) AS project_count FROM projects GROUP BY pro_id
                ) prj ON prj.pro_id = pros.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY {$orderBy}";

        return Database::query($sql, $params);
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

        $row['media_json'] = self::parseJsonArray($row['media_json'] ?? '[]');
        $row['materials_json'] = self::parseJsonArray($row['materials_json'] ?? '[]');

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
             ORDER BY pros.created_at DESC'
        );
    }

    public static function createProfessional(array $data): int
    {
        return Database::exec(
            'INSERT INTO pros (
                full_name, slug, profile_pic, cover_photo, role, profile_description, specialization, primary_work_type, primary_work_area,
                verification_status, rating, years_experience, projects_delivered, starting_price, min_project_value, max_project_value,
                consultation_fee, city, service_areas, materials_json, design_styles_json, languages_json, certifications_json,
                response_time_hours, bio, why_work_with_me, offerings_json, is_active
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
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
                !empty($data['verification_status']) ? 1 : 0,
                isset($data['rating']) ? (float)$data['rating'] : 0,
                isset($data['years_experience']) ? (int)$data['years_experience'] : 0,
                isset($data['projects_delivered']) ? (int)$data['projects_delivered'] : 0,
                isset($data['starting_price']) ? (float)$data['starting_price'] : 0,
                isset($data['min_project_value']) ? (float)$data['min_project_value'] : null,
                isset($data['max_project_value']) ? (float)$data['max_project_value'] : null,
                isset($data['consultation_fee']) ? (float)$data['consultation_fee'] : null,
                $data['city'] ?? null,
                json_encode(self::parseJsonArray($data['service_areas'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['materials_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['design_styles_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['languages_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['certifications_json'] ?? []), JSON_UNESCAPED_UNICODE),
                isset($data['response_time_hours']) ? (int)$data['response_time_hours'] : null,
                $data['bio'] ?? null,
                $data['why_work_with_me'] ?? null,
                json_encode(self::parseJsonArray($data['offerings_json'] ?? []), JSON_UNESCAPED_UNICODE),
                isset($data['is_active']) ? (int)(bool)$data['is_active'] : 1,
            ]
        );
    }

    public static function updateProfessional(int $id, array $data): void
    {
        Database::exec(
            'UPDATE pros SET
                full_name=?, slug=?, profile_pic=?, cover_photo=?, role=?, profile_description=?, specialization=?, primary_work_type=?, primary_work_area=?,
                verification_status=?, rating=?, years_experience=?, projects_delivered=?, starting_price=?, min_project_value=?, max_project_value=?, consultation_fee=?, city=?,
                service_areas=?, materials_json=?, design_styles_json=?, languages_json=?, certifications_json=?, response_time_hours=?,
                bio=?, why_work_with_me=?, offerings_json=?, is_active=?, updated_at=NOW()
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
                !empty($data['verification_status']) ? 1 : 0,
                isset($data['rating']) ? (float)$data['rating'] : 0,
                isset($data['years_experience']) ? (int)$data['years_experience'] : 0,
                isset($data['projects_delivered']) ? (int)$data['projects_delivered'] : 0,
                isset($data['starting_price']) ? (float)$data['starting_price'] : 0,
                isset($data['min_project_value']) ? (float)$data['min_project_value'] : null,
                isset($data['max_project_value']) ? (float)$data['max_project_value'] : null,
                isset($data['consultation_fee']) ? (float)$data['consultation_fee'] : null,
                $data['city'] ?? null,
                json_encode(self::parseJsonArray($data['service_areas'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['materials_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['design_styles_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['languages_json'] ?? []), JSON_UNESCAPED_UNICODE),
                json_encode(self::parseJsonArray($data['certifications_json'] ?? []), JSON_UNESCAPED_UNICODE),
                isset($data['response_time_hours']) ? (int)$data['response_time_hours'] : null,
                $data['bio'] ?? null,
                $data['why_work_with_me'] ?? null,
                json_encode(self::parseJsonArray($data['offerings_json'] ?? []), JSON_UNESCAPED_UNICODE),
                isset($data['is_active']) ? (int)(bool)$data['is_active'] : 1,
                $id,
            ]
        );
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
        return Database::exec(
            'INSERT INTO projects (
                pro_id, slug, project_name, project_description, total_cost, bhk_type, year_completed, timeline_months, project_duration_label,
                location, work_type, area_of_work, materials_json, media_json, video_url, design_style, team_size, warranty_years,
                testimonial_client_name, testimonial_text, testimonial_rating
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
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
            ]
        );
    }

    public static function updatePortfolio(int $id, array $data): void
    {
        Database::exec(
            'UPDATE projects SET
                pro_id=?, slug=?, project_name=?, project_description=?, total_cost=?, bhk_type=?, year_completed=?, timeline_months=?, project_duration_label=?,
                location=?, work_type=?, area_of_work=?, materials_json=?, media_json=?, video_url=?, design_style=?, team_size=?, warranty_years=?,
                testimonial_client_name=?, testimonial_text=?, testimonial_rating=?, updated_at=NOW()
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
                $id,
            ]
        );
    }

    public static function deletePortfolio(int $id): void
    {
        Database::exec('DELETE FROM projects WHERE id=?', [$id]);
    }

    public static function professionalOptions(): array
    {
        return Database::query('SELECT id, full_name, slug FROM pros ORDER BY full_name ASC');
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
