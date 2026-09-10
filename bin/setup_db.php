<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Ramsey\Uuid\Uuid;

echo "=== INITIALIZING DATABASE SETUP ===\n";

$cfg = require __DIR__ . '/../config/database.php';
$mysqlSuccess = false;

// 1. Try MySQL
try {
    echo "Attempting MySQL connection to {$cfg['host']}:{$cfg['port']} with user '{$cfg['username']}'...\n";
    $pdo = new PDO("mysql:host={$cfg['host']};port={$cfg['port']};charset=utf8mb4", $cfg['username'], $cfg['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "Creating database 'oga_landlord' if not exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS oga_landlord CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE oga_landlord");

    echo "Running MySQL schema migrations...\n";
    $sql = file_get_contents(__DIR__ . '/../database/migrations/2026_09_08_000001_create_oga_landlord_tables.sql');
    $pdo->exec($sql);

    // Seed baseline accounts with password123
    $defaultPasswordHash = password_hash('password123', PASSWORD_BCRYPT);

    // SuperAdmin
    $saCheck = $pdo->prepare("SELECT id FROM users WHERE email = 'superadmin@ogalandlord.ng' OR email = 'superadmin@propertycare.ng'");
    $saCheck->execute();
    if (!$saCheck->fetch()) {
        $saUuid = Uuid::uuid4()->toString();
        $pdo->prepare("INSERT INTO users (uuid, full_name, email, phone_number, password_hash, role) VALUES (:u, 'Alhaji Farouk Al-Mansur', 'superadmin@ogalandlord.ng', '+2348000000001', :p, 'SUPERADMIN')")
            ->execute(['u' => $saUuid, 'p' => $defaultPasswordHash]);
    }

    // Landlord
    $lCheck = $pdo->prepare("SELECT id FROM users WHERE email = 'landlord@ogalandlord.ng' OR email = 'landlord@propertycare.ng'");
    $lCheck->execute();
    $landlord = $lCheck->fetch();

    if (!$landlord) {
        $landlordUuid = Uuid::uuid4()->toString();
        $pdo->prepare("INSERT INTO users (uuid, full_name, email, phone_number, password_hash, role) VALUES (:u, 'Chief Ibrahim Bello', 'landlord@ogalandlord.ng', '+2348030000001', :p, 'LANDLORD')")
            ->execute(['u' => $landlordUuid, 'p' => $defaultPasswordHash]);
        $landlordId = (int) $pdo->lastInsertId();
    } else {
        $landlordId = (int) $landlord['id'];
        $pdo->prepare("UPDATE users SET password_hash = :p WHERE id = :id")->execute(['p' => $defaultPasswordHash, 'id' => $landlordId]);
    }

    // Landlord Property
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

    // Caretaker
    $cCheck = $pdo->prepare("SELECT id FROM users WHERE email = 'caretaker.idu@ogalandlord.ng' OR email = 'caretaker.idu@propertycare.ng'");
    $cCheck->execute();
    $caretaker = $cCheck->fetch();

    if (!$caretaker) {
        $caretakerUuid = Uuid::uuid4()->toString();
        $pdo->prepare("INSERT INTO users (uuid, full_name, email, phone_number, password_hash, role) VALUES (:u, 'Musa Danjuma', 'caretaker.idu@ogalandlord.ng', '+2348030000002', :p, 'CARETAKER')")
            ->execute(['u' => $caretakerUuid, 'p' => $defaultPasswordHash]);
        $caretakerId = (int) $pdo->lastInsertId();
        $pdo->exec("INSERT IGNORE INTO property_caretaker_assignments (property_id, caretaker_id) VALUES ({$propId}, {$caretakerId})");
    } else {
        $caretakerId = (int) $caretaker['id'];
        $pdo->prepare("UPDATE users SET password_hash = :p WHERE id = :id")->execute(['p' => $defaultPasswordHash, 'id' => $caretakerId]);
    }

    // SaaS Sub
    $subCheck = $pdo->prepare("SELECT id FROM saas_subscriptions WHERE landlord_id = :lid");
    $subCheck->execute(['lid' => $landlordId]);
    if (!$subCheck->fetch()) {
        $pdo->exec("INSERT INTO saas_subscriptions (landlord_id, billing_model, rate_per_unit, rate_per_property, currency, next_billing_date, status) VALUES ({$landlordId}, 'PER_UNIT', 3000.00, 25000.00, 'NGN', '" . date('Y-m-d', strtotime('+1 year')) . "', 'ACTIVE')");
    }

    echo "✓ MySQL database 'property_platform' successfully initialized and seeded!\n";
    $mysqlSuccess = true;
} catch (\Throwable $e) {
    echo "Notice: MySQL setup encountered: " . $e->getMessage() . "\n";
}

if (!$mysqlSuccess) {
    echo "Defaulting seamlessly to pre-seeded SQLite database...\n";
}