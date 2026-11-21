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
$host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
if ($method === 'GET' && $host === 'www.homeinteriors360.com') {
    $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
    header('Location: https://homeinteriors360.com' . $requestUri, true, 301);
    exit;
}
if ($method === 'GET' && $path !== '/' && str_ends_with($path, '/') && !str_starts_with($path, '/api/')) {
    $query = (string)(parse_url((string)($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_QUERY) ?? '');
    $target = rtrim($path, '/');
    header('Location: ' . $target . ($query !== '' ? '?' . $query : ''), true, 301);
    exit;
}
if ($method === 'POST' && !empty($_POST['_method'])) {
    $method = strtoupper((string)$_POST['_method']);
}
// Cache successful public HTML briefly so crawler bursts do not exhaust the
// shared-hosting PHP worker pool. Error responses are explicitly made private
// in the catch block below and are never cacheable.
header('Cache-Control: public, max-age=300, s-maxage=300, stale-while-revalidate=60');
$content = SiteRepository::allContent();
$seoCityPages = [
    'delhi-ncr' => ['city' => 'Delhi NCR', 'nearby' => 'Delhi, Gurugram, Noida, Faridabad, and Ghaziabad'],
    'gurgaon' => ['city' => 'Gurugram', 'nearby' => 'Golf Course Road, Sohna Road, Dwarka Expressway, and New Gurgaon'],
    'noida' => ['city' => 'Noida', 'nearby' => 'Noida, Greater Noida, and Noida Extension'],
    'mumbai' => ['city' => 'Mumbai', 'nearby' => 'Mumbai, Navi Mumbai, and Thane'],
    'pune' => ['city' => 'Pune', 'nearby' => 'Kharadi, Wakad, Baner, Hinjewadi, and nearby Pune markets'],
    'chennai' => ['city' => 'Chennai', 'nearby' => 'OMR, Anna Nagar, Velachery, Adyar, Porur, and nearby Chennai markets'],
    'hyderabad' => ['city' => 'Hyderabad', 'nearby' => 'Gachibowli, Hitech City, Kondapur, and Attapur'],
    'bangalore' => ['city' => 'Bangalore', 'nearby' => 'Whitefield, Sarjapur Road, Electronic City, and North Bangalore'],
];
$seoLeadIntentCopy = [
    '/interior-design-leads' => [
        'chips' => ['interior design leads', 'architect leads', 'turnkey interior leads', 'residential project enquiries'],
        'sections' => [
            ['title' => 'Interior Design Leads for Residential Project Teams', 'body' => ['Use this page when you want the broader category of interior project enquiries across full home interiors, modular kitchens, wardrobes, painting, renovation, and turnkey execution.', 'HomeInteriors360 captures homeowner intent with city, locality, budget, work type, and project context so architects, interior designers, and contractors can identify relevant conversations faster.']],
            ['title' => 'Lead Types Covered', 'body' => [['Full home interior requirements', 'Modular kitchen and wardrobe enquiries', 'Renovation, painting, ceiling, lighting and furniture work', 'Budget-tagged homeowner enquiries from Delhi NCR and supported cities']]],
        ],
    ],
    '/interior-designer-leads' => [
        'chips' => ['interior designer leads', 'design studio enquiries', 'homeowner consultation leads', 'portfolio-led sales'],
        'sections' => [
            ['title' => 'Interior Designer Leads for Design Studios', 'body' => ['This page focuses on professional interior designers and studios that sell design consultation, design-and-build packages, modular work, and full home interior projects.', 'The intent is different from generic contractor leads: buyers can use portfolio strength, response time, city fit, and scope fit to decide which homeowner enquiries to pursue.']],
            ['title' => 'Best Fit for Designers', 'body' => [['Independent interior designers', 'Design studios and boutique firms', 'Modular kitchen and wardrobe brands', 'Turnkey residential interior teams']]],
        ],
    ],
    '/buy-interior-design-leads' => [
        'chips' => ['buy interior design leads', 'lead packages', 'Razorpay checkout', 'buyer dashboard'],
        'sections' => [
            ['title' => 'Buy Interior Design Leads Online', 'body' => ['This is the transaction-focused page for professionals ready to select filters, add lead packages to cart, apply eligible offers, pay securely, and access purchased leads from the buyer dashboard.', 'Use the marketplace when you already know your preferred city, society, work type, budget band, and number of leads to buy.']],
            ['title' => 'Purchase Flow', 'body' => [['Choose city, locality, budget and requirement filters', 'Review unique lead count and pricing', 'Complete secure Razorpay checkout', 'Access purchased leads from your dashboard']]],
        ],
    ],
    '/interior-leads-provider-india' => [
        'chips' => ['interior leads provider India', 'lead marketplace India', 'verified interior enquiries', 'city-wise leads'],
        'sections' => [
            ['title' => 'Interior Leads Provider for Indian Interior Markets', 'body' => ['This page explains HomeInteriors360 as a lead provider rather than a single-city landing page. It covers how lead demand varies by city, budget, property type, and service category.', 'The strongest current focus is Delhi NCR, with city pages for Gurgaon, Delhi, Noida and other supported markets where data is available.']],
            ['title' => 'How Leads Are Structured', 'body' => [['City and locality context', 'Homeowner requirement type', 'Budget range and timeline', 'Digital delivery after verified checkout']]],
        ],
    ],
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
        'meta' => 'Buy interior design leads online from HomeInteriors360. City-wise and work-type lead packages with quality-based pricing.',
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
    $localAreas = match ($slug) {
        'gurgaon' => 'Golf Course Road, Sohna Road, SPR, New Gurgaon, Dwarka Expressway, Sector 57, Sector 67, DLF phases and premium gated societies',
        'noida' => 'Noida sectors, Noida Extension, Greater Noida West, Expressway micro-markets and new apartment clusters',
        'delhi-ncr' => 'Delhi, Gurgaon, Noida, Greater Noida, Faridabad and Ghaziabad residential pockets',
        'mumbai' => 'Mumbai, Thane, Navi Mumbai and compact apartment renovation markets',
        'pune' => 'Kharadi, Wakad, Baner, Hinjewadi, Viman Nagar and family apartment clusters',
        'hyderabad' => 'Gachibowli, Hitech City, Kondapur, Kokapet, Attapur and villa/apartment corridors',
        'bangalore' => 'Whitefield, Sarjapur Road, Electronic City, North Bangalore and premium gated communities',
        default => $cityData['nearby'],
    };
    $seoLeadPages['/interior-designer-leads-' . $slug] = [
        'keyword' => 'interior designer leads ' . $cityData['city'],
        'title' => 'Interior Designer Leads in ' . $cityData['city'],
        'subtitle' => 'Buy location-specific interior designer leads from homeowners in ' . $cityData['nearby'] . '. Filter by budget, work type, locality, and recency.',
        'meta' => 'Buy verified interior designer leads in ' . $cityData['city'] . '. Homeowner enquiries for full home interiors, kitchens, wardrobes, and renovation projects.',
        'city' => $cityData['city'],
        'local_areas' => $localAreas,
    ];
    $seoLeadPages['/interior-design-lead-generation-' . $slug] = [
        'keyword' => 'interior design lead generation ' . $cityData['city'],
        'title' => 'Interior Design Lead Generation in ' . $cityData['city'],
        'subtitle' => 'Grow your interior business in ' . $cityData['city'] . ' with verified homeowner enquiries, digital lead delivery, and transparent lead package pricing.',
        'meta' => 'Interior design lead generation in ' . $cityData['city'] . ' for designers, architects, and contractors. Buy filtered residential interior leads.',
        'city' => $cityData['city'],
        'local_areas' => $localAreas,
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
        header('Cache-Control: public, max-age=300, must-revalidate');
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /admin\n";
        echo "Disallow: /api/\n";
        echo "Disallow: /lead-cart\n";
        echo "Disallow: /lead-checkout\n";
        echo "Disallow: /lead-dashboard\n";
        echo "Disallow: /lead-download/\n";
        echo "Disallow: /ai-room-visualizer\n";
        echo "Sitemap: " . absoluteUrl('/sitemap.xml') . "\n";
        echo "LLMs: " . absoluteUrl('/llms.txt') . "\n";
        exit;
    }

    if ($path === '/lead-alert-sw.js') {
        header('Content-Type: application/javascript; charset=utf-8');
        echo "self.addEventListener('push', event => { const data = event.data ? event.data.json() : {}; event.waitUntil(self.registration.showNotification(data.title || 'New HomeInteriors360 lead', { body: data.body || 'A matching project opportunity is available.', data: data })); });\n";
        echo "self.addEventListener('notificationclick', event => { event.notification.close(); const url = event.notification.data && event.notification.data.url ? event.notification.data.url : '/lead-marketplace'; event.waitUntil(clients.openWindow(url)); });\n";
        exit;
    }

    if ($path === '/sitemap.xml') {
        header('Cache-Control: public, max-age=300, must-revalidate');
        header('Content-Type: application/xml; charset=utf-8');
        echo SitemapService::xml($seoLeadPages, $seoCityPages);
        exit;

        /* Legacy inline sitemap generator retained below temporarily for source-history compatibility. */
        $formatSitemapLastmod = static function (mixed $value = null): string {
            $raw = trim((string)($value ?? ''));
            $timestamp = $raw !== '' ? strtotime($raw) : time();
            if ($timestamp === false) {
                $timestamp = time();
            }
            return gmdate('Y-m-d\TH:i:s', $timestamp) . '.000Z';
        };
        $today = $formatSitemapLastmod();
        $urls = [
            ['loc' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => '/home-interior-hire-a-designer', 'priority' => '0.96', 'changefreq' => 'weekly'],
            ['loc' => '/hire-a-designer', 'priority' => '0.82', 'changefreq' => 'weekly'],
            ['loc' => '/lead-marketplace', 'priority' => '0.95', 'changefreq' => 'daily'],
            ['loc' => '/for-professionals', 'priority' => '0.92', 'changefreq' => 'weekly'],
            ['loc' => '/delhi-ncr-founding-partners', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => '/pricing-details', 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => '/pricing', 'priority' => '0.75', 'changefreq' => 'weekly'],
            ['loc' => '/interior-designer-registration', 'priority' => '0.88', 'changefreq' => 'weekly'],
            ['loc' => '/register-interior-designer', 'priority' => '0.72', 'changefreq' => 'weekly'],
            ['loc' => '/professionals', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => '/properties', 'priority' => '0.76', 'changefreq' => 'weekly'],
            ['loc' => '/cost-calculator', 'priority' => '0.72', 'changefreq' => 'monthly'],
            ['loc' => '/full-home-cost-calculator', 'priority' => '0.72', 'changefreq' => 'monthly'],
            ['loc' => '/kitchen-cost-calculator', 'priority' => '0.72', 'changefreq' => 'monthly'],
            ['loc' => '/modular-kitchen-cost-calculator', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/wardrobe-cost-calculator', 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => '/1-bhk-cost-calculator', 'priority' => '0.68', 'changefreq' => 'monthly'],
            ['loc' => '/2-bhk-cost-calculator', 'priority' => '0.68', 'changefreq' => 'monthly'],
            ['loc' => '/3-bhk-cost-calculator', 'priority' => '0.68', 'changefreq' => 'monthly'],
            ['loc' => '/4-bhk-cost-calculator', 'priority' => '0.66', 'changefreq' => 'monthly'],
            ['loc' => '/living-room-cost-calculator', 'priority' => '0.64', 'changefreq' => 'monthly'],
            ['loc' => '/bathroom-cost-calculator', 'priority' => '0.64', 'changefreq' => 'monthly'],
            ['loc' => '/pooja-room-cost-calculator', 'priority' => '0.64', 'changefreq' => 'monthly'],
            ['loc' => '/false-ceiling-cost-calculator', 'priority' => '0.64', 'changefreq' => 'monthly'],
            ['loc' => '/painting-cost-calculator', 'priority' => '0.64', 'changefreq' => 'monthly'],
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
        try {
            foreach (Database::query("SELECT slug, updated_at FROM real_estate_projects WHERE is_active=1 ORDER BY updated_at DESC LIMIT 500") as $property) {
                $urls[] = ['loc' => '/property/' . $property['slug'], 'priority' => '0.64', 'changefreq' => 'weekly', 'lastmod' => substr((string)($property['updated_at'] ?? $today), 0, 10)];
            }
        } catch (Throwable) {
            // The real estate module may not exist before its migration has run.
        }
        $urls[] = ['loc' => '/design-ideas', 'priority' => '0.9', 'changefreq' => 'weekly'];
        foreach (Database::query('SELECT slug, updated_at FROM design_idea_aliases WHERE is_active=1 ORDER BY updated_at DESC LIMIT 200') as $alias) {
            $urls[] = ['loc' => '/design-ideas/' . $alias['slug'], 'priority' => '0.86', 'changefreq' => 'weekly', 'lastmod' => substr((string)($alias['updated_at'] ?? $today), 0, 10)];
        }
        foreach (Database::query('SELECT slug, updated_at FROM design_ideas WHERE is_active=1 ORDER BY updated_at DESC LIMIT 500') as $idea) {
            $urls[] = ['loc' => '/design-ideas/idea/' . $idea['slug'], 'priority' => '0.72', 'changefreq' => 'monthly', 'lastmod' => substr((string)($idea['updated_at'] ?? $today), 0, 10)];
        }
        $urls[] = ['loc' => '/articles', 'priority' => '0.86', 'changefreq' => 'weekly'];
        try {
            foreach (Database::query('SELECT slug, updated_at FROM articles WHERE is_active=1 ORDER BY updated_at DESC LIMIT 1000') as $article) {
                $urls[] = ['loc' => '/articles/' . $article['slug'], 'priority' => '0.78', 'changefreq' => 'daily', 'lastmod' => substr((string)($article['updated_at'] ?? $today), 0, 10)];
            }
        } catch (Throwable) {
            // Article CMS may not exist before its migration has run.
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
            echo "    <lastmod>" . htmlspecialchars($formatSitemapLastmod($url['lastmod'] ?? $today), ENT_XML1, 'UTF-8') . "</lastmod>\n";
            echo "  </url>\n";
        }
        echo "</urlset>\n";
        exit;
    }

    if ($path === '/auth/google/designer' && $method === 'GET') {
        try {
            Auth::start();
            $state = bin2hex(random_bytes(24));
            $_SESSION['google_designer_oauth_state'] = $state;
            $_SESSION['google_designer_oauth_return_source'] = trim((string)($_GET['source'] ?? ''));
            header('Location: ' . GoogleOAuthService::authorizationUrl($state), true, 302);
            exit;
        } catch (Throwable $e) {
            redirectTo('/interior-designer-registration?google_error=' . rawurlencode($e->getMessage()));
        }
    }

    if ($path === '/auth/google/designer/callback' && $method === 'GET') {
        Auth::start();
        $state = (string)($_GET['state'] ?? '');
        $expectedState = (string)($_SESSION['google_designer_oauth_state'] ?? '');
        unset($_SESSION['google_designer_oauth_state']);
        if ($state === '' || $expectedState === '' || !hash_equals($expectedState, $state)) {
            redirectTo('/interior-designer-registration?google_error=' . rawurlencode('Google sign-up verification failed. Please try again.'));
        }
        if (!empty($_GET['error'])) {
            redirectTo('/interior-designer-registration?google_error=' . rawurlencode('Google sign-up was cancelled.'));
        }
        try {
            $identity = GoogleOAuthService::identityFromAuthorizationCode(trim((string)($_GET['code'] ?? '')));
            $existing = Database::one('SELECT id, username, email, role, pro_id, google_subject FROM users WHERE google_subject=? OR email=? ORDER BY id DESC LIMIT 1', [$identity['sub'], $identity['email']]);
            if ($existing) {
                if (($existing['role'] ?? '') !== 'designer' || empty($existing['pro_id'])) {
                    throw new RuntimeException('This Google email is already used by a different HomeInteriors360 account.');
                }
                if (empty($existing['google_subject'])) {
                    Database::exec('UPDATE users SET google_subject=?, updated_at=NOW() WHERE id=?', [$identity['sub'], (int)$existing['id']]);
                    $existing['google_subject'] = $identity['sub'];
                }
                Auth::loginUser($existing);
                $designerBuyer = SiteRepository::designerBuyerForUser($existing);
                if ($designerBuyer) {
                    setBuyerSession($designerBuyer);
                }
                redirectTo('/designer');
            }
            $_SESSION['google_designer_identity'] = $identity;
            $source = trim((string)($_SESSION['google_designer_oauth_return_source'] ?? ''));
            unset($_SESSION['google_designer_oauth_return_source']);
            redirectTo('/interior-designer-registration?google=connected' . ($source !== '' ? '&source=' . rawurlencode($source) : ''));
        } catch (Throwable $e) {
            redirectTo('/interior-designer-registration?google_error=' . rawurlencode($e->getMessage()));
        }
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

    if ($path === '/api/designer-contact-queries' && $method === 'POST') {
        try {
            jsonResponse(['success' => true, 'id' => SiteRepository::createDesignerContactQuery(requestJson())]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/interior-designer-registration' && $method === 'POST') {
        try {
            $body = requestData();
            Auth::start();
            if (!empty($body['google_signup'])) {
                $googleIdentity = $_SESSION['google_designer_identity'] ?? null;
                if (!is_array($googleIdentity) || empty($googleIdentity['sub']) || empty($googleIdentity['email'])) {
                    throw new InvalidArgumentException('Please continue with Google again before saving your designer registration.');
                }
                $body['_google_identity'] = $googleIdentity;
                $body['email'] = (string)$googleIdentity['email'];
            }
            $step = (int)($body['registration_step'] ?? 0);
            $isFinal = !empty($body['final_step']);
            $profilePic = saveUploadedFile($_FILES['profile_pic'] ?? [], 'professionals', null);
            $coverPhoto = saveUploadedFile($_FILES['cover_photo'] ?? [], 'professionals', null);
            if ($profilePic) {
                $body['profile_pic'] = $profilePic;
            }
            if ($coverPhoto) {
                $body['cover_photo'] = $coverPhoto;
            }
            if (!empty($body['pro_id'])) {
                $result = SiteRepository::updateInteriorDesignerRegistrationStep((int)$body['pro_id'], $body);
            } else {
                $result = SiteRepository::registerInteriorDesigner($body);
            }
            if ((string)($body['source'] ?? '') === 'founding_partner') {
                LeadMatchingService::captureFoundingPartner($body, (int)($result['pro_id'] ?? 0), !empty($result['user_id']) ? (int)$result['user_id'] : null);
            }
            if ($isFinal && !empty($result['user'])) {
                Auth::loginUser($result['user']);
            }
            if ($isFinal && !empty($result['buyer'])) {
                setBuyerSession($result['buyer']);
            }
            jsonResponse([
                'success' => true,
                'pro_id' => $result['pro_id'],
                'user_id' => $result['user_id'] ?? null,
                'step' => $step,
                'redirect_url' => $isFinal ? '/designer/portfolio-onboarding' : null,
            ]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/designer-membership-plans' && $method === 'GET') {
        jsonResponse(['plans' => SiteRepository::designerMembershipPlans()]);
    }

    if ($path === '/api/designer/membership' && $method === 'GET') {
        jsonResponse(['membership' => SiteRepository::designerMembershipStatus(Auth::requireDesigner())]);
    }

    if ($path === '/api/designer/membership/checkout' && $method === 'POST') {
        $user = Auth::requireDesigner();
        try {
            jsonResponse(['success' => true] + SiteRepository::createDesignerMembershipSubscription($user, requestJson()));
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    if ($path === '/api/designer/membership/verify' && $method === 'POST') {
        $user = Auth::requireDesigner();
        $body = requestJson();
        try {
            SiteRepository::activateDesignerMembershipSubscription($user, (string)($body['razorpay_subscription_id'] ?? ''), (string)($body['razorpay_payment_id'] ?? ''), (string)($body['razorpay_signature'] ?? ''));
            jsonResponse(['success' => true, 'redirect_url' => '/designer']);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/designer/alert-preferences' && $method === 'POST') {
        $user = Auth::requireDesigner();
        $body = requestData();
        LeadMatchingService::savePreferences((int)$user['pro_id'], [
            'cities' => $body['cities'] ?? '',
            'localities' => $body['localities'] ?? '',
            'project_categories' => $body['project_categories'] ?? '',
            'min_project_budget' => $body['min_project_budget'] ?? 0,
            'max_project_budget' => $body['max_project_budget'] ?? 0,
            'whatsapp_alerts_enabled' => !empty($body['whatsapp_alerts_enabled']),
            'browser_push_enabled' => !empty($body['browser_push_enabled']),
            'quiet_hours_start' => $body['quiet_hours_start'] ?? '',
            'quiet_hours_end' => $body['quiet_hours_end'] ?? '',
            'daily_alert_limit' => $body['daily_alert_limit'] ?? 10,
            'alerts_paused' => !empty($body['alerts_paused']),
            'whatsapp_consent' => !empty($body['whatsapp_consent']),
            'browser_push_consent' => !empty($body['browser_push_consent']),
        ]);
        jsonResponse(['success' => true, 'message' => 'Alert preferences saved.']);
    }

    if ($path === '/api/designer/push-subscriptions' && $method === 'POST') {
        $user = Auth::requireDesigner();
        try {
            LeadMatchingService::savePushSubscription((int)$user['pro_id'], requestJson(), (string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
            jsonResponse(['success' => true]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/designer/push-subscriptions' && $method === 'DELETE') {
        $user = Auth::requireDesigner();
        $body = requestJson();
        LeadMatchingService::removePushSubscription((int)$user['pro_id'], (string)($body['endpoint'] ?? ''));
        jsonResponse(['success' => true]);
    }

    if ($path === '/api/lead-engagement' && $method === 'POST') {
        $user = Auth::requireDesigner();
        $body = requestJson();
        LeadMatchingService::recordEngagement((int)($body['lead_id'] ?? 0), (int)$user['pro_id'], (string)($body['event_type'] ?? ''), $body);
        jsonResponse(['success' => true]);
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

    if ($path === '/api/designer/profile') {
        $user = Auth::requireDesigner();
        if ($method === 'GET') {
            $profile = SiteRepository::designerProfessional((int)$user['pro_id']);
            if (!$profile) {
                jsonResponse(['error' => 'Professional profile not found.'], 404);
            }
            jsonResponse(['profile' => $profile]);
        }
        if ($method === 'POST') {
            try {
                $body = requestData();
                $current = SiteRepository::designerProfessional((int)$user['pro_id']);
                if (!$current) {
                    jsonResponse(['error' => 'Professional profile not found.'], 404);
                }
                $body['profile_pic'] = saveUploadedFile($_FILES['profile_pic'] ?? [], 'professionals', (string)($current['profile_pic'] ?? ''));
                $body['cover_photo'] = saveUploadedFile($_FILES['cover_photo'] ?? [], 'professionals', (string)($current['cover_photo'] ?? ''));
                SiteRepository::updateDesignerProfessional((int)$user['pro_id'], $body);
                jsonResponse(['success' => true]);
            } catch (InvalidArgumentException $e) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
        }
    }

    if (preg_match('#^/api/designer/portfolios/(\d+)$#', $path, $match)) {
        $user = Auth::requireDesigner();
        $portfolioId = (int)$match[1];
        try {
            if ($method === 'PUT' || $method === 'POST') {
                $body = requestData();
                $current = SiteRepository::designerPortfolio((int)$user['pro_id'], $portfolioId);
                if (!$current) {
                    jsonResponse(['error' => 'Portfolio work was not found.'], 404);
                }
                $existingMedia = getJsonArrayField($body, 'current_media_json', getJsonArrayField($current, 'media_json', []));
                $body['media_json'] = saveUploadedFiles($_FILES['media_files'] ?? [], 'portfolio', $existingMedia);
                SiteRepository::updateDesignerPortfolio((int)$user['pro_id'], $portfolioId, $body);
                jsonResponse(['success' => true]);
            }
            if ($method === 'DELETE') {
                SiteRepository::deleteDesignerPortfolio((int)$user['pro_id'], $portfolioId);
                jsonResponse(['success' => true]);
            }
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 404);
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

    if ($path === '/api/designer/quotation-trial' && $method === 'POST') {
        $user = Auth::requireDesigner();
        try {
            $status = SiteRepository::designerSubscriptionStatus($user);
            if (empty($status['trial_available'])) {
                jsonResponse(['error' => 'This free trial is no longer available for this account.'], 400);
            }
            jsonResponse(['success' => true] + SiteRepository::createDesignerRecurringSubscription($user, true));
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/designer/quotation-subscription' && $method === 'POST') {
        $user = Auth::requireDesigner();
        try {
            jsonResponse(['success' => true] + SiteRepository::createDesignerRecurringSubscription($user));
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            jsonResponse(['error' => $e->getMessage()], $e->getCode() === 401 ? 401 : 500);
        }
    }

    if ($path === '/api/designer/quotation-subscription/verify' && $method === 'POST') {
        $user = Auth::requireDesigner();
        $body = requestJson();
        try {
            SiteRepository::activateDesignerRecurringSubscription(
                $user,
                trim((string)($body['razorpay_subscription_id'] ?? '')),
                trim((string)($body['razorpay_payment_id'] ?? '')),
                trim((string)($body['razorpay_signature'] ?? ''))
            );
            jsonResponse(['success' => true, 'redirect_url' => '/designer/quotations/create']);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/razorpay/quotation-subscription-webhook' && $method === 'POST') {
        $raw = file_get_contents('php://input') ?: '';
        $signature = (string)($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '');
        if (RAZORPAY_WEBHOOK_SECRET === '' || !hash_equals(hash_hmac('sha256', $raw, RAZORPAY_WEBHOOK_SECRET), $signature)) {
            jsonResponse(['error' => 'Invalid webhook signature'], 401);
        }
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            jsonResponse(['error' => 'Invalid webhook payload'], 400);
        }
        SiteRepository::updateDesignerRecurringSubscriptionFromWebhook($payload);
        jsonResponse(['success' => true]);
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

    if ($path === '/api/site-search' && $method === 'GET') {
        $query = trim((string)($_GET['q'] ?? ''));
        if (mb_strlen($query) < 1) {
            jsonResponse(['search_id' => null, 'results' => []]);
        }
        jsonResponse(SiteRepository::searchPublicPages($query, (string)($_GET['source'] ?? '/')));
    }

    if ($path === '/api/site-search/click' && $method === 'POST') {
        SiteRepository::trackSiteSearchClick(requestJson());
        jsonResponse(['success' => true]);
    }

    if ($path === '/api/home-services' && $method === 'GET') {
        jsonResponse(['categories' => SiteRepository::homeServiceCatalog(), 'cities' => SiteRepository::homeServiceCities()]);
    }

    if (preg_match('#^/api/home-services/([a-z0-9-]+)/form$#', $path, $homeServiceFormMatch) && $method === 'GET') {
        $service = SiteRepository::homeServiceBySlug((string)$homeServiceFormMatch[1]);
        if (!$service) {
            jsonResponse(['error' => 'Service not found.'], 404);
        }
        jsonResponse(['service' => $service, 'form' => SiteRepository::homeServiceForm((int)$service['id'])]);
    }

    if ($path === '/api/home-services/enquiries' && $method === 'POST') {
        try {
            $data = requestData();
            $result = SiteRepository::createHomeServiceEnquiry($data);
            jsonResponse(['success' => true, 'enquiry_id' => $result['id'], 'lead_id' => $result['lead_id'] ?? null, 'duplicate' => $result['duplicate'] ?? false]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            jsonResponse(['error' => 'Unable to save this service request. Please try again.'], 500);
        }
    }

    if ($path === '/api/admin/home-services/nodes' && $method === 'POST') {
        Auth::requireAdmin();
        try {
            jsonResponse(['success' => true, 'id' => SiteRepository::saveHomeServiceNode(requestData())]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 422);
        }
    }

    if ($path === '/api/admin/home-services/questions' && $method === 'POST') {
        Auth::requireAdmin();
        try {
            $data = requestData();
            $questions = json_decode((string)($data['questions_json'] ?? '[]'), true);
            if (!is_array($questions)) {
                throw new InvalidArgumentException('Questions JSON is invalid.');
            }
            SiteRepository::saveHomeServiceFormQuestions((int)($data['service_id'] ?? 0), $questions);
            jsonResponse(['success' => true]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 422);
        }
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

    if ($path === '/api/single-lead-orders/create' && $method === 'POST') {
        Auth::start();
        $body = requestJson();
        $saleId = (int)($body['sale_id'] ?? 0);
        $buyer = buyerUser();
        try {
            if (!$buyer) {
                $buyer = SiteRepository::createOrLoginBuyerForCheckout($body['buyer'] ?? []);
                setBuyerSession($buyer);
            }
            $sale = SiteRepository::getSingleLeadSaleById($saleId, true);
            if (!$sale) {
                jsonResponse(['error' => 'Single lead is not available.'], 404);
            }
            $receipt = 'single_' . (int)$buyer['id'] . '_' . $saleId . '_' . time();
            $singleLeadAmount = (int)round((float)$sale['price_amount'] * 100);
            $order = razorpayCreateOrder($singleLeadAmount, $receipt, [
                'buyer_id' => (int)$buyer['id'],
                'single_lead_sale_id' => $saleId,
                'lead_id' => (int)$sale['lead_id'],
                'module' => 'single_lead_sale',
            ]);
            SiteRepository::createSingleLeadPurchaseOrder((int)$buyer['id'], $saleId, (string)$order['id']);
            jsonResponse([
                'success' => true,
                'key_id' => defined('RAZORPAY_KEY_ID') ? RAZORPAY_KEY_ID : '',
                'order_id' => (string)$order['id'],
                'amount' => (int)($order['amount'] ?? $singleLeadAmount),
                'currency' => (string)($order['currency'] ?? 'INR'),
                'buyer' => buyerUser(),
            ]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        } catch (RuntimeException $e) {
            jsonResponse(['error' => $e->getMessage()], $e->getCode() === 401 ? 401 : 500);
        }
    }

    if ($path === '/api/single-lead-orders/verify' && $method === 'POST') {
        $buyer = requireBuyer();
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
        try {
            SiteRepository::markSingleLeadPurchasePaid($orderId, (int)$buyer['id'], $paymentId, $signature);
            jsonResponse(['success' => true, 'redirect_url' => '/lead-dashboard?payment=single-lead-success']);
        } catch (RuntimeException $e) {
            jsonResponse(['error' => $e->getMessage()], 409);
        }
    }

    if ($path === '/api/lead-cart') {
        Auth::start();
        $_SESSION['lead_cart'] = isset($_SESSION['lead_cart']) && is_array($_SESSION['lead_cart']) ? $_SESSION['lead_cart'] : [];
        $buyer = buyerUser();
        $firstTimeEligible = $buyer && !empty($_SESSION['designer_onboarding_free_lead_buyer_id']) && (int)$_SESSION['designer_onboarding_free_lead_buyer_id'] === (int)$buyer['id'] && SiteRepository::designerOnboardingLeadBenefitEligible((int)$buyer['id']);
        if ($method === 'GET') {
            try {
                jsonResponse(SiteRepository::leadCartSummary($_SESSION['lead_cart'], $_SESSION['lead_coupon'] ?? null, $firstTimeEligible, $buyer ? (int)$buyer['id'] : null));
            } catch (InvalidArgumentException) {
                unset($_SESSION['lead_coupon']);
                jsonResponse(SiteRepository::leadCartSummary($_SESSION['lead_cart'], null, $firstTimeEligible, $buyer ? (int)$buyer['id'] : null));
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
                jsonResponse(['success' => true] + SiteRepository::leadCartSummary($_SESSION['lead_cart'], $_SESSION['lead_coupon'] ?? null, $firstTimeEligible, $buyer ? (int)$buyer['id'] : null));
            } catch (InvalidArgumentException) {
                unset($_SESSION['lead_coupon']);
                jsonResponse(['success' => true] + SiteRepository::leadCartSummary($_SESSION['lead_cart'], null, $firstTimeEligible, $buyer ? (int)$buyer['id'] : null));
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
                jsonResponse(['success' => true] + SiteRepository::leadCartSummary($_SESSION['lead_cart'], $_SESSION['lead_coupon'] ?? null, $firstTimeEligible, $buyer ? (int)$buyer['id'] : null));
            } catch (InvalidArgumentException) {
                unset($_SESSION['lead_coupon']);
                jsonResponse(['success' => true] + SiteRepository::leadCartSummary($_SESSION['lead_cart'], null, $firstTimeEligible, $buyer ? (int)$buyer['id'] : null));
            }
        }
    }

    if ($path === '/api/lead-cart/coupon' && $method === 'POST') {
        Auth::start();
        $_SESSION['lead_cart'] = isset($_SESSION['lead_cart']) && is_array($_SESSION['lead_cart']) ? $_SESSION['lead_cart'] : [];
        $body = requestJson();
        $code = strtoupper(trim((string)($body['code'] ?? '')));
        $buyer = buyerUser();
        $firstTimeEligible = $buyer && !empty($_SESSION['designer_onboarding_free_lead_buyer_id']) && (int)$_SESSION['designer_onboarding_free_lead_buyer_id'] === (int)$buyer['id'] && SiteRepository::designerOnboardingLeadBenefitEligible((int)$buyer['id']);
        $summary = SiteRepository::leadCartSummary($_SESSION['lead_cart'], null, $firstTimeEligible, $buyer ? (int)$buyer['id'] : null);
        $coupon = SiteRepository::validateLeadCoupon($code, (float)$summary['subtotal'], (int)$summary['lead_count']);
        $_SESSION['lead_coupon'] = (string)$coupon['code'];
        jsonResponse(['success' => true] + SiteRepository::leadCartSummary($_SESSION['lead_cart'], $_SESSION['lead_coupon'], $firstTimeEligible, $buyer ? (int)$buyer['id'] : null));
    }

    if ($path === '/api/lead-cart/coupon' && $method === 'DELETE') {
        Auth::start();
        unset($_SESSION['lead_coupon']);
        $_SESSION['lead_cart'] = isset($_SESSION['lead_cart']) && is_array($_SESSION['lead_cart']) ? $_SESSION['lead_cart'] : [];
        $buyer = buyerUser();
        $firstTimeEligible = $buyer && !empty($_SESSION['designer_onboarding_free_lead_buyer_id']) && (int)$_SESSION['designer_onboarding_free_lead_buyer_id'] === (int)$buyer['id'] && SiteRepository::designerOnboardingLeadBenefitEligible((int)$buyer['id']);
        jsonResponse(['success' => true] + SiteRepository::leadCartSummary($_SESSION['lead_cart'], null, $firstTimeEligible, $buyer ? (int)$buyer['id'] : null));
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
            $buyer = SiteRepository::createOrLoginBuyerForCheckout($body['buyer'] ?? []);
            setBuyerSession($buyer);
        }
        $firstTimeEligible = !empty($_SESSION['designer_onboarding_free_lead_buyer_id']) && (int)$_SESSION['designer_onboarding_free_lead_buyer_id'] === (int)$buyer['id'] && SiteRepository::designerOnboardingLeadBenefitEligible((int)$buyer['id']);
        if (!empty($body['account_only'])) {
            try {
                $summary = SiteRepository::leadCartSummary($_SESSION['lead_cart'], $_SESSION['lead_coupon'] ?? null, $firstTimeEligible, $buyer ? (int)$buyer['id'] : null);
            } catch (InvalidArgumentException) {
                unset($_SESSION['lead_coupon']);
                $summary = SiteRepository::leadCartSummary($_SESSION['lead_cart'], null, $firstTimeEligible, $buyer ? (int)$buyer['id'] : null);
            }
            jsonResponse(['success' => true, 'buyer' => buyerUser(), 'cart' => $summary]);
        }
        try {
            $summary = SiteRepository::leadCartSummary($_SESSION['lead_cart'], $_SESSION['lead_coupon'] ?? null, $firstTimeEligible, $buyer ? (int)$buyer['id'] : null);
        } catch (InvalidArgumentException) {
            unset($_SESSION['lead_coupon']);
            $summary = SiteRepository::leadCartSummary($_SESSION['lead_cart'], null, $firstTimeEligible, $buyer ? (int)$buyer['id'] : null);
        }
        $cart = $summary['items'];
        if (!$summary['items']) {
            jsonResponse(['error' => 'Cart is empty'], 400);
        }
        $amount = (float)$summary['grand_total'];
        if ($amount <= 0) {
            SiteRepository::createFreeLeadPurchase((int)$buyer['id'], $cart, $summary['coupon']['code'] ?? null, (float)$summary['discount_amount']);
            if ($firstTimeEligible) {
                SiteRepository::redeemDesignerOnboardingLeadBenefit((int)$buyer['id']);
                unset($_SESSION['designer_onboarding_free_lead_buyer_id']);
            }
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
        SiteRepository::recordAbandonedCheckout('bulk_leads', $buyer, $amount, ['cart' => $cart, 'summary' => $summary], (string)$order['id'], (int)$buyer['id']);
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
            if (!empty($_SESSION['designer_onboarding_free_lead_buyer_id']) && (int)$_SESSION['designer_onboarding_free_lead_buyer_id'] === (int)$buyer['id']) {
                SiteRepository::redeemDesignerOnboardingLeadBenefit((int)$buyer['id']);
                unset($_SESSION['designer_onboarding_free_lead_buyer_id']);
            }
            $_SESSION['lead_cart'] = [];
            unset($_SESSION['lead_coupon']);
        }
        jsonResponse(['success' => true, 'redirect_url' => '/lead-dashboard?payment=success']);
    }

    if ($path === '/api/admin/checkout-recovery/export' && $method === 'GET') {
        Auth::requireAdmin();
        $rows = SiteRepository::abandonedCheckouts((string)($_GET['status'] ?? 'open'));
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="checkout-recovery-' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Checkout type', 'Status', 'Name', 'Email', 'Phone', 'Amount', 'Order / subscription ID', 'Started at', 'Cart snapshot']);
        foreach ($rows as $row) {
            fputcsv($output, [(string)$row['checkout_type'], (string)$row['checkout_status'], (string)($row['customer_name'] ?? ''), (string)($row['customer_email'] ?? ''), (string)($row['customer_phone'] ?? ''), (string)$row['amount_total'], (string)($row['external_order_id'] ?? ''), (string)$row['started_at'], (string)($row['cart_snapshot_json'] ?? '')]);
        }
        fclose($output);
        exit;
    }

    if ($path === '/api/admin/export' && $method === 'GET') {
        Auth::requireAdmin();
        $resource = strtolower(trim((string)($_GET['resource'] ?? '')));
        try {
            $xlsx = SiteRepository::adminExportXlsx($resource);
            $filename = preg_replace('/[^a-z0-9-]+/', '-', $resource) ?: 'admin-export';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="homeinteriors360-' . $filename . '-' . date('Y-m-d') . '.xlsx"');
            header('Content-Length: ' . strlen($xlsx));
            echo $xlsx;
            exit;
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
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

    if ($path === '/api/admin/articles') {
        $user = Auth::requireAdmin();
        if ($method === 'GET') {
            jsonResponse(['articles' => SiteRepository::listArticles([], true)]);
        }
        if ($method === 'POST') {
            $body = requestData();
            foreach (['type', 'color', 'city', 'state', 'style', 'layout', 'location', 'photo_source_name'] as $field) {
                if (($body[$field] ?? '') === '__other__') {
                    $body[$field] = trim((string)($body[$field . '_other'] ?? ''));
                }
            }
            $body['image_path'] = saveUploadedFile($_FILES['image_file'] ?? [], 'articles', (string)(($body['image_path'] ?? '') ?: ($body['current_image_path'] ?? '')));
            $body['created_by'] = (int)($user['id'] ?? 0);
            try {
                $id = SiteRepository::saveArticle($body);
                jsonResponse(['success' => true, 'id' => $id]);
            } catch (InvalidArgumentException $e) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
        }
    }

    if (preg_match('#^/api/admin/articles/(\\d+)$#', $path, $match)) {
        $user = Auth::requireAdmin();
        $id = (int)$match[1];
        if ($method === 'GET') {
            $article = SiteRepository::getArticle($id, true);
            if (!$article) {
                jsonResponse(['error' => 'Article not found'], 404);
            }
            jsonResponse(['article' => $article]);
        }
        if ($method === 'PUT' || $method === 'POST') {
            $body = requestData();
            foreach (['type', 'color', 'city', 'state', 'style', 'layout', 'location', 'photo_source_name'] as $field) {
                if (($body[$field] ?? '') === '__other__') {
                    $body[$field] = trim((string)($body[$field . '_other'] ?? ''));
                }
            }
            $body['image_path'] = saveUploadedFile($_FILES['image_file'] ?? [], 'articles', (string)(($body['image_path'] ?? '') ?: ($body['current_image_path'] ?? '')));
            $body['created_by'] = (int)($user['id'] ?? 0);
            try {
                SiteRepository::saveArticle($body, $id);
                jsonResponse(['success' => true]);
            } catch (InvalidArgumentException $e) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
        }
        if ($method === 'DELETE') {
            SiteRepository::deleteArticle($id);
            jsonResponse(['success' => true]);
        }
    }

    if ($path === '/api/admin/leads' && $method === 'GET') {
        Auth::requireAdmin();
        jsonResponse(['leads' => SiteRepository::listLeads()]);
    }

    if ($path === '/api/admin/leads/upload' && $method === 'POST') {
        Auth::requireAdmin();
        try {
            $result = SiteRepository::importLeadsFromUploadedFile($_FILES['lead_file'] ?? []);
            jsonResponse(['success' => true] + $result);
        } catch (Throwable $e) {
            jsonResponse(['error' => $e->getMessage()], $e instanceof InvalidArgumentException ? 400 : 500);
        }
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

    if ($path === '/api/admin/single-lead-sales' && ($method === 'POST' || $method === 'PUT')) {
        $user = Auth::requireAdmin();
        $body = requestData();
        try {
            if (($body['action'] ?? '') === 'delete') {
                SiteRepository::deleteSingleLeadSale((int)($body['id'] ?? 0));
                redirectTo('/admin/single-lead-sales?deleted=1');
            }
            if (($body['action'] ?? '') === 'set_status') {
                SiteRepository::updateSingleLeadSaleStatus((int)($body['id'] ?? 0), (string)($body['status'] ?? 'draft'));
                redirectTo('/admin/single-lead-sales?saved=1');
            }
            if (($body['action'] ?? '') === 'update_price') {
                SiteRepository::updateSingleLeadSalePrice((int)($body['id'] ?? 0), (float)($body['price_amount'] ?? 0));
                redirectTo('/admin/single-lead-sales?saved=1');
            }
            $body['created_by'] = (int)($user['id'] ?? 0);
            SiteRepository::saveSingleLeadSale($body, !empty($body['id']) ? (int)$body['id'] : null);
            redirectTo('/admin/single-lead-sales?saved=1');
        } catch (InvalidArgumentException | RuntimeException $e) {
            jsonResponse(['error' => $e->getMessage()], $e instanceof InvalidArgumentException ? 400 : 500);
        }
    }

    if ($path === '/api/admin/founding-partners/status' && ($method === 'POST' || $method === 'PUT')) {
        Auth::requireAdmin();
        $body = requestData();
        try {
            LeadMatchingService::updateFoundingPartnerStatus((int)($body['id'] ?? 0), (string)($body['status'] ?? 'Applied'), !empty($body['badge_enabled']));
            redirectTo('/admin/lead-alerts');
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/api/admin/lead-alert-settings' && ($method === 'POST' || $method === 'PUT')) {
        Auth::requireAdmin();
        LeadMatchingService::saveSettings(requestData());
        redirectTo('/admin/lead-alerts');
    }

    if ($path === '/api/admin/lead-alerts/recalculate' && $method === 'POST') {
        Auth::requireAdmin();
        $body = requestData();
        $matches = LeadMatchingService::recalculateMatches((int)($body['lead_id'] ?? 0));
        if (str_contains((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')) {
            jsonResponse(['success' => true, 'matches' => $matches]);
        }
        redirectTo('/admin/lead-alerts?lead_id=' . (int)($body['lead_id'] ?? 0));
    }

    if ($path === '/api/admin/lead-alerts/start' && $method === 'POST') {
        Auth::requireAdmin();
        $body = requestData();
        $jobs = LeadMatchingService::scheduleWaves((int)($body['lead_id'] ?? 0));
        if (str_contains((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')) {
            jsonResponse(['success' => true, 'jobs' => $jobs]);
        }
        redirectTo('/admin/lead-alerts?lead_id=' . (int)($body['lead_id'] ?? 0));
    }

    if ($path === '/api/admin/lead-alerts/process' && ($method === 'POST' || $method === 'GET')) {
        Auth::requireAdmin();
        jsonResponse(['success' => true] + LeadMatchingService::processDueJobs(50));
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

    if (preg_match('#^/api/admin/designer-accounts/(\\d+)$#', $path, $match) && $method === 'GET') {
        Auth::requireAdmin();
        $detail = SiteRepository::designerAccountDetail((int)$match[1]);
        if (!$detail) {
            jsonResponse(['error' => 'Designer account not found.'], 404);
        }
        jsonResponse($detail);
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

    if (preg_match('#^/api/admin/designer-contact-queries/(\\d+)/status$#', $path, $match) && $method === 'PUT') {
        Auth::requireAdmin();
        try {
            SiteRepository::updateDesignerContactQueryStatus((int)$match[1], (string)(requestJson()['status'] ?? ''));
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
            foreach (['type', 'color', 'city', 'state', 'style', 'layout', 'location', 'photo_source_name'] as $field) {
                if (($body[$field] ?? '') === '__other__') {
                    $body[$field] = trim((string)($body[$field . '_other'] ?? ''));
                }
            }
            if (empty($body['name']) || empty($body['type'])) {
                jsonResponse(['error' => 'Idea title and type are required'], 400);
            }
            $body['image_url'] = saveUploadedFile($_FILES['image_file'] ?? [], 'design-ideas', (string)($body['current_image_url'] ?? ''));
            if (empty($body['image_url'])) {
                jsonResponse(['error' => 'Please upload a main image for this design idea.'], 400);
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
            foreach (['type', 'color', 'city', 'state', 'style', 'layout', 'location', 'photo_source_name'] as $field) {
                if (($body[$field] ?? '') === '__other__') {
                    $body[$field] = trim((string)($body[$field . '_other'] ?? ''));
                }
            }
            if (empty($body['name']) || empty($body['type'])) {
                jsonResponse(['error' => 'Idea title and type are required'], 400);
            }
            $body['image_url'] = saveUploadedFile($_FILES['image_file'] ?? [], 'design-ideas', (string)($body['current_image_url'] ?? ''));
            if (empty($body['image_url'])) {
                jsonResponse(['error' => 'Please upload a main image for this design idea.'], 400);
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
    if ($path === '/api/boq/anonymous-drafts' && $method === 'POST') {
        try {
            $draft = SiteRepository::createAnonymousBoqDraft(requestData());
            jsonResponse(['success' => true, 'token' => $draft['token'], 'redirect_url' => '/boq-studio/editor/' . $draft['token']]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if (preg_match('#^/api/boq/anonymous-drafts/([a-f0-9]{48})$#', $path, $match)) {
        if ($method === 'GET') {
            $draft = SiteRepository::getAnonymousBoqDraft((string)$match[1]);
            if (!$draft) {
                jsonResponse(['error' => 'Sample BOQ not found'], 404);
            }
            jsonResponse(['draft' => $draft['draft']]);
        }
        if ($method === 'POST' || $method === 'PUT') {
            try {
                $draft = SiteRepository::saveAnonymousBoqDraft((string)$match[1], requestData());
                jsonResponse(['success' => true, 'draft' => $draft['draft']]);
            } catch (InvalidArgumentException $e) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
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
                    SiteRepository::startDesignerQuotationTrial($user);
                    if (!SiteRepository::designerCanCreateQuotation($user)) {
                        jsonResponse(['error' => 'Your 15-day trial has ended. Set up monthly automatic payment to create new quotations.'], 403);
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
        render(empty($match[2]) ? 'public/proposal-public' : 'public/proposal-print', [
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
        $user = Auth::requireAuth();
        if (!Auth::isAdmin($user) && !Auth::isDesigner($user)) {
            jsonResponse(['error' => 'Rate cards are not available for this account.'], 403);
        }
        if ($method === 'GET') {
            if (Auth::isDesigner($user)) {
                $_GET['designer_id'] = (int)($user['pro_id'] ?? 0);
            }
            jsonResponse(['rate_cards' => SiteRepository::quotationRateCards($_GET)]);
        }
        if ($method === 'POST') {
            try {
                $body = requestData();
                if (Auth::isDesigner($user)) {
                    $body['designer_id'] = (int)($user['pro_id'] ?? 0);
                }
                $id = SiteRepository::saveQuotationRateCard($body);
                jsonResponse(['success' => true, 'id' => $id]);
            } catch (InvalidArgumentException $e) {
                jsonResponse(['error' => $e->getMessage()], 400);
            }
        }
    }

    if (preg_match('#^/api/rate-cards/(\d+)$#', $path, $match) && ($method === 'PUT' || $method === 'POST')) {
        $user = Auth::requireAuth();
        $rateId = (int)$match[1];
        $existing = Database::one('SELECT designer_id FROM quotation_rate_cards WHERE id=?', [$rateId]);
        if (!$existing || (!Auth::isAdmin($user) && (!Auth::isDesigner($user) || (int)$existing['designer_id'] !== (int)($user['pro_id'] ?? 0)))) {
            jsonResponse(['error' => 'Rate card not found'], 404);
        }
        try {
            $body = requestData();
            if (Auth::isDesigner($user)) {
                $body['designer_id'] = (int)$user['pro_id'];
            }
            SiteRepository::saveQuotationRateCard($body, $rateId);
            jsonResponse(['success' => true, 'id' => $rateId]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
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
        $quote = SiteRepository::publicProposalData((string)$match[1], true);
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

    if (preg_match('#^/api/proposals/([a-f0-9]{32,96})/(accept|request-revision)$#', $path, $match) && $method === 'POST') {
        try {
            $body = requestData();
            $quote = SiteRepository::respondToPublicProposal((string)$match[1], $match[2] === 'accept' ? 'accept' : 'revision', (string)($body['note'] ?? ''));
            if (!$quote) {
                jsonResponse(['error' => 'Proposal not found'], 404);
            }
            jsonResponse(['success' => true, 'status' => $quote['status']]);
        } catch (InvalidArgumentException $e) {
            jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    if ($path === '/contact-us') {
        render('public/contact-us', [
            'title' => 'Contact HomeInteriors360 | Designer and Partnership Support',
            'metaDescription' => 'Contact HomeInteriors360 CEO office for designer support, partnerships, platform feedback, lead marketplace queries, and account help.',
            'canonicalUrl' => absoluteUrl('/contact-us'),
            'active' => '',
            'content' => $content,
            'structuredData' => [[
                '@context' => 'https://schema.org', '@type' => 'ContactPage', 'name' => 'Contact HomeInteriors360', 'url' => absoluteUrl('/contact-us'),
            ]],
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
                            'Each lead receives a fixed quality price from INR 199 to INR 999 when it is created.',
                            'Location, project budget, and completed requirement details contribute to the lead quality price.',
                            'Registration-only lead benefits, when available, are applied in the designer onboarding flow.',
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
        $legalType = $path === '/contact-us' ? 'ContactPage' : 'WebPage';
        render('public/legal', [
            'title' => $page['title'] . ' | HomeInteriors360',
            'metaDescription' => $page['meta'],
            'canonicalUrl' => absoluteUrl($path),
            'active' => '',
            'content' => $content,
            'legalTitle' => $page['title'],
            'lastUpdated' => $page['lastUpdated'] ?? '28 May 2026',
            'sections' => $page['sections'],
            'structuredData' => [[
                '@context' => 'https://schema.org',
                '@type' => $legalType,
                'name' => $page['title'],
                'description' => $page['meta'],
                'url' => absoluteUrl($path),
            ]],
        ]);
        exit;
    }

    if (isset($seoLeadPages[$path])) {
        $page = $seoLeadPages[$path];
        $keyword = (string)$page['keyword'];
        $citySuffix = !empty($page['city']) ? ' in ' . (string)$page['city'] : '';
        $intentProfile = $seoLeadIntentCopy[$path] ?? null;
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
                'answer' => 'Each lead has a fixed quality price from INR 199 to INR 999. The price reflects location, project budget, and completed requirement details. Registration-only benefits are handled in the designer onboarding flow.',
            ],
        ];
        if (!empty($page['city'])) {
            array_unshift($faqs, [
                'question' => 'Which areas are covered for ' . (string)$page['city'] . ' interior leads?',
                'answer' => 'HomeInteriors360 supports matching by city and locality. For ' . (string)$page['city'] . ', useful service-area examples include ' . (string)($page['local_areas'] ?? $page['city']) . ', depending on available homeowner enquiry data.',
            ]);
        }
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
                    'Lead packages use visible quality-based pricing. First-time eligibility, coupons, subtotal, discount, and grand total are shown before payment.',
                    'After successful Razorpay payment verification, the purchased package becomes available from the buyer dashboard as a digital download.',
                ],
            ],
        ];
        if ($intentProfile && !empty($intentProfile['sections'])) {
            $landingSections = array_merge((array)$intentProfile['sections'], $landingSections);
        }
        if (!empty($page['city'])) {
            array_splice($landingSections, 1, 0, [[
                'title' => 'Local Demand Areas in ' . (string)$page['city'],
                'body' => [
                    'Interior lead demand in ' . (string)$page['city'] . ' is shaped by locality, apartment size, possession status, renovation age, and budget band. Useful micro-markets include ' . (string)($page['local_areas'] ?? $page['city']) . '.',
                    'Use city and locality filters before purchase so your team follows up only on serviceable homeowner enquiries instead of generic, low-fit contacts.',
                ],
            ]]);
        }
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
            'keywords' => implode(', ', (array)($intentProfile['chips'] ?? [$keyword, 'verified interior leads', 'interior lead generation'])),
            'offers' => [
                '@type' => 'AggregateOffer',
                'priceCurrency' => 'INR',
                'lowPrice' => '199',
                'highPrice' => '999',
                'offerCount' => '5',
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
            'keywordChips' => $intentProfile['chips'] ?? [$keyword, 'verified interior leads', 'interior lead generation', 'lead marketplace'],
            'landingSections' => $landingSections,
            'faqs' => $faqs,
            'cityLinks' => $seoCityLinks,
            'recentLeadPreviews' => $path === '/interior-designer-leads-delhi-ncr'
                ? SiteRepository::recentPublicLeadPreviews('Delhi NCR', 10)
                : [],
            'structuredData' => [$serviceSchema, $faqSchema, $breadcrumbSchema],
        ]);
        exit;
    }

    // Public pages
    if ($path === '/boq-studio') {
        SiteRepository::trackBoqEvent('landing_view');
        render('public/boq-studio', [
            'title' => 'BOQ Studio for Interior Designers | HomeInteriors360',
            'metaDescription' => 'Create a GST-ready, client-ready interior BOQ in minutes. Try the HomeInteriors360 BOQ Studio free without signing up.',
            'canonicalUrl' => absoluteUrl('/boq-studio'),
            'active' => 'professionals-growth',
            'content' => $content,
            'boqTemplates' => SiteRepository::boqTemplates(),
        ]);
        exit;
    }
    if (preg_match('#^/boq-studio/editor/([a-f0-9]{48})$#', $path, $match)) {
        $draft = SiteRepository::getAnonymousBoqDraft((string)$match[1]);
        if (!$draft) {
            header('Location: /boq-studio');
            exit;
        }
        render('public/boq-studio-editor', [
            'title' => 'Edit Sample BOQ | HomeInteriors360',
            'metaDescription' => 'Build a client-ready sample interior BOQ.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'professionals-growth',
            'content' => $content,
            'draftToken' => (string)$match[1],
            'draft' => $draft['draft'],
            'rateCards' => SiteRepository::quotationRateCards(['city' => (string)$draft['draft']['city']]),
        ]);
        exit;
    }
    if (preg_match('#^/boq-studio/unlock/([a-f0-9]{48})$#', $path, $match)) {
        $draft = SiteRepository::getAnonymousBoqDraft((string)$match[1]);
        if (!$draft) {
            header('Location: /boq-studio');
            exit;
        }
        SiteRepository::trackBoqEvent('signup_gate_shown', (string)$match[1]);
        render('public/boq-studio-unlock', [
            'title' => 'Unlock Your BOQ | HomeInteriors360',
            'metaDescription' => 'Create your interior designer account and unlock the full BOQ Studio.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'professionals-growth',
            'content' => $content,
            'draftToken' => (string)$match[1],
            'draft' => $draft['draft'],
        ]);
        exit;
    }
    if ($path === '/') {
        $homeServiceSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => 'Interior Designers and Architects Near You',
            'description' => 'Find verified interior designers, architects, contractors, and turnkey home interior professionals across Delhi NCR, Gurgaon, Delhi, and Noida.',
            'provider' => ['@type' => 'Organization', 'name' => 'HomeInteriors360', 'url' => absoluteUrl('/')],
            'areaServed' => ['Delhi NCR', 'Gurgaon', 'Delhi', 'Noida', 'Greater Noida', 'Ghaziabad', 'Faridabad'],
            'serviceType' => 'Interior designer matching and consultation',
        ];
        render('public/home', [
            'title' => 'HomeInteriors360 | Find Interior Designers & Architects in Delhi NCR',
            'metaDescription' => 'Discover interior designers, architects and turnkey experts in Delhi NCR. Compare portfolios, explore design ideas, estimate costs and book a free design consultation.',
            'ogTitle' => 'HomeInteriors360 | Find Interior Designers & Architects in Delhi NCR',
            'ogDescription' => 'Compare trusted interior professionals, explore design ideas and start your free home design consultation across Delhi NCR.',
            'canonicalUrl' => absoluteUrl('/'),
            'active' => 'home',
            'content' => $content,
            'payload' => SiteRepository::homepagePayload(),
            'structuredData' => [$homeServiceSchema],
        ]);
        exit;
    }

    if ($path === '/hire-a-designer') {
        header('Location: /home-interior-hire-a-designer', true, 301);
        exit;
    }

    if ($path === '/home-interior-hire-a-designer') {
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

    if ($path === '/articles') {
        $urlAlias = SiteRepository::getUrlAliasByPath('/articles');
        $filters = [
            'q' => $_GET['q'] ?? '',
            'article_type' => $_GET['article_type'] ?? '',
            'work_type' => $_GET['work_type'] ?? '',
            'city' => $_GET['city'] ?? '',
            'state' => $_GET['state'] ?? '',
        ];
        $articles = SiteRepository::listArticles($filters);
        $articleItemListSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Interior Design Articles & Guides',
            'itemListElement' => array_map(static fn(array $article, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => (string)($article['title'] ?? ''),
                'url' => absoluteUrl('/articles/' . (string)($article['slug'] ?? '')),
            ], array_slice($articles, 0, 20), array_keys(array_slice($articles, 0, 20))),
        ];
        render('public/articles', [
            'title' => (string)($urlAlias['meta_title'] ?? 'Interior Design Articles & Guides | HomeInteriors360'),
            'metaDescription' => (string)($urlAlias['meta_description'] ?? 'Read interior design articles, cost guides, room ideas, city planning notes and professional growth guides from HomeInteriors360.'),
            'canonicalUrl' => absoluteUrl('/articles'),
            'active' => 'articles',
            'content' => $content,
            'articles' => $articles,
            'filterOptions' => SiteRepository::articleFilterOptions(),
            'globalAlias' => $urlAlias,
            'structuredData' => [[
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => 'Interior Design Articles & Guides',
                'url' => absoluteUrl('/articles'),
            ], $articleItemListSchema],
        ]);
        exit;
    }

    if (preg_match('#^/articles/([a-z0-9-]+)$#', $path, $articleMatch)) {
        $article = SiteRepository::getArticle((string)$articleMatch[1]);
        if (!$article) {
            http_response_code(404);
            render('public/articles', [
                'title' => 'Article Not Found | HomeInteriors360',
                'metaDescription' => 'Browse HomeInteriors360 interior design articles and guides.',
                'metaRobots' => 'noindex,follow',
                'active' => 'articles',
                'content' => $content,
                'articles' => SiteRepository::listArticles(['limit' => 9]),
                'filterOptions' => SiteRepository::articleFilterOptions(),
            ]);
            exit;
        }
        $urlAlias = SiteRepository::getUrlAliasByPath('/articles/' . (string)$article['slug']);
        $faqSchema = null;
        if (!empty($article['faqs'])) {
            $faqSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => array_map(static fn(array $faq): array => [
                    '@type' => 'Question',
                    'name' => (string)$faq['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => (string)$faq['answer'],
                    ],
                ], $article['faqs']),
            ];
        }
        $schemas = array_filter([
            [
                '@context' => 'https://schema.org',
                '@type' => ['Article', 'BlogPosting'],
                'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => absoluteUrl('/articles/' . (string)$article['slug'])],
                'headline' => (string)$article['title'],
                'description' => (string)$article['excerpt'],
                'image' => !empty($article['image_path']) ? absoluteUrl((string)$article['image_path']) : absoluteUrl('/social-share-v2.jpg'),
                'datePublished' => (string)($article['published_at'] ?? $article['created_at'] ?? ''),
                'dateModified' => (string)($article['updated_at'] ?? ''),
                'author' => ['@type' => 'Organization', 'name' => (string)($article['author_name'] ?: 'HomeInteriors360')],
                'publisher' => ['@type' => 'Organization', 'name' => 'HomeInteriors360', 'url' => absoluteUrl('/'), 'logo' => ['@type' => 'ImageObject', 'url' => absoluteUrl('/logo.png')]],
                'articleSection' => (string)($article['article_type'] ?? 'Interior Design'),
                'keywords' => implode(', ', (array)($article['tags_json'] ?? [])),
            ],
            $faqSchema,
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => absoluteUrl('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Articles', 'item' => absoluteUrl('/articles')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => (string)$article['title'], 'item' => absoluteUrl('/articles/' . (string)$article['slug'])],
                ],
            ],
        ]);
        render('public/article-detail', [
            'title' => (string)($urlAlias['meta_title'] ?? ($article['meta_title'] ?: $article['title'] . ' | HomeInteriors360')),
            'metaDescription' => (string)($urlAlias['meta_description'] ?? ($article['meta_description'] ?: $article['excerpt'])),
            'metaKeywords' => implode(', ', array_filter(array_unique(array_merge(
                (array)($article['tags_json'] ?? []),
                [(string)($article['work_type'] ?? ''), (string)($article['article_type'] ?? ''), (string)($article['city'] ?? ''), 'interior designer leads', 'home interior design']
            )))),
            'canonicalUrl' => absoluteUrl((string)($urlAlias['canonical_url'] ?: '/articles/' . $article['slug'])),
            'metaRobots' => (string)($urlAlias['robots'] ?: 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1'),
            'ogImage' => !empty($urlAlias['image_url']) ? absoluteUrl((string)$urlAlias['image_url']) : (!empty($article['image_path']) ? absoluteUrl((string)$article['image_path']) : absoluteUrl('/social-share-v2.jpg')),
            'ogType' => 'article',
            'articlePublishedTime' => !empty($article['published_at']) ? date('c', strtotime((string)$article['published_at'])) : null,
            'articleModifiedTime' => !empty($article['updated_at']) ? date('c', strtotime((string)$article['updated_at'])) : null,
            'metaModifiedTime' => !empty($article['updated_at']) ? date('c', strtotime((string)$article['updated_at'])) : null,
            'active' => 'articles',
            'content' => $content,
            'article' => $article,
            'related' => SiteRepository::relatedArticles($article, 3),
            'latest' => SiteRepository::listArticles(['limit' => 4]),
            'globalAlias' => $urlAlias,
            'structuredData' => $schemas,
        ]);
        exit;
    }

    if ($path === '/home-services' || $path === '/services') {
        if ($path === '/services') {
            header('Location: /home-services', true, 301);
            exit;
        }
        $catalogue = SiteRepository::homeServiceCatalog();
        render('public/home-services', [
            'title' => 'Home Services in Delhi NCR | Get Free Quotes | HomeInteriors360',
            'metaDescription' => 'Find trusted home service professionals in Delhi NCR for interiors, electricians, plumbers, AC repair, cleaning, painting, moving and more. Get free quotes.',
            'canonicalUrl' => absoluteUrl('/home-services'),
            'active' => 'home-services',
            'content' => $content,
            'catalogue' => $catalogue,
            // This is a public availability display, not the complete form catalogue.
            'cities' => SiteRepository::homeServiceAvailableCitiesForSlug('interior-design'),
            'structuredData' => [[
                '@context' => 'https://schema.org', '@type' => 'CollectionPage',
                'name' => 'Home Services in Delhi NCR', 'url' => absoluteUrl('/home-services'),
                'description' => 'Find home service professionals for design, repair, cleaning, maintenance and household support.',
            ]],
        ]);
        exit;
    }

    $serviceRoute = null;
    $serviceCity = '';
    $serviceLocality = null;
    // Match the explicit /services/category/service route first. Otherwise its
    // three segments also satisfy the generic city/service/locality pattern.
    if (preg_match('#^/services/([a-z0-9-]+)/([a-z0-9-]+)$#', $path, $servicePathMatch)) {
        $serviceRoute = SiteRepository::homeServiceByPath((string)$servicePathMatch[1], (string)$servicePathMatch[2]);
    } elseif (preg_match('#^/([a-z-]+)/([a-z0-9-]+)/([a-z0-9-]+)$#', $path, $localityServiceMatch)) {
        $locality = LocationLandingCatalog::locality((string)$localityServiceMatch[1], (string)$localityServiceMatch[3]);
        if ($locality !== null && (string)$localityServiceMatch[2] === 'interior-design') {
            $serviceLocality = $locality;
            $serviceCity = (string)$locality['city'];
            $serviceRoute = SiteRepository::homeServiceBySlug('interior-design');
        }
    } elseif (preg_match('#^/([a-z-]+)/([a-z0-9-]+)$#', $path, $cityServiceMatch)) {
        $catalogueCity = LocationLandingCatalog::city((string)$cityServiceMatch[1]);
        if ($catalogueCity !== null && (string)$cityServiceMatch[2] === 'interior-design') {
            $serviceCity = (string)$catalogueCity['city'];
            $serviceRoute = SiteRepository::homeServiceBySlug('interior-design');
        } else {
            foreach (SiteRepository::homeServiceCities() as $city) {
                if (slugify($city) !== (string)$cityServiceMatch[1]) {
                    continue;
                }
                $serviceCity = $city;
                $serviceRoute = SiteRepository::homeServiceBySlug((string)$cityServiceMatch[2]);
                break;
            }
        }
    }
    if ($serviceRoute) {
        if ($serviceCity !== '' && $serviceLocality === null && !LocationLandingCatalog::cityForName($serviceCity) && !SiteRepository::homeServiceAvailableInCity((int)$serviceRoute['id'], $serviceCity)) {
            http_response_code(404);
            echo 'Service is not available in this city yet.';
            exit;
        }
        $canonicalPath = $serviceLocality !== null
            ? '/' . $serviceLocality['city_slug'] . '/' . $serviceRoute['slug'] . '/' . $serviceLocality['slug']
            : ($serviceCity !== ''
                ? '/' . slugify($serviceCity) . '/' . $serviceRoute['slug']
                : '/services/' . $serviceRoute['category_slug'] . '/' . $serviceRoute['slug']);
        $isInteriorDesignCityPage = (string)$serviceRoute['slug'] === 'interior-design' && $serviceCity !== '';
        $isInteriorDesignLocalityPage = $serviceLocality !== null;
        $isInteriorDesignHub = (string)$serviceRoute['slug'] === 'interior-design' && $serviceCity === '';
        $isHomeCleaningHub = (string)$serviceRoute['slug'] === 'home-cleaning' && $serviceCity === '';
        $serviceTitle = $isInteriorDesignLocalityPage
            ? 'Interior Designers in ' . $serviceLocality['name'] . ', ' . $serviceCity . ' | HomeInteriors360'
            : ($isHomeCleaningHub
            ? 'Home Cleaning Services in Delhi NCR | Deep Cleaning | HomeInteriors360'
            : ($isInteriorDesignCityPage
            ? 'Interior Designers in ' . $serviceCity . ' | Home Design & Decor | HomeInteriors360'
            : ($isInteriorDesignHub
                ? 'Interior Designers, Home Decor & Renovation | HomeInteriors360'
                : (string)$serviceRoute['name'] . ($serviceCity !== '' ? ' in ' . $serviceCity : '') . ' | HomeInteriors360')));
        $serviceHeading = $isInteriorDesignLocalityPage
            ? 'Interior Designers in ' . $serviceLocality['name'] . ', ' . $serviceCity
            : ($isHomeCleaningHub
            ? 'Home Cleaning Services for Deep, Move-in and Regular Cleaning'
            : ($isInteriorDesignHub
            ? 'Interior Designers for Home Design, Decor & Renovation'
            : (string)$serviceRoute['name']));
        $providers = $serviceCity !== '' ? SiteRepository::homeServiceProfessionals((int)$serviceRoute['id'], $serviceCity) : [];
        $form = SiteRepository::homeServiceForm((int)$serviceRoute['id']);
        $availableServiceCities = SiteRepository::homeServiceAvailableCities((int)$serviceRoute['id']);
        $relatedServices = [];
        foreach (SiteRepository::homeServiceCatalog() as $category) {
            foreach ((array)($category['children'] ?? []) as $candidate) {
                if ((int)($candidate['id'] ?? 0) !== (int)$serviceRoute['id'] && ($serviceCity === '' || SiteRepository::homeServiceAvailableInCity((int)$candidate['id'], $serviceCity))) {
                    $candidate['category_slug'] = $category['slug'] ?? '';
                    $relatedServices[] = $candidate;
                }
            }
        }
        $relatedServices = array_slice($relatedServices, 0, 8);
        $cityPlanningLinks = $serviceCity === '' ? [] : [
            ['label' => 'Find professionals in ' . $serviceCity, 'href' => '/professionals?city=' . rawurlencode($serviceCity)],
            ['label' => 'Explore interior design ideas', 'href' => '/design-ideas'],
            ['label' => 'Plan your interior budget', 'href' => '/full-home-cost-calculator'],
            ['label' => 'Get a free design consultation', 'href' => '/home-interior-hire-a-designer?city=' . rawurlencode($serviceCity)],
        ];
        if ($isInteriorDesignLocalityPage) {
            array_unshift($cityPlanningLinks, ['label' => 'Interior designers across ' . $serviceCity, 'href' => '/' . $serviceLocality['city_slug'] . '/interior-design']);
        }
        $serviceLabel = (string)$serviceRoute['name'] . ($serviceCity !== '' ? ' in ' . $serviceCity : '');
        $serviceSearchLabel = $isInteriorDesignCityPage
            ? 'an interior designer in ' . $serviceCity
            : strtolower($serviceLabel);
        $serviceFaqs = $isInteriorDesignLocalityPage ? [
            ['question' => 'How do I find an interior designer in ' . $serviceLocality['name'] . '?', 'answer' => 'Share your property type, room list, budget range, possession date and locality. HomeInteriors360 uses these details to help connect you with relevant professionals serving ' . $serviceLocality['name'] . ' and ' . $serviceCity . '.'],
            ['question' => 'What affects interior design pricing in ' . $serviceLocality['name'] . '?', 'answer' => 'Pricing depends on home size, scope, materials, custom furniture, civil changes, access rules and finish level. Compare written room-wise scopes, inclusions, exclusions, taxes and timelines before choosing a professional.'],
            ['question' => 'Can I plan a full home, kitchen or renovation project?', 'answer' => 'Yes. You can share a full-home, modular kitchen, wardrobe, renovation or room-specific requirement. The selected professional will confirm site feasibility, exact scope, timeline and final pricing directly with you.'],
        ] : [
            ['question' => 'How do I find ' . $serviceSearchLabel . '?', 'answer' => 'Share your location, scope, timing and budget. HomeInteriors360 uses these details to help route your enquiry to suitable professionals.'],
            ['question' => 'Is the consultation free?', 'answer' => 'Submitting your requirement is free. Final scope, site visit, pricing, availability and service terms are agreed directly with the professional you choose.'],
            ['question' => 'What should I compare before choosing a professional?', 'answer' => 'Compare relevant experience, scope clarity, material or workmanship assumptions, timeline, warranty, payment schedule and communication before confirming work.'],
        ];
        if ($isHomeCleaningHub) {
            $serviceFaqs = [
                ['question' => 'What does a home cleaning service include?', 'answer' => 'The scope can include kitchen, bathroom, floor, window, appliance, sofa or upholstery cleaning depending on the brief. Confirm the rooms, surfaces, consumables, exclusions and disposal arrangement before booking.'],
                ['question' => 'How much does home cleaning cost in Delhi NCR?', 'answer' => 'Home cleaning pricing varies by BHK size, occupancy, furnishing, condition, access, required equipment and whether the job is deep, move-in, move-out or recurring cleaning. Share the details to receive a relevant quote.'],
                ['question' => 'How should I prepare for a deep cleaning visit?', 'answer' => 'Share the home size, locality, preferred date, furnished or vacant status and any priority areas. Keep valuables and fragile items aside, then confirm access, parking and lift rules with the professional.'],
            ];
        }
        $offerSchema = [
            '@context' => 'https://schema.org', '@type' => 'Service',
            'name' => $serviceLabel,
            'description' => (string)($serviceRoute['short_description'] ?? '') . ' Get matched with suitable professionals through HomeInteriors360.',
            'url' => absoluteUrl($canonicalPath),
            'areaServed' => $isInteriorDesignLocalityPage
                ? ['@type' => 'Place', 'name' => $serviceLocality['name'] . ', ' . $serviceCity, 'containedInPlace' => ['@type' => 'City', 'name' => $serviceCity]]
                : ['@type' => 'City', 'name' => $serviceCity ?: 'Delhi NCR'],
            'provider' => ['@type' => 'Organization', 'name' => 'HomeInteriors360', 'url' => absoluteUrl('/')],
            'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'INR', 'availability' => 'https://schema.org/InStock'],
        ];
        $faqSchema = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(static fn(array $faq): array => ['@type' => 'Question', 'name' => $faq['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']]], $serviceFaqs)];
        $breadcrumbItems = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => absoluteUrl('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Home Services', 'item' => absoluteUrl('/home-services')],
        ];
        if ($isInteriorDesignLocalityPage) {
            $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 3, 'name' => 'Interior Design in ' . $serviceCity, 'item' => absoluteUrl('/' . $serviceLocality['city_slug'] . '/interior-design')];
            $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 4, 'name' => 'Interior Designers in ' . $serviceLocality['name'], 'item' => absoluteUrl($canonicalPath)];
        } else {
            $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $serviceLabel, 'item' => absoluteUrl($canonicalPath)];
        }
        $breadcrumbSchema = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $breadcrumbItems];
        $editorialContent = SiteRepository::homeServiceEditorialContent($serviceRoute, $serviceCity);
        if ($isInteriorDesignLocalityPage) {
            $editorialContent = [
                'heading' => 'Interior design in ' . $serviceLocality['name'] . ', ' . $serviceCity,
                'paragraphs' => [
                    'Homes in ' . $serviceLocality['name'] . ' benefit from an interior brief that starts with the real space: the rooms to be completed, the storage you need, the expected possession or renovation date, and the design language you want to live with. For apartments, builder floors and independent homes alike, a useful first conversation should identify the work that is essential now and the pieces that can be phased later.',
                    'A clear, room-wise scope helps you compare interior designers serving ' . $serviceLocality['name'] . ' on more than a headline estimate. Ask each professional to separate modular work, custom carpentry, civil changes, electrical work, lighting, loose furniture and taxes. Confirm material grades, hardware, timelines, site access requirements and exclusions before making a decision.',
                    'HomeInteriors360 helps homeowners start with a locality-aware requirement and connect with professionals who serve ' . $serviceLocality['name'] . ' and the wider ' . $serviceCity . ' area. Final design, site assessment, availability, pricing and execution terms are agreed directly with the professional you choose.',
                ],
            ];
        }
        render('public/home-service-detail', [
            'title' => $serviceTitle,
            'metaDescription' => $isInteriorDesignLocalityPage
                ? 'Find interior designers in ' . $serviceLocality['name'] . ', ' . $serviceCity . ' for home interiors, kitchens, wardrobes, renovation and decor. Get a free consultation.'
                : ($isInteriorDesignCityPage
                ? 'Find interior designers in ' . $serviceCity . ' for home interiors, modular kitchens, wardrobes, renovation, decor and turnkey design. Get a free consultation.'
                : ($isHomeCleaningHub
                    ? 'Find home cleaning services in Delhi NCR for deep cleaning, move-in cleaning, kitchens, bathrooms, sofas and carpets. Share your requirement for free quotes.'
                    : ($isInteriorDesignHub
                    ? 'Compare interior designers for home interiors, modular kitchens, wardrobes, renovation and decor across Delhi NCR. Get a free design consultation.'
                    : 'Find ' . strtolower((string)$serviceRoute['name']) . ($serviceCity !== '' ? ' in ' . $serviceCity : ' across Delhi NCR') . '. Share your requirement and get free quotes from suitable professionals.'))),
            'canonicalUrl' => absoluteUrl($canonicalPath),
            'active' => 'home-services',
            'content' => $content,
            'service' => $serviceRoute,
            'serviceHeading' => $serviceHeading,
            'editorialContent' => $editorialContent,
            'form' => $form,
            'providers' => $providers,
            'cities' => $availableServiceCities,
            'relatedServices' => $relatedServices,
            'cityPlanningLinks' => $cityPlanningLinks,
            'locality' => $serviceLocality,
            // City hubs expose the full directory. A locality page keeps nearby
            // discovery useful without rendering hundreds of links in one page.
            'localityLinks' => $isInteriorDesignCityPage
                ? array_slice(array_values(array_filter(
                    LocationLandingCatalog::localitiesForCity(slugify($serviceCity)),
                    static fn(array $area): bool => !$isInteriorDesignLocalityPage || (string)$area['slug'] !== (string)$serviceLocality['slug']
                )), 0, $isInteriorDesignLocalityPage ? 12 : null)
                : [],
            'serviceFaqs' => $serviceFaqs,
            'selectedCity' => $serviceCity,
            'structuredData' => [$offerSchema, $faqSchema, $breadcrumbSchema],
        ]);
        exit;
    }

    if ($path === '/admin/home-services') {
        Auth::requireAdmin();
        $rows = [];
        $adminError = '';
        try {
            $rows = SiteRepository::homeServiceAdminRows();
        } catch (Throwable) {
            $adminError = 'Home Services tables are not available yet. Run the latest database migration on live.';
        }
        render('admin/home-services', [
            'title' => 'Home Services Catalogue | HomeInteriors360',
            'metaDescription' => 'Manage home service categories, enquiry forms and city availability.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin', 'content' => $content, 'rows' => $rows,
            'categories' => array_values(array_filter($rows, static fn(array $row): bool => ($row['node_type'] ?? '') === 'category')),
            'services' => array_values(array_filter($rows, static fn(array $row): bool => ($row['node_type'] ?? '') === 'service')),
            'adminError' => $adminError,
        ]);
        exit;
    }

    if ($path === '/professionals' || preg_match('#^/professionals/([a-z0-9-]+)$#i', $path, $match)) {
        $alias = $match[1] ?? null;
        if ($alias !== null) {
            $pro = SiteRepository::getProBySlug((string)$alias);
            if ($pro) {
                $profileData = SiteRepository::proProfileData((int)$pro['id']);
                $profileUrl = absoluteUrl('/professionals/' . (string)$pro['slug']);
                $profileSchema = [
                    '@context' => 'https://schema.org',
                    '@type' => ['ProfessionalService', 'LocalBusiness'],
                    'name' => (string)$pro['full_name'],
                    'url' => $profileUrl,
                    'image' => !empty($pro['profile_image']) ? absoluteUrl((string)$pro['profile_image']) : absoluteUrl('/logo.png'),
                    'telephone' => (string)($pro['phone'] ?? '+91-9540573661'),
                    'email' => (string)($pro['email'] ?? 'jitender@homeinteriors360.com'),
                    'address' => ['@type' => 'PostalAddress', 'addressLocality' => (string)($pro['city'] ?? ''), 'addressCountry' => 'IN'],
                    'areaServed' => (string)($pro['city'] ?? 'Delhi NCR'),
                    'description' => (string)($pro['bio'] ?? $pro['profile_description'] ?? ''),
                ];
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
                    'editorialContent' => SiteRepository::professionalEditorialContent($pro, $profileData),
                    'canonicalUrl' => $profileUrl,
                    'structuredData' => [$profileSchema],
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
            $pros = SiteRepository::listPros($initialFilters);
            $itemListSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => $aliasData['title'],
                'itemListElement' => array_map(static fn(array $pro, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => (string)($pro['full_name'] ?? ''),
                    'url' => absoluteUrl('/professionals/' . (string)($pro['slug'] ?? '')),
                ], array_slice($pros, 0, 20), array_keys(array_slice($pros, 0, 20))),
            ];
            $collectionSchema = [
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => $aliasData['title'],
                'description' => $aliasData['subtitle'],
                'url' => absoluteUrl('/professionals/' . (string)$alias),
            ];
            render('public/professionals', [
                'title' => $aliasData['title'] . ' | ' . (string)SiteRepository::content('seo.directory.title', 'Find Professionals'),
                'metaDescription' => $aliasData['subtitle'],
                'canonicalUrl' => absoluteUrl('/professionals/' . (string)$alias),
                'active' => 'directory',
                'content' => $content,
                'pros' => $pros,
                'filterOptions' => SiteRepository::proFilterOptions(),
                'initialFilters' => $initialFilters,
                'directoryTitle' => $aliasData['title'],
                'directorySubtitle' => $aliasData['subtitle'],
                'structuredData' => [$collectionSchema, $itemListSchema],
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
        $pros = SiteRepository::listPros($initialFilters);
        $itemListSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Find Interior Designers & Architects Near You',
            'itemListElement' => array_map(static fn(array $pro, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => (string)($pro['full_name'] ?? ''),
                'url' => absoluteUrl('/professionals/' . (string)($pro['slug'] ?? '')),
            ], array_slice($pros, 0, 20), array_keys(array_slice($pros, 0, 20))),
        ];
        $collectionSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Find Interior Designers & Architects Near You',
            'description' => 'Browse verified architects, interior designers, and contractors by city, work type, budget, experience, and rating.',
            'url' => absoluteUrl('/professionals'),
        ];
        render('public/professionals', [
            'title' => 'Find Interior Designers & Architects in Delhi NCR | HomeInteriors360',
            'metaDescription' => 'Find interior designers, architects, contractors and turnkey firms in Delhi NCR. Compare portfolios, experience, work type, city and project budget.',
            'canonicalUrl' => absoluteUrl('/professionals'),
            'active' => 'directory',
            'content' => $content,
            'pros' => $pros,
            'filterOptions' => SiteRepository::proFilterOptions(),
            'initialFilters' => $initialFilters,
            'directoryTitle' => 'Find Interior Designers & Architects Near You',
            'directorySubtitle' => 'Compare verified interior designers, architects, contractors and turnkey firms by city, work type, portfolio, budget and experience.',
            'structuredData' => [$collectionSchema, $itemListSchema],
        ]);
        exit;
    }

    $calculatorPages = [
        '/cost-calculator' => [
            'nav_label' => 'All calculators',
            'title' => 'Home Interior Cost Calculator',
            'seo_title' => 'Home Interior Cost Calculator | HomeInteriors360',
            'subtitle' => 'Estimate full home, kitchen, wardrobe, room-wise and BHK-wise interior budgets in a guided calculator.',
            'requirement' => 'Design Cost Calculator',
        ],
        '/full-home-cost-calculator' => [
            'nav_label' => 'Full Home',
            'title' => 'Full Home Interior Cost Calculator',
            'seo_title' => 'Full Home Interior Cost Calculator | HomeInteriors360',
            'subtitle' => 'Estimate a complete home interior budget by BHK, home size, rooms and finish package.',
            'requirement' => 'Full Home Cost Calculator',
        ],
        '/kitchen-cost-calculator' => [
            'nav_label' => 'Kitchen',
            'title' => 'Kitchen Cost Calculator',
            'seo_title' => 'Kitchen Cost Calculator | Modular Kitchen Estimate',
            'subtitle' => 'Estimate modular kitchen pricing by BHK context, home size and material package.',
            'requirement' => 'Kitchen Cost Calculator',
            'rooms' => ['Kitchen'],
            'fixed_rooms' => ['Kitchen'],
        ],
        '/modular-kitchen-cost-calculator' => [
            'nav_label' => 'Modular Kitchen',
            'title' => 'Modular Kitchen Cost Calculator',
            'seo_title' => 'Modular Kitchen Cost Calculator | HomeInteriors360',
            'subtitle' => 'Calculate a practical starting budget for modular cabinets, countertop planning and kitchen accessories.',
            'requirement' => 'Modular Kitchen Cost Calculator',
            'rooms' => ['Kitchen'],
            'fixed_rooms' => ['Kitchen'],
        ],
        '/wardrobe-cost-calculator' => [
            'nav_label' => 'Wardrobe',
            'title' => 'Wardrobe Cost Calculator',
            'seo_title' => 'Wardrobe Cost Calculator | HomeInteriors360',
            'subtitle' => 'Estimate wardrobe, loft and storage pricing for bedrooms by package and home context.',
            'requirement' => 'Wardrobe Cost Calculator',
            'rooms' => ['Wardrobe', 'Master Bedroom', 'Bedroom 2', 'Bedroom 3'],
            'fixed_rooms' => ['Wardrobe'],
        ],
        '/1-bhk-cost-calculator' => [
            'nav_label' => '1 BHK',
            'title' => '1 BHK Interior Cost Calculator',
            'seo_title' => '1 BHK Interior Cost Calculator | HomeInteriors360',
            'subtitle' => 'Estimate 1 BHK interior cost for compact homes, rental upgrades and starter apartments.',
            'requirement' => '1 BHK Cost Calculator',
            'floor_plans' => ['1BHK'],
            'fixed_floor_plan' => '1BHK',
            'rooms' => ['Living Room', 'Kitchen', 'Master Bedroom', 'Bathroom', 'Pooja Unit'],
        ],
        '/2-bhk-cost-calculator' => [
            'nav_label' => '2 BHK',
            'title' => '2 BHK Interior Cost Calculator',
            'seo_title' => '2 BHK Interior Cost Calculator | HomeInteriors360',
            'subtitle' => 'Estimate 2 BHK interior cost for modular kitchen, wardrobes, living room and bedroom work.',
            'requirement' => '2 BHK Cost Calculator',
            'floor_plans' => ['2BHK'],
            'fixed_floor_plan' => '2BHK',
            'rooms' => ['Living Room', 'Kitchen', 'Master Bedroom', 'Bedroom 2', 'Bathroom', 'Pooja Unit'],
        ],
        '/3-bhk-cost-calculator' => [
            'nav_label' => '3 BHK',
            'title' => '3 BHK Interior Cost Calculator',
            'seo_title' => '3 BHK Interior Cost Calculator | HomeInteriors360',
            'subtitle' => 'Estimate 3 BHK interior cost for full home storage, kitchen, living, bedroom and pooja unit scope.',
            'requirement' => '3 BHK Cost Calculator',
            'floor_plans' => ['3BHK'],
            'fixed_floor_plan' => '3BHK',
        ],
        '/4-bhk-cost-calculator' => [
            'nav_label' => '4 BHK',
            'title' => '4 BHK Interior Cost Calculator',
            'seo_title' => '4 BHK Interior Cost Calculator | HomeInteriors360',
            'subtitle' => 'Estimate 4 BHK interior cost for large apartments and premium full-home execution.',
            'requirement' => '4 BHK Cost Calculator',
            'floor_plans' => ['4BHK'],
            'fixed_floor_plan' => '4BHK',
        ],
        '/living-room-cost-calculator' => [
            'nav_label' => 'Living Room',
            'title' => 'Living Room Cost Calculator',
            'seo_title' => 'Living Room Interior Cost Calculator | HomeInteriors360',
            'subtitle' => 'Estimate living room interior cost for TV units, seating wall, storage and decor scope.',
            'requirement' => 'Living Room Cost Calculator',
            'rooms' => ['Living Room'],
            'fixed_rooms' => ['Living Room'],
        ],
        '/bathroom-cost-calculator' => [
            'nav_label' => 'Bathroom',
            'title' => 'Bathroom Cost Calculator',
            'seo_title' => 'Bathroom Interior Cost Calculator | HomeInteriors360',
            'subtitle' => 'Estimate bathroom vanity, mirror, storage and dry-zone upgrade costs.',
            'requirement' => 'Bathroom Cost Calculator',
            'rooms' => ['Bathroom'],
            'fixed_rooms' => ['Bathroom'],
        ],
        '/pooja-room-cost-calculator' => [
            'nav_label' => 'Pooja Room',
            'title' => 'Pooja Room Cost Calculator',
            'seo_title' => 'Pooja Room Cost Calculator | HomeInteriors360',
            'subtitle' => 'Estimate compact and premium pooja unit budgets for apartments and homes.',
            'requirement' => 'Pooja Room Cost Calculator',
            'rooms' => ['Pooja Unit'],
            'fixed_rooms' => ['Pooja Unit'],
        ],
        '/false-ceiling-cost-calculator' => [
            'nav_label' => 'False Ceiling',
            'title' => 'False Ceiling Cost Calculator',
            'seo_title' => 'False Ceiling Cost Calculator | HomeInteriors360',
            'subtitle' => 'Estimate gypsum false ceiling, cove lighting and ceiling detail costs.',
            'requirement' => 'False Ceiling Cost Calculator',
            'rooms' => ['False Ceiling'],
            'fixed_rooms' => ['False Ceiling'],
        ],
        '/painting-cost-calculator' => [
            'nav_label' => 'Painting',
            'title' => 'Painting Cost Calculator',
            'seo_title' => 'Home Painting Cost Calculator | HomeInteriors360',
            'subtitle' => 'Estimate interior painting budgets for putty, primer and premium wall finishes.',
            'requirement' => 'Painting Cost Calculator',
            'rooms' => ['Painting'],
            'fixed_rooms' => ['Painting'],
        ],
    ];

    if (isset($calculatorPages[$path])) {
        $calculatorPage = $calculatorPages[$path];
        $calculatorSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => (string)($calculatorPage['seo_title'] ?? $calculatorPage['title']),
            'url' => absoluteUrl($path),
            'description' => (string)$calculatorPage['subtitle'],
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => 'HomeInteriors360',
                'url' => absoluteUrl('/'),
            ],
            'mainEntity' => [
                '@type' => 'Service',
                'name' => (string)$calculatorPage['title'],
                'serviceType' => 'Home interior cost estimation',
                'description' => (string)$calculatorPage['subtitle'],
                'provider' => [
                    '@type' => 'Organization',
                    'name' => 'HomeInteriors360',
                    'url' => absoluteUrl('/'),
                ],
                'areaServed' => [
                    '@type' => 'AdministrativeArea',
                    'name' => 'Delhi NCR',
                ],
            ],
        ];
        $calculatorFaqSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'How accurate is this ' . strtolower((string)$calculatorPage['title']) . '?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'It gives a practical starting range based on BHK type, home size, selected rooms and package level. Final pricing depends on site measurement, material selection, hardware brands, civil changes and custom design details.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Can I compare designers after using the calculator?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Yes. You can submit your estimate and request a free design consultation. HomeInteriors360 can connect you with relevant interior designers, architects or contractors in your city.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'What affects interior design cost the most?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'The biggest cost drivers are scope of work, number of wardrobes, kitchen size, finish quality, hardware, countertop material, false ceiling area, lighting, painting and any civil or renovation work.',
                    ],
                ],
            ],
        ];
        render('public/calculator', [
            'title' => (string)($calculatorPage['seo_title'] ?? ($calculatorPage['title'] . ' | HomeInteriors360')),
            'metaDescription' => (string)$calculatorPage['subtitle'],
            'canonicalUrl' => absoluteUrl($path),
            'active' => 'calculator',
            'content' => $content,
            'calculatorConfig' => $calculatorPage,
            'calculatorPages' => $calculatorPages,
            'structuredData' => [$calculatorSchema, $calculatorFaqSchema],
        ]);
        exit;
    }

    if ($path === '/ai-room-visualizer') {
        header('X-Robots-Tag: noindex, nofollow', true);
        redirectTo('/');
    }

    if ($path === '/pricing') {
        $softwareSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'SoftwareApplication',
            'name' => 'BOQ/ Quotation Builder',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web',
            'description' => 'Quotation builder and proposal generator for interior designers with PDF proposals, GST, discounts, milestones and WhatsApp sharing.',
            'offers' => [
                '@type' => 'Offer',
                'price' => '999',
                'priceCurrency' => 'INR',
                'category' => 'subscription',
                'url' => absoluteUrl('/pricing#designer-quotation-builder'),
            ],
        ];
        render('public/pricing', [
            'title' => (string)SiteRepository::content('seo.pricing.title', 'BOQ/ Quotation Builder | HomeInteriors360'),
            'metaDescription' => (string)SiteRepository::content('seo.pricing.description', 'Interior designer quotation builder pricing with 15 days free, PDF proposals, GST, payment milestones, lead tools and ₹999/month after trial.'),
            'canonicalUrl' => absoluteUrl('/pricing'),
            'active' => 'pricing',
            'content' => $content,
            'reviews' => SiteRepository::pricingReviews(),
            'structuredData' => [$softwareSchema],
        ]);
        exit;
    }

    if ($path === '/lead-marketplace') {
        $leadServiceSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => 'Buy Interior Design Leads',
            'description' => 'Buy filtered homeowner interior design leads by city, society, budget, work type, and available quantity.',
            'provider' => ['@type' => 'Organization', 'name' => 'HomeInteriors360', 'url' => absoluteUrl('/')],
            'areaServed' => ['Delhi NCR', 'Gurgaon', 'Delhi', 'Noida', 'Greater Noida', 'Ghaziabad', 'Faridabad'],
            'offers' => ['@type' => 'AggregateOffer', 'priceCurrency' => 'INR', 'lowPrice' => '0', 'highPrice' => '100', 'url' => absoluteUrl('/lead-marketplace')],
        ];
        render('public/lead-marketplace', [
            'title' => 'Buy Filtered Interior Design Leads | HomeInteriors360',
            'metaDescription' => 'Buy interior design leads by city, society, budget, work type and lead count with transparent cart pricing and secure checkout.',
            'canonicalUrl' => absoluteUrl('/lead-marketplace'),
            'active' => 'leads',
            'content' => $content,
            'structuredData' => [$leadServiceSchema],
        ]);
        exit;
    }

    $singleLeadFilterRoute = preg_match('#^/single-lead/(?:in-|work-|budget-)#', $path) === 1;
    if ($path === '/single-lead' || $singleLeadFilterRoute) {
        $routeSegments = $singleLeadFilterRoute ? explode('/', trim(substr($path, strlen('/single-lead/')), '/')) : [];
        $filters = SiteRepository::singleLeadSaleFiltersFromPath($routeSegments);
        if ($path === '/single-lead') {
            $requestedFilters = [
                'city' => trim((string)($_GET['city'] ?? '')),
                'requirement' => trim((string)($_GET['work'] ?? $_GET['requirement'] ?? '')),
                'budget' => trim((string)($_GET['budget'] ?? '')),
            ];
            $requestedFilters = array_filter($requestedFilters, static fn(string $value): bool => $value !== '');
            if ($requestedFilters !== []) {
                $candidatePath = SiteRepository::singleLeadSaleListingPath($requestedFilters);
                $resolvedFilters = SiteRepository::singleLeadSaleFiltersFromPath(explode('/', trim(substr($candidatePath, strlen('/single-lead/')), '/')));
                if ($resolvedFilters !== []) {
                    header('Location: ' . $candidatePath, true, 302);
                    exit;
                }
            }
        }
        $canonicalPath = SiteRepository::singleLeadSaleListingPath($filters);
        if ($singleLeadFilterRoute && $path !== $canonicalPath) {
            header('Location: ' . $canonicalPath, true, 301);
            exit;
        }
        $sales = SiteRepository::listSingleLeadSales(false, $filters);
        $filterOptions = SiteRepository::singleLeadSaleFilterOptions();
        $titleParts = [];
        if (!empty($filters['requirement'])) {
            $titleParts[] = (string)$filters['requirement'];
        }
        $titleParts[] = 'Interior Leads';
        if (!empty($filters['city'])) {
            $titleParts[] = 'in ' . (string)$filters['city'];
        }
        if (!empty($filters['budget'])) {
            $titleParts[] = 'for ' . (string)$filters['budget'] . ' budgets';
        }
        $listingHeading = implode(' ', $titleParts);
        $listingDescription = 'Buy selected individual interior design leads priced from ₹199 to ₹999 by project quality.'
            . (!empty($filters['city']) ? ' Browse available homeowner enquiries in ' . (string)$filters['city'] . '.' : '')
            . (!empty($filters['requirement']) ? ' This page focuses on ' . (string)$filters['requirement'] . ' requirements.' : '')
            . ' Phone numbers stay masked until secure payment is completed.';
        $breadcrumbItems = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => absoluteUrl('/')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'Single Interior Leads', 'item' => absoluteUrl('/single-lead')],
        ];
        if ($filters !== []) {
            $breadcrumbItems[] = ['@type' => 'ListItem', 'position' => 3, 'name' => $listingHeading, 'item' => absoluteUrl($canonicalPath)];
        }
        $itemList = array_slice($sales, 0, 50);
        $publicSaleTitle = static fn(array $sale): string => trim((string)preg_replace('/\s*#\d+\b/', '', (string)($sale['title'] ?? ''))) ?: 'Single Interior Lead';
        render('public/single-lead-list', [
            'title' => $listingHeading . ' | HomeInteriors360',
            'metaDescription' => $listingDescription,
            'canonicalUrl' => absoluteUrl($canonicalPath),
            'active' => 'leads',
            'content' => $content,
            'sales' => $sales,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
            'listingHeading' => $listingHeading,
            'listingDescription' => $listingDescription,
            'listingPath' => $canonicalPath,
            'buyer' => buyerUser(),
            'autoOpenSaleId' => (int)($_GET['sale'] ?? 0),
            'structuredData' => [[
                '@context' => 'https://schema.org',
                '@type' => 'CollectionPage',
                'name' => $listingHeading,
                'description' => $listingDescription,
                'url' => absoluteUrl($canonicalPath),
            ], [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $breadcrumbItems,
            ], [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => $listingHeading,
                'numberOfItems' => count($sales),
                'itemListElement' => array_map(static fn(array $sale, int $position): array => [
                    '@type' => 'ListItem',
                    'position' => $position + 1,
                    'name' => $publicSaleTitle($sale),
                    'url' => absoluteUrl('/single-lead/' . (string)($sale['slug'] ?? '')),
                ], $itemList, array_keys($itemList)),
            ]],
        ]);
        exit;
    }

    if (preg_match('#^/single-lead/([a-z0-9-]+)$#i', $path, $match)) {
        $sale = SiteRepository::getSingleLeadSaleBySlug((string)$match[1], true);
        if (!$sale) {
            http_response_code(404);
            echo 'Single lead not found or no longer available.';
            exit;
        }
        header('Location: /single-lead?sale=' . (int)$sale['id'], true, 302);
        exit;
    }

    if ($path === '/delhi-ncr-founding-partners') {
        $foundingSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => 'Delhi NCR Founding Partner Programme',
            'description' => 'Professional partner programme for interior designers, architects, contractors and modular firms seeking matched Delhi NCR project opportunities.',
            'provider' => ['@type' => 'Organization', 'name' => 'HomeInteriors360', 'url' => absoluteUrl('/')],
            'areaServed' => ['Delhi NCR', 'Gurgaon', 'Delhi', 'Noida', 'Faridabad', 'Ghaziabad'],
        ];
        render('public/founding-partners', [
            'title' => 'Delhi NCR Founding Partner Programme | HomeInteriors360',
            'metaDescription' => 'Join HomeInteriors360 as a Delhi NCR founding professional partner for fresh location-matched interior project opportunities.',
            'canonicalUrl' => absoluteUrl('/delhi-ncr-founding-partners'),
            'active' => 'pricing',
            'content' => $content,
            'structuredData' => [$foundingSchema],
        ]);
        exit;
    }

    if ($path === '/for-professionals') {
        $proServiceSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => 'Growth Platform for Interior Designers and Architects',
            'description' => 'Create profile, upload portfolio, get trial leads, buy homeowner enquiries and use quotation tools on HomeInteriors360.',
            'provider' => ['@type' => 'Organization', 'name' => 'HomeInteriors360', 'url' => absoluteUrl('/')],
            'serviceType' => 'Professional growth platform for interior businesses',
        ];
        render('public/for-professionals', [
            'title' => 'Growth Platform for Interior Designers & Architects | HomeInteriors360',
            'metaDescription' => 'Create a professional profile, upload portfolio work, get 3 free leads, buy preferred homeowner leads, and use proposal tools on HomeInteriors360.',
            'canonicalUrl' => absoluteUrl('/for-professionals'),
            'active' => 'professionals-growth',
            'content' => $content,
            'structuredData' => [$proServiceSchema],
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
        $ideas = SiteRepository::listDesignIdeas($filters);
        $designCollectionSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => 'Interior Design Ideas',
            'description' => 'Browse interior design ideas by room, colour, city, style and layout.',
            'url' => absoluteUrl('/design-ideas'),
        ];
        $designItemListSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Interior Design Ideas',
            'itemListElement' => array_map(static fn(array $idea, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => (string)($idea['name'] ?? ''),
                'url' => absoluteUrl('/design-ideas/idea/' . (string)($idea['slug'] ?? '')),
            ], array_slice($ideas, 0, 20), array_keys(array_slice($ideas, 0, 20))),
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
            'ideas' => $ideas,
            'aliases' => SiteRepository::listDesignIdeaAliases(),
            'sections' => SiteRepository::listDesignIdeaSections(),
            'filterOptions' => SiteRepository::designIdeaFilterOptions(),
            'activeFilters' => $filters,
            'structuredData' => [$breadcrumbSchema, $designCollectionSchema, $designItemListSchema],
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
            'ogImage' => !empty($urlAlias['image_url']) ? absoluteUrl((string)$urlAlias['image_url']) : ($image !== '' ? absoluteUrl($image) : absoluteUrl('/social-share-v2.jpg')),
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
        $designCollectionSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => (string)$alias['title'],
            'description' => (string)($alias['meta_description'] ?: $alias['subtitle']),
            'url' => absoluteUrl('/design-ideas/' . (string)$alias['slug']),
        ];
        $designItemListSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => (string)$alias['title'],
            'itemListElement' => array_map(static fn(array $idea, int $index): array => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => (string)($idea['name'] ?? ''),
                'url' => absoluteUrl('/design-ideas/idea/' . (string)($idea['slug'] ?? '')),
            ], array_slice($page['ideas'], 0, 20), array_keys(array_slice($page['ideas'], 0, 20))),
        ];
        render('public/design-ideas', [
            'title' => (string)($urlAlias['meta_title'] ?? ($alias['meta_title'] ?: $alias['title'] . ' | HomeInteriors360')),
            'metaDescription' => (string)($urlAlias['meta_description'] ?? ($alias['meta_description'] ?: $alias['subtitle'])),
            'canonicalUrl' => absoluteUrl((string)($urlAlias['canonical_url'] ?: '/design-ideas/' . $alias['slug'])),
            'metaRobots' => (string)($urlAlias['robots'] ?: 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1'),
            'ogImage' => !empty($urlAlias['image_url']) ? absoluteUrl((string)$urlAlias['image_url']) : (!empty($alias['hero_image']) ? absoluteUrl((string)$alias['hero_image']) : absoluteUrl('/social-share-v2.jpg')),
            'active' => 'design-ideas',
            'content' => $content,
            'globalAlias' => $urlAlias,
            'alias' => $alias,
            'ideas' => $page['ideas'],
            'aliases' => SiteRepository::listDesignIdeaAliases(),
            'sections' => SiteRepository::listDesignIdeaSections(),
            'filterOptions' => SiteRepository::designIdeaFilterOptions(),
            'activeFilters' => $activeFilters,
            'structuredData' => [$breadcrumbSchema, $designCollectionSchema, $designItemListSchema],
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
            'ogImage' => $image !== '' ? absoluteUrl($image) : absoluteUrl('/social-share-v2.jpg'),
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

    if ($path === '/register-interior-designer') {
        header('Location: /interior-designer-registration', true, 301);
        exit;
    }

    if ($path === '/interior-designer-registration') {
        Auth::start();
        $googleIdentity = isset($_SESSION['google_designer_identity']) && is_array($_SESSION['google_designer_identity'])
            ? $_SESSION['google_designer_identity']
            : null;
        $registrationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            'name' => 'Register as an Interior Designer',
            'description' => 'Register an interior design firm, create a professional profile, upload portfolio work and access homeowner leads on HomeInteriors360.',
            'provider' => ['@type' => 'Organization', 'name' => 'HomeInteriors360', 'url' => absoluteUrl('/')],
            'serviceType' => 'Interior designer registration and lead access',
        ];
        render('public/interior-designer-registration', [
            'title' => 'Register as Interior Designer | HomeInteriors360',
            'metaDescription' => 'Register your interior design firm, create a professional profile, upload portfolio work, and get 3 free homeowner leads on HomeInteriors360.',
            'canonicalUrl' => absoluteUrl('/interior-designer-registration'),
            'active' => 'pricing',
            'content' => $content,
            'standardOptions' => SiteRepository::professionalStandardOptions(),
            'membershipPlans' => SiteRepository::designerMembershipPlans(),
            'googleIdentity' => $googleIdentity,
            'googleConfigured' => GoogleOAuthService::configured(),
            'structuredData' => [$registrationSchema],
        ]);
        exit;
    }

    if ($path === '/lead-checkout') {
        redirectTo('/lead-cart');
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
        $projectImages = array_values(array_filter((array)($project['media_json'] ?? [])));
        $portfolioSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'CreativeWork',
            'name' => (string)$project['project_name'],
            'url' => absoluteUrl('/portfolio/' . (string)$project['slug']),
            'description' => (string)($project['project_description'] ?? ''),
            'creator' => ['@type' => 'Organization', 'name' => (string)($project['pro_name'] ?? 'HomeInteriors360 professional')],
            'contentLocation' => ['@type' => 'Place', 'name' => (string)($project['location'] ?? '')],
            'image' => array_map(static fn(string $url): string => absoluteUrl($url), $projectImages),
        ];
        $imageSchema = $projectImages ? [
            '@context' => 'https://schema.org',
            '@type' => 'ImageObject',
            'contentUrl' => absoluteUrl((string)$projectImages[0]),
            'name' => (string)$project['project_name'],
            'description' => (string)($project['work_type'] ?? 'Interior design project'),
        ] : null;
        render('public/portfolio-detail', [
            'title' => $project['project_name'] . ' | ' . (string)SiteRepository::content('seo.portfolio.title_suffix', 'HomeInteriors360'),
            'metaDescription' => sprintf(
                '%s by %s in %s. See project cost, timeline, materials, images, and testimonial details.',
                (string)$project['project_name'],
                (string)($project['pro_name'] ?? 'this professional'),
                (string)($project['location'] ?? 'Gurgaon')
            ),
            'canonicalUrl' => absoluteUrl('/portfolio/' . (string)$project['slug']),
            'ogImage' => $projectImages ? absoluteUrl((string)$projectImages[0]) : absoluteUrl('/social-share-v2.jpg'),
            'active' => 'directory',
            'content' => $content,
            'project' => $project,
            'relatedProjects' => SiteRepository::listOtherProjectsByPro((int)$project['pro_id'], (string)$project['slug']),
            'structuredData' => array_values(array_filter([$portfolioSchema, $imageSchema])),
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
        $registrationLeadDelivery = SiteRepository::autoAssignDesignerRegistrationLeads($user, $designerBuyer);
        render('designer/dashboard', [
            'title' => 'Designer Workspace | HomeInteriors360',
            'metaDescription' => 'Designer-only workspace for assigned leads, quotations, and proposal generator.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'stats' => SiteRepository::designerDashboardStats((int)$user['pro_id']),
            'subscription' => SiteRepository::designerSubscriptionStatus($user),
            'membership' => SiteRepository::designerMembershipStatus($user),
            'membershipPlans' => SiteRepository::designerMembershipPlans(),
            'profileChecklist' => SiteRepository::designerProfileChecklist((int)$user['pro_id']),
            'buyer' => $designerBuyer,
            'freeLeadEligible' => false,
            'registrationLeadDelivery' => $registrationLeadDelivery,
        ]);
        exit;
    }

    if ($path === '/designer/claim-registration-leads') {
        redirectTo('/designer');
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

    if ($path === '/designer/profile') {
        $user = Auth::requireDesigner();
        $profile = SiteRepository::designerProfessional((int)$user['pro_id']);
        if (!$profile) {
            redirectTo('/designer');
        }
        render('designer/profile', [
            'title' => 'My Professional Profile | HomeInteriors360',
            'metaDescription' => 'Update your HomeInteriors360 professional profile.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'profile' => $profile,
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

    if ($path === '/designer/alert-preferences') {
        $user = Auth::requireDesigner();
        render('designer/alert-preferences', [
            'title' => 'Lead Alert Preferences | HomeInteriors360',
            'metaDescription' => 'Manage lead matching and alert preferences.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'preferences' => LeadMatchingService::getPreferences((int)$user['pro_id']),
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

    if ($path === '/designer/rate-card') {
        $user = Auth::requireDesigner();
        render('admin/quotation-rate-card', [
            'title' => 'My BOQ Rate Card | HomeInteriors360',
            'metaDescription' => 'Manage your private BOQ rates.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'rateCards' => SiteRepository::quotationRateCards(['designer_id' => (int)$user['pro_id']]),
            'packages' => SiteRepository::quotationPackages(true),
            'professionals' => array_values(array_filter(SiteRepository::professionalOptions(), static fn(array $pro): bool => (int)$pro['id'] === (int)$user['pro_id'])),
            'portalBase' => '/designer',
            'isDesignerPortal' => true,
            'designerId' => (int)$user['pro_id'],
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
        }
        if (!$quote && !SiteRepository::designerCanCreateQuotation($user)) {
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
        } elseif (!$quote && !empty($_GET['lead_id'])) {
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

    if (in_array($path, ['/admin/content', '/admin/content-manager'], true)) {
        Auth::requireAdmin();
        $items = [];
        $adminError = '';
        try {
            $items = SiteRepository::contentList();
        } catch (Throwable $e) {
            $adminError = 'Content records could not be loaded. Please check the site_content table on live.';
        }
        render('admin/content', [
            'title' => (string)SiteRepository::content('admin.content.title', 'Content Manager'),
            'metaDescription' => 'Update homepage content, logos, SEO fields, and reusable site copy from the admin panel.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'items' => $items,
            'adminError' => $adminError,
        ]);
        exit;
    }

    if (in_array($path, ['/admin/articles', '/admin/article-manager'], true)) {
        Auth::requireAdmin();
        $articles = [];
        $adminError = '';
        try {
            $articles = SiteRepository::listArticles([], true);
            foreach ($articles as &$article) {
                $article['faqs'] = SiteRepository::articleFaqs((int)$article['id']);
            }
            unset($article);
        } catch (Throwable $e) {
            $adminError = 'Article records could not be loaded. Please run the article CMS migration on live.';
        }
        render('admin/articles', [
            'title' => 'Article Content Management | HomeInteriors360',
            'metaDescription' => 'Create and manage SEO articles, city and work-type mappings, FAQs, cover images, and public article pages.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'articles' => $articles,
            'filterOptions' => SiteRepository::articleFilterOptions(),
            'adminError' => $adminError,
        ]);
        exit;
    }

    if ($path === '/admin/lead-alerts') {
        Auth::requireAdmin();
        $leadId = isset($_GET['lead_id']) ? (int)$_GET['lead_id'] : 0;
        render('admin/lead-alerts', [
            'title' => 'Lead Alerts & Founding Partners | HomeInteriors360',
            'metaDescription' => 'Manage Founding Partner applications, lead matching, alert waves, and notification delivery.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'settings' => LeadMatchingService::settings(),
            'partners' => LeadMatchingService::listFoundingPartners(),
            'matches' => $leadId > 0 ? LeadMatchingService::matchingReport($leadId) : [],
            'jobs' => LeadMatchingService::recentNotificationJobs(100),
        ]);
        exit;
    }

    if (in_array($path, ['/admin/leads', '/admin/lead-tracker'], true)) {
        Auth::requireAdmin();
        $leads = [];
        $adminError = '';
        try {
            $leads = SiteRepository::listLeads();
        } catch (Throwable $e) {
            $adminError = 'Lead records could not be loaded. Please check the leads table columns on live.';
        }
        render('admin/leads', [
            'title' => (string)SiteRepository::content('admin.leads.title', 'Lead Tracker'),
            'metaDescription' => 'Review and update incoming leads with status tracking for the HomeInteriors360 sales team.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'leads' => $leads,
            'adminError' => $adminError,
        ]);
        exit;
    }

    if ($path === '/admin/site-search') {
        Auth::requireAdmin();
        $filters = [
            'days' => max(1, min(365, (int)($_GET['days'] ?? 30))),
            'q' => trim((string)($_GET['q'] ?? '')),
        ];
        render('admin/site-search', [
            'title' => 'Search Analytics | HomeInteriors360',
            'metaDescription' => 'Review on-site searches and result clicks.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'filters' => $filters,
            'analytics' => SiteRepository::siteSearchAnalytics($filters),
        ]);
        exit;
    }

    if ($path === '/admin/single-lead-sales') {
        Auth::requireAdmin();
        $adminError = '';
        $sales = [];
        $availableLeads = [];
        try {
            $sales = SiteRepository::listSingleLeadSales(true);
            $availableLeads = SiteRepository::leadsAvailableForSingleSale();
        } catch (Throwable $e) {
            $adminError = 'Single lead sale panel could not be loaded. Please run the latest migration.';
        }
        render('admin/single-lead-sales', [
            'title' => 'Single Lead Sale Backend | HomeInteriors360',
            'metaDescription' => 'Create and manage single interior lead sale buckets.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'sales' => $sales,
            'availableLeads' => $availableLeads,
            'adminError' => $adminError,
        ]);
        exit;
    }

    if ($path === '/admin/leads/sample.xlsx') {
        Auth::requireAdmin();
        $sample = SiteRepository::leadUploadSampleXlsx();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="homeinteriors360-lead-upload-sample.xlsx"');
        header('Content-Length: ' . strlen($sample));
        echo $sample;
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

    if ($path === '/admin/checkout-recovery') {
        Auth::requireAdmin();
        render('admin/checkout-recovery', [
            'title' => 'Checkout Recovery',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'attempts' => SiteRepository::abandonedCheckouts((string)($_GET['status'] ?? 'open')),
            'statusFilter' => (string)($_GET['status'] ?? 'open'),
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

    if ($path === '/admin/designer-contact-queries') {
        Auth::requireAdmin();
        render('admin/designer-contact-queries', [
            'title' => 'Designer Contact Queries', 'metaDescription' => 'CEO office designer contact inbox.', 'metaRobots' => 'noindex,nofollow', 'active' => 'admin', 'content' => $content,
            'queries' => SiteRepository::listDesignerContactQueries(),
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
            'professionals' => SiteRepository::professionalOptions(),
            'filterOptions' => SiteRepository::designIdeaAdminOptions(),
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

    http_response_code(404);
    header('X-Robots-Tag: noindex, follow', true);
    render('public/not-found', [
        'title' => 'Page Not Found | HomeInteriors360',
        'metaDescription' => 'The requested page is unavailable. Explore HomeInteriors360 interior designers, design ideas, cost calculators, and consultation options.',
        'metaRobots' => 'noindex,follow',
        'canonicalUrl' => absoluteUrl('/'),
        'active' => '',
        'content' => $content,
    ]);
} catch (Throwable $e) {
    error_log(sprintf(
        '[HomeInteriors360] Public request failed: %s at %s for %s',
        $e->getMessage(),
        basename($e->getFile()) . ':' . $e->getLine(),
        $path
    ));
    if (str_starts_with($path, '/api/')) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    http_response_code(500);
    header('Cache-Control: no-store, max-age=0, must-revalidate', true);
    header('Pragma: no-cache', true);
    header('Surrogate-Control: no-store', true);
    header('X-Robots-Tag: noindex, nofollow', true);
    echo 'Server Error';
}
