<?php
declare(strict_types=1);

$sqliteFile = __DIR__ . '/oga_landlord.sqlite';
$pdo = new PDO('sqlite:' . $sqliteFile, null, null, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->exec('PRAGMA foreign_keys = ON;');

// Create tables compatible with SQLite
$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    phone_number TEXT NOT NULL,
    password_hash TEXT NULL,
    role TEXT NOT NULL DEFAULT 'TENANT',
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS properties (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    landlord_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    address_line_1 TEXT NOT NULL,
    city TEXT NOT NULL,
    state TEXT NOT NULL,
    country TEXT NOT NULL DEFAULT 'Nigeria',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS property_caretaker_assignments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    property_id INTEGER NOT NULL,
    caretaker_id INTEGER NOT NULL,
    can_manage_tickets INTEGER NOT NULL DEFAULT 1,
    can_view_finances INTEGER NOT NULL DEFAULT 0,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (caretaker_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(property_id, caretaker_id)
);

CREATE TABLE IF NOT EXISTS units (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    property_id INTEGER NOT NULL,
    unit_number TEXT NOT NULL,
    apartment_type TEXT NOT NULL,
    rooms_count INTEGER NOT NULL DEFAULT 2,
    default_rent_amount REAL NOT NULL,
    currency TEXT NOT NULL DEFAULT 'NGN',
    is_occupied INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS leases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    unit_id INTEGER NOT NULL,
    tenant_id INTEGER NOT NULL,
    agreement_mode TEXT NOT NULL,
    agreement_status TEXT NOT NULL DEFAULT 'DRAFT',
    rent_amount REAL NOT NULL,
    currency TEXT NOT NULL DEFAULT 'NGN',
    rent_start_date DATE NOT NULL,
    rent_due_date DATE NOT NULL,
    emergency_contact_name TEXT NOT NULL,
    emergency_contact_relationship TEXT NOT NULL,
    emergency_contact_phone TEXT NOT NULL,
    signing_token_hash TEXT NULL UNIQUE,
    signing_token_expires_at DATETIME NULL,
    generated_pdf_path TEXT NULL,
    uploaded_agreement_path TEXT NULL,
    document_sha256_hash TEXT NULL,
    tenant_signature_type TEXT NULL,
    tenant_signature_image_path TEXT NULL,
    tenant_signed_at DATETIME NULL,
    tenant_ip_address TEXT NULL,
    tenant_user_agent TEXT NULL,
    landlord_signed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS rent_reminder_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    lease_id INTEGER NOT NULL,
    dunning_phase TEXT NOT NULL,
    days_to_due INTEGER NOT NULL,
    recipient_email TEXT NOT NULL,
    recipient_phone TEXT NOT NULL,
    caretakers_cc_json TEXT NULL,
    email_sent_successfully INTEGER NOT NULL DEFAULT 0,
    sms_sent_successfully INTEGER NOT NULL DEFAULT 0,
    dispatch_payload TEXT NULL,
    sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lease_id) REFERENCES leases(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS rent_payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    lease_id INTEGER NOT NULL,
    amount REAL NOT NULL,
    currency TEXT NOT NULL DEFAULT 'NGN',
    payment_method TEXT NOT NULL,
    gateway_reference TEXT NULL UNIQUE,
    payment_status TEXT NOT NULL DEFAULT 'PENDING',
    payment_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    covered_period_start DATE NOT NULL,
    covered_period_end DATE NOT NULL,
    receipt_number TEXT NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lease_id) REFERENCES leases(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS processed_webhooks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    idempotency_key TEXT NOT NULL UNIQUE,
    gateway_provider TEXT NOT NULL,
    event_type TEXT NOT NULL,
    payload_hash TEXT NOT NULL,
    processed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS maintenance_tickets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    property_id INTEGER NOT NULL,
    unit_id INTEGER NOT NULL,
    tenant_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    priority TEXT NOT NULL DEFAULT 'MEDIUM',
    status TEXT NOT NULL DEFAULT 'REPORTED',
    ticket_code TEXT NULL UNIQUE,
    category TEXT NOT NULL DEFAULT 'GENERAL',
    is_escalated_to_landlord INTEGER NOT NULL DEFAULT 0,
    escalated_at DATETIME NULL,
    escalation_reason TEXT NULL,
    assigned_vendor_name TEXT NULL,
    assigned_vendor_phone TEXT NULL,
    resolved_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ticket_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ticket_id INTEGER NOT NULL,
    sender_id INTEGER NOT NULL,
    sender_role TEXT NOT NULL,
    sender_name TEXT NOT NULL,
    message TEXT NOT NULL,
    attachment_path TEXT NULL,
    is_internal_note INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES maintenance_tickets(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tenancy_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    property_id INTEGER NOT NULL,
    unit_id INTEGER NOT NULL,
    tenant_id INTEGER NOT NULL,
    lease_id INTEGER NULL,
    tenant_name TEXT NOT NULL,
    tenant_email TEXT NOT NULL,
    tenant_phone TEXT NOT NULL,
    rent_amount REAL NOT NULL,
    currency TEXT NOT NULL DEFAULT 'NGN',
    rent_start_date DATE NOT NULL,
    terminated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    duration_of_stay TEXT NOT NULL,
    termination_reason TEXT NULL,
    terminated_by_user_id INTEGER NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tenant_payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    lease_id INTEGER NOT NULL,
    tenant_id INTEGER NOT NULL,
    property_id INTEGER NOT NULL,
    payment_type TEXT NOT NULL,
    beneficiary_type TEXT NOT NULL DEFAULT 'LANDLORD',
    title TEXT NOT NULL,
    amount REAL NOT NULL,
    currency TEXT NOT NULL DEFAULT 'NGN',
    payment_method TEXT NOT NULL,
    gateway_reference TEXT NULL,
    bank_transfer_sender_name TEXT NULL,
    bank_transfer_reference TEXT NULL,
    receipt_proof_path TEXT NULL,
    status TEXT NOT NULL DEFAULT 'PENDING_CONFIRMATION',
    confirmed_by_user_id INTEGER NULL,
    confirmed_by_role TEXT NULL,
    confirmed_at DATETIME NULL,
    receipt_number TEXT NOT NULL UNIQUE,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS saas_subscriptions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    landlord_id INTEGER NOT NULL,
    billing_model TEXT NOT NULL DEFAULT 'PER_UNIT',
    rate_per_unit REAL NOT NULL DEFAULT 3000.00,
    rate_per_property REAL NOT NULL DEFAULT 25000.00,
    currency TEXT NOT NULL DEFAULT 'NGN',
    next_billing_date DATE NOT NULL,
    status TEXT NOT NULL DEFAULT 'ACTIVE',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE
);
");

// Seed Baseline Users & Estates
$passHash = password_hash('password123', PASSWORD_BCRYPT);

$pdo->prepare("INSERT OR IGNORE INTO users (id, uuid, full_name, email, phone_number, password_hash, role) VALUES
(1, '7152e490-dab3-4062-b56a-8d068cf41154', 'Alhaji Farouk Al-Mansur', 'superadmin@propertycare.ng', '+2348000000001', :p, 'SUPERADMIN'),
(2, 'd7b8d4cf-52a5-4210-bc4d-d916d01a8978', 'Chief Ibrahim Bello', 'landlord@propertycare.ng', '+2348030000001', :p, 'LANDLORD'),
(3, '3ed6963e-6fb5-4b65-bc7f-10c59a4ae6d7', 'Musa Danjuma', 'caretaker.idu@propertycare.ng', '+2348030000002', :p, 'CARETAKER'),
(4, '84f7b231-152e-4b62-9762-59543026ad01', 'Amara Okafor', 'amara.okafor@example.com', '+2348030000003', :p, 'TENANT'),
(5, 'c90b6a22-38d7-4648-bce6-31d7967b578c', 'Tunde Bakare', 'tunde.bakare@example.com', '+2348030000004', :p, 'TENANT'),
(6, 'd11b6a22-38d7-4648-bce6-31d7967b578d', 'Dr. Chinedu Eze', 'chinedu.eze@example.com', '+2348030000005', :p, 'TENANT'),
(7, 'e22b6a22-38d7-4648-bce6-31d7967b578e', 'Fatima Bello-Kano', 'fatima.kano@example.com', '+2348030000006', :p, 'TENANT')
")->execute(['p' => $passHash]);

// Seed Property
$pdo->exec("INSERT OR IGNORE INTO properties (id, uuid, landlord_id, title, address_line_1, city, state, country) VALUES
(1, '43144a95-5a21-4f18-bc1c-99d63c95977a', 2, 'PHDL Unity Estate, Idu', 'Plot 42 Railway Corridor, Idu Industrial', 'Abuja', 'FCT', 'Nigeria')");

// Seed Caretaker Assignment
$pdo->exec("INSERT OR IGNORE INTO property_caretaker_assignments (property_id, caretaker_id, can_manage_tickets, can_view_finances) VALUES
(1, 3, 1, 0)");

// Seed Units
$units = [
    [1, 'Unit 1A', '2-Bedroom Apartment', 2500000.00, 1],
    [2, 'Unit 1B', '2-Bedroom Apartment', 2500000.00, 1],
    [3, 'Unit 2A', '3-Bedroom Penthouse', 3800000.00, 1],
    [4, 'Unit 2B', '3-Bedroom Penthouse', 3800000.00, 0],
    [5, 'Unit 3A', 'Studio Apartment', 1500000.00, 0],
    [6, 'Unit 3B', 'Studio Apartment', 1500000.00, 0],
    [7, 'Unit 4A', '1-Bedroom Flat', 1800000.00, 0],
    [8, 'Unit 4B', '1-Bedroom Flat', 1800000.00, 1],
];
$uStmt = $pdo->prepare("INSERT OR IGNORE INTO units (id, uuid, property_id, unit_number, apartment_type, default_rent_amount, is_occupied)
VALUES (:id, :uuid, 1, :num, :type, :rent, :occ)");
foreach ($units as $u) {
    $uStmt->execute([
        'id'   => $u[0],
        'uuid' => sprintf('u-%04d-unit-%d', $u[0], $u[0]),
        'num'  => $u[1],
        'type' => $u[2],
        'rent' => $u[3],
        'occ'  => $u[4],
    ]);
}

// Seed Leases
$pdo->exec("INSERT OR IGNORE INTO leases (id, uuid, unit_id, tenant_id, agreement_mode, agreement_status, rent_amount, rent_start_date, rent_due_date, emergency_contact_name, emergency_contact_relationship, emergency_contact_phone, document_sha256_hash, tenant_signed_at, tenant_ip_address) VALUES
(1, 'l-0001-lease-1', 1, 4, 'GENERATE_AND_SIGN', 'FULLY_EXECUTED', 2500000.00, '2025-10-08', '2026-10-08', 'Ngozi Okafor', 'Sister', '+2348039990001', 'df0c4e5ab3b9a1b4be92676c9633adfa9f82d94fc0ac74900ef3c33371ae6181', '2025-10-08 14:15:00', '127.0.0.1 (Localhost Verified)'),
(2, 'l-0002-lease-2', 3, 5, 'GENERATE_AND_SIGN', 'WAITING_FOR_TENANT_SIGNATURE', 3800000.00, '2026-09-01', '2026-09-22', 'Kunle Bakare', 'Brother', '+2348039990002', NULL, NULL, NULL),
(3, 'l-0003-lease-3', 2, 6, 'GENERATE_AND_SIGN', 'FULLY_EXECUTED', 2500000.00, '2025-11-15', '2026-11-15', 'Kelechi Eze', 'Brother', '+2348039990003', 'a81f33b1e2c84d6790938b8e05c879d231940ef87a0301eb5f891104e427189c', '2025-11-15 09:30:00', '197.210.45.12 (MTN Nigeria)'),
(4, 'l-0004-lease-4', 8, 7, 'GENERATE_AND_SIGN', 'WAITING_FOR_TENANT_SIGNATURE', 1800000.00, '2026-09-05', '2026-09-28', 'Aminu Kano', 'Father', '+2348039990004', NULL, NULL, NULL)");

// Seed Maintenance Tickets
$pdo->exec("INSERT OR IGNORE INTO maintenance_tickets (id, uuid, property_id, unit_id, tenant_id, title, description, priority, status, assigned_vendor_name, assigned_vendor_phone) VALUES
(1, 't-0001', 1, 8, 4, 'Kitchen sink pipe dripping', 'Under-sink connector needs Teflon tape and new gasket', 'MEDIUM', 'IN_REVIEW', 'Emeka Plumbing Services', '+2348051234567'),
(2, 't-0002', 1, 1, 4, 'Pre-paid meter display blank', 'Inverter bypass tripped during storm', 'HIGH', 'TECHNICIAN_DISPATCHED', 'Musa Electricals', '+2348067890123')");

// Seed SaaS Subscription
$pdo->exec("INSERT OR IGNORE INTO saas_subscriptions (id, landlord_id, billing_model, rate_per_unit, rate_per_property, next_billing_date, status) VALUES
(1, 2, 'PER_UNIT', 3000.00, 25000.00, '2027-09-08', 'ACTIVE')");

echo "SQLite database initialized successfully at: " . $sqliteFile . PHP_EOL;
