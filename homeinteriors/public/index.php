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
$content = SiteRepository::allContent();

try {
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
            'budget_min' => $_GET['budget_min'] ?? null,
            'budget_max' => $_GET['budget_max'] ?? null,
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
        $body = requestJson();
        if (empty($body['name']) || empty($body['phone']) || empty($body['city']) || empty($body['requirement'])) {
            jsonResponse(['error' => 'Name, phone, city and requirement are required'], 400);
        }
        $leadId = SiteRepository::createLead([
            'name' => (string)$body['name'],
            'phone' => (string)$body['phone'],
            'city' => (string)$body['city'],
            'requirement' => (string)$body['requirement'],
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

    // Public pages
    if ($path === '/') {
        render('public/home', [
            'title' => (string)SiteRepository::content('seo.home.title', 'HomeInteriors360'),
            'active' => 'home',
            'content' => $content,
            'payload' => SiteRepository::homepagePayload(),
        ]);
        exit;
    }

    if ($path === '/professionals') {
        render('public/professionals', [
            'title' => (string)SiteRepository::content('seo.directory.title', 'Find Professionals'),
            'active' => 'directory',
            'content' => $content,
            'pros' => SiteRepository::listPros([]),
        ]);
        exit;
    }

    if (preg_match('#^/professionals/([a-z0-9-]+)$#i', $path, $match)) {
        $pro = SiteRepository::getProBySlug((string)$match[1]);
        if (!$pro) {
            http_response_code(404);
            echo 'Professional not found';
            exit;
        }
        $profileData = SiteRepository::proProfileData((int)$pro['id']);
        render('public/professional-profile', [
            'title' => $pro['full_name'] . ' | ' . (string)SiteRepository::content('seo.profile.title_suffix', 'HomeInteriors360'),
            'active' => 'directory',
            'content' => $content,
            'pro' => $pro,
            'projects' => $profileData['projects'],
            'reviews' => $profileData['reviews'],
        ]);
        exit;
    }

    if ($path === '/cost-calculator') {
        render('public/calculator', [
            'title' => (string)SiteRepository::content('seo.calculator.title', 'Design Cost Calculator'),
            'active' => 'calculator',
            'content' => $content,
        ]);
        exit;
    }

    // Admin pages
    if ($path === '/admin/login') {
        render('admin/login', [
            'title' => (string)SiteRepository::content('admin.login.title', 'Admin Login'),
            'content' => $content,
        ]);
        exit;
    }

    if ($path === '/admin') {
        Auth::requireAuth();
        render('admin/dashboard', [
            'title' => (string)SiteRepository::content('admin.title', 'Admin Dashboard'),
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
            'active' => 'admin',
            'content' => $content,
            'pros' => SiteRepository::listPros([]),
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
