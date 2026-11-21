<?php

declare(strict_types=1);

final class SiteRepository
{
    public const SINGLE_LEAD_PRICE = 199.00;
    public const LEAD_PRICE_TIERS = [199.00, 399.00, 599.00, 799.00, 999.00];
    public const MAX_BUYERS_PER_LEAD = 3;

    private static ?array $contentCache = null;
    private static array $tableColumnCache = [];

    /**
     * Public launch markets for the interior-design landing page. Keep this list
     * intentionally small: it is an editorial service area, not a blanket rule
     * that makes every home-service category available in every city.
     */
    private const FEATURED_INTERIOR_DESIGN_CITIES = ['Mumbai', 'Pune', 'Chennai', 'Bangalore', 'Hyderabad'];

    public static function boqTemplates(): array
    {
        return [
            ['key' => '1bhk', 'name' => '1 BHK', 'area' => 550, 'rooms' => ['Living room', 'Modular kitchen', 'Master bedroom']],
            ['key' => '2bhk', 'name' => '2 BHK', 'area' => 850, 'rooms' => ['Living room', 'Modular kitchen', 'Master bedroom', 'Bedroom 2']],
            ['key' => '3bhk', 'name' => '3 BHK', 'area' => 1200, 'rooms' => ['Living room', 'Modular kitchen', 'Master bedroom', 'Bedroom 2', 'Bedroom 3']],
            ['key' => '4bhk', 'name' => '4 BHK', 'area' => 1650, 'rooms' => ['Living room', 'Dining area', 'Modular kitchen', 'Master bedroom', 'Bedroom 2', 'Bedroom 3', 'Guest bedroom']],
            ['key' => 'kitchen', 'name' => 'Modular kitchen', 'area' => 110, 'rooms' => ['Modular kitchen']],
            ['key' => 'wardrobe', 'name' => 'Wardrobe', 'area' => 80, 'rooms' => ['Master bedroom']],
            ['key' => 'false-ceiling', 'name' => 'False ceiling', 'area' => 350, 'rooms' => ['Living room']],
            ['key' => 'blank', 'name' => 'Blank BOQ', 'area' => 0, 'rooms' => []],
        ];
    }

    public static function createAnonymousBoqDraft(array $data): array
    {
        $templateKey = strtolower(trim((string)($data['template_key'] ?? '')));
        $template = null;
        foreach (self::boqTemplates() as $candidate) {
            if ($candidate['key'] === $templateKey) {
                $template = $candidate;
                break;
            }
        }
        $city = trim((string)($data['city'] ?? ''));
        $tier = trim((string)($data['quality_tier'] ?? ''));
        if (!$template || $city === '' || !in_array($tier, ['Economy', 'Standard', 'Premium', 'Luxury'], true)) {
            throw new InvalidArgumentException('Choose a template, city, and quality tier to start your BOQ.');
        }
        $area = max(0, (float)($data['carpet_area'] ?? 0));
        $scale = !empty($template['area']) && $area > 0 ? $area / (float)$template['area'] : 1;
        $packages = self::quotationPackages(true);
        $packageId = 0;
        foreach ($packages as $package) {
            if (strcasecmp((string)$package['material_grade'], $tier) === 0 || strcasecmp((string)$package['name'], $tier) === 0) {
                $packageId = (int)$package['id'];
                break;
            }
        }
        $rates = self::quotationRateCards(['city' => $city, 'package_id' => $packageId]);
        $byCategory = [];
        foreach ($rates as $rate) {
            $byCategory[strtolower((string)$rate['category'])] ??= $rate;
        }
        $defaultItems = self::boqTemplateItems($templateKey, $template['rooms'], $byCategory, $scale);
        $draft = [
            'template_key' => $templateKey,
            'template_name' => $template['name'],
            'city' => $city,
            'quality_tier' => $tier,
            'carpet_area' => $area ?: null,
            'client_name' => '',
            'client_phone' => '',
            'client_email' => '',
            'site_address' => '',
            'property_type' => 'Apartment',
            'bhk' => strtoupper(str_replace('bhk', 'BHK', $templateKey)),
            'project_stage' => 'New construction',
            'quote_date' => date('Y-m-d'),
            'valid_until' => date('Y-m-d', strtotime('+15 days')),
            'items' => $defaultItems,
            'discount_amount' => 0,
            'discount_percentage' => 0,
            'gst_percentage' => 18,
            'payment_schedule' => [['label' => 'Booking amount', 'percentage' => 10], ['label' => 'Design sign-off', 'percentage' => 40], ['label' => 'Material delivery', 'percentage' => 40], ['label' => 'Handover', 'percentage' => 10]],
        ];
        $draft['totals'] = QuotationCalculator::calculateQuote($draft, $defaultItems, self::quotationSettings());
        $token = bin2hex(random_bytes(24));
        $sessionToken = self::boqAnonymousSessionToken();
        Database::exec('INSERT INTO boq_anonymous_drafts (public_token, session_token, template_key, city, quality_tier, carpet_area, draft_json, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY))', [$token, $sessionToken, $templateKey, $city, $tier, $area ?: null, json_encode($draft, JSON_UNESCAPED_UNICODE),]);
        self::trackBoqEvent('quote_created_anon', $token, ['template' => $templateKey, 'city' => $city, 'tier' => $tier]);
        return ['token' => $token, 'draft' => $draft];
    }

    public static function getAnonymousBoqDraft(string $token): ?array
    {
        $row = Database::one('SELECT * FROM boq_anonymous_drafts WHERE public_token=? AND expires_at>NOW()', [$token]);
        if (!$row || !hash_equals((string)$row['session_token'], self::boqAnonymousSessionToken(false))) {
            return null;
        }
        $draft = json_decode((string)$row['draft_json'], true);
        return is_array($draft) ? ['token' => $token, 'draft' => $draft] : null;
    }

    public static function saveAnonymousBoqDraft(string $token, array $data): array
    {
        $stored = self::getAnonymousBoqDraft($token);
        if (!$stored) {
            throw new InvalidArgumentException('This sample BOQ is unavailable. Please start a new one.');
        }
        $draft = array_merge($stored['draft'], array_intersect_key($data, array_flip(['client_name', 'client_phone', 'client_email', 'site_address', 'property_type', 'bhk', 'carpet_area', 'project_stage', 'quote_date', 'valid_until', 'discount_amount', 'discount_percentage', 'gst_percentage', 'payment_schedule', 'items'])));
        $items = self::decodeStructuredRows($draft['items'] ?? []);
        if ($items === []) {
            throw new InvalidArgumentException('Add at least one BOQ item.');
        }
        $draft['totals'] = QuotationCalculator::calculateQuote($draft, $items, self::quotationSettings());
        Database::exec('UPDATE boq_anonymous_drafts SET draft_json=? WHERE public_token=?', [json_encode($draft, JSON_UNESCAPED_UNICODE), $token]);
        self::trackBoqEvent('boq_draft_saved', $token, ['items' => count($items)]);
        return ['token' => $token, 'draft' => $draft];
    }

    public static function trackBoqEvent(string $event, ?string $token = null, array $properties = []): void
    {
        try {
            Database::exec('INSERT INTO boq_funnel_events (anonymous_token, event_name, properties_json) VALUES (?, ?, ?)', [$token, $event, json_encode($properties, JSON_UNESCAPED_UNICODE)]);
        } catch (Throwable) {
        }
    }

    private static function boqAnonymousSessionToken(bool $create = true): string
    {
        $token = (string)($_COOKIE['boq_anon_session'] ?? '');
        if (preg_match('/^[a-f0-9]{48}$/', $token)) {
            return $token;
        }
        if (!$create) {
            return '';
        }
        $token = bin2hex(random_bytes(24));
        setcookie('boq_anon_session', $token, ['expires' => time() + 30 * 86400, 'path' => '/', 'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'), 'httponly' => true, 'samesite' => 'Lax']);
        $_COOKIE['boq_anon_session'] = $token;
        return $token;
    }

    private static function boqTemplateItems(string $templateKey, array $rooms, array $rates, float $scale): array
    {
        $map = [
            'Living room' => [['TV unit', 'TV unit'], ['False ceiling', 'False ceiling']],
            'Dining area' => [['Crockery unit', 'Crockery unit']],
            'Modular kitchen' => [['Modular kitchen', 'Modular kitchen']],
            'Master bedroom' => [['Wardrobe', 'Wardrobe'], ['Bed with storage', 'Bed with storage']],
            'Bedroom 2' => [['Wardrobe', 'Wardrobe'], ['Bed with storage', 'Bed with storage']],
            'Bedroom 3' => [['Wardrobe', 'Wardrobe'], ['Bed with storage', 'Bed with storage']],
            'Guest bedroom' => [['Wardrobe', 'Wardrobe'], ['Bed with storage', 'Bed with storage']],
        ];
        if ($templateKey === 'wardrobe') $map = ['Master bedroom' => [['Wardrobe', 'Wardrobe']]];
        if ($templateKey === 'false-ceiling') $map = ['Living room' => [['False ceiling', 'False ceiling']]];
        $items = [];
        foreach ($rooms as $room) {
            foreach ($map[$room] ?? [] as [$category, $fallback]) {
                $rate = $rates[strtolower($category)] ?? [];
                $areaDriven = str_contains(strtolower((string)($rate['unit_type'] ?? '')), 'sq ft');
                $quantity = $areaDriven ? max(1, round(100 * $scale, 2)) : ($category === 'Modular kitchen' ? max(8, round(10 * $scale, 2)) : 1);
                $items[] = ['room_name' => $room, 'category' => $category, 'item_name' => $rate['item_name'] ?? $fallback, 'specification' => trim(implode(' · ', array_filter([$rate['material'] ?? '', $rate['finish'] ?? '', $rate['brand'] ?? '']))), 'unit_type' => $rate['unit_type'] ?? 'Per unit', 'quantity' => $quantity, 'rate' => (float)($rate['client_selling_rate'] ?? $rate['base_rate'] ?? 0), 'vendor_cost' => (float)($rate['vendor_cost'] ?? 0), 'gst_percentage' => (float)($rate['gst_percentage'] ?? 18), 'include_in_proposal' => 1];
            }
        }
        return $items;
    }

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
            'cities' => [
                'Gurgaon', 'Delhi', 'Noida', 'Greater Noida', 'Ghaziabad', 'Faridabad',
                'Mumbai', 'Pune', 'Bengaluru', 'Hyderabad', 'Chennai', 'Ahmedabad',
                'Kolkata', 'Jaipur', 'Lucknow', 'Chandigarh', 'Kochi', 'Indore',
            ],
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

    public static function homeServiceCities(): array
    {
        return [
            'Mumbai', 'Delhi', 'Bangalore', 'Hyderabad', 'Chennai', 'Ahmedabad', 'Kolkata', 'Surat', 'Pune', 'Jaipur',
            'Lucknow', 'Kanpur', 'Nagpur', 'Indore', 'Thane', 'Bhopal', 'Visakhapatnam', 'Pimpri-Chinchwad', 'Patna', 'Vadodara',
            'Ghaziabad', 'Ludhiana', 'Agra', 'Nashik', 'Faridabad', 'Meerut', 'Rajkot', 'Kalyan-Dombivli', 'Vasai-Virar', 'Varanasi',
            'Srinagar', 'Chhatrapati Sambhajinagar', 'Dhanbad', 'Amritsar', 'Navi Mumbai', 'Prayagraj', 'Howrah', 'Ranchi', 'Jabalpur', 'Gwalior',
            'Coimbatore', 'Vijayawada', 'Jodhpur', 'Madurai', 'Raipur', 'Kota', 'Chandigarh', 'Guwahati', 'Solapur', 'Hubli-Dharwad',
            'Gurgaon', 'Noida', 'Greater Noida',
        ];
    }

    public static function homeServiceCatalog(bool $includeInactive = false): array
    {
        if (!self::tableExists('home_service_nodes')) {
            return [];
        }
        $where = $includeInactive ? '' : ' WHERE is_active=1';
        $rows = Database::query('SELECT * FROM home_service_nodes' . $where . ' ORDER BY sort_order, name');
        $byParent = [];
        foreach ($rows as $row) {
            $byParent[(int)($row['parent_id'] ?? 0)][] = $row;
        }
        $catalog = [];
        foreach ($byParent[0] ?? [] as $category) {
            $category['children'] = $byParent[(int)$category['id']] ?? [];
            $catalog[] = $category;
        }
        return $catalog;
    }

    public static function homeServiceByPath(string $categorySlug, string $serviceSlug): ?array
    {
        if (!self::tableExists('home_service_nodes')) {
            return null;
        }
        $service = Database::one(
            'SELECT s.*, c.name AS category_name, c.slug AS category_slug, c.short_description AS category_description
             FROM home_service_nodes s
             JOIN home_service_nodes c ON c.id=s.parent_id AND c.node_type="category"
             WHERE c.slug=? AND s.slug=? AND s.node_type="service" AND c.is_active=1 AND s.is_active=1 LIMIT 1',
            [$categorySlug, $serviceSlug]
        );
        return $service ?: null;
    }

    public static function homeServiceBySlug(string $serviceSlug): ?array
    {
        if (!self::tableExists('home_service_nodes')) {
            return null;
        }
        return Database::one(
            'SELECT s.*, c.name AS category_name, c.slug AS category_slug, c.short_description AS category_description
             FROM home_service_nodes s
             JOIN home_service_nodes c ON c.id=s.parent_id AND c.node_type="category"
             WHERE s.slug=? AND s.node_type="service" AND c.is_active=1 AND s.is_active=1 LIMIT 1',
            [$serviceSlug]
        ) ?: null;
    }

    /**
     * Server-rendered editorial copy for the public service landing pages.
     * It uses the catalogue record so a service can be renamed or moved without
     * duplicating copy across templates.
     */
    public static function homeServiceEditorialContent(array $service, string $city = ''): array
    {
        $name = trim((string)($service['name'] ?? 'Home service'));
        $nameLower = mb_strtolower($name);
        $category = trim((string)($service['category_name'] ?? 'Home services'));
        $cityGuides = [
            'Gurgaon' => ['Golf Course Road, Sohna Road, Dwarka Expressway and New Gurgaon', 'society access, possession timelines and vendor coordination'],
            'Delhi' => ['South Delhi, West Delhi, Dwarka, Rohini and East Delhi', 'parking, building access and work-hour approvals'],
            'Noida' => ['Noida Expressway, Sector 50, Sector 74, Sector 137 and Noida Extension', 'tower access, lift timing and apartment handover schedules'],
            'Greater Noida' => ['Greater Noida West, Pari Chowk, Knowledge Park and nearby sectors', 'new-home possession dates and society access'],
            'Ghaziabad' => ['Indirapuram, Vaishali, Raj Nagar Extension and Crossings Republik', 'community approvals, lift access and staged execution'],
            'Faridabad' => ['Greater Faridabad, Sector 75, Surajkund and nearby localities', 'site access, material delivery and phased planning'],
            'Mumbai' => ['Andheri, Powai, Thane, Navi Mumbai and South Mumbai', 'building permissions, lift bookings and compact-space planning'],
            'Pune' => ['Kharadi, Wakad, Baner, Hinjewadi and Viman Nagar', 'society approvals, possession schedules and contractor access'],
            'Chennai' => ['OMR, Anna Nagar, Velachery, Adyar and Porur', 'apartment access, ventilation needs and material delivery timing'],
            'Bangalore' => ['Whitefield, Sarjapur Road, Electronic City, HSR Layout and North Bangalore', 'community approval processes, site access and scheduling'],
            'Hyderabad' => ['Gachibowli, Kondapur, Hitech City, Kukatpally and Manikonda', 'tower access, new-possession handover and work timing'],
        ];
        $cityGuide = $cityGuides[$city] ?? [$city !== '' ? 'nearby localities' : 'Delhi NCR', 'site access and work scheduling'];
        $location = $city !== '' ? $city . ' including ' . $cityGuide[0] : 'Delhi NCR';
        $scope = trim((string)($service['short_description'] ?? ''));
        $priceGuidance = match ((string)($service['category_slug'] ?? '')) {
            'design-and-construction' => 'For planned design and execution work, budgets commonly begin around Rs 50,000 for focused scopes and can extend beyond Rs 10 lakh for larger, material-led projects.',
            'home-improvement' => 'For improvement work, compact jobs may start around Rs 15,000, while full-room upgrades, waterproofing or material-heavy work can move into several lakhs.',
            'repair-and-maintenance' => 'For repair and maintenance, a visit or minor repair is often a few hundred to a few thousand rupees; replacement parts, installation work and emergency timing can change the final amount.',
            'cleaning-and-protection' => 'For cleaning and protection services, pricing usually depends on home size, condition, access, equipment, chemicals and whether the requirement is one-time or recurring.',
            'moving-and-household-support' => 'For moving and household support, pricing depends on distance, workforce, packing material, floor access, schedule and the level of responsibility requested.',
            default => 'Pricing depends on the scope, materials, workforce, site condition and timing required for the work.',
        };
        $serviceFocus = match ((string)($service['slug'] ?? '')) {
            'interior-design' => 'For interior design, prepare your room list, approximate carpet area, possession date and preferred finish level before requesting quotes.',
            'electricians' => 'For electrical work, note the issue, the number of points, fixture type, access restrictions and whether the work is repair, installation or inspection.',
            'plumbers' => 'For plumbing, explain whether the requirement is a leak, blockage, fitting replacement, installation or a larger bathroom or kitchen upgrade.',
            'painting' => 'For painting, include the approximate area, interior or exterior surfaces, repair requirements, paint preference and target completion date.',
            'home-cleaning' => 'For home cleaning, mention the BHK size, occupancy, furnishing level, last deep-clean date and whether you need move-in, move-out or occupied-home cleaning.',
            'packers-movers' => 'For moving, list the origin, destination, home size, lift availability, packing expectation and the date on which the move needs to happen.',
            default => 'A clear description, photographs where appropriate, preferred visit date and locality details help professionals assess the work before they call.',
        };

        return [
            'heading' => 'About ' . $name . ($city !== '' ? ' in ' . $city : ''),
            'paragraphs' => [
                $name . ' is a practical part of maintaining, improving or completing a home. ' . ($scope !== '' ? $scope . ' ' : '') . 'Homeowners in ' . $location . ' usually compare providers on response time, clarity of scope, workmanship, materials or spare parts, and the ability to work around society access and household schedules. ' . $serviceFocus,
                'The ' . $nameLower . ' market is highly local. In ' . ($city !== '' ? $city : 'Delhi NCR') . ', practical planning often includes ' . $cityGuide[1] . '. A provider who understands parking, lift rules, visitor approvals, working hours and common building conditions can plan a more useful visit. Ask for the exact work included, exclusions, warranty or revisit terms, estimated turnaround and any site-preparation needed before confirming. A written scope is particularly useful when the job involves multiple trades, custom materials or phased work.',
                'Cost should be compared against the scope rather than treated as a single headline number. ' . $priceGuidance . ' The most reliable comparison is a like-for-like quote that identifies labour, materials, brand or grade, transport, disposal, taxes and any optional add-ons. If a site visit is required, confirm whether its fee is adjusted against the final work order.',
                'HomeInteriors360 makes the first step simpler by collecting the details relevant to ' . $nameLower . ' and routing the enquiry by city and locality. You can share your requirement once, review suitable responses and decide whom to contact without committing to a job. Before confirming, compare scope, expected timing, material or parts assumptions, payment milestones and post-service support in writing for complete clarity. Final pricing, availability, service terms and execution are agreed directly with the professional you select, so you stay in control of the decision.',
            ],
        ];
    }

    public static function homeServiceForm(int $serviceId): array
    {
        if (!self::tableExists('home_service_form_versions')) {
            return ['version' => null, 'questions' => []];
        }
        $version = Database::one('SELECT * FROM home_service_form_versions WHERE service_id=? AND is_published=1 ORDER BY version_no DESC LIMIT 1', [$serviceId]);
        if (!$version) {
            return ['version' => null, 'questions' => []];
        }
        $questions = Database::query('SELECT * FROM home_service_form_questions WHERE form_version_id=? ORDER BY sort_order, id', [(int)$version['id']]);
        foreach ($questions as &$question) {
            $options = json_decode((string)($question['options_json'] ?? ''), true);
            $question['options'] = is_array($options) ? $options : [];
        }
        unset($question);
        return ['version' => $version, 'questions' => $questions];
    }

    public static function homeServiceAvailableInCity(int $serviceId, string $city): bool
    {
        if (self::tableExists('home_service_availability') && Database::one('SELECT id FROM home_service_availability WHERE service_id=? AND city=? AND is_active=1 LIMIT 1', [$serviceId, $city]) !== null) {
            return true;
        }
        return self::isFeaturedInteriorDesignCity($serviceId, $city);
    }

    /** @return array<int, string> */
    public static function homeServiceAvailableCities(int $serviceId): array
    {
        $cities = [];
        if (self::tableExists('home_service_availability')) {
            $cities = array_map(
                static fn(array $row): string => (string)$row['city'],
                Database::query('SELECT city FROM home_service_availability WHERE service_id=? AND is_active=1 ORDER BY city', [$serviceId])
            );
        }
        if (self::isFeaturedInteriorDesignCity($serviceId, self::FEATURED_INTERIOR_DESIGN_CITIES[0])) {
            $cities = [...$cities, ...self::FEATURED_INTERIOR_DESIGN_CITIES];
        }
        return array_values(array_unique($cities));
    }

    /** @return array<int, string> */
    public static function homeServiceAvailableCitiesForSlug(string $serviceSlug): array
    {
        $cities = [];
        if (self::tableExists('home_service_availability')) {
            $cities = array_map(
                static fn(array $row): string => (string)$row['city'],
                Database::query(
                    'SELECT a.city FROM home_service_availability a JOIN home_service_nodes s ON s.id=a.service_id WHERE s.slug=? AND s.node_type="service" AND s.is_active=1 AND a.is_active=1 ORDER BY a.city',
                    [$serviceSlug]
                )
            );
        }
        if ($serviceSlug === 'interior-design') {
            $cities = [...$cities, ...self::FEATURED_INTERIOR_DESIGN_CITIES];
        }
        return array_values(array_unique($cities));
    }

    public static function homeServiceProfessionals(int $serviceId, string $city): array
    {
        if (!self::tableExists('home_service_provider_services')) {
            return [];
        }
        $rows = Database::query(
            'SELECT p.id, p.full_name, p.slug, p.profile_pic, p.role, p.city, p.rating, p.years_experience, p.profile_description,
                    COUNT(r.id) AS review_count
             FROM home_service_provider_services ps
             JOIN pros p ON p.id=ps.pro_id AND p.is_active=1
             LEFT JOIN reviews r ON r.pro_id=p.id
             LEFT JOIN home_service_provider_coverage pc ON pc.pro_id=p.id AND pc.service_id=ps.service_id AND pc.city=?
             WHERE ps.service_id=? AND ps.approval_status="approved" AND (p.city=? OR pc.id IS NOT NULL)
             GROUP BY p.id ORDER BY p.verification_status DESC, p.rating DESC, p.updated_at DESC LIMIT 12',
            [$city, $serviceId, $city]
        );
        return $rows;
    }

    public static function createHomeServiceEnquiry(array $data): array
    {
        if (!self::tableExists('home_service_enquiries')) {
            throw new InvalidArgumentException('Home services are not configured yet. Please run the latest migration.');
        }
        $serviceId = (int)($data['service_id'] ?? 0);
        $formVersionId = (int)($data['form_version_id'] ?? 0);
        $service = Database::one('SELECT * FROM home_service_nodes WHERE id=? AND node_type="service" AND is_active=1', [$serviceId]);
        if (!$service || $formVersionId <= 0) {
            throw new InvalidArgumentException('Choose an active home service.');
        }
        $version = Database::one('SELECT id FROM home_service_form_versions WHERE id=? AND service_id=? AND is_published=1', [$formVersionId, $serviceId]);
        if (!$version) {
            throw new InvalidArgumentException('This service form is no longer available. Please refresh and try again.');
        }
        $name = trim((string)($data['name'] ?? ''));
        $phone = self::normalizeLeadMobile((string)($data['phone'] ?? ''));
        $email = trim((string)($data['email'] ?? ''));
        $city = trim((string)($data['city'] ?? ''));
        $locality = trim((string)($data['locality'] ?? ''));
        if ($name === '' || $phone === '' || !in_array($city, self::homeServiceCities(), true)) {
            throw new InvalidArgumentException('Name, a valid mobile number, and city are required.');
        }
        if (empty($data['provider_contact_consent'])) {
            throw new InvalidArgumentException('Consent is required to share your requirement with suitable service professionals.');
        }
        if (!self::homeServiceAvailableInCity($serviceId, $city)) {
            throw new InvalidArgumentException('This service is not live in the selected city yet.');
        }
        $answers = is_array($data['answers'] ?? null) ? $data['answers'] : [];
        $form = self::homeServiceForm($serviceId);
        $questionsByKey = [];
        foreach ($form['questions'] as $question) {
            $questionsByKey[(string)$question['field_key']] = $question;
            if (!empty($question['is_required']) && trim((string)($answers[(string)$question['field_key']] ?? '')) === '') {
                throw new InvalidArgumentException((string)$question['label'] . ' is required.');
            }
        }
        $duplicate = Database::one(
            'SELECT id FROM home_service_enquiries WHERE phone=? AND service_id=? AND city=? AND status IN ("open","matched") AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY id DESC LIMIT 1',
            [$phone, $serviceId, $city]
        );
        if ($duplicate) {
            return ['id' => (int)$duplicate['id'], 'duplicate' => true];
        }
        $budget = trim((string)($answers['budget_range'] ?? $data['budget_range'] ?? ''));
        $description = trim((string)($answers['scope'] ?? $answers['issue'] ?? $answers['household_details'] ?? $data['description'] ?? ''));
        $legacyLeadId = self::createLead([
            'name' => $name,
            'phone' => $phone,
            'city' => $city,
            'society_area' => $locality,
            'budget' => $budget,
            'requirement' => $service['name'] . ($description !== '' ? ': ' . $description : ''),
            'plan_type' => 'Home Service',
            'source' => 'homepage',
            'status' => 'new',
        ]);
        $preferredAt = trim((string)($answers['preferred_date'] ?? $answers['schedule'] ?? $data['preferred_at'] ?? ''));
        $enquiryId = Database::exec(
            'INSERT INTO home_service_enquiries (lead_id, service_id, form_version_id, name, phone, email, city, locality, pincode, preferred_at, description, status, max_buyers, expires_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,DATE_ADD(NOW(), INTERVAL 72 HOUR))',
            [$legacyLeadId, $serviceId, $formVersionId, $name, $phone, $email !== '' ? $email : null, $city, $locality !== '' ? $locality : null, trim((string)($data['pincode'] ?? '')) ?: null, $preferredAt !== '' ? $preferredAt : null, $description !== '' ? $description : null, 'open', self::MAX_BUYERS_PER_LEAD]
        );
        foreach ($questionsByKey as $key => $question) {
            if (!array_key_exists($key, $answers) || trim((string)$answers[$key]) === '') {
                continue;
            }
            Database::exec('INSERT INTO home_service_enquiry_answers (enquiry_id, question_id, value_json) VALUES (?,?,?)', [$enquiryId, (int)$question['id'], json_encode(['value' => $answers[$key]], JSON_UNESCAPED_UNICODE)]);
        }
        Database::exec('INSERT INTO home_service_consents (enquiry_id, purpose, consent_text_version, granted_at) VALUES (?,?,?,NOW())', [$enquiryId, 'provider_contact', 'home-services-v1']);
        if (!empty($data['marketing_consent'])) {
            Database::exec('INSERT INTO home_service_consents (enquiry_id, purpose, consent_text_version, granted_at) VALUES (?,?,?,NOW())', [$enquiryId, 'marketing', 'home-services-v1']);
        }
        return ['id' => $enquiryId, 'duplicate' => false, 'lead_id' => $legacyLeadId];
    }

    public static function homeServiceAdminRows(): array
    {
        if (!self::tableExists('home_service_nodes')) {
            return [];
        }
        return Database::query(
            'SELECT s.*, p.name AS parent_name, COUNT(DISTINCT f.id) AS form_versions, COUNT(DISTINCT a.id) AS active_cities
             FROM home_service_nodes s
             LEFT JOIN home_service_nodes p ON p.id=s.parent_id
             LEFT JOIN home_service_form_versions f ON f.service_id=s.id AND f.is_published=1
             LEFT JOIN home_service_availability a ON a.service_id=s.id AND a.is_active=1
             GROUP BY s.id ORDER BY COALESCE(p.sort_order, s.sort_order), s.sort_order, s.name'
        );
    }

    public static function saveHomeServiceNode(array $data): int
    {
        $id = (int)($data['id'] ?? 0);
        $name = trim((string)($data['name'] ?? ''));
        $type = trim((string)($data['node_type'] ?? 'service'));
        $parentId = (int)($data['parent_id'] ?? 0);
        if ($name === '' || !in_array($type, ['category', 'subcategory', 'service'], true)) {
            throw new InvalidArgumentException('Service name and a valid node type are required.');
        }
        if ($type === 'category') {
            $parentId = 0;
        } elseif ($parentId <= 0) {
            throw new InvalidArgumentException('Select a parent category for this service.');
        }
        $slug = slugify((string)($data['slug'] ?? $name));
        if ($slug === '') {
            throw new InvalidArgumentException('A valid service slug is required.');
        }
        $parentValue = $parentId > 0 ? $parentId : null;
        $values = [$parentValue, $type, $name, $slug, trim((string)($data['short_description'] ?? '')) ?: null, trim((string)($data['icon_key'] ?? '')) ?: null, !empty($data['is_active']) ? 1 : 0, (int)($data['sort_order'] ?? 0)];
        if ($id > 0) {
            Database::exec('UPDATE home_service_nodes SET parent_id=?, node_type=?, name=?, slug=?, short_description=?, icon_key=?, is_active=?, sort_order=?, updated_at=NOW() WHERE id=?', [...$values, $id]);
            return $id;
        }
        return Database::exec('INSERT INTO home_service_nodes (parent_id,node_type,name,slug,short_description,icon_key,is_active,sort_order) VALUES (?,?,?,?,?,?,?,?)', $values);
    }

    public static function saveHomeServiceFormQuestions(int $serviceId, array $questions): void
    {
        if ($serviceId <= 0) {
            throw new InvalidArgumentException('Select a service first.');
        }
        $service = Database::one('SELECT id FROM home_service_nodes WHERE id=? AND node_type="service"', [$serviceId]);
        if (!$service) {
            throw new InvalidArgumentException('Service not found.');
        }
        $version = Database::one('SELECT * FROM home_service_form_versions WHERE service_id=? AND is_published=1 ORDER BY version_no DESC LIMIT 1', [$serviceId]);
        $nextVersion = $version ? (int)$version['version_no'] + 1 : 1;
        $versionId = Database::exec('INSERT INTO home_service_form_versions (service_id,version_no,is_published,published_at) VALUES (?,?,1,NOW())', [$serviceId, $nextVersion]);
        foreach (array_values($questions) as $index => $question) {
            $key = slugify(str_replace('_', '-', (string)($question['key'] ?? $question['field_key'] ?? '')));
            $key = str_replace('-', '_', $key);
            $label = trim((string)($question['label'] ?? ''));
            $type = (string)($question['type'] ?? $question['field_type'] ?? 'text');
            if ($key === '' || $label === '' || !in_array($type, ['text', 'textarea', 'select', 'radio', 'date', 'number', 'boolean'], true)) {
                continue;
            }
            $options = $question['options'] ?? [];
            if (is_string($options)) {
                $options = array_values(array_filter(array_map('trim', explode(',', $options))));
            }
            Database::exec('INSERT INTO home_service_form_questions (form_version_id,field_key,label,field_type,is_required,options_json,sort_order) VALUES (?,?,?,?,?,?,?)', [$versionId, $key, $label, $type, !empty($question['required']) ? 1 : 0, $options !== [] ? json_encode($options, JSON_UNESCAPED_UNICODE) : null, $index + 1]);
        }
        if ($version) {
            Database::exec('UPDATE home_service_form_versions SET is_published=0 WHERE id=?', [(int)$version['id']]);
        }
    }

    public static function homeServiceIndexablePaths(): array
    {
        if (!self::tableExists('home_service_nodes') || !self::tableExists('home_service_availability')) {
            return [];
        }
        $paths = ['/home-services'];
        // Every active service has a useful canonical hub page. City pages remain
        // restricted to the places with an approved provider mapping below.
        foreach (Database::query(
            'SELECT c.slug AS category_slug, s.slug AS service_slug, COALESCE(s.updated_at,s.created_at) AS updated_at
             FROM home_service_nodes s
             JOIN home_service_nodes c ON c.id=s.parent_id AND c.node_type="category" AND c.is_active=1
             WHERE s.node_type="service" AND s.is_active=1
             ORDER BY c.sort_order, s.sort_order'
        ) as $service) {
            $paths[] = [
                'path' => '/services/' . $service['category_slug'] . '/' . $service['service_slug'],
                // The Home Cleaning hub received an editorial relaunch after its
                // catalogue record was created; let the sitemap use this release's
                // timestamp instead of publishing a stale database date.
                'updated_at' => $service['service_slug'] === 'home-cleaning' ? null : ($service['updated_at'] ?? null),
            ];
        }
        $rows = Database::query(
            'SELECT c.slug AS category_slug, s.slug AS service_slug, a.city, GREATEST(COALESCE(s.updated_at,s.created_at), COALESCE(a.updated_at,a.created_at)) AS updated_at
             FROM home_service_nodes s
             JOIN home_service_nodes c ON c.id=s.parent_id AND c.node_type="category" AND c.is_active=1
             JOIN home_service_availability a ON a.service_id=s.id AND a.is_active=1
             WHERE s.node_type="service" AND s.is_active=1
               AND EXISTS (
                    SELECT 1
                    FROM home_service_provider_services ps
                    JOIN pros p ON p.id=ps.pro_id AND p.is_active=1
                    LEFT JOIN home_service_provider_coverage pc
                        ON pc.pro_id=p.id AND pc.service_id=ps.service_id AND pc.city=a.city
                    WHERE ps.service_id=s.id
                      AND ps.approval_status="approved"
                      AND (p.city=a.city OR pc.id IS NOT NULL)
               )
             ORDER BY c.sort_order, s.sort_order, a.city'
        );
        foreach ($rows as $row) {
            $paths[] = [
                'city_path' => '/' . slugify((string)$row['city']) . '/' . $row['service_slug'],
                'updated_at' => $row['updated_at'] ?? null,
            ];
        }
        $interiorDesign = Database::one(
            'SELECT c.slug AS category_slug, s.slug AS service_slug, s.updated_at
             FROM home_service_nodes s
             JOIN home_service_nodes c ON c.id=s.parent_id AND c.node_type="category" AND c.is_active=1
             WHERE s.node_type="service" AND s.is_active=1 AND s.slug="interior-design" LIMIT 1'
        );
        if ($interiorDesign) {
            $paths[] = [
                'city_path' => '/mumbai/interior-design',
                'updated_at' => $interiorDesign['updated_at'] ?? null,
            ];
            foreach (array_slice(self::FEATURED_INTERIOR_DESIGN_CITIES, 1) as $city) {
                $paths[] = [
                    'city_path' => '/' . slugify($city) . '/interior-design',
                    'updated_at' => $interiorDesign['updated_at'] ?? null,
                ];
            }
            // Curated locality pages are child hubs of the live interior-design
            // city pages. They have server-rendered content, canonical URLs and
            // breadcrumbs, so emit every approved locality route in the sitemap.
            foreach (LocationLandingCatalog::allLocalities() as $locality) {
                $paths[] = [
                    'locality_path' => '/' . $locality['city_slug'] . '/interior-design/' . $locality['slug'],
                    'updated_at' => $interiorDesign['updated_at'] ?? null,
                ];
            }
        }
        return $paths;
    }

    private static function isFeaturedInteriorDesignCity(int $serviceId, string $city): bool
    {
        if (!in_array(trim($city), self::FEATURED_INTERIOR_DESIGN_CITIES, true) || !self::tableExists('home_service_nodes')) {
            return false;
        }
        $service = Database::one('SELECT id FROM home_service_nodes WHERE slug="interior-design" AND node_type="service" AND is_active=1 LIMIT 1');
        return $service !== null && (int)$service['id'] === $serviceId;
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
            $pro['offerings_json'] = self::parseJsonArray($pro['offerings_json'] ?? '[]');
            $pro['design_styles_json'] = self::parseJsonArray($pro['design_styles_json'] ?? '[]');
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
                       pros.city, pros.office_address, pros.primary_work_type, pros.primary_work_area, pros.response_time_hours,
                       pros.offerings_json, pros.design_styles_json,
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

    /** Server-rendered profile guide for crawlable professional pages. */
    public static function professionalEditorialContent(array $pro, array $profileData = []): array
    {
        $name = trim((string)($pro['full_name'] ?? 'This professional'));
        $city = trim((string)($pro['city'] ?? 'Delhi NCR')) ?: 'Delhi NCR';
        $role = trim((string)($pro['role'] ?? 'interior professional')) ?: 'interior professional';
        $workType = trim((string)($pro['primary_work_type'] ?? $pro['specialization'] ?? 'home interiors')) ?: 'home interiors';
        $areas = array_slice(self::parseJsonArray($pro['service_areas'] ?? []), 0, 4);
        $offerings = array_slice(self::parseJsonArray($pro['offerings_json'] ?? []), 0, 5);
        $styles = array_slice(self::parseJsonArray($pro['design_styles_json'] ?? []), 0, 3);
        $materials = array_slice(self::parseJsonArray($pro['materials_json'] ?? []), 0, 4);
        $projects = array_slice((array)($profileData['projects'] ?? []), 0, 3);
        $years = (int)($pro['years_experience'] ?? 0);
        $projectCount = (int)($pro['projects_delivered'] ?? 0);
        $startingPrice = (float)($pro['starting_price'] ?? 0);
        $areaPhrase = $areas !== [] ? implode(', ', $areas) : $city . ' and nearby Delhi NCR localities';
        $offeringPhrase = $offerings !== [] ? implode(', ', $offerings) : $workType;
        $stylePhrase = $styles !== [] ? implode(', ', $styles) : 'the homeowner\'s preferred design direction';
        $materialPhrase = $materials !== [] ? implode(', ', $materials) : 'the materials and finishes agreed during planning';
        $experiencePhrase = $years >= 1 && $years <= 70
            ? $years . '+ years of listed experience'
            : 'a detailed public professional profile';
        $projectPhrase = $projectCount > 0
            ? number_format($projectCount) . ' delivered projects listed on the profile'
            : (count($projects) > 0 ? count($projects) . ' portfolio project' . (count($projects) === 1 ? '' : 's') . ' currently visible' : 'the portfolio information currently available');
        $pricePhrase = $startingPrice > 0
            ? 'The profile shows a starting price of Rs ' . number_format($startingPrice, 0) . ', while the final estimate should be based on the measured scope and selected specifications.'
            : 'Pricing should be requested against a written scope so room count, materials, civil changes, lighting, appliances and site conditions are compared on a like-for-like basis.';
        $portfolioNames = array_values(array_filter(array_map(static fn(array $project): string => trim((string)($project['project_name'] ?? '')), $projects)));
        $portfolioPhrase = $portfolioNames !== []
            ? 'Visible portfolio work includes ' . implode(', ', $portfolioNames) . '.'
            : 'Review the available portfolio, service information and consultation response before making a decision.';

        return [
            'heading' => 'About ' . $name . ' for ' . $workType . ' in ' . $city,
            'paragraphs' => [
                $name . ' is listed on HomeInteriors360 as a ' . $role . ' serving ' . $areaPhrase . '. The profile is designed to help homeowners understand the professional\'s stated focus before they begin a consultation. For a ' . $workType . ' project, the most useful first conversation covers the property type, room list, possession stage, preferred completion date, budget comfort and the decisions that need specialist input. This gives both sides a practical starting point instead of a generic enquiry.',
                'The services shown for ' . $name . ' include ' . $offeringPhrase . '. Homeowners can use the profile to shortlist a professional when the project needs experience across planning, design coordination, modular work, renovation or execution. Ask for a room-wise scope, drawings or visual references where relevant, and clarity on what will be handled by the core team versus specialist vendors. The listed design direction includes ' . $stylePhrase . ', while material discussions can cover ' . $materialPhrase . ' according to the final project brief.',
                'This profile presents ' . $experiencePhrase . ' and ' . $projectPhrase . '. ' . $portfolioPhrase . ' Portfolio images, work descriptions and project context are useful signals, but every home has different measurements, site conditions and approval requirements. Before appointing any professional, confirm the measurement process, design revisions, project timeline, supervision model, warranty terms, payment milestones and change-request procedure in writing. This is especially important for renovation and multi-room work where one decision can affect several trades.',
                $pricePhrase . ' HomeInteriors360 lets you send one structured requirement to ' . $name . ' with your city, locality, budget and project need already included. You can then compare the response with your priorities and decide whether to proceed with a consultation. Availability, exact scope, commercial terms and execution remain a direct agreement between you and the professional, keeping the final choice transparent and homeowner-led.',
            ],
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

    /** Full, flat account and linked-profile export for the Designer Accounts backend. */
    public static function designerAccountsForExport(): array
    {
        $columns = static function (string $table): array {
            if (!self::tableExists($table)) {
                return [];
            }
            return array_values(array_filter(array_map(
                static fn(array $row): string => (string)($row['Field'] ?? ''),
                Database::query('SHOW COLUMNS FROM `' . $table . '`')
            )));
        };

        $accountColumns = array_values(array_filter($columns('users'), static fn(string $column): bool => !in_array($column, ['password_hash', 'reset_token', 'token_hash'], true)));
        $professionalColumns = $columns('pros');
        $extendedColumns = $columns('professional_extended_details');
        $selects = [];
        foreach ($accountColumns as $column) {
            $selects[] = 'u.`' . $column . '` AS `account_' . $column . '`';
        }
        foreach ($professionalColumns as $column) {
            $selects[] = 'p.`' . $column . '` AS `professional_' . $column . '`';
        }
        foreach ($extendedColumns as $column) {
            $selects[] = 'ped.`' . $column . '` AS `profile_' . $column . '`';
        }
        $selects[] = '(SELECT COUNT(*) FROM projects prj WHERE prj.pro_id=u.pro_id) AS portfolio_project_count';
        $join = $extendedColumns ? ' LEFT JOIN professional_extended_details ped ON ped.pro_id=u.pro_id' : '';
        return Database::query(
            'SELECT ' . implode(', ', $selects) . ' FROM users u LEFT JOIN pros p ON p.id=u.pro_id' . $join . " WHERE u.role='designer' ORDER BY u.updated_at DESC, u.id DESC"
        );
    }

    public static function designerAccountDetail(int $id): ?array
    {
        $account = Database::one("SELECT id, username, email, role, pro_id, is_active, created_at, updated_at FROM users WHERE id=? AND role='designer' LIMIT 1", [$id]);
        if (!$account) {
            return null;
        }
        return [
            'account' => $account,
            'professional' => !empty($account['pro_id']) ? Database::one('SELECT * FROM pros WHERE id=? LIMIT 1', [(int)$account['pro_id']]) : null,
        ];
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
            self::updateDesignerProfessionalVisibility($proId, $data);
            return $id;
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters.');
        }
        $userId = Database::exec(
            "INSERT INTO users (username, password_hash, email, role, pro_id, is_active) VALUES (?, ?, ?, 'designer', ?, ?)",
            [$username, password_hash($password, PASSWORD_DEFAULT), $email !== '' ? $email : null, $proId, $isActive]
        );
        self::updateDesignerProfessionalVisibility($proId, $data);
        return $userId;
    }

    private static function updateDesignerProfessionalVisibility(int $proId, array $data): void
    {
        if (!array_key_exists('profile_visible', $data) && !array_key_exists('profile_accepting_leads', $data)) {
            return;
        }
        $professional = Database::one('SELECT is_active, accepting_leads FROM pros WHERE id=? LIMIT 1', [$proId]);
        if (!$professional) {
            return;
        }
        $visible = array_key_exists('profile_visible', $data) ? (!empty($data['profile_visible']) ? 1 : 0) : (int)$professional['is_active'];
        $acceptingLeads = array_key_exists('profile_accepting_leads', $data) ? (!empty($data['profile_accepting_leads']) ? 1 : 0) : (int)($professional['accepting_leads'] ?? 1);
        Database::exec('UPDATE pros SET is_active=?, accepting_leads=?, updated_at=NOW() WHERE id=?', [$visible, $acceptingLeads, $proId]);
        self::syncProfessionalUrlAlias($proId);
    }

    public static function registerInteriorDesigner(array $data): array
    {
        $googleIdentity = is_array($data['_google_identity'] ?? null) ? $data['_google_identity'] : null;
        $name = trim((string)($data['full_name'] ?? $data['name'] ?? ($googleIdentity['name'] ?? '')));
        $email = strtolower(trim((string)($googleIdentity['email'] ?? $data['email'] ?? '')));
        $phone = preg_replace('/\D+/', '', (string)($data['phone'] ?? '')) ?? '';
        $password = (string)($data['password'] ?? '');
        if ($googleIdentity) {
            $password = bin2hex(random_bytes(24));
        }
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($phone) < 10 || (!$googleIdentity && strlen($password) < 8)) {
            throw new InvalidArgumentException('Name, valid email, 10 digit mobile, and minimum 8 character password are required.');
        }
        if (Database::one('SELECT id FROM users WHERE email=? OR username=? OR google_subject=?', [$email, $email, $googleIdentity['sub'] ?? null])) {
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
            "INSERT INTO users (username, password_hash, email, google_subject, role, pro_id, is_active) VALUES (?, ?, ?, ?, 'designer', ?, 1)",
            [$email, password_hash($password, PASSWORD_DEFAULT), $email, $googleIdentity['sub'] ?? null, $proId]
        );
        $buyer = self::createOrLoginDesignerBuyer([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
        ]);
        self::grantDesignerOnboardingLeadBenefit($userId, $proId, (int)($buyer['id'] ?? 0));

	        return [
	            'pro_id' => $proId,
	            'user_id' => $userId,
	            'buyer' => $buyer,
	            'user' => Database::one('SELECT id, username, email, role, pro_id FROM users WHERE id=?', [$userId]) ?: [],
	        ];
	    }

    public static function updateInteriorDesignerRegistrationStep(int $proId, array $data): array
    {
        $pro = Database::one('SELECT * FROM pros WHERE id=?', [$proId]);
        if (!$pro) {
            throw new InvalidArgumentException('Designer profile was not found.');
        }
        $email = strtolower(trim((string)($data['email'] ?? $pro['email'] ?? '')));
        $phone = preg_replace('/\D+/', '', (string)($data['phone'] ?? $pro['phone'] ?? '')) ?? '';
        if ($email === '' || strcasecmp($email, (string)($pro['email'] ?? '')) !== 0 || substr($phone, -10) !== substr((string)($pro['phone'] ?? ''), -10)) {
            throw new InvalidArgumentException('Designer profile verification failed. Please restart registration.');
        }

        $scalarFields = [
            'office_address', 'city', 'primary_work_area', 'years_experience', 'primary_work_type', 'specialization',
            'projects_delivered', 'response_time_hours', 'starting_price', 'consultation_fee', 'min_project_value',
            'max_project_value', 'founded_year', 'team_size', 'client_count', 'office_hours', 'website_url',
            'google_business_url', 'service_summary', 'profile_description', 'bio', 'why_work_with_me',
        ];
        $sets = [];
        $values = [];
        foreach ($scalarFields as $field) {
            if (array_key_exists($field, $data)) {
                $sets[] = $field . '=?';
                $values[] = $data[$field] === '' ? null : $data[$field];
            }
        }

        foreach (['profile_pic', 'cover_photo'] as $imageField) {
            if (!empty($data[$imageField])) {
                $sets[] = $imageField . '=?';
                $values[] = $data[$imageField];
            }
        }

        foreach (['service_areas', 'materials_json', 'offerings_json', 'design_styles_json', 'languages_json', 'certifications_json'] as $jsonField) {
            if (array_key_exists($jsonField, $data)) {
                $sets[] = $jsonField . '=?';
                $values[] = json_encode(self::parseJsonArray($data[$jsonField]), JSON_UNESCAPED_UNICODE);
            }
        }

        foreach (['process_steps_json', 'awards_json', 'faq_json'] as $linesField) {
            if (array_key_exists($linesField, $data)) {
                $sets[] = $linesField . '=?';
                $values[] = json_encode(self::parseTextLines($data[$linesField]), JSON_UNESCAPED_UNICODE);
            }
        }

        if ($sets !== []) {
            $values[] = $proId;
            Database::exec('UPDATE pros SET ' . implode(', ', $sets) . ', verification_status=0, verification_status_code="UNVERIFIED", listing_tier="FREE", is_active=0, updated_at=NOW() WHERE id=?', $values);
            self::syncProfessionalUrlAlias($proId);
        }

        $user = Database::one('SELECT id, username, email, role, pro_id FROM users WHERE pro_id=? AND role="designer" ORDER BY id DESC LIMIT 1', [$proId]) ?: [];
        $buyer = $email !== '' ? self::designerBuyerForUser(['email' => $email]) : null;
        return [
            'pro_id' => $proId,
            'user_id' => (int)($user['id'] ?? 0),
            'user' => $user,
            'buyer' => $buyer,
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

    public static function designerProfessional(int $proId): ?array
    {
        return $proId > 0 ? Database::one('SELECT * FROM pros WHERE id=? LIMIT 1', [$proId]) : null;
    }

    /**
     * A designer may maintain their public business information, but platform
     * moderation, listing visibility, commercial tiers and client ratings stay
     * controlled by the admin workflow.
     */
    public static function updateDesignerProfessional(int $proId, array $data): void
    {
        $current = self::designerProfessional($proId);
        if (!$current) {
            throw new InvalidArgumentException('Professional profile not found.');
        }
        if (array_key_exists('full_name', $data) && trim((string)$data['full_name']) === '') {
            throw new InvalidArgumentException('Firm or designer name is required.');
        }

        $editable = [
            'full_name', 'profile_pic', 'cover_photo', 'role', 'profile_description', 'specialization', 'primary_work_type', 'primary_work_area',
            'years_experience', 'projects_delivered', 'starting_price', 'min_project_value', 'max_project_value', 'consultation_fee', 'city',
            'office_address', 'phone', 'email', 'website_url', 'google_business_url', 'founded_year', 'team_size', 'office_hours', 'client_count',
            'service_summary', 'service_areas', 'materials_json', 'design_styles_json', 'languages_json', 'certifications_json', 'process_steps_json',
            'awards_json', 'faq_json', 'response_time_hours', 'bio', 'why_work_with_me', 'offerings_json',
        ];
        $merged = $current;
        foreach ($editable as $field) {
            if (array_key_exists($field, $data)) {
                $merged[$field] = $data[$field];
            }
        }

        // Preserve fields controlled only by HomeInteriors360 administration.
        $merged['slug'] = (string)$current['slug'];
        $merged['rating'] = $current['rating'] ?? 0;
        $merged['verification_status'] = $current['verification_status'] ?? 0;
        $merged['verification_status_code'] = $current['verification_status_code'] ?? 'UNVERIFIED';
        $merged['is_premium'] = $current['is_premium'] ?? 0;
        $merged['listing_tier'] = $current['listing_tier'] ?? 'FREE';
        $merged['accepting_leads'] = $current['accepting_leads'] ?? 1;
        $merged['suspension_reason'] = $current['suspension_reason'] ?? null;
        $merged['verification_notes'] = $current['verification_notes'] ?? null;
        $merged['is_active'] = $current['is_active'] ?? 0;
        self::updateProfessional($proId, $merged);
    }

    public static function designerPortfolio(int $proId, int $portfolioId): ?array
    {
        return Database::one('SELECT * FROM projects WHERE id=? AND pro_id=? LIMIT 1', [$portfolioId, $proId]);
    }

    public static function updateDesignerPortfolio(int $proId, int $portfolioId, array $data): void
    {
        $current = self::designerPortfolio($proId, $portfolioId);
        if (!$current) {
            throw new InvalidArgumentException('Portfolio work was not found.');
        }
        $editable = [
            'project_name', 'project_description', 'total_cost', 'bhk_type', 'year_completed', 'timeline_months', 'project_duration_label',
            'location', 'work_type', 'area_of_work', 'materials_json', 'media_json', 'video_url', 'design_style', 'team_size', 'warranty_years',
        ];
        $merged = $current;
        foreach ($editable as $field) {
            if (array_key_exists($field, $data)) {
                $merged[$field] = $data[$field];
            }
        }
        $merged['pro_id'] = $proId;
        $merged['slug'] = (string)$current['slug'];
        $merged['testimonial_client_name'] = $current['testimonial_client_name'] ?? null;
        $merged['testimonial_text'] = $current['testimonial_text'] ?? null;
        $merged['testimonial_rating'] = $current['testimonial_rating'] ?? null;
        $merged['moderation_status'] = 'SUBMITTED';
        $merged['project_verification_status'] = $current['project_verification_status'] ?? 'UNVERIFIED';
        $merged['is_featured'] = $current['is_featured'] ?? 0;
        $merged['moderation_notes'] = $current['moderation_notes'] ?? null;
        self::updatePortfolio($portfolioId, $merged);
    }

    public static function deleteDesignerPortfolio(int $proId, int $portfolioId): void
    {
        if (!self::designerPortfolio($proId, $portfolioId)) {
            throw new InvalidArgumentException('Portfolio work was not found.');
        }
        Database::exec('DELETE FROM projects WHERE id=? AND pro_id=?', [$portfolioId, $proId]);
        try {
            Database::exec('UPDATE url_aliases SET is_active=0, updated_at=NOW() WHERE entity_table="projects" AND entity_id=?', [$portfolioId]);
        } catch (Throwable) {
            // Older installations may not yet have the global alias table.
        }
    }

    public static function designerBuyerForUser(array $user): ?array
    {
        $email = strtolower(trim((string)($user['email'] ?? '')));
        if ($email !== '') {
            $buyer = Database::one('SELECT * FROM lead_buyers WHERE LOWER(email)=? AND is_active=1 ORDER BY id DESC LIMIT 1', [$email]);
            if ($buyer) {
                return $buyer;
            }
        }

        $proId = (int)($user['pro_id'] ?? 0);
        if ($proId > 0) {
            $pro = Database::one('SELECT phone FROM pros WHERE id=?', [$proId]);
            $phone = preg_replace('/\D+/', '', (string)($pro['phone'] ?? '')) ?? '';
            if (strlen($phone) >= 10) {
                return Database::one('SELECT * FROM lead_buyers WHERE phone=? AND is_active=1 ORDER BY id DESC LIMIT 1', [$phone]);
            }
        }

        return null;
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
            'Wardrobe' => 95000,
            'Master Bedroom' => 110000,
            'Bedroom 2' => 90000,
            'Bedroom 3' => 90000,
            'Bathroom' => 60000,
            'Pooja Unit' => 35000,
            'False Ceiling' => 70000,
            'Painting' => 45000,
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
        $phone = self::normalizeLeadMobile((string)($data['phone'] ?? ''));
        if ($phone === '') {
            throw new InvalidArgumentException('A valid 10-digit mobile number is required.');
        }

        $existingLeadId = self::leadIdForMobile($phone);
        if ($existingLeadId !== null) {
            self::persistLeadPrice($existingLeadId);
            self::syncLeadToSingleLeadSale($existingLeadId);
            return $existingLeadId;
        }

        $leadId = Database::exec(
            'INSERT INTO leads (name, phone, phone_normalized, city, society_area, budget, requirement, pro_id, plan_type, source, status, floor_plan, package_tier, rooms_json, estimate)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['name'],
                $phone,
                $phone,
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
        self::persistLeadPrice($leadId);
        self::syncLeadToSingleLeadSale($leadId);
        return $leadId;
    }

    /**
     * Calculates a fixed sell price from lead quality. The score deliberately uses
     * only information that is actually captured on the homeowner lead.
     */
    public static function leadPriceForLead(array $lead): array
    {
        $score = 0;
        $city = mb_strtolower(trim((string)($lead['city'] ?? '')));
        $topTierCities = [
            'gurgaon', 'gurugram', 'delhi', 'new delhi', 'noida', 'greater noida',
            'hyderabad', 'pune', 'mumbai', 'navi mumbai', 'bengaluru', 'bangalore',
            'chennai', 'ahmedabad', 'kolkata', 'faridabad', 'ghaziabad',
        ];
        $locationScore = in_array($city, $topTierCities, true) ? 1 : 0;
        $score += $locationScore;

        $budget = self::budgetToRupees((string)($lead['budget'] ?? ''));
        $budgetScore = $budget >= 2000000 ? 2 : ($budget >= 1000000 ? 1 : 0);
        $score += $budgetScore;

        $coreFields = ['city', 'society_area', 'requirement', 'budget'];
        $filledCoreFields = 0;
        foreach ($coreFields as $field) {
            if (trim((string)($lead[$field] ?? '')) !== '') {
                $filledCoreFields++;
            }
        }
        $detailScore = $filledCoreFields === 4 ? 2 : ($filledCoreFields === 3 ? 1 : 0);
        $score += $detailScore;

        $extraFields = 0;
        foreach (['plan_type', 'floor_plan', 'package_tier', 'estimate'] as $field) {
            if (trim((string)($lead[$field] ?? '')) !== '') {
                $extraFields++;
            }
        }
        if (!empty($lead['rooms_json']) && $lead['rooms_json'] !== '[]') {
            $extraFields++;
        }
        $extraScore = $extraFields >= 2 ? 1 : 0;
        $score += $extraScore;

        $tierIndex = min(count(self::LEAD_PRICE_TIERS) - 1, max(0, $score));
        return [
            'amount' => self::LEAD_PRICE_TIERS[$tierIndex],
            'score' => $score,
            'tier_index' => $tierIndex,
            'factors' => [
                'top_tier_city' => $locationScore,
                'budget' => $budgetScore,
                'completed_core_fields' => $detailScore,
                'additional_project_details' => $extraScore,
                'filled_core_fields' => $filledCoreFields,
            ],
        ];
    }

    private static function storedLeadPrice(array $lead): float
    {
        $stored = (float)($lead['lead_price'] ?? 0);
        return in_array($stored, self::LEAD_PRICE_TIERS, true)
            ? $stored
            : (float)self::leadPriceForLead($lead)['amount'];
    }

    /** Stores the calculated price and keeps an existing single-lead listing aligned. */
    public static function persistLeadPrice(int $leadId): void
    {
        if ($leadId <= 0 || !self::tableHasColumn('leads', 'lead_price')) {
            return;
        }
        $lead = Database::one('SELECT * FROM leads WHERE id=?', [$leadId]);
        if (!$lead) {
            return;
        }
        $price = self::leadPriceForLead($lead);
        Database::exec('UPDATE leads SET lead_price=?, updated_at=NOW() WHERE id=?', [(float)$price['amount'], $leadId]);
        // A single-lead sale can have an admin-set commercial price. Keep it stable once listed.
    }

    /**
     * Saves each homeowner chatbot answer in the primary leads table. Drafts remain
     * non-saleable until the visitor has supplied the required details and consent.
     */
    public static function saveChatbotLeadProgress(array $data): array
    {
        $token = trim((string)($data['chat_session_token'] ?? ''));
        if (!preg_match('/^[a-zA-Z0-9-]{20,64}$/', $token)) {
            throw new InvalidArgumentException('Your chat session has expired. Please reopen the consultation chat.');
        }

        $draftPlanType = 'chatbot:' . $token;
        $lead = Database::one('SELECT * FROM leads WHERE plan_type=? ORDER BY id DESC LIMIT 1', [$draftPlanType]);
        $name = trim((string)($data['name'] ?? ($lead['name'] ?? '')));
        $phoneRaw = trim((string)($data['phone'] ?? ''));
        $phone = self::normalizeLeadMobile($phoneRaw);
        $city = trim((string)($data['city'] ?? ($lead['city'] ?? '')));
        $society = trim((string)($data['society_area'] ?? ($lead['society_area'] ?? '')));
        $budget = trim((string)($data['budget'] ?? ($lead['budget'] ?? '')));
        $requirement = trim((string)($data['requirement'] ?? ($lead['requirement'] ?? '')));
        $hasConsent = !empty($data['lead_consent']);
        $isComplete = $name !== '' && $phone !== '' && $city !== '' && $requirement !== '' && $hasConsent;

        $name = $name !== '' ? $name : 'Chatbot visitor';
        $city = $city !== '' ? $city : 'Pending';
        $requirement = $requirement !== '' ? $requirement : 'Home Interior Consultation';
        $storedPhone = $phone !== '' ? $phone : ('chatbot-' . substr(hash('sha256', $token), 0, 16));
        $status = $isComplete ? 'new' : 'contacted';
        $existingLeadId = $phone !== '' ? self::leadIdForMobile($phone) : null;
        $phoneNormalizedForDraft = $phone !== '' && ($existingLeadId === null || (int)($lead['id'] ?? 0) === $existingLeadId) ? $phone : null;

        if ($isComplete && $existingLeadId !== null && (int)($lead['id'] ?? 0) !== $existingLeadId) {
                Database::exec(
                    'UPDATE leads SET name=?, city=?, society_area=?, budget=?, requirement=?, plan_type=?, status=?, updated_at=NOW() WHERE id=?',
                    [$name, $city, $society ?: null, $budget ?: null, $requirement, $isComplete ? 'home_interior_designer_matching' : $draftPlanType, $status, $existingLeadId]
                );
                if ($lead) {
                    Database::exec('DELETE FROM leads WHERE id=? AND plan_type=?', [(int)$lead['id'], $draftPlanType]);
                }
                if ($isComplete) {
                    self::persistLeadPrice($existingLeadId);
                    self::syncLeadToSingleLeadSale($existingLeadId);
                }
                return ['lead_id' => $existingLeadId, 'complete' => $isComplete];
        }

        if ($lead) {
            Database::exec(
                'UPDATE leads SET name=?, phone=?, phone_normalized=?, city=?, society_area=?, budget=?, requirement=?, plan_type=?, status=?, updated_at=NOW() WHERE id=?',
                [$name, $storedPhone, $phoneNormalizedForDraft, $city, $society ?: null, $budget ?: null, $requirement, $isComplete ? 'home_interior_designer_matching' : $draftPlanType, $status, (int)$lead['id']]
            );
            $leadId = (int)$lead['id'];
        } else {
            $leadId = Database::exec(
                'INSERT INTO leads (name, phone, phone_normalized, city, society_area, budget, requirement, plan_type, source, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, "homepage", ?)',
                [$name, $storedPhone, $phoneNormalizedForDraft, $city, $society ?: null, $budget ?: null, $requirement, $isComplete ? 'home_interior_designer_matching' : $draftPlanType, $status]
            );
        }

        if ($isComplete) {
            self::persistLeadPrice($leadId);
            self::syncLeadToSingleLeadSale($leadId);
        }
        return ['lead_id' => $leadId, 'complete' => $isComplete];
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
        $hasProId = self::tableHasColumn('leads', 'pro_id');
        if (!empty($filters['designer_id']) && $hasProId) {
            $where[] = 'l.pro_id = ?';
            $params[] = (int)$filters['designer_id'];
        }
        $leadColumns = [
            'id', 'name', 'phone', 'city', 'society_area', 'budget', 'requirement', 'plan_type',
            'source', 'status', 'estimate', 'created_at',
        ];
        $detectedLeadColumns = array_filter($leadColumns, static fn(string $column): bool => self::tableHasColumn('leads', $column));
        $selects = [];
        foreach ($leadColumns as $column) {
            if (in_array($column, $detectedLeadColumns, true)) {
                $selects[] = 'l.`' . $column . '`';
            } else {
                $selects[] = 'NULL AS `' . $column . '`';
            }
        }
        if (!$detectedLeadColumns) {
            $selects = ['l.*'];
        }
        $selects[] = $hasProId && self::tableExists('pros') && self::tableHasColumn('pros', 'full_name')
            ? 'p.full_name AS pro_name'
            : 'NULL AS pro_name';
        $sql = 'SELECT ' . implode(', ', $selects) . ' FROM leads l';
        if ($hasProId && self::tableExists('pros')) {
            $sql .= ' LEFT JOIN pros p ON p.id = l.pro_id';
        }
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $orderColumn = self::tableHasColumn('leads', 'created_at') ? 'l.created_at' : 'l.id';
        $limit = max(25, min(10000, (int)($filters['limit'] ?? 100)));
        return Database::query($sql . ' ORDER BY ' . $orderColumn . ' DESC LIMIT ' . $limit, $params);
    }

    /** Public-safe, newest lead snapshots for lead-generation landing pages. */
    public static function recentPublicLeadPreviews(string $market = 'Delhi NCR', int $limit = 10): array
    {
        $limit = max(1, min(10, $limit));
        $marketKey = mb_strtolower(trim($market));
        $ncrCities = ['delhi', 'new delhi', 'gurgaon', 'gurugram', 'noida', 'greater noida', 'faridabad', 'ghaziabad'];
        $sales = array_filter(self::listSingleLeadSales(false), static function (array $sale) use ($marketKey, $ncrCities): bool {
            $city = mb_strtolower(trim((string)($sale['lead']['city'] ?? '')));
            if (in_array($marketKey, ['delhi ncr', 'ncr'], true)) {
                return in_array($city, $ncrCities, true);
            }
            return $marketKey === '' || $city === $marketKey;
        });

        return array_map(static fn(array $sale): array => [
            'name' => trim((string)($sale['lead']['name'] ?? 'Homeowner enquiry')) ?: 'Homeowner enquiry',
            'phone' => (string)($sale['lead']['phone'] ?? 'Masked'),
            'city' => self::singleLeadCityLabel((string)($sale['lead']['city'] ?? 'Delhi NCR')),
            'society_area' => trim((string)($sale['lead']['society_area'] ?? '')),
            'budget' => trim((string)($sale['lead']['budget'] ?? '')),
            'requirement' => trim((string)($sale['lead']['requirement'] ?? 'Interior design requirement')),
            'created_at' => (string)($sale['created_at'] ?? ''),
            'slug' => (string)($sale['slug'] ?? ''),
        ], array_slice(array_values($sales), 0, $limit));
    }

    public static function updateLeadStatus(int $leadId, string $status): void
    {
        Database::exec('UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ?', [$status, $leadId]);
    }

    public static function leadUploadSampleRows(): array
    {
        return [
            ['Name', 'Phone', 'City', 'Society / Area', 'Budget', 'Requirement', 'Plan Type', 'Source', 'Status', 'Floor Plan', 'Package Tier', 'Estimate'],
            ['Rohit Sharma', '9810012345', 'Gurgaon', 'DLF Phase 5', '10 lakh', 'Full home interiors for 3BHK apartment', 'uploaded', 'homepage', 'new', '3BHK', 'Premium', '850000'],
            ['Neha Gupta', '9876543210', 'Noida', 'Sector 137', '5 lakh', 'Modular kitchen and wardrobes', 'uploaded', 'homepage', 'new', '2BHK', 'Essential', '420000'],
            ['Amit Verma', '9955511122', 'Delhi', 'Vasant Kunj', '25 lakh', 'Luxury villa renovation with false ceiling and lighting', 'uploaded', 'homepage', 'new', '4BHK', 'Luxury', '1800000'],
        ];
    }

    public static function leadUploadSampleXlsx(): string
    {
        return self::buildSimpleXlsx(self::leadUploadSampleRows());
    }

    /** Returns an admin-safe Excel workbook for the requested backend dataset. */
    public static function adminExportXlsx(string $resource): string
    {
        $resource = strtolower(trim($resource));
        $records = match ($resource) {
            'leads' => self::listLeads(['limit' => 10000]),
            'professionals' => self::listProfessionalsForAdmin(),
            'professional-verification' => self::listPros([]),
            'designer-accounts' => self::designerAccountsForExport(),
            'quotations' => self::listQuotations([]),
            'rate-cards' => self::quotationRateCards([]),
            'quotation-packages' => self::quotationPackages(),
            'proposal-templates' => self::proposalTemplates(),
            'portfolios' => self::listPortfolioForAdmin(),
            'single-lead-sales' => self::listSingleLeadSales(true),
            'property-projects' => self::listRealEstateProjects([], true),
            'property-enquiries' => self::listPropertyEnquiries(),
            'designer-contact-queries' => self::listDesignerContactQueries(),
            'articles' => self::listArticles([], true),
            'content' => self::contentList(),
            'url-aliases' => self::listUrlAliases(),
            'design-ideas' => self::listDesignIdeas([], true),
            'design-idea-sections' => self::listDesignIdeaSections(true),
            'design-idea-leads' => self::listDesignIdeaLeads(),
            'lead-coupons' => self::listLeadCoupons(),
            'checkout-recovery' => self::abandonedCheckouts('all'),
            'site-search' => self::siteSearchAnalyticsRows(),
            default => throw new InvalidArgumentException('Unsupported admin export.'),
        };

        return self::buildSimpleXlsx(self::adminExportRows($records), substr(str_replace('-', ' ', $resource), 0, 31));
    }

    private static function adminExportRows(array $records): array
    {
        if (!$records) {
            return [['No records available']];
        }
        $columns = [];
        $normalized = [];
        foreach ($records as $record) {
            $line = [];
            foreach ((array)$record as $key => $value) {
                if (in_array((string)$key, ['password_hash', 'reset_token', 'token_hash'], true)) {
                    continue;
                }
                $line[(string)$key] = is_array($value) || is_object($value)
                    ? (string)json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    : (string)($value ?? '');
                $columns[(string)$key] = true;
            }
            $normalized[] = $line;
        }
        $headers = array_keys($columns);
        $rows = [array_map(static fn(string $header): string => ucwords(str_replace('_', ' ', $header)), $headers)];
        foreach ($normalized as $line) {
            $rows[] = array_map(static fn(string $header): string => $line[$header] ?? '', $headers);
        }
        return $rows;
    }

    public static function importLeadsFromUploadedFile(array $file): array
    {
        if (empty($file['tmp_name']) || !is_uploaded_file((string)$file['tmp_name'])) {
            throw new InvalidArgumentException('Please upload a valid Excel or CSV file.');
        }

        $name = strtolower((string)($file['name'] ?? ''));
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        if (!in_array($extension, ['xlsx', 'csv'], true)) {
            throw new InvalidArgumentException('Only .xlsx and .csv files are supported.');
        }

        $rows = $extension === 'xlsx'
            ? self::parseSimpleXlsx((string)$file['tmp_name'])
            : self::parseCsvRows((string)$file['tmp_name']);
        if (count($rows) < 2) {
            throw new InvalidArgumentException('The uploaded file does not contain lead rows.');
        }

        $headers = array_map(static fn($value): string => self::normalizeLeadUploadHeader((string)$value), $rows[0]);
        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach (array_slice($rows, 1) as $index => $row) {
            $lineNumber = $index + 2;
            if (!array_filter($row, static fn($value): bool => trim((string)$value) !== '')) {
                continue;
            }
            $data = [];
            foreach ($headers as $position => $key) {
                if ($key !== '') {
                    $data[$key] = trim((string)($row[$position] ?? ''));
                }
            }

            $lead = [
                'name' => $data['name'] ?? '',
                'phone' => self::normalizeLeadMobile((string)($data['phone'] ?? $data['mobile'] ?? '')),
                'city' => $data['city'] ?? '',
                'society_area' => $data['society_area'] ?? $data['society'] ?? $data['locality'] ?? '',
                'budget' => $data['budget'] ?? '',
                'requirement' => $data['requirement'] ?? $data['requirement_type'] ?? '',
                'plan_type' => $data['plan_type'] ?? 'uploaded',
                'source' => self::normalizeLeadSource((string)($data['source'] ?? 'homepage')),
                'status' => self::normalizeLeadStatus((string)($data['status'] ?? 'new')),
                'floor_plan' => self::normalizeFloorPlan((string)($data['floor_plan'] ?? $data['bhk'] ?? '')),
                'package_tier' => self::normalizePackageTier((string)($data['package_tier'] ?? '')),
                'estimate' => self::numericOrNull((string)($data['estimate'] ?? '')),
            ];

            $missing = [];
            foreach (['name', 'phone', 'city', 'requirement'] as $required) {
                if (trim((string)$lead[$required]) === '') {
                    $missing[] = str_replace('_', ' ', $required);
                }
            }
            if ($missing) {
                $skipped++;
                $errors[] = 'Row ' . $lineNumber . ': missing ' . implode(', ', $missing) . '.';
                continue;
            }

            if (self::leadIdForMobile((string)$lead['phone']) !== null) {
                self::syncLeadToSingleLeadSale((int)self::leadIdForMobile((string)$lead['phone']));
                $skipped++;
                $errors[] = 'Row ' . $lineNumber . ': duplicate mobile number skipped.';
                continue;
            }

            self::insertLeadImportRow($lead);
            $created++;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 30),
        ];
    }

    private static function insertLeadImportRow(array $lead): int
    {
        $lead['phone'] = self::normalizeLeadMobile((string)($lead['phone'] ?? ''));
        if ($lead['phone'] === '') {
            throw new InvalidArgumentException('A valid 10-digit mobile number is required.');
        }
        $lead['phone_normalized'] = $lead['phone'];
        $fields = [];
        $values = [];
        foreach ($lead as $field => $value) {
            if (self::tableHasColumn('leads', $field) && $value !== null && $value !== '') {
                $fields[] = '`' . $field . '`';
                $values[] = $value;
            }
        }

        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $leadId = Database::exec('INSERT INTO leads (' . implode(', ', $fields) . ') VALUES (' . $placeholders . ')', $values);
        self::persistLeadPrice($leadId);
        self::syncLeadToSingleLeadSale($leadId);
        return $leadId;
    }

    private static function normalizeLeadMobile(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?? '';
        if (strlen($digits) > 10) {
            $digits = substr($digits, -10);
        }

        return strlen($digits) === 10 ? $digits : '';
    }

    private static function leadIdForMobile(string $phone): ?int
    {
        if ($phone === '') {
            return null;
        }

        $column = self::tableHasColumn('leads', 'phone_normalized') ? 'phone_normalized' : 'phone';
        $row = Database::one('SELECT id FROM leads WHERE `' . $column . '` = ? LIMIT 1', [$phone]);
        return $row ? (int)$row['id'] : null;
    }

    private static function normalizeLeadUploadHeader(string $value): string
    {
        $key = strtolower(trim($value));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key) ?? '';
        $key = trim($key, '_');
        return [
            'mobile_number' => 'phone',
            'mobile' => 'phone',
            'society_area' => 'society_area',
            'society_project_name' => 'society_area',
            'society_name' => 'society_area',
            'area' => 'society_area',
            'locality' => 'society_area',
            'requirement_type' => 'requirement',
            'work_type' => 'requirement',
            'bhk' => 'floor_plan',
            'estimate_amount' => 'estimate',
        ][$key] ?? $key;
    }

    private static function normalizeLeadSource(string $source): string
    {
        $source = strtolower(trim($source));
        return in_array($source, ['homepage', 'profile', 'calculator', 'pricing'], true) ? $source : 'homepage';
    }

    private static function normalizeLeadStatus(string $status): string
    {
        $status = strtolower(trim($status));
        return in_array($status, ['new', 'contacted', 'converted'], true) ? $status : 'new';
    }

    private static function normalizeFloorPlan(string $value): ?string
    {
        $value = strtoupper(trim($value));
        if ($value !== '' && !str_ends_with($value, 'BHK') && preg_match('/^[1-4]$/', $value)) {
            $value .= 'BHK';
        }
        return in_array($value, ['1BHK', '2BHK', '3BHK', '4BHK'], true) ? $value : null;
    }

    private static function normalizePackageTier(string $value): ?string
    {
        $value = ucwords(strtolower(trim($value)));
        return in_array($value, ['Essential', 'Premium', 'Luxury'], true) ? $value : null;
    }

    private static function numericOrNull(string $value): ?float
    {
        $clean = preg_replace('/[^\d.]/', '', $value) ?? '';
        return $clean === '' ? null : (float)$clean;
    }

    private static function parseCsvRows(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new InvalidArgumentException('Unable to read CSV file.');
        }
        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = array_map(static fn($value): string => trim((string)$value), $row);
        }
        fclose($handle);
        return $rows;
    }

    private static function parseSimpleXlsx(string $path): array
    {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('XLSX import requires the PHP Zip extension. Please upload CSV if Zip is unavailable.');
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new InvalidArgumentException('Unable to open Excel file.');
        }
        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $xml = simplexml_load_string($sharedXml);
            if ($xml) {
                foreach ($xml->si as $si) {
                    $sharedStrings[] = trim((string)($si->t ?? ''));
                }
            }
        }
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheetXml === false) {
            throw new InvalidArgumentException('Excel file must contain a first worksheet.');
        }
        $xml = simplexml_load_string($sheetXml);
        if (!$xml) {
            throw new InvalidArgumentException('Unable to read first worksheet.');
        }
        $rows = [];
        foreach ($xml->sheetData->row as $rowNode) {
            $row = [];
            foreach ($rowNode->c as $cell) {
                $ref = (string)$cell['r'];
                $column = preg_replace('/\d+/', '', $ref) ?: 'A';
                $index = self::xlsxColumnIndex($column);
                $type = (string)$cell['t'];
                $value = (string)($cell->v ?? '');
                if ($type === 's') {
                    $value = $sharedStrings[(int)$value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = (string)($cell->is->t ?? '');
                }
                $row[$index] = trim($value);
            }
            if ($row) {
                ksort($row);
                $normalized = [];
                for ($i = 0, $max = max(array_keys($row)); $i <= $max; $i++) {
                    $normalized[] = $row[$i] ?? '';
                }
                $rows[] = $normalized;
            }
        }
        return $rows;
    }

    private static function xlsxColumnIndex(string $column): int
    {
        $index = 0;
        foreach (str_split(strtoupper($column)) as $char) {
            $index = ($index * 26) + (ord($char) - 64);
        }
        return max(0, $index - 1);
    }

    private static function buildSimpleXlsx(array $rows, string $sheetName = 'Leads'): string
    {
        if (!class_exists('ZipArchive')) {
            $csv = '';
            foreach ($rows as $row) {
                $csv .= implode(',', array_map(static fn($value): string => '"' . str_replace('"', '""', (string)$value) . '"', $row)) . "\n";
            }
            return $csv;
        }
        $temp = tempnam(sys_get_temp_dir(), 'lead-template-');
        $zip = new ZipArchive();
        $zip->open($temp, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $safeSheetName = htmlspecialchars(trim($sheetName) ?: 'Export', ENT_XML1, 'UTF-8');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="' . $safeSheetName . '" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $sheetRows = '';
        foreach ($rows as $rowIndex => $row) {
            $sheetRows .= '<row r="' . ($rowIndex + 1) . '">';
            foreach ($row as $colIndex => $value) {
                $cellRef = self::xlsxColumnName($colIndex + 1) . ($rowIndex + 1);
                $sheetRows .= '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . htmlspecialchars((string)$value, ENT_XML1, 'UTF-8') . '</t></is></c>';
            }
            $sheetRows .= '</row>';
        }
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>' . $sheetRows . '</sheetData></worksheet>');
        $zip->close();
        $content = (string)file_get_contents($temp);
        unlink($temp);
        return $content;
    }

    private static function xlsxColumnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }
        return $name;
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
        if (!self::tableExists('site_content')) {
            return [];
        }
        $rows = Database::query('SELECT id, key_name, content_value, content_type, updated_at FROM site_content ORDER BY updated_at DESC, key_name LIMIT 300');
        foreach ($rows as &$row) {
            $value = (string)($row['content_value'] ?? '');
            $row['content_value_full'] = $value;
            $row['content_value'] = strlen($value) > 1200 ? substr($value, 0, 1200) . '...' : $value;
        }
        unset($row);
        return $rows;
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

    public static function articleFilterOptions(): array
    {
        $fallback = self::professionalStandardOptions();
        return [
            'cities' => $fallback['cities'],
            'work_types' => $fallback['work_types'],
            'article_types' => ['Interior Guide', 'Design Ideas', 'Cost Guide', 'Lead Guide', 'Professional Growth', 'News', 'Case Study'],
            'states' => ['Delhi', 'Haryana', 'Uttar Pradesh'],
        ];
    }

    public static function listArticles(array $filters = [], bool $admin = false): array
    {
        if (!self::tableExists('articles')) {
            return [];
        }
        $where = [];
        $params = [];
        if (!$admin) {
            $where[] = 'is_active=1';
        }
        foreach (['article_type', 'work_type', 'city', 'state'] as $field) {
            $value = trim((string)($filters[$field] ?? ''));
            if ($value !== '') {
                $where[] = $field . '=?';
                $params[] = $value;
            }
        }
        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(title LIKE ? OR excerpt LIKE ? OR content_html LIKE ? OR article_type LIKE ? OR work_type LIKE ? OR city LIKE ?)';
            $search = '%' . $q . '%';
            array_push($params, $search, $search, $search, $search, $search, $search);
        }
        $sql = 'SELECT * FROM articles';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY is_featured DESC, COALESCE(published_at, created_at) DESC, id DESC';
        if (!empty($filters['limit'])) {
            $sql .= ' LIMIT ' . max(1, min(200, (int)$filters['limit']));
        }
        $rows = Database::query($sql, $params);
        foreach ($rows as &$row) {
            $row = self::normalizeArticleRow($row);
        }
        unset($row);
        return $rows;
    }

    public static function getArticle(string|int $identifier, bool $admin = false): ?array
    {
        if (!self::tableExists('articles')) {
            return null;
        }
        $field = is_int($identifier) || ctype_digit((string)$identifier) ? 'id' : 'slug';
        $sql = 'SELECT * FROM articles WHERE ' . $field . '=?';
        if (!$admin) {
            $sql .= ' AND is_active=1';
        }
        $row = Database::one($sql, [$identifier]);
        if (!$row) {
            return null;
        }
        $article = self::normalizeArticleRow($row);
        $article['faqs'] = self::articleFaqs((int)$article['id']);
        return $article;
    }

    public static function articleFaqs(int $articleId): array
    {
        if (!self::tableExists('article_faqs')) {
            return [];
        }
        return Database::query('SELECT id, question, answer, sequence_order FROM article_faqs WHERE article_id=? ORDER BY sequence_order ASC, id ASC', [$articleId]);
    }

    public static function saveArticle(array $data, ?int $id = null): int
    {
        $title = trim((string)($data['title'] ?? ''));
        $contentHtml = trim((string)($data['content_html'] ?? ''));
        if ($title === '') {
            throw new InvalidArgumentException('Article title is required.');
        }
        if (trim(strip_tags($contentHtml)) === '') {
            throw new InvalidArgumentException('Article content is required.');
        }
        $slug = trim((string)($data['slug'] ?? ''));
        $slug = $slug !== '' ? slugify($slug) : slugify($title);
        $slug = self::uniqueArticleSlug($slug, $id);
        $fields = [
            'title', 'slug', 'excerpt', 'content_html', 'article_type', 'work_type', 'city', 'state',
            'author_name', 'image_path', 'tags_json', 'meta_title', 'meta_description',
            'is_featured', 'is_active', 'created_by', 'published_at',
        ];
        if (self::tableHasColumn('articles', 'content')) {
            $fields[] = 'content';
        }
        if (self::tableHasColumn('articles', 'featured_image')) {
            $fields[] = 'featured_image';
        }
        if (self::tableHasColumn('articles', 'is_published')) {
            $fields[] = 'is_published';
        }
        $values = [];
        foreach ($fields as $field) {
            if ($field === 'title') {
                $values[] = $title;
            } elseif ($field === 'slug') {
                $values[] = $slug;
            } elseif (in_array($field, ['content_html', 'content'], true)) {
                $values[] = $contentHtml;
            } elseif ($field === 'article_type') {
                $values[] = trim((string)($data[$field] ?? 'Interior Guide')) ?: 'Interior Guide';
            } elseif ($field === 'featured_image') {
                $value = $data['image_path'] ?? null;
                $values[] = trim((string)$value) === '' ? null : $value;
            } elseif ($field === 'tags_json') {
                $values[] = json_encode(self::parseJsonArray($data[$field] ?? []), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } elseif ($field === 'is_published') {
                $values[] = !empty($data['is_active']) ? 1 : 0;
            } elseif (in_array($field, ['is_featured', 'is_active'], true)) {
                $values[] = !empty($data[$field]) ? 1 : 0;
            } elseif ($field === 'created_by') {
                $values[] = !empty($data[$field]) ? (int)$data[$field] : null;
            } elseif ($field === 'published_at') {
                $values[] = !empty($data[$field]) ? $data[$field] : null;
            } else {
                $value = $data[$field] ?? null;
                $values[] = trim((string)$value) === '' ? null : $value;
            }
        }
        if ($id) {
            $sets = implode(', ', array_map(static fn(string $field): string => $field . '=?', $fields));
            Database::exec('UPDATE articles SET ' . $sets . ' WHERE id=?', [...$values, $id]);
            $articleId = $id;
        } else {
            $articleId = Database::exec('INSERT INTO articles (' . implode(', ', $fields) . ') VALUES (' . implode(', ', array_fill(0, count($fields), '?')) . ')', $values);
        }
        self::replaceArticleFaqs($articleId, $data['faqs'] ?? []);
        self::syncArticleUrlAlias($articleId);
        SitemapService::regeneratePublicSitemap();
        return $articleId;
    }

    public static function deleteArticle(int $id): void
    {
        Database::exec('DELETE FROM articles WHERE id=?', [$id]);
        try {
            Database::exec("DELETE FROM url_aliases WHERE entity_table='articles' AND entity_id=?", [$id]);
        } catch (Throwable) {
            // Continue to sitemap refresh even if alias cleanup is unavailable.
        }
        SitemapService::regeneratePublicSitemap();
    }

    public static function relatedArticles(array $article, int $limit = 3): array
    {
        if (!self::tableExists('articles')) {
            return [];
        }
        $sql = 'SELECT * FROM articles WHERE id<>? AND is_active=1 AND (article_type=? OR work_type <=> ? OR city <=> ?) ORDER BY is_featured DESC, COALESCE(published_at, created_at) DESC LIMIT ' . max(1, min(8, $limit));
        $rows = Database::query($sql, [(int)$article['id'], (string)$article['article_type'], $article['work_type'] ?? null, $article['city'] ?? null]);
        foreach ($rows as &$row) {
            $row = self::normalizeArticleRow($row);
        }
        unset($row);
        return $rows;
    }

    private static function normalizeArticleRow(array $row): array
    {
        $row['tags_json'] = self::parseJsonArray($row['tags_json'] ?? '[]');
        $row['excerpt'] = trim((string)($row['excerpt'] ?? '')) ?: self::articleExcerpt((string)($row['content_html'] ?? ''), 170);
        $row['featured_image'] = (string)($row['image_path'] ?? '');
        return $row;
    }

    private static function articleExcerpt(string $html, int $limit = 170): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '');
        if (strlen($text) <= $limit) {
            return $text;
        }
        return rtrim(substr($text, 0, $limit - 3)) . '...';
    }

    private static function uniqueArticleSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug ?: 'article';
        $candidate = $base;
        $i = 2;
        do {
            $sql = 'SELECT id FROM articles WHERE slug=?';
            $params = [$candidate];
            if ($ignoreId) {
                $sql .= ' AND id<>?';
                $params[] = $ignoreId;
            }
            $exists = Database::one($sql, $params);
            if (!$exists) {
                return $candidate;
            }
            $candidate = $base . '-' . $i++;
        } while ($i < 200);
        return $base . '-' . time();
    }

    private static function replaceArticleFaqs(int $articleId, mixed $faqs): void
    {
        if (!self::tableExists('article_faqs')) {
            return;
        }
        Database::exec('DELETE FROM article_faqs WHERE article_id=?', [$articleId]);
        if (!is_array($faqs)) {
            return;
        }
        $sequence = 1;
        foreach ($faqs as $faq) {
            if (!is_array($faq)) {
                continue;
            }
            $question = trim((string)($faq['question'] ?? ''));
            $answer = trim((string)($faq['answer'] ?? ''));
            if ($question === '' && $answer === '') {
                continue;
            }
            if ($question === '' || $answer === '') {
                throw new InvalidArgumentException('Each FAQ needs both question and answer.');
            }
            Database::exec('INSERT INTO article_faqs (article_id, question, answer, sequence_order) VALUES (?, ?, ?, ?)', [$articleId, $question, $answer, $sequence++]);
        }
    }

    private static function syncArticleUrlAlias(int $articleId): void
    {
        $article = self::getArticle($articleId, true);
        if (!$article) {
            return;
        }
        self::syncUrlAlias([
            'path' => '/articles/' . (string)$article['slug'],
            'page_type' => 'article_detail',
            'entity_table' => 'articles',
            'entity_id' => $articleId,
            'source' => 'article',
            'meta_title' => $article['meta_title'] ?: $article['title'] . ' | HomeInteriors360',
            'meta_description' => $article['meta_description'] ?: $article['excerpt'],
            'h1' => $article['title'],
            'content_html' => $article['content_html'],
            'image_url' => $article['image_path'] ?? null,
            'canonical_url' => '/articles/' . (string)$article['slug'],
            'is_active' => !empty($article['is_active']) ? 1 : 0,
        ]);
    }

    private static function tableExists(string $table): bool
    {
        try {
            $pdo = Database::pdo();
            $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table) ?: $table;
            $stmt = $pdo->query('SHOW TABLES LIKE ' . $pdo->quote($safeTable));
            return (bool)($stmt ? $stmt->fetch(PDO::FETCH_NUM) : false);
        } catch (Throwable) {
            return false;
        }
    }

    private static function tableHasColumn(string $table, string $column): bool
    {
        $key = $table . '.' . $column;
        if (array_key_exists($key, self::$tableColumnCache)) {
            return self::$tableColumnCache[$key];
        }
        try {
            $safeTable = preg_replace('/[^a-zA-Z0-9_]/', '', $table) ?: $table;
            $rows = Database::query('SHOW COLUMNS FROM `' . $safeTable . '`');
            $columns = array_map(static fn(array $row): string => (string)($row['Field'] ?? ''), $rows);
            foreach ($columns as $existingColumn) {
                self::$tableColumnCache[$table . '.' . $existingColumn] = true;
            }
            self::$tableColumnCache[$key] = in_array($column, $columns, true);
        } catch (Throwable) {
            self::$tableColumnCache[$key] = false;
        }
        return self::$tableColumnCache[$key];
    }

    public static function leadMarketplaceCounts(string $dateFilter = 'all_time', ?string $startDate = null, ?string $endDate = null): array
    {
        $leads = self::leadRowsForDateFilter($dateFilter, $startDate, $endDate);
        $definitions = self::leadFilterDefinitions($leads);
        $cards = [];
        foreach ($definitions as $definition) {
            $matching = array_values(array_filter($leads, static fn(array $lead): bool => self::leadMatchesCriteria($lead, $definition['criteria'])));
            if ($matching === []) {
                continue;
            }
            $availableMatching = array_values(array_filter($matching, static fn(array $lead): bool => self::leadCanBeAssignedToBuyer((int)$lead['id'])));
            $count = count($availableMatching);
            $price = self::leadPriceForRows($availableMatching, false);
            $sampleLeads = array_map(
                static fn(array $lead): array => self::publicSampleLead($lead),
                self::sampleMatchingLeads($availableMatching !== [] ? $availableMatching : $matching, 3)
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
                'total_matching_lead_count' => count($matching),
                'is_sold_out' => $count === 0,
                'price_total' => $price['total'],
                'pricing' => $price,
                'price_tiers' => $price['lines'],
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

    private static function singleLeadSalesTableExists(): bool
    {
        return self::tableExists('single_lead_sales');
    }

    private static function singleLeadSaleSelect(): string
    {
        $soldCountSql = self::leadPurchaseLeadTableExists()
            ? 'COALESCE(COUNT(DISTINCT pll.buyer_id), 0)'
            : '0';
        $leadPrice = self::tableHasColumn('leads', 'lead_price') ? 'l.lead_price' : 'NULL AS lead_price';
        return 's.*, l.name, l.phone, l.city, l.society_area, l.budget, l.requirement, l.plan_type, l.source, l.floor_plan, l.package_tier, l.rooms_json, l.estimate, ' . $leadPrice . ', l.status AS lead_status, l.created_at AS lead_created_at,
                ' . $soldCountSql . ' AS sold_count';
    }

    private static function singleLeadSaleFromJoin(): string
    {
        return ' FROM single_lead_sales s
                 JOIN leads l ON l.id=s.lead_id'
            . (self::leadPurchaseLeadTableExists() ? ' LEFT JOIN lead_purchase_leads pll ON pll.lead_id=s.lead_id' : '');
    }

    private static function hydrateSingleLeadSale(array $row, bool $public = false): array
    {
        $soldCount = (int)($row['sold_count'] ?? 0);
        $maxBuyers = max(1, min(self::MAX_BUYERS_PER_LEAD, (int)($row['max_buyers'] ?? self::MAX_BUYERS_PER_LEAD)));
        $remaining = max(0, $maxBuyers - $soldCount);
        $status = (string)($row['status'] ?? 'draft');
        if ($remaining <= 0 && $status === 'active') {
            $status = 'sold_out';
        }
        return [
            'id' => (int)($row['id'] ?? 0),
            'lead_id' => (int)($row['lead_id'] ?? 0),
            'slug' => (string)($row['slug'] ?? ''),
            'title' => (string)($row['title'] ?? ''),
            'price_amount' => (float)($row['price_amount'] ?? self::SINGLE_LEAD_PRICE),
            'max_buyers' => $maxBuyers,
            'sold_count' => $soldCount,
            'remaining_slots' => $remaining,
            'status' => $status,
            'notes' => (string)($row['notes'] ?? ''),
            'created_at' => (string)($row['created_at'] ?? ''),
            'updated_at' => (string)($row['updated_at'] ?? ''),
            'lead' => [
                'id' => (int)($row['lead_id'] ?? 0),
                'name' => (string)($row['name'] ?? ''),
                'phone' => $public ? self::maskPhone((string)($row['phone'] ?? '')) : (string)($row['phone'] ?? ''),
                'city' => (string)($row['city'] ?? ''),
                'society_area' => (string)($row['society_area'] ?? ''),
                'budget' => (string)($row['budget'] ?? ''),
                'requirement' => (string)($row['requirement'] ?? ''),
                'plan_type' => (string)($row['plan_type'] ?? ''),
                'source' => (string)($row['source'] ?? ''),
                'floor_plan' => (string)($row['floor_plan'] ?? ''),
                'package_tier' => (string)($row['package_tier'] ?? ''),
                'rooms' => self::parseJsonArray($row['rooms_json'] ?? '[]'),
                'estimate' => isset($row['estimate']) ? (float)$row['estimate'] : null,
                'lead_price' => self::storedLeadPrice($row),
                'status' => (string)($row['lead_status'] ?? ''),
                'created_at' => (string)($row['lead_created_at'] ?? ''),
            ],
        ];
    }

    public static function listSingleLeadSales(bool $admin = false, array $filters = []): array
    {
        if (!self::singleLeadSalesTableExists()) {
            return [];
        }
        $where = $admin ? '' : " WHERE s.status IN ('active', 'sold_out')";
        $rows = Database::query(
            'SELECT ' . self::singleLeadSaleSelect() . self::singleLeadSaleFromJoin() . $where . ' GROUP BY s.id ORDER BY s.updated_at DESC LIMIT 300'
        );
        $sales = array_map(static fn(array $row): array => self::hydrateSingleLeadSale($row, !$admin), $rows);
        if (!$admin) {
            usort($sales, static function (array $left, array $right): int {
                $leftAvailable = (string)($left['status'] ?? '') === 'active' && (int)($left['remaining_slots'] ?? 0) > 0;
                $rightAvailable = (string)($right['status'] ?? '') === 'active' && (int)($right['remaining_slots'] ?? 0) > 0;
                return ($rightAvailable ? 1 : 0) <=> ($leftAvailable ? 1 : 0);
            });
        }
        if ($admin || $filters === []) {
            return $sales;
        }

        return array_values(array_filter($sales, static function (array $sale) use ($filters): bool {
            $lead = (array)($sale['lead'] ?? []);
            foreach (['city', 'requirement', 'budget', 'plan_type'] as $field) {
                $expected = trim((string)($filters[$field] ?? ''));
                if ($expected === '') {
                    continue;
                }
                $actual = trim((string)($lead[$field] ?? ''));
                if ($field === 'city') {
                    $actual = self::singleLeadCityLabel($actual);
                    $expected = self::singleLeadCityLabel($expected);
                }
                if (mb_strtolower($actual) !== mb_strtolower($expected)) {
                    return false;
                }
            }
            return true;
        }));
    }

    /** Lists each available lead created today (IST) with its stored quality price. */
    public static function syncTodayLeadsToSingleLeadSales(): int
    {
        if (!self::singleLeadSalesTableExists()) {
            return 0;
        }

        $india = new DateTimeZone('Asia/Kolkata');
        $utc = new DateTimeZone('UTC');
        $today = new DateTimeImmutable('today', $india);
        $tomorrow = $today->modify('+1 day');
        $start = $today->setTimezone($utc)->format('Y-m-d H:i:s');
        $end = $tomorrow->setTimezone($utc)->format('Y-m-d H:i:s');
        $availability = self::leadPurchaseLeadTableExists()
            ? ' AND (SELECT COUNT(DISTINCT pll.buyer_id) FROM lead_purchase_leads pll WHERE pll.lead_id=l.id) < ' . self::MAX_BUYERS_PER_LEAD
            : '';

        return Database::exec(
            'INSERT INTO single_lead_sales (lead_id, slug, title, price_amount, max_buyers, status, notes, created_by)
             SELECT l.id,
                    CONCAT("single-interior-lead-", l.id),
                    CONCAT(COALESCE(NULLIF(TRIM(l.city), ""), "Delhi NCR"), " Interior Lead #", l.id),
                    ' . (self::tableHasColumn('leads', 'lead_price') ? 'COALESCE(l.lead_price, 199)' : '199') . ', ' . self::MAX_BUYERS_PER_LEAD . ', "active", "Automatically listed from today\'s fresh lead inventory.", NULL
             FROM leads l
             WHERE l.created_at >= ?
               AND l.created_at < ?
               AND LOWER(COALESCE(l.status, "new")) = "new"
               AND NOT EXISTS (SELECT 1 FROM single_lead_sales s WHERE s.lead_id=l.id)'
                . $availability,
            [$start, $end]
        );
    }

    /** Adds one newly-created eligible lead to the single-lead catalog immediately. */
    private static function syncLeadToSingleLeadSale(int $leadId): void
    {
        if ($leadId <= 0 || !self::singleLeadSalesTableExists()) {
            return;
        }
        $availability = self::leadPurchaseLeadTableExists()
            ? ' AND (SELECT COUNT(DISTINCT pll.buyer_id) FROM lead_purchase_leads pll WHERE pll.lead_id=l.id) < ' . self::MAX_BUYERS_PER_LEAD
            : '';
        Database::exec(
            'INSERT INTO single_lead_sales (lead_id, slug, title, price_amount, max_buyers, status, notes, created_by)
             SELECT l.id,
                    CONCAT("single-interior-lead-", l.id),
                    CONCAT(COALESCE(NULLIF(TRIM(l.city), ""), "Delhi NCR"), " Interior Lead"),
                    ' . (self::tableHasColumn('leads', 'lead_price') ? 'COALESCE(l.lead_price, 199)' : '199') . ', ' . self::MAX_BUYERS_PER_LEAD . ', "active", "Automatically listed from fresh lead inventory.", NULL
             FROM leads l
             WHERE l.id=?
               AND LOWER(COALESCE(l.status, "new"))="new"
               AND NOT EXISTS (SELECT 1 FROM single_lead_sales s WHERE s.lead_id=l.id)'
                . $availability,
            [$leadId]
        );
    }

    /** Public filter values and their stable SEO slugs, derived only from active inventory. */
    public static function singleLeadSaleFilterOptions(): array
    {
        $options = ['cities' => [], 'requirements' => [], 'budgets' => []];
        foreach (self::listSingleLeadSales(false) as $sale) {
            $lead = (array)($sale['lead'] ?? []);
            foreach ([
                'cities' => ['field' => 'city', 'label' => self::singleLeadCityLabel((string)($lead['city'] ?? ''))],
                'requirements' => ['field' => 'requirement', 'label' => trim((string)($lead['requirement'] ?? ''))],
                'budgets' => ['field' => 'budget', 'label' => trim((string)($lead['budget'] ?? ''))],
            ] as $group => $item) {
                $label = trim((string)$item['label']);
                if ($label === '') {
                    continue;
                }
                $slug = slugify($label);
                $options[$group][$slug] = ['value' => $label, 'slug' => $slug];
            }
        }
        foreach ($options as $group => $items) {
            uasort($items, static fn(array $a, array $b): int => strcasecmp((string)$a['value'], (string)$b['value']));
            $options[$group] = array_values($items);
        }
        return $options;
    }

    public static function singleLeadSaleListingPath(array $filters = []): string
    {
        $segments = ['/single-lead'];
        $map = ['city' => 'in-', 'requirement' => 'work-', 'budget' => 'budget-'];
        foreach ($map as $field => $prefix) {
            $value = trim((string)($filters[$field] ?? ''));
            if ($value === '') {
                continue;
            }
            if ($field === 'city') {
                $value = self::singleLeadCityLabel($value);
            }
            $segments[] = $prefix . slugify($value);
        }
        return implode('/', $segments);
    }

    public static function singleLeadSaleFiltersFromPath(array $segments): array
    {
        $options = self::singleLeadSaleFilterOptions();
        $groups = [
            'in-' => ['group' => 'cities', 'field' => 'city'],
            'work-' => ['group' => 'requirements', 'field' => 'requirement'],
            'budget-' => ['group' => 'budgets', 'field' => 'budget'],
        ];
        $filters = [];
        foreach ($segments as $segment) {
            $segment = strtolower(trim((string)$segment));
            foreach ($groups as $prefix => $config) {
                if (!str_starts_with($segment, $prefix)) {
                    continue;
                }
                $slug = substr($segment, strlen($prefix));
                foreach ($options[$config['group']] as $option) {
                    if ((string)$option['slug'] === $slug) {
                        $filters[$config['field']] = (string)$option['value'];
                        break 2;
                    }
                }
            }
        }
        return $filters;
    }

    public static function singleLeadSaleSeoPaths(): array
    {
        $options = self::singleLeadSaleFilterOptions();
        $paths = ['/single-lead'];
        foreach (['cities' => 'city', 'requirements' => 'requirement', 'budgets' => 'budget'] as $group => $field) {
            foreach ($options[$group] as $option) {
                $paths[] = self::singleLeadSaleListingPath([$field => (string)$option['value']]);
            }
        }
        // Do not manufacture city/work combinations. Empty facet pages are weak
        // discovery targets and make it harder for crawlers to prioritise real stock.
        foreach (self::listSingleLeadSales(false) as $sale) {
            $lead = (array)($sale['lead'] ?? []);
            $city = self::singleLeadCityLabel((string)($lead['city'] ?? ''));
            $requirement = trim((string)($lead['requirement'] ?? ''));
            if ($city !== '' && $requirement !== '') {
                $paths[] = self::singleLeadSaleListingPath([
                    'city' => $city,
                    'requirement' => $requirement,
                ]);
            }
        }
        return array_slice(array_values(array_unique($paths)), 0, 250);
    }

    private static function singleLeadCityLabel(string $city): string
    {
        $normalized = mb_strtolower(trim($city));
        return match ($normalized) {
            'gurgaon', 'gurugram' => 'Gurgaon',
            'greater noida' => 'Greater Noida',
            'noida' => 'Noida',
            'delhi', 'new delhi' => 'Delhi',
            'ghaziabad' => 'Ghaziabad',
            'faridabad' => 'Faridabad',
            default => trim($city),
        };
    }

    public static function leadsAvailableForSingleSale(): array
    {
        if (!self::leadPurchaseLeadTableExists()) {
            $rows = Database::query('SELECT l.id, l.name, l.phone, l.city, l.society_area, l.budget, l.requirement, l.status, l.created_at, 0 AS sold_count FROM leads l ORDER BY l.created_at DESC LIMIT 300');
            return array_map(static function (array $row): array {
                $row['phone_masked'] = self::maskPhone((string)($row['phone'] ?? ''));
                $row['remaining_slots'] = self::MAX_BUYERS_PER_LEAD;
                return $row;
            }, $rows);
        }
        $rows = Database::query(
            'SELECT l.id, l.name, l.phone, l.city, l.society_area, l.budget, l.requirement, l.status, l.created_at,
                    COALESCE(COUNT(DISTINCT pll.buyer_id), 0) AS sold_count
             FROM leads l
             LEFT JOIN lead_purchase_leads pll ON pll.lead_id=l.id
             GROUP BY l.id
             HAVING sold_count < ' . self::MAX_BUYERS_PER_LEAD . '
             ORDER BY l.created_at DESC
             LIMIT 300'
        );
        return array_map(static function (array $row): array {
            $row['phone_masked'] = self::maskPhone((string)($row['phone'] ?? ''));
            $row['remaining_slots'] = max(0, self::MAX_BUYERS_PER_LEAD - (int)($row['sold_count'] ?? 0));
            return $row;
        }, $rows);
    }

    public static function saveSingleLeadSale(array $data, ?int $id = null): int
    {
        if (!self::singleLeadSalesTableExists()) {
            throw new RuntimeException('single_lead_sales table is missing. Please run the latest migration.');
        }
        $leadId = (int)($data['lead_id'] ?? 0);
        $lead = Database::one('SELECT * FROM leads WHERE id=?', [$leadId]);
        if (!$lead) {
            throw new InvalidArgumentException('Please select a valid lead.');
        }
        $title = trim((string)($data['title'] ?? ''));
        if ($title === '') {
            $title = trim('Single ' . (string)($lead['city'] ?? '') . ' Lead - ' . (string)($lead['society_area'] ?? ''), ' -');
        }
        $slugBase = slugify((string)($data['slug'] ?? $title . '-' . $leadId));
        $slug = $slugBase !== '' ? $slugBase : 'single-lead-' . $leadId;
        $existing = Database::one('SELECT id FROM single_lead_sales WHERE slug=? AND id<>? LIMIT 1', [$slug, (int)($id ?? 0)]);
        if ($existing) {
            $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
        }
        $status = in_array((string)($data['status'] ?? 'active'), ['draft', 'active', 'paused', 'sold_out'], true) ? (string)$data['status'] : 'active';
        $maxBuyers = max(1, min(self::MAX_BUYERS_PER_LEAD, (int)($data['max_buyers'] ?? self::MAX_BUYERS_PER_LEAD)));
        $notes = trim((string)($data['notes'] ?? ''));
        $createdBy = isset($data['created_by']) && (int)$data['created_by'] > 0 ? (int)$data['created_by'] : null;
        $autoPrice = self::storedLeadPrice($lead);
        $requestedPrice = trim((string)($data['price_amount'] ?? ''));
        $price = $requestedPrice === '' ? $autoPrice : (float)$requestedPrice;
        if ($price < 1 || $price > 1999) {
            throw new InvalidArgumentException('Single lead price must be between ₹1 and ₹1,999.');
        }
        $price = round($price, 2);
        if ($id) {
            Database::exec(
                'UPDATE single_lead_sales SET lead_id=?, slug=?, title=?, price_amount=?, max_buyers=?, status=?, notes=?, updated_at=NOW() WHERE id=?',
                [$leadId, $slug, $title, $price, $maxBuyers, $status, $notes, $id]
            );
            return $id;
        }
        return Database::exec(
            'INSERT INTO single_lead_sales (lead_id, slug, title, price_amount, max_buyers, status, notes, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [$leadId, $slug, $title, $price, $maxBuyers, $status, $notes, $createdBy]
        );
    }

    public static function deleteSingleLeadSale(int $id): void
    {
        if (self::singleLeadSalesTableExists()) {
            Database::exec('DELETE FROM single_lead_sales WHERE id=?', [$id]);
        }
    }

    public static function updateSingleLeadSalePrice(int $id, float $price): void
    {
        if (!self::singleLeadSalesTableExists()) {
            throw new RuntimeException('single_lead_sales table is missing. Please run the latest migration.');
        }
        if ($id <= 0 || $price < 1 || $price > 1999) {
            throw new InvalidArgumentException('Single lead price must be between ₹1 and ₹1,999.');
        }
        $updated = Database::exec('UPDATE single_lead_sales SET price_amount=?, updated_at=NOW() WHERE id=?', [round($price, 2), $id]);
        if ($updated < 1) {
            throw new InvalidArgumentException('Single lead sale was not found.');
        }
    }

    public static function updateSingleLeadSaleStatus(int $id, string $status): void
    {
        if (!self::singleLeadSalesTableExists()) {
            throw new RuntimeException('single_lead_sales table is missing. Please run the latest migration.');
        }
        if (!in_array($status, ['draft', 'active', 'paused', 'sold_out'], true)) {
            throw new InvalidArgumentException('Invalid single lead sale status.');
        }
        Database::exec('UPDATE single_lead_sales SET status=?, updated_at=NOW() WHERE id=?', [$status, $id]);
    }

    public static function getSingleLeadSaleBySlug(string $slug, bool $public = true): ?array
    {
        if (!self::singleLeadSalesTableExists()) {
            return null;
        }
        $where = $public ? "s.slug=? AND s.status IN ('active', 'sold_out')" : 's.slug=?';
        $row = Database::one(
            'SELECT ' . self::singleLeadSaleSelect() . self::singleLeadSaleFromJoin() . ' WHERE ' . $where . ' GROUP BY s.id LIMIT 1',
            [$slug]
        );
        return $row ? self::hydrateSingleLeadSale($row, $public) : null;
    }

    public static function getSingleLeadSaleById(int $id, bool $public = true): ?array
    {
        if (!self::singleLeadSalesTableExists()) {
            return null;
        }
        $where = $public ? "s.id=? AND s.status IN ('active', 'sold_out')" : 's.id=?';
        $row = Database::one(
            'SELECT ' . self::singleLeadSaleSelect() . self::singleLeadSaleFromJoin() . ' WHERE ' . $where . ' GROUP BY s.id LIMIT 1',
            [$id]
        );
        return $row ? self::hydrateSingleLeadSale($row, $public) : null;
    }

    public static function createSingleLeadPurchaseOrder(int $buyerId, int $saleId, string $orderId): array
    {
        $sale = self::getSingleLeadSaleById($saleId, true);
        if (!$sale || $sale['status'] !== 'active' || (int)$sale['remaining_slots'] <= 0) {
            throw new InvalidArgumentException('This lead is no longer available for single purchase.');
        }
        $leadId = (int)$sale['lead_id'];
        if (!self::leadCanBeAssignedToBuyer($leadId, $buyerId)) {
            throw new InvalidArgumentException('This lead is already purchased by your account or is sold out.');
        }
        $cart = [[
            'single_lead_sale_id' => $saleId,
            'lead_ids' => [$leadId],
            'filter_name' => (string)$sale['title'],
            'criteria' => ['single_lead_sale_id' => $saleId, 'lead_id' => $leadId],
            'date_filter' => 'single_lead',
            'lead_count' => 1,
            'selected_count' => 1,
            'price_total' => (float)$sale['price_amount'],
            'pricing' => [
                'total' => (float)$sale['price_amount'],
                'lines' => [['label' => 'Single lead', 'count' => 1, 'rate' => (float)$sale['price_amount'], 'amount' => (float)$sale['price_amount']]],
            ],
        ]];
        $purchaseId = Database::exec(
            'INSERT INTO lead_purchases (buyer_id, razorpay_order_id, coupon_code, discount_amount, amount_total, currency, payment_status, cart_json)
             VALUES (?, ?, NULL, 0, ?, ?, "pending", ?)',
            [$buyerId, $orderId, (float)$sale['price_amount'], defined('RAZORPAY_CURRENCY') ? RAZORPAY_CURRENCY : 'INR', json_encode($cart, JSON_UNESCAPED_UNICODE)]
        );
        $buyer = Database::one('SELECT name, email, phone FROM lead_buyers WHERE id=?', [$buyerId]) ?? [];
        self::recordAbandonedCheckout('single_lead', $buyer, (float)$sale['price_amount'], $cart, $orderId, $buyerId);
        return ['purchase_id' => $purchaseId, 'sale' => $sale];
    }

    public static function markSingleLeadPurchasePaid(string $orderId, int $buyerId, string $paymentId, string $signature): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $purchase = Database::one('SELECT * FROM lead_purchases WHERE razorpay_order_id=? AND buyer_id=? FOR UPDATE', [$orderId, $buyerId]);
            if (!$purchase) {
                throw new RuntimeException('Payment order mismatch.');
            }
            if (($purchase['payment_status'] ?? '') === 'paid') {
                $pdo->commit();
                return;
            }
            $cart = json_decode((string)($purchase['cart_json'] ?? '[]'), true);
            $item = is_array($cart[0] ?? null) ? $cart[0] : [];
            $saleId = (int)($item['single_lead_sale_id'] ?? 0);
            $leadId = (int)($item['lead_ids'][0] ?? 0);
            $sale = self::getSingleLeadSaleById($saleId, true);
            if (!$sale || $leadId <= 0 || (int)$sale['lead_id'] !== $leadId || !self::leadCanBeAssignedToBuyer($leadId, $buyerId)) {
                throw new RuntimeException('This lead is no longer available.');
            }
            if (round((float)($purchase['amount_total'] ?? 0), 2) !== round((float)$sale['price_amount'], 2)) {
                throw new RuntimeException('This lead price has changed. Please start checkout again.');
            }
            $purchaseItemId = Database::exec(
                'INSERT INTO lead_purchase_items (purchase_id, buyer_id, filter_name, filter_json, date_filter, leads_count, amount_total, pricing_json)
                 VALUES (?, ?, ?, ?, "single_lead", 1, ?, ?)',
                [
                    (int)$purchase['id'],
                    $buyerId,
                    (string)($sale['title'] ?? 'Single Interior Lead'),
                    json_encode(['criteria' => ['single_lead_sale_id' => $saleId, 'lead_id' => $leadId], 'lead_ids' => [$leadId]], JSON_UNESCAPED_UNICODE),
                    (float)$sale['price_amount'],
                    json_encode(['total' => (float)$sale['price_amount'], 'lines' => [['label' => 'Single lead', 'count' => 1, 'rate' => (float)$sale['price_amount'], 'amount' => (float)$sale['price_amount']]]], JSON_UNESCAPED_UNICODE),
                ]
            );
            $assigned = self::assignPurchasedLeadIds((int)$purchase['id'], $purchaseItemId, $buyerId, [$leadId]);
            if ($assigned === []) {
                throw new RuntimeException('This lead is no longer available.');
            }
            Database::exec(
                'UPDATE lead_purchases SET razorpay_payment_id=?, razorpay_signature=?, payment_status="paid", paid_at=NOW(), updated_at=NOW() WHERE id=?',
                [$paymentId, $signature, (int)$purchase['id']]
            );
            $fresh = self::getSingleLeadSaleById($saleId, false);
            if ($fresh && (int)$fresh['remaining_slots'] <= 0) {
                Database::exec('UPDATE single_lead_sales SET status="sold_out", updated_at=NOW() WHERE id=? AND status="active"', [$saleId]);
            }
            self::completeAbandonedCheckout($orderId);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    public static function leadPriceForCount(int $count, bool $firstTimeEligible = true): array
    {
        $free = $firstTimeEligible ? min($count, 3) : 0;
        $payable = max($count - $free, 0);
        $lines = [];
        if ($free > 0) {
            $lines[] = ['label' => 'First-time offer', 'count' => $free, 'rate' => 0, 'amount' => 0];
        }
        if ($payable > 0) {
            $lines[] = ['label' => 'Lead price starts from', 'count' => $payable, 'rate' => self::SINGLE_LEAD_PRICE, 'amount' => $payable * self::SINGLE_LEAD_PRICE];
        }
        return [
            'total' => array_sum(array_column($lines, 'amount')),
            'free_leads' => $free,
            'first_time_eligible' => $firstTimeEligible,
            'lines' => $lines,
        ];
    }

    /** Returns the real quality-price total for the exact lead rows being offered. */
    private static function leadPriceForRows(array $leads, bool $firstTimeEligible = false): array
    {
        $leads = self::sortLeadRowsForPricing($leads);
        $free = $firstTimeEligible ? min(count($leads), 3) : 0;
        $groups = [];
        foreach ($leads as $index => $lead) {
            if ($index < $free) {
                continue;
            }
            $price = self::storedLeadPrice($lead);
            $key = (string)(int)$price;
            $groups[$key] = ($groups[$key] ?? 0) + 1;
        }
        $lines = [];
        if ($free > 0) {
            $lines[] = ['label' => 'New-account lead benefit', 'count' => $free, 'rate' => 0, 'amount' => 0];
        }
        foreach ($groups as $price => $count) {
            $amount = (float)$price * $count;
            $lines[] = ['label' => 'Quality-priced leads', 'count' => $count, 'rate' => (float)$price, 'amount' => $amount];
        }
        return [
            'total' => array_sum(array_column($lines, 'amount')),
            'free_leads' => $free,
            'first_time_eligible' => $firstTimeEligible,
            'lines' => $lines,
        ];
    }

    /** Lowest price is selected first so package totals are predictable and reproducible. */
    private static function sortLeadRowsForPricing(array $leads): array
    {
        usort($leads, static function (array $left, array $right): int {
            $priceComparison = self::storedLeadPrice($left) <=> self::storedLeadPrice($right);
            if ($priceComparison !== 0) {
                return $priceComparison;
            }
            return strcmp((string)($right['created_at'] ?? ''), (string)($left['created_at'] ?? ''));
        });
        return $leads;
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
        $matching = self::sortLeadRowsForPricing(self::matchingLeadRows($criteria, $dateFilter, $startDate, $endDate));
        $matching = array_values(array_filter($matching, static fn(array $lead): bool => self::leadCanBeAssignedToBuyer((int)$lead['id'])));
        $price = self::leadPriceForRows(array_slice($matching, 0, $count), $firstTimeEligible);
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

    public static function leadCartSummary(array $cart, ?string $couponCode = null, bool $firstTimeEligible = true, ?int $buyerId = null): array
    {
        $items = [];
        $seenLeadKeys = [];
        $uniqueLeadCount = 0;
        $allSelectedLeads = [];
        foreach ($cart as $cartItem) {
            $normalized = self::normalizeLeadCartItem(is_array($cartItem) ? $cartItem : [], false);
            $requestedCount = (int)$normalized['selected_count'];
            $matching = self::sortLeadRowsForPricing(self::matchingLeadRows(
                $normalized['criteria'],
                $normalized['date_filter'],
                $normalized['start_date'],
                $normalized['end_date']
            ));
            $leadIds = [];
            $selectedLeads = [];
            foreach ($matching as $lead) {
                if (count($leadIds) >= $requestedCount) {
                    break;
                }
                if (!self::leadCanBeAssignedToBuyer((int)$lead['id'], $buyerId)) {
                    continue;
                }
                $uniqueKey = self::leadUniqueKey($lead);
                if (isset($seenLeadKeys[$uniqueKey])) {
                    continue;
                }
                $seenLeadKeys[$uniqueKey] = true;
                $leadIds[] = (int)$lead['id'];
                $selectedLeads[] = $lead;
            }
            $uniqueLeadCount += count($leadIds);
            $allSelectedLeads = array_merge($allSelectedLeads, $selectedLeads);
            $itemPricing = self::leadPriceForRows($selectedLeads, false);
            $itemAmount = (float)$itemPricing['total'];
            $normalized['requested_count'] = $requestedCount;
            $normalized['lead_count'] = count($leadIds);
            $normalized['unique_lead_count'] = count($leadIds);
            $normalized['duplicate_count'] = max(0, $requestedCount - count($leadIds));
            $normalized['lead_ids'] = $leadIds;
            $normalized['price_total'] = $itemAmount;
            $normalized['pricing'] = $itemPricing;
            if ($leadIds === []) {
                continue;
            }
            $items[] = $normalized;
        }
        $cartPricing = self::leadPriceForRows($allSelectedLeads, false);
        $subtotal = (float)$cartPricing['total'];
        $leadCount = $uniqueLeadCount;
        $firstTimeCredit = 0.0;
        if ($firstTimeEligible && $allSelectedLeads !== []) {
            $benefitLeads = array_slice(self::sortLeadRowsForPricing($allSelectedLeads), 0, 10);
            $firstTimeCredit = (float)self::leadPriceForRows($benefitLeads, false)['total'];
        }
        $coupon = null;
        $discount = 0.0;
        if ($couponCode) {
            $coupon = self::validateLeadCoupon($couponCode, max(0, $subtotal - $firstTimeCredit), $leadCount);
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
            'first_time_credit' => $firstTimeCredit,
            'grand_total' => max($subtotal - $firstTimeCredit - $discount, 0),
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
        return self::designerOnboardingLeadBenefitEligible($buyerId);
    }

    public static function createDesignerContactQuery(array $data): int
    {
        $name = trim((string)($data['name'] ?? ''));
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $phone = preg_replace('/\D+/', '', (string)($data['phone'] ?? '')) ?? '';
        $subject = trim((string)($data['subject'] ?? ''));
        $message = trim((string)($data['message'] ?? ''));
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($phone) < 10 || $subject === '' || $message === '' || empty($data['consent'])) {
            throw new InvalidArgumentException('Name, valid email, mobile number, subject, message, and consent are required.');
        }
        return Database::exec(
            'INSERT INTO designer_contact_queries (name, email, phone, firm_name, role_name, city, subject, message, linkedin_url, consent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)',
            [$name, $email, $phone, trim((string)($data['firm_name'] ?? '')) ?: null, trim((string)($data['role_name'] ?? '')) ?: null, trim((string)($data['city'] ?? '')) ?: null, $subject, $message, trim((string)($data['linkedin_url'] ?? '')) ?: null]
        );
    }

    public static function listDesignerContactQueries(): array
    {
        return Database::query('SELECT * FROM designer_contact_queries ORDER BY FIELD(status, "new", "in_progress", "resolved", "closed"), created_at DESC');
    }

    public static function updateDesignerContactQueryStatus(int $id, string $status): void
    {
        if (!in_array($status, ['new', 'in_progress', 'resolved', 'closed'], true)) {
            throw new InvalidArgumentException('Invalid contact query status.');
        }
        Database::exec('UPDATE designer_contact_queries SET status=?, updated_at=NOW() WHERE id=?', [$status, $id]);
    }

    public static function grantDesignerOnboardingLeadBenefit(int $userId, int $proId, int $buyerId): void
    {
        if ($userId <= 0 || $proId <= 0 || $buyerId <= 0) {
            return;
        }
        try {
            Database::exec('INSERT INTO designer_onboarding_lead_benefits (user_id, pro_id, buyer_id, lead_count, status) VALUES (?, ?, ?, 3, "available")', [$userId, $proId, $buyerId]);
        } catch (PDOException $e) {
            if ((string)$e->getCode() !== '23000') {
                throw $e;
            }
        }
    }

    public static function designerOnboardingLeadBenefitEligible(int $buyerId): bool
    {
        return Database::one('SELECT id FROM designer_onboarding_lead_benefits WHERE buyer_id=? AND status="available" LIMIT 1', [$buyerId]) !== null;
    }

    public static function redeemDesignerOnboardingLeadBenefit(int $buyerId): void
    {
        Database::exec('UPDATE designer_onboarding_lead_benefits SET status="redeemed", redeemed_at=NOW(), updated_at=NOW() WHERE buyer_id=? AND status="available"', [$buyerId]);
    }

    public static function autoAssignDesignerRegistrationLeads(array $user, ?array $buyer = null): array
    {
        $proId = (int)($user['pro_id'] ?? 0);
        $buyer = $buyer ?: self::designerBuyerForUser($user);
        $buyerId = (int)($buyer['id'] ?? 0);
        if ($proId <= 0 || $buyerId <= 0) {
            return ['assigned_count' => 0, 'is_complete' => false];
        }
        $benefit = Database::one('SELECT * FROM designer_onboarding_lead_benefits WHERE buyer_id=? LIMIT 1', [$buyerId]);
        if (!$benefit) {
            return ['assigned_count' => self::registrationLeadDeliveryCount($buyerId), 'is_complete' => true];
        }
        if ((string)$benefit['status'] !== 'available') {
            return ['assigned_count' => self::registrationLeadDeliveryCount($buyerId), 'is_complete' => true];
        }
        $pro = Database::one('SELECT city FROM pros WHERE id=?', [$proId]);
        $city = trim((string)($pro['city'] ?? ''));
        if ($city === '') {
            return ['assigned_count' => 0, 'is_complete' => false];
        }
        $cityKey = strtolower(str_replace([' ', '-'], '', $city));
        $cityAliases = $cityKey === 'gurgaon' || $cityKey === 'gurugram' ? ['gurgaon', 'gurugram'] : [$cityKey];
        $leads = Database::query('SELECT id, city FROM leads WHERE status="new" ORDER BY created_at DESC, id DESC LIMIT 500');
        $leadIds = [];
        foreach ($leads as $lead) {
            $leadCity = strtolower(str_replace([' ', '-'], '', (string)($lead['city'] ?? '')));
            if (!in_array($leadCity, $cityAliases, true) || !self::leadCanBeAssignedToBuyer((int)$lead['id'], $buyerId)) {
                continue;
            }
            $leadIds[] = (int)$lead['id'];
            if (count($leadIds) === min(3, (int)$benefit['lead_count'])) {
                break;
            }
        }
        if ($leadIds === []) {
            return ['assigned_count' => 0, 'is_complete' => false];
        }
        $orderId = 'registration_leads_' . $buyerId . '_' . time() . '_' . bin2hex(random_bytes(3));
        $cart = [[
            'filter_name' => 'Designer registration benefit',
            'criteria' => ['city' => $city],
            'date_filter' => 'all_time',
            'lead_ids' => $leadIds,
            'price_total' => 0,
            'pricing' => ['total' => 0, 'lines' => [['label' => 'Registration benefit', 'count' => count($leadIds), 'rate' => 0, 'amount' => 0]],],
        ]];
        $purchaseId = self::createLeadPurchaseOrder($buyerId, $cart, $orderId, 0);
        self::markLeadPurchasePaid($orderId, $buyerId, 'registration_benefit', 'registration_benefit');
        self::redeemDesignerOnboardingLeadBenefit($buyerId);
        return ['assigned_count' => count($leadIds), 'is_complete' => true, 'purchase_id' => $purchaseId];
    }

    public static function registrationLeadDeliveryCount(int $buyerId): int
    {
        $row = Database::one('SELECT COALESCE(SUM(i.leads_count), 0) AS c FROM lead_purchase_items i JOIN lead_purchases p ON p.id=i.purchase_id WHERE i.buyer_id=? AND i.filter_name="Designer registration benefit" AND p.payment_status="paid"', [$buyerId]);
        return (int)($row['c'] ?? 0);
    }

    private static function leadPurchaseLeadTableExists(): bool
    {
        static $exists = null;
        if ($exists !== null) {
            return $exists;
        }
        try {
            $row = Database::one('SHOW TABLES LIKE "lead_purchase_leads"');
            $exists = (bool)$row;
        } catch (Throwable) {
            $exists = false;
        }
        return $exists;
    }

    private static function userIdForBuyer(int $buyerId): ?int
    {
        $row = Database::one(
            'SELECT u.id
             FROM lead_buyers b
             JOIN users u ON LOWER(u.email)=LOWER(b.email)
             WHERE b.id=? AND u.is_active=1
             ORDER BY u.id DESC
             LIMIT 1',
            [$buyerId]
        );
        return !empty($row['id']) ? (int)$row['id'] : null;
    }

    private static function leadCanBeAssignedToBuyer(int $leadId, ?int $buyerId = null): bool
    {
        if ($leadId <= 0 || !self::leadPurchaseLeadTableExists()) {
            return true;
        }
        if ($buyerId !== null && $buyerId > 0) {
            $existing = Database::one('SELECT id FROM lead_purchase_leads WHERE buyer_id=? AND lead_id=? LIMIT 1', [$buyerId, $leadId]);
            if ($existing) {
                return false;
            }
        }
        $row = Database::one('SELECT COUNT(DISTINCT buyer_id) AS c FROM lead_purchase_leads WHERE lead_id=?', [$leadId]);
        return (int)($row['c'] ?? 0) < self::MAX_BUYERS_PER_LEAD;
    }

    private static function assignPurchasedLeadIds(int $purchaseId, int $purchaseItemId, int $buyerId, array $leadIds): array
    {
        $leadIds = array_values(array_unique(array_filter(array_map('intval', $leadIds))));
        if ($leadIds === [] || !self::leadPurchaseLeadTableExists()) {
            return $leadIds;
        }
        $userId = self::userIdForBuyer($buyerId);
        $assigned = [];
        foreach ($leadIds as $leadId) {
            // Lock the lead row while counting assignments so concurrent checkouts cannot oversell it.
            Database::one('SELECT id FROM leads WHERE id=? FOR UPDATE', [$leadId]);
            if (!self::leadCanBeAssignedToBuyer($leadId, $buyerId)) {
                continue;
            }
            Database::exec(
                'INSERT IGNORE INTO lead_purchase_leads (purchase_id, purchase_item_id, buyer_id, user_id, lead_id)
                 VALUES (?, ?, ?, ?, ?)',
                [$purchaseId, $purchaseItemId, $buyerId, $userId, $leadId]
            );
            $row = Database::one(
                'SELECT lead_id FROM lead_purchase_leads WHERE purchase_item_id=? AND buyer_id=? AND lead_id=?',
                [$purchaseItemId, $buyerId, $leadId]
            );
            if ($row) {
                $assigned[] = $leadId;
            }
        }
        return $assigned;
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
        return count(array_filter($leads, static fn(array $lead): bool => self::leadMatchesCriteria($lead, $criteria) && self::leadCanBeAssignedToBuyer((int)$lead['id'])));
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

    /** Checkout may create or reuse a buyer without exposing an account-password step. */
    public static function createOrLoginBuyerForCheckout(array $data): array
    {
        $name = trim((string)($data['name'] ?? ''));
        $phone = preg_replace('/\D+/', '', (string)($data['phone'] ?? '')) ?? '';
        $email = strtolower(trim((string)($data['email'] ?? '')));
        if ($name === '' || strlen($phone) < 10 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Name, valid email, and 10 digit mobile number are required.');
        }

        $existing = Database::one('SELECT * FROM lead_buyers WHERE (phone=? OR LOWER(email)=?) AND is_active=1 ORDER BY id DESC LIMIT 1', [$phone, $email]);
        if ($existing) {
            return $existing;
        }

        $id = Database::exec(
            'INSERT INTO lead_buyers (name, email, phone, password_hash) VALUES (?, ?, ?, ?)',
            [$name, $email, $phone, password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT)]
        );
        return Database::one('SELECT * FROM lead_buyers WHERE id=?', [$id]) ?? [];
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

    public static function recordAbandonedCheckout(string $type, array $contact, float $amount, array $snapshot, string $externalOrderId = '', ?int $buyerId = null, ?int $userId = null, ?int $proId = null): void
    {
        if (!self::tableExists('abandoned_checkout_attempts') || !in_array($type, ['single_lead', 'bulk_leads', 'proposal_generator'], true)) {
            return;
        }
        $phone = preg_replace('/\D+/', '', (string)($contact['phone'] ?? '')) ?? '';
        Database::exec(
            'INSERT INTO abandoned_checkout_attempts (checkout_type, checkout_status, external_order_id, buyer_id, user_id, pro_id, customer_name, customer_email, customer_phone, amount_total, currency, cart_snapshot_json, source_path)
             VALUES (?, "open", ?, ?, ?, ?, ?, ?, ?, ?, "INR", ?, ?)
             ON DUPLICATE KEY UPDATE checkout_status="open", buyer_id=VALUES(buyer_id), user_id=VALUES(user_id), pro_id=VALUES(pro_id), customer_name=VALUES(customer_name), customer_email=VALUES(customer_email), customer_phone=VALUES(customer_phone), amount_total=VALUES(amount_total), cart_snapshot_json=VALUES(cart_snapshot_json), source_path=VALUES(source_path), updated_at=NOW()',
            [
                $type,
                $externalOrderId !== '' ? $externalOrderId : null,
                $buyerId,
                $userId,
                $proId,
                trim((string)($contact['name'] ?? '')) ?: null,
                trim((string)($contact['email'] ?? '')) ?: null,
                $phone !== '' ? $phone : null,
                max(0, $amount),
                json_encode($snapshot, JSON_UNESCAPED_UNICODE),
                substr((string)($_SERVER['REQUEST_URI'] ?? ''), 0, 500) ?: null,
            ]
        );
    }

    public static function completeAbandonedCheckout(string $externalOrderId): void
    {
        if ($externalOrderId === '' || !self::tableExists('abandoned_checkout_attempts')) {
            return;
        }
        Database::exec('UPDATE abandoned_checkout_attempts SET checkout_status="completed", completed_at=NOW(), updated_at=NOW() WHERE external_order_id=? AND checkout_status="open"', [$externalOrderId]);
    }

    public static function abandonedCheckouts(string $status = 'open'): array
    {
        if (!self::tableExists('abandoned_checkout_attempts')) {
            return [];
        }
        $where = $status === 'all' ? '' : ' WHERE checkout_status="open"';
        return Database::query('SELECT * FROM abandoned_checkout_attempts' . $where . ' ORDER BY started_at DESC LIMIT 2000');
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
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $purchase = Database::one('SELECT * FROM lead_purchases WHERE razorpay_order_id = ? AND buyer_id = ? FOR UPDATE', [$orderId, $buyerId]);
            if (!$purchase) {
                throw new RuntimeException('Payment order mismatch.');
            }
            if (($purchase['payment_status'] ?? '') === 'paid') {
                $pdo->commit();
                return;
            }
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
                $purchaseItemId = Database::exec(
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
                $assignedLeadIds = self::assignPurchasedLeadIds((int)$purchase['id'], $purchaseItemId, $buyerId, $leadIds);
                if ($assignedLeadIds !== $leadIds) {
                    Database::exec(
                        'UPDATE lead_purchase_items SET filter_json=?, leads_count=? WHERE id=?',
                        [
                            json_encode([
                                'criteria' => $normalized['criteria'],
                                'start_date' => $normalized['start_date'],
                                'end_date' => $normalized['end_date'],
                                'lead_ids' => $assignedLeadIds,
                            ], JSON_UNESCAPED_UNICODE),
                            count($assignedLeadIds),
                            $purchaseItemId,
                        ]
                    );
                }
            }
            self::completeAbandonedCheckout($orderId);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
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
        if (self::leadPurchaseLeadTableExists()) {
            $assignedRows = Database::query(
                'SELECT l.id, l.name, l.phone, l.city, l.society_area, l.budget, l.requirement, l.source, l.status, l.estimate, l.created_at
                 FROM lead_purchase_leads pll
                 JOIN leads l ON l.id=pll.lead_id
                 WHERE pll.purchase_item_id=? AND pll.buyer_id=?
                 ORDER BY pll.id ASC',
                [$itemId, $buyerId]
            );
            if ($assignedRows !== []) {
                return $assignedRows;
            }
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
        $leadPrice = self::tableHasColumn('leads', 'lead_price') ? 'lead_price' : 'NULL AS lead_price';
        $sql = 'SELECT id, name, phone, city, society_area, budget, requirement, source, status, plan_type, floor_plan, package_tier, rooms_json, estimate, ' . $leadPrice . ', created_at FROM leads';
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

    /**
     * Searches the public catalogue in PHP so a query can match separate words
     * anywhere in a title, description, heading, or URL instead of requiring an
     * exact consecutive keyword phrase.
     */
    public static function searchPublicPages(string $query, string $sourcePage = '', int $limit = 8): array
    {
        $query = trim((string)preg_replace('/\s+/u', ' ', $query));
        $normalized = self::siteSearchText($query);
        if (mb_strlen($normalized) < 1) {
            return ['search_id' => null, 'results' => []];
        }

        $tokens = self::siteSearchTokens($normalized);
        $matches = [];
        foreach (self::siteSearchCatalogue() as $item) {
            $title = trim((string)($item['title'] ?? ''));
            $description = trim((string)($item['description'] ?? ''));
            $path = self::normalizeUrlAliasPath((string)($item['path'] ?? '/'));
            if ($title === '' || $path === '/' && $title === '') {
                continue;
            }
            $titleText = self::siteSearchText($title);
            $descriptionText = self::siteSearchText($description);
            $pathText = self::siteSearchText(str_replace(['/', '-'], ' ', $path));
            $score = 0;
            $matchedTokens = 0;
            foreach ($tokens as $token) {
                if (str_contains($titleText, $token)) {
                    $score += 26;
                    $matchedTokens++;
                } elseif (str_contains($descriptionText, $token)) {
                    $score += 12;
                    $matchedTokens++;
                } elseif (str_contains($pathText, $token)) {
                    $score += 8;
                    $matchedTokens++;
                }
            }
            if ($matchedTokens === 0) {
                continue;
            }
            if (str_contains($titleText . ' ' . $descriptionText, $normalized)) {
                $score += 18;
            }
            $score += min(10, $matchedTokens * 3);
            $item['path'] = $path;
            $item['title'] = $title;
            $item['description'] = self::siteSearchExcerpt($description);
            $item['score'] = $score;
            $matches[] = $item;
        }
        usort($matches, static fn(array $a, array $b): int => ($b['score'] <=> $a['score']) ?: strcmp((string)$a['title'], (string)$b['title']));
        $results = array_values(array_slice($matches, 0, max(1, min(12, $limit))));
        $searchId = self::trackSiteSearchQuery($query, $normalized, count($results), $sourcePage);
        foreach ($results as &$result) {
            unset($result['score']);
        }
        unset($result);
        return ['search_id' => $searchId, 'results' => $results];
    }

    public static function trackSiteSearchClick(array $data): void
    {
        $requestedPath = trim((string)($data['path'] ?? ''));
        if (!str_starts_with($requestedPath, '/') || str_starts_with($requestedPath, '//')) {
            return;
        }
        $path = self::normalizeUrlAliasPath($requestedPath);
        try {
            if (!self::tableExists('site_search_clicks')) {
                return;
            }
            Database::exec(
                'INSERT INTO site_search_clicks (search_query_id, session_key, result_path, result_title, result_type) VALUES (?, ?, ?, ?, ?)',
                [
                    !empty($data['search_id']) ? (int)$data['search_id'] : null,
                    self::siteSearchSessionKey(),
                    $path,
                    mb_substr(trim((string)($data['title'] ?? '')), 0, 500),
                    mb_substr(trim((string)($data['type'] ?? '')), 0, 80),
                ]
            );
        } catch (Throwable) {
            // Search tracking must never delay a visitor's selected destination.
        }
    }

    /** Admin-only search terms and selected-result activity. */
    public static function siteSearchAnalytics(array $filters = []): array
    {
        if (!self::ensureSiteSearchTables()) {
            return ['stats' => ['searches' => 0, 'unique_searches' => 0, 'clicks' => 0, 'click_through_rate' => 0], 'queries' => [], 'clicks' => []];
        }

        $days = max(1, min(365, (int)($filters['days'] ?? 30)));
        $term = trim((string)($filters['q'] ?? ''));
        $where = ['q.created_at >= DATE_SUB(NOW(), INTERVAL ' . $days . ' DAY)'];
        $params = [];
        if ($term !== '') {
            $where[] = '(q.query_text LIKE ? OR q.source_page LIKE ? OR c.result_path LIKE ? OR c.result_title LIKE ?)';
            $like = '%' . $term . '%';
            array_push($params, $like, $like, $like, $like);
        }
        $whereSql = implode(' AND ', $where);
        $stats = Database::one(
            'SELECT COUNT(DISTINCT q.id) AS searches, COUNT(DISTINCT q.normalized_query) AS unique_searches, COUNT(c.id) AS clicks
             FROM site_search_queries q
             LEFT JOIN site_search_clicks c ON c.search_query_id=q.id
             WHERE ' . $whereSql,
            $params
        ) ?: [];
        $searches = (int)($stats['searches'] ?? 0);
        $clicks = (int)($stats['clicks'] ?? 0);
        $stats = [
            'searches' => $searches,
            'unique_searches' => (int)($stats['unique_searches'] ?? 0),
            'clicks' => $clicks,
            'click_through_rate' => $searches > 0 ? round(($clicks / $searches) * 100, 1) : 0,
        ];
        $queries = Database::query(
            'SELECT q.id, q.query_text, q.normalized_query, q.result_count, q.source_page, q.created_at,
                    COUNT(c.id) AS click_count, MAX(c.result_path) AS clicked_path, MAX(c.result_title) AS clicked_title
             FROM site_search_queries q
             LEFT JOIN site_search_clicks c ON c.search_query_id=q.id
             WHERE ' . $whereSql . '
             GROUP BY q.id, q.query_text, q.normalized_query, q.result_count, q.source_page, q.created_at
             ORDER BY q.created_at DESC
             LIMIT 500',
            $params
        );
        $clickRows = Database::query(
            'SELECT c.id, c.result_path, c.result_title, c.result_type, c.created_at, q.query_text, q.source_page
             FROM site_search_clicks c
             LEFT JOIN site_search_queries q ON q.id=c.search_query_id
             WHERE c.created_at >= DATE_SUB(NOW(), INTERVAL ' . $days . ' DAY)' . ($term !== '' ? ' AND (q.query_text LIKE ? OR c.result_path LIKE ? OR c.result_title LIKE ?)' : '') . '
             ORDER BY c.created_at DESC
             LIMIT 300',
            $term !== '' ? [$like, $like, $like] : []
        );
        return ['stats' => $stats, 'queries' => $queries, 'clicks' => $clickRows];
    }

    public static function siteSearchAnalyticsRows(): array
    {
        $analytics = self::siteSearchAnalytics(['days' => 365]);
        return array_map(static function (array $query): array {
            return [
                'searched_at' => $query['created_at'] ?? '',
                'search_query' => $query['query_text'] ?? '',
                'normalized_query' => $query['normalized_query'] ?? '',
                'source_page' => $query['source_page'] ?? '',
                'suggestions_shown' => $query['result_count'] ?? 0,
                'click_count' => $query['click_count'] ?? 0,
                'clicked_page' => $query['clicked_path'] ?? '',
                'clicked_title' => $query['clicked_title'] ?? '',
            ];
        }, $analytics['queries']);
    }

    private static function siteSearchCatalogue(): array
    {
        $items = [];
        $add = static function (array $item) use (&$items): void {
            if (empty($item['is_indexable'])) {
                return;
            }
            $path = self::normalizeUrlAliasPath((string)($item['path'] ?? ''));
            if ($path === '/' && ($item['title'] ?? '') === '') {
                return;
            }
            $item['path'] = $path;
            if (!isset($items[$path]) || strlen((string)($item['description'] ?? '')) > strlen((string)($items[$path]['description'] ?? ''))) {
                $items[$path] = $item;
            }
        };

        foreach (self::siteSearchStaticPages() as $page) {
            $page['is_indexable'] = true;
            $add($page);
        }
        try {
            if (self::tableExists('url_aliases')) {
                foreach (Database::query("SELECT path, page_type, meta_title, meta_description, h1 FROM url_aliases WHERE is_active=1 AND path NOT LIKE '/admin%' AND path NOT LIKE '/api%' AND path <> '/properties' AND path NOT LIKE '/property/%' AND path NOT LIKE '/professionals/%' AND path NOT LIKE '/portfolio/%' AND path NOT LIKE '/design-ideas/%' AND COALESCE(robots, '') NOT LIKE '%noindex%' ORDER BY updated_at DESC LIMIT 2500") as $row) {
                    $add(['path' => $row['path'], 'title' => $row['meta_title'] ?: ($row['h1'] ?: self::siteSearchLabelFromPath((string)$row['path'])), 'description' => $row['meta_description'] ?? '', 'type' => self::siteSearchTypeLabel((string)($row['page_type'] ?? 'Page')), 'is_indexable' => true]);
                }
            }
            if (self::tableExists('articles')) {
                $publishedFilter = self::tableHasColumn('articles', 'is_published') ? ' AND is_published=1' : '';
                foreach (Database::query("SELECT slug, title, excerpt, meta_title, meta_description, article_type, city, work_type FROM articles WHERE is_active=1{$publishedFilter} AND TRIM(COALESCE(slug, ''))<>'' AND (published_at IS NULL OR published_at<=NOW()) ORDER BY COALESCE(published_at, created_at) DESC LIMIT 1000") as $row) {
                    $add(['path' => '/articles/' . $row['slug'], 'title' => $row['meta_title'] ?: $row['title'], 'description' => ($row['meta_description'] ?: $row['excerpt']) . ' ' . ($row['city'] ?? '') . ' ' . ($row['work_type'] ?? ''), 'type' => 'Article', 'is_indexable' => true]);
                }
            }
            if (self::tableExists('design_ideas')) {
                $photoSourceFilter = self::tableHasColumn('design_ideas', 'photo_source_name') ? " AND TRIM(COALESCE(photo_source_name, ''))<>''" : '';
                foreach (Database::query("SELECT slug, name, short_description, meta_title, meta_description, type, city, style, color FROM design_ideas WHERE is_active=1{$photoSourceFilter} AND TRIM(COALESCE(slug, ''))<>'' ORDER BY is_featured DESC, updated_at DESC LIMIT 1000") as $row) {
                    $add(['path' => '/design-ideas/idea/' . $row['slug'], 'title' => $row['meta_title'] ?: $row['name'], 'description' => ($row['meta_description'] ?: $row['short_description']) . ' ' . implode(' ', array_filter([$row['type'] ?? '', $row['city'] ?? '', $row['style'] ?? '', $row['color'] ?? ''])), 'type' => 'Design idea', 'is_indexable' => true]);
                }
            }
            if (self::tableExists('design_idea_aliases')) {
                foreach (Database::query("SELECT slug, title, subtitle, meta_title, meta_description FROM design_idea_aliases WHERE is_active=1 AND TRIM(COALESCE(slug, ''))<>'' ORDER BY updated_at DESC LIMIT 200") as $row) {
                    $add(['path' => '/design-ideas/' . $row['slug'], 'title' => $row['meta_title'] ?: $row['title'], 'description' => $row['meta_description'] ?: ($row['subtitle'] ?? ''), 'type' => 'Design ideas', 'is_indexable' => true]);
                }
            }
            if (self::tableExists('pros')) {
                foreach (Database::query("SELECT slug, full_name, profile_description, role, city, primary_work_type, specialization FROM pros WHERE is_active=1 AND TRIM(COALESCE(slug, ''))<>'' ORDER BY verification_status DESC, updated_at DESC LIMIT 1000") as $row) {
                    $add(['path' => '/professionals/' . $row['slug'], 'title' => $row['full_name'] . ' | HomeInteriors360', 'description' => implode(' ', array_filter([$row['profile_description'] ?? '', $row['role'] ?? '', $row['city'] ?? '', $row['primary_work_type'] ?? '', $row['specialization'] ?? ''])), 'type' => 'Professional', 'is_indexable' => true]);
                }
            }
            if (self::tableExists('projects')) {
                $projectModerationFilter = self::tableHasColumn('projects', 'moderation_status') ? " AND COALESCE(p.moderation_status, 'APPROVED')='APPROVED'" : '';
                foreach (Database::query("SELECT p.slug, p.project_name, p.project_description, p.location, p.work_type, p.design_style, pr.full_name FROM projects p JOIN pros pr ON pr.id=p.pro_id AND pr.is_active=1 WHERE TRIM(COALESCE(p.slug, ''))<>''{$projectModerationFilter} ORDER BY p.updated_at DESC LIMIT 800") as $row) {
                    $add(['path' => '/portfolio/' . $row['slug'], 'title' => $row['project_name'] . ' | ' . $row['full_name'], 'description' => implode(' ', array_filter([$row['project_description'] ?? '', $row['location'] ?? '', $row['work_type'] ?? '', $row['design_style'] ?? ''])), 'type' => 'Portfolio', 'is_indexable' => true]);
                }
            }
            if (self::tableExists('home_service_nodes')) {
                foreach (Database::query('SELECT s.slug, s.name, s.short_description, c.slug AS category_slug, c.name AS category_name FROM home_service_nodes s JOIN home_service_nodes c ON c.id=s.parent_id AND c.node_type="category" WHERE s.node_type="service" AND s.is_active=1 AND c.is_active=1 ORDER BY c.sort_order, s.sort_order') as $row) {
                    $add(['path' => '/services/' . $row['category_slug'] . '/' . $row['slug'], 'title' => $row['name'] . ' Services | HomeInteriors360', 'description' => ($row['short_description'] ?? '') . ' ' . ($row['category_name'] ?? ''), 'type' => 'Home service', 'is_indexable' => true]);
                }
            }
        } catch (Throwable) {
            // Static pages still provide a useful search result during a partial migration.
        }
        return array_values($items);
    }

    private static function siteSearchStaticPages(): array
    {
        return [
            ['path' => '/', 'title' => 'HomeInteriors360 | Interior Design, Decor & Home Services', 'description' => 'Find interior designers, home services, design ideas, calculators and free consultations.', 'type' => 'Page'],
            ['path' => '/home-interior-hire-a-designer', 'title' => 'Hire an Interior Designer | HomeInteriors360', 'description' => 'Share your home interior requirement and get matched with suitable designers.', 'type' => 'Page'],
            ['path' => '/professionals', 'title' => 'Find Interior Designers & Architects | HomeInteriors360', 'description' => 'Browse professional interior designers, architects and contractors by city and work type.', 'type' => 'Directory'],
            ['path' => '/home-services', 'title' => 'Home Services | HomeInteriors360', 'description' => 'Explore cleaning, repair, electrical, plumbing, painting, moving and interior services.', 'type' => 'Home service'],
            ['path' => '/design-ideas', 'title' => 'Interior Design Ideas | HomeInteriors360', 'description' => 'Browse kitchen, bedroom, living room, bathroom and home interior design ideas.', 'type' => 'Design ideas'],
            ['path' => '/articles', 'title' => 'Interior Design Articles & Guides | HomeInteriors360', 'description' => 'Read guides for interiors, home decor, renovation, budgets and home services.', 'type' => 'Article'],
            ['path' => '/cost-calculator', 'title' => 'Interior Cost Calculator | HomeInteriors360', 'description' => 'Estimate interior design and home renovation budgets.', 'type' => 'Calculator'],
            ['path' => '/kitchen-cost-calculator', 'title' => 'Kitchen Cost Calculator | HomeInteriors360', 'description' => 'Estimate a modular kitchen budget and finishes.', 'type' => 'Calculator'],
            ['path' => '/full-home-cost-calculator', 'title' => 'Full Home Interior Cost Calculator | HomeInteriors360', 'description' => 'Estimate a full home interiors budget by BHK and finish tier.', 'type' => 'Calculator'],
            ['path' => '/single-lead', 'title' => 'Buy Verified Home Buyer Leads | HomeInteriors360', 'description' => 'Browse verified homeowner enquiries for professionals.', 'type' => 'Lead marketplace'],
            ['path' => '/interior-designer-registration', 'title' => 'Register as an Interior Designer | HomeInteriors360', 'description' => 'Create a professional profile, upload portfolio work and receive leads.', 'type' => 'Professional'],
            ['path' => '/for-professionals', 'title' => 'Tools for Interior Design Professionals | HomeInteriors360', 'description' => 'Professional listing, portfolio, leads and proposal builder tools.', 'type' => 'Professional'],
        ];
    }

    private static function trackSiteSearchQuery(string $query, string $normalized, int $resultCount, string $sourcePage): ?int
    {
        try {
            if (!self::ensureSiteSearchTables()) {
                return null;
            }
            return Database::exec(
                'INSERT INTO site_search_queries (session_key, query_text, normalized_query, result_count, source_page) VALUES (?, ?, ?, ?, ?)',
                [self::siteSearchSessionKey(), mb_substr($query, 0, 255), mb_substr($normalized, 0, 255), $resultCount, mb_substr(self::normalizeUrlAliasPath($sourcePage ?: '/'), 0, 500)]
            );
        } catch (Throwable) {
            return null;
        }
    }

    private static function siteSearchSessionKey(): string
    {
        $key = (string)($_COOKIE['hi360_search_session'] ?? '');
        if (!preg_match('/^[a-f0-9]{32}$/', $key)) {
            $key = bin2hex(random_bytes(16));
            setcookie('hi360_search_session', $key, ['expires' => time() + 60 * 60 * 24 * 90, 'path' => '/', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'httponly' => true, 'samesite' => 'Lax']);
        }
        return $key;
    }

    /**
     * Shared-hosting fallback for a missed additive migration. The statements are
     * fixed and idempotent; no request data is ever used as SQL.
     */
    private static function ensureSiteSearchTables(): bool
    {
        static $ready = null;
        if ($ready !== null) {
            return $ready;
        }
        try {
            if (!self::tableExists('site_search_queries')) {
                Database::pdo()->exec('CREATE TABLE IF NOT EXISTS site_search_queries (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    session_key CHAR(32) DEFAULT NULL,
                    query_text VARCHAR(255) NOT NULL,
                    normalized_query VARCHAR(255) NOT NULL,
                    result_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                    source_page VARCHAR(500) DEFAULT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_site_search_queries_created (created_at),
                    INDEX idx_site_search_queries_normalized (normalized_query),
                    INDEX idx_site_search_queries_session (session_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            }
            if (!self::tableExists('site_search_clicks')) {
                Database::pdo()->exec('CREATE TABLE IF NOT EXISTS site_search_clicks (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    search_query_id BIGINT UNSIGNED DEFAULT NULL,
                    session_key CHAR(32) DEFAULT NULL,
                    result_path VARCHAR(500) NOT NULL,
                    result_title VARCHAR(500) DEFAULT NULL,
                    result_type VARCHAR(80) DEFAULT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_site_search_clicks_created (created_at),
                    INDEX idx_site_search_clicks_query (search_query_id),
                    INDEX idx_site_search_clicks_path (result_path),
                    CONSTRAINT fk_site_search_clicks_query FOREIGN KEY (search_query_id) REFERENCES site_search_queries(id) ON DELETE SET NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            }
            return $ready = self::tableExists('site_search_queries') && self::tableExists('site_search_clicks');
        } catch (Throwable) {
            return $ready = false;
        }
    }

    private static function siteSearchText(string $value): string
    {
        $value = trim(strip_tags(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        $value = mb_strtolower($value, 'UTF-8');
        return trim((string)preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value));
    }

    private static function siteSearchTokens(string $normalized): array
    {
        $stopWords = ['and', 'the', 'for', 'with', 'near', 'from', 'how', 'what', 'where', 'a', 'an', 'in', 'of', 'to'];
        $tokens = array_filter(preg_split('/\s+/u', $normalized) ?: [], static fn(string $token): bool => mb_strlen($token) >= 2 && !in_array($token, $stopWords, true));
        return array_values(array_unique($tokens)) ?: [$normalized];
    }

    private static function siteSearchExcerpt(string $value): string
    {
        $value = trim((string)preg_replace('/\s+/u', ' ', strip_tags($value)));
        return mb_strlen($value) > 160 ? mb_substr($value, 0, 157) . '...' : $value;
    }

    private static function siteSearchLabelFromPath(string $path): string
    {
        return ucwords(str_replace('-', ' ', trim($path, '/'))) ?: 'HomeInteriors360';
    }

    private static function siteSearchTypeLabel(string $value): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $value ?: 'Page'));
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

    /** Values used by the editor. Unlike public filters, drafts are included. */
    public static function designIdeaAdminOptions(): array
    {
        $ideaValues = static function (string $field): array {
            $allowed = ['type', 'color', 'city', 'state', 'style', 'layout', 'location', 'photo_source_name'];
            if (!in_array($field, $allowed, true)) {
                return [];
            }
            return array_map(
                static fn(array $row): string => trim((string)$row[$field]),
                Database::query('SELECT DISTINCT ' . $field . ' FROM design_ideas WHERE ' . $field . ' IS NOT NULL AND TRIM(' . $field . ')<>"" ORDER BY ' . $field)
            );
        };
        $cities = array_merge($ideaValues('city'), array_map(static fn(array $row): string => trim((string)$row['city']), Database::query('SELECT DISTINCT city FROM pros WHERE city IS NOT NULL AND TRIM(city)<>"" ORDER BY city')));
        $jsonValues = static function (string $field): array {
            $values = [];
            foreach (Database::query('SELECT ' . $field . ' FROM design_ideas WHERE ' . $field . ' IS NOT NULL AND TRIM(' . $field . ')<>""') as $row) {
                foreach (self::parseJsonArray($row[$field] ?? []) as $value) {
                    $value = trim((string)$value);
                    if ($value !== '') $values[$value] = true;
                }
            }
            $values = array_keys($values);
            sort($values, SORT_NATURAL | SORT_FLAG_CASE);
            return $values;
        };
        return [
            'types' => $ideaValues('type'),
            'colors' => $ideaValues('color'),
            'cities' => array_values(array_unique(array_filter($cities))),
            'states' => $ideaValues('state'),
            'styles' => $ideaValues('style'),
            'layouts' => $ideaValues('layout'),
            'locations' => $ideaValues('location'),
            'photo_sources' => $ideaValues('photo_source_name'),
            'materials' => $jsonValues('materials_json'),
            'tags' => $jsonValues('tags_json'),
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
            $where[] = 'di.is_active=1';
            $where[] = "TRIM(COALESCE(di.photo_source_name, '')) <> ''";
        }
        foreach (['type', 'color', 'city', 'state', 'style', 'layout'] as $field) {
            $value = trim((string)($filters[$field] ?? ''));
            if ($value !== '') {
                $where[] = 'di.' . $field . '=?';
                $params[] = $value;
            }
        }
        $q = trim((string)($filters['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(di.name LIKE ? OR di.location LIKE ? OR di.city LIKE ? OR di.type LIKE ? OR di.color LIKE ? OR di.style LIKE ?)';
            $search = '%' . $q . '%';
            array_push($params, $search, $search, $search, $search, $search, $search);
        }
        $sql = 'SELECT di.*, p.full_name AS recommended_designer_name, p.slug AS recommended_designer_slug, p.city AS recommended_designer_city
                FROM design_ideas di
                LEFT JOIN pros p ON p.id=di.recommended_designer_id AND p.is_active=1';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY di.is_featured DESC, di.updated_at DESC, di.id DESC';
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
        $sql = 'SELECT di.*, p.full_name AS recommended_designer_name, p.slug AS recommended_designer_slug, p.city AS recommended_designer_city
                FROM design_ideas di
                LEFT JOIN pros p ON p.id=di.recommended_designer_id AND p.is_active=1
                WHERE di.' . $field . '=?';
        if (!$admin) {
            $sql .= " AND di.is_active=1 AND TRIM(COALESCE(di.photo_source_name, '')) <> ''";
        }
        $row = Database::one($sql, [$identifier]);
        return $row ? self::normalizeDesignIdeaRow($row) : null;
    }

    private static function normalizeDesignIdeaRow(array $row): array
    {
        $row['gallery_json'] = self::parseJsonArray($row['gallery_json'] ?? '[]');
        $row['materials_json'] = self::parseJsonArray($row['materials_json'] ?? '[]');
        $row['tags_json'] = self::parseJsonArray($row['tags_json'] ?? '[]');
        return $row;
    }

    public static function saveDesignIdea(array $data, ?int $id = null): int
    {
        $name = trim((string)($data['name'] ?? ''));
        $type = trim((string)($data['type'] ?? ''));
        if ($name === '' || $type === '') {
            throw new InvalidArgumentException('Idea title and type are required.');
        }
        $existing = $id ? Database::one('SELECT * FROM design_ideas WHERE id=? LIMIT 1', [$id]) : null;
        if ($id && !$existing) {
            throw new InvalidArgumentException('Design idea was not found.');
        }
        // A published URL should remain stable after editorial title updates.
        $data['slug'] = $existing['slug'] ?? self::uniqueDesignIdeaSlug(slugify($name));
        if (!array_key_exists('gallery_json', $data) && $existing) {
            $data['gallery_json'] = $existing['gallery_json'] ?? [];
        }
        if (trim((string)($data['photo_source_name'] ?? '')) === '') {
            throw new InvalidArgumentException('Photo owner or source is required for every design idea.');
        }
        $data['image_alt'] = trim((string)($data['image_alt'] ?? '')) ?: trim($name . ' - ' . $type . (!empty($data['city']) ? ' in ' . $data['city'] : ''));
        $summary = trim((string)($data['short_description'] ?? $data['description'] ?? ''));
        $data['meta_title'] = trim((string)($data['meta_title'] ?? '')) ?: $name . ' | HomeInteriors360';
        $data['meta_description'] = trim((string)($data['meta_description'] ?? '')) ?: $summary;
        $data['meta_title'] = function_exists('mb_substr') ? mb_substr($data['meta_title'], 0, 160) : substr($data['meta_title'], 0, 160);
        $data['meta_description'] = function_exists('mb_substr') ? mb_substr($data['meta_description'], 0, 160) : substr($data['meta_description'], 0, 160);
        $fields = [
            'slug', 'name', 'location', 'city', 'state', 'type', 'color', 'style', 'layout',
            'length_ft', 'breadth_ft', 'height_ft', 'budget_min', 'budget_max',
            'short_description', 'description', 'image_url', 'image_alt', 'photo_source_name', 'photo_source_url', 'photo_license', 'recommended_designer_id', 'gallery_json', 'materials_json', 'tags_json',
            'meta_title', 'meta_description', 'is_featured', 'is_active',
        ];
        $values = [];
        foreach ($fields as $field) {
            if (in_array($field, ['gallery_json', 'materials_json', 'tags_json'], true)) {
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

    private static function uniqueDesignIdeaSlug(string $base): string
    {
        $base = $base !== '' ? $base : 'design-idea';
        $slug = $base;
        $suffix = 2;
        while (Database::one('SELECT id FROM design_ideas WHERE slug=? LIMIT 1', [$slug])) {
            $slug = $base . '-' . $suffix++;
        }
        return $slug;
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
        $userId = (int)($user['id'] ?? 0);
        $proId = (int)($user['pro_id'] ?? 0);
        $subscription = $userId > 0 && $proId > 0 ? Database::one(
            "SELECT * FROM designer_quotation_subscriptions
             WHERE (user_id=? OR pro_id=?) AND status IN ('created','authenticated','active','pending')
             ORDER BY COALESCE(current_end_at, created_at) DESC, id DESC LIMIT 1",
            [$userId, $proId]
        ) : null;
        if ($subscription && !empty($subscription['current_start_at']) && strtotime((string)$subscription['current_start_at']) <= time() && !empty($subscription['current_end_at']) && strtotime((string)$subscription['current_end_at']) >= time()) {
            $daysLeft = max(0, (int)ceil((strtotime((string)$subscription['current_end_at']) - time()) / 86400));
            return [
                'has_subscription' => true,
                'is_trial' => false,
                'trial_available' => false,
                'is_expired' => false,
                'can_create' => true,
                'show_warning' => $daysLeft <= 3,
                'days_left' => $daysLeft,
                'expires_at' => $subscription['current_end_at'],
                'can_cancel_mandate' => true,
                'message' => $daysLeft <= 3 ? $daysLeft . ' day' . ($daysLeft === 1 ? '' : 's') . ' left before your next automatic payment.' : 'Monthly subscription active.',
            ];
        }
        $registration = Database::one(
            'SELECT * FROM designer_feature_registrations
             WHERE payment_status="paid" AND (user_id=? OR pro_id=?)
             ORDER BY paid_at DESC, id DESC
             LIMIT 1',
            [(int)($user['id'] ?? 0), (int)($user['pro_id'] ?? 0)]
        );
        $legacySubscriptionExpired = false;
        if ($registration && !empty($registration['paid_at'])) {
            $expiresAt = date('Y-m-d H:i:s', strtotime((string)$registration['paid_at'] . ' +30 days'));
            $daysLeft = (int)ceil((strtotime($expiresAt) - time()) / 86400);
            if ($daysLeft >= 0) {
            return ['has_subscription' => true, 'is_trial' => false, 'trial_available' => false, 'is_expired' => false, 'can_create' => true, 'show_warning' => $daysLeft <= 3, 'days_left' => $daysLeft, 'expires_at' => $expiresAt, 'can_cancel_mandate' => false, 'message' => $daysLeft <= 3 ? $daysLeft . ' day' . ($daysLeft === 1 ? '' : 's') . ' left in your existing subscription.' : 'Subscription active.'];
            }
            $legacySubscriptionExpired = true;
        }

        $trial = $userId > 0 && $proId > 0 ? Database::one('SELECT * FROM designer_quotation_trials WHERE user_id=? OR pro_id=? ORDER BY id DESC LIMIT 1', [$userId, $proId]) : null;
        if (!$trial) {
            if ($legacySubscriptionExpired) {
                return ['has_subscription' => false, 'is_trial' => false, 'trial_available' => false, 'is_expired' => true, 'can_create' => false, 'show_warning' => false, 'days_left' => 0, 'expires_at' => null, 'can_cancel_mandate' => false, 'message' => 'Your previous subscription has ended. Set up a ₹999/month automatic payment to create new quotations.'];
            }
            return ['has_subscription' => false, 'is_trial' => false, 'trial_available' => true, 'is_expired' => false, 'can_create' => false, 'show_warning' => false, 'days_left' => 15, 'expires_at' => null, 'can_cancel_mandate' => false, 'message' => 'Authorise a ₹2 standing-instruction verification to start your 15-day free trial. ₹999/month starts only after the trial.'];
        }
        $daysLeft = (int)ceil((strtotime((string)$trial['ends_at']) - time()) / 86400);
        $expired = $daysLeft < 0;
        return [
            'has_subscription' => false,
            'is_trial' => !$expired,
            'trial_available' => false,
            'is_expired' => $expired,
            'can_create' => !$expired,
            'show_warning' => !$expired && $daysLeft <= 3,
            'days_left' => max($daysLeft, 0),
            'expires_at' => $trial['ends_at'],
            'can_cancel_mandate' => !empty($subscription),
            'message' => $expired ? 'Your 15-day free trial has ended. Set up a ₹999/month automatic payment to create new quotations.' : ($daysLeft <= 3 ? $daysLeft . ' day' . ($daysLeft === 1 ? '' : 's') . ' left in your 15-day free trial.' : 'Your 15-day free trial is active.'),
        ];
    }

    public static function startDesignerQuotationTrial(array $user): array
    {
        $userId = (int)($user['id'] ?? 0);
        $proId = (int)($user['pro_id'] ?? 0);
        if ($userId <= 0 || $proId <= 0) {
            throw new InvalidArgumentException('A designer profile is required to start the free trial.');
        }
        $status = self::designerSubscriptionStatus($user);
        if (!$status['trial_available']) {
            return $status;
        }
        try {
            Database::exec('INSERT INTO designer_quotation_trials (user_id, pro_id, starts_at, ends_at) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY))', [$userId, $proId]);
        } catch (PDOException $e) {
            if ((string)$e->getCode() !== '23000') {
                throw $e;
            }
        }
        return self::designerSubscriptionStatus($user);
    }

    public static function designerCanCreateQuotation(array $user): bool
    {
        return !empty(self::designerSubscriptionStatus($user)['can_create']);
    }

    public static function designerMembershipPlans(): array
    {
        $plans = Database::query('SELECT * FROM designer_membership_plans WHERE is_active=1 ORDER BY sort_order, id');
        foreach ($plans as &$plan) {
            $features = json_decode((string)($plan['features_json'] ?? '[]'), true);
            $plan['features'] = is_array($features) ? $features : [];
            unset($plan['features_json']);
        }
        return $plans;
    }

    private static function membershipRazorpayPlanId(string $code, string $cycle): string
    {
        return match ($code . ':' . $cycle) {
            'pro:monthly' => RAZORPAY_DESIGNER_PRO_MONTHLY_PLAN_ID,
            'pro:yearly' => RAZORPAY_DESIGNER_PRO_YEARLY_PLAN_ID,
            'featured:monthly' => RAZORPAY_DESIGNER_FEATURED_MONTHLY_PLAN_ID,
            'featured:yearly' => RAZORPAY_DESIGNER_FEATURED_YEARLY_PLAN_ID,
            default => '',
        };
    }

    public static function designerMembershipStatus(array $user): array
    {
        $membership = Database::one(
            "SELECT m.*, p.code, p.name, p.billing_cycle, p.lead_discount_percentage, p.bonus_lead_credits, p.featured_locality_slots
             FROM designer_memberships m JOIN designer_membership_plans p ON p.id=m.plan_id
             WHERE (m.user_id=? OR m.pro_id=?) ORDER BY m.created_at DESC, m.id DESC LIMIT 1",
            [(int)($user['id'] ?? 0), (int)($user['pro_id'] ?? 0)]
        );
        return $membership ?: ['code' => 'free', 'name' => 'Free Listing', 'status' => 'free', 'billing_cycle' => 'monthly'];
    }

    public static function createDesignerMembershipSubscription(array $user, array $data): array
    {
        $code = trim((string)($data['plan_code'] ?? ''));
        $cycle = trim((string)($data['billing_cycle'] ?? 'monthly'));
        $plan = Database::one('SELECT * FROM designer_membership_plans WHERE code=? AND billing_cycle=? AND is_active=1 LIMIT 1', [$code, $cycle]);
        if (!$plan || $code === 'free') {
            throw new InvalidArgumentException('Please select a paid membership plan.');
        }
        $razorpayPlanId = self::membershipRazorpayPlanId($code, $cycle);
        if ($razorpayPlanId === '') {
            throw new RuntimeException('This membership plan is not configured for payment yet. Please contact HomeInteriors360 support.');
        }
        $email = strtolower(trim((string)($data['invoice_email'] ?? $user['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Enter a valid invoice email.');
        }
        $phone = preg_replace('/\D+/', '', (string)($data['whatsapp_phone'] ?? '')) ?? '';
        if (strlen($phone) < 10) {
            throw new InvalidArgumentException('Enter a valid WhatsApp number.');
        }
        $subscription = razorpayCreateSubscription($razorpayPlanId, [
            'module' => 'designer_membership', 'user_id' => (string)$user['id'], 'pro_id' => (string)$user['pro_id'], 'plan_code' => $code,
        ]);
        if (empty($subscription['id'])) {
            throw new RuntimeException('Razorpay could not create the membership payment instruction.');
        }
        $pro = Database::one('SELECT full_name FROM pros WHERE id=?', [(int)$user['pro_id']]) ?: [];
        Database::exec(
            'INSERT INTO designer_memberships (user_id,pro_id,plan_id,status,razorpay_subscription_id,business_name,owner_name,whatsapp_phone,invoice_email,gstin,featured_locality,metadata_json) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
            [(int)$user['id'], (int)$user['pro_id'], (int)$plan['id'], (string)($subscription['status'] ?? 'created'), (string)$subscription['id'], trim((string)($data['business_name'] ?? $pro['full_name'] ?? '')), trim((string)($data['owner_name'] ?? '')), $phone, $email, trim((string)($data['gstin'] ?? '')), trim((string)($data['featured_locality'] ?? '')), json_encode(['consent' => !empty($data['renewal_consent'])], JSON_UNESCAPED_UNICODE)]
        );
        return ['subscription_id' => (string)$subscription['id'], 'key_id' => RAZORPAY_KEY_ID, 'name' => 'HomeInteriors360', 'description' => (string)$plan['name'] . ' membership'];
    }

    public static function activateDesignerMembershipSubscription(array $user, string $subscriptionId, string $paymentId, string $signature): void
    {
        if (!razorpaySubscriptionSignatureIsValid($subscriptionId, $paymentId, $signature)) {
            throw new InvalidArgumentException('Membership payment signature mismatch.');
        }
        $record = Database::one('SELECT * FROM designer_memberships WHERE razorpay_subscription_id=? AND (user_id=? OR pro_id=?)', [$subscriptionId, (int)$user['id'], (int)$user['pro_id']]);
        if (!$record) {
            throw new InvalidArgumentException('Membership payment instruction was not found.');
        }
        Database::exec('UPDATE designer_memberships SET status="active", razorpay_payment_id=?, razorpay_signature=?, starts_at=NOW(), ends_at=DATE_ADD(NOW(), INTERVAL 1 MONTH), updated_at=NOW() WHERE id=?', [$paymentId, $signature, (int)$record['id']]);
        Database::exec('UPDATE pros SET is_premium=1, listing_tier="PAID", updated_at=NOW() WHERE id=?', [(int)$user['pro_id']]);
    }

    public static function createDesignerRecurringSubscription(array $user, bool $withTrial = false): array
    {
        $userId = (int)($user['id'] ?? 0);
        $proId = (int)($user['pro_id'] ?? 0);
        if ($userId <= 0 || $proId <= 0) {
            throw new InvalidArgumentException('A designer account is required.');
        }
        $status = self::designerSubscriptionStatus($user);
        if (!empty($status['has_subscription']) && !$status['is_expired']) {
            throw new InvalidArgumentException('Your monthly subscription is already active.');
        }
        $startAt = $withTrial ? strtotime('+15 days') : null;
        $subscription = razorpayCreateSubscription(RAZORPAY_QUOTATION_PLAN_ID, [
            'module' => 'designer_quotation_builder',
            'user_id' => (string)$userId,
            'pro_id' => (string)$proId,
        ], $startAt, $withTrial ? 200 : null);
        if (empty($subscription['id'])) {
            throw new RuntimeException('Razorpay could not create the recurring payment instruction.');
        }
        Database::exec(
            'INSERT INTO designer_quotation_subscriptions (user_id, pro_id, razorpay_plan_id, razorpay_subscription_id, status, mandate_status, current_start_at, current_end_at) VALUES (?, ?, ?, ?, ?, "pending", ?, ?)',
            [$userId, $proId, RAZORPAY_QUOTATION_PLAN_ID, (string)$subscription['id'], (string)($subscription['status'] ?? 'created'), $startAt ? date('Y-m-d H:i:s', $startAt) : null, $startAt ? date('Y-m-d H:i:s', strtotime('+1 month', $startAt)) : null]
        );
        $professional = Database::one('SELECT full_name, email, phone FROM pros WHERE id=?', [$proId]) ?? [];
        self::recordAbandonedCheckout('proposal_generator', ['name' => $professional['full_name'] ?? ($user['username'] ?? ''), 'email' => $professional['email'] ?? ($user['email'] ?? ''), 'phone' => $professional['phone'] ?? ''], $withTrial ? 2 : 999, ['product' => 'BOQ / Proposal Generator', 'trial' => $withTrial], (string)$subscription['id'], null, $userId, $proId);
        return ['subscription_id' => (string)$subscription['id'], 'key_id' => RAZORPAY_KEY_ID, 'name' => 'HomeInteriors360', 'description' => $withTrial ? '₹2 authorisation today, 15 days free, then ₹999/month' : 'Quotation Builder monthly membership'];
    }

    public static function activateDesignerRecurringSubscription(array $user, string $subscriptionId, string $paymentId, string $signature): void
    {
        if (!razorpaySubscriptionSignatureIsValid($subscriptionId, $paymentId, $signature)) {
            throw new InvalidArgumentException('Recurring payment signature mismatch.');
        }
        $record = Database::one('SELECT * FROM designer_quotation_subscriptions WHERE razorpay_subscription_id=? AND (user_id=? OR pro_id=?)', [$subscriptionId, (int)$user['id'], (int)$user['pro_id']]);
        if (!$record) {
            throw new InvalidArgumentException('Recurring payment instruction was not found.');
        }
        Database::exec('UPDATE designer_quotation_subscriptions SET razorpay_payment_id=?, razorpay_signature=?, status="active", mandate_status="authorised", current_start_at=COALESCE(current_start_at, NOW()), current_end_at=COALESCE(current_end_at, DATE_ADD(NOW(), INTERVAL 1 MONTH)), updated_at=NOW() WHERE id=?', [$paymentId, $signature, (int)$record['id']]);
        self::completeAbandonedCheckout($subscriptionId);
        self::startDesignerQuotationTrial($user);
    }

    public static function updateDesignerRecurringSubscriptionFromWebhook(array $payload): void
    {
        $entity = is_array($payload['payload']['subscription']['entity'] ?? null) ? $payload['payload']['subscription']['entity'] : [];
        $paymentEntity = is_array($payload['payload']['payment']['entity'] ?? null) ? $payload['payload']['payment']['entity'] : [];
        $subscriptionId = (string)($entity['id'] ?? $paymentEntity['subscription_id'] ?? '');
        if ($subscriptionId === '') {
            return;
        }
        $event = strtolower((string)($payload['event'] ?? ''));
        $status = strtolower((string)($entity['status'] ?? ''));
        $paymentFailed = $event === 'payment.failed' || in_array($status, ['halted', 'cancelled', 'completed', 'expired'], true);
        if ($paymentFailed) {
            $status = 'cancelled';
        }
        $startAt = !empty($entity['current_start']) ? date('Y-m-d H:i:s', (int)$entity['current_start']) : null;
        $endAt = !empty($entity['current_end']) ? date('Y-m-d H:i:s', (int)$entity['current_end']) : null;
        $cancelledAt = !empty($entity['ended_at']) ? date('Y-m-d H:i:s', (int)$entity['ended_at']) : null;
        Database::exec('UPDATE designer_quotation_subscriptions SET status=?, mandate_status=?, current_start_at=COALESCE(?, current_start_at), current_end_at=COALESCE(?, current_end_at), cancelled_at=COALESCE(?, cancelled_at), updated_at=NOW() WHERE razorpay_subscription_id=?', [$status, $paymentFailed ? 'payment_failed' : (in_array($status, ['active', 'authenticated'], true) ? 'authorised' : $status), $startAt, $endAt, $paymentFailed ? date('Y-m-d H:i:s') : $cancelledAt, $subscriptionId]);
        if ($paymentFailed) {
            try {
                razorpayCancelSubscription($subscriptionId);
            } catch (Throwable) {
                // The provider may already have ended the subscription.
            }
        }
    }

    public static function reconcileDesignerRecurringSubscriptions(): array
    {
        $records = Database::query(
            "SELECT * FROM designer_quotation_subscriptions
             WHERE status IN ('created','authenticated','active','pending')
               AND ((current_start_at IS NOT NULL AND DATE(current_start_at) < CURDATE())
                 OR (current_end_at IS NOT NULL AND DATE(current_end_at) < CURDATE()))"
        );
        $result = ['checked' => 0, 'cancelled' => 0, 'active' => 0, 'errors' => []];
        foreach ($records as $record) {
            $result['checked']++;
            try {
                $provider = razorpayFetchSubscription((string)$record['razorpay_subscription_id']);
                $status = strtolower((string)($provider['status'] ?? ''));
                $startAt = !empty($provider['current_start']) ? date('Y-m-d H:i:s', (int)$provider['current_start']) : null;
                $endAt = !empty($provider['current_end']) ? date('Y-m-d H:i:s', (int)$provider['current_end']) : null;
                $paymentSucceeded = $status === 'active' && ($endAt === null || strtotime($endAt) >= strtotime('today'));
                if ($paymentSucceeded) {
                    Database::exec('UPDATE designer_quotation_subscriptions SET status="active", mandate_status="authorised", current_start_at=COALESCE(?, current_start_at), current_end_at=COALESCE(?, current_end_at), updated_at=NOW() WHERE id=?', [$startAt, $endAt, (int)$record['id']]);
                    $result['active']++;
                    continue;
                }
                try {
                    razorpayCancelSubscription((string)$record['razorpay_subscription_id']);
                } catch (Throwable) {
                    // A failed, halted, or already-cancelled provider subscription is still locked locally.
                }
                Database::exec('UPDATE designer_quotation_subscriptions SET status="cancelled", mandate_status="payment_failed", cancelled_at=NOW(), updated_at=NOW() WHERE id=?', [(int)$record['id']]);
                $result['cancelled']++;
            } catch (Throwable $exception) {
                $result['errors'][] = 'Subscription ' . (string)$record['razorpay_subscription_id'] . ': ' . $exception->getMessage();
            }
        }
        return $result;
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
            unset($item['vendor_cost'], $item['margin_amount'], $item['markup_percentage'], $item['is_manual_override'], $item['notes']);
        }
        unset($item);
        return $quote;
    }

    public static function respondToPublicProposal(string $token, string $response, string $note = ''): ?array
    {
        $quote = self::getQuotationByToken($token);
        if (!$quote) {
            return null;
        }
        if (!empty($quote['valid_until']) && strtotime((string)$quote['valid_until']) < strtotime(date('Y-m-d'))) {
            throw new InvalidArgumentException('This proposal has expired. Please request an updated proposal.');
        }
        $status = $response === 'accept' ? 'accepted' : 'revision_requested';
        self::updateQuotationStatus((int)$quote['id'], $status, null, $note ?: ($status === 'accepted' ? 'Accepted by client' : 'Revision requested by client'));
        self::recordQuotationEvent((int)$quote['id'], $status, ['note' => substr($note, 0, 1000)]);
        return self::publicProposalData($token, false);
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
        foreach (['client_name', 'city', 'property_type', 'package_id'] as $field) {
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
            'lead_id', 'quote_date', 'client_name', 'client_phone', 'client_email', 'city', 'locality', 'society_name', 'site_address',
            'property_type', 'bhk', 'carpet_area', 'builtup_area', 'possession_status', 'expected_start_date',
            'expected_handover_date', 'project_stage', 'budget_range', 'design_style', 'scope_type', 'quality_tier', 'package_id', 'template_id',
            'designer_id', 'assigned_to_user_id', 'subtotal', 'design_fee', 'project_management_fee',
            'site_visit_fee', 'discount_amount', 'discount_percentage', 'gst_percentage', 'gst_amount',
            'final_amount', 'vendor_cost', 'margin_amount', 'margin_percentage', 'platform_commission',
            'payment_schedule_json', 'status', 'valid_until', 'internal_notes', 'client_notes', 'exclusions_text', 'terms_html', 'studio_name', 'studio_logo_url', 'studio_accent_color', 'updated_by',
        ];
        $values = [
            !empty($data['lead_id']) ? (int)$data['lead_id'] : null,
            ($data['quote_date'] ?? '') ?: date('Y-m-d'),
            trim((string)$data['client_name']),
            trim((string)($data['client_phone'] ?? '')) ?: null,
            trim((string)($data['client_email'] ?? '')) ?: null,
            trim((string)$data['city']),
            trim((string)($data['locality'] ?? '')) ?: null,
            trim((string)($data['society_name'] ?? '')) ?: null,
            trim((string)($data['site_address'] ?? '')) ?: null,
            trim((string)$data['property_type']),
            trim((string)($data['bhk'] ?? '')) ?: null,
            ($data['carpet_area'] ?? '') !== '' ? (float)$data['carpet_area'] : null,
            ($data['builtup_area'] ?? '') !== '' ? (float)$data['builtup_area'] : null,
            trim((string)($data['possession_status'] ?? '')) ?: null,
            ($data['expected_start_date'] ?? '') ?: null,
            ($data['expected_handover_date'] ?? '') ?: null,
            trim((string)($data['project_stage'] ?? '')) ?: null,
            trim((string)($data['budget_range'] ?? '')) ?: null,
            trim((string)($data['design_style'] ?? '')) ?: null,
            trim((string)($data['scope_type'] ?? '')) ?: null,
            trim((string)($data['quality_tier'] ?? '')) ?: null,
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
            trim((string)($data['exclusions_text'] ?? '')) ?: null,
            trim((string)($data['terms_html'] ?? $settings['boq_default_terms'] ?? '')) ?: null,
            trim((string)($data['studio_name'] ?? '')) ?: null,
            trim((string)($data['studio_logo_url'] ?? '')) ?: null,
            trim((string)($data['studio_accent_color'] ?? '')) ?: null,
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
        self::recordAbandonedCheckout('proposal_generator', ['name' => $name, 'email' => (string)($data['email'] ?? ''), 'phone' => $phone], 999, ['company_name' => $data['company_name'] ?? '', 'city' => $data['city'] ?? '', 'product' => 'BOQ / Proposal Generator'], (string)$order['id']);
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

        $order = razorpayCreateOrder(99900, 'designer_' . substr($phone, -10) . '_' . time(), [
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
            'amount' => (int)($order['amount'] ?? 99900),
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
        self::completeAbandonedCheckout($orderId);
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
            'INSERT INTO quotation_items (quotation_id, room_name, category, item_name, description, specification, material, finish, brand, image_url, unit_type, quantity, length, width, height, calculated_area, rate, amount, discount_amount, gst_percentage, gst_amount, vendor_cost, markup_percentage, margin_amount, include_in_proposal, is_manual_override, sort_order, room_sort_order, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $quoteId,
                trim((string)($item['room_name'] ?? 'Room')) ?: 'Room',
                trim((string)($item['category'] ?? 'Other')) ?: 'Other',
                trim((string)($item['item_name'] ?? 'Interior item')) ?: 'Interior item',
                trim((string)($item['description'] ?? '')) ?: null,
                trim((string)($item['specification'] ?? '')) ?: null,
                trim((string)($item['material'] ?? '')) ?: null,
                trim((string)($item['finish'] ?? '')) ?: null,
                trim((string)($item['brand'] ?? '')) ?: null,
                trim((string)($item['image_url'] ?? '')) ?: null,
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
                max(0.0, (float)($item['markup_percentage'] ?? 0)),
                (float)($item['margin_amount'] ?? 0),
                !empty($item['include_in_proposal']) || !array_key_exists('include_in_proposal', $item) ? 1 : 0,
                !empty($item['is_manual_override']) ? 1 : 0,
                $sortOrder,
                max(0, (int)($item['room_sort_order'] ?? 0)),
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
            'quote_number', 'revision_number', 'parent_quote_id', 'lead_id', 'quote_date', 'client_name', 'client_phone', 'client_email', 'city', 'locality', 'society_name', 'site_address',
            'property_type', 'bhk', 'carpet_area', 'builtup_area', 'possession_status', 'expected_start_date', 'expected_handover_date', 'project_stage',
            'budget_range', 'design_style', 'scope_type', 'quality_tier', 'package_id', 'template_id', 'designer_id', 'assigned_to_user_id', 'subtotal',
            'design_fee', 'project_management_fee', 'site_visit_fee', 'discount_amount', 'discount_percentage', 'gst_percentage',
            'gst_amount', 'final_amount', 'vendor_cost', 'margin_amount', 'margin_percentage', 'platform_commission',
            'payment_schedule_json', 'status', 'proposal_token', 'valid_until', 'internal_notes', 'client_notes', 'exclusions_text', 'terms_html', 'studio_name', 'studio_logo_url', 'studio_accent_color', 'created_by', 'updated_by',
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

    public static function recordQuotationEvent(int $quoteId, string $eventName, array $eventData = []): void
    {
        Database::exec('INSERT INTO quotation_events (quotation_id, event_name, event_data_json, ip_address) VALUES (?, ?, ?, ?)', [
            $quoteId,
            substr($eventName, 0, 80),
            json_encode($eventData, JSON_UNESCAPED_UNICODE),
            substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 80),
        ]);
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
