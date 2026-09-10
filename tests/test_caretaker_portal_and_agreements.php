<?php

function runVerificationSuite() {
    $baseUrl = 'http://127.0.0.1:8000';
    $results = [];

    // Helper: curl with cookie jar
    function makeRequest($url, $method = 'GET', $postData = null, $cookieFile = null) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postData) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($postData) ? http_build_query($postData) : $postData);
            }
        }
        if ($cookieFile) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $redirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        curl_close($ch);
        return ['code' => $code, 'body' => $body, 'redirect' => $redirect];
    }

    echo "===============================================================\n";
    echo "  TEST SUITE: CARETAKER SIGNATURE RESTRICTIONS & MULTI-TENANTS \n";
    echo "===============================================================\n\n";

    // -------------------------------------------------------------
    // TEST 1: Caretaker Login & Attempt to Access Signature Portal (/sign)
    // -------------------------------------------------------------
    echo "[TEST 1] Caretaker attempting to access /sign portal...\n";
    $caretakerCookie = tempnam(sys_get_temp_dir(), 'ck_caretaker_');
    $loginRes = makeRequest("$baseUrl/login", 'POST', [
        'email' => 'caretaker.idu@ogalandlord.ng',
        'password' => 'password123'
    ], $caretakerCookie);

    $signRes = makeRequest("$baseUrl/sign", 'GET', null, $caretakerCookie);
    echo " - HTTP Status Code: {$signRes['code']} (Expected: 403)\n";
    $blocked = ($signRes['code'] === 403);
    $hasRestrictedText = stripos($signRes['body'], 'Tenant Signature Portal is Restricted') !== false;
    $hasEvidenceAct = stripos($signRes['body'], 'Evidence Act 2011') !== false;
    echo " - Blocked with HTTP 403: " . ($blocked ? "PASS" : "FAIL") . "\n";
    echo " - Explains Restriction: " . ($hasRestrictedText ? "PASS" : "FAIL") . "\n";
    echo " - Cites Evidence Act: " . ($hasEvidenceAct ? "PASS" : "FAIL") . "\n";

    if ($blocked && $hasRestrictedText && $hasEvidenceAct) {
        echo ">>> TEST 1 RESULT: SUCCESS (Caretaker successfully blocked from /sign)\n\n";
        $results[] = true;
    } else {
        echo ">>> TEST 1 RESULT: FAILED\n\n";
        $results[] = false;
    }

    // -------------------------------------------------------------
    // TEST 2: SuperAdmin Attempt to Access Signature Portal (/sign)
    // -------------------------------------------------------------
    echo "[TEST 2] SuperAdmin attempting to access /sign portal...\n";
    $adminCookie = tempnam(sys_get_temp_dir(), 'ck_admin_');
    makeRequest("$baseUrl/login", 'POST', [
        'email' => 'superadmin@ogalandlord.ng',
        'password' => 'password123'
    ], $adminCookie);

    $adminSignRes = makeRequest("$baseUrl/sign", 'GET', null, $adminCookie);
    echo " - HTTP Status Code: {$adminSignRes['code']} (Expected: 403)\n";
    $adminBlocked = ($adminSignRes['code'] === 403);
    echo " - Admin Blocked with HTTP 403: " . ($adminBlocked ? "PASS" : "FAIL") . "\n";

    if ($adminBlocked) {
        echo ">>> TEST 2 RESULT: SUCCESS (Admin successfully blocked from /sign)\n\n";
        $results[] = true;
    } else {
        echo ">>> TEST 2 RESULT: FAILED\n\n";
        $results[] = false;
    }

    // -------------------------------------------------------------
    // TEST 3: Caretaker Attempt to POST Signature via API (/api/v1/signing/execute)
    // -------------------------------------------------------------
    echo "[TEST 3] Caretaker attempting to POST signature to API...\n";
    $apiRes = makeRequest("$baseUrl/api/v1/signing/execute", 'POST', json_encode([
        'token' => 'dummy-token',
        'signature_type' => 'DRAWN',
        'signature_data' => 'forged_signature'
    ]), $caretakerCookie);
    echo " - API Status Code: {$apiRes['code']} (Expected: 403)\n";
    $apiBlocked = ($apiRes['code'] === 403);
    $apiJson = json_decode($apiRes['body'], true);
    $hasApiError = isset($apiJson['error']) && stripos($apiJson['error'], 'not permitted to sign') !== false;
    echo " - API Rejection: " . ($apiBlocked ? "PASS" : "FAIL") . "\n";
    echo " - API Error Message: " . ($hasApiError ? "PASS" : "FAIL") . "\n";

    if ($apiBlocked && $hasApiError) {
        echo ">>> TEST 3 RESULT: SUCCESS (API blocked caretaker forgery)\n\n";
        $results[] = true;
    } else {
        echo ">>> TEST 3 RESULT: FAILED\n\n";
        $results[] = false;
    }

    // -------------------------------------------------------------
    // TEST 4: Caretaker Dashboard Tenancy Agreements Page with List of Tenants
    // -------------------------------------------------------------
    echo "[TEST 4] Caretaker opening Tenancy Agreements (List of Tenants)...\n";
    $caretakerDash = makeRequest("$baseUrl/caretaker?tab=agreements", 'GET', null, $caretakerCookie);
    echo " - Dashboard HTTP Code: {$caretakerDash['code']} (Expected: 200)\n";
    
    $hasAmara = stripos($caretakerDash['body'], 'Amara Okafor') !== false;
    $hasTunde = stripos($caretakerDash['body'], 'Tunde Bakare') !== false;
    $hasChinedu = stripos($caretakerDash['body'], 'Dr. Chinedu Eze') !== false;
    $hasFatima = stripos($caretakerDash['body'], 'Fatima Bello-Kano') !== false;
    $hasSignedBadge = stripos($caretakerDash['body'], 'Signed & Verified') !== false;
    $hasPendingBadge = stripos($caretakerDash['body'], 'Not Signed (Pending)') !== false;

    echo " - Contains Tenant 1 (Amara Okafor): " . ($hasAmara ? "PASS" : "FAIL") . "\n";
    echo " - Contains Tenant 2 (Tunde Bakare): " . ($hasTunde ? "PASS" : "FAIL") . "\n";
    echo " - Contains Tenant 3 (Dr. Chinedu Eze): " . ($hasChinedu ? "PASS" : "FAIL") . "\n";
    echo " - Contains Tenant 4 (Fatima Bello-Kano): " . ($hasFatima ? "PASS" : "FAIL") . "\n";
    echo " - Contains 'Signed & Verified' Status Badge: " . ($hasSignedBadge ? "PASS" : "FAIL") . "\n";
    echo " - Contains 'Not Signed (Pending)' Status Badge: " . ($hasPendingBadge ? "PASS" : "FAIL") . "\n";

    if ($caretakerDash['code'] === 200 && $hasAmara && $hasTunde && $hasChinedu && $hasFatima && $hasSignedBadge && $hasPendingBadge) {
        echo ">>> TEST 4 RESULT: SUCCESS (List of multiple tenants loaded with signed & unsigned statuses)\n\n";
        $results[] = true;
    } else {
        echo ">>> TEST 4 RESULT: FAILED\n\n";
        $results[] = false;
    }

    // -------------------------------------------------------------
    // TEST 5: Caretaker Previewing SIGNED Tenancy Agreement (Lease 1: Amara Okafor)
    // -------------------------------------------------------------
    echo "[TEST 5] Caretaker previewing SIGNED agreement (ID: 1)...\n";
    $signedPreview = makeRequest("$baseUrl/agreement/preview?id=1", 'GET', null, $caretakerCookie);
    echo " - Preview HTTP Code: {$signedPreview['code']} (Expected: 200)\n";
    $hasSignedTitle = stripos($signedPreview['body'], 'TENANCY AGREEMENT') !== false;
    $hasCertificate = stripos($signedPreview['body'], 'Certificate of Completion') !== false;
    $hasSignedStatus = stripos($signedPreview['body'], 'Fully Executed & Sealed') !== false;
    $hasBackToTenants = stripos($signedPreview['body'], 'Back to Managed Tenants') !== false;

    echo " - Has Document Title: " . ($hasSignedTitle ? "PASS" : "FAIL") . "\n";
    echo " - Has Certificate of Completion: " . ($hasCertificate ? "PASS" : "FAIL") . "\n";
    echo " - Has 'Fully Executed & Sealed' state: " . ($hasSignedStatus ? "PASS" : "FAIL") . "\n";
    echo " - Has 'Back to Managed Tenants' navigation: " . ($hasBackToTenants ? "PASS" : "FAIL") . "\n";

    if ($signedPreview['code'] === 200 && $hasSignedTitle && $hasCertificate && $hasSignedStatus && $hasBackToTenants) {
        echo ">>> TEST 5 RESULT: SUCCESS (Signed agreement preview verified)\n\n";
        $results[] = true;
    } else {
        echo ">>> TEST 5 RESULT: FAILED\n\n";
        $results[] = false;
    }

    // -------------------------------------------------------------
    // TEST 6: Caretaker Previewing NOT SIGNED Tenancy Agreement (Lease 2: Tunde Bakare)
    // -------------------------------------------------------------
    echo "[TEST 6] Caretaker previewing NOT SIGNED agreement (ID: 2)...\n";
    $unsignedPreview = makeRequest("$baseUrl/agreement/preview?id=2", 'GET', null, $caretakerCookie);
    echo " - Preview HTTP Code: {$unsignedPreview['code']} (Expected: 200)\n";
    $hasTundeName = stripos($unsignedPreview['body'], 'Tunde Bakare') !== false;
    $hasUnsignedBanner = stripos($unsignedPreview['body'], 'Awaiting Tenant Signature') !== false;
    $hasUnsignedWatermark = stripos($unsignedPreview['body'], 'AWAITING SIGNATURE') !== false;
    $hasNoCaretakerForgeryNotice = stripos($unsignedPreview['body'], 'Caretakers and Administrators cannot sign on behalf of tenants') !== false;

    echo " - Displays Tenant Name (Tunde Bakare): " . ($hasTundeName ? "PASS" : "FAIL") . "\n";
    echo " - Displays 'Awaiting Tenant Signature' Status: " . ($hasUnsignedBanner ? "PASS" : "FAIL") . "\n";
    echo " - Displays Unsigned Watermark / State: " . ($hasUnsignedWatermark ? "PASS" : "FAIL") . "\n";
    echo " - Forgery Restriction Notice Present: " . ($hasNoCaretakerForgeryNotice ? "PASS" : "FAIL") . "\n";

    if ($unsignedPreview['code'] === 200 && $hasTundeName && $hasUnsignedBanner && $hasUnsignedWatermark && $hasNoCaretakerForgeryNotice) {
        echo ">>> TEST 6 RESULT: SUCCESS (Unsigned draft preview & restriction verified)\n\n";
        $results[] = true;
    } else {
        echo ">>> TEST 6 RESULT: FAILED\n\n";
        $results[] = false;
    }

    @unlink($caretakerCookie);
    @unlink($adminCookie);

    $passed = count(array_filter($results));
    $total = count($results);
    echo "===============================================================\n";
    echo "SUMMARY: $passed / $total TESTS PASSED\n";
    echo "===============================================================\n";

    if ($passed === $total) {
        exit(0);
    } else {
        exit(1);
    }
}

runVerificationSuite();
