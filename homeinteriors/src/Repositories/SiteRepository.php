<?php

declare(strict_types=1);

final class SiteRepository
{
    private static ?array $contentCache = null;

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
            'property_projects' => (int)(Database::one('SELECT COUNT(*) AS c FROM real_estate_projects WHERE is_active = 1')['c'] ?? 0),
            'property_enquiries' => (int)(Database::one("SELECT COUNT(*) AS c FROM property_enquiries WHERE status = 'new'")['c'] ?? 0),
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
            return $projectId;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
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
}
