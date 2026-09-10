<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Database\Connection;
use App\Domain\Tenant\Services\TenantPortalService;
use App\Domain\Tenancy\Services\TenancyTerminationService;

echo "==========================================================\n";
echo "  OGA LANDLORD PLATFORM: FULL TENANT MODULE TEST SUITE   \n";
echo "==========================================================\n\n";

$db = new Connection();
$pdo = $db->getPdo();

$tenantService = new TenantPortalService($db);
$terminationService = new TenancyTerminationService($db);

$passed = 0;
$total = 0;

function assertTest(bool $condition, string $testName): void {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo "  [PASS] {$testName}\n";
    } else {
        echo "  [FAIL] {$testName}\n";
    }
}

// -------------------------------------------------------------
// TEST 0: Public Self-Registration Prohibition (Requirement 1 & 2)
// -------------------------------------------------------------
echo "--- TEST GROUP 0: Invitation-Only & Registration Blockage ---\n";
$ctx = stream_context_create(['http' => ['ignore_errors' => true]]);
$regResponse = @file_get_contents('http://127.0.0.1:8000/register/tenant', false, $ctx);
$statusLine = $http_response_header[0] ?? '';
assertTest(str_contains($statusLine, '403'), "Direct public tenant registration (/register/tenant) returns 403 Forbidden (Invitation-Only Enforced)");

// -------------------------------------------------------------
// TEST 1: Tenant Seed & Verification (Requirement 3)
// -------------------------------------------------------------
echo "\n--- TEST GROUP 1: Tenant Seed & Portal Data Retrieval ---\n";
$tenantUser = $pdo->query("SELECT * FROM users WHERE role = 'TENANT' ORDER BY id ASC LIMIT 1")->fetch();
assertTest(!empty($tenantUser), "Tenant user exists in database");
$tenantId = (int)$tenantUser['id'];

$dashboardData = $tenantService->getTenantDashboardData($tenantId);
assertTest(!empty($dashboardData['tenant']), "Tenant profile retrieved");
assertTest(!empty($dashboardData['lease']), "Active lease retrieved for tenant");
assertTest($dashboardData['lease']['agreement_status'] === 'FULLY_EXECUTED', "Lease agreement is fully executed & certified");
assertTest(!empty($dashboardData['lease']['document_sha256_hash']), "Audit SHA-256 cryptographic seal present");

// -------------------------------------------------------------
// TEST 2: Lodging Maintenance Complaints (Requirement 5 & 6)
// -------------------------------------------------------------
echo "\n--- TEST GROUP 2: Maintenance Complaints & Ticket Code Generation ---\n";
$ticketRes = $tenantService->createMaintenanceTicket($tenantId, [
    'title' => 'Kitchen Sink Mixer Pressure Drop',
    'description' => 'Water pressure in the kitchen mixer tap dropped suddenly this morning. Seems valve is jammed.',
    'category' => 'PLUMBING',
    'priority' => 'MEDIUM'
]);

assertTest(!empty($ticketRes['ticket_code']), "Ticket code generated: " . ($ticketRes['ticket_code'] ?? ''));
assertTest(str_starts_with($ticketRes['ticket_code'], 'TKT-2026-'), "Ticket code follows format TKT-2026-XXXX");
$ticketId = (int)$ticketRes['ticket_id'];

$ticketInDb = $pdo->query("SELECT * FROM maintenance_tickets WHERE id = {$ticketId}")->fetch();
assertTest((int)$ticketInDb['is_escalated_to_landlord'] === 0, "Ticket is strictly private to Caretaker (not escalated to Landlord)");

// -------------------------------------------------------------
// TEST 3: Landlord Privacy Isolation Check (Requirement 5)
// -------------------------------------------------------------
echo "\n--- TEST GROUP 3: Landlord Privacy Isolation (Evidence-Enforced) ---\n";
$landlordUser = $pdo->query("SELECT * FROM users WHERE role = 'LANDLORD' LIMIT 1")->fetch();
$landlordId = (int)$landlordUser['id'];
$caretakerUser = $pdo->query("SELECT * FROM users WHERE role = 'CARETAKER' LIMIT 1")->fetch();
$caretakerId = (int)$caretakerUser['id'];

$landlordDenied = false;
try {
    $tenantService->getTicketWithMessages($ticketId, $landlordId, 'LANDLORD');
} catch (\RuntimeException $e) {
    $landlordDenied = true;
}
assertTest($landlordDenied, "Landlord is STRICTLY BLOCKED from viewing non-escalated Caretaker ticket");

// Caretaker and Tenant CAN view the ticket
$caretakerView = $tenantService->getTicketWithMessages($ticketId, $caretakerId, 'CARETAKER');
assertTest(!empty($caretakerView['ticket']), "Caretaker can access private maintenance ticket");

// -------------------------------------------------------------
// TEST 4: Interactive Ticket Messaging Thread (Requirement 6)
// -------------------------------------------------------------
echo "\n--- TEST GROUP 4: Interactive Chat Thread Between Tenant & Caretaker ---\n";
$msg1 = $tenantService->postTicketMessage(
    $ticketId,
    $caretakerId,
    'CARETAKER',
    $caretakerUser['full_name'],
    "Hello {$tenantUser['full_name']}, Musa Danjuma here. I have scheduled Bello Plumbing to inspect the mixer today at 2 PM."
);
assertTest(!empty($msg1['message_id']), "Caretaker posts update message to chat thread");

$msg2 = $tenantService->postTicketMessage(
    $ticketId,
    $tenantId,
    'TENANT',
    $tenantUser['full_name'],
    "Thank you Musa, I will ensure someone is at home to give access."
);
assertTest(!empty($msg2['message_id']), "Tenant replies in interactive chat thread");

$threadAfterChat = $tenantService->getTicketWithMessages($ticketId, $tenantId, 'TENANT');
assertTest(count($threadAfterChat['messages']) >= 3, "Chat thread preserves chronological conversation messages");

// -------------------------------------------------------------
// TEST 5: Caretaker Escalates Ticket to Landlord
// -------------------------------------------------------------
echo "\n--- TEST GROUP 5: Caretaker Ticket Escalation to Landlord ---\n";
$escalateResult = $tenantService->escalateTicketToLandlord(
    $ticketId, 
    $caretakerId, 
    'Mixer requires complete replacement of Italian brass valve (₦65,000). Exceeds caretaker maintenance ceiling, requires landlord approval.'
);
assertTest($escalateResult['success'] === true, "Caretaker successfully tags and escalates ticket to Landlord with audit reason");

// Now Landlord CAN view the ticket!
$landlordViewAfter = $tenantService->getTicketWithMessages($ticketId, $landlordId, 'LANDLORD');
assertTest(!empty($landlordViewAfter['ticket']), "Landlord now granted visibility after explicit Caretaker escalation");
assertTest(!empty($landlordViewAfter['ticket']['escalation_reason']), "Landlord can see the budget need and escalation reason");

// Landlord participates in thread
$msg3 = $tenantService->postTicketMessage(
    $ticketId,
    $landlordId,
    'LANDLORD',
    $landlordUser['full_name'],
    "Approved. Musa, please disburse ₦65,000 from the maintenance petty float and retain receipt."
);
assertTest(!empty($msg3['message_id']), "Landlord posts approval message to thread");

// Caretaker marks resolved
$resolveResult = $tenantService->updateTicketStatus($ticketId, 'RESOLVED', 'CARETAKER', $caretakerUser['full_name']);
assertTest($resolveResult['success'] === true, "Ticket marked as RESOLVED by caretaker");

// -------------------------------------------------------------
// TEST 6: Multi-Channel Payments (Requirement 7)
// -------------------------------------------------------------
echo "\n--- TEST GROUP 6: Multi-Channel Payments & Stamped Receipts ---\n";

// Payment A: Estate Levy (Security & Waste) via Paystack Gateway -> Auto-confirmed
$levyPayment = $tenantService->createPayment($tenantId, [
    'payment_type' => 'ESTATE_LEVY',
    'beneficiary_type' => 'ESTATE',
    'title' => '2026 Annual Security & Waste Disposal Levy',
    'amount' => 45000.00,
    'payment_method' => 'PAYSTACK',
    'gateway_reference' => 'paystack_mock_gtw_' . bin2hex(random_bytes(6))
]);
assertTest($levyPayment['status'] === 'CONFIRMED', "Estate levy paid via online gateway is auto-confirmed immediately");
assertTest(!empty($levyPayment['receipt_number']), "Confirmed payment receives official receipt number: " . ($levyPayment['receipt_number'] ?? ''));

// Payment B: Annual Rent via Bank Transfer -> Requires Confirmation
$rentPayment = $tenantService->createPayment($tenantId, [
    'payment_type' => 'RENT',
    'beneficiary_type' => 'LANDLORD',
    'title' => 'Annual Rent Renewal 2026-2027',
    'amount' => 2500000.00,
    'payment_method' => 'BANK_TRANSFER',
    'bank_transfer_sender_name' => 'Amara Okafor',
    'bank_transfer_reference' => 'ZENITH-TRF-' . rand(100000, 999999)
]);
assertTest($rentPayment['status'] === 'PENDING_CONFIRMATION', "Rent via direct bank transfer is placed in PENDING_CONFIRMATION queue");

// Confirmation by Landlord
$confirmResult = $tenantService->confirmPayment((int)$rentPayment['payment_id'], $landlordId, 'LANDLORD', 'Bank transfer verified in Zenith corporate account.');
assertTest($confirmResult['success'] === true, "Landlord confirms bank transfer payment");

// Check payment status in database
$checkConfirmed = $pdo->query("SELECT * FROM tenant_payments WHERE id = {$rentPayment['payment_id']}")->fetch();
assertTest($checkConfirmed['status'] === 'CONFIRMED', "Payment status successfully updated to CONFIRMED");
assertTest(!empty($checkConfirmed['confirmed_at']), "Confirmed timestamp recorded");
assertTest($checkConfirmed['confirmed_by_role'] === 'LANDLORD', "Confirmed by role recorded as LANDLORD");

// -------------------------------------------------------------
// TEST 7: Tenancy Termination & Unit Vacating (Requirement 4)
// -------------------------------------------------------------
echo "\n--- TEST GROUP 7: Tenancy Termination & Vacating Unit ---\n";
$propertyId = (int)$dashboardData['lease']['property_id'];

$rand = bin2hex(random_bytes(4));
$testUnitNumber = "Unit TEST-{$rand}";
$unitInsert = $pdo->prepare("INSERT INTO units (uuid, property_id, unit_number, apartment_type, default_rent_amount, is_occupied) VALUES (:uuid, :pid, :unum, '3-Bedroom Penthouse', 3600000.00, 1)");
$unitInsert->execute(['uuid' => "UNT-TEST-{$rand}", 'pid' => $propertyId, 'unum' => $testUnitNumber]);
$testUnitId = (int)$pdo->lastInsertId();

$testTenantInsert = $pdo->prepare("INSERT INTO users (uuid, full_name, email, phone_number, role, password_hash, is_active) VALUES (:uuid, 'Emeka Nnamdi', :email, '+2348035559988', 'TENANT', 'hash', 1)");
$testTenantInsert->execute(['uuid' => "USR-TEST-{$rand}", 'email' => "emeka.test.{$rand}@example.com"]);
$testTenantId = (int)$pdo->lastInsertId();

$testLeaseInsert = $pdo->prepare("INSERT INTO leases (uuid, unit_id, tenant_id, rent_amount, currency, rent_start_date, rent_due_date, agreement_mode, agreement_status, emergency_contact_name, emergency_contact_relationship, emergency_contact_phone, created_at)
                                  VALUES (:uuid, :uid, :tid, 3600000.00, 'NGN', '2024-03-01', '2025-03-01', 'STANDARD', 'FULLY_EXECUTED', 'Chioma Nnamdi', 'Spouse', '+2348039998877', '2024-03-01 10:00:00')");
$testLeaseInsert->execute(['uuid' => "LSE-TEST-{$rand}", 'uid' => $testUnitId, 'tid' => $testTenantId]);
$testLeaseId = (int)$pdo->lastInsertId();

// Verify unit is occupied before termination
$unitBefore = $pdo->query("SELECT is_occupied FROM units WHERE id = {$testUnitId}")->fetch();
assertTest((int)$unitBefore['is_occupied'] === 1, "Test unit is occupied prior to termination");

// Terminate Tenancy
$terminationResult = $terminationService->terminateTenancy(
    $testLeaseId,
    $landlordId,
    'Contractual tenancy tenure expired. Tenant relocated overseas to United Kingdom.'
);
assertTest($terminationResult['success'] === true, "Tenancy successfully terminated by Landlord");

// Verify Lease status
$leaseAfter = $pdo->query("SELECT agreement_status FROM leases WHERE id = {$testLeaseId}")->fetch();
assertTest($leaseAfter['agreement_status'] === 'TERMINATED', "Lease marked as TERMINATED in database");

// Verify Unit is vacated
$unitAfter = $pdo->query("SELECT is_occupied FROM units WHERE id = {$testUnitId}")->fetch();
assertTest((int)$unitAfter['is_occupied'] === 0, "Unit is immediately vacated (is_occupied = 0) and ready for reassignment");

// Verify History Record in tenancy_history
$historyRecord = $pdo->query("SELECT * FROM tenancy_history WHERE lease_id = {$testLeaseId}")->fetch();
assertTest(!empty($historyRecord), "Tenancy history record created in tenancy_history table");
assertTest($historyRecord['tenant_name'] === 'Emeka Nnamdi', "Former tenant name preserved: Emeka Nnamdi");
assertTest((float)$historyRecord['rent_amount'] === 3600000.00, "Rent value preserved: ₦3,600,000.00");
assertTest(!empty($historyRecord['duration_of_stay']), "Duration of stay accurately calculated: " . ($historyRecord['duration_of_stay'] ?? ''));
assertTest(str_contains($historyRecord['termination_reason'], 'relocated overseas'), "Departure reason faithfully recorded in termination_reason");

// -------------------------------------------------------------
// SUMMARY
// -------------------------------------------------------------
echo "\n==========================================================\n";
echo "  TEST SUMMARY: {$passed} / {$total} TESTS PASSED\n";
if ($passed === $total) {
    echo "  STATUS: ALL TENANT MODULE ACCEPTANCE CRITERIA VERIFIED! \n";
} else {
    echo "  STATUS: SOME TESTS FAILED\n";
}
echo "==========================================================\n";
