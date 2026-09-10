<?php

function testUserLoginAndPreview($roleName, $email, $password) {
    echo "=== Testing $roleName ($email) ===\n";
    $cookieJar = tempnam(sys_get_temp_dir(), 'cookie_');

    // 1. Send Login Request
    $ch = curl_init('http://127.0.0.1:8000/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'email' => $email,
        'password' => $password
    ]));
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);

    echo "Login Response: HTTP $httpCode, Redirect: $redirectUrl\n";

    // 2. Fetch Agreement Preview
    $ch = curl_init('http://127.0.0.1:8000/agreement/preview?id=1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJar);
    $html = curl_exec($ch);
    $previewCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    @unlink($cookieJar);

    $hasLegalTitle = stripos($html, 'TENANCY AGREEMENT') !== false;
    $hasCertificate = stripos($html, 'Certificate of Completion') !== false;
    $hasLandlord = stripos($html, 'Chief Ibrahim Bello') !== false;
    $hasTenant = stripos($html, 'Amara Okafor') !== false;

    echo "Preview HTTP Code: $previewCode\n";
    echo "Document Content Checks:\n";
    echo " - Has 'TENANCY AGREEMENT': " . ($hasLegalTitle ? "PASS" : "FAIL") . "\n";
    echo " - Has 'CERTIFICATE OF COMPLETION': " . ($hasCertificate ? "PASS" : "FAIL") . "\n";
    echo " - Has Landlord Name: " . ($hasLandlord ? "PASS" : "FAIL") . "\n";
    echo " - Has Tenant Name: " . ($hasTenant ? "PASS" : "FAIL") . "\n";

    if ($previewCode === 200 && $hasLegalTitle && $hasCertificate) {
        echo "RESULT: SUCCESS for $roleName!\n\n";
        return true;
    } else {
        echo "RESULT: FAILED for $roleName!\n\n";
        return false;
    }
}

$allSuccess = true;
$allSuccess = testUserLoginAndPreview('Landlord', 'landlord@ogalandlord.ng', 'password123') && $allSuccess;
$allSuccess = testUserLoginAndPreview('Caretaker', 'caretaker.idu@ogalandlord.ng', 'password123') && $allSuccess;
$allSuccess = testUserLoginAndPreview('SuperAdmin', 'superadmin@ogalandlord.ng', 'password123') && $allSuccess;

if ($allSuccess) {
    echo "ALL 3 ROLES SUCCESSFULLY AUTHENTICATED AND PREVIEWED THE SIGNED TENANCY AGREEMENT!\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED!\n";
    exit(1);
}
