<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Database\Connection;
use Ramsey\Uuid\Uuid;

echo "=== UPGRADING DATABASE SCHEMA FOR EXTENDED FEATURES ===\n";

$db = new Connection();
$pdo = $db->getPdo();

// 1. Helper to safely add column if not exists in SQLite
function addColumnIfNotExists(PDO $pdo, string $table, string $column, string $typeDef): void {
    $stmt = $pdo->query("PRAGMA table_info({$table})");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $existing = array_column($cols, 'name');
    if (!in_array($column, $existing, true)) {
        $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$column} {$typeDef}");
        echo "  + Added column {$table}.{$column}\n";
    }
}

// Add KYC and Company columns to users
addColumnIfNotExists($pdo, 'users', 'company_name', 'TEXT NULL');
addColumnIfNotExists($pdo, 'users', 'id_type', 'TEXT NULL DEFAULT "NIN"');
addColumnIfNotExists($pdo, 'users', 'id_number', 'TEXT NULL');
addColumnIfNotExists($pdo, 'users', 'kyc_nin', 'TEXT NULL');
addColumnIfNotExists($pdo, 'users', 'kyc_bvn', 'TEXT NULL');
addColumnIfNotExists($pdo, 'users', 'kyc_status', 'TEXT NOT NULL DEFAULT "VERIFIED"');
addColumnIfNotExists($pdo, 'users', 'employer_name', 'TEXT NULL');
addColumnIfNotExists($pdo, 'users', 'occupation', 'TEXT NULL');
addColumnIfNotExists($pdo, 'users', 'guarantor_name', 'TEXT NULL');
addColumnIfNotExists($pdo, 'users', 'guarantor_phone', 'TEXT NULL');
addColumnIfNotExists($pdo, 'users', 'admin_notes', 'TEXT NULL');
addColumnIfNotExists($pdo, 'users', 'rate_per_unit', 'REAL NULL DEFAULT 3000.0');

// Add photo, artisan, and cost columns to maintenance_tickets
addColumnIfNotExists($pdo, 'maintenance_tickets', 'photo_url', 'TEXT NULL');
addColumnIfNotExists($pdo, 'maintenance_tickets', 'artisan_id', 'INTEGER NULL');
addColumnIfNotExists($pdo, 'maintenance_tickets', 'estimated_cost', 'REAL NULL');
addColumnIfNotExists($pdo, 'maintenance_tickets', 'actual_cost', 'REAL NULL');
addColumnIfNotExists($pdo, 'maintenance_tickets', 'work_status', 'TEXT NOT NULL DEFAULT "REPORTED"');

// 2. Create landlord_payment_settings
$pdo->exec("
CREATE TABLE IF NOT EXISTS landlord_payment_settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    landlord_id INTEGER NOT NULL UNIQUE,
    bank_name TEXT NOT NULL DEFAULT 'Zenith Bank Plc',
    account_number TEXT NOT NULL DEFAULT '1012345678',
    account_name TEXT NOT NULL DEFAULT 'Chief Ibrahim Bello Properties',
    gateway_provider TEXT NOT NULL DEFAULT 'PAYSTACK',
    paystack_public_key TEXT NULL,
    paystack_secret_key TEXT NULL,
    flutterwave_public_key TEXT NULL,
    flutterwave_secret_key TEXT NULL,
    settlement_preference TEXT NOT NULL DEFAULT 'DIRECT_BANK',
    auto_receipt_generation INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (landlord_id) REFERENCES users(id) ON DELETE CASCADE
);");
echo "  ✓ Table landlord_payment_settings verified.\n";

// 3. Create artisans table
$pdo->exec("
CREATE TABLE IF NOT EXISTS artisans (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    landlord_id INTEGER NULL,
    property_id INTEGER NULL,
    full_name TEXT NOT NULL,
    phone_number TEXT NOT NULL,
    email TEXT NULL,
    trade_skill TEXT NOT NULL,
    rating REAL NOT NULL DEFAULT 4.8,
    jobs_completed INTEGER NOT NULL DEFAULT 0,
    hourly_rate REAL NOT NULL DEFAULT 5000.00,
    is_verified INTEGER NOT NULL DEFAULT 1,
    is_available INTEGER NOT NULL DEFAULT 1,
    city TEXT NOT NULL DEFAULT 'Abuja',
    state TEXT NOT NULL DEFAULT 'FCT',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);");
echo "  ✓ Table artisans verified.\n";

// 4. Create property_expenses table
$pdo->exec("
CREATE TABLE IF NOT EXISTS property_expenses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    property_id INTEGER NOT NULL,
    unit_id INTEGER NULL,
    recorded_by_id INTEGER NOT NULL,
    category TEXT NOT NULL,
    title TEXT NOT NULL,
    description TEXT NULL,
    amount REAL NOT NULL,
    currency TEXT NOT NULL DEFAULT 'NGN',
    artisan_id INTEGER NULL,
    vendor_name TEXT NULL,
    expense_date DATE NOT NULL,
    receipt_url TEXT NULL,
    is_tax_deductible INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);");
echo "  ✓ Table property_expenses verified.\n";

// 5. Create community_announcements table
$pdo->exec("
CREATE TABLE IF NOT EXISTS community_announcements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    property_id INTEGER NULL,
    sender_id INTEGER NOT NULL,
    sender_role TEXT NOT NULL,
    title TEXT NOT NULL,
    message TEXT NOT NULL,
    category TEXT NOT NULL DEFAULT 'GENERAL',
    priority TEXT NOT NULL DEFAULT 'NORMAL',
    dispatch_channels TEXT NOT NULL DEFAULT 'NOTICE_BOARD,SMS,EMAIL',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);");
echo "  ✓ Table community_announcements verified.\n";

// 6. Create facility_bookings table
$pdo->exec("
CREATE TABLE IF NOT EXISTS facility_bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    property_id INTEGER NOT NULL,
    tenant_id INTEGER NOT NULL,
    facility_name TEXT NOT NULL,
    booking_date DATE NOT NULL,
    time_slot TEXT NOT NULL,
    guest_count INTEGER NOT NULL DEFAULT 1,
    purpose TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'PENDING',
    reviewed_by_id INTEGER NULL,
    reviewed_at DATETIME NULL,
    review_notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE
);");
echo "  ✓ Table facility_bookings verified.\n";

// 7. Create alert_rules table
$pdo->exec("
CREATE TABLE IF NOT EXISTS alert_rules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    uuid TEXT NOT NULL UNIQUE,
    scope TEXT NOT NULL DEFAULT 'GLOBAL',
    target_id INTEGER NULL,
    rule_name TEXT NOT NULL,
    trigger_event TEXT NOT NULL,
    channels TEXT NOT NULL DEFAULT 'SMS,EMAIL,IN_APP',
    grace_period_days INTEGER NOT NULL DEFAULT 7,
    penalty_rate_percent REAL NOT NULL DEFAULT 1.5,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);");
echo "  ✓ Table alert_rules verified.\n";

// 8. Seed Baseline Artisans
$artisanCount = (int)$pdo->query("SELECT COUNT(*) FROM artisans")->fetchColumn();
if ($artisanCount === 0) {
    $artisans = [
        ['Emeka Obi', '+2348031122334', 'emeka.plumbing@artisans.ng', 'PLUMBING', 4.9, 42, 6000.00, 'Abuja', 'FCT'],
        ['Babatunde Adeleke', '+2348032233445', 'babatunde.electric@artisans.ng', 'ELECTRICAL', 4.8, 38, 7500.00, 'Lagos', 'Lagos'],
        ['Haruna Mohammed', '+2348033344556', 'haruna.hvac@artisans.ng', 'HVAC_AIR_CONDITIONING', 4.9, 56, 10000.00, 'Abuja', 'FCT'],
        ['Chukwudi Nwachukwu', '+2348034455667', 'chukwudi.carpentry@artisans.ng', 'CARPENTRY', 4.7, 29, 6500.00, 'Port Harcourt', 'Rivers'],
        ['Segun Alabi', '+2348035566778', 'segun.painter@artisans.ng', 'PAINTING', 4.8, 31, 5000.00, 'Lagos', 'Lagos'],
        ['Aliyu Garba', '+2348036677889', 'aliyu.generator@artisans.ng', 'GENERATOR_REPAIR', 5.0, 64, 12000.00, 'Abuja', 'FCT'],
    ];

    $insArtisan = $pdo->prepare("INSERT INTO artisans (uuid, landlord_id, property_id, full_name, phone_number, email, trade_skill, rating, jobs_completed, hourly_rate, is_verified, is_available, city, state)
                                VALUES (:uuid, 2, 1, :name, :phone, :email, :skill, :rating, :jobs, :rate, 1, 1, :city, :state)");
    foreach ($artisans as $a) {
        $insArtisan->execute([
            'uuid' => Uuid::uuid4()->toString(),
            'name' => $a[0],
            'phone' => $a[1],
            'email' => $a[2],
            'skill' => $a[3],
            'rating' => $a[4],
            'jobs' => $a[5],
            'rate' => $a[6],
            'city' => $a[7],
            'state' => $a[8],
        ]);
    }
    echo "  ✓ Seeded 6 trusted artisans.\n";
}

// 9. Seed Default Payment Settings for Chief Ibrahim Bello (Landlord #2)
$payCheck = $pdo->query("SELECT COUNT(*) FROM landlord_payment_settings WHERE landlord_id = 2")->fetchColumn();
if ((int)$payCheck === 0) {
    $pdo->prepare("INSERT INTO landlord_payment_settings (uuid, landlord_id, bank_name, account_number, account_name, gateway_provider, paystack_public_key, paystack_secret_key, flutterwave_public_key, settlement_preference, auto_receipt_generation)
                   VALUES (:uuid, 2, 'Zenith Bank Plc', '1012345678', 'Chief Ibrahim Bello Properties Ltd', 'PAYSTACK', 'pk_test_9874abc5678def', 'sk_test_1234secretkey', 'FLWPUBK_TEST-98218731', 'DIRECT_BANK', 1)")
        ->execute(['uuid' => Uuid::uuid4()->toString()]);
    echo "  ✓ Seeded payment settings for Landlord #2.\n";
}

// 10. Seed Baseline Expenses
$expCount = (int)$pdo->query("SELECT COUNT(*) FROM property_expenses")->fetchColumn();
if ($expCount === 0) {
    $expenses = [
        [1, 1, 2, 'ARTISAN_REPAIR', 'Main Overhead Water Pump Replacement', 'Replaced burnt submersible pump with 2HP Grundfos pump', 285000.00, 1, 'Emeka Obi Plumbing', date('Y-m-d', strtotime('-5 days'))],
        [1, null, 2, 'DIESEL_GENERATOR', 'Estate Central Generator 500L Diesel Top-up', 'Procured 500 liters of diesel for estate backup generator', 450000.00, null, 'TotalEnergies Idu', date('Y-m-d', strtotime('-12 days'))],
        [1, null, 2, 'SECURITY_GUARD', 'September Armed Security Guard Retainer', 'Monthly settlement for Halogen Security guard squad at main gates', 180000.00, null, 'Halogen Security Services', date('Y-m-d', strtotime('-2 days'))],
        [1, 3, 2, 'ARTISAN_REPAIR', 'Inverter Line Rewiring & Surge Protection', 'Protected unit against high voltage surge from grid', 65000.00, 2, 'Babatunde Adeleke Electric', date('Y-m-d', strtotime('-8 days'))],
    ];
    $insExp = $pdo->prepare("INSERT INTO property_expenses (uuid, property_id, unit_id, recorded_by_id, category, title, description, amount, artisan_id, vendor_name, expense_date)
                            VALUES (:uuid, :pid, :uid, :rec, :cat, :title, :desc, :amt, :aid, :vendor, :date)");
    foreach ($expenses as $e) {
        $insExp->execute([
            'uuid' => Uuid::uuid4()->toString(),
            'pid' => $e[0],
            'uid' => $e[1],
            'rec' => $e[2],
            'cat' => $e[3],
            'title' => $e[4],
            'desc' => $e[5],
            'amt' => $e[6],
            'aid' => $e[7],
            'vendor' => $e[8],
            'date' => $e[9],
        ]);
    }
    echo "  ✓ Seeded 4 property expenses.\n";
}

// 11. Seed Baseline Notice Board Announcements
$annCount = (int)$pdo->query("SELECT COUNT(*) FROM community_announcements")->fetchColumn();
if ($annCount === 0) {
    $announcements = [
        [1, 3, 'CARETAKER', 'Scheduled Generator Maintenance Notice', 'Dear Residents, our 250kVA standby generator will undergo scheduled servicing on Saturday between 10:00 AM and 1:00 PM. Please ensure home appliances are safeguarded.', 'POWER_GENERATOR', 'HIGH'],
        [1, 2, 'LANDLORD', 'Quarterly Water Treatment & Tank Flushing', 'The central underground reservoirs and rooftop tanks will be chlorinated and backwashed on Tuesday. Running water will pause for 3 hours.', 'WATER_SUPPLY', 'NORMAL'],
        [1, 3, 'CARETAKER', 'Security Gate Pass Protocol Update', 'Residents are reminded to pre-register visitors via the portal or notify the security gate house for fast-track gate verification.', 'SECURITY', 'NORMAL'],
    ];
    $insAnn = $pdo->prepare("INSERT INTO community_announcements (uuid, property_id, sender_id, sender_role, title, message, category, priority)
                             VALUES (:uuid, :pid, :sid, :srole, :title, :msg, :cat, :prio)");
    foreach ($announcements as $an) {
        $insAnn->execute([
            'uuid' => Uuid::uuid4()->toString(),
            'pid' => $an[0],
            'sid' => $an[1],
            'srole' => $an[2],
            'title' => $an[3],
            'msg' => $an[4],
            'cat' => $an[5],
            'prio' => $an[6],
        ]);
    }
    echo "  ✓ Seeded 3 notice board announcements.\n";
}

// 12. Seed Baseline Facility Bookings
$facCount = (int)$pdo->query("SELECT COUNT(*) FROM facility_bookings")->fetchColumn();
if ($facCount === 0) {
    $bookings = [
        [1, 4, 'ESTATE_CLUBHOUSE', date('Y-m-d', strtotime('+3 days')), '14:00 - 18:00', 15, 'Family birthday celebration and small gathering', 'APPROVED', 3, 'Approved by Caretaker. No loud music after 10PM.'],
        [1, 4, 'SWIMMING_POOL', date('Y-m-d', strtotime('+5 days')), '10:00 - 12:00', 4, 'Private swim training session', 'PENDING', null, null],
        [1, 6, 'TENNIS_COURT', date('Y-m-d', strtotime('+2 days')), '07:00 - 09:00', 2, 'Morning doubles match with neighbor', 'APPROVED', 3, 'Confirmed. Court keys available with gate security.'],
    ];
    $insFac = $pdo->prepare("INSERT INTO facility_bookings (uuid, property_id, tenant_id, facility_name, booking_date, time_slot, guest_count, purpose, status, reviewed_by_id, review_notes)
                            VALUES (:uuid, :pid, :tid, :fac, :date, :slot, :guests, :purpose, :status, :rev, :notes)");
    foreach ($bookings as $b) {
        $insFac->execute([
            'uuid' => Uuid::uuid4()->toString(),
            'pid' => $b[0],
            'tid' => $b[1],
            'fac' => $b[2],
            'date' => $b[3],
            'slot' => $b[4],
            'guests' => $b[5],
            'purpose' => $b[6],
            'status' => $b[7],
            'rev' => $b[8],
            'notes' => $b[9],
        ]);
    }
    echo "  ✓ Seeded 3 facility bookings.\n";
}

// 13. Seed Baseline Alert Rules
$ruleCount = (int)$pdo->query("SELECT COUNT(*) FROM alert_rules")->fetchColumn();
if ($ruleCount === 0) {
    $rules = [
        ['GLOBAL', null, 'Early Rent Reminder (T-30 Days)', 'RENT_DUE_T30', 'EMAIL,SMS,IN_APP', 7, 1.5, 1],
        ['GLOBAL', null, 'Final Warning Notice (T-7 Days)', 'RENT_DUE_T7', 'EMAIL,SMS,IN_APP', 7, 2.0, 1],
        ['GLOBAL', null, 'Eve of Due Date Alert (T-1 Day)', 'RENT_DUE_T1', 'SMS,IN_APP', 5, 2.5, 1],
        ['GLOBAL', null, 'Daily Dunning After Grace Period', 'RENT_OVERDUE_DAILY', 'SMS,EMAIL,IN_APP', 14, 2.5, 1],
        ['ESTATE', 1, 'Unity Estate Maintenance Logged SMS Alert', 'MAINTENANCE_LOGGED', 'SMS,IN_APP', 0, 0.0, 1],
        ['ESTATE', 1, 'Facility Reservation Immediate Confirmation', 'FACILITY_BOOKED', 'EMAIL,SMS', 0, 0.0, 1],
    ];
    $insRule = $pdo->prepare("INSERT INTO alert_rules (uuid, scope, target_id, rule_name, trigger_event, channels, grace_period_days, penalty_rate_percent, is_active)
                             VALUES (:uuid, :scope, :tid, :name, :evt, :ch, :grace, :pen, :act)");
    foreach ($rules as $r) {
        $insRule->execute([
            'uuid' => Uuid::uuid4()->toString(),
            'scope' => $r[0],
            'tid' => $r[1],
            'name' => $r[2],
            'evt' => $r[3],
            'ch' => $r[4],
            'grace' => $r[5],
            'pen' => $r[6],
            'act' => $r[7],
        ]);
    }
    echo "  ✓ Seeded 6 alert rules.\n";
}

// 14. Update sample tenant KYC info
$pdo->exec("UPDATE users SET id_type = 'NIN', id_number = '78901234567', kyc_status = 'VERIFIED', employer_name = 'Central Bank of Nigeria', occupation = 'Senior Financial Analyst', guarantor_name = 'Barrister Okey Okafor', guarantor_phone = '+2348029988776' WHERE id = 4");
$pdo->exec("UPDATE users SET id_type = 'BVN', id_number = '22334455667', kyc_status = 'VERIFIED', employer_name = 'Dangote Group', occupation = 'Operations Manager', guarantor_name = 'Alhaji Sani Bakare', guarantor_phone = '+2348039988775' WHERE id = 5");

echo "\n✓ DATABASE UPGRADE COMPLETED WITH 100% SUCCESS!\n";
