<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Domain\Agreement\Services\AgreementCompilerService;
use App\Domain\Agreement\Services\SigningTokenService;
use App\Domain\Auth\Services\AuthService;
use App\Domain\Caretaker\Services\CaretakerRoutingService;
use App\Domain\Onboarding\Services\TenantOnboardingService;
use App\Domain\Payments\Services\SaasBillingService;
use App\Domain\Reminders\Services\RentReminderService;
use App\Domain\SuperAdmin\Services\SuperAdminDataService;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\TenantOnboardingController;
use App\Http\Middleware\AuthMiddleware;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Notifications\EmailNotificationChannel;
use App\Infrastructure\Notifications\SmsNotificationChannel;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = new Connection();
$auth = new AuthService($db);
$authMiddleware = new AuthMiddleware($auth);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// 1. PUBLIC LANDING PAGE
if ($path === '/' && $method === 'GET') {
    require __DIR__ . '/../resources/views/landing.php';
    exit;
}

// 2. AUTHENTICATION (LOGIN, LOGOUT, & 1-CLICK DEMO ACCESS)
$authCtrl = new AuthController($auth);

if ($path === '/login') {
    if ($method === 'GET') {
        $authCtrl->showLogin();
    } elseif ($method === 'POST') {
        $authCtrl->login();
    }
    exit;
}

if ($path === '/logout') {
    $authCtrl->logout();
    exit;
}

// Route Aliases & Shortcuts
if ($path === '/login/landlord') {
    header('Location: /login?role=landlord&switch=1');
    exit;
}
if ($path === '/login/caretaker' || $path === '/login/admin') {
    header('Location: /login?role=caretaker&switch=1');
    exit;
}
if ($path === '/login/superadmin') {
    header('Location: /login?role=superadmin&switch=1');
    exit;
}
if ($path === '/login/tenant') {
    header('Location: /login?role=tenant&switch=1');
    exit;
}

// 2b. LANDLORD REGISTRATION & NATIONWIDE PROPERTY LISTING
if ($path === '/register' || $path === '/signup' || $path === '/register/landlord') {
    $errorMessage = null;
    if ($method === 'POST') {
        try {
            $regService = new \App\Domain\Auth\Services\LandlordRegistrationService($db);
            $result = $regService->register($_POST);

            // Log in the newly registered landlord
            $auth->login($result['user']);

            // Redirect to landlord dashboard with welcome query
            header('Location: /landlord?registered=1');
            exit;
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
        }
    }
    require __DIR__ . '/../resources/views/auth/register_landlord.php';
    exit;
}

// 2c. ESTATES DIRECTORY SEARCH / AUTOCOMPLETE API (ABUJA & LAGOS)
if ($path === '/api/v1/estates' || $path === '/api/estates') {
    header('Content-Type: application/json; charset=utf-8');
    $pdo = $db->getPdo();
    $stateParam = trim($_GET['state'] ?? '');
    $query = trim($_GET['q'] ?? '');

    $sql = "SELECT id, title, state, district, zone_region, estate_category, market_availability, description, source
            FROM estates_directory WHERE 1=1";
    $params = [];

    if (!empty($stateParam)) {
        if (strcasecmp($stateParam, 'abuja') === 0 || strcasecmp($stateParam, 'fct') === 0 || strcasecmp($stateParam, 'abuja fct') === 0) {
            $sql .= " AND state = 'Abuja'";
        } elseif (strcasecmp($stateParam, 'lagos') === 0) {
            $sql .= " AND state = 'Lagos'";
        } else {
            $sql .= " AND state = ?";
            $params[] = $stateParam;
        }
    }

    if (!empty($query)) {
        $prefixMatch = $query . '%';
        $containsMatch = '%' . $query . '%';
        $sql .= " AND (title LIKE ? OR title LIKE ? OR district LIKE ?)";
        $params[] = $prefixMatch;
        $params[] = $containsMatch;
        $params[] = $prefixMatch;
        $sql .= " ORDER BY (CASE WHEN LOWER(title) LIKE LOWER(?) THEN 1 WHEN LOWER(title) LIKE LOWER(?) THEN 2 ELSE 3 END), title ASC LIMIT 50";
        $params[] = $prefixMatch;
        $params[] = $containsMatch;
    } else {
        $sql .= " ORDER BY title ASC LIMIT 60";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count'   => count($results),
        'estates' => $results,
    ]);
    exit;
}

// Prohibition of direct tenant registration (if someone requests /register/tenant or /signup/tenant)
if ($path === '/register/tenant' || $path === '/signup/tenant') {
    http_response_code(403);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>Invitation Required</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-slate-50 flex items-center justify-center min-h-screen p-4"><div class="max-w-md w-full bg-white rounded-2xl p-8 border border-slate-200 text-center shadow-lg"><div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-4 text-2xl font-bold">🔒</div><h2 class="text-xl font-bold text-slate-900 mb-2">Invitation-Only Access</h2><p class="text-sm text-slate-600 mb-6">Tenant and resident accounts cannot be created directly. Tenants are securely invited by their Landlord upon tenancy onboarding.</p><div class="flex gap-2 justify-center"><a href="/login?role=tenant" class="bg-[#1D4ED8] text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-blue-800 transition">Tenant Login</a><a href="/demo/tenant" class="bg-blue-50 text-[#1D4ED8] border border-blue-200 px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-blue-100 transition">Test Play Tenant Mode</a></div></div></body></html>';
    exit;
}

if ($path === '/dashboard' || $path === '/dashboards') {
    $user = $auth->currentUser();
    if ($user !== null) {
        header('Location: ' . $auth->redirectPathForRole($user['role']));
        exit;
    }
    header('Location: /login');
    exit;
}

if ($path === '/dashboard/landlord' || $path === '/landlord-dashboard') {
    header('Location: /demo/landlord');
    exit;
}

if ($path === '/dashboard/caretaker' || $path === '/caretaker-dashboard') {
    header('Location: /demo/caretaker');
    exit;
}

if ($path === '/dashboard/superadmin' || $path === '/dashboard/admin' || $path === '/superadmin-dashboard' || $path === '/admin-dashboard') {
    header('Location: /demo/superadmin');
    exit;
}

if ($path === '/dashboard/tenant' || $path === '/tenant-dashboard') {
    header('Location: /demo/tenant');
    exit;
}

// 1-Click Instant Demo Login Routes
if ($path === '/demo/landlord' || ($path === '/demo' && strtolower($_GET['role'] ?? '') === 'landlord')) {
    $user = $auth->attempt('landlord@ogalandlord.ng', 'password123') 
        ?: $auth->attempt('landlord@propertycare.ng', 'password123')
        ?: $auth->attempt('ibrahim.bello@example.com', 'password123');
    if (!$user) {
        $user = $db->getPdo()->query("SELECT * FROM users WHERE role = 'LANDLORD' ORDER BY id ASC LIMIT 1")->fetch();
    }
    if ($user) {
        $auth->login($user);
        header('Location: /landlord');
        exit;
    }
}

if ($path === '/demo/caretaker' || ($path === '/demo' && in_array(strtolower($_GET['role'] ?? ''), ['caretaker', 'admin'], true))) {
    $user = $auth->attempt('caretaker.idu@ogalandlord.ng', 'password123') ?: $auth->attempt('caretaker.idu@propertycare.ng', 'password123');
    if ($user) {
        $auth->login($user);
        header('Location: /caretaker');
        exit;
    }
}

if ($path === '/demo/superadmin' || ($path === '/demo' && strtolower($_GET['role'] ?? '') === 'superadmin')) {
    $user = $auth->attempt('superadmin@ogalandlord.ng', 'password123') ?: $auth->attempt('superadmin@propertycare.ng', 'password123');
    if ($user) {
        $auth->login($user);
        header('Location: /admin');
        exit;
    }
}

if ($path === '/demo/tenant' || ($path === '/demo' && strtolower($_GET['role'] ?? '') === 'tenant')) {
    $user = $auth->attempt('amara.okafor@example.com', 'password123');
    if (!$user) {
        $user = $db->getPdo()->query("SELECT * FROM users WHERE role = 'TENANT' ORDER BY id ASC LIMIT 1")->fetch();
    }
    if ($user) {
        $auth->login($user);
        header('Location: /tenant');
        exit;
    }
}

if ($path === '/demo') {
    header('Location: /login');
    exit;
}

// 3. LANDLORD WORKSPACE
if ($path === '/landlord') {
    $user = $authMiddleware->requireRole('LANDLORD', 'SUPERADMIN');
    $pdo = $db->getPdo();
    $landlordId = (int)$user['id'];
    $isAdmin = ($user['role'] === 'SUPERADMIN') ? 1 : 0;

    // 1. Properties / Projects
    $pStmt = $pdo->prepare("SELECT p.*,
                            (SELECT COUNT(*) FROM units un WHERE un.property_id = p.id) as unit_count,
                            (SELECT COUNT(*) FROM units un WHERE un.property_id = p.id AND un.is_occupied = 1) as occupied_count,
                            pca.caretaker_id, u.full_name as caretaker_name, u.email as caretaker_email, u.phone_number as caretaker_phone,
                            pca.can_manage_tickets, pca.can_view_finances
                            FROM properties p
                            LEFT JOIN property_caretaker_assignments pca ON pca.property_id = p.id
                            LEFT JOIN users u ON pca.caretaker_id = u.id
                            WHERE p.landlord_id = :lid OR :is_admin = 1
                            ORDER BY p.id ASC");
    $pStmt->execute(['lid' => $landlordId, 'is_admin' => $isAdmin]);
    $properties = $pStmt->fetchAll();
    if (empty($properties)) {
        $properties = $pdo->query("SELECT p.*,
                            (SELECT COUNT(*) FROM units un WHERE un.property_id = p.id) as unit_count,
                            (SELECT COUNT(*) FROM units un WHERE un.property_id = p.id AND un.is_occupied = 1) as occupied_count,
                            pca.caretaker_id, u.full_name as caretaker_name, u.email as caretaker_email, u.phone_number as caretaker_phone,
                            pca.can_manage_tickets, pca.can_view_finances
                            FROM properties p
                            LEFT JOIN property_caretaker_assignments pca ON pca.property_id = p.id
                            LEFT JOIN users u ON pca.caretaker_id = u.id
                            ORDER BY p.id ASC")->fetchAll();
    }

    // 2. Active Leases (Excluding TERMINATED leases)
    $lStmt = $pdo->prepare("SELECT l.*, u.full_name as tenant_name, u.email as tenant_email, u.phone_number as tenant_phone, un.unit_number, un.apartment_type
                            FROM leases l
                            JOIN users u ON l.tenant_id = u.id
                            JOIN units un ON l.unit_id = un.id
                            JOIN properties p ON un.property_id = p.id
                            WHERE (p.landlord_id = :lid OR :is_admin = 1)
                              AND l.agreement_status != 'TERMINATED'
                            ORDER BY l.id DESC");
    $lStmt->execute(['lid' => $landlordId, 'is_admin' => $isAdmin]);
    $leases = $lStmt->fetchAll();

    // 2b. Former Tenants History & Timelines (Requirement 4)
    $terminationService = new \App\Domain\Tenancy\Services\TenancyTerminationService($db);
    $formerTenants = $terminationService->getFormerTenantsHistory(landlordId: $isAdmin ? null : $landlordId);

    // 3. All Units in Estate
    $uStmt = $pdo->prepare("SELECT un.*, p.title as property_title, p.city as property_city, p.state as property_state,
                                   l.id as lease_id, l.agreement_status, l.rent_due_date, l.rent_start_date, l.rent_amount as lease_rent_amount,
                                   u.full_name as tenant_name, u.email as tenant_email, u.phone_number as tenant_phone
                            FROM units un
                            JOIN properties p ON un.property_id = p.id
                            LEFT JOIN leases l ON (l.unit_id = un.id AND l.agreement_status != 'TERMINATED')
                            LEFT JOIN users u ON l.tenant_id = u.id
                            WHERE p.landlord_id = :lid OR :is_admin = 1
                            ORDER BY un.unit_number ASC");
    $uStmt->execute(['lid' => $landlordId, 'is_admin' => $isAdmin]);
    $allUnits = $uStmt->fetchAll();

    // 4. Maintenance Tickets (Requirement 5: Landlord ONLY views tickets if Caretaker tagged/escalated)
    $tStmt = $pdo->prepare("SELECT t.*, un.unit_number, un.apartment_type, p.title as property_title, u.full_name as tenant_name,
                                   (SELECT COUNT(*) FROM ticket_messages m WHERE m.ticket_id = t.id) as message_count
                            FROM maintenance_tickets t
                            JOIN properties p ON t.property_id = p.id
                            JOIN units un ON t.unit_id = un.id
                            JOIN users u ON t.tenant_id = u.id
                            WHERE (p.landlord_id = :lid OR :is_admin = 1)
                              AND (t.is_escalated_to_landlord = 1 OR :is_admin = 1)
                            ORDER BY t.id DESC");
    $tStmt->execute(['lid' => $landlordId, 'is_admin' => $isAdmin]);
    $tickets = $tStmt->fetchAll();

    // 5. Assigned Caretakers
    $cStmt = $pdo->prepare("SELECT u.id, u.full_name, u.email, u.phone_number, pca.can_manage_tickets, pca.can_view_finances, p.title as property_title
                            FROM property_caretaker_assignments pca
                            JOIN users u ON pca.caretaker_id = u.id
                            JOIN properties p ON pca.property_id = p.id
                            WHERE p.landlord_id = :lid OR :is_admin = 1");
    $cStmt->execute(['lid' => $landlordId, 'is_admin' => $isAdmin]);
    $caretakers = $cStmt->fetchAll();

    // 6. Payments requiring Landlord or Caretaker confirmation (Requirement 7)
    $payStmt = $pdo->prepare("SELECT tp.*, un.unit_number, p.title as property_title, u.full_name as tenant_name
                              FROM tenant_payments tp
                              JOIN properties p ON tp.property_id = p.id
                              JOIN leases l ON tp.lease_id = l.id
                              JOIN units un ON l.unit_id = un.id
                              JOIN users u ON tp.tenant_id = u.id
                              WHERE p.landlord_id = :lid OR :is_admin = 1
                              ORDER BY tp.id DESC");
    $payStmt->execute(['lid' => $landlordId, 'is_admin' => $isAdmin]);
    $landlordPayments = $payStmt->fetchAll();

    // 7. SaaS Billing
    $saasService = new SaasBillingService($db);
    $billing = $saasService->calculateLandlordAnnualInvoice($landlordId);

    // 8. Payment & Gateway Settings
    $paymentSettingsService = new \App\Domain\Payment\Services\PaymentSettingsService($db);
    $paymentSettings = $paymentSettingsService->getSettings($landlordId);

    // 9. Financial Analytics & Expenses
    $financeService = new \App\Domain\Finance\Services\FinancialAnalyticsService($db);
    $firstPropId = !empty($properties[0]['id']) ? (int)$properties[0]['id'] : 1;
    $financials = $financeService->getPropertyFinancials($firstPropId);
    $taxSummary = $financeService->getTaxSummary($firstPropId);
    $propertyExpenses = $financeService->listExpenses($firstPropId);

    // 10. Artisans
    $artisanService = new \App\Domain\Artisan\Services\ArtisanService($db);
    $artisans = $artisanService->listArtisans($landlordId);

    // 11. Community Announcements & Facility Bookings
    $communityService = new \App\Domain\Community\Services\CommunityService($db);
    $communityAnnouncements = $communityService->listAnnouncements($firstPropId);
    $facilityBookings = $communityService->listBookings($firstPropId);

    // 12. Alert Rules
    $alertRulesService = new \App\Domain\Reminders\Services\AlertRulesService($db);
    $alertRules = $alertRulesService->listRules('LANDLORD', $landlordId);
    if (empty($alertRules)) {
        $alertRules = $alertRulesService->listRules('GLOBAL');
    }

    $metrics = [
        'total_units' => count($allUnits) ?: 8,
        'occupied_units' => count($leases),
        'vacant_units' => max(0, count($allUnits) - count($leases)),
        'total_properties' => count($properties) ?: 1,
        'former_tenants_count' => count($formerTenants),
        'pending_payments_count' => count(array_filter($landlordPayments, fn($p) => $p['status'] === 'PENDING_CONFIRMATION')),
        'net_operating_income' => $financials['net_operating_income'] ?? 0,
        'tax_estimate' => $taxSummary['estimated_tax_payable'] ?? 0,
    ];

    require __DIR__ . '/../resources/views/landlord/dashboard.php';
    exit;
}

// 4. TENANT ONBOARDING WIZARD
if ($path === '/onboarding') {
    $user = $auth->currentUser();
    require __DIR__ . '/../resources/views/onboarding/wizard.php';
    exit;
}

// 5. CARETAKER WORKSPACE & AGREEMENTS ALIASES
if ($path === '/caretaker/agreements' || $path === '/agreements' || $path === '/tenancy-agreements') {
    header('Location: /caretaker?tab=agreements');
    exit;
}

if ($path === '/caretaker') {
    $user = $authMiddleware->requireRole('CARETAKER', 'SUPERADMIN');
    $pdo = $db->getPdo();
    $caretakerId = (int)$user['id'];
    $isAdmin = ($user['role'] === 'SUPERADMIN') ? 1 : 0;

    // Property Filtering (All Estates vs Specific Estate)
    $selectedPropertyId = !empty($_GET['property_id']) ? (int)$_GET['property_id'] : 0;

    // 1. Assigned Properties across nationwide locations
    $pStmt = $pdo->prepare("SELECT p.*,
                            (SELECT COUNT(*) FROM units un WHERE un.property_id = p.id) as units_count,
                            (SELECT COUNT(*) FROM units un WHERE un.property_id = p.id AND un.is_occupied = 1) as occupied_count
                            FROM properties p
                            JOIN property_caretaker_assignments pca ON pca.property_id = p.id
                            WHERE pca.caretaker_id = :cid OR :is_admin = 1
                            ORDER BY p.id ASC");
    $pStmt->execute(['cid' => $caretakerId, 'is_admin' => $isAdmin]);
    $assignedProperties = $pStmt->fetchAll();
    if (empty($assignedProperties)) {
        $assignedProperties = $pdo->query("SELECT p.*, (SELECT COUNT(*) FROM units un WHERE un.property_id = p.id) as units_count, (SELECT COUNT(*) FROM units un WHERE un.property_id = p.id AND un.is_occupied = 1) as occupied_count FROM properties p ORDER BY id ASC")->fetchAll();
    }

    $property = null;
    if ($selectedPropertyId > 0) {
        foreach ($assignedProperties as $ap) {
            if ((int)$ap['id'] === $selectedPropertyId) {
                $property = $ap;
                break;
            }
        }
    }
    if (!$property) {
        $property = $assignedProperties[0] ?? null;
    }

    $propFilterSql = ($selectedPropertyId > 0) ? " AND p.id = :selected_pid " : "";
    $params = ['cid' => $caretakerId, 'is_admin' => $isAdmin];
    if ($selectedPropertyId > 0) {
        $params['selected_pid'] = $selectedPropertyId;
    }

    // 2. All Managed Tenants & Leases for this Caretaker (filtered if selected)
    $lStmt = $pdo->prepare("SELECT l.*, 
                                   u.full_name as tenant_name, u.email as tenant_email, u.phone_number as tenant_phone,
                                   un.unit_number, un.apartment_type,
                                   p.title as property_title, p.address_line_1, p.city, p.state
                            FROM leases l
                            JOIN users u ON l.tenant_id = u.id
                            JOIN units un ON l.unit_id = un.id
                            JOIN properties p ON un.property_id = p.id
                            JOIN property_caretaker_assignments pca ON pca.property_id = p.id
                            WHERE (pca.caretaker_id = :cid OR :is_admin = 1) {$propFilterSql}
                            ORDER BY l.id ASC");
    $lStmt->execute($params);
    $managedLeases = $lStmt->fetchAll();

    // 3. Estate Units
    $uStmt = $pdo->prepare("SELECT un.*, p.title as property_title, p.city as property_city, p.state as property_state,
                                   l.id as lease_id, l.agreement_status, l.rent_due_date,
                                   u.full_name as tenant_name, u.email as tenant_email, u.phone_number as tenant_phone
                            FROM units un
                            JOIN properties p ON un.property_id = p.id
                            JOIN property_caretaker_assignments pca ON pca.property_id = p.id
                            LEFT JOIN leases l ON l.unit_id = un.id AND l.agreement_status != 'TERMINATED'
                            LEFT JOIN users u ON l.tenant_id = u.id
                            WHERE (pca.caretaker_id = :cid OR :is_admin = 1) {$propFilterSql}
                            ORDER BY p.id ASC, un.unit_number ASC");
    $uStmt->execute($params);
    $estateUnits = $uStmt->fetchAll();

    // 4. Maintenance Tickets
    $tStmt = $pdo->prepare("SELECT t.*, un.unit_number, un.apartment_type, p.title as property_title, p.city as property_city, p.state as property_state,
                                   u.full_name as tenant_name, u.phone_number as tenant_phone,
                                   (SELECT COUNT(*) FROM ticket_messages m WHERE m.ticket_id = t.id) as message_count,
                                   (SELECT m.created_at FROM ticket_messages m WHERE m.ticket_id = t.id ORDER BY m.id DESC LIMIT 1) as last_message_at
                            FROM maintenance_tickets t
                            JOIN properties p ON t.property_id = p.id
                            JOIN units un ON t.unit_id = un.id
                            JOIN users u ON t.tenant_id = u.id
                            JOIN property_caretaker_assignments pca ON pca.property_id = p.id
                            WHERE (pca.caretaker_id = :cid OR :is_admin = 1) {$propFilterSql}
                            ORDER BY t.id DESC");
    $tStmt->execute($params);
    $tickets = $tStmt->fetchAll();

    // 5. Tenant Payments
    $payStmt = $pdo->prepare("SELECT tp.*, un.unit_number, p.title as property_title, p.city as property_city, p.state as property_state,
                                     u.full_name as tenant_name
                              FROM tenant_payments tp
                              JOIN properties p ON tp.property_id = p.id
                              JOIN property_caretaker_assignments pca ON pca.property_id = p.id
                              JOIN leases l ON tp.lease_id = l.id
                              JOIN units un ON l.unit_id = un.id
                              JOIN users u ON tp.tenant_id = u.id
                              WHERE (pca.caretaker_id = :cid OR :is_admin = 1) {$propFilterSql}
                              ORDER BY tp.id DESC");
    $payStmt->execute($params);
    $caretakerPayments = $payStmt->fetchAll();

    // 6. Artisans for Work Dispatch
    $artisanService = new \App\Domain\Artisan\Services\ArtisanService($db);
    $artisans = $artisanService->listArtisans();

    // 7. Community Notice Board & Facility Bookings
    $communityService = new \App\Domain\Community\Services\CommunityService($db);
    $propId = $property ? (int)$property['id'] : null;
    $announcements = $communityService->listAnnouncements($propId);
    $facilityBookings = $communityService->listBookings($propId);

    // 8. Expenses Log
    $financeService = new \App\Domain\Finance\Services\FinancialAnalyticsService($db);
    $propertyExpenses = $financeService->listExpenses($propId);

    require __DIR__ . '/../resources/views/caretaker/dashboard.php';
    exit;
}

// 6. SUPERADMIN CONTROL CENTER
if ($path === '/admin' || $path === '/superadmin') {
    $user = $authMiddleware->requireRole('SUPERADMIN');
    $pdo = $db->getPdo();
    $superAdminService = new SuperAdminDataService($db);

    $landlordsCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'LANDLORD'")->fetchColumn();
    $propertiesCount = (int) $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn();
    $unitsCount = (int) $pdo->query("SELECT COUNT(*) FROM units")->fetchColumn();
    $tenantsCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'TENANT'")->fetchColumn();

    $saasService = new SaasBillingService($db);
    $firstLandlordId = (int) ($pdo->query("SELECT id FROM users WHERE role = 'LANDLORD' LIMIT 1")->fetchColumn() ?: 1);
    $billing = $saasService->calculateLandlordAnnualInvoice($firstLandlordId);

    $allTenants = $superAdminService->getAllTenants();
    $allLandlords = $superAdminService->getAllLandlords();

    $metrics = [
        'landlords_count'  => $landlordsCount,
        'properties_count' => $propertiesCount,
        'units_count'      => $unitsCount,
        'tenants_count'    => $tenantsCount,
    ];

    // Global Alert Rules & Configurations
    $alertRulesService = new \App\Domain\Reminders\Services\AlertRulesService($db);
    $allAlertRules = $alertRulesService->listRules();

    // All Estates & Properties for deep audit
    $allEstates = $pdo->query("SELECT p.*, u.full_name as landlord_name, u.email as landlord_email FROM properties p JOIN users u ON p.landlord_id = u.id ORDER BY p.id ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Artisans Directory
    $artisanService = new \App\Domain\Artisan\Services\ArtisanService($db);
    $allArtisans = $artisanService->listArtisans();

    // Community Management
    $communityService = new \App\Domain\Community\Services\CommunityService($db);
    $allAnnouncements = $communityService->listAnnouncements();
    $allBookings = $communityService->listBookings();

    require __DIR__ . '/../resources/views/superadmin/dashboard.php';
    exit;
}

// 6b. SUPERADMIN DATA EXPORT & UPLOAD ROUTING
if ($path === '/admin/export/tenants/csv') {
    $user = $authMiddleware->requireRole('SUPERADMIN');
    $service = new SuperAdminDataService($db);
    $csv = $service->exportTenantsCsv();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="tenants_directory_' . date('Ymd_His') . '.csv"');
    echo $csv;
    exit;
}

if ($path === '/admin/export/landlords/csv') {
    $user = $authMiddleware->requireRole('SUPERADMIN');
    $service = new SuperAdminDataService($db);
    $csv = $service->exportLandlordsCsv();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="landlords_directory_' . date('Ymd_His') . '.csv"');
    echo $csv;
    exit;
}

if ($path === '/admin/export/tenants/pdf') {
    $user = $authMiddleware->requireRole('SUPERADMIN');
    $service = new SuperAdminDataService($db);
    $items = $service->getAllTenants();
    $exportType = 'tenants';
    $reportTitle = 'Tenancy & Resident Register Audit Sheet';
    require __DIR__ . '/../resources/views/superadmin/export_pdf.php';
    exit;
}

if ($path === '/admin/export/landlords/pdf') {
    $user = $authMiddleware->requireRole('SUPERADMIN');
    $service = new SuperAdminDataService($db);
    $items = $service->getAllLandlords();
    $exportType = 'landlords';
    $reportTitle = 'Landlords & Estate Portfolio Audit Sheet';
    require __DIR__ . '/../resources/views/superadmin/export_pdf.php';
    exit;
}

if ($path === '/admin/templates/tenants.csv') {
    $user = $authMiddleware->requireRole('SUPERADMIN');
    $service = new SuperAdminDataService($db);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="tenants_import_template.csv"');
    echo $service->getTenantsCsvTemplate();
    exit;
}

if ($path === '/admin/templates/landlords.csv') {
    $user = $authMiddleware->requireRole('SUPERADMIN');
    $service = new SuperAdminDataService($db);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="landlords_import_template.csv"');
    echo $service->getLandlordsCsvTemplate();
    exit;
}

if ($path === '/admin/upload/tenants' && $method === 'POST') {
    $user = $authMiddleware->requireRole('SUPERADMIN');
    header('Content-Type: application/json');
    $service = new SuperAdminDataService($db);

    $csvContent = '';
    if (!empty($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
        $csvContent = file_get_contents($_FILES['file']['tmp_name']) ?: '';
    } else {
        $csvContent = file_get_contents('php://input');
    }

    if (empty(trim($csvContent))) {
        http_response_code(400);
        echo json_encode(['error' => 'No CSV content received. Please select a valid CSV file.']);
        exit;
    }

    try {
        $summary = $service->importTenantsFromCsv($csvContent);
        echo json_encode(['success' => true, 'summary' => $summary]);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($path === '/admin/upload/landlords' && $method === 'POST') {
    $user = $authMiddleware->requireRole('SUPERADMIN');
    header('Content-Type: application/json');
    $service = new SuperAdminDataService($db);

    $csvContent = '';
    if (!empty($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
        $csvContent = file_get_contents($_FILES['file']['tmp_name']) ?: '';
    } else {
        $csvContent = file_get_contents('php://input');
    }

    if (empty(trim($csvContent))) {
        http_response_code(400);
        echo json_encode(['error' => 'No CSV content received. Please select a valid CSV file.']);
        exit;
    }

    try {
        $summary = $service->importLandlordsFromCsv($csvContent);
        echo json_encode(['success' => true, 'summary' => $summary]);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 6c. API: LANDLORD PROPERTIES
if ($path === '/api/v1/landlord/properties' && $method === 'GET') {
    header('Content-Type: application/json');
    $pdo = $db->getPdo();
    $currentUser = $auth->currentUser();
    if ($currentUser && $currentUser['role'] === 'LANDLORD') {
        $stmt = $pdo->prepare("SELECT id, title, address_line_1, city, state FROM properties WHERE landlord_id = :lid ORDER BY title ASC");
        $stmt->execute(['lid' => $currentUser['id']]);
        $props = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($props)) {
            $props = $pdo->query("SELECT id, title, address_line_1, city, state FROM properties ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        $props = $pdo->query("SELECT id, title, address_line_1, city, state FROM properties ORDER BY title ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
    echo json_encode($props);
    exit;
}

// 7. PUBLIC TENANT SIGNING PORTAL (RESTRICTED FOR CARETAKER & ADMIN)
if ($path === '/sign') {
    $currentUser = $auth->currentUser();
    if ($currentUser && in_array($currentUser['role'], ['CARETAKER', 'SUPERADMIN', 'ADMIN', 'LANDLORD'], true)) {
        http_response_code(403);
        require __DIR__ . '/../resources/views/signing/restricted.php';
        exit;
    }
    require __DIR__ . '/../resources/views/signing/portal.php';
    exit;
}

// 7b. TENANCY AGREEMENT PREVIEW (AUTHORIZED: LANDLORD, CARETAKER, SUPERADMIN)
if ($path === '/agreement/preview' || $path === '/agreement' || preg_match('#^/agreements?/(\d+)$#', $path, $matches)) {
    $user = $authMiddleware->requireRole('LANDLORD', 'CARETAKER', 'SUPERADMIN');
    $pdo = $db->getPdo();

    $leaseId = (int)($_GET['id'] ?? ($matches[1] ?? 1));
    $stmt = $pdo->prepare("SELECT l.*, 
                                  u.full_name as tenant_name, u.email as tenant_email, u.phone_number as tenant_phone,
                                  un.unit_number, un.apartment_type,
                                  p.title as property_title, p.address_line_1, p.city, p.state, p.country,
                                  p.landlord_id
                           FROM leases l
                           JOIN users u ON l.tenant_id = u.id
                           JOIN units un ON l.unit_id = un.id
                           JOIN properties p ON un.property_id = p.id
                           WHERE l.id = :id
                           LIMIT 1");
    $stmt->execute(['id' => $leaseId]);
    $lease = $stmt->fetch();

    if (!$lease) {
        $stmt = $pdo->query("SELECT l.*, 
                                    u.full_name as tenant_name, u.email as tenant_email, u.phone_number as tenant_phone,
                                    un.unit_number, un.apartment_type,
                                    p.title as property_title, p.address_line_1, p.city, p.state, p.country,
                                    p.landlord_id
                             FROM leases l
                             JOIN users u ON l.tenant_id = u.id
                             JOIN units un ON l.unit_id = un.id
                             JOIN properties p ON un.property_id = p.id
                             ORDER BY l.id ASC LIMIT 1");
        $lease = $stmt ? $stmt->fetch() : null;
    }

    if (!$lease) {
        $lease = [
            'id' => 1,
            'uuid' => 'LSE-2026-0001',
            'rent_amount' => 2500000.00,
            'currency' => 'NGN',
            'rent_start_date' => '2025-10-08',
            'rent_due_date' => '2026-10-08',
            'agreement_status' => 'FULLY_EXECUTED',
            'document_sha256_hash' => 'df0c4e5ab3b9a1b4be92676c9633adfa9f82d94fc0ac74900ef3c33371ae6181',
            'emergency_contact_name' => 'Ngozi Okafor',
            'emergency_contact_relationship' => 'Sister',
            'emergency_contact_phone' => '+2348039990001',
            'tenant_signed_at' => '2025-10-08 14:15:00',
            'tenant_ip_address' => '127.0.0.1 (Localhost Verified)',
        ];
    }

    $tenant = [
        'full_name' => $lease['tenant_name'] ?? 'Amara Okafor',
        'email' => $lease['tenant_email'] ?? 'amara.okafor@example.com',
        'phone_number' => $lease['tenant_phone'] ?? '+2348030000003',
    ];

    $landlord = [
        'full_name' => 'Chief Ibrahim Bello',
        'email' => 'landlord@ogalandlord.ng',
        'phone_number' => '+2348030000001',
    ];

    $caretaker = [
        'full_name' => 'Musa Danjuma',
        'email' => 'caretaker.idu@ogalandlord.ng',
        'phone_number' => '+2348030000002',
    ];

    $unit = [
        'unit_number' => $lease['unit_number'] ?? 'Unit 1A',
        'apartment_type' => $lease['apartment_type'] ?? '2-Bedroom Apartment',
    ];

    $property = [
        'title' => $lease['property_title'] ?? 'PHDL Unity Estate, Idu',
        'address_line_1' => $lease['address_line_1'] ?? 'Plot 42 Railway Corridor, Idu Industrial',
        'city' => $lease['city'] ?? 'Abuja',
        'state' => $lease['state'] ?? 'FCT',
        'country' => $lease['country'] ?? 'Nigeria',
    ];

    require __DIR__ . '/../resources/views/agreement/preview.php';
    exit;
}

// 8. API: TENANT ONBOARDING (POST)
if ($path === '/api/v1/onboarding/tenants' && $method === 'POST') {
    $compiler = new AgreementCompilerService($db);
    $token = new SigningTokenService();
    $service = new TenantOnboardingService($db, $compiler, $token);
    $ctrl = new TenantOnboardingController($service);
    $ctrl->store();
    exit;
}

// 9. API: SIGNING EXECUTION (POST - RESTRICTED FOR CARETAKER & ADMIN)
if ($path === '/api/v1/signing/execute' && $method === 'POST') {
    header('Content-Type: application/json');
    $currentUser = $auth->currentUser();
    if ($currentUser && in_array($currentUser['role'], ['CARETAKER', 'SUPERADMIN', 'ADMIN', 'LANDLORD'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied: Caretakers and Administrators are not permitted to sign agreements on behalf of tenants.']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? [];

    $token = $data['token'] ?? '';
    $sigType = $data['signature_type'] ?? 'DRAWN';
    $sigData = $data['signature_data'] ?? '';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown Device';

    try {
        $tokenService = new SigningTokenService();
        $tokenHash = $tokenService->verifyAndExtractHash($token);

        $compiler = new AgreementCompilerService($db);
        $result = $compiler->executeTenantSignature($tokenHash, $sigType, $sigData, $ip, $ua);
        echo json_encode($result);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 10. API: PAYMENT WEBHOOK RECONCILIATION (POST)
if ($path === '/api/v1/webhooks/payments' && $method === 'POST') {
    $mailer = new EmailNotificationChannel();
    $router = new CaretakerRoutingService($db);
    $ctrl = new PaymentWebhookController($db, $mailer, $router);
    $ctrl->handle();
    exit;
}

// 11. API: TRIGGER RENT REMINDERS SCHEDULER (POST)
if ($path === '/api/v1/reminders/run' && $method === 'POST') {
    header('Content-Type: application/json');
    $mailer = new EmailNotificationChannel();
    $sms = new SmsNotificationChannel();
    $router = new CaretakerRoutingService($db);
    $reminderService = new RentReminderService($db, $mailer, $sms, $router);
    $result = $reminderService->executeScheduledRun(dryRun: true);
    echo json_encode($result);
    exit;
}

// ==========================================
// TENANT WORKSPACE & RECEIPT ROUTES
// ==========================================

// 12. TENANT PORTAL DASHBOARD (GET)
if ($path === '/tenant') {
    $user = $authMiddleware->requireRole('TENANT', 'SUPERADMIN');
    $pdo = $db->getPdo();
    $tenantPortalService = new \App\Domain\Tenant\Services\TenantPortalService($db);

    $tenantId = (int)$user['id'];
    if ($user['role'] === 'SUPERADMIN') {
        if (isset($_GET['tenant_id'])) {
            $tenantId = (int)$_GET['tenant_id'];
        } else {
            $firstTid = $pdo->query("SELECT id FROM users WHERE role = 'TENANT' ORDER BY id ASC LIMIT 1")->fetchColumn();
            if ($firstTid) {
                $tenantId = (int)$firstTid;
            }
        }
    }

    try {
        $dashboardData = $tenantPortalService->getTenantDashboardData($tenantId);
    } catch (\Throwable $e) {
        $dashboardData = [
            'tenant'        => $user,
            'lease'         => null,
            'landlord'      => null,
            'caretaker'     => null,
            'tickets'       => [],
            'payments'      => [],
            'pending_bills' => [],
            'error'         => $e->getMessage(),
        ];
    }

    $communityService = new \App\Domain\Community\Services\CommunityService($db);
    $propId = !empty($dashboardData['lease']['property_id']) ? (int)$dashboardData['lease']['property_id'] : null;
    $announcements = $communityService->listAnnouncements($propId);
    $facilityBookings = $communityService->listBookings($propId, $tenantId);

    $paymentSettingsService = new \App\Domain\Payment\Services\PaymentSettingsService($db);
    $landlordId = !empty($dashboardData['landlord']['id']) ? (int)$dashboardData['landlord']['id'] : 1;
    $paymentDetails = $paymentSettingsService->getPublicPaymentDetails($landlordId);

    require __DIR__ . '/../resources/views/tenant/dashboard.php';
    exit;
}

// 13. STAMPED PAYMENT RECEIPT VIEW (GET)
if ($path === '/tenant/receipt' || $path === '/receipt' || $path === '/payments/receipt') {
    $currentUser = $auth->currentUser();
    if (!$currentUser) {
        header('Location: /login');
        exit;
    }

    $pdo = $db->getPdo();
    $receiptNum = trim($_GET['number'] ?? $_GET['receipt_number'] ?? '');
    $paymentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($receiptNum !== '') {
        $rStmt = $pdo->prepare("SELECT tp.*, u.full_name as tenant_name, u.email as tenant_email, u.phone_number as tenant_phone,
                                       un.unit_number, un.apartment_type,
                                       p.title as property_title, p.address_line_1, p.city, p.state, p.country,
                                       conf.full_name as confirmed_by_name
                                FROM tenant_payments tp
                                JOIN users u ON tp.tenant_id = u.id
                                JOIN properties p ON tp.property_id = p.id
                                JOIN leases l ON tp.lease_id = l.id
                                JOIN units un ON l.unit_id = un.id
                                LEFT JOIN users conf ON tp.confirmed_by_user_id = conf.id
                                WHERE tp.receipt_number = :num LIMIT 1");
        $rStmt->execute(['num' => $receiptNum]);
    } else {
        $rStmt = $pdo->prepare("SELECT tp.*, u.full_name as tenant_name, u.email as tenant_email, u.phone_number as tenant_phone,
                                       un.unit_number, un.apartment_type,
                                       p.title as property_title, p.address_line_1, p.city, p.state, p.country,
                                       conf.full_name as confirmed_by_name
                                FROM tenant_payments tp
                                JOIN users u ON tp.tenant_id = u.id
                                JOIN properties p ON tp.property_id = p.id
                                JOIN leases l ON tp.lease_id = l.id
                                JOIN units un ON l.unit_id = un.id
                                LEFT JOIN users conf ON tp.confirmed_by_user_id = conf.id
                                WHERE tp.id = :id LIMIT 1");
        $rStmt->execute(['id' => $paymentId]);
    }

    $payment = $rStmt->fetch();
    if (!$payment) {
        http_response_code(404);
        echo "<!DOCTYPE html><html><body style='font-family:sans-serif;padding:40px;text-align:center;'><h2>Receipt Not Found</h2><p>Could not locate the requested payment record or official receipt.</p><a href='/tenant'>Return to Tenant Dashboard</a></body></html>";
        exit;
    }

    require __DIR__ . '/../resources/views/tenant/receipt.php';
    exit;
}

// ==========================================
// TENANT, MAINTENANCE & PAYMENT APIS
// ==========================================

// 14. API: LODGE MAINTENANCE COMPLAINT / CONCERN (POST)
if ($path === '/api/v1/tenant/tickets' && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user || !in_array($user['role'], ['TENANT', 'SUPERADMIN'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied: Only authenticated tenants can lodge maintenance concerns.']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;

    $tenantId = (int)$user['id'];
    if ($user['role'] === 'SUPERADMIN' && !empty($data['tenant_id'])) {
        $tenantId = (int)$data['tenant_id'];
    }

    try {
        $service = new \App\Domain\Tenant\Services\TenantPortalService($db);
        $result = $service->createMaintenanceTicket($tenantId, $data);
        echo json_encode($result);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 15. API: FETCH TICKET THREAD & CONVERSATION (GET)
if (preg_match('#^/api/v1/tickets/(\d+)/thread$#', $path, $matches) && $method === 'GET') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required.']);
        exit;
    }

    $ticketId = (int)$matches[1];
    try {
        $service = new \App\Domain\Tenant\Services\TenantPortalService($db);
        $result = $service->getTicketWithMessages($ticketId, (int)$user['id'], $user['role']);
        echo json_encode($result);
    } catch (\Throwable $e) {
        http_response_code(403);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 16. API: POST MESSAGE IN TICKET CHAT THREAD (POST)
if (preg_match('#^/api/v1/tickets/(\d+)/messages$#', $path, $matches) && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required.']);
        exit;
    }

    $ticketId = (int)$matches[1];
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;

    $message = trim($data['message'] ?? '');
    if ($message === '') {
        http_response_code(422);
        echo json_encode(['error' => 'Message content cannot be empty.']);
        exit;
    }

    try {
        $service = new \App\Domain\Tenant\Services\TenantPortalService($db);
        // Privacy check: verify user has rights to participate in this ticket
        $service->getTicketWithMessages($ticketId, (int)$user['id'], $user['role']);

        $res = $service->postTicketMessage(
            ticketId: $ticketId,
            senderId: (int)$user['id'],
            senderRole: $user['role'],
            senderName: $user['full_name'],
            message: $message,
            attachment: $data['attachment_path'] ?? null
        );
        echo json_encode($res);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 17. API: CARETAKER TAG / ESCALATE CONCERN TO LANDLORD (POST)
if (preg_match('#^/api/v1/caretaker/tickets/(\d+)/escalate$#', $path, $matches) && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user || !in_array($user['role'], ['CARETAKER', 'SUPERADMIN'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied: Only assigned caretakers can escalate concerns to the landlord.']);
        exit;
    }

    $ticketId = (int)$matches[1];
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;

    $reason = trim($data['reason'] ?? 'Caretaker requested Landlord authorization and capital repair funding.');

    try {
        $service = new \App\Domain\Tenant\Services\TenantPortalService($db);
        $result = $service->escalateTicketToLandlord($ticketId, (int)$user['id'], $reason);
        echo json_encode($result);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 18. API: UPDATE TICKET STATUS (POST)
if (preg_match('#^/api/v1/tickets/(\d+)/status$#', $path, $matches) && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required.']);
        exit;
    }

    $ticketId = (int)$matches[1];
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;

    $status = trim($data['status'] ?? 'RESOLVED');

    try {
        $service = new \App\Domain\Tenant\Services\TenantPortalService($db);
        $result = $service->updateTicketStatus($ticketId, $status, $user['role'], $user['full_name']);
        echo json_encode($result);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 19. API: TENANT PAYMENT SUBMISSION (POST)
if ($path === '/api/v1/tenant/payments' && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user || !in_array($user['role'], ['TENANT', 'SUPERADMIN'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied: Only authenticated residents can submit payments.']);
        exit;
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;

    $tenantId = (int)$user['id'];
    if ($user['role'] === 'SUPERADMIN' && !empty($data['tenant_id'])) {
        $tenantId = (int)$data['tenant_id'];
    }

    try {
        $service = new \App\Domain\Tenant\Services\TenantPortalService($db);
        $result = $service->createPayment($tenantId, $data);
        echo json_encode($result);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 20. API: PAYMENT CONFIRMATION BY LANDLORD OR CARETAKER (POST)
if (preg_match('#^/api/v1/payments/(\d+)/confirm$#', $path, $matches) && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user || !in_array($user['role'], ['LANDLORD', 'CARETAKER', 'SUPERADMIN'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied: Only Landlords, Caretakers, or SuperAdmins can confirm payments.']);
        exit;
    }

    $paymentId = (int)$matches[1];
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;

    try {
        $service = new \App\Domain\Tenant\Services\TenantPortalService($db);
        $result = $service->confirmPayment($paymentId, (int)$user['id'], $user['role'], $data['notes'] ?? null);
        echo json_encode($result);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 21. API: LANDLORD TERMINATE TENANCY & VACATE UNIT (POST - Requirement 4)
if (preg_match('#^/api/v1/landlord/leases/(\d+)/terminate$#', $path, $matches) && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user || !in_array($user['role'], ['LANDLORD', 'SUPERADMIN'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied: Only Landlords or SuperAdmins can terminate tenancies and vacate units.']);
        exit;
    }

    $leaseId = (int)$matches[1];
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;

    $reason = trim($data['reason'] ?? 'End of Lease Term / Notice to Vacate / Non-renewal');

    try {
        $service = new \App\Domain\Tenancy\Services\TenancyTerminationService($db);
        $result = $service->terminateTenancy($leaseId, (int)$user['id'], $reason);
        echo json_encode($result);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 22. API: UPDATE PROPERTY CARETAKER DELEGATION (POST)
if (preg_match('#^/api/v1/landlord/properties/(\d+)/caretaker$#', $path, $matches) && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user || !in_array($user['role'], ['LANDLORD', 'SUPERADMIN'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied: Only Landlords or SuperAdmins can update caretaker assignments.']);
        exit;
    }

    $propertyId = (int)$matches[1];
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;

    $mode = trim($data['mode'] ?? 'SUPERADMIN_CONCIERGE');
    $caretakerData = $data['caretaker'] ?? [
        'name'  => $data['caretaker_name'] ?? ($data['name'] ?? null),
        'email' => $data['caretaker_email'] ?? ($data['email'] ?? null),
        'phone' => $data['caretaker_phone'] ?? ($data['phone'] ?? null),
    ];

    try {
        $service = new \App\Domain\Caretaker\Services\CaretakerDelegationService($db);
        $result = $service->updateDelegation(
            $propertyId,
            (int)$user['id'],
            $mode,
            $caretakerData,
            $user['role'] === 'SUPERADMIN'
        );
        echo json_encode($result);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 23. API: LANDLORD PAYMENT & GATEWAY SETTINGS
if ($path === '/api/v1/landlord/payment-settings') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required.']);
        exit;
    }

    $service = new \App\Domain\Payment\Services\PaymentSettingsService($db);
    $targetLandlordId = (int)$user['id'];
    if ($user['role'] === 'SUPERADMIN' && !empty($_REQUEST['landlord_id'])) {
        $targetLandlordId = (int)$_REQUEST['landlord_id'];
    }

    if ($method === 'GET') {
        echo json_encode($service->getSettings($targetLandlordId));
        exit;
    }

    if ($method === 'POST') {
        if (!in_array($user['role'], ['LANDLORD', 'SUPERADMIN'], true)) {
            http_response_code(403);
            echo json_encode(['error' => 'Access Denied: Only Landlords and SuperAdmins can modify payment settings.']);
            exit;
        }
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;
        try {
            $result = $service->saveSettings($targetLandlordId, $data);
            echo json_encode($result);
        } catch (\Throwable $e) {
            http_response_code(422);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}

// 24. API: FINANCIAL EXPENSES & ANALYTICS
if ($path === '/api/v1/landlord/expenses') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required.']);
        exit;
    }

    $service = new \App\Domain\Finance\Services\FinancialAnalyticsService($db);

    if ($method === 'GET') {
        $propertyId = isset($_GET['property_id']) ? (int)$_GET['property_id'] : null;
        echo json_encode($service->listExpenses($propertyId));
        exit;
    }

    if ($method === 'POST') {
        if (!in_array($user['role'], ['LANDLORD', 'CARETAKER', 'SUPERADMIN'], true)) {
            http_response_code(403);
            echo json_encode(['error' => 'Access Denied: Insufficient permissions to record expenses.']);
            exit;
        }
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;
        $data['recorded_by_id'] = (int)$user['id'];
        try {
            $result = $service->recordExpense($data);
            echo json_encode($result);
        } catch (\Throwable $e) {
            http_response_code(422);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}

if ($path === '/api/v1/landlord/financials' && $method === 'GET') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required.']);
        exit;
    }
    $propertyId = (int)($_GET['property_id'] ?? 1);
    $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $service = new \App\Domain\Finance\Services\FinancialAnalyticsService($db);
    echo json_encode([
        'financials' => $service->getPropertyFinancials($propertyId, $year),
        'tax_summary' => $service->getTaxSummary($propertyId, $year),
    ]);
    exit;
}

// 25. API: ARTISAN DIRECTORY & WORK DISPATCH
if ($path === '/api/v1/artisans') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required.']);
        exit;
    }
    $service = new \App\Domain\Artisan\Services\ArtisanService($db);
    $skill = $_GET['skill'] ?? null;
    echo json_encode($service->listArtisans(tradeSkill: $skill));
    exit;
}

if ($path === '/api/v1/artisans/create' && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user || !in_array($user['role'], ['LANDLORD', 'CARETAKER', 'SUPERADMIN'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied: Only managers can register artisans.']);
        exit;
    }
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;
    if ($user['role'] === 'LANDLORD') {
        $data['landlord_id'] = (int)$user['id'];
    }
    try {
        $service = new \App\Domain\Artisan\Services\ArtisanService($db);
        $res = $service->createArtisan($data);
        echo json_encode($res);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($path === '/api/v1/artisans/assign' && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user || !in_array($user['role'], ['LANDLORD', 'CARETAKER', 'SUPERADMIN'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied: Only managers can assign artisans.']);
        exit;
    }
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;
    $ticketId = (int)($data['ticket_id'] ?? 0);
    $artisanId = (int)($data['artisan_id'] ?? 0);
    $cost = (float)($data['estimated_cost'] ?? 0);
    try {
        $service = new \App\Domain\Artisan\Services\ArtisanService($db);
        $res = $service->assignToTicket($ticketId, $artisanId, $cost);
        echo json_encode($res);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($path === '/api/v1/artisans/status' && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user || !in_array($user['role'], ['LANDLORD', 'CARETAKER', 'SUPERADMIN'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied.']);
        exit;
    }
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;
    $ticketId = (int)($data['ticket_id'] ?? 0);
    $workStatus = trim($data['work_status'] ?? 'IN_PROGRESS');
    $actualCost = isset($data['actual_cost']) ? (float)$data['actual_cost'] : null;
    try {
        $service = new \App\Domain\Artisan\Services\ArtisanService($db);
        $res = $service->updateTicketWorkStatus($ticketId, $workStatus, $actualCost);
        echo json_encode($res);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 26. API: COMMUNITY NOTICE BOARD & ANNOUNCEMENTS
if ($path === '/api/v1/community/announcements') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required.']);
        exit;
    }
    $service = new \App\Domain\Community\Services\CommunityService($db);
    if ($method === 'GET') {
        $propertyId = isset($_GET['property_id']) ? (int)$_GET['property_id'] : null;
        echo json_encode($service->listAnnouncements($propertyId));
        exit;
    }
    if ($method === 'POST') {
        if (!in_array($user['role'], ['LANDLORD', 'CARETAKER', 'SUPERADMIN'], true)) {
            http_response_code(403);
            echo json_encode(['error' => 'Access Denied: Only managers can publish community announcements.']);
            exit;
        }
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;
        $data['sender_id'] = (int)$user['id'];
        $data['sender_role'] = $user['role'];
        try {
            $res = $service->createAnnouncement($data);
            echo json_encode($res);
        } catch (\Throwable $e) {
            http_response_code(422);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}

// 27. API: FACILITY BOOKINGS
if ($path === '/api/v1/community/facilities') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required.']);
        exit;
    }
    $service = new \App\Domain\Community\Services\CommunityService($db);
    $propertyId = isset($_GET['property_id']) ? (int)$_GET['property_id'] : null;
    $tenantId = ($user['role'] === 'TENANT') ? (int)$user['id'] : null;
    echo json_encode($service->listBookings($propertyId, $tenantId));
    exit;
}

if ($path === '/api/v1/community/facilities/book' && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user || !in_array($user['role'], ['TENANT', 'SUPERADMIN'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied: Only residents can book facilities.']);
        exit;
    }
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;
    if ($user['role'] === 'TENANT') {
        $data['tenant_id'] = (int)$user['id'];
    }
    try {
        $service = new \App\Domain\Community\Services\CommunityService($db);
        $res = $service->createBooking($data);
        echo json_encode($res);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($path === '/api/v1/community/facilities/status' && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user || !in_array($user['role'], ['CARETAKER', 'LANDLORD', 'SUPERADMIN'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied: Only managers can approve or decline bookings.']);
        exit;
    }
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;
    $bookingId = (int)($data['booking_id'] ?? 0);
    $status = trim($data['status'] ?? 'APPROVED');
    try {
        $service = new \App\Domain\Community\Services\CommunityService($db);
        $res = $service->updateBookingStatus($bookingId, $status);
        echo json_encode($res);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 28. API: ALERT RULES & AUTOMATED REMINDER SIMULATION
if ($path === '/api/v1/alerts/rules') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required.']);
        exit;
    }
    $service = new \App\Domain\Reminders\Services\AlertRulesService($db);
    if ($method === 'GET') {
        $scope = $_GET['scope'] ?? null;
        $targetId = isset($_GET['target_id']) ? (int)$_GET['target_id'] : null;
        echo json_encode($service->listRules($scope, $targetId));
        exit;
    }
    if ($method === 'POST') {
        if (!in_array($user['role'], ['LANDLORD', 'SUPERADMIN'], true)) {
            http_response_code(403);
            echo json_encode(['error' => 'Access Denied: Only Landlords and SuperAdmins can configure alert rules.']);
            exit;
        }
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;
        try {
            $res = $service->saveRule($data);
            echo json_encode($res);
        } catch (\Throwable $e) {
            http_response_code(422);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}

if ($path === '/api/v1/alerts/simulate' && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required.']);
        exit;
    }
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;
    $ruleId = (int)($data['rule_id'] ?? 1);
    try {
        $service = new \App\Domain\Reminders\Services\AlertRulesService($db);
        $res = $service->triggerRuleSimulation($ruleId);
        echo json_encode($res);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 29. API: SUPERADMIN EDITABLE LANDLORD PROFILE & KYC VERIFICATION
if ($path === '/api/v1/superadmin/landlords/update' && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user || $user['role'] !== 'SUPERADMIN') {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied: SuperAdmin privileges required.']);
        exit;
    }
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;
    $landlordId = (int)($data['landlord_id'] ?? 0);
    try {
        $service = new SuperAdminDataService($db);
        $res = $service->updateLandlordProfile($landlordId, $data);
        echo json_encode($res);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

if ($path === '/api/v1/superadmin/kyc/verify' && $method === 'POST') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user || !in_array($user['role'], ['SUPERADMIN', 'LANDLORD'], true)) {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied: Insufficient permissions to verify resident KYC.']);
        exit;
    }
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?? $_POST;
    $tenantId = (int)($data['tenant_id'] ?? 0);
    $status = trim($data['status'] ?? 'VERIFIED');
    $nin = $data['nin'] ?? $data['id_number'] ?? null;
    $bvn = $data['bvn'] ?? null;
    $notes = $data['admin_notes'] ?? ($data['notes'] ?? null);
    try {
        $service = new SuperAdminDataService($db);
        $res = $service->updateTenantKyc($tenantId, $status, $nin, $bvn, $notes);
        echo json_encode($res);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// 30. API: SUPERADMIN DEEP ESTATE OPERATIONS AUDIT
if ($path === '/api/v1/superadmin/estates/operations' && $method === 'GET') {
    header('Content-Type: application/json');
    $user = $auth->currentUser();
    if (!$user || $user['role'] !== 'SUPERADMIN') {
        http_response_code(403);
        echo json_encode(['error' => 'Access Denied: SuperAdmin privileges required.']);
        exit;
    }
    $propertyId = (int)($_GET['property_id'] ?? 1);
    try {
        $service = new SuperAdminDataService($db);
        $res = $service->getEstateDeepOperations($propertyId);
        echo json_encode($res);
    } catch (\Throwable $e) {
        http_response_code(422);
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

http_response_code(404);
echo "Not found";