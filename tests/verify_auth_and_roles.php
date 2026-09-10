<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Domain\Auth\Services\AuthService;
use App\Infrastructure\Database\Connection;

echo "=== VERIFYING AUTHENTICATION & MULTI-ROLE ROUTING ===\n\n";

$db = new Connection();
$auth = new AuthService($db);

// TEST 1: Landlord Login
echo "[1/4] Verifying Landlord Authentication...\n";
$landlord = $auth->attempt('landlord@propertycare.ng', 'password123');
if (!$landlord || $landlord['role'] !== 'LANDLORD') {
    throw new RuntimeException("Landlord auth failed.");
}
echo "  ✓ Authenticated: {$landlord['full_name']} ({$landlord['role']})\n";
echo "  ✓ Destination Route: " . $auth->redirectPathForRole($landlord['role']) . "\n\n";

// TEST 2: Caretaker Login
echo "[2/4] Verifying Caretaker Authentication...\n";
$caretaker = $auth->attempt('caretaker.idu@propertycare.ng', 'password123');
if (!$caretaker || $caretaker['role'] !== 'CARETAKER') {
    throw new RuntimeException("Caretaker auth failed.");
}
echo "  ✓ Authenticated: {$caretaker['full_name']} ({$caretaker['role']})\n";
echo "  ✓ Destination Route: " . $auth->redirectPathForRole($caretaker['role']) . "\n\n";

// TEST 3: SuperAdmin Login
echo "[3/4] Verifying SuperAdmin Authentication...\n";
$superadmin = $auth->attempt('superadmin@propertycare.ng', 'password123');
if (!$superadmin || $superadmin['role'] !== 'SUPERADMIN') {
    throw new RuntimeException("SuperAdmin auth failed.");
}
echo "  ✓ Authenticated: {$superadmin['full_name']} ({$superadmin['role']})\n";
echo "  ✓ Destination Route: " . $auth->redirectPathForRole($superadmin['role']) . "\n\n";

// TEST 4: Invalid Password Rejection & Asset Checks
echo "[4/4] Verifying Security Guardrails & Logo Assets...\n";
$failedAuth = $auth->attempt('landlord@propertycare.ng', 'wrongpassword');
if ($failedAuth !== null) {
    throw new RuntimeException("Security violation: Invalid password was accepted.");
}
echo "  ✓ Invalid credentials correctly rejected.\n";

$logoPath = __DIR__ . '/../public/assets/images/logo.jpg';
if (!file_exists($logoPath)) {
    throw new RuntimeException("Project logo missing at public/assets/images/logo.jpg");
}
echo "  ✓ Official project logo verified at public/assets/images/logo.jpg (" . filesize($logoPath) . " bytes)\n\n";

echo "ALL AUTHENTICATION & MULTI-ROLE CHECKS PASSED WITH ZERO ERRORS!\n";