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
        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return [];
            }
            if (str_contains($trimmed, "\n")) {
                return array_values(array_filter(array_map('trim', preg_split('/\R+/', $trimmed) ?: []), static fn(string $v): bool => $v !== ''));
            }
            if (str_contains($trimmed, ',')) {
                return array_values(array_filter(array_map('trim', explode(',', $trimmed)), static fn(string $v): bool => $v !== ''));
            }
        }
        $decoded = json_decode((string)$value, true);
        if (is_array($decoded)) {
            return array_values(array_filter(array_map('strval', $decoded), static fn(string $v): bool => trim($v) !== ''));
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
                "SELECT id, full_name, slug, role, specialization, profile_pic, rating, verification_status, is_premium, years_experience, city, primary_work_type, primary_work_area,
                        COALESCE(NULLIF(projects_delivered,0), (SELECT COUNT(*) FROM projects px WHERE px.pro_id=pros.id)) AS projects_delivered
                 FROM pros
                 WHERE is_active = 1
                 ORDER BY is_premium DESC, verification_status DESC, rating DESC, updated_at DESC
                 LIMIT 12"
            ),
            'featured_projects' => Database::query(
                "SELECT p.id, p.slug, p.project_name, p.project_description, p.total_cost, p.location, p.work_type, p.area_of_work, p.media_json,
                        pr.full_name AS pro_name, pr.slug AS pro_slug, pr.profile_pic AS pro_profile_pic, pr.city AS pro_city
                 FROM projects p
                 JOIN pros pr ON pr.id = p.pro_id
                 WHERE pr.is_active = 1
                 ORDER BY pr.is_premium DESC, p.year_completed DESC, p.created_at DESC
                 LIMIT 8"
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
             WHERE pro_id IN ({$placeholders})
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
                       pros.verification_status, pros.is_premium, pros.rating, pros.years_experience, pros.starting_price, pros.min_project_value, pros.max_project_value,
                       pros.city, pros.primary_work_type, pros.primary_work_area,
                       COALESCE(NULLIF(pros.projects_delivered,0), prj.project_count, 0) AS projects_delivered
                FROM pros
                LEFT JOIN (
                  SELECT pro_id, COUNT(*) AS project_count FROM projects GROUP BY pro_id
                ) prj ON prj.pro_id = pros.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY pros.is_premium DESC, {$orderBy}";

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
             ORDER BY pros.is_premium DESC, pros.created_at DESC'
        );
    }

    public static function createProfessional(array $data): int
    {
        return Database::exec(
            'INSERT INTO pros (
                full_name, slug, profile_pic, cover_photo, role, profile_description, specialization, primary_work_type, primary_work_area,
                verification_status, is_premium, rating, years_experience, projects_delivered, starting_price, min_project_value, max_project_value,
                consultation_fee, city, office_address, phone, email, website_url, founded_year, team_size, office_hours, client_count,
                service_summary, service_areas, materials_json, design_styles_json, languages_json, certifications_json, process_steps_json,
                awards_json, faq_json, response_time_hours, bio, why_work_with_me, offerings_json, google_business_url, is_active
             ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
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
                !empty($data['is_premium']) ? 1 : 0,
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
    }

    public static function updateProfessional(int $id, array $data): void
    {
        Database::exec(
            'UPDATE pros SET
                full_name=?, slug=?, profile_pic=?, cover_photo=?, role=?, profile_description=?, specialization=?, primary_work_type=?, primary_work_area=?,
                verification_status=?, is_premium=?, rating=?, years_experience=?, projects_delivered=?, starting_price=?, min_project_value=?, max_project_value=?, consultation_fee=?, city=?,
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
                !empty($data['verification_status']) ? 1 : 0,
                !empty($data['is_premium']) ? 1 : 0,
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

    public static function leadMarketplaceCounts(string $dateFilter = 'last_30_days', ?string $startDate = null, ?string $endDate = null): array
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
            $price = self::leadPriceForCount($count, true);
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
            ];
        }
        return $cards;
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
        $dateFilter = (string)($item['date_filter'] ?? 'last_30_days');
        $startDate = isset($item['start_date']) ? (string)$item['start_date'] : null;
        $endDate = isset($item['end_date']) ? (string)$item['end_date'] : null;
        $count = self::countLeadsForCriteria($criteria, $dateFilter, $startDate, $endDate);
        $price = self::leadPriceForCount($count, $firstTimeEligible);
        return [
            'id' => hash('sha256', json_encode([$criteria, $dateFilter, $startDate, $endDate])),
            'filter_name' => (string)($item['filter_name'] ?? self::criteriaLabel($criteria)),
            'criteria' => $criteria,
            'date_filter' => $dateFilter,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'lead_count' => $count,
            'price_total' => $price['total'],
            'pricing' => $price,
        ];
    }

    public static function leadCartSummary(array $cart, ?string $couponCode = null, bool $firstTimeEligible = true): array
    {
        $items = array_values(array_map(static fn(array $item): array => self::normalizeLeadCartItem($item, $firstTimeEligible), $cart));
        $subtotal = (float)array_sum(array_column($items, 'price_total'));
        $leadCount = (int)array_sum(array_column($items, 'lead_count'));
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
            'coupon' => $coupon,
            'discount_amount' => $discount,
            'grand_total' => max($subtotal - $discount, 0),
            'first_time_eligible' => $firstTimeEligible,
        ];
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
                        ], JSON_UNESCAPED_UNICODE),
                        $normalized['date_filter'],
                        $normalized['lead_count'],
                        $normalized['price_total'],
                        json_encode($normalized['pricing'], JSON_UNESCAPED_UNICODE),
                    ]
                );
            }
        }
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
        $criteria = is_array($filter['criteria'] ?? null) ? $filter['criteria'] : [];
        $leads = self::leadRowsForDateFilter((string)($item['date_filter'] ?? 'last_30_days'), $filter['start_date'] ?? null, $filter['end_date'] ?? null);
        return array_values(array_filter($leads, static fn(array $lead): bool => self::leadMatchesCriteria($lead, $criteria)));
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
}
