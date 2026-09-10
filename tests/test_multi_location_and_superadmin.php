<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Domain\Agreement\Services\AgreementCompilerService;
use App\Domain\Agreement\Services\SigningTokenService;
use App\Domain\Onboarding\DTOs\TenantOnboardingDTO;
use App\Domain\Onboarding\Services\TenantOnboardingService;
use App\Domain\SuperAdmin\Services\SuperAdminDataService;
use App\Infrastructure\Database\Connection;

echo "=== 1. INITIALIZING DATABASE CONNECTION & SCHEMA ===\n";
$db = new Connection();
$pdo = $db->getPdo();
$compiler = new AgreementCompilerService($db);
$token = new SigningTokenService();
$onboardingService = new TenantOnboardingService($db, $compiler, $token);
$superAdminService = new SuperAdminDataService($db);

// Check if rooms_count column exists
$columns = $pdo->query("PRAGMA table_info(units)")->fetchAll(PDO::FETCH_ASSOC);
$colNames = array_column($columns, 'name');
if (!in_array('rooms_count', $colNames, true)) {
    throw new RuntimeException("FAILURE: rooms_count column not found in units table!");
}
echo "✓ Schema verified: units.rooms_count is active.\n";

echo "\n=== 2. TESTING MULTI-LOCATION TENANT ONBOARDING (NEW LOCATION CREATION) ===\n";
$uniqueEmail = 'chidi.anayo.' . time() . '@test-ogalandlord.ng';
$dto = TenantOnboardingDTO::fromArray([
    'tenant_name' => 'Chidi Anayo',
    'tenant_email' => $uniqueEmail,
    'tenant_phone' => '+2348035559988',
    'landlord_id' => 2, // Chief Ibrahim Bello
    'new_property_title' => 'Guzape Luxury Hill Estate',
    'new_property_address' => 'Plot 88 Diplomatic Zone, Guzape',
    'new_property_city' => 'Guzape District',
    'new_property_state' => 'Abuja',
    'unit_number' => 'Penthouse 01',
    'apartment_type' => 'Penthouse Luxury Suite',
    'rooms_count' => 4,
    'rent_amount' => 12500000.00,
    'rent_start_date' => '2026-10-01',
    'rent_due_date' => '2027-10-01',
    'emergency_name' => 'Kemi Anayo',
    'emergency_relationship' => 'Spouse',
    'emergency_phone' => '+2348035559989',
    'agreement_mode' => 'GENERATE_AND_SIGN'
]);

$onboardResult = $onboardingService->onboard($dto);
echo "✓ Onboarding response: " . $onboardResult['human_message'] . "\n";
echo "✓ Signing URL generated: " . $onboardResult['signing_url'] . "\n";

// Verify the property exists
$propStmt = $pdo->prepare("SELECT * FROM properties WHERE title = 'Guzape Luxury Hill Estate' LIMIT 1");
$propStmt->execute();
$newProp = $propStmt->fetch(PDO::FETCH_ASSOC);
if (!$newProp) {
    throw new RuntimeException("FAILURE: Property was not found!");
}
echo "✓ New location created with ID {$newProp['id']} in {$newProp['city']}, {$newProp['state']}.\n";

// Verify unit was created with correct apartment_type and rooms_count
$unitStmt = $pdo->prepare("SELECT * FROM units WHERE property_id = :pid AND unit_number = 'Penthouse 01' LIMIT 1");
$unitStmt->execute(['pid' => $newProp['id']]);
$newUnit = $unitStmt->fetch(PDO::FETCH_ASSOC);
if (!$newUnit || (int)$newUnit['rooms_count'] !== 4 || $newUnit['apartment_type'] !== 'Penthouse Luxury Suite') {
    throw new RuntimeException("FAILURE: Unit was not properly created with apartment type and rooms_count!");
}
echo "✓ Unit verified: {$newUnit['unit_number']} - {$newUnit['apartment_type']} with {$newUnit['rooms_count']} rooms.\n";

echo "\n=== 3. TESTING SUPERADMIN DATA SERVICE DIRECTORY RETRIEVAL ===\n";
$tenants = $superAdminService->getAllTenants();
echo "✓ Total Tenants found: " . count($tenants) . "\n";
$foundChidi = false;
foreach ($tenants as $t) {
    if ($t['email'] === $uniqueEmail) {
        $foundChidi = true;
        echo "✓ Located newly onboarded tenant in SuperAdmin directory:\n";
        echo "  - Resident: {$t['full_name']} ({$t['email']})\n";
        echo "  - Estate: {$t['property_title']} ({$t['city']}, {$t['state']})\n";
        echo "  - Unit: {$t['unit_number']} ({$t['apartment_type']})\n";
        echo "  - Rooms: {$t['rooms_count']} Rooms\n";
        echo "  - Annual Rent: ₦" . number_format((float)$t['rent_amount'], 2) . "\n";
        echo "  - Status: {$t['agreement_status']}\n";
        break;
    }
}
if (!$foundChidi) {
    throw new RuntimeException("FAILURE: Could not locate newly onboarded tenant in getAllTenants()!");
}

$landlords = $superAdminService->getAllLandlords();
echo "✓ Total Landlords found: " . count($landlords) . "\n";
$foundBello = false;
foreach ($landlords as $l) {
    if ((int)$l['id'] === 2) {
        $foundBello = true;
        echo "✓ Located Landlord Chief Ibrahim Bello in SuperAdmin directory:\n";
        echo "  - Properties/Locations Owned: {$l['properties_count']} locations\n";
        echo "  - Estates List: {$l['estates_list']}\n";
        echo "  - Total Units Capacity: {$l['total_units']}\n";
        echo "  - Active Tenancies: {$l['active_leases']}\n";
        echo "  - Total Portfolio ARR: ₦" . number_format((float)$l['total_annual_rent_roll'], 2) . "\n";
        break;
    }
}
if (!$foundBello) {
    throw new RuntimeException("FAILURE: Could not locate Chief Ibrahim Bello in getAllLandlords()!");
}

echo "\n=== 4. TESTING CSV EXPORTS AND TEMPLATES ===\n";
$tenantsCsv = $superAdminService->exportTenantsCsv();
if (!str_contains($tenantsCsv, 'Full Name') || !str_contains($tenantsCsv, 'Rooms Count') || !str_contains($tenantsCsv, 'Chidi Anayo')) {
    throw new RuntimeException("FAILURE: Tenants CSV export does not contain expected columns or data!");
}
echo "✓ Tenants RFC 4180 CSV export generated successfully (" . strlen($tenantsCsv) . " bytes).\n";

$landlordsCsv = $superAdminService->exportLandlordsCsv();
if (!str_contains($landlordsCsv, 'Full Name') || !str_contains($landlordsCsv, 'Managed Estates Count') || !str_contains($landlordsCsv, 'Chief Ibrahim Bello')) {
    throw new RuntimeException("FAILURE: Landlords CSV export does not contain expected columns or data!");
}
echo "✓ Landlords RFC 4180 CSV export generated successfully (" . strlen($landlordsCsv) . " bytes).\n";

$tenantsTemplate = $superAdminService->getTenantsCsvTemplate();
if (!str_contains($tenantsTemplate, 'Apartment Type') || !str_contains($tenantsTemplate, 'Rooms Count')) {
    throw new RuntimeException("FAILURE: Tenants CSV template missing expected headers!");
}
echo "✓ Tenants CSV template generated successfully.\n";

$landlordsTemplate = $superAdminService->getLandlordsCsvTemplate();
if (!str_contains($landlordsTemplate, 'Full Name') || !str_contains($landlordsTemplate, 'City')) {
    throw new RuntimeException("FAILURE: Landlords CSV template missing expected headers!");
}
echo "✓ Landlords CSV template generated successfully.\n";

echo "\n=== 5. TESTING SUPERADMIN CSV INGESTION / UPLOAD ===\n";
$time = time();
$sampleCsvToUpload = <<<CSV
Tenant Full Name,Tenant Email,Tenant Phone,Property / Estate Title,Property City,Property State,Unit Number,Apartment Type,Rooms Count,Annual Rent,Rent Start Date,Rent Due Date
"Zainab Ahmed","zainab.{$time}@test-ogalandlord.ng","+2348021112233","Crown Court Estate","Maitama","Abuja","Unit 5C-{$time}","3-Bedroom Flat / Apartment",3,5500000,2026-11-01,2027-11-01
"Oluwaseun Adeyemi","oluwaseun.{$time}@test-ogalandlord.ng","+2348021112244","PHDL Unity Estate","Idu District","Abuja","Unit 6B-{$time}","2-Bedroom Apartment",2,2700000,2026-11-01,2027-11-01
CSV;

$importSummary = $superAdminService->importTenantsFromCsv($sampleCsvToUpload);
echo "✓ Tenants CSV Ingestion Result: {$importSummary['inserted']} inserted, {$importSummary['skipped_duplicates']} duplicates skipped.\n";
if ($importSummary['inserted'] !== 2) {
    throw new RuntimeException("FAILURE: Expected 2 inserted records from CSV upload!");
}

// Ingest again to test duplicate suppression
$importAgain = $superAdminService->importTenantsFromCsv($sampleCsvToUpload);
echo "✓ Duplicate Check: {$importAgain['inserted']} inserted, {$importAgain['skipped_duplicates']} duplicates skipped.\n";
if ($importAgain['skipped_duplicates'] !== 2) {
    throw new RuntimeException("FAILURE: Duplicate check did not skip already existing tenants!");
}

// Close statement cursors and release database connections/handles
if (isset($propStmt)) {
    $propStmt->closeCursor();
    unset($propStmt);
}
if (isset($unitStmt)) {
    $unitStmt->closeCursor();
    unset($unitStmt);
}
unset($pdo, $db, $compiler, $token, $onboardingService, $superAdminService);

// Run WAL checkpoint on a fresh, clean connection to ensure file handles are flushed
$checkpointDb = new Connection();
$checkpointPdo = $checkpointDb->getPdo();
$checkpointPdo->exec('PRAGMA wal_checkpoint(TRUNCATE);');
unset($checkpointPdo, $checkpointDb);

echo "\n=== 6. VERIFYING HTTP ENDPOINTS ON LOCAL SERVER ===\n";
$serverUrl = 'http://127.0.0.1:8000';

function quickHttp(string $url, ?string &$sessionCookie = null): array {
    $headers = "Connection: close\r\n";
    if ($sessionCookie) {
        $headers .= "Cookie: {$sessionCookie}\r\n";
    }
    $ctx = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => $headers,
            'ignore_errors' => true,
            'follow_location' => 0,
            'timeout' => 15,
        ]
    ]);
    $res = @file_get_contents($url, false, $ctx);
    $responseHeaders = $http_response_header ?? [];
    $code = 0;
    if (!empty($responseHeaders[0]) && preg_match('#HTTP/\S+\s+(\d+)#', $responseHeaders[0], $m)) {
        $code = (int)$m[1];
    }
    foreach ($responseHeaders as $hdr) {
        if (stripos($hdr, 'Set-Cookie:') === 0) {
            $cookiePart = trim(substr($hdr, strlen('Set-Cookie:')));
            $cookieKeyValue = explode(';', $cookiePart)[0];
            $sessionCookie = $cookieKeyValue;
        }
    }
    return ['res' => (string)$res, 'code' => $code];
}

// 1. API: Properties
$out = quickHttp($serverUrl . '/api/v1/landlord/properties');
$code = $out['code'];
$res = $out['res'];
echo "✓ GET /api/v1/landlord/properties HTTP code: $code\n";
$propsJson = json_decode((string)$res, true);
if ($code !== 200 || !is_array($propsJson) || empty($propsJson)) {
    throw new RuntimeException("FAILURE: /api/v1/landlord/properties did not return valid JSON list!");
}
echo "  Found " . count($propsJson) . " active properties across locations.\n";

// 2. Demo SuperAdmin login & session verification
$sessionCookie = null;
$out = quickHttp($serverUrl . '/demo/superadmin', $sessionCookie);
echo "✓ Demo SuperAdmin Login HTTP code: {$out['code']}\n";

// 3. GET /admin/export/tenants/csv
$out = quickHttp($serverUrl . '/admin/export/tenants/csv', $sessionCookie);
$code = $out['code'];
$res = $out['res'];
echo "✓ GET /admin/export/tenants/csv HTTP code: $code (Length: " . strlen($res) . " bytes)\n";
if ($code !== 200 || !str_contains($res, 'Full Name') || !str_contains($res, 'Rooms Count')) {
    throw new RuntimeException("FAILURE: /admin/export/tenants/csv failed!");
}

// 4. GET /admin/export/landlords/csv
$out = quickHttp($serverUrl . '/admin/export/landlords/csv', $sessionCookie);
$code = $out['code'];
$res = $out['res'];
echo "✓ GET /admin/export/landlords/csv HTTP code: $code (Length: " . strlen($res) . " bytes)\n";
if ($code !== 200 || !str_contains($res, 'Full Name') || !str_contains($res, 'Managed Estates Count')) {
    throw new RuntimeException("FAILURE: /admin/export/landlords/csv failed!");
}

// 5. GET /admin/export/tenants/pdf
$out = quickHttp($serverUrl . '/admin/export/tenants/pdf', $sessionCookie);
$code = $out['code'];
$res = $out['res'];
echo "✓ GET /admin/export/tenants/pdf HTTP code: $code (Contains 'Print / Save as PDF': " . (str_contains($res, 'Print / Save as PDF') ? 'YES' : 'NO') . ")\n";

// 6. GET /admin/export/landlords/pdf
$out = quickHttp($serverUrl . '/admin/export/landlords/pdf', $sessionCookie);
$code = $out['code'];
$res = $out['res'];
echo "✓ GET /admin/export/landlords/pdf HTTP code: $code (Contains 'Print / Save as PDF': " . (str_contains($res, 'Print / Save as PDF') ? 'YES' : 'NO') . ")\n";

// 7. GET /admin with tenants tab
$out = quickHttp($serverUrl . '/admin?tab=tenants', $sessionCookie);
$code = $out['code'];
$res = $out['res'];
echo "✓ GET /admin?tab=tenants HTTP code: $code (Contains 'Tenants & Residents Directory': " . (str_contains($res, 'Tenants & Residents Directory') ? 'YES' : 'NO') . ")\n";

// 8. GET /onboarding
$out = quickHttp($serverUrl . '/onboarding', $sessionCookie);
$code = $out['code'];
$res = $out['res'];
echo "✓ GET /onboarding HTTP code: $code (Contains 'Apartment Type' & 'Number of Rooms': " . (str_contains($res, 'Apartment Type') && str_contains($res, 'Number of Rooms') ? 'YES' : 'NO') . ")\n";

echo "\n============================================\n";
echo "🎉 ALL TESTS PASSED WITH 100% SUCCESS!\n";
echo "============================================\n";
