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
            'content' => $content,
        ]);
        exit;
    }

    if ($path === '/admin') {
        Auth::requireAuth();
        render('admin/dashboard', [
            'title' => (string)SiteRepository::content('admin.title', 'Admin Dashboard'),
            'metaDescription' => 'Admin dashboard for HomeInteriors360 site management, leads, professionals, and content.',
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
            'active' => 'admin',
            'content' => $content,
            'portfolios' => SiteRepository::listPortfolioForAdmin(),
            'professionals' => SiteRepository::professionalOptions(),
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
