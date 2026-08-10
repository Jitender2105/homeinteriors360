<?php

declare(strict_types=1);

$bootstrapSameDir = __DIR__ . '/src/bootstrap.php';
$bootstrapParentDir = __DIR__ . '/../src/bootstrap.php';
if (is_file($bootstrapSameDir)) {
    require $bootstrapSameDir;
} elseif (is_file($bootstrapParentDir)) {
    require $bootstrapParentDir;
} else {
    http_response_code(500);
    echo 'Bootstrap file not found';
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST' && !empty($_POST['_method'])) {
    $method = strtoupper((string)$_POST['_method']);
}
$content = SiteRepository::allContent();
$seoCityPages = [
    'delhi-ncr' => ['city' => 'Delhi NCR', 'nearby' => 'Delhi, Gurugram, Noida, Faridabad, and Ghaziabad'],
    'gurgaon' => ['city' => 'Gurugram', 'nearby' => 'Golf Course Road, Sohna Road, Dwarka Expressway, and New Gurgaon'],
    'noida' => ['city' => 'Noida', 'nearby' => 'Noida, Greater Noida, and Noida Extension'],
    'mumbai' => ['city' => 'Mumbai', 'nearby' => 'Mumbai, Navi Mumbai, and Thane'],
    'pune' => ['city' => 'Pune', 'nearby' => 'Kharadi, Wakad, Baner, Hinjewadi, and nearby Pune markets'],
    'hyderabad' => ['city' => 'Hyderabad', 'nearby' => 'Gachibowli, Hitech City, Kondapur, and Attapur'],
    'bangalore' => ['city' => 'Bangalore', 'nearby' => 'Whitefield, Sarjapur Road, Electronic City, and North Bangalore'],
];
$seoLeadPages = [
    '/interior-design-leads' => [
        'keyword' => 'interior design leads',
        'title' => 'Interior Design Leads for Architects & Interior Designers',
        'subtitle' => 'Buy verified interior design leads from homeowners searching for modular kitchens, full home interiors, renovation, wardrobes, and premium residential design services.',
        'meta' => 'Buy verified interior design leads for architects, interior designers, studios, and contractors. Filter by city, locality, budget, work type, and date range.',
    ],
    '/interior-designer-leads' => [
        'keyword' => 'interior designer leads',
        'title' => 'Interior Designer Leads for Growing Design Studios',
        'subtitle' => 'Get homeowner enquiries matched by city, budget, locality, and work type so your sales team can focus on serious interior design prospects.',
        'meta' => 'Interior designer leads for design studios and firms. Buy filtered homeowner leads with transparent pricing and secure Razorpay checkout.',
    ],
    '/buy-interior-design-leads' => [
        'keyword' => 'buy interior design leads',
        'title' => 'Buy Interior Design Leads Online',
        'subtitle' => 'Choose active lead packages, add filtered opportunities to cart, and access purchased leads from your buyer dashboard after payment verification.',
        'meta' => 'Buy interior design leads online from HomeInteriors360. City-wise and work-type lead packages with first-time free lead offer and slab pricing.',
    ],
    '/interior-leads-provider-india' => [
        'keyword' => 'interior leads provider in India',
        'title' => 'Interior Leads Provider in India',
        'subtitle' => 'HomeInteriors360 connects interior designers, architects, and contractors with verified residential interior leads across India-focused service markets.',
        'meta' => 'Interior leads provider in India for architects, interior designers, and contractors. Buy verified residential interior leads with transparent pricing.',
    ],
    '/interior-design-lead-generation' => [
        'keyword' => 'interior design lead generation',
        'title' => 'Interior Design Lead Generation for Professionals',
        'subtitle' => 'A lead generation marketplace built for interior brands that need qualified homeowner enquiries, clean filtering, and sales-ready lead context.',
        'meta' => 'Interior design lead generation for architects, interior designers, contractors, and studios. Verified homeowner leads with city and budget filters.',
    ],
];
foreach ($seoCityPages as $slug => $cityData) {
    $seoLeadPages['/interior-designer-leads-' . $slug] = [
        'keyword' => 'interior designer leads ' . $cityData['city'],
        'title' => 'Interior Designer Leads in ' . $cityData['city'],
        'subtitle' => 'Buy location-specific interior designer leads from homeowners in ' . $cityData['nearby'] . '. Filter by budget, work type, locality, and recency.',
        'meta' => 'Buy verified interior designer leads in ' . $cityData['city'] . '. Homeowner enquiries for full home interiors, kitchens, wardrobes, and renovation projects.',
        'city' => $cityData['city'],
    ];
    $seoLeadPages['/interior-design-lead-generation-' . $slug] = [
        'keyword' => 'interior design lead generation ' . $cityData['city'],
        'title' => 'Interior Design Lead Generation in ' . $cityData['city'],
        'subtitle' => 'Grow your interior business in ' . $cityData['city'] . ' with verified homeowner enquiries, digital lead delivery, and transparent lead package pricing.',
        'meta' => 'Interior design lead generation in ' . $cityData['city'] . ' for designers, architects, and contractors. Buy filtered residential interior leads.',
        'city' => $cityData['city'],
    ];
}
$seoCityLinks = array_map(
    static fn(string $slug, array $data): array => [
        'href' => '/interior-designer-leads-' . $slug,
        'label' => 'Interior Designer Leads in ' . $data['city'],
    ],
    array_keys($seoCityPages),
    $seoCityPages
);

try {
    if ($path === '/robots.txt') {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /admin\n";
        echo "Disallow: /api/\n";
        echo "Disallow: /lead-cart\n";
        echo "Disallow: /lead-checkout\n";
        echo "Disallow: /lead-dashboard\n";
        echo "Disallow: /lead-download/\n";
        echo "Sitemap: " . absoluteUrl('/sitemap.xml') . "\n";
        exit;
    }

    if ($path === '/sitemap.xml') {
        $today = date('Y-m-d');
        $urls = [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => '/home-interior-hire-a-designer', 'priority' => '0.96', 'changefreq' => 'weekly'],
            ['loc' => '/lead-marketplace', 'priority' => '0.95', 'changefreq' => 'daily'],
            ['loc' => '/pricing-details', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => '/pricing', 'priority' => '0.75', 'changefreq' => 'weekly'],
            ['loc' => '/interior-designer-registration', 'priority' => '0.88', 'changefreq' => 'weekly'],
            ['loc' => '/professionals', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => '/ai-room-visualizer', 'priority' => '0.86', 'changefreq' => 'weekly'],
            ['loc' => '/cost-calculator', 'priority' => '0.72', 'changefreq' => 'monthly'],
            ['loc' => '/contact-us', 'priority' => '0.45', 'changefreq' => 'yearly'],
            ['loc' => '/privacy-policy', 'priority' => '0.35', 'changefreq' => 'yearly'],
            ['loc' => '/terms-and-conditions', 'priority' => '0.35', 'changefreq' => 'yearly'],
            ['loc' => '/shipping-policy', 'priority' => '0.35', 'changefreq' => 'yearly'],
            ['loc' => '/cancellation-and-refunds-policy', 'priority' => '0.35', 'changefreq' => 'yearly'],
        ];
        foreach (array_keys($seoLeadPages) as $seoPath) {
            $urls[] = ['loc' => $seoPath, 'priority' => '0.92', 'changefreq' => 'weekly'];
        }
        $directoryAliasPaths = [
            'interior-designer',
            'architect',
            'architect-interior-designer',
            'full-home-interior-designer',
            'kitchen-interior-designer',
            'wardrobe-interior-designer',
            'renovation-interior-designer',
        ];
        foreach (array_keys($seoCityPages) as $citySlug) {
            foreach ([
                'interior-designer',
                'architect',
                'architect-interior-designer',
                'full-home-interior-designer',
                'kitchen-interior-designer',
                'wardrobe-interior-designer',
                'renovation-interior-designer',
            ] as $baseAlias) {
                $directoryAliasPaths[] = $baseAlias . '-in-' . $citySlug;
            }
        }
        foreach (array_unique($directoryAliasPaths) as $aliasPath) {
            $urls[] = ['loc' => '/professionals/' . $aliasPath, 'priority' => '0.65', 'changefreq' => 'weekly'];
        }
        foreach (Database::query('SELECT slug, updated_at FROM pros WHERE is_active = 1 ORDER BY updated_at DESC LIMIT 200') as $pro) {
            $urls[] = ['loc' => '/professionals/' . $pro['slug'], 'priority' => '0.55', 'changefreq' => 'weekly', 'lastmod' => substr((string)($pro['updated_at'] ?? $today), 0, 10)];
        }
        foreach (Database::query("SELECT slug, updated_at FROM projects WHERE COALESCE(moderation_status, 'APPROVED')='APPROVED' ORDER BY updated_at DESC LIMIT 200") as $project) {
            $urls[] = ['loc' => '/portfolio/' . $project['slug'], 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => substr((string)($project['updated_at'] ?? $today), 0, 10)];
        }
        $urls[] = ['loc' => '/design-ideas', 'priority' => '0.9', 'changefreq' => 'weekly'];
        foreach (Database::query('SELECT slug, updated_at FROM design_idea_aliases WHERE is_active=1 ORDER BY updated_at DESC LIMIT 200') as $alias) {
            $urls[] = ['loc' => '/design-ideas/' . $alias['slug'], 'priority' => '0.86', 'changefreq' => 'weekly', 'lastmod' => substr((string)($alias['updated_at'] ?? $today), 0, 10)];
        }
        foreach (Database::query('SELECT slug, updated_at FROM design_ideas WHERE is_active=1 ORDER BY updated_at DESC LIMIT 500') as $idea) {
            $urls[] = ['loc' => '/design-ideas/idea/' . $idea['slug'], 'priority' => '0.72', 'changefreq' => 'monthly', 'lastmod' => substr((string)($idea['updated_at'] ?? $today), 0, 10)];
        }
        try {
            foreach (Database::query("SELECT path, updated_at, page_type FROM url_aliases WHERE is_active=1 AND path NOT LIKE '/admin%' AND path NOT LIKE '/api%' AND path <> '/properties' AND path NOT LIKE '/property/%' AND COALESCE(robots, '') NOT LIKE '%noindex%' ORDER BY updated_at DESC LIMIT 2000") as $aliasUrl) {
                $priority = str_contains((string)$aliasUrl['page_type'], 'detail') ? '0.62' : '0.82';
                $urls[] = ['loc' => $aliasUrl['path'], 'priority' => $priority, 'changefreq' => 'weekly', 'lastmod' => substr((string)($aliasUrl['updated_at'] ?? $today), 0, 10)];
            }
        } catch (Throwable) {
            // The global alias table may not exist before the migration has run.
        }
        $dedupedUrls = [];
        foreach ($urls as $url) {
            $dedupedUrls[(string)$url['loc']] = $url;
        }
        $urls = array_values($dedupedUrls);

        header('Content-Type: application/xml; charset=utf-8');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($urls as $url) {
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars(absoluteUrl((string)$url['loc']), ENT_XML1, 'UTF-8') . "</loc>\n";
            echo "    <lastmod>" . htmlspecialchars((string)($url['lastmod'] ?? $today), ENT_XML1, 'UTF-8') . "</lastmod>\n";
            echo "    <changefreq>" . htmlspecialchars((string)$url['changefreq'], ENT_XML1, 'UTF-8') . "</changefreq>\n";
            echo "    <priority>" . htmlspecialchars((string)$url['priority'], ENT_XML1, 'UTF-8') . "</priority>\n";
            echo "  </url>\n";
        }
        echo "</urlset>\n";
        exit;
    }

    // Auth APIs
    if ($path === '/api/auth/login' && $method === 'POST') {
        $body = requestJson();
        if (empty($body['username']) || empty($body['password'])) {
            jsonResponse(['error' => 'Username and password are required'], 400);
        }

        $user = Auth::login((string)$body['username'], (string)$body['password']);
        if (!$user) {
            jsonResponse(['error' => 'Invalid credentials'], 401);
        }
        if (($user['role'] ?? '') === 'designer') {
            $designerBuyer = SiteRepository::designerBuyerForUser($user);
            if ($designerBuyer) {
                setBuyerSession($designerBuyer);
            }
        }
        jsonResponse(['success' => true, 'user' => $user]);
    }

    if ($path === '/api/auth/logout' && ($method === 'POST' || $method === 'GET')) {
        Auth::logout();
        if ($method === 'GET') {
            redirectTo('/admin/login');
        }
        jsonResponse(['success' => true]);
    }

    if ($path === '/api/auth/me' && $method === 'GET') {
        $user = Auth::user();
        if (!$user) {
            jsonResponse(['error' => 'Unauthorized'], 401);
        }
        jsonResponse(['user' => $user]);
    }

    // Public APIs
    if ($path === '/api/homepage' && $method === 'GET') {
        jsonResponse([
            'content' => SiteRepository::allContent(),
            'payload' => SiteRepository::homepagePayload(),
        ]);
    }

    if ($path === '/api/pros' && $method === 'GET') {
        $filters = [
            'role' => $_GET['role'] ?? null,
            'city' => $_GET['city'] ?? null,
            'work_type' => $_GET['work_type'] ?? null,
            'work_area' => $_GET['work_area'] ?? null,
            'budget_min' => $_GET['budget_min'] ?? null,
            'budget_max' => $_GET['budget_max'] ?? null,
            'experience_min' => $_GET['experience_min'] ?? null,
            'projects_min' => $_GET['projects_min'] ?? null,
            'rating_min' => $_GET['rating_min'] ?? null,
            'sort_by' => $_GET['sort_by'] ?? null,
        ];
        jsonResponse(['pros' => SiteRepository::listPros($filters)]);
    }

    if (preg_match('#^/api/pros/([a-z0-9-]+)$#i', $path, $match) && $method === 'GET') {
        $pro = SiteRepository::getProBySlug((string)$match[1]);
        if (!$pro) {
            jsonResponse(['error' => 'Professional not found'], 404);
        }
        $profile = SiteRepository::proProfileData((int)$pro['id']);
        jsonResponse(['pro' => $pro, 'projects' => $profile['projects'], 'reviews' => $profile['reviews']]);
    }

    if (preg_match('#^/api/portfolio/([a-z0-9-]+)$#i', $path, $match) && $method === 'GET') {
        $project = SiteRepository::getProjectBySlug((string)$match[1]);
        if (!$project) {
            jsonResponse(['error' => 'Portfolio project not found'], 404);
        }
        $related = SiteRepository::listOtherProjectsByPro((int)$project['pro_id'], (string)$project['slug']);
        jsonResponse(['project' => $project, 'related_projects' => $related]);
    }

    if ($path === '/api/site-content' && $method === 'GET') {
        $keyPrefix = trim((string)($_GET['prefix'] ?? ''));
        $all = SiteRepository::allContent();
        if ($keyPrefix === '') {
            jsonResponse(['content' => $all]);
        }
        $filtered = [];
        foreach ($all as $k => $v) {
            if (str_starts_with($k, $keyPrefix)) {
                $filtered[$k] = $v;
            }
        }
        jsonResponse(['content' => $filtered]);
    }

    if ($path === '/api/societies' && $method === 'GET') {
        jsonResponse([
            'societies' => SiteRepository::societyOptions(
                (string)($_GET['q'] ?? ''),
                isset($_GET['city']) ? (string)$_GET['city'] : null
            ),
        ]);
    }

    if ($path === '/api/property-projects' && $method === 'GET') {
        $filters = [
            'listing_for' => $_GET['listing_for'] ?? null,
            'city' => $_GET['city'] ?? null,
            'locality' => $_GET['locality'] ?? null,
            'property_type' => $_GET['property_type'] ?? null,
            'project_status' => $_GET['project_status'] ?? null,
            'bhk_type' => $_GET['bhk_type'] ?? null,
            'price_min' => $_GET['price_min'] ?? null,
            'price_max' => $_GET['price_max'] ?? null,
            'q' => $_GET['q'] ?? null,
            'sort' => $_GET['sort'] ?? null,
        ];
        jsonResponse(['projects' => SiteRepository::listRealEstateProjects($filters)]);
    }

    if (preg_match('#^/api/property-projects/([a-z0-9-]+)$#i', $path, $match) && $method === 'GET') {
        $project = SiteRepository::getRealEstateProject((string)$match[1]);
        if (!$project) {
            jsonResponse(['error' => 'Property project not found'], 404);
        }
        jsonResponse(['project' => $project]);
    }

    if ($path === '/api/design-ideas' && $method === 'GET') {
        $filters = array_filter([
            'q' => $_GET['q'] ?? null,
            'type' => $_GET['type'] ?? null,
            'color' => $_GET['color'] ?? null,
            'city' => $_GET['city'] ?? null,
            'state' => $_GET['state'] ?? null,
            'style' => $_GET['style'] ?? null,
            'layout' => $_GET['layout'] ?? null,
        ], static fn(mixed $value): bool => $value !== null && $value !== '');
        jsonResponse(['ideas' => SiteRepository::listDesignIdeas($filters)]);
    }

    if (preg_match('#^/api/design-ideas/([a-z0-9-]+)$#i', $path, $match) && $method === 'GET') {
        $idea = SiteRepository::getDesignIdea((string)$match[1]);
        if (!$idea) {
            jsonResponse(['error' => 'Design idea not found'], 404);
        }
        jsonResponse(['idea' => $idea]);
    }

    if ($path === '/api/design-idea-leads' && $method === 'POST') {
        try {
            $id = SiteRepository::createDesignIdeaLead(requestJson());
            jsonResponse(['success' => true, 'id' => $id]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/interior-designer-registration' && $method === 'POST') {
        try {
            $body = requestData();
            $body['profile_pic'] = saveUploadedFile($_FILES['profile_pic'] ?? [], 'professionals', null);
            $body['cover_photo'] = saveUploadedFile($_FILES['cover_photo'] ?? [], 'professionals', null);
            $result = SiteRepository::registerInteriorDesigner($body);
            if (!empty($result['user'])) {
                Auth::start();
                session_regenerate_id(true);
                $_SESSION['auth_user'] = $result['user'];
            }
            if (!empty($result['buyer'])) {
                setBuyerSession($result['buyer']);
            }
            jsonResponse([
                'success' => true,
                'pro_id' => $result['pro_id'],
                'redirect_url' => '/designer/portfolio-onboarding',
            ]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/designer/portfolios' && $method === 'POST') {
        $user = Auth::requireDesigner();
        try {
            $body = requestData();
            $body['media_json'] = saveUploadedFiles($_FILES['media_files'] ?? [], 'portfolio', []);
            $id = SiteRepository::createDesignerPortfolio($user, $body);
            jsonResponse(['success' => true, 'id' => $id]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/designer-feature-registrations' && $method === 'POST') {
        try {
            $order = SiteRepository::createDesignerSubscriptionOrder(requestJson());
            jsonResponse(['success' => true] + $order);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            jsonResponse(['error' => $e->getMessage()], $e->getCode() === 401 ? 401 : 500);
        }
    }

    if ($path === '/api/designer-feature-registrations/verify' && $method === 'POST') {
        $body = requestJson();
        $orderId = trim((string)($body['razorpay_order_id'] ?? ''));
        $paymentId = trim((string)($body['razorpay_payment_id'] ?? ''));
        $signature = trim((string)($body['razorpay_signature'] ?? ''));
        if ($orderId === '' || $paymentId === '' || $signature === '') {
            jsonResponse(['error' => 'Missing Razorpay payment fields'], 400);
        }
        try {
            $user = SiteRepository::activateDesignerSubscription($orderId, $paymentId, $signature);
            if (!$user) {
                jsonResponse(['error' => 'Designer account could not be activated'], 500);
            }
            Auth::start();
            session_regenerate_id(true);
            $_SESSION['auth_user'] = $user;
            jsonResponse(['success' => true, 'redirect_url' => '/designer', 'username' => $user['username'] ?? '']);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/mobile/professional/register' && $method === 'POST') {
        try {
            jsonResponse(['success' => true] + SiteRepository::requestMobileProfessionalOtp(requestJson()));
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/mobile/professional/verify-otp' && $method === 'POST') {
        try {
            $user = SiteRepository::verifyMobileProfessionalOtp(requestJson());
            if (!$user) {
                jsonResponse(['error' => 'Professional account could not be verified.'], 500);
            }
            Auth::start();
            session_regenerate_id(true);
            $_SESSION['auth_user'] = $user;
            jsonResponse([
                'success' => true,
                'redirect_url' => '/',
                'user' => $user,
            ]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/ai-room-visualizer/styles' && $method === 'GET') {
        jsonResponse(['styles' => SiteRepository::aiVisualizerStyles()]);
    }

    if ($path === '/api/ai-room-visualizer/render' && $method === 'POST') {
        $body = requestData();
        $original = saveUploadedFile($_FILES['room_photo'] ?? [], 'ai-room-visualizer', null);
        if (!$original) {
            jsonResponse(['error' => 'Please upload a clear interior room photo.'], 400);
        }
        try {
            $render = SiteRepository::createAiVisualizerRender($body, $original);
            jsonResponse(['success' => true, 'render' => $render]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/property-enquiries' && $method === 'POST') {
        try {
            $id = SiteRepository::createPropertyEnquiry(requestJson());
            jsonResponse(['success' => true, 'id' => $id]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/project-requirements' && $method === 'POST') {
        try {
            $body = requestData();
            $files = saveProjectRequirementFiles($_FILES['requirement_files'] ?? []);
            $fileType = (string)($body['requirement_file_type'] ?? 'other');
            foreach ($files as &$file) {
                $file['file_type'] = $fileType;
            }
            unset($file);
            $result = SiteRepository::createProjectRequirement($body, $files);
            jsonResponse([
                'success' => true,
                'requirement_id' => $result['id'],
                'lead_id' => $result['lead_id'],
                'lead_quality' => $result['lead_quality'],
                'otp_required' => true,
                'otp_request_id' => $result['otp_request_id'],
                'message' => 'Project requirement captured. Mobile verification can be completed once OTP delivery is connected.',
            ]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    if (preg_match('#^/api/project-requirements/(\d+)$#', $path, $match) && ($method === 'POST' || $method === 'PUT')) {
        try {
            $body = requestData();
            $files = saveProjectRequirementFiles($_FILES['requirement_files'] ?? []);
            $fileType = (string)($body['requirement_file_type'] ?? 'other');
            foreach ($files as &$file) {
                $file['file_type'] = $fileType;
            }
            unset($file);
            $result = SiteRepository::updateProjectRequirement((int)$match[1], $body, $files);
            jsonResponse([
                'success' => true,
                'requirement_id' => $result['id'],
                'lead_id' => $result['lead_id'],
                'lead_quality' => $result['lead_quality'],
                'message' => 'Thank you. Your complete project brief has been captured.',
            ]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        } catch (Throwable $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    if (preg_match('#^/api/project-requirements/(\d+)/verify-otp$#', $path, $match) && $method === 'POST') {
        try {
            $body = requestJson();
            SiteRepository::verifyProjectRequirementOtp((int)$match[1], (string)($body['otp'] ?? ''));
            jsonResponse(['success' => true]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/leads' && $method === 'POST') {
        $body = requestData();
        if (empty($body['name']) || empty($body['phone']) || empty($body['city']) || empty($body['requirement'])) {
            jsonResponse(['error' => 'Name, phone, city and requirement are required'], 400);
        }
        if (empty($body['lead_consent'])) {
            jsonResponse(['error' => 'Consent is required before submitting the enquiry.'], 400);
        }
        $leadId = SiteRepository::createLead([
            'name' => (string)$body['name'],
            'phone' => (string)$body['phone'],
            'city' => (string)$body['city'],
            'society_area' => (string)($body['society_area'] ?? ''),
            'budget' => (string)($body['budget'] ?? ''),
            'requirement' => (string)$body['requirement'],
            'plan_type' => (string)($body['plan_type'] ?? ''),
            'source' => (string)($body['source'] ?? 'homepage'),
            'pro_id' => isset($body['pro_id']) ? (int)$body['pro_id'] : null,
            'floor_plan' => $body['floor_plan'] ?? null,
            'package_tier' => $body['package_tier'] ?? null,
            'rooms' => isset($body['rooms']) && is_array($body['rooms']) ? $body['rooms'] : null,
            'estimate' => isset($body['estimate']) ? (float)$body['estimate'] : null,
        ]);

        jsonResponse(['success' => true, 'lead_id' => $leadId]);
    }

    if ($path === '/api/calculator/estimate' && $method === 'POST') {
        $body = requestJson();
        if (empty($body['floor_plan']) || empty($body['package_tier']) || empty($body['rooms']) || !is_array($body['rooms'])) {
            jsonResponse(['error' => 'floor_plan, package_tier and rooms are required'], 400);
        }

        $estimate = SiteRepository::calculateEstimate((string)$body['floor_plan'], (string)$body['package_tier'], $body['rooms']);

        if (!empty($body['name']) && !empty($body['phone']) && !empty($body['city'])) {
            if (empty($body['lead_consent'])) {
                jsonResponse(['error' => 'Consent is required before submitting the enquiry.'], 400);
            }
            SiteRepository::createLead([
                'name' => (string)$body['name'],
                'phone' => (string)$body['phone'],
                'city' => (string)$body['city'],
                'society_area' => (string)($body['society_area'] ?? ''),
                'budget' => (string)($body['budget'] ?? ''),
                'requirement' => (string)($body['requirement'] ?? 'Design Cost Calculator'),
                'source' => 'calculator',
                'floor_plan' => (string)$body['floor_plan'],
                'package_tier' => (string)$body['package_tier'],
                'rooms' => $body['rooms'],
                'estimate' => $estimate,
            ]);
        }

        jsonResponse(['estimate' => $estimate]);
    }

    if ($path === '/api/lead-marketplace/counts' && $method === 'GET') {
        $dateFilter = (string)($_GET['date_filter'] ?? 'all_time');
        jsonResponse([
            'items' => SiteRepository::leadMarketplaceCounts(
                $dateFilter,
                isset($_GET['start_date']) ? (string)$_GET['start_date'] : null,
                isset($_GET['end_date']) ? (string)$_GET['end_date'] : null
            ),
        ]);
    }

    if ($path === '/api/lead-coupons/public' && $method === 'GET') {
        jsonResponse(['coupons' => SiteRepository::publicLeadCoupons()]);
    }

    if ($path === '/api/lead-cart') {
        Auth::start();
        $_SESSION['lead_cart'] = isset($_SESSION['lead_cart']) && is_array($_SESSION['lead_cart']) ? $_SESSION['lead_cart'] : [];
        $buyer = buyerUser();
        $firstTimeEligible = !$buyer || SiteRepository::buyerFirstTimeLeadOfferEligible((int)$buyer['id']);
        if ($method === 'GET') {
            try {
                jsonResponse(SiteRepository::leadCartSummary($_SESSION['lead_cart'], $_SESSION['lead_coupon'] ?? null, $firstTimeEligible));
            } catch (InvalidArgumentException) {
                unset($_SESSION['lead_coupon']);
                jsonResponse(SiteRepository::leadCartSummary($_SESSION['lead_cart'], null, $firstTimeEligible));
            }
        }
        if ($method === 'POST') {
            $body = requestJson();
            $item = SiteRepository::normalizeLeadCartItem($body, $firstTimeEligible);
            foreach ($_SESSION['lead_cart'] as $cartKey => $cartItem) {
                $normalized = SiteRepository::normalizeLeadCartItem(is_array($cartItem) ? $cartItem : [], $firstTimeEligible);
                if (($normalized['package_id'] ?? '') === ($item['package_id'] ?? '')) {
                    unset($_SESSION['lead_cart'][$cartKey]);
                }
            }
            $_SESSION['lead_cart'][$item['id']] = $item;
            try {
                jsonResponse(['success' => true] + SiteRepository::leadCartSummary($_SESSION['lead_cart'], $_SESSION['lead_coupon'] ?? null, $firstTimeEligible));
            } catch (InvalidArgumentException) {
                unset($_SESSION['lead_coupon']);
                jsonResponse(['success' => true] + SiteRepository::leadCartSummary($_SESSION['lead_cart'], null, $firstTimeEligible));
            }
        }
        if ($method === 'DELETE') {
            $id = (string)($_GET['id'] ?? '');
            if ($id === 'all') {
                $_SESSION['lead_cart'] = [];
                unset($_SESSION['lead_coupon']);
            } elseif ($id !== '') {
                unset($_SESSION['lead_cart'][$id]);
                foreach ($_SESSION['lead_cart'] as $cartKey => $cartItem) {
                    $normalized = SiteRepository::normalizeLeadCartItem(is_array($cartItem) ? $cartItem : [], $firstTimeEligible);
                    if (($normalized['id'] ?? '') === $id || ($normalized['package_id'] ?? '') === $id) {
                        unset($_SESSION['lead_cart'][$cartKey]);
                    }
                }
            }
            try {
                jsonResponse(['success' => true] + SiteRepository::leadCartSummary($_SESSION['lead_cart'], $_SESSION['lead_coupon'] ?? null, $firstTimeEligible));
            } catch (InvalidArgumentException) {
                unset($_SESSION['lead_coupon']);
                jsonResponse(['success' => true] + SiteRepository::leadCartSummary($_SESSION['lead_cart'], null, $firstTimeEligible));
            }
        }
    }

    if ($path === '/api/lead-cart/coupon' && $method === 'POST') {
        Auth::start();
        $_SESSION['lead_cart'] = isset($_SESSION['lead_cart']) && is_array($_SESSION['lead_cart']) ? $_SESSION['lead_cart'] : [];
        $body = requestJson();
        $code = strtoupper(trim((string)($body['code'] ?? '')));
        $buyer = buyerUser();
        $firstTimeEligible = !$buyer || SiteRepository::buyerFirstTimeLeadOfferEligible((int)$buyer['id']);
        $summary = SiteRepository::leadCartSummary($_SESSION['lead_cart'], null, $firstTimeEligible);
        $coupon = SiteRepository::validateLeadCoupon($code, (float)$summary['subtotal'], (int)$summary['lead_count']);
        $_SESSION['lead_coupon'] = (string)$coupon['code'];
        jsonResponse(['success' => true] + SiteRepository::leadCartSummary($_SESSION['lead_cart'], $_SESSION['lead_coupon'], $firstTimeEligible));
    }

    if ($path === '/api/lead-cart/coupon' && $method === 'DELETE') {
        Auth::start();
        unset($_SESSION['lead_coupon']);
        $_SESSION['lead_cart'] = isset($_SESSION['lead_cart']) && is_array($_SESSION['lead_cart']) ? $_SESSION['lead_cart'] : [];
        $buyer = buyerUser();
        $firstTimeEligible = !$buyer || SiteRepository::buyerFirstTimeLeadOfferEligible((int)$buyer['id']);
        jsonResponse(['success' => true] + SiteRepository::leadCartSummary($_SESSION['lead_cart'], null, $firstTimeEligible));
    }

    if ($path === '/api/buyer/login' && $method === 'POST') {
        $body = requestJson();
        $buyer = SiteRepository::loginBuyer((string)($body['phone'] ?? ''), (string)($body['password'] ?? ''));
        if (!$buyer) {
            jsonResponse(['error' => 'Invalid mobile number or password'], 401);
        }
        setBuyerSession($buyer);
        jsonResponse(['success' => true, 'buyer' => buyerUser()]);
    }

    if ($path === '/api/buyer/logout' && ($method === 'POST' || $method === 'GET')) {
        clearBuyerSession();
        if ($method === 'GET') {
            redirectTo('/lead-checkout');
        }
        jsonResponse(['success' => true]);
    }

    if ($path === '/api/buyer/me' && $method === 'GET') {
        jsonResponse(['buyer' => buyerUser()]);
    }

    if ($path === '/api/buyer/purchases' && $method === 'GET') {
        $buyer = requireBuyer();
        jsonResponse(['purchases' => SiteRepository::buyerPurchases((int)$buyer['id'])]);
    }

    if (($path === '/api/create-order' || $path === '/api/lead-orders/create') && $method === 'POST') {
        Auth::start();
        $body = requestJson();

        if (isset($body['amount'])) {
            $amount = (int)$body['amount'];
            if ($amount < 100) {
                jsonResponse(['error' => 'Amount must be at least 100 paise'], 400);
            }
            $currency = strtoupper(trim((string)($body['currency'] ?? (defined('RAZORPAY_CURRENCY') ? RAZORPAY_CURRENCY : 'INR'))));
            if (!preg_match('/^[A-Z]{3}$/', $currency)) {
                jsonResponse(['error' => 'Currency must be a valid 3-letter code'], 400);
            }
            $receipt = trim((string)($body['receipt'] ?? 'order_' . time()));
            try {
                $order = razorpayCreateOrder($amount, $receipt, ['module' => 'standard_checkout'], $currency);
            } catch (RuntimeException $e) {
                jsonResponse(['error' => $e->getMessage()], $e->getCode() === 401 ? 401 : 500);
            }
            jsonResponse([
                'success' => true,
                'order_id' => (string)$order['id'],
                'amount' => (int)($order['amount'] ?? $amount),
                'currency' => (string)($order['currency'] ?? $currency),
            ]);
        }

        $_SESSION['lead_cart'] = isset($_SESSION['lead_cart']) && is_array($_SESSION['lead_cart']) ? $_SESSION['lead_cart'] : [];
        $buyer = buyerUser();
        if (!$buyer) {
            $buyer = SiteRepository::createOrLoginBuyer($body['buyer'] ?? []);
            setBuyerSession($buyer);
        }
        $firstTimeEligible = SiteRepository::buyerFirstTimeLeadOfferEligible((int)$buyer['id']);
        if (!empty($body['account_only'])) {
            try {
                $summary = SiteRepository::leadCartSummary($_SESSION['lead_cart'], $_SESSION['lead_coupon'] ?? null, $firstTimeEligible);
            } catch (InvalidArgumentException) {
                unset($_SESSION['lead_coupon']);
                $summary = SiteRepository::leadCartSummary($_SESSION['lead_cart'], null, $firstTimeEligible);
            }
            jsonResponse(['success' => true, 'buyer' => buyerUser(), 'cart' => $summary]);
        }
        try {
            $summary = SiteRepository::leadCartSummary($_SESSION['lead_cart'], $_SESSION['lead_coupon'] ?? null, $firstTimeEligible);
        } catch (InvalidArgumentException) {
            unset($_SESSION['lead_coupon']);
            $summary = SiteRepository::leadCartSummary($_SESSION['lead_cart'], null, $firstTimeEligible);
        }
        $cart = $summary['items'];
        if (!$summary['items']) {
            jsonResponse(['error' => 'Cart is empty'], 400);
        }
        $amount = (float)$summary['grand_total'];
        if ($amount <= 0) {
            SiteRepository::createFreeLeadPurchase((int)$buyer['id'], $cart, $summary['coupon']['code'] ?? null, (float)$summary['discount_amount']);
            $_SESSION['lead_cart'] = [];
            unset($_SESSION['lead_coupon']);
            jsonResponse([
                'success' => true,
                'free_checkout' => true,
                'redirect_url' => '/lead-dashboard?payment=free',
                'buyer' => buyerUser(),
            ]);
        }
        $receipt = 'leads_' . (int)$buyer['id'] . '_' . time();
        try {
            $order = razorpayCreateOrder((int)round($amount * 100), $receipt, ['buyer_id' => (int)$buyer['id'], 'module' => 'lead_marketplace']);
        } catch (RuntimeException $e) {
            jsonResponse(['error' => $e->getMessage()], $e->getCode() === 401 ? 401 : 500);
        }
        SiteRepository::createLeadPurchaseOrder((int)$buyer['id'], $cart, (string)$order['id'], $amount, $summary['coupon']['code'] ?? null, (float)$summary['discount_amount']);
        jsonResponse([
            'success' => true,
            'key_id' => defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : '',
            'order_id' => (string)$order['id'],
            'amount' => (int)($order['amount'] ?? round($amount * 100)),
            'currency' => (string)($order['currency'] ?? 'INR'),
            'buyer' => buyerUser(),
        ]);
    }

    if (($path === '/api/verify-payment' || $path === '/api/lead-orders/verify') && $method === 'POST') {
        $buyer = $path === '/api/lead-orders/verify' ? requireBuyer() : buyerUser();
        $body = requestJson();
        $orderId = trim((string)($body['razorpay_order_id'] ?? ''));
        $paymentId = trim((string)($body['razorpay_payment_id'] ?? ''));
        $signature = trim((string)($body['razorpay_signature'] ?? ''));
        if ($orderId === '' || $paymentId === '' || $signature === '') {
            jsonResponse(['error' => 'Missing Razorpay payment fields'], 400);
        }
        if (!razorpaySignatureIsValid($orderId, $paymentId, $signature)) {
            jsonResponse(['error' => 'Payment signature mismatch'], 400);
        }
        $shouldMarkLeadPurchase = $path === '/api/lead-orders/verify' || (string)($body['context'] ?? '') === 'lead_marketplace';
        if ($buyer && $shouldMarkLeadPurchase) {
            SiteRepository::markLeadPurchasePaid($orderId, (int)$buyer['id'], $paymentId, $signature);
            $_SESSION['lead_cart'] = [];
            unset($_SESSION['lead_coupon']);
        }
        jsonResponse(['success' => true, 'redirect_url' => '/lead-dashboard?payment=success']);
    }

    // Admin APIs
    if ($path === '/api/admin/content') {
        Auth::requireAdmin();
        if ($method === 'GET') {
            jsonResponse(['items' => SiteRepository::contentList()]);
        }
        if ($method === 'PUT' || $method === 'POST') {
            $body = requestJson();
            if (empty($body['key_name']) || !array_key_exists('content_value', $body)) {
                jsonResponse(['error' => 'key_name and content_value are required'], 400);
            }
            SiteRepository::upsertContent((string)$body['key_name'], (string)$body['content_value'], (string)($body['content_type'] ?? 'text'));
            jsonResponse(['success' => true]);
        }
    }

    if ($path === '/api/admin/leads' && $method === 'GET') {
        Auth::requireAdmin();
        jsonResponse(['leads' => SiteRepository::listLeads()]);
    }

    if ($path === '/api/admin/leads/status' && $method === 'PUT') {
        Auth::requireAdmin();
        $body = requestJson();
        if (empty($body['lead_id']) || empty($body['status'])) {
            jsonResponse(['error' => 'lead_id and status are required'], 400);
        }
        $allowed = ['new', 'contacted', 'converted'];
        if (!in_array($body['status'], $allowed, true)) {
            jsonResponse(['error' => 'Invalid status'], 400);
        }
        SiteRepository::updateLeadStatus((int)$body['lead_id'], (string)$body['status']);
        jsonResponse(['success' => true]);
    }

    if ($path === '/api/admin/pros' && $method === 'GET') {
        Auth::requireAdmin();
        jsonResponse(['pros' => SiteRepository::listPros([])]);
    }

    if ($path === '/api/admin/pros/verify' && $method === 'PUT') {
        Auth::requireAdmin();
        $body = requestJson();
        if (empty($body['pro_id']) || !isset($body['verification_status'])) {
            jsonResponse(['error' => 'pro_id and verification_status are required'], 400);
        }
        SiteRepository::setProVerification((int)$body['pro_id'], (bool)$body['verification_status']);
        jsonResponse(['success' => true]);
    }

    if ($path === '/api/admin/lead-coupons') {
        Auth::requireAdmin();
        if ($method === 'GET') {
            jsonResponse(['coupons' => SiteRepository::listLeadCoupons()]);
        }
        if ($method === 'POST') {
            $body = requestJson();
            $id = SiteRepository::saveLeadCoupon($body);
            jsonResponse(['success' => true, 'id' => $id]);
        }
    }

    if (preg_match('#^/api/admin/lead-coupons/(\\d+)$#', $path, $match)) {
        Auth::requireAdmin();
        $id = (int)$match[1];
        if ($method === 'PUT' || $method === 'POST') {
            SiteRepository::saveLeadCoupon(requestJson(), $id);
            jsonResponse(['success' => true]);
        }
        if ($method === 'DELETE') {
            SiteRepository::deleteLeadCoupon($id);
            jsonResponse(['success' => true]);
        }
    }

    if ($path === '/api/admin/professionals') {
        Auth::requireAdmin();
        if ($method === 'GET') {
            jsonResponse(['professionals' => SiteRepository::listProfessionalsForAdmin()]);
        }
        if ($method === 'POST') {
            $body = requestData();
            if (empty($body['full_name']) || empty($body['slug'])) {
                jsonResponse(['error' => 'full_name and slug are required'], 400);
            }
            $body['profile_pic'] = saveUploadedFile($_FILES['profile_pic'] ?? [], 'professionals', null);
            $body['cover_photo'] = saveUploadedFile($_FILES['cover_photo'] ?? [], 'professionals', null);
            $id = SiteRepository::createProfessional($body);
            jsonResponse(['success' => true, 'id' => $id]);
        }
    }

    if (preg_match('#^/api/admin/professionals/(\\d+)$#', $path, $match)) {
        Auth::requireAdmin();
        $id = (int)$match[1];
        if ($method === 'PUT') {
            $body = requestData();
            if (empty($body['full_name']) || empty($body['slug'])) {
                jsonResponse(['error' => 'full_name and slug are required'], 400);
            }
            $body['profile_pic'] = saveUploadedFile($_FILES['profile_pic'] ?? [], 'professionals', (string)($body['current_profile_pic'] ?? ''));
            $body['cover_photo'] = saveUploadedFile($_FILES['cover_photo'] ?? [], 'professionals', (string)($body['current_cover_photo'] ?? ''));
            SiteRepository::updateProfessional($id, $body);
            jsonResponse(['success' => true]);
        }
        if ($method === 'DELETE') {
            SiteRepository::deleteProfessional($id);
            jsonResponse(['success' => true]);
        }
    }

    if ($path === '/api/admin/designer-accounts') {
        Auth::requireAdmin();
        if ($method === 'GET') {
            jsonResponse(['accounts' => SiteRepository::listDesignerAccounts()]);
        }
        if ($method === 'POST') {
            try {
                $id = SiteRepository::saveDesignerAccount(requestData());
                jsonResponse(['success' => true, 'id' => $id]);
            } catch (InvalidArgumentException $e) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
        }
    }

    if (preg_match('#^/api/admin/designer-accounts/(\\d+)$#', $path, $match) && ($method === 'PUT' || $method === 'POST')) {
        Auth::requireAdmin();
        try {
            SiteRepository::saveDesignerAccount(requestData(), (int)$match[1]);
            jsonResponse(['success' => true]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/admin/portfolios') {
        Auth::requireAdmin();
        if ($method === 'GET') {
            $proId = isset($_GET['pro_id']) ? (int)$_GET['pro_id'] : null;
            jsonResponse([
                'portfolios' => SiteRepository::listPortfolioForAdmin($proId),
                'professionals' => SiteRepository::professionalOptions(),
            ]);
        }
        if ($method === 'POST') {
            $body = requestData();
            if (empty($body['pro_id']) || empty($body['slug']) || empty($body['project_name'])) {
                jsonResponse(['error' => 'pro_id, slug and project_name are required'], 400);
            }
            $existingMedia = getJsonArrayField($body, 'current_media_json', []);
            $body['media_json'] = saveUploadedFiles($_FILES['media_files'] ?? [], 'portfolio', $existingMedia);
            $id = SiteRepository::createPortfolio($body);
            jsonResponse(['success' => true, 'id' => $id]);
        }
    }

    if (preg_match('#^/api/admin/portfolios/(\\d+)$#', $path, $match)) {
        Auth::requireAdmin();
        $id = (int)$match[1];
        if ($method === 'PUT') {
            $body = requestData();
            if (empty($body['pro_id']) || empty($body['slug']) || empty($body['project_name'])) {
                jsonResponse(['error' => 'pro_id, slug and project_name are required'], 400);
            }
            $existingMedia = getJsonArrayField($body, 'current_media_json', []);
            $body['media_json'] = saveUploadedFiles($_FILES['media_files'] ?? [], 'portfolio', $existingMedia);
            SiteRepository::updatePortfolio($id, $body);
            jsonResponse(['success' => true]);
        }
        if ($method === 'DELETE') {
            SiteRepository::deletePortfolio($id);
            jsonResponse(['success' => true]);
        }
    }

    if ($path === '/api/admin/property-projects') {
        Auth::requireAdmin();
        if ($method === 'GET') {
            jsonResponse(['projects' => SiteRepository::listRealEstateProjects([], true)]);
        }
        if ($method === 'POST') {
            $body = requestData();
            if (empty($body['project_name']) || empty($body['slug']) || empty($body['property_type']) || empty($body['city'])) {
                jsonResponse(['error' => 'Project name, slug, property type, and city are required'], 400);
            }
            $media = SiteRepository::decodeStructuredRows($body['media_json'] ?? []);
            foreach (saveUploadedFiles($_FILES['project_images'] ?? [], 'property-projects', []) as $url) {
                $media[] = ['media_type' => 'image', 'media_url' => $url, 'title' => $body['project_name'], 'is_cover' => $media === [] ? 1 : 0];
            }
            $floorPlans = SiteRepository::decodeStructuredRows($body['floor_plans_json'] ?? []);
            foreach (saveUploadedFiles($_FILES['floor_plan_files'] ?? [], 'property-floor-plans', []) as $url) {
                $floorPlans[] = ['title' => 'Floor plan', 'image_url' => $url];
            }
            $body['media_json'] = $media;
            $body['floor_plans_json'] = $floorPlans;
            $id = SiteRepository::saveRealEstateProject($body);
            jsonResponse(['success' => true, 'id' => $id]);
        }
    }

    if (preg_match('#^/api/admin/property-projects/(\\d+)$#', $path, $match)) {
        Auth::requireAdmin();
        $id = (int)$match[1];
        if ($method === 'GET') {
            $project = SiteRepository::getRealEstateProject($id, true);
            if (!$project) {
                jsonResponse(['error' => 'Property project not found'], 404);
            }
            jsonResponse(['project' => $project]);
        }
        if ($method === 'PUT') {
            $body = requestData();
            if (empty($body['project_name']) || empty($body['slug']) || empty($body['property_type']) || empty($body['city'])) {
                jsonResponse(['error' => 'Project name, slug, property type, and city are required'], 400);
            }
            $media = SiteRepository::decodeStructuredRows($body['media_json'] ?? []);
            foreach (saveUploadedFiles($_FILES['project_images'] ?? [], 'property-projects', []) as $url) {
                $media[] = ['media_type' => 'image', 'media_url' => $url, 'title' => $body['project_name'], 'is_cover' => $media === [] ? 1 : 0];
            }
            $floorPlans = SiteRepository::decodeStructuredRows($body['floor_plans_json'] ?? []);
            foreach (saveUploadedFiles($_FILES['floor_plan_files'] ?? [], 'property-floor-plans', []) as $url) {
                $floorPlans[] = ['title' => 'Floor plan', 'image_url' => $url];
            }
            $body['media_json'] = $media;
            $body['floor_plans_json'] = $floorPlans;
            SiteRepository::saveRealEstateProject($body, $id);
            jsonResponse(['success' => true]);
        }
        if ($method === 'DELETE') {
            SiteRepository::deleteRealEstateProject($id);
            jsonResponse(['success' => true]);
        }
    }

    if (preg_match('#^/api/admin/property-enquiries/(\\d+)/status$#', $path, $match) && $method === 'PUT') {
        Auth::requireAdmin();
        $body = requestJson();
        try {
            SiteRepository::updatePropertyEnquiryStatus((int)$match[1], (string)($body['status'] ?? ''));
            jsonResponse(['success' => true]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/admin/url-aliases') {
        Auth::requireAdmin();
        if ($method === 'GET') {
            jsonResponse(['aliases' => SiteRepository::listUrlAliases()]);
        }
        if ($method === 'POST') {
            $body = requestData();
            $body['image_url'] = saveUploadedFile($_FILES['image_file'] ?? [], 'url-aliases', (string)(($body['image_url'] ?? '') ?: ($body['current_image_url'] ?? '')));
            try {
                $id = SiteRepository::saveUrlAlias($body);
                jsonResponse(['success' => true, 'id' => $id]);
            } catch (InvalidArgumentException $e) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
        }
    }

    if (preg_match('#^/api/admin/url-aliases/(\\d+)$#', $path, $match)) {
        Auth::requireAdmin();
        $id = (int)$match[1];
        if ($method === 'GET') {
            $alias = SiteRepository::getUrlAlias($id);
            if (!$alias) {
                jsonResponse(['error' => 'URL alias not found'], 404);
            }
            jsonResponse(['alias' => $alias]);
        }
        if ($method === 'PUT' || $method === 'POST') {
            $body = requestData();
            $body['image_url'] = saveUploadedFile($_FILES['image_file'] ?? [], 'url-aliases', (string)(($body['image_url'] ?? '') ?: ($body['current_image_url'] ?? '')));
            try {
                SiteRepository::saveUrlAlias($body, $id);
                jsonResponse(['success' => true]);
            } catch (InvalidArgumentException $e) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
        }
        if ($method === 'DELETE') {
            SiteRepository::deleteUrlAlias($id);
            jsonResponse(['success' => true]);
        }
    }

    if ($path === '/api/admin/design-ideas') {
        Auth::requireAdmin();
        if ($method === 'GET') {
            jsonResponse(['ideas' => SiteRepository::listDesignIdeas([], true)]);
        }
        if ($method === 'POST') {
            $body = requestData();
            if (empty($body['name']) || empty($body['slug']) || empty($body['type'])) {
                jsonResponse(['error' => 'Idea name, slug, and type are required'], 400);
            }
            $id = SiteRepository::saveDesignIdea($body);
            jsonResponse(['success' => true, 'id' => $id]);
        }
    }

    if (preg_match('#^/api/admin/design-ideas/(\\d+)$#', $path, $match)) {
        Auth::requireAdmin();
        $id = (int)$match[1];
        if ($method === 'GET') {
            $idea = SiteRepository::getDesignIdea($id, true);
            if (!$idea) {
                jsonResponse(['error' => 'Design idea not found'], 404);
            }
            jsonResponse(['idea' => $idea]);
        }
        if ($method === 'PUT' || $method === 'POST') {
            $body = requestData();
            if (empty($body['name']) || empty($body['slug']) || empty($body['type'])) {
                jsonResponse(['error' => 'Idea name, slug, and type are required'], 400);
            }
            SiteRepository::saveDesignIdea($body, $id);
            jsonResponse(['success' => true]);
        }
        if ($method === 'DELETE') {
            SiteRepository::deleteDesignIdea($id);
            jsonResponse(['success' => true]);
        }
    }

    if ($path === '/api/admin/design-idea-aliases') {
        Auth::requireAdmin();
        if ($method === 'GET') {
            jsonResponse(['aliases' => SiteRepository::listDesignIdeaAliases(true)]);
        }
        if ($method === 'POST') {
            $body = requestData();
            if (empty($body['title']) || empty($body['slug'])) {
                jsonResponse(['error' => 'Alias title and slug are required'], 400);
            }
            $id = SiteRepository::saveDesignIdeaAlias($body);
            jsonResponse(['success' => true, 'id' => $id]);
        }
    }

    if (preg_match('#^/api/admin/design-idea-aliases/(\\d+)$#', $path, $match)) {
        Auth::requireAdmin();
        $id = (int)$match[1];
        if ($method === 'GET') {
            $alias = SiteRepository::getDesignIdeaAlias($id, true);
            if (!$alias) {
                jsonResponse(['error' => 'Alias page not found'], 404);
            }
            jsonResponse(['alias' => $alias]);
        }
        if ($method === 'PUT' || $method === 'POST') {
            $body = requestData();
            if (empty($body['title']) || empty($body['slug'])) {
                jsonResponse(['error' => 'Alias title and slug are required'], 400);
            }
            SiteRepository::saveDesignIdeaAlias($body, $id);
            jsonResponse(['success' => true]);
        }
        if ($method === 'DELETE') {
            SiteRepository::deleteDesignIdeaAlias($id);
            jsonResponse(['success' => true]);
        }
    }

    if ($path === '/api/admin/design-idea-sections') {
        Auth::requireAdmin();
        if ($method === 'GET') {
            jsonResponse(['sections' => SiteRepository::listDesignIdeaSections(true)]);
        }
        if ($method === 'POST') {
            $body = requestData();
            if (empty($body['section_key']) || empty($body['title'])) {
                jsonResponse(['error' => 'Section key and title are required'], 400);
            }
            try {
                $id = SiteRepository::saveDesignIdeaSection($body);
                jsonResponse(['success' => true, 'id' => $id]);
            } catch (InvalidArgumentException $e) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
        }
    }

    if (preg_match('#^/api/admin/design-idea-sections/(\\d+)$#', $path, $match)) {
        Auth::requireAdmin();
        $id = (int)$match[1];
        if ($method === 'GET') {
            $section = SiteRepository::getDesignIdeaSection($id, true);
            if (!$section) {
                jsonResponse(['error' => 'Design idea section not found'], 404);
            }
            jsonResponse(['section' => $section]);
        }
        if ($method === 'PUT' || $method === 'POST') {
            $body = requestData();
            if (empty($body['section_key']) || empty($body['title'])) {
                jsonResponse(['error' => 'Section key and title are required'], 400);
            }
            try {
                SiteRepository::saveDesignIdeaSection($body, $id);
                jsonResponse(['success' => true]);
            } catch (InvalidArgumentException $e) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
        }
        if ($method === 'DELETE') {
            SiteRepository::deleteDesignIdeaSection($id);
            jsonResponse(['success' => true]);
        }
    }

    if (preg_match('#^/api/admin/design-idea-leads/(\\d+)/status$#', $path, $match) && $method === 'PUT') {
        Auth::requireAdmin();
        $body = requestJson();
        try {
            SiteRepository::updateDesignIdeaLeadStatus((int)$match[1], (string)($body['status'] ?? ''));
            jsonResponse(['success' => true]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
    if ($path === '/api/quotations') {
        $user = Auth::requireAuth();
        if ($method === 'GET') {
            $filters = $_GET;
            if (Auth::isDesigner($user)) {
                $filters['designer_id'] = (int)($user['pro_id'] ?? 0);
            }
            jsonResponse(['quotations' => SiteRepository::listQuotations($filters)]);
        }
        if ($method === 'POST') {
            try {
                $body = requestData();
                if (Auth::isDesigner($user)) {
                    if (!SiteRepository::designerCanCreateQuotation($user)) {
                        jsonResponse(['error' => 'Your subscription has expired. Please renew to create new quotations.'], 403);
                    }
                    $body['designer_id'] = (int)($user['pro_id'] ?? 0);
                    $body['assigned_to_user_id'] = (int)$user['id'];
                }
                $id = SiteRepository::saveQuotation($body, null, (int)$user['id']);
                $base = Auth::isDesigner($user) ? '/designer/quotations/' : '/admin/quotations/';
                jsonResponse(['success' => true, 'id' => $id, 'redirect_url' => $base . $id]);
            } catch (InvalidArgumentException $e) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
        }
    }

    if (preg_match('#^/api/quotations/(\\d+)$#', $path, $match)) {
        $user = Auth::requireAuth();
        $id = (int)$match[1];
        if (!SiteRepository::userCanAccessQuotation($user, $id)) {
            jsonResponse(['error' => 'Quotation not found'], 404);
        }
        if ($method === 'GET') {
            $quote = SiteRepository::getQuotation($id);
            if (!$quote) {
                jsonResponse(['error' => 'Quotation not found'], 404);
            }
            jsonResponse(['quotation' => $quote]);
        }
        if ($method === 'PUT' || $method === 'POST') {
            try {
                $body = requestData();
                if (Auth::isDesigner($user)) {
                    $body['designer_id'] = (int)($user['pro_id'] ?? 0);
                    $body['assigned_to_user_id'] = (int)$user['id'];
                }
                SiteRepository::saveQuotation($body, $id, (int)$user['id']);
                jsonResponse(['success' => true, 'id' => $id]);
            } catch (InvalidArgumentException $e) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
        }
        if ($method === 'DELETE') {
            SiteRepository::deleteQuotation($id);
            jsonResponse(['success' => true]);
        }
    }

    if (preg_match('#^/api/quotations/(\\d+)/(duplicate|revision|send|status)$#', $path, $match) && $method === 'POST') {
        $user = Auth::requireAuth();
        $id = (int)$match[1];
        $action = (string)$match[2];
        if (!SiteRepository::userCanAccessQuotation($user, $id)) {
            jsonResponse(['error' => 'Quotation not found'], 404);
        }
        try {
            if ($action === 'duplicate' || $action === 'revision') {
                $newId = SiteRepository::duplicateQuotation($id, (int)$user['id'], $action === 'revision');
                $base = Auth::isDesigner($user) ? '/designer/quotations/' : '/admin/quotations/';
                jsonResponse(['success' => true, 'id' => $newId, 'redirect_url' => $base . $newId]);
            }
            if ($action === 'send') {
                SiteRepository::updateQuotationStatus($id, 'sent_to_client', (int)$user['id'], 'Proposal marked as sent');
                $quote = SiteRepository::getQuotation($id);
                jsonResponse([
                    'success' => true,
                    'proposal_url' => $quote ? absoluteUrl('/proposal/' . (string)$quote['proposal_token'] . '/pdf') : '',
                ]);
            }
            $body = requestJson();
            SiteRepository::updateQuotationStatus($id, (string)($body['status'] ?? ''), (int)$user['id'], (string)($body['notes'] ?? ''));
            jsonResponse(['success' => true]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if (preg_match('#^/api/quotations/(\\d+)/pdf$#', $path, $match) && $method === 'GET') {
        $user = Auth::requireAuth();
        if (!SiteRepository::userCanAccessQuotation($user, (int)$match[1])) {
            http_response_code(404);
            echo 'Quotation not found';
            exit;
        }
        $quote = SiteRepository::getQuotation((int)$match[1]);
        if (!$quote) {
            http_response_code(404);
            echo 'Quotation not found';
            exit;
        }
        render('public/proposal-print', [
            'title' => $quote['quote_number'] . ' Proposal PDF | HomeInteriors360',
            'metaRobots' => 'noindex,nofollow',
            'content' => $content,
            'quote' => $quote,
            'publicMode' => false,
            'autoPrint' => true,
        ]);
        exit;
    }

    if ($path === '/api/rate-cards') {
        Auth::requireAdmin();
        if ($method === 'GET') {
            jsonResponse(['rate_cards' => SiteRepository::quotationRateCards($_GET)]);
        }
        if ($method === 'POST') {
            try {
                $id = SiteRepository::saveQuotationRateCard(requestData());
                jsonResponse(['success' => true, 'id' => $id]);
            } catch (InvalidArgumentException $e) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
        }
    }

    if (preg_match('#^/api/rate-cards/(\\d+)$#', $path, $match) && ($method === 'PUT' || $method === 'POST')) {
        Auth::requireAdmin();
        try {
            SiteRepository::saveQuotationRateCard(requestData(), (int)$match[1]);
            jsonResponse(['success' => true]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/quotation-packages') {
        Auth::requireAdmin();
        if ($method === 'GET') {
            jsonResponse(['packages' => SiteRepository::quotationPackages()]);
        }
        if ($method === 'POST') {
            try {
                $id = SiteRepository::saveQuotationPackage(requestData());
                jsonResponse(['success' => true, 'id' => $id]);
            } catch (InvalidArgumentException $e) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
        }
    }

    if (preg_match('#^/api/quotation-packages/(\\d+)$#', $path, $match) && ($method === 'PUT' || $method === 'POST')) {
        Auth::requireAdmin();
        try {
            SiteRepository::saveQuotationPackage(requestData(), (int)$match[1]);
            jsonResponse(['success' => true]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/proposal-templates') {
        Auth::requireAdmin();
        if ($method === 'GET') {
            jsonResponse(['templates' => SiteRepository::proposalTemplates()]);
        }
        if ($method === 'POST') {
            try {
                $id = SiteRepository::saveProposalTemplate(requestData());
                jsonResponse(['success' => true, 'id' => $id]);
            } catch (InvalidArgumentException $e) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
        }
    }

    if (preg_match('#^/api/proposal-templates/(\\d+)$#', $path, $match) && ($method === 'PUT' || $method === 'POST')) {
        Auth::requireAdmin();
        try {
            SiteRepository::saveProposalTemplate(requestData(), (int)$match[1]);
            jsonResponse(['success' => true]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/quotation-settings') {
        Auth::requireAdmin();
        if ($method === 'GET') {
            jsonResponse(['settings' => SiteRepository::quotationSettings()]);
        }
        if ($method === 'POST' || $method === 'PUT') {
            SiteRepository::saveQuotationSettings(requestData());
            jsonResponse(['success' => true]);
        }
    }

    if (preg_match('#^/proposal/([a-f0-9]{32,96})(/pdf)?$#', $path, $match)) {
        if (empty($match[2])) {
            http_response_code(404);
            echo '404 Not Found';
            exit;
        }
        $quote = SiteRepository::publicProposalData((string)$match[1], false);
        if (!$quote) {
            http_response_code(404);
            echo 'Proposal not found';
            exit;
        }
        $expired = !empty($quote['valid_until']) && strtotime((string)$quote['valid_until']) < strtotime(date('Y-m-d')) && !in_array((string)$quote['status'], ['accepted', 'rejected'], true);
        render('public/proposal-print', [
            'title' => $quote['quote_number'] . ' Proposal | HomeInteriors360',
            'metaDescription' => 'Client proposal from HomeInteriors360.',
            'metaRobots' => 'noindex,nofollow',
            'content' => $content,
            'quote' => $quote,
            'expired' => $expired,
            'publicMode' => true,
            'autoPrint' => !empty($match[2]),
        ]);
        exit;
    }

    $legalPages = [
        '/contact-us' => [
            'title' => 'Contact Us',
            'meta' => 'Contact HomeInteriors360 for lead purchases, interior design enquiries, support, and billing help.',
            'sections' => [
                [
                    'title' => 'Business and Support Contact',
                    'body' => [
                        'HomeInteriors360 helps homeowners discover interior professionals and helps architects, interior designers, contractors, and related service providers purchase qualified homeowner leads.',
                        [
                            'Support email: jitender@homeinteriors360.com',
                            'Support phone: +91-9540573661',
                            'Website: https://homeinteriors360.com',
                            'Typical response time: within 1 to 2 business days',
                        ],
                    ],
                ],
                [
                    'title' => 'Payment Support',
                    'body' => [
                        'For Razorpay payment queries, share your registered mobile number, email address, order amount, payment date, and Razorpay payment ID if available.',
                    ],
                ],
            ],
        ],
        '/privacy-policy' => [
            'title' => 'Privacy Policy',
            'meta' => 'DPDPA 2023 aligned privacy policy for HomeInteriors360 users, lead buyers, homeowners, professionals, and payment customers.',
            'lastUpdated' => '5 June 2026',
            'sections' => [
                [
                    'title' => 'Purpose and Applicability',
                    'body' => [
                        'This Privacy Policy explains how HomeInteriors360 collects, uses, stores, shares, and protects personal data when you use HomeInteriors360.com, submit an interior design enquiry, register as a professional or lead buyer, purchase digital leads, or contact support.',
                        'This policy is intended to align with the Digital Personal Data Protection Act, 2023 of India. In this policy, you are the Data Principal and HomeInteriors360 acts as the Data Fiduciary for personal data that we collect and process for our website, marketplace, enquiry, payment, support, and marketing activities.',
                    ],
                ],
                [
                    'title' => 'Personal Data We Collect',
                    'body' => [
                        'We collect personal data that you provide directly, data generated when you use the website, and limited transaction information required to operate the service.',
                        [
                            'Identity and contact details such as name, mobile number, email address, city, society name, locality, and address or project area details where provided.',
                            'Interior requirement details such as property type, rooms, scope of work, budget range, design preferences, project timeline, uploaded information, and messages submitted through forms.',
                            'Professional or buyer details such as company name, role, business category, service area, plan interest, buyer account information, and purchased lead package details.',
                            'Payment and order references such as Razorpay order ID, payment ID, amount, currency, payment status, invoice or receipt references, and refund status where applicable.',
                            'Technical and usage data such as IP address, browser, device, pages visited, cookies, analytics identifiers, source campaign, and security logs.',
                        ],
                    ],
                ],
                [
                    'title' => 'How We Use Personal Data',
                    'body' => [
                        'We process personal data only for lawful purposes connected with HomeInteriors360 services, user requests, consent, legal compliance, security, and legitimate platform operations.',
                        [
                            'To respond to homeowner enquiries and match users with interior designers, architects, contractors, aggregators, or other relevant service providers.',
                            'To create and manage professional, buyer, lead marketplace, cart, checkout, and support workflows.',
                            'To sell, deliver, and support digital lead packages and managed growth enquiries.',
                            'To verify orders, process payments, prevent fraud, handle refunds, and maintain transaction records.',
                            'To send service updates, promotional communication, reminders, offers, and follow-up messages by email, SMS, WhatsApp, RCS, phone call, or other communication channels where consent or applicable law permits.',
                            'To improve the website, measure campaign performance, troubleshoot issues, secure the platform, and comply with legal, tax, audit, or regulatory requirements.',
                        ],
                    ],
                ],
                [
                    'title' => 'Consent and Withdrawal',
                    'body' => [
                        'Where we rely on consent, we ask for clear consent through website forms, account flows, checkout flows, or other communication. By ticking the consent checkbox or submitting an enquiry with consent, you agree that HomeInteriors360 may process your personal data for the specified purposes and contact you through the channels mentioned in the form.',
                        'You may withdraw consent for optional promotional communication by contacting support at jitender@homeinteriors360.com or by using any unsubscribe or opt-out method provided in our messages. Withdrawal of consent will not affect processing already completed before withdrawal and may limit our ability to provide requested services, lead delivery, support, or account communication.',
                    ],
                ],
                [
                    'title' => 'Sharing and Disclosure',
                    'body' => [
                        'We do not sell personal data as a standalone database. We may share data where necessary to provide our services, complete transactions, meet legal obligations, or protect the platform.',
                        [
                            'With registered professionals, interior designers, architects, contractors, aggregators, or lead buyers where your enquiry or purchased lead access requires such sharing.',
                            'With payment providers such as Razorpay for payment processing, order verification, refunds, fraud control, and payment support.',
                            'With hosting, database, analytics, communication, customer support, email, SMS, WhatsApp, RCS, call, marketing, and security service providers acting as processors or service partners.',
                            'With government, law enforcement, courts, regulators, or advisers if required by law, legal process, tax, audit, investigation, or dispute resolution.',
                            'With a successor entity if there is a merger, acquisition, restructuring, sale of assets, or business transfer, subject to continued protection of personal data.',
                        ],
                    ],
                ],
                [
                    'title' => 'Payments and Financial Data',
                    'body' => [
                        'Online payments are processed by Razorpay or other authorised payment partners. HomeInteriors360 does not store card numbers, UPI PINs, banking passwords, CVV, or full payment instrument credentials on its server.',
                        'We store only limited payment references required for order fulfilment, payment verification, refunds, accounting, dispute handling, and compliance.',
                    ],
                ],
                [
                    'title' => 'Cookies, Analytics, and Tracking',
                    'body' => [
                        'We may use cookies, pixels, analytics tags, Google Tag Manager, advertising tags, and similar technologies to understand website usage, measure campaigns, improve user experience, prevent abuse, and run remarketing or promotional campaigns.',
                        'You can control cookies through your browser settings. Disabling cookies may affect login, cart, checkout, analytics, or website functionality.',
                    ],
                ],
                [
                    'title' => 'Your Rights as a Data Principal',
                    'body' => [
                        'Subject to applicable law, you may request access to information about your personal data, correction of inaccurate or incomplete data, erasure of data that is no longer necessary, grievance redressal, and withdrawal of consent for consent-based processing.',
                        [
                            'Access: ask us for a summary of personal data being processed and relevant processing activities.',
                            'Correction: ask us to correct inaccurate or misleading personal data.',
                            'Completion: ask us to complete incomplete personal data where necessary for the processing purpose.',
                            'Erasure: ask us to delete personal data when it is no longer necessary for the purpose for which it was collected, unless retention is required by law or legitimate business needs.',
                            'Grievance: raise a privacy grievance using the contact details on this page.',
                            'Nomination: nominate another individual to exercise your rights in case of death or incapacity, where and when this right is operational under applicable rules.',
                        ],
                    ],
                ],
                [
                    'title' => 'Data Retention',
                    'body' => [
                        'We retain personal data only as long as reasonably required for the purposes described in this policy, including lead matching, buyer access, payment records, support, fraud prevention, legal compliance, tax, audit, dispute resolution, and service improvement.',
                        'When personal data is no longer required, we will delete, anonymise, aggregate, or securely archive it, unless retention is necessary to comply with law or defend legal claims.',
                    ],
                ],
                [
                    'title' => 'Security Safeguards',
                    'body' => [
                        'We use reasonable technical, organisational, and contractual safeguards designed to protect personal data from unauthorised access, misuse, disclosure, alteration, loss, or destruction. These may include access controls, server-side validation, payment signature verification, HTTPS, restricted administrative access, backups, and monitoring.',
                        'No online system is completely risk-free. Users should also keep account credentials confidential and contact us promptly if they suspect unauthorised access or misuse.',
                    ],
                ],
                [
                    'title' => 'Children and Minors',
                    'body' => [
                        'HomeInteriors360 services are intended for adults and business users. We do not knowingly collect personal data from children under 18 years of age. If we learn that a child has submitted personal data without appropriate consent, we will take reasonable steps to delete or restrict the data.',
                    ],
                ],
                [
                    'title' => 'Cross-Border Processing',
                    'body' => [
                        'Our hosting, analytics, communication, support, or payment partners may process data in India or in other countries where permitted by applicable law and platform configuration. We take reasonable steps to ensure that such processing remains connected to the purposes described in this policy and protected through appropriate safeguards.',
                    ],
                ],
                [
                    'title' => 'Grievance and Privacy Contact',
                    'body' => [
                        'For privacy requests, consent withdrawal, correction, erasure, or grievances, contact HomeInteriors360 support at jitender@homeinteriors360.com or +91-9540573661. Please include your name, registered mobile number or email address, and a clear description of the request so we can verify and respond appropriately.',
                        'We aim to respond to genuine privacy requests within a reasonable time. Additional verification may be required before acting on requests that affect account access, lead delivery, payment records, or third-party disclosures.',
                    ],
                ],
            ],
        ],
        '/terms-and-conditions' => [
            'title' => 'Terms and Conditions',
            'meta' => 'Terms and conditions for HomeInteriors360 services, lead purchases, consent, privacy, payments, and website use.',
            'lastUpdated' => '5 June 2026',
            'sections' => [
                [
                    'title' => 'Acceptance of Terms',
                    'body' => [
                        'By accessing HomeInteriors360.com, submitting a form, creating an account, purchasing a lead package, using the lead marketplace, or communicating with us, you agree to these Terms and Conditions, the Privacy Policy, the Cancellation and Refunds Policy, the Shipping Policy, and any additional terms shown at checkout or on the relevant page.',
                        'If you do not agree with these terms, do not use the website or submit personal data through our forms.',
                    ],
                ],
                [
                    'title' => 'About HomeInteriors360 Services',
                    'body' => [
                        'HomeInteriors360 provides an online discovery, enquiry, and lead marketplace for home interior design, architecture, contractor, renovation, aggregator, and related home improvement services. We are an aggregator and marketplace platform, not the final interior execution vendor unless expressly stated in a written agreement.',
                        'Our services may include homeowner enquiry collection, designer or professional discovery, lead marketplace access, paid digital lead packages, managed growth enquiries, account support, professional profile visibility, and related digital services.',
                    ],
                ],
                [
                    'title' => 'User Responsibilities',
                    'body' => [
                        [
                            'You must provide true, current, and complete information in forms, checkout, and account flows.',
                            'You must use the website only for lawful purposes and must not misuse, scrape, attack, spam, reverse engineer, overload, or disrupt the platform.',
                            'You must not submit another person\'s personal data without authority or consent.',
                            'Professionals and buyers must contact leads respectfully and comply with applicable consumer protection, telecom, privacy, advertising, and data protection laws.',
                            'You are responsible for maintaining confidentiality of login credentials and for activity under your account.',
                        ],
                    ],
                ],
                [
                    'title' => 'Consent for Communication',
                    'body' => [
                        'When you tick the consent checkbox or submit a form with consent, you agree to HomeInteriors360 processing your personal data as described in the Privacy Policy and contacting you for service updates, enquiry follow-up, offers, promotions, reminders, account support, and related communication through email, SMS, WhatsApp, RCS, phone call, or other communication channels.',
                        'You may withdraw consent for optional promotional communication by contacting support. Service, transaction, payment, legal, account, security, or lead delivery messages may still be sent where necessary to fulfil a request, comply with law, or protect the platform.',
                    ],
                ],
                [
                    'title' => 'Lead Purchase Terms',
                    'body' => [
                        [
                            'Lead packages are priced according to available filters, lead count, buyer eligibility, and pricing slabs shown before checkout.',
                            'Purchased leads are digital products made available to the buyer account that completed payment and must not be resold, republished, scraped, shared publicly, or misused.',
                            'A lead is an enquiry or contact record and is not a guaranteed conversion, sale, site visit, meeting, project award, revenue, or exclusive opportunity unless expressly stated.',
                            'Lead availability, count, quality signals, geography, budget, and filters may change based on database updates, duplicate checks, user behaviour, and operational rules.',
                            'Buyers are responsible for professional follow-up, lawful communication, offer accuracy, customer handling, and service delivery after contacting a lead.',
                        ],
                    ],
                ],
                [
                    'title' => 'Homeowner Enquiries and Designer Matching',
                    'body' => [
                        'When a homeowner submits an enquiry, HomeInteriors360 may share relevant details with selected professionals, aggregators, service providers, or team members to help respond to the requirement. We do not guarantee that every enquiry will receive a quote, site visit, design proposal, or final service provider selection.',
                        'Any contract, site visit, quotation, design, execution, warranty, after-sales support, or payment arrangement between a homeowner and a third-party professional is between those parties unless HomeInteriors360 is expressly made a party through a written agreement.',
                    ],
                ],
                [
                    'title' => 'Pricing, Offers, Coupons, and Taxes',
                    'body' => [
                        'Prices, discounts, coupons, first-time offers, lead slabs, and plan features may be updated from time to time. The final payable amount shown at checkout before payment is the applicable amount for that order.',
                        'Coupons may have minimum order value, expiry, usage, buyer eligibility, lead count, and other conditions. HomeInteriors360 may modify, disable, or reject coupons where misuse, technical error, or ineligibility is detected.',
                        'Taxes, payment gateway charges, invoices, and compliance records may be handled as required by applicable law and business process.',
                    ],
                ],
                [
                    'title' => 'Payments and Razorpay',
                    'body' => [
                        'Payments are collected in INR through Razorpay or other authorised payment partners. An order is treated as successful only after payment confirmation and successful payment signature or status verification by HomeInteriors360.',
                        'HomeInteriors360 does not store card number, CVV, UPI PIN, banking password, or full payment credential information on its server. Payment failures, bank delays, chargebacks, refunds, and settlement timelines may be subject to payment partner and bank processes.',
                    ],
                ],
                [
                    'title' => 'Cancellations, Refunds, and Delivery',
                    'body' => [
                        'Digital lead package delivery, cancellation, and refund rules are explained in the Shipping Policy and Cancellation and Refunds Policy. Since lead packages are digital products, cancellation is generally unavailable once leads are delivered or made available in the buyer dashboard, except where the refund policy applies.',
                    ],
                ],
                [
                    'title' => 'Privacy and DPDPA 2023',
                    'body' => [
                        'Use of the website involves processing of personal data. Our Privacy Policy explains the purposes of processing, consent, withdrawal, sharing, retention, security, Data Principal rights, and grievance contact details in relation to the Digital Personal Data Protection Act, 2023.',
                        'By using the website and submitting forms, you agree to provide accurate data and to avoid submitting personal data of another person without valid authority or consent.',
                    ],
                ],
                [
                    'title' => 'Intellectual Property',
                    'body' => [
                        'The website content, design, layout, logos, text, images, software, lead marketplace structure, pricing logic, and platform materials are owned by or licensed to HomeInteriors360, unless otherwise stated. You may not copy, reproduce, scrape, sell, or commercially exploit website content without written permission.',
                    ],
                ],
                [
                    'title' => 'Third-Party Services and Links',
                    'body' => [
                        'The website may contain links, embeds, analytics, payment tools, communication tools, or listings involving third parties. HomeInteriors360 is not responsible for third-party websites, independent professional services, third-party privacy practices, or external content that we do not control.',
                    ],
                ],
                [
                    'title' => 'Disclaimers and Limitation of Liability',
                    'body' => [
                        'The website and services are provided on an as-is and as-available basis. While we aim to maintain accurate information and smooth delivery, we do not warrant uninterrupted access, error-free operation, guaranteed lead conversion, guaranteed project award, guaranteed quote, or guaranteed third-party performance.',
                        'To the maximum extent permitted by law, HomeInteriors360 will not be liable for indirect, incidental, consequential, special, punitive, or loss-of-profit damages arising from website use, lead purchase, third-party services, or communication between users and professionals.',
                    ],
                ],
                [
                    'title' => 'Suspension and Termination',
                    'body' => [
                        'We may suspend, restrict, or terminate access to the website, buyer account, lead dashboard, coupons, or services if we suspect fraud, payment misuse, unlawful activity, data misuse, scraping, abusive communication, policy violation, security risk, or breach of these terms.',
                    ],
                ],
                [
                    'title' => 'Governing Law and Dispute Resolution',
                    'body' => [
                        'These terms are governed by the laws of India. The parties will first try to resolve disputes through good-faith support escalation. Subject to applicable law, courts and authorities with jurisdiction over the business location of HomeInteriors360 will have jurisdiction for disputes arising from these terms or website use.',
                    ],
                ],
                [
                    'title' => 'Updates to Terms',
                    'body' => [
                        'We may update these Terms and Conditions to reflect service changes, legal requirements, payment rules, data protection obligations, or operational improvements. The updated version will be posted on this page with the latest update date. Continued use of the website after updates means you accept the updated terms.',
                    ],
                ],
            ],
        ],
        '/shipping-policy' => [
            'title' => 'Shipping Policy',
            'meta' => 'Shipping and delivery policy for HomeInteriors360 digital lead packages.',
            'sections' => [
                [
                    'title' => 'Digital Delivery Only',
                    'body' => [
                        'HomeInteriors360 currently sells digital lead packages and service enquiries. No physical product is shipped by courier, post, or transport.',
                    ],
                ],
                [
                    'title' => 'Delivery Timeline',
                    'body' => [
                        'After successful payment verification, purchased lead packages are made available in the buyer dashboard for download or access. Delivery is usually immediate, but may take up to 24 hours if payment confirmation, account verification, or technical review is required.',
                    ],
                ],
                [
                    'title' => 'Delivery Issues',
                    'body' => [
                        'If a paid lead package is not visible in your dashboard after successful payment, contact support with your registered mobile number, email address, amount paid, and Razorpay payment ID.',
                    ],
                ],
            ],
        ],
        '/pricing-details' => [
            'title' => 'Pricing Details',
            'meta' => 'Pricing details for HomeInteriors360 lead packages and managed growth services.',
            'sections' => [
                [
                    'title' => 'Lead Package Pricing',
                    'body' => [
                        'Lead package prices are shown before checkout based on the selected city, locality, budget, work type, date range, and available lead count.',
                        [
                            'First-time eligible buyer offer: up to first 10 leads at INR 0 when available.',
                            'First 100 paid leads: INR 100 per lead.',
                            '101 to 1000 paid leads: INR 80 per lead.',
                            'Above 1000 paid leads: INR 60 per lead.',
                        ],
                    ],
                ],
                [
                    'title' => 'Final Payable Amount',
                    'body' => [
                        'The cart and checkout pages show subtotal, coupon discount if any, and final payable amount before Razorpay payment is opened.',
                    ],
                ],
                [
                    'title' => 'Managed Growth Account',
                    'body' => [
                        'Managed growth account pricing is custom and depends on scope, geography, profile management, content support, and lead generation requirements. Interested professionals can submit the pricing enquiry form and the sales team will respond.',
                    ],
                ],
            ],
        ],
        '/cancellation-and-refunds-policy' => [
            'title' => 'Cancellation and Refunds Policy',
            'meta' => 'Cancellation and refund policy for HomeInteriors360 paid lead packages and services.',
            'sections' => [
                [
                    'title' => 'Cancellation Before Payment',
                    'body' => [
                        'You can cancel a lead package order any time before completing Razorpay payment by leaving checkout or clearing your cart. No amount is charged before successful payment.',
                    ],
                ],
                [
                    'title' => 'After Successful Payment',
                    'body' => [
                        'Paid lead packages are digital products. Once leads are delivered or made available in the buyer dashboard, cancellation is generally not available.',
                    ],
                ],
                [
                    'title' => 'Refund Eligibility',
                    'body' => [
                        [
                            'Duplicate payment for the same order.',
                            'Payment debited but no order or lead package created after verification.',
                            'Technical delivery failure that HomeInteriors360 cannot resolve within a reasonable time.',
                        ],
                    ],
                ],
                [
                    'title' => 'Refund Timeline',
                    'body' => [
                        'Approved refunds are initiated to the original payment method through Razorpay or the relevant payment channel. Bank or payment provider timelines may apply after the refund is initiated.',
                    ],
                ],
                [
                    'title' => 'Non-Refundable Cases',
                    'body' => [
                        'Refunds are not provided merely because a purchased lead does not convert into a project, the buyer changes their mind after delivery, or the buyer misuses or fails to contact the leads.',
                    ],
                ],
            ],
        ],
    ];

    if (isset($legalPages[$path])) {
        $page = $legalPages[$path];
        render('public/legal', [
            'title' => $page['title'] . ' | HomeInteriors360',
            'metaDescription' => $page['meta'],
            'active' => '',
            'content' => $content,
            'legalTitle' => $page['title'],
            'lastUpdated' => $page['lastUpdated'] ?? '28 May 2026',
            'sections' => $page['sections'],
        ]);
        exit;
    }

    if (isset($seoLeadPages[$path])) {
        $page = $seoLeadPages[$path];
        $keyword = (string)$page['keyword'];
        $citySuffix = !empty($page['city']) ? ' in ' . (string)$page['city'] : '';
        $faqs = [
            [
                'question' => 'What are interior design leads?',
                'answer' => 'Interior design leads are homeowner enquiries with project context such as city, locality, budget, work type, requirement, and contact details that help professionals start qualified conversations.',
            ],
            [
                'question' => 'Can I buy leads by city and locality?',
                'answer' => 'Yes. HomeInteriors360 lets buyers filter lead packages by city, society or locality, budget band, work type, and date range where matching data is available.',
            ],
            [
                'question' => 'Are interior designer leads guaranteed to convert?',
                'answer' => 'No lead marketplace can guarantee conversion. We provide filtered homeowner enquiries, and your sales follow-up, pricing, portfolio, service quality, and response time influence conversion.',
            ],
            [
                'question' => 'How much do interior design leads cost?',
                'answer' => 'Eligible first-time buyers can get up to the first 10 leads free. Paid slabs are INR 100 per lead for the first 100 paid leads, INR 80 from 101 to 1000, and INR 60 above 1000.',
            ],
        ];
        $landingSections = [
            [
                'title' => 'Buy ' . ucwords($keyword) . ' With Clear Filters',
                'body' => [
                    'HomeInteriors360 is built for professionals who want practical lead discovery instead of random enquiries. Each package is created from available homeowner requirements and can be filtered by city, locality, budget, work type, and recency.',
                    'This helps architects, interior designers, contractors, modular kitchen teams, and full-home interior studios focus on leads that match their target service areas and project value.',
                ],
            ],
            [
                'title' => 'Why Professionals Use HomeInteriors360',
                'body' => [
                    'The marketplace combines lead generation, buyer account access, secure payment, and dashboard delivery. You can review lead counts and estimated pricing before opening Razorpay checkout.',
                    'For competitive searches like interior designer leads, interior design lead generation, and interior leads provider in India, the page structure is designed to explain service intent clearly to both users and search engines.',
                ],
            ],
            [
                'title' => 'Verified Lead Context' . $citySuffix,
                'body' => [
                    'Lead quality depends on context. HomeInteriors360 captures project requirement, phone number, city, society or area, budget, package details, and source wherever the homeowner provided it.',
                    'The buyer dashboard restricts downloads to paid packages linked to your account, keeping lead access cleaner and easier to audit.',
                ],
            ],
            [
                'title' => 'Transparent Pricing and Digital Delivery',
                'body' => [
                    'Lead packages use visible slab pricing. First-time eligibility, coupons, subtotal, discount, and grand total are shown before payment.',
                    'After successful Razorpay payment verification, the purchased package becomes available from the buyer dashboard as a digital download.',
                ],
            ],
        ];
        $faqSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static fn(array $faq): array => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ], $faqs),
        ];
        $serviceSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => $page['title'],
            'description' => $page['meta'],
            'provider' => [
                '@type' => 'Organization',
                'name' => 'HomeInteriors360',
                'url' => absoluteUrl('/'),
            ],
            'areaServed' => (string)($page['city'] ?? 'India'),
            'serviceType' => 'Interior design lead generation',
            'offers' => [
                '@type' => 'AggregateOffer',
                'priceCurrency' => 'INR',
                'lowPrice' => '0',
                'highPrice' => '100',
                'offerCount' => '3',
                'url' => absoluteUrl('/lead-marketplace'),
            ],
        ];
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => absoluteUrl('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $page['title'], 'item' => absoluteUrl($path)],
            ],
        ];
        render('public/seo-landing', [
            'title' => $page['title'] . ' | HomeInteriors360',
            'metaDescription' => $page['meta'],
            'canonicalUrl' => absoluteUrl($path),
            'metaModifiedTime' => date('c'),
            'active' => 'leads',
            'content' => $content,
            'landingTitle' => $page['title'],
            'landingSubtitle' => $page['subtitle'],
            'keywordChips' => [$keyword, 'verified interior leads', 'interior lead generation', 'lead marketplace'],
            'landingSections' => $landingSections,
            'faqs' => $faqs,
            'cityLinks' => $seoCityLinks,
            'structuredData' => [$serviceSchema, $faqSchema, $breadcrumbSchema],
        ]);
        exit;
    }

    // Public pages
    if ($path === '/') {
        render('public/home', [
            'title' => 'Home Interior Designers & Free Design Consultation | HomeInteriors360',
            'metaDescription' => 'Hire interior designers across Delhi NCR, compare professionals, book free design consultation, and buy verified interior design leads from HomeInteriors360.',
            'active' => 'home',
            'content' => $content,
            'payload' => SiteRepository::homepagePayload(),
        ]);
        exit;
    }

    if ($path === '/home-interior-hire-a-designer' || $path === '/hire-a-designer') {
        $serviceSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => 'Home Interior Designer Matching',
            'description' => 'Hire a home interior designer through HomeInteriors360. Submit your requirement and get matched with verified interior designers, architects, contractors, and aggregator partners.',
            'provider' => [
                '@type' => 'Organization',
                'name' => 'HomeInteriors360',
                'url' => absoluteUrl('/'),
            ],
            'areaServed' => ['Delhi NCR', 'Gurugram', 'Noida', 'Faridabad', 'Ghaziabad'],
            'serviceType' => 'Home interior designer lead generation and matching',
        ];
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => absoluteUrl('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Hire a Designer', 'item' => absoluteUrl('/home-interior-hire-a-designer')],
            ],
        ];
        render('public/home-interior-hire-designer', [
            'title' => 'Hire a Home Interior Designer | HomeInteriors360',
            'metaDescription' => 'Hire a home interior designer through HomeInteriors360. Compare verified interior designers, architects, and aggregator partners for full home interiors, kitchens, wardrobes, and renovation.',
            'canonicalUrl' => absoluteUrl('/home-interior-hire-a-designer'),
            'active' => 'directory',
            'content' => $content,
            'payload' => SiteRepository::homepagePayload(),
            'filterOptions' => SiteRepository::proFilterOptions(),
            'structuredData' => [$serviceSchema, $breadcrumbSchema],
        ]);
        exit;
    }

    if ($path === '/professionals' || preg_match('#^/professionals/([a-z0-9-]+)$#i', $path, $match)) {
        $alias = $match[1] ?? null;
        if ($alias !== null) {
            $pro = SiteRepository::getProBySlug((string)$alias);
            if ($pro) {
                $profileData = SiteRepository::proProfileData((int)$pro['id']);
                render('public/professional-profile', [
                    'title' => $pro['full_name'] . ' | ' . (string)SiteRepository::content('seo.profile.title_suffix', 'HomeInteriors360'),
                    'metaDescription' => trim(sprintf(
                        '%s in %s with %d years of experience, %d delivered projects, and detailed business profile data including office contact, service summary, portfolio, and lead form.',
                        (string)$pro['full_name'],
                        (string)($pro['city'] ?? 'your city'),
                        (int)($pro['years_experience'] ?? 0),
                        (int)($pro['projects_delivered'] ?? 0)
                    )),
                    'active' => 'directory',
                    'content' => $content,
                    'pro' => $pro,
                    'projects' => $profileData['projects'],
                    'reviews' => $profileData['reviews'],
                ]);
                exit;
            }

            $aliasData = SiteRepository::resolveDirectoryAlias((string)$alias);
            if (!$aliasData) {
                http_response_code(404);
                echo 'Professional not found';
                exit;
            }
            $initialFilters = array_filter(array_merge($aliasData['filters'] ?? [], [
                'role' => $_GET['role'] ?? null,
                'city' => $_GET['city'] ?? null,
                'work_type' => $_GET['work_type'] ?? null,
                'work_area' => $_GET['work_area'] ?? null,
                'budget_min' => $_GET['budget_min'] ?? null,
                'budget_max' => $_GET['budget_max'] ?? null,
                'experience_min' => $_GET['experience_min'] ?? null,
                'projects_min' => $_GET['projects_min'] ?? null,
                'rating_min' => $_GET['rating_min'] ?? null,
                'sort_by' => $_GET['sort_by'] ?? null,
            ]), static fn(mixed $value): bool => $value !== null && $value !== '');
            render('public/professionals', [
                'title' => $aliasData['title'] . ' | ' . (string)SiteRepository::content('seo.directory.title', 'Find Professionals'),
                'metaDescription' => $aliasData['subtitle'],
                'active' => 'directory',
                'content' => $content,
                'pros' => SiteRepository::listPros($initialFilters),
                'filterOptions' => SiteRepository::proFilterOptions(),
                'initialFilters' => $initialFilters,
                'directoryTitle' => $aliasData['title'],
                'directorySubtitle' => $aliasData['subtitle'],
            ]);
            exit;
        }

        $initialFilters = array_filter([
            'role' => $_GET['role'] ?? null,
            'city' => $_GET['city'] ?? null,
            'work_type' => $_GET['work_type'] ?? null,
            'work_area' => $_GET['work_area'] ?? null,
            'budget_min' => $_GET['budget_min'] ?? null,
            'budget_max' => $_GET['budget_max'] ?? null,
            'experience_min' => $_GET['experience_min'] ?? null,
            'projects_min' => $_GET['projects_min'] ?? null,
            'rating_min' => $_GET['rating_min'] ?? null,
            'sort_by' => $_GET['sort_by'] ?? null,
        ], static fn(mixed $value): bool => $value !== null && $value !== '');
        render('public/professionals', [
            'title' => (string)SiteRepository::content('seo.directory.title', 'Find Professionals'),
            'metaDescription' => (string)SiteRepository::content('seo.directory.description', 'Browse verified architects, designers, and contractors with filters for city, work type, budget, experience, and rating.'),
            'active' => 'directory',
            'content' => $content,
            'pros' => SiteRepository::listPros($initialFilters),
            'filterOptions' => SiteRepository::proFilterOptions(),
            'initialFilters' => $initialFilters,
        ]);
        exit;
    }

    if ($path === '/cost-calculator') {
        render('public/calculator', [
            'title' => (string)SiteRepository::content('seo.calculator.title', 'Design Cost Calculator'),
            'metaDescription' => (string)SiteRepository::content('seo.calculator.description', 'Estimate your interior design cost in a few steps and save your lead request with HomeInteriors360.'),
            'active' => 'calculator',
            'content' => $content,
        ]);
        exit;
    }

    if ($path === '/ai-room-visualizer') {
        render('public/ai-room-visualizer', [
            'title' => 'AI Room Visualizer | Before After Interior Design Render',
            'metaDescription' => 'Upload a room photo, choose a style, and create an AI-ready before-after interior design render brief with HomeInteriors360.',
            'active' => 'visualizer',
            'content' => $content,
            'styles' => SiteRepository::aiVisualizerStyles(),
        ]);
        exit;
    }

    if ($path === '/pricing') {
        render('public/pricing', [
            'title' => (string)SiteRepository::content('seo.pricing.title', 'Pricing for Architects & Interior Designers'),
            'metaDescription' => (string)SiteRepository::content('seo.pricing.description', 'Choose lead purchase or managed account plans for architects and interior designers, with sales registration and growth support.'),
            'active' => 'pricing',
            'content' => $content,
            'reviews' => SiteRepository::pricingReviews(),
        ]);
        exit;
    }

    if ($path === '/lead-marketplace') {
        render('public/lead-marketplace', [
            'title' => 'Buy Filtered Interior Design Leads | HomeInteriors360',
            'metaDescription' => 'Buy verified homeowner lead packages by city, society, budget, work type, and date range.',
            'active' => 'leads',
            'content' => $content,
        ]);
        exit;
    }

    if ($path === '/design-ideas') {
        $urlAlias = SiteRepository::getUrlAliasByPath('/design-ideas');
        $filters = array_filter([
            'q' => $_GET['q'] ?? null,
            'type' => $_GET['type'] ?? null,
            'color' => $_GET['color'] ?? null,
            'city' => $_GET['city'] ?? null,
            'state' => $_GET['state'] ?? null,
            'style' => $_GET['style'] ?? null,
            'layout' => $_GET['layout'] ?? null,
        ], static fn(mixed $value): bool => $value !== null && $value !== '');
        $matchedAlias = SiteRepository::matchDesignIdeaAliasForFilters($filters);
        if ($matchedAlias) {
            $remainingFilters = $filters;
            foreach ([
                'type' => 'filter_type',
                'color' => 'filter_color',
                'city' => 'filter_city',
                'state' => 'filter_state',
                'style' => 'filter_style',
                'layout' => 'filter_layout',
            ] as $filterKey => $aliasKey) {
                if (
                    isset($remainingFilters[$filterKey])
                    && trim((string)($matchedAlias[$aliasKey] ?? '')) !== ''
                    && mb_strtolower((string)$remainingFilters[$filterKey]) === mb_strtolower((string)$matchedAlias[$aliasKey])
                ) {
                    unset($remainingFilters[$filterKey]);
                }
            }
            $target = '/design-ideas/' . $matchedAlias['slug'];
            if ($remainingFilters) {
                $target .= '?' . http_build_query($remainingFilters);
            }
            header('Location: ' . $target, true, 301);
            exit;
        }
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => absoluteUrl('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Design Ideas', 'item' => absoluteUrl('/design-ideas')],
            ],
        ];
        render('public/design-ideas', [
            'title' => (string)($urlAlias['meta_title'] ?? 'Interior Design Ideas with Photos, Colours and Quotes | HomeInteriors360'),
            'metaDescription' => (string)($urlAlias['meta_description'] ?? 'Browse dynamic interior design ideas by room type, colour, city, style, layout, and dimensions. Save favourites and request a quotation.'),
            'canonicalUrl' => absoluteUrl((string)($urlAlias['canonical_url'] ?: '/design-ideas')),
            'metaRobots' => (string)($urlAlias['robots'] ?: 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1'),
            'ogImage' => !empty($urlAlias['image_url']) ? absoluteUrl((string)$urlAlias['image_url']) : null,
            'active' => 'design-ideas',
            'content' => $content,
            'globalAlias' => $urlAlias,
            'ideas' => SiteRepository::listDesignIdeas($filters),
            'aliases' => SiteRepository::listDesignIdeaAliases(),
            'sections' => SiteRepository::listDesignIdeaSections(),
            'filterOptions' => SiteRepository::designIdeaFilterOptions(),
            'activeFilters' => $filters,
            'structuredData' => [$breadcrumbSchema],
        ]);
        exit;
    }

    if (preg_match('#^/design-ideas/idea/([a-z0-9-]+)$#i', $path, $match)) {
        $idea = SiteRepository::getDesignIdea((string)$match[1]);
        if (!$idea) {
            http_response_code(404);
            echo 'Design idea not found';
            exit;
        }
        $urlAlias = SiteRepository::getUrlAliasByPath('/design-ideas/idea/' . (string)$idea['slug']);
        $image = (string)($idea['image_url'] ?? '');
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => absoluteUrl('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Design Ideas', 'item' => absoluteUrl('/design-ideas')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => (string)$idea['name'], 'item' => absoluteUrl('/design-ideas/idea/' . $idea['slug'])],
            ],
        ];
        $creativeWorkSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'ImageGallery',
            'name' => (string)$idea['name'],
            'description' => (string)($idea['meta_description'] ?: $idea['short_description']),
            'image' => array_values(array_map(static fn(string $url): string => absoluteUrl($url), array_filter(array_merge([(string)$idea['image_url']], (array)$idea['gallery_json'])))),
            'about' => [(string)$idea['type'], (string)$idea['style'], (string)$idea['color']],
            'contentLocation' => [
                '@type' => 'Place',
                'name' => trim((string)$idea['location'] . ', ' . (string)$idea['city'], ', '),
            ],
        ];
        render('public/design-idea-detail', [
            'title' => (string)($urlAlias['meta_title'] ?? ($idea['meta_title'] ?: $idea['name'] . ' | HomeInteriors360')),
            'metaDescription' => (string)($urlAlias['meta_description'] ?? ($idea['meta_description'] ?: $idea['short_description'])),
            'canonicalUrl' => absoluteUrl((string)($urlAlias['canonical_url'] ?: '/design-ideas/idea/' . $idea['slug'])),
            'metaRobots' => (string)($urlAlias['robots'] ?: 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1'),
            'ogImage' => !empty($urlAlias['image_url']) ? absoluteUrl((string)$urlAlias['image_url']) : ($image !== '' ? absoluteUrl($image) : absoluteUrl('/logo.png')),
            'active' => 'design-ideas',
            'content' => $content,
            'globalAlias' => $urlAlias,
            'idea' => $idea,
            'structuredData' => [$breadcrumbSchema, $creativeWorkSchema],
        ]);
        exit;
    }

    if (preg_match('#^/design-ideas/([a-z0-9-]+)$#i', $path, $match)) {
        $page = SiteRepository::designIdeaAliasPage((string)$match[1]);
        if (!$page) {
            http_response_code(404);
            echo 'Design idea page not found';
            exit;
        }
        $alias = $page['alias'];
        $urlAlias = SiteRepository::getUrlAliasByPath('/design-ideas/' . (string)$alias['slug']);
        $activeFilters = array_filter(array_merge($page['filters'] ?? [], [
            'q' => $_GET['q'] ?? null,
            'type' => $_GET['type'] ?? ($page['filters']['type'] ?? null),
            'color' => $_GET['color'] ?? ($page['filters']['color'] ?? null),
            'city' => $_GET['city'] ?? ($page['filters']['city'] ?? null),
            'state' => $_GET['state'] ?? ($page['filters']['state'] ?? null),
            'style' => $_GET['style'] ?? ($page['filters']['style'] ?? null),
            'layout' => $_GET['layout'] ?? ($page['filters']['layout'] ?? null),
        ]), static fn(mixed $value): bool => $value !== null && $value !== '');
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => absoluteUrl('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Design Ideas', 'item' => absoluteUrl('/design-ideas')],
                ['@type' => 'ListItem', 'position' => 3, 'name' => (string)$alias['title'], 'item' => absoluteUrl('/design-ideas/' . $alias['slug'])],
            ],
        ];
        render('public/design-ideas', [
            'title' => (string)($urlAlias['meta_title'] ?? ($alias['meta_title'] ?: $alias['title'] . ' | HomeInteriors360')),
            'metaDescription' => (string)($urlAlias['meta_description'] ?? ($alias['meta_description'] ?: $alias['subtitle'])),
            'canonicalUrl' => absoluteUrl((string)($urlAlias['canonical_url'] ?: '/design-ideas/' . $alias['slug'])),
            'metaRobots' => (string)($urlAlias['robots'] ?: 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1'),
            'ogImage' => !empty($urlAlias['image_url']) ? absoluteUrl((string)$urlAlias['image_url']) : (!empty($alias['hero_image']) ? absoluteUrl((string)$alias['hero_image']) : absoluteUrl('/logo.png')),
            'active' => 'design-ideas',
            'content' => $content,
            'globalAlias' => $urlAlias,
            'alias' => $alias,
            'ideas' => $page['ideas'],
            'aliases' => SiteRepository::listDesignIdeaAliases(),
            'sections' => SiteRepository::listDesignIdeaSections(),
            'filterOptions' => SiteRepository::designIdeaFilterOptions(),
            'activeFilters' => $activeFilters,
            'structuredData' => [$breadcrumbSchema],
        ]);
        exit;
    }

    if ($path === '/properties') {
        $filters = array_filter([
            'listing_for' => $_GET['listing_for'] ?? 'buy',
            'city' => $_GET['city'] ?? null,
            'locality' => $_GET['locality'] ?? null,
            'property_type' => $_GET['property_type'] ?? null,
            'project_status' => $_GET['project_status'] ?? null,
            'bhk_type' => $_GET['bhk_type'] ?? null,
            'price_min' => $_GET['price_min'] ?? null,
            'price_max' => $_GET['price_max'] ?? null,
            'q' => $_GET['q'] ?? null,
            'sort' => $_GET['sort'] ?? null,
        ], static fn(mixed $value): bool => $value !== null && $value !== '');
        $listingFor = ($filters['listing_for'] ?? 'buy') === 'rent' ? 'rent' : 'buy';
        render('public/property-listings', [
            'title' => $listingFor === 'rent' ? 'Flats and Homes for Rent | HomeInteriors360' : 'Flats and Residential Projects for Sale | HomeInteriors360',
            'metaDescription' => $listingFor === 'rent'
                ? 'Browse residential projects and homes for rent with photos, videos, layouts, monthly rent, amenities, and location details.'
                : 'Browse flats and residential projects for sale with photos, videos, floor plans, prices, amenities, inventory, and location details.',
            'active' => 'properties',
            'content' => $content,
            'filters' => $filters,
            'filterOptions' => SiteRepository::realEstateFilterOptions(),
            'projects' => SiteRepository::listRealEstateProjects($filters),
        ]);
        exit;
    }

    if (preg_match('#^/property/([a-z0-9-]+)$#i', $path, $match)) {
        $project = SiteRepository::getRealEstateProject((string)$match[1]);
        if (!$project) {
            http_response_code(404);
            echo 'Property project not found';
            exit;
        }
        $image = '';
        foreach (($project['media'] ?? []) as $item) {
            if (($item['media_type'] ?? 'image') === 'image') {
                $image = (string)$item['media_url'];
                break;
            }
        }
        $structuredProject = [
            '@context' => 'https://schema.org',
            '@type' => 'ApartmentComplex',
            'name' => (string)$project['project_name'],
            'description' => (string)($project['short_description'] ?: $project['description']),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => (string)($project['address'] ?? ''),
                'addressLocality' => (string)($project['locality'] ?? ''),
                'addressRegion' => (string)($project['state'] ?? ''),
                'postalCode' => (string)($project['pincode'] ?? ''),
                'addressCountry' => 'IN',
            ],
            'image' => array_values(array_map(static fn(array $item): string => absoluteUrl((string)$item['media_url']), array_filter($project['media'] ?? [], static fn(array $item): bool => ($item['media_type'] ?? 'image') === 'image'))),
        ];
        render('public/property-detail', [
            'title' => (string)($project['meta_title'] ?: $project['project_name'] . ' | Price, Floor Plans & Photos'),
            'metaDescription' => (string)($project['meta_description'] ?: $project['short_description']),
            'canonicalUrl' => absoluteUrl('/property/' . $project['slug']),
            'ogImage' => $image !== '' ? absoluteUrl($image) : absoluteUrl('/logo.png'),
            'active' => 'properties',
            'content' => $content,
            'project' => $project,
            'structuredData' => [$structuredProject],
        ]);
        exit;
    }

    if ($path === '/lead-cart') {
        render('public/lead-cart', [
            'title' => 'Lead Cart | HomeInteriors360',
            'metaDescription' => 'Review selected filtered lead packages and slab-based pricing.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'leads',
            'content' => $content,
        ]);
        exit;
    }

    if ($path === '/interior-designer-registration' || $path === '/register-interior-designer') {
        render('public/interior-designer-registration', [
            'title' => 'Register as Interior Designer | HomeInteriors360',
            'metaDescription' => 'Register your interior design firm, create a professional profile, upload portfolio work, and get 10 free homeowner leads on HomeInteriors360.',
            'active' => 'pricing',
            'content' => $content,
            'standardOptions' => SiteRepository::professionalStandardOptions(),
        ]);
        exit;
    }

    if ($path === '/lead-checkout') {
        render('public/lead-checkout', [
            'title' => 'Lead Checkout | HomeInteriors360',
            'metaDescription' => 'Create your buyer account, login, and complete Razorpay payment for lead packages.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'leads',
            'content' => $content,
            'buyer' => buyerUser(),
            'razorpayConfigured' => razorpayConfigured(),
        ]);
        exit;
    }

    if ($path === '/lead-dashboard') {
        $buyer = requireBuyer();
        render('public/lead-dashboard', [
            'title' => 'Lead Buyer Dashboard | HomeInteriors360',
            'metaDescription' => 'Download purchased lead packages securely.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'leads',
            'content' => $content,
            'buyer' => $buyer,
            'purchases' => SiteRepository::buyerPurchases((int)$buyer['id']),
        ]);
        exit;
    }

    if (preg_match('#^/lead-download/(\\d+)$#', $path, $match)) {
        $buyer = requireBuyer();
        $rows = SiteRepository::purchasedLeadRows((int)$buyer['id'], (int)$match[1]);
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="HomeInteriors360-Leads-' . (int)$match[1] . '.xls"');
        echo "<table><tr><th>Name</th><th>Phone</th><th>City</th><th>Society / Area</th><th>Budget</th><th>Requirement</th><th>Source</th><th>Date</th></tr>";
        foreach ($rows as $row) {
            echo '<tr>';
            foreach (['name', 'phone', 'city', 'society_area', 'budget', 'requirement', 'source', 'created_at'] as $key) {
                echo '<td>' . htmlspecialchars((string)($row[$key] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }

    if (preg_match('#^/portfolio/([a-z0-9-]+)$#i', $path, $match)) {
        $project = SiteRepository::getProjectBySlug((string)$match[1]);
        if (!$project) {
            http_response_code(404);
            echo 'Portfolio project not found';
            exit;
        }
        render('public/portfolio-detail', [
            'title' => $project['project_name'] . ' | ' . (string)SiteRepository::content('seo.portfolio.title_suffix', 'HomeInteriors360'),
            'metaDescription' => sprintf(
                '%s by %s in %s. See project cost, timeline, materials, images, and testimonial details.',
                (string)$project['project_name'],
                (string)($project['pro_name'] ?? 'this professional'),
                (string)($project['location'] ?? 'Gurgaon')
            ),
            'active' => 'directory',
            'content' => $content,
            'project' => $project,
            'relatedProjects' => SiteRepository::listOtherProjectsByPro((int)$project['pro_id'], (string)$project['slug']),
        ]);
        exit;
    }

    // Designer portal pages
    if ($path === '/designer/login') {
        render('designer/login', [
            'title' => 'Designer Login | HomeInteriors360',
            'metaDescription' => 'Secure interior designer login for leads, quotations, and proposal generator.',
            'metaRobots' => 'noindex,nofollow',
            'content' => $content,
        ]);
        exit;
    }

    if ($path === '/designer') {
        $user = Auth::requireDesigner();
        $designerBuyer = SiteRepository::designerBuyerForUser($user);
        if ($designerBuyer) {
            setBuyerSession($designerBuyer);
        }
        render('designer/dashboard', [
            'title' => 'Designer Workspace | HomeInteriors360',
            'metaDescription' => 'Designer-only workspace for assigned leads, quotations, and proposal generator.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'stats' => SiteRepository::designerDashboardStats((int)$user['pro_id']),
            'subscription' => SiteRepository::designerSubscriptionStatus($user),
            'profileChecklist' => SiteRepository::designerProfileChecklist((int)$user['pro_id']),
            'buyer' => $designerBuyer,
            'freeLeadEligible' => $designerBuyer ? SiteRepository::buyerFirstTimeLeadOfferEligible((int)$designerBuyer['id']) : false,
        ]);
        exit;
    }

    if ($path === '/designer/portfolio-onboarding') {
        $user = Auth::requireDesigner();
        render('designer/portfolio-onboarding', [
            'title' => 'Upload Portfolio | HomeInteriors360',
            'metaDescription' => 'Upload portfolio work for your HomeInteriors360 professional profile.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'portfolios' => SiteRepository::listPortfolioForAdmin((int)$user['pro_id']),
            'standardOptions' => SiteRepository::professionalStandardOptions(),
        ]);
        exit;
    }

    if ($path === '/designer/leads') {
        $user = Auth::requireDesigner();
        render('designer/leads', [
            'title' => 'My Leads | HomeInteriors360',
            'metaDescription' => 'Interior designer assigned leads.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'leads' => SiteRepository::listLeads(['designer_id' => (int)$user['pro_id']]),
        ]);
        exit;
    }

    if ($path === '/designer/quotations') {
        $user = Auth::requireDesigner();
        $filters = $_GET;
        $filters['designer_id'] = (int)$user['pro_id'];
        $designerQuotations = SiteRepository::listQuotations($filters);
        render('admin/quotations', [
            'title' => 'My Quotations | HomeInteriors360',
            'metaDescription' => 'Designer quotation builder for assigned leads and proposals.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'stats' => SiteRepository::quotationStatsFromRows($designerQuotations),
            'quotations' => $designerQuotations,
            'packages' => SiteRepository::quotationPackages(true),
            'professionals' => SiteRepository::professionalOptions(),
            'portalBase' => '/designer',
            'isDesignerPortal' => true,
        ]);
        exit;
    }

    if ($path === '/designer/quotations/create' || preg_match('#^/designer/quotations/(\\d+)/edit$#', $path, $designerQuoteEditMatch)) {
        $user = Auth::requireDesigner();
        $quote = null;
        if (!empty($designerQuoteEditMatch[1])) {
            $quoteId = (int)$designerQuoteEditMatch[1];
            if (!SiteRepository::userCanAccessQuotation($user, $quoteId)) {
                http_response_code(404);
                echo 'Quotation not found';
                exit;
            }
            $quote = SiteRepository::getQuotation($quoteId);
        } elseif (!SiteRepository::designerCanCreateQuotation($user)) {
            render('designer/dashboard', [
                'title' => 'Designer Workspace | HomeInteriors360',
                'metaDescription' => 'Designer-only workspace for assigned leads, quotations, and proposal generator.',
                'metaRobots' => 'noindex,nofollow',
                'active' => 'admin',
                'content' => $content,
                'stats' => SiteRepository::designerDashboardStats((int)$user['pro_id']),
                'subscription' => SiteRepository::designerSubscriptionStatus($user),
                'profileChecklist' => SiteRepository::designerProfileChecklist((int)$user['pro_id']),
                'createBlocked' => true,
            ]);
            exit;
        } elseif (!empty($_GET['lead_id'])) {
            $leadRows = SiteRepository::listLeads(['designer_id' => (int)$user['pro_id']]);
            $allowedLeadIds = array_map(static fn(array $lead): int => (int)$lead['id'], $leadRows);
            if (!in_array((int)$_GET['lead_id'], $allowedLeadIds, true)) {
                http_response_code(404);
                echo 'Lead not found';
                exit;
            }
            $quote = SiteRepository::prefillQuotationFromLead((int)$_GET['lead_id']);
        }
        $quote = is_array($quote) ? $quote : [];
        $quote['designer_id'] = (int)$user['pro_id'];
        render('admin/quotation-form', [
            'title' => !empty($quote['id']) ? 'Edit Quotation' : 'Create Quotation',
            'metaDescription' => 'Create or edit itemised interior quotation proposals.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'quote' => $quote,
            'packages' => SiteRepository::quotationPackages(true),
            'templates' => SiteRepository::proposalTemplates(true),
            'professionals' => array_values(array_filter(SiteRepository::professionalOptions(), static fn(array $pro): bool => (int)$pro['id'] === (int)$user['pro_id'])),
            'rateCards' => SiteRepository::quotationRateCards(['designer_id' => (int)$user['pro_id']]),
            'settings' => SiteRepository::quotationSettings(),
            'portalBase' => '/designer',
            'isDesignerPortal' => true,
        ]);
        exit;
    }

    if (preg_match('#^/designer/quotations/(\\d+)$#', $path, $designerQuoteMatch)) {
        $user = Auth::requireDesigner();
        $quoteId = (int)$designerQuoteMatch[1];
        if (!SiteRepository::userCanAccessQuotation($user, $quoteId)) {
            http_response_code(404);
            echo 'Quotation not found';
            exit;
        }
        $quote = SiteRepository::getQuotation($quoteId);
        render('admin/quotation-detail', [
            'title' => $quote['quote_number'] . ' | Designer Quotation',
            'metaDescription' => 'Designer quotation summary, proposal preview, activity, and sharing actions.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'quote' => $quote,
            'settings' => SiteRepository::quotationSettings(),
            'portalBase' => '/designer',
            'isDesignerPortal' => true,
        ]);
        exit;
    }

    // Admin pages
    if ($path === '/admin/login') {
        render('admin/login', [
            'title' => (string)SiteRepository::content('admin.login.title', 'Admin Login'),
            'metaDescription' => 'Secure admin login for managing professionals, portfolios, leads, and site content.',
            'metaRobots' => 'noindex,nofollow',
            'content' => $content,
        ]);
        exit;
    }

    if ($path === '/admin') {
        Auth::requireAdmin();
        render('admin/dashboard', [
            'title' => (string)SiteRepository::content('admin.title', 'Admin Dashboard'),
            'metaDescription' => 'Admin dashboard for HomeInteriors360 site management, leads, professionals, and content.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'counts' => SiteRepository::adminCounts(),
        ]);
        exit;
    }

    if ($path === '/admin/content') {
        Auth::requireAdmin();
        render('admin/content', [
            'title' => (string)SiteRepository::content('admin.content.title', 'Content Manager'),
            'metaDescription' => 'Update homepage content, logos, SEO fields, and reusable site copy from the admin panel.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'items' => SiteRepository::contentList(),
        ]);
        exit;
    }

    if ($path === '/admin/leads') {
        Auth::requireAdmin();
        render('admin/leads', [
            'title' => (string)SiteRepository::content('admin.leads.title', 'Lead Tracker'),
            'metaDescription' => 'Review and update incoming leads with status tracking for the HomeInteriors360 sales team.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'leads' => SiteRepository::listLeads(),
        ]);
        exit;
    }

    if ($path === '/admin/quotations') {
        Auth::requireAdmin();
        render('admin/quotations', [
            'title' => 'Quotation Builder',
            'metaDescription' => 'Manage quotation builder proposals, status, revisions, and client proposal links.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'stats' => SiteRepository::quotationDashboardStats(),
            'quotations' => SiteRepository::listQuotations($_GET),
            'packages' => SiteRepository::quotationPackages(true),
            'professionals' => SiteRepository::professionalOptions(),
        ]);
        exit;
    }

    if ($path === '/admin/quotations/create' || preg_match('#^/admin/quotations/(\\d+)/edit$#', $path, $quoteEditMatch)) {
        Auth::requireAdmin();
        $quote = null;
        if (!empty($quoteEditMatch[1])) {
            $quote = SiteRepository::getQuotation((int)$quoteEditMatch[1]);
            if (!$quote) {
                http_response_code(404);
                echo 'Quotation not found';
                exit;
            }
        } elseif (!empty($_GET['lead_id'])) {
            $quote = SiteRepository::prefillQuotationFromLead((int)$_GET['lead_id']);
        }
        render('admin/quotation-form', [
            'title' => $quote && !empty($quote['id']) ? 'Edit Quotation' : 'Create Quotation',
            'metaDescription' => 'Create or edit itemised interior quotation proposals.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'quote' => $quote,
            'packages' => SiteRepository::quotationPackages(true),
            'templates' => SiteRepository::proposalTemplates(true),
            'professionals' => SiteRepository::professionalOptions(),
            'rateCards' => SiteRepository::quotationRateCards([]),
            'settings' => SiteRepository::quotationSettings(),
        ]);
        exit;
    }

    if (preg_match('#^/admin/quotations/(\\d+)$#', $path, $quoteMatch)) {
        Auth::requireAdmin();
        $quote = SiteRepository::getQuotation((int)$quoteMatch[1]);
        if (!$quote) {
            http_response_code(404);
            echo 'Quotation not found';
            exit;
        }
        render('admin/quotation-detail', [
            'title' => $quote['quote_number'] . ' | Quotation Builder',
            'metaDescription' => 'Quotation summary, proposal preview, activity, and sharing actions.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'quote' => $quote,
            'settings' => SiteRepository::quotationSettings(),
        ]);
        exit;
    }

    if ($path === '/admin/quotation-rate-card') {
        Auth::requireAdmin();
        render('admin/quotation-rate-card', [
            'title' => 'Quotation Rate Card',
            'metaDescription' => 'Manage city, package, material, and category-wise quotation rates.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'rateCards' => SiteRepository::quotationRateCards($_GET),
            'packages' => SiteRepository::quotationPackages(),
            'professionals' => SiteRepository::professionalOptions(),
        ]);
        exit;
    }

    if ($path === '/admin/quotation-packages') {
        Auth::requireAdmin();
        render('admin/quotation-packages', [
            'title' => 'Quotation Package Master',
            'metaDescription' => 'Manage quotation packages, design support, warranty, timeline, and default margins.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'packages' => SiteRepository::quotationPackages(),
        ]);
        exit;
    }

    if ($path === '/admin/proposal-templates') {
        Auth::requireAdmin();
        render('admin/proposal-templates', [
            'title' => 'Proposal Templates',
            'metaDescription' => 'Manage proposal cover copy, inclusions, exclusions, terms, warranty, and payment schedule.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'templates' => SiteRepository::proposalTemplates(),
        ]);
        exit;
    }

    if ($path === '/admin/quotation-settings') {
        Auth::requireAdmin();
        render('admin/quotation-settings', [
            'title' => 'Quotation Settings',
            'metaDescription' => 'Manage GST, validity, fees, commission, payment schedule, WhatsApp message, and support details.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'settings' => SiteRepository::quotationSettings(),
        ]);
        exit;
    }

    if ($path === '/admin/pros') {
        Auth::requireAdmin();
        render('admin/pros', [
            'title' => (string)SiteRepository::content('admin.pros.title', 'Pro Verification'),
            'metaDescription' => 'Verify professionals, manage premium status, and control public listing visibility.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'pros' => SiteRepository::listPros([]),
        ]);
        exit;
    }

    if ($path === '/admin/professionals') {
        Auth::requireAdmin();
        render('admin/professionals', [
            'title' => 'Professionals Manager',
            'metaDescription' => 'Create and manage professional profiles with images, filters, pricing, and portfolio linkage.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'professionals' => SiteRepository::listProfessionalsForAdmin(),
            'standardOptions' => SiteRepository::professionalStandardOptions(),
        ]);
        exit;
    }

    if ($path === '/admin/designer-accounts') {
        Auth::requireAdmin();
        render('admin/designer-accounts', [
            'title' => 'Designer Login Accounts',
            'metaDescription' => 'Create and manage separate quotation builder login accounts for interior designers.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'accounts' => SiteRepository::listDesignerAccounts(),
            'professionals' => SiteRepository::professionalOptions(),
        ]);
        exit;
    }

    if ($path === '/admin/portfolios') {
        Auth::requireAdmin();
        render('admin/portfolios', [
            'title' => 'Portfolio Manager',
            'metaDescription' => 'Create and manage portfolio projects, images, testimonials, and project metadata.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'portfolios' => SiteRepository::listPortfolioForAdmin(),
            'professionals' => SiteRepository::professionalOptions(),
            'standardOptions' => SiteRepository::professionalStandardOptions(),
        ]);
        exit;
    }

    if ($path === '/admin/lead-coupons') {
        Auth::requireAdmin();
        render('admin/lead-coupons', [
            'title' => 'Lead Coupon Backend',
            'metaDescription' => 'Create and manage lead marketplace coupons by slab, discount, visibility, and dates.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'coupons' => SiteRepository::listLeadCoupons(),
        ]);
        exit;
    }

    if ($path === '/admin/property-projects') {
        Auth::requireAdmin();
        render('admin/property-projects', [
            'title' => 'Property Project Manager',
            'metaDescription' => 'Manage real estate projects, units, pricing, media, floor plans, amenities, and SEO.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'projects' => SiteRepository::listRealEstateProjects([], true),
        ]);
        exit;
    }

    if ($path === '/admin/property-enquiries') {
        Auth::requireAdmin();
        render('admin/property-enquiries', [
            'title' => 'Property Enquiries',
            'metaDescription' => 'Manage buyer and tenant enquiries from real estate project pages.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'enquiries' => SiteRepository::listPropertyEnquiries(),
        ]);
        exit;
    }

    if ($path === '/admin/url-aliases') {
        Auth::requireAdmin();
        render('admin/url-aliases', [
            'title' => 'Global URL Alias Manager',
            'metaDescription' => 'Manage SEO metadata, H1, rich content, image and index settings for every public URL.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'aliases' => SiteRepository::listUrlAliases(),
        ]);
        exit;
    }

    if ($path === '/admin/design-ideas') {
        Auth::requireAdmin();
        render('admin/design-ideas', [
            'title' => 'Design Idea Backend',
            'metaDescription' => 'Create and manage dynamic design idea cards, media, filters, dimensions, and SEO.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'ideas' => SiteRepository::listDesignIdeas([], true),
        ]);
        exit;
    }

    if ($path === '/admin/design-idea-aliases') {
        Auth::requireAdmin();
        redirectTo('/admin/url-aliases');
    }

    if ($path === '/admin/design-idea-sections') {
        Auth::requireAdmin();
        render('admin/design-idea-sections', [
            'title' => 'Design Idea Section Backend',
            'metaDescription' => 'Manage dynamic blocks and item grids shown on design idea pages.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'sections' => SiteRepository::listDesignIdeaSections(true),
        ]);
        exit;
    }

    if ($path === '/admin/design-idea-leads') {
        Auth::requireAdmin();
        render('admin/design-idea-leads', [
            'title' => 'Design Idea Quote Leads',
            'metaDescription' => 'Manage quote requests captured from design idea pages.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'leads' => SiteRepository::listDesignIdeaLeads(),
        ]);
        exit;
    }

    echo '404 Not Found';
} catch (Throwable $e) {
    if (str_starts_with($path, '/api/')) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    http_response_code(500);
    echo 'Server Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
