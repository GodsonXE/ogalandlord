<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Database\Connection;
use Ramsey\Uuid\Uuid;

$db = new Connection();
$pdo = $db->getPdo();
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

echo "Running migrations for driver [{$driver}]...\n";

if ($driver === 'sqlite') {
    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uuid TEXT NOT NULL UNIQUE,
            full_name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone_number TEXT NOT NULL,
            password_hash TEXT NULL,
            role TEXT NOT NULL DEFAULT 'TENANT',
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS properties (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uuid TEXT NOT NULL UNIQUE,
            landlord_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            address_line_1 TEXT NOT NULL,
            city TEXT NOT NULL,
            state TEXT NOT NULL,
            country TEXT NOT NULL DEFAULT 'Nigeria',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS property_caretaker_assignments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            property_id INTEGER NOT NULL,
            caretaker_id INTEGER NOT NULL,
            can_manage_tickets INTEGER NOT NULL DEFAULT 1,
            can_view_finances INTEGER NOT NULL DEFAULT 0,
            assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
            FOREIGN KEY (caretaker_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE(property_id, caretaker_id)
        )",
        "CREATE TABLE IF NOT EXISTS units (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uuid TEXT NOT NULL UNIQUE,
            property_id INTEGER NOT NULL,
            unit_number TEXT NOT NULL,
            apartment_type TEXT NOT NULL,
            default_rent_amount REAL NOT NULL,
            currency TEXT NOT NULL DEFAULT 'NGN',
            is_occupied INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS leases (
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
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
            FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE RESTRICT
        )",
        "CREATE TABLE IF NOT EXISTS rent_reminder_logs (
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
            sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (lease_id) REFERENCES leases(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS rent_payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uuid TEXT NOT NULL UNIQUE,
            lease_id INTEGER NOT NULL,
            amount REAL NOT NULL,
            currency TEXT NOT NULL DEFAULT 'NGN',
            payment_method TEXT NOT NULL,
            gateway_reference TEXT NULL UNIQUE,
            payment_status TEXT NOT NULL DEFAULT 'PENDING',
            payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            covered_period_start DATE NOT NULL,
            covered_period_end DATE NOT NULL,
            receipt_number TEXT NOT NULL UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (lease_id) REFERENCES leases(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS processed_webhooks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            idempotency_key TEXT NOT NULL UNIQUE,
            gateway_provider TEXT NOT NULL,
            event_type TEXT NOT NULL,
            payload_hash TEXT NOT NULL,
            processed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS maintenance_tickets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uuid TEXT NOT NULL UNIQUE,
            property_id INTEGER NOT NULL,
            unit_id INTEGER NOT NULL,
            tenant_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            description TEXT NOT NULL,
            priority TEXT NOT NULL DEFAULT 'MEDIUM',
            status TEXT NOT NULL DEFAULT 'REPORTED',
            assigned_vendor_name TEXT NULL,
            assigned_vendor_phone TEXT NULL,
            resolved_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
            FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
            FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        "CREATE TABLE IF NOT EXISTS saas_subscriptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            landlord_id INTEGER NOT NULL,
            billing_model TEXT NOT NULL DEFAULT 'PER_UNIT',
            rate_per_unit REAL NOT NULL DEFAULT 3000.00,
            rate_per_property REAL NOT NULL DEFAULT 25000.00,
            currency TEXT NOT NULL DEFAULT 'NGN',
            next_billing_date DATE NOT NULL,
            status TEXT NOT NULL DEFAULT 'ACTIVE',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE
        )"
    ];

    foreach ($queries as $q) {
        $pdo->exec($q);
    }
} else {
    $sql = file_get_contents(__DIR__ . '/../database/migrations/2026_09_08_000001_create_oga_landlord_tables.sql');
    $pdo->exec($sql);
}

// Seed baseline accounts with password123
$defaultPasswordHash = password_hash('password123', PASSWORD_BCRYPT);

// 1. SuperAdmin
$saCheck = $pdo->prepare("SELECT id FROM users WHERE email = 'superadmin@propertycare.ng'");
$saCheck->execute();
if (!$saCheck->fetch()) {
    $saUuid = Uuid::uuid4()->toString();
    $pdo->prepare("INSERT INTO users (uuid, full_name, email, phone_number, password_hash, role) VALUES (:u, 'Alhaji Farouk Al-Mansur', 'superadmin@propertycare.ng', '+2348000000001', :p, 'SUPERADMIN')")
        ->execute(['u' => $saUuid, 'p' => $defaultPasswordHash]);
    echo "Seeded SuperAdmin: superadmin@propertycare.ng (password: password123)\n";
}

// 2. Landlord & Property
$lCheck = $pdo->prepare("SELECT id FROM users WHERE email = 'landlord@propertycare.ng'");
$lCheck->execute();
$landlord = $lCheck->fetch();

if (!$landlord) {
    $landlordUuid = Uuid::uuid4()->toString();
    $pdo->prepare("INSERT INTO users (uuid, full_name, email, phone_number, password_hash, role) VALUES (:u, 'Chief Ibrahim Bello', 'landlord@propertycare.ng', '+2348030000001', :p, 'LANDLORD')")
        ->execute(['u' => $landlordUuid, 'p' => $defaultPasswordHash]);
    $landlordId = (int) $pdo->lastInsertId();
    echo "Seeded Landlord: landlord@propertycare.ng (password: password123)\n";
} else {
    $landlordId = (int) $landlord['id'];
    $pdo->prepare("UPDATE users SET password_hash = :p WHERE id = :id")->execute(['p' => $defaultPasswordHash, 'id' => $landlordId]);
}

// Ensure landlord property exists
$pCheck = $pdo->prepare("SELECT id FROM properties WHERE landlord_id = :lid LIMIT 1");
$pCheck->execute(['lid' => $landlordId]);
$property = $pCheck->fetch();

if (!$property) {
    $propUuid = Uuid::uuid4()->toString();
    $pdo->exec("INSERT INTO properties (uuid, landlord_id, title, address_line_1, city, state, country) VALUES ('{$propUuid}', {$landlordId}, 'PHDL Unity Estate, Idu', 'Plot 42 Railway Corridor', 'Idu Industrial', 'Abuja (FCT)', 'Nigeria')");
    $propId = (int) $pdo->lastInsertId();

    for ($i = 1; $i <= 8; $i++) {
        $unitUuid = Uuid::uuid4()->toString();
        $pdo->exec("INSERT INTO units (uuid, property_id, unit_number, apartment_type, default_rent_amount, currency) VALUES ('{$unitUuid}', {$propId}, 'Unit {$i}A', '2-Bedroom Apartment', 2500000.00, 'NGN')");
    }
} else {
    $propId = (int) $property['id'];
}

// 3. Caretaker
$cCheck = $pdo->prepare("SELECT id FROM users WHERE email = 'caretaker.idu@propertycare.ng'");
$cCheck->execute();
$caretaker = $cCheck->fetch();

if (!$caretaker) {
    $caretakerUuid = Uuid::uuid4()->toString();
    $pdo->prepare("INSERT INTO users (uuid, full_name, email, phone_number, password_hash, role) VALUES (:u, 'Musa Danjuma', 'caretaker.idu@propertycare.ng', '+2348030000002', :p, 'CARETAKER')")
        ->execute(['u' => $caretakerUuid, 'p' => $defaultPasswordHash]);
    $caretakerId = (int) $pdo->lastInsertId();
    $pdo->exec("INSERT OR IGNORE INTO property_caretaker_assignments (property_id, caretaker_id) VALUES ({$propId}, {$caretakerId})");
    echo "Seeded Caretaker: caretaker.idu@propertycare.ng (password: password123)\n";
} else {
    $caretakerId = (int) $caretaker['id'];
    $pdo->prepare("UPDATE users SET password_hash = :p WHERE id = :id")->execute(['p' => $defaultPasswordHash, 'id' => $caretakerId]);
}

// 4. Sample Maintenance Tickets for Caretaker
$tCheck = $pdo->query("SELECT COUNT(*) FROM maintenance_tickets")->fetchColumn();
if ((int)$tCheck === 0) {
    $firstUnit = $pdo->query("SELECT id FROM units WHERE property_id = {$propId} LIMIT 1")->fetchColumn();
    $tenantId = $landlordId; // Placeholder tenant
    $tickets = [
        ['Water pressure valve leaking under sink', 'Small steady drip observed under kitchen sink pipe since yesterday.', 'MEDIUM', 'IN_REVIEW', null],
        ['Pre-paid meter circuit breaker trips on heavy load', 'Trips whenever master bedroom AC is powered on.', 'HIGH', 'TECHNICIAN_DISPATCHED', 'Musa Electricals (0803-111-2233)'],
        ['Master bedroom window latch loosened', 'Window lock does not fasten securely during heavy winds.', 'LOW', 'COMPLETED', 'Abuja Carpentry Co.']
    ];
    foreach ($tickets as $t) {
        $tuuid = Uuid::uuid4()->toString();
        $stmt = $pdo->prepare("INSERT INTO maintenance_tickets (uuid, property_id, unit_id, tenant_id, title, description, priority, status, assigned_vendor_name) VALUES (:u, :pid, :uid, :tid, :title, :desc, :prio, :st, :ven)");
        $stmt->execute([
            'u'     => $tuuid,
            'pid'   => $propId,
            'uid'   => $firstUnit ?: 1,
            'tid'   => $tenantId,
            'title' => $t[0],
            'desc'  => $t[1],
            'prio'  => $t[2],
            'st'    => $t[3],
            'ven'   => $t[4]
        ]);
    }
}

// 5. Baseline SaaS Subscription for Landlord
$subCheck = $pdo->prepare("SELECT id FROM saas_subscriptions WHERE landlord_id = :lid");
$subCheck->execute(['lid' => $landlordId]);
if (!$subCheck->fetch()) {
    $pdo->exec("INSERT INTO saas_subscriptions (landlord_id, billing_model, rate_per_unit, rate_per_property, currency, next_billing_date, status) VALUES ({$landlordId}, 'PER_UNIT', 3000.00, 25000.00, 'NGN', '" . date('Y-m-d', strtotime('+1 year')) . "', 'ACTIVE')");
}

echo "Migrations and seeding completed successfully.\n";