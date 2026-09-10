<?php
$pdo = new PDO('sqlite:database/property_platform.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ensure all users exist
$passHash = password_hash('password123', PASSWORD_BCRYPT);
$users = [
    [4, '84f7b231-152e-4b62-9762-59543026ad01', 'Amara Okafor', 'amara.okafor@example.com', '+2348030000003', 'TENANT'],
    [5, 'c90b6a22-38d7-4648-bce6-31d7967b578c', 'Tunde Bakare', 'tunde.bakare@example.com', '+2348030000004', 'TENANT'],
    [6, 'd11b6a22-38d7-4648-bce6-31d7967b578d', 'Dr. Chinedu Eze', 'chinedu.eze@example.com', '+2348030000005', 'TENANT'],
    [7, 'e22b6a22-38d7-4648-bce6-31d7967b578e', 'Fatima Bello-Kano', 'fatima.kano@example.com', '+2348030000006', 'TENANT'],
    [8, 'f33b6a22-38d7-4648-bce6-31d7967b578f', 'Gbenga Adenuga', 'gbenga.adenuga@example.com', '+2348030000007', 'TENANT'],
];

$uStmt = $pdo->prepare("INSERT OR REPLACE INTO users (id, uuid, full_name, email, phone_number, password_hash, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
foreach ($users as $u) {
    $uStmt->execute([$u[0], $u[1], $u[2], $u[3], $u[4], $passHash, $u[5]]);
}

// Reset and set clean leases
$pdo->exec("DELETE FROM leases");
$leases = [
    [
        'id' => 1,
        'uuid' => 'LSE-2026-0001',
        'unit_id' => 1, // Unit 1A
        'tenant_id' => 4, // Amara Okafor
        'agreement_mode' => 'GENERATE_AND_SIGN',
        'agreement_status' => 'FULLY_EXECUTED',
        'rent_amount' => 2500000.00,
        'rent_start_date' => '2025-10-08',
        'rent_due_date' => '2026-10-08',
        'emergency_contact_name' => 'Ngozi Okafor',
        'emergency_contact_relationship' => 'Sister',
        'emergency_contact_phone' => '+2348039990001',
        'document_sha256_hash' => 'df0c4e5ab3b9a1b4be92676c9633adfa9f82d94fc0ac74900ef3c33371ae6181',
        'tenant_signed_at' => '2025-10-08 14:15:00',
        'tenant_ip_address' => '102.89.34.19 (MTN Nigeria - Wuse II)'
    ],
    [
        'id' => 2,
        'uuid' => 'LSE-2026-0002',
        'unit_id' => 3, // Unit 2A
        'tenant_id' => 5, // Tunde Bakare
        'agreement_mode' => 'GENERATE_AND_SIGN',
        'agreement_status' => 'WAITING_FOR_TENANT_SIGNATURE',
        'rent_amount' => 3800000.00,
        'rent_start_date' => '2026-09-01',
        'rent_due_date' => '2026-09-22',
        'emergency_contact_name' => 'Kunle Bakare',
        'emergency_contact_relationship' => 'Brother',
        'emergency_contact_phone' => '+2348039990002',
        'document_sha256_hash' => null,
        'tenant_signed_at' => null,
        'tenant_ip_address' => null
    ],
    [
        'id' => 3,
        'uuid' => 'LSE-2026-0003',
        'unit_id' => 2, // Unit 1B
        'tenant_id' => 6, // Dr. Chinedu Eze
        'agreement_mode' => 'GENERATE_AND_SIGN',
        'agreement_status' => 'FULLY_EXECUTED',
        'rent_amount' => 2500000.00,
        'rent_start_date' => '2025-11-15',
        'rent_due_date' => '2026-11-15',
        'emergency_contact_name' => 'Kelechi Eze',
        'emergency_contact_relationship' => 'Brother',
        'emergency_contact_phone' => '+2348039990003',
        'document_sha256_hash' => 'a81f33b1e2c84d6790938b8e05c879d231940ef87a0301eb5f891104e427189c',
        'tenant_signed_at' => '2025-11-15 09:30:00',
        'tenant_ip_address' => '197.210.45.12 (Airtel Broadband - Abuja)'
    ],
    [
        'id' => 4,
        'uuid' => 'LSE-2026-0004',
        'unit_id' => 8, // Unit 4B
        'tenant_id' => 7, // Fatima Bello-Kano
        'agreement_mode' => 'GENERATE_AND_SIGN',
        'agreement_status' => 'WAITING_FOR_TENANT_SIGNATURE',
        'rent_amount' => 1800000.00,
        'rent_start_date' => '2026-09-05',
        'rent_due_date' => '2026-09-28',
        'emergency_contact_name' => 'Aminu Kano',
        'emergency_contact_relationship' => 'Father',
        'emergency_contact_phone' => '+2348039990004',
        'document_sha256_hash' => null,
        'tenant_signed_at' => null,
        'tenant_ip_address' => null
    ],
    [
        'id' => 5,
        'uuid' => 'LSE-2026-0005',
        'unit_id' => 5, // Unit 3A
        'tenant_id' => 8, // Gbenga Adenuga
        'agreement_mode' => 'GENERATE_AND_SIGN',
        'agreement_status' => 'FULLY_EXECUTED',
        'rent_amount' => 1500000.00,
        'rent_start_date' => '2026-01-10',
        'rent_due_date' => '2027-01-10',
        'emergency_contact_name' => 'Folake Adenuga',
        'emergency_contact_relationship' => 'Wife',
        'emergency_contact_phone' => '+2348039990005',
        'document_sha256_hash' => 'c44e99f1823bb3d702ef631adfa9f82d94fc0ac74900ef3c33371ae618199321',
        'tenant_signed_at' => '2026-01-10 11:20:00',
        'tenant_ip_address' => '105.112.98.54 (Spectranet LTE)'
    ]
];

$lStmt = $pdo->prepare("INSERT INTO leases (id, uuid, unit_id, tenant_id, agreement_mode, agreement_status, rent_amount, rent_start_date, rent_due_date, emergency_contact_name, emergency_contact_relationship, emergency_contact_phone, document_sha256_hash, tenant_signed_at, tenant_ip_address)
VALUES (:id, :uuid, :unit_id, :tenant_id, :agreement_mode, :agreement_status, :rent_amount, :rent_start_date, :rent_due_date, :emergency_contact_name, :emergency_contact_relationship, :emergency_contact_phone, :document_sha256_hash, :tenant_signed_at, :tenant_ip_address)");

foreach ($leases as $l) {
    $lStmt->execute($l);
}

// Update units occupancy
$pdo->exec("UPDATE units SET is_occupied = 0");
$pdo->exec("UPDATE units SET is_occupied = 1 WHERE id IN (1, 2, 3, 5, 8)");

echo "Database cleaned & synchronized with 5 managed tenant leases (3 Signed, 2 Waiting for Tenant Signature)!\n";
