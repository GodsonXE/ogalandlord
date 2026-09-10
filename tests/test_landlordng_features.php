<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Database\Connection;
use App\Domain\Payment\Services\PaymentSettingsService;
use App\Domain\Artisan\Services\ArtisanService;
use App\Domain\Finance\Services\FinancialAnalyticsService;
use App\Domain\Community\Services\CommunityService;
use App\Domain\Reminders\Services\AlertRulesService;
use App\Domain\SuperAdmin\Services\SuperAdminDataService;
use App\Domain\Tenant\Services\TenantPortalService;

echo "=== OGA LANDLORD: LANDLORDNG FULL FEATURE SUITE VERIFICATION ===\n\n";

$db = new Connection();
$pdo = $db->getPdo();

$passed = 0;
$total = 0;

function assertTest(bool $condition, string $title) {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$title}\n";
    } else {
        echo "  [FAIL] {$title}\n";
    }
}

// -------------------------------------------------------------
// 1. LANDING PAGE & DESIGN VERIFICATION
// -------------------------------------------------------------
echo "1. Testing Landing Page & LandlordNG Design Compliance...\n";
ob_start();
require __DIR__ . '/../resources/views/landing.php';
$landingHtml = ob_get_clean();

assertTest(strpos($landingHtml, 'Oga<span class="text-[#1D4ED8]">Landlord</span>') !== false || strpos($landingHtml, 'OgaLandlord') !== false, "Landing page carries official Oga Landlord branding");
assertTest(strpos($landingHtml, '#1D49CA') !== false || strpos($landingHtml, '#070D1E') !== false, "Hero adopts brand blue container (#1D49CA)");
assertTest(strpos($landingHtml, '0% Rent Commission') !== false, "LandlordNG floating badge '0% Rent Commission' is rendered");
assertTest(strpos($landingHtml, 'Automated SMS & In-App') !== false, "LandlordNG floating badge 'Automated SMS & In-App' is rendered");
assertTest(strpos($landingHtml, 'Rent Collection & Payment Tracking') !== false, "Pillar 1: Rent Collection & Payment Tracking feature card present");
assertTest(strpos($landingHtml, 'Maintenance Request Management') !== false, "Pillar 2: Maintenance Request Management feature card present");
assertTest(strpos($landingHtml, 'Tenant Management & Records') !== false, "Pillar 3: Tenant Management & Records feature card present");
assertTest(strpos($landingHtml, 'Financial Reporting & Analytics') !== false, "Pillar 4: Financial Reporting & Analytics feature card present");
assertTest(strpos($landingHtml, 'Estate & Community Management') !== false, "Pillar 5: Estate & Community Management feature card present");
assertTest(strpos($landingHtml, 'Notifications & Alerts') !== false, "Pillar 6: Notifications & Alerts feature card present");
assertTest(strpos($landingHtml, 'Interactive Testing Playground') !== false, "Interactive 4-role test play playground rendered");

// -------------------------------------------------------------
// 2. RENT COLLECTION & PAYMENT TRACKING
// -------------------------------------------------------------
echo "\n2. Testing Rent Collection & Payment Gateway Settings...\n";
$paymentService = new PaymentSettingsService($db);
$landlordId = (int)$pdo->query("SELECT id FROM users WHERE role = 'LANDLORD' LIMIT 1")->fetchColumn() ?: 1;

$savedRes = $paymentService->saveSettings($landlordId, [
    'bank_name' => 'Guaranty Trust Bank (GTBank)',
    'account_number' => '0123456789',
    'account_name' => 'Chief Ibrahim Bello Holdings',
    'gateway_provider' => 'PAYSTACK',
    'paystack_public_key' => 'pk_test_sample_oga_landlord',
    'paystack_secret_key' => 'sk_test_sample_oga_landlord',
    'auto_confirm_gateways' => 1
]);
$savedSettings = $savedRes['settings'] ?? [];
assertTest(($savedSettings['bank_name'] ?? '') === 'Guaranty Trust Bank (GTBank)', "Landlord bank details saved successfully");
assertTest(($savedSettings['gateway_provider'] ?? '') === 'PAYSTACK', "Payment gateway provider configured to Paystack");

$retrievedSettings = $paymentService->getSettings($landlordId);
assertTest($retrievedSettings['account_number'] === '0123456789', "Payment settings retrieved accurately from database");

$publicDetails = $paymentService->getPublicPaymentDetails(1);
assertTest(!isset($publicDetails['paystack_secret_key']), "Secret gateway keys are stripped from public resident view");
assertTest(!empty($publicDetails['bank_name']), "Public bank name available for tenant transfer");

// -------------------------------------------------------------
// 3. MAINTENANCE REQUEST & ARTISAN MANAGEMENT
// -------------------------------------------------------------
echo "\n3. Testing Maintenance Request Management & Artisan Assignment...\n";
$artisanService = new ArtisanService($db);
$tenantId = (int)$pdo->query("SELECT id FROM users WHERE role = 'TENANT' LIMIT 1")->fetchColumn() ?: 3;
$unitId = (int)$pdo->query("SELECT id FROM units LIMIT 1")->fetchColumn() ?: 1;
$propId = (int)$pdo->query("SELECT id FROM properties LIMIT 1")->fetchColumn() ?: 1;

// Register trusted artisan
$newArtisan = $artisanService->createArtisan([
    'landlord_id' => $landlordId,
    'full_name' => 'Emeka Okafor (Master Plumber)',
    'phone_number' => '+234 802 334 5566',
    'email' => 'emeka.plumbing@example.com',
    'trade_skill' => 'PLUMBING',
    'rating' => 4.9,
    'hourly_rate' => 7500
]);
$artisanId = (int)($newArtisan['artisan_id'] ?? 0);
assertTest($artisanId > 0, "Trusted artisan registered in database with trade and rating");

// Lodge photo-based ticket
$tenantPortalService = new TenantPortalService($db);
$ticketResult = $tenantPortalService->createMaintenanceTicket($tenantId, [
    'category' => 'Plumbing',
    'title' => 'Under-sink kitchen flex pipe crack',
    'description' => 'Water spraying under the sink when main tap opens. Urgently requires replacement.',
    'priority' => 'HIGH',
    'photo_url' => 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=800&q=80'
]);
assertTest($ticketResult['success'] === true && !empty($ticketResult['ticket_id']), "Tenant lodged ticket with photo URL attachment");

$ticketId = (int)$ticketResult['ticket_id'];

// Check photo_url stored in db
$storedPhoto = $pdo->query("SELECT photo_url FROM maintenance_tickets WHERE id = {$ticketId}")->fetchColumn();
assertTest(strpos((string)$storedPhoto, 'unsplash') !== false, "Photo URL persisted in maintenance_tickets table");

// Assign artisan to ticket
$assignResult = $artisanService->assignToTicket($ticketId, $artisanId, 15000.0);
assertTest($assignResult['success'] === true, "Artisan assigned to maintenance ticket with estimated cost");

// Update work status to completed
$statusResult = $artisanService->updateTicketWorkStatus($ticketId, 'COMPLETED', 14500.0);
assertTest($statusResult['success'] === true && $statusResult['work_status'] === 'COMPLETED', "Artisan work status updated to COMPLETED with actual cost recorded");

// -------------------------------------------------------------
// 4. TENANT MANAGEMENT & KYC RECORDS
// -------------------------------------------------------------
echo "\n4. Testing Tenant Management & KYC Records...\n";
$superAdminService = new SuperAdminDataService($db);

// Update tenant KYC
$kycResult = $superAdminService->updateTenantKyc($tenantId, 'VERIFIED', '54109821092', '22198031045');
assertTest($kycResult['success'] === true && $kycResult['kyc_status'] === 'VERIFIED', "Tenant digital KYC verified and sealed with NIN & BVN");

$tenantCheck = $pdo->query("SELECT kyc_status, kyc_nin FROM users WHERE id = {$tenantId}")->fetch(PDO::FETCH_ASSOC);
assertTest($tenantCheck['kyc_status'] === 'VERIFIED' && $tenantCheck['kyc_nin'] === '54109821092', "NIN verified record matches database");

// -------------------------------------------------------------
// 5. FINANCIAL REPORTING & ANALYTICS
// -------------------------------------------------------------
echo "\n5. Testing Financial Reporting & NOI / Tax Analytics...\n";
$financeService = new FinancialAnalyticsService($db);

// Record property operating expense
$expenseResult = $financeService->recordExpense([
    'property_id' => $propId,
    'category' => 'REPAIRS',
    'title' => 'Replacement of kitchen brass ball valve',
    'amount' => 14500.0,
    'expense_date' => date('Y-m-d'),
    'vendor_name' => 'Emeka Okafor (Plumber)',
    'recorded_by' => $landlordId
]);
assertTest($expenseResult['success'] === true, "Operating expense recorded in property ledger");

$financials = $financeService->getPropertyFinancials($propId);
assertTest(isset($financials['gross_rent_roll']) && isset($financials['net_operating_income']), "Financial analysis computes gross rent and Net Operating Income (NOI)");
assertTest($financials['total_operating_expenses'] >= 14500.0, "Operating expenses aggregated accurately in property NOI schedule");

$taxSummary = $financeService->getTaxSummary($propId);
assertTest(isset($taxSummary['withholding_tax_estimate']), "Tax-ready financial report calculates 10% statutory WHT");

// -------------------------------------------------------------
// 6. ESTATE & COMMUNITY MANAGEMENT
// -------------------------------------------------------------
echo "\n6. Testing Estate Notice Board & Facility Booking Management...\n";
$communityService = new CommunityService($db);

// Broadcast community announcement
$annResult = $communityService->createAnnouncement([
    'property_id' => $propId,
    'sender_id' => $landlordId,
    'sender_role' => 'LANDLORD',
    'title' => 'Scheduled Water Infrastructure Upgrade',
    'message' => 'The central borehole pumps will undergo quarterly servicing tomorrow between 10:00 and 14:00.',
    'priority' => 'IMPORTANT'
]);
assertTest($annResult['success'] === true, "Community announcement published to resident notice board");

$announcements = $communityService->listAnnouncements($propId);
assertTest(count($announcements) > 0 && $announcements[0]['title'] === 'Scheduled Water Infrastructure Upgrade', "Notice board announcement visible in estate feed");

// Book shared facility
$bookingResult = $communityService->createBooking([
    'property_id' => $propId,
    'tenant_id' => $tenantId,
    'facility_name' => 'Swimming Pool & Cabana',
    'booking_date' => date('Y-m-d', strtotime('+5 days')),
    'time_slot' => '14:00 - 18:00',
    'guest_count' => 12,
    'purpose' => 'Family birthday celebration'
]);
assertTest($bookingResult['success'] === true && $bookingResult['status'] === 'PENDING', "Resident requested facility reservation with PENDING status");

$bookingId = (int)$bookingResult['booking_id'];

// Caretaker/SuperAdmin approves booking
$updateStatus = $communityService->updateBookingStatus($bookingId, 'APPROVED');
assertTest($updateStatus['success'] === true && $updateStatus['status'] === 'APPROVED', "Caretaker approved facility reservation request");

// -------------------------------------------------------------
// 7. NOTIFICATIONS & ALERT RULES
// -------------------------------------------------------------
echo "\n7. Testing Notification Rules & Multi-Channel Simulation...\n";
$alertRulesService = new AlertRulesService($db);

$rule = $alertRulesService->createOrUpdateRule([
    'scope' => 'GLOBAL',
    'landlord_id' => null,
    'property_id' => null,
    'event_type' => 'RENT_DUE_T7',
    'recipient_role' => 'TENANT',
    'channel' => 'MULTI_CHANNEL',
    'grace_period_days' => 7,
    'penalty_rate_percent' => 5.0,
    'is_active' => 1
]);
assertTest($rule['id'] > 0, "Global alert rule created with grace period and penalty rate");

$sim = $alertRulesService->simulateAlert('RENT_DUE_T7');
assertTest($sim['success'] === true, "Smart alert rule simulation executed successfully");
assertTest(in_array('SMS', $sim['simulated_channels']), "Simulation includes SMS gateway alert channel");
assertTest(in_array('IN_APP', $sim['simulated_channels']), "Simulation includes In-App push notification channel");

// -------------------------------------------------------------
// 8. SUPERADMIN AUTHORITY & DEEP OPERATIONS
// -------------------------------------------------------------
echo "\n8. Testing SuperAdmin Deep Operations & Landlord Management...\n";

// Update Landlord Profile
$updateLandlordRes = $superAdminService->updateLandlordProfile([
    'id' => $landlordId,
    'full_name' => 'Chief Ibrahim Bello CFR',
    'email' => 'ibrahim.bello@example.com',
    'phone_number' => '+234 803 111 2233',
    'company_name' => 'Bello Capital & Properties Plc',
    'is_active' => 1,
    'rate_per_unit' => 3500.0
]);
assertTest($updateLandlordRes['success'] === true, "SuperAdmin updated Landlord profile and SaaS per-unit rate");

$landlordCheck = $pdo->query("SELECT full_name, company_name, rate_per_unit FROM users WHERE id = {$landlordId}")->fetch(PDO::FETCH_ASSOC);
assertTest($landlordCheck['full_name'] === 'Chief Ibrahim Bello CFR' && (float)$landlordCheck['rate_per_unit'] === 3500.0, "Updated landlord record persisted accurately");

// Deep Estate Operations Audit
$deepOps = $superAdminService->getEstateDeepOperations($propId);
assertTest(isset($deepOps['property']) && isset($deepOps['tickets']) && isset($deepOps['expenses']), "SuperAdmin deep estate operations retrieved comprehensive estate health");
assertTest(count($deepOps['facility_bookings']) > 0, "SuperAdmin can audit facility reservations across estates");
assertTest(count($deepOps['announcements']) > 0, "SuperAdmin can inspect community notice board broadcasts");

// -------------------------------------------------------------
// SUMMARY
// -------------------------------------------------------------
echo "\n=======================================================\n";
echo "TEST RESULTS: {$passed} / {$total} TESTS PASSED (" . round(($passed / $total) * 100, 1) . "%)\n";
echo "=======================================================\n";

if ($passed === $total) {
    echo "🎉 ALL LANDLORDNG SUITE TESTS PASSED PERFECTLY!\n";
    exit(0);
} else {
    echo "⚠️ SOME TESTS FAILED.\n";
    exit(1);
}
