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
            ['loc' => '/professionals', 'priority' => '0.8', 'changefreq' => 'weekly'],
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
        foreach (['interior-designer-in-gurgaon', 'interior-designer-in-delhi-ncr', 'architect-interior-designer-in-gurgaon', 'full-home-interior-designer-in-gurgaon', 'kitchen-interior-designer-in-gurgaon'] as $aliasPath) {
            $urls[] = ['loc' => '/professionals/' . $aliasPath, 'priority' => '0.65', 'changefreq' => 'weekly'];
        }
        foreach (Database::query('SELECT slug, updated_at FROM pros WHERE is_active = 1 ORDER BY updated_at DESC LIMIT 200') as $pro) {
            $urls[] = ['loc' => '/professionals/' . $pro['slug'], 'priority' => '0.55', 'changefreq' => 'weekly', 'lastmod' => substr((string)($pro['updated_at'] ?? $today), 0, 10)];
        }
        foreach (Database::query('SELECT slug, updated_at FROM projects ORDER BY updated_at DESC LIMIT 200') as $project) {
            $urls[] = ['loc' => '/portfolio/' . $project['slug'], 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => substr((string)($project['updated_at'] ?? $today), 0, 10)];
        }

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

    if ($path === '/api/leads' && $method === 'POST') {
        $body = requestData();
        if (empty($body['name']) || empty($body['phone']) || empty($body['city']) || empty($body['requirement'])) {
            jsonResponse(['error' => 'Name, phone, city and requirement are required'], 400);
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
        Auth::requireAuth();
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
        Auth::requireAuth();
        jsonResponse(['leads' => SiteRepository::listLeads()]);
    }

    if ($path === '/api/admin/leads/status' && $method === 'PUT') {
        Auth::requireAuth();
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
        Auth::requireAuth();
        jsonResponse(['pros' => SiteRepository::listPros([])]);
    }

    if ($path === '/api/admin/pros/verify' && $method === 'PUT') {
        Auth::requireAuth();
        $body = requestJson();
        if (empty($body['pro_id']) || !isset($body['verification_status'])) {
            jsonResponse(['error' => 'pro_id and verification_status are required'], 400);
        }
        SiteRepository::setProVerification((int)$body['pro_id'], (bool)$body['verification_status']);
        jsonResponse(['success' => true]);
    }

    if ($path === '/api/admin/lead-coupons') {
        Auth::requireAuth();
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
        Auth::requireAuth();
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
        Auth::requireAuth();
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
        Auth::requireAuth();
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

    if ($path === '/api/admin/portfolios') {
        Auth::requireAuth();
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
        Auth::requireAuth();
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
                            'Support email: admin@homeinteriors360.com',
                            'Support phone: +91 93158 68727',
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
            'meta' => 'Privacy policy for HomeInteriors360 users, lead buyers, and payment customers.',
            'sections' => [
                [
                    'title' => 'Information We Collect',
                    'body' => [
                        'We collect information submitted through forms, account registration, lead checkout, and professional profile requests. This may include name, phone number, email address, city, society or locality, budget, project requirement, and payment-related order references.',
                    ],
                ],
                [
                    'title' => 'How We Use Information',
                    'body' => [
                        [
                            'To respond to homeowner and professional enquiries.',
                            'To create buyer accounts and provide purchased lead access.',
                            'To process lead package orders through Razorpay.',
                            'To maintain platform security, prevent misuse, and improve services.',
                        ],
                    ],
                ],
                [
                    'title' => 'Payments and Sensitive Data',
                    'body' => [
                        'Online payments are processed by Razorpay. We do not store card numbers, UPI PINs, banking passwords, or full payment instrument details on our server.',
                    ],
                ],
                [
                    'title' => 'Data Sharing',
                    'body' => [
                        'We may share relevant lead information with registered buyers after a successful purchase. We may also share necessary transaction details with payment, hosting, analytics, legal, or compliance service providers where required.',
                    ],
                ],
            ],
        ],
        '/terms-and-conditions' => [
            'title' => 'Terms and Conditions',
            'meta' => 'Terms and conditions for HomeInteriors360 services, lead purchases, and website use.',
            'sections' => [
                [
                    'title' => 'Use of the Website',
                    'body' => [
                        'By using HomeInteriors360.com, submitting an enquiry, creating a buyer account, or purchasing a lead package, you agree to use the website lawfully and provide accurate information.',
                    ],
                ],
                [
                    'title' => 'Services Offered',
                    'body' => [
                        'HomeInteriors360 provides an online discovery and lead marketplace for interior design, architecture, contractor, and related home improvement services. Paid products currently include digital lead packages and managed growth enquiries for professionals.',
                    ],
                ],
                [
                    'title' => 'Lead Purchase Terms',
                    'body' => [
                        [
                            'Lead packages are priced according to available filters, lead count, and pricing slabs shown before checkout.',
                            'Purchased leads are for the buyer account that completed payment and must not be resold, scraped, or misused.',
                            'A lead is not a guaranteed conversion, sale, site visit, or project award.',
                            'Buyers are responsible for contacting leads professionally and complying with applicable laws.',
                        ],
                    ],
                ],
                [
                    'title' => 'Payments',
                    'body' => [
                        'Payments are collected in INR through Razorpay. An order is considered successful only after payment confirmation and signature verification by the website.',
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
            'title' => (string)SiteRepository::content('seo.home.title', 'HomeInteriors360'),
            'metaDescription' => (string)SiteRepository::content('seo.home.description', 'Find verified architects, interior designers, and contractors for your home project.'),
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
        Auth::requireAuth();
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
        Auth::requireAuth();
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
        Auth::requireAuth();
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

    if ($path === '/admin/pros') {
        Auth::requireAuth();
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
        Auth::requireAuth();
        render('admin/professionals', [
            'title' => 'Professionals Manager',
            'metaDescription' => 'Create and manage professional profiles with images, filters, pricing, and portfolio linkage.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'professionals' => SiteRepository::listProfessionalsForAdmin(),
        ]);
        exit;
    }

    if ($path === '/admin/portfolios') {
        Auth::requireAuth();
        render('admin/portfolios', [
            'title' => 'Portfolio Manager',
            'metaDescription' => 'Create and manage portfolio projects, images, testimonials, and project metadata.',
            'metaRobots' => 'noindex,nofollow',
            'active' => 'admin',
            'content' => $content,
            'portfolios' => SiteRepository::listPortfolioForAdmin(),
            'professionals' => SiteRepository::professionalOptions(),
        ]);
        exit;
    }

    if ($path === '/admin/lead-coupons') {
        Auth::requireAuth();
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

    http_response_code(404);
    echo '404 Not Found';
} catch (Throwable $e) {
    if (str_starts_with($path, '/api/')) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
    http_response_code(500);
    echo 'Server Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}
