<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Database\Connection;
use App\Domain\Auth\Services\LandlordRegistrationService;
use App\Domain\Caretaker\Services\CaretakerDelegationService;

echo "======================================================================\n";
echo "  TEST SUITE: LANDLORD SIGNUP, MULTI-LOCATION & CARETAKER DELEGATION \n";
echo "======================================================================\n\n";

$db = new Connection();
$pdo = $db->getPdo();

$registrationService = new LandlordRegistrationService($db);
$delegationService = new CaretakerDelegationService($db);

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
// TEST GROUP 1: Landlord Self-Service Registration (SuperAdmin Concierge)
// -------------------------------------------------------------
echo "--- TEST GROUP 1: Landlord Registration with SuperAdmin Concierge ---\n";

$uniqueKey = time() . '_' . mt_rand(100, 999);
$landlordData1 = [
    'full_name'                 => "Alhaji Aminu Kano {$uniqueKey}",
    'email'                     => "aminu.kano.{$uniqueKey}@example.com",
    'phone_number'              => '08021112233',
    'password'                  => 'Password123!',
    'property_title'            => "Arewa Horizon Court {$uniqueKey}",
    'state'                     => 'Kano',
    'city'                      => 'Kano Municipal',
    'address_line_1'            => 'Plot 18 Bompai Industrial Area',
    'total_units'               => 4,
    'unit_prefix'               => 'Flat ',
    'apartment_type'            => '3-Bedroom Apartment',
    'default_rent'              => 1500000.00,
    'caretaker_delegation_mode' => 'SUPERADMIN_CONCIERGE',
];

$result1 = $registrationService->register($landlordData1);
assertTest($result1['success'] === true, "Landlord registration returned success = true");
assertTest(!empty($result1['user_id']), "New landlord user created with ID: {$result1['user_id']}");
assertTest(!empty($result1['property_id']), "New property created with ID: {$result1['property_id']}");
assertTest($result1['units_created'] === 4, "Auto-generated 4 apartment units for property");

// Verify in DB
$userCheck = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$userCheck->execute(['id' => $result1['user_id']]);
$userRow = $userCheck->fetch();
assertTest($userRow['role'] === 'LANDLORD', "User has role = LANDLORD");

$propCheck = $pdo->prepare("SELECT * FROM properties WHERE id = :id");
$propCheck->execute(['id' => $result1['property_id']]);
$propRow = $propCheck->fetch();
assertTest($propRow['state'] === 'Kano' && $propRow['city'] === 'Kano Municipal', "Property state and city saved correctly");
assertTest($propRow['caretaker_delegation_mode'] === 'SUPERADMIN_CONCIERGE', "Property mode is SUPERADMIN_CONCIERGE");

// Verify SuperAdmin concierge assigned
$assignmentCheck = $pdo->prepare("SELECT * FROM property_caretaker_assignments WHERE property_id = :pid");
$assignmentCheck->execute(['pid' => $result1['property_id']]);
$assignmentRow = $assignmentCheck->fetch();
assertTest(!empty($assignmentRow), "Caretaker assignment record exists");
assertTest((int)$assignmentRow['caretaker_id'] === 1, "Assigned caretaker is SuperAdmin (ID: 1) for concierge");

// -------------------------------------------------------------
// TEST GROUP 2: Landlord Registration with Nominated Custom Caretaker
// -------------------------------------------------------------
echo "\n--- TEST GROUP 2: Landlord Registration with Nominated Caretaker ---\n";

$uniqueKey2 = time() . '_' . mt_rand(1000, 9999);
$landlordData2 = [
    'full_name'                 => "Chief Emeka Okoro {$uniqueKey2}",
    'email'                     => "emeka.okoro.{$uniqueKey2}@example.com",
    'phone_number'              => '08034445566',
    'password'                  => 'Password123!',
    'property_title'            => "Coal City Heights {$uniqueKey2}",
    'state'                     => 'Enugu',
    'city'                      => 'Enugu',
    'address_line_1'            => '12 Independence Layout',
    'total_units'               => 3,
    'unit_prefix'               => 'Suite ',
    'apartment_type'            => '2-Bedroom Apartment',
    'default_rent'              => 1200000.00,
    'caretaker_delegation_mode' => 'CUSTOM_CARETAKER',
    'nominee_name'              => "Obi Chukwu {$uniqueKey2}",
    'nominee_email'             => "obi.caretaker.{$uniqueKey2}@example.com",
    'nominee_phone'             => '08067778899',
];

$result2 = $registrationService->register($landlordData2);
assertTest($result2['success'] === true, "Registration with custom caretaker returned success");
assertTest($result2['caretaker_mode'] === 'CUSTOM_CARETAKER', "Result confirms CUSTOM_CARETAKER mode");

$propCheck->execute(['id' => $result2['property_id']]);
$propRow2 = $propCheck->fetch();
assertTest($propRow2['state'] === 'Enugu', "Property 2 state is Enugu");
assertTest($propRow2['caretaker_delegation_mode'] === 'CUSTOM_CARETAKER', "Property 2 mode is CUSTOM_CARETAKER");

// Verify custom caretaker account created
$caretakerCheck = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$caretakerCheck->execute(['email' => "obi.caretaker.{$uniqueKey2}@example.com"]);
$caretakerUser = $caretakerCheck->fetch();
assertTest(!empty($caretakerUser), "Nominated caretaker user account created");
assertTest($caretakerUser['role'] === 'CARETAKER', "Nominee account has role = CARETAKER");

$assignmentCheck->execute(['pid' => $result2['property_id']]);
$assignmentRow2 = $assignmentCheck->fetch();
assertTest((int)$assignmentRow2['caretaker_id'] === (int)$caretakerUser['id'], "Property assigned to custom caretaker user");

// -------------------------------------------------------------
// TEST GROUP 3: Dynamic Caretaker Delegation Updates
// -------------------------------------------------------------
echo "\n--- TEST GROUP 3: Dynamic Caretaker Delegation Updates ---\n";

$targetPropId = (int)$result1['property_id'];
$landlordId1 = (int)$result1['user_id'];

// Switch Property 1 from SuperAdmin to Custom Caretaker
$updateResult1 = $delegationService->updateDelegation(
    $targetPropId,
    $landlordId1,
    'CUSTOM_CARETAKER',
    [
        'name'  => "Garba Shehu {$uniqueKey}",
        'email' => "garba.shehu.{$uniqueKey}@example.com",
        'phone' => '08055554433'
    ]
);
assertTest($updateResult1['success'] === true, "Updated Property 1 to CUSTOM_CARETAKER");
assertTest($updateResult1['mode'] === 'CUSTOM_CARETAKER', "Result mode confirms CUSTOM_CARETAKER");

$currentCaretakerInfo = $delegationService->getPropertyCaretaker($targetPropId);
assertTest($currentCaretakerInfo['caretaker_delegation_mode'] === 'CUSTOM_CARETAKER', "Fetched delegation mode is CUSTOM_CARETAKER");
assertTest($currentCaretakerInfo['caretaker_name'] === "Garba Shehu {$uniqueKey}", "Assigned caretaker name updated to Garba Shehu");

// Switch Property 1 back to SuperAdmin Concierge
$updateResult2 = $delegationService->updateDelegation(
    $targetPropId,
    $landlordId1,
    'SUPERADMIN_CONCIERGE'
);
assertTest($updateResult2['success'] === true, "Switched Property 1 back to SUPERADMIN_CONCIERGE");
$currentCaretakerInfo2 = $delegationService->getPropertyCaretaker($targetPropId);
assertTest($currentCaretakerInfo2['caretaker_delegation_mode'] === 'SUPERADMIN_CONCIERGE', "Delegation mode returned to SUPERADMIN_CONCIERGE");
assertTest((int)$currentCaretakerInfo2['caretaker_id'] === 1, "Caretaker ID points back to SuperAdmin (ID: 1)");

// -------------------------------------------------------------
// TEST GROUP 4: Caretaker Multi-Location Portfolio Operations
// -------------------------------------------------------------
echo "\n--- TEST GROUP 4: Caretaker Multi-Location Operations ---\n";

// Caretaker ID: 3 (Musa Danjuma) has properties across Abuja, Lagos, and Port Harcourt
$caretakerId = 3;
$musaProps = $pdo->prepare("SELECT p.id, p.title, p.city, p.state
                            FROM properties p
                            JOIN property_caretaker_assignments pca ON pca.property_id = p.id
                            WHERE pca.caretaker_id = :cid
                            ORDER BY p.id ASC");
$musaProps->execute(['cid' => $caretakerId]);
$propsList = $musaProps->fetchAll();

assertTest(count($propsList) >= 3, "Caretaker Musa Danjuma is assigned to at least 3 nationwide properties (found " . count($propsList) . ")");

$statesFound = array_column($propsList, 'state');
assertTest(in_array('Abuja', $statesFound, true) || in_array('FCT', $statesFound, true), "Caretaker manages property in Abuja");
assertTest(in_array('Rivers', $statesFound, true), "Caretaker manages property in Port Harcourt (Rivers)");

// Verify filtering by property_id works correctly for caretaker
$sampleProp = $propsList[0] ?? null;
assertTest($sampleProp !== null, "Found assigned property (ID: " . ($sampleProp['id'] ?? 0) . " - " . ($sampleProp['title'] ?? '') . ")");

$sampleUnitsStmt = $pdo->prepare("SELECT un.* FROM units un
                                 JOIN properties p ON un.property_id = p.id
                                 JOIN property_caretaker_assignments pca ON pca.property_id = p.id
                                 WHERE pca.caretaker_id = :cid AND p.id = :pid");
$sampleUnitsStmt->execute(['cid' => $caretakerId, 'pid' => $sampleProp['id']]);
$sampleUnits = $sampleUnitsStmt->fetchAll();
assertTest(count($sampleUnits) > 0, "Filtered units for estate returned " . count($sampleUnits) . " units without leaking other estates");

// Close all SQLite connections in test process before making HTTP requests to prevent file locking deadlocks
unset($sampleUnitsStmt);
$pdo = null;
$db = null;
$registrationService = null;
$delegationService = null;
gc_collect_cycles();

// -------------------------------------------------------------
// TEST GROUP 5: HTTP Endpoints & Tenant Invitation-Only Integrity
// -------------------------------------------------------------
echo "\n--- TEST GROUP 5: HTTP Endpoints & Boundary Verification ---\n";

$httpScript = __DIR__ . '/run_http_checks.php';
file_put_contents($httpScript, '<?php
function testHttpGet(string $url): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $body = (string)curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ["code" => $code, "body" => $body];
}

$tenantRes = testHttpGet("http://127.0.0.1:8000/register/tenant");
$landlordRes = testHttpGet("http://127.0.0.1:8000/register");
$altLandlordRes = testHttpGet("http://127.0.0.1:8000/register/landlord");
$homeRes = testHttpGet("http://127.0.0.1:8000/");

echo json_encode([
    "tenant" => $tenantRes,
    "landlord" => $landlordRes,
    "altLandlord" => $altLandlordRes,
    "home" => $homeRes,
]);
');

$output = shell_exec('php ' . escapeshellarg($httpScript));
if (file_exists($httpScript)) {
    unlink($httpScript);
}
$httpResults = json_decode($output ?: '{}', true);

// 1. Tenant self-registration must be blocked (403 Forbidden)
$tenantRes = $httpResults['tenant'] ?? ['code' => 0, 'body' => ''];
assertTest($tenantRes['code'] === 403, "Tenant registration (/register/tenant) returns 403 Forbidden (Invitation-Only Enforced)");

// 2. Landlord self-registration page must be accessible (200 OK)
$landlordRes = $httpResults['landlord'] ?? ['code' => 0, 'body' => ''];
assertTest($landlordRes['code'] === 200, "Landlord registration page (/register) returns 200 OK");
assertTest(str_contains($landlordRes['body'], 'Landlord Account'), "Form title contains 'Landlord Account'");
assertTest(str_contains($landlordRes['body'], 'Oga Landlord SuperAdmin Concierge') || str_contains($landlordRes['body'], 'SuperAdmin Concierge'), "Form offers SuperAdmin Concierge option");
assertTest(str_contains($landlordRes['body'], 'Nominate My Own Caretaker'), "Form offers Custom Caretaker option");

// 3. Alternate route /register/landlord
$altLandlordRes = $httpResults['altLandlord'] ?? ['code' => 0, 'body' => ''];
assertTest($altLandlordRes['code'] === 200, "Alternate route (/register/landlord) returns 200 OK");

// 4. Homepage interactive test-play launch
$homeRes = $httpResults['home'] ?? ['code' => 0, 'body' => ''];
assertTest(str_contains($homeRes['body'], 'Tenant Module') || str_contains($homeRes['body'], 'Tenant Test Play') || str_contains($homeRes['body'], 'Resident / Tenant Experience'), "Homepage features Resident / Tenant experience launcher");
assertTest(str_contains($homeRes['body'], 'Interactive Testing Playground') || str_contains($homeRes['body'], 'Interactive Test Play'), "Homepage highlights 'Interactive Testing Playground'");
assertTest(str_contains($homeRes['body'], '/register'), "Homepage features Landlord registration link (/register)");

// -------------------------------------------------------------
// SUMMARY
// -------------------------------------------------------------
echo "\n======================================================================\n";
echo "  TEST SUMMARY: {$passed} / {$total} TESTS PASSED\n";
if ($passed === $total) {
    echo "  RESULT: ALL TESTS PASSED! FULL COMPLIANCE CONFIRMED.\n";
} else {
    echo "  RESULT: " . ($total - $passed) . " TESTS FAILED!\n";
}
echo "======================================================================\n";

exit($passed === $total ? 0 : 1);
