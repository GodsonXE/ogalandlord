<?php
declare(strict_types=1);

namespace App\Infrastructure\Database;

use PDO;
use PDOException;
use RuntimeException;

class Connection
{
    private ?PDO $pdo = null;
    private static bool $schemaEnsured = false;

    public function __construct(private readonly ?array $config = null) {}

    public function getPdo(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $cfg = $this->config ?? require __DIR__ . '/../../../config/database.php';

        if (($cfg['driver'] ?? 'mysql') === 'sqlite') {
            $defaultSqlite = __DIR__ . '/../../../database/oga_landlord.sqlite';
            if (!file_exists($defaultSqlite) && file_exists(__DIR__ . '/../../../database/property_platform.sqlite')) {
                $defaultSqlite = __DIR__ . '/../../../database/property_platform.sqlite';
            }
            $dbPath = $cfg['database'] ?? $defaultSqlite;
            $dsn = 'sqlite:' . $dbPath;
            try {
                $this->pdo = new PDO($dsn, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                $this->pdo->exec('PRAGMA foreign_keys = ON;');
                $this->pdo->exec('PRAGMA journal_mode = WAL;');
                $this->pdo->exec('PRAGMA synchronous = NORMAL;');
                $this->pdo->exec('PRAGMA busy_timeout = 10000;');
                $this->ensureSchemaUpToDate($this->pdo, 'sqlite');
                return $this->pdo;
            } catch (PDOException $e) {
                throw new RuntimeException("SQLite connection error: " . $e->getMessage());
            }
        }

        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['driver'],
            $cfg['host'],
            $cfg['port'],
            $cfg['database'],
            $cfg['charset']
        );

        try {
            $this->pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            $this->ensureSchemaUpToDate($this->pdo, (string)$cfg['driver']);
            return $this->pdo;
        } catch (PDOException $e) {
            // If unknown database error (1049), auto-create the database in MySQL
            if ($e->getCode() === 1049) {
                try {
                    $serverPdo = new PDO(
                        sprintf('%s:host=%s;port=%d;charset=%s', $cfg['driver'], $cfg['host'], $cfg['port'], $cfg['charset']),
                        $cfg['username'],
                        $cfg['password']
                    );
                    $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$cfg['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    
                    // Reconnect to newly created database
                    $this->pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]);
                    
                    // Run baseline migration
                    $sqlPath = __DIR__ . '/../../../database/migrations/2026_09_08_000001_create_platform_tables.sql';
                    if (file_exists($sqlPath)) {
                        $this->pdo->exec(file_get_contents($sqlPath));
                    }
                    return $this->pdo;
                } catch (\Throwable $createErr) {
                    // Fall through to fallback
                }
            }

            // If MySQL is down or unreachable, seamlessly fall back to pre-seeded SQLite database
            $sqlitePath = __DIR__ . '/../../../database/property_platform.sqlite';
            if (!file_exists($sqlitePath)) {
                $setupScript = __DIR__ . '/../../../database/setup_sqlite.php';
                if (file_exists($setupScript)) {
                    require_once $setupScript;
                }
            }

            if (file_exists($sqlitePath)) {
                $this->pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                $this->pdo->exec('PRAGMA foreign_keys = ON;');
                $this->ensureSchemaUpToDate($this->pdo, 'sqlite');
                return $this->pdo;
            }

            throw new RuntimeException("Database connection error: " . $e->getMessage());
        }
    }

    private function ensureSchemaUpToDate(PDO $pdo, string $driver): void
    {
        if (self::$schemaEnsured) {
            return;
        }
        self::$schemaEnsured = true;

        try {
            if ($driver === 'sqlite') {
                // 1. units.rooms_count
                $cols = $pdo->query("PRAGMA table_info(units)")->fetchAll();
                $colNames = array_column($cols, 'name');
                if (!in_array('rooms_count', $colNames, true)) {
                    $pdo->exec("ALTER TABLE units ADD COLUMN rooms_count INTEGER NOT NULL DEFAULT 2");
                }

                // 2. maintenance_tickets additions
                $ticketCols = $pdo->query("PRAGMA table_info(maintenance_tickets)")->fetchAll();
                $ticketColNames = array_column($ticketCols, 'name');
                if (!in_array('ticket_code', $ticketColNames, true)) {
                    $pdo->exec("ALTER TABLE maintenance_tickets ADD COLUMN ticket_code TEXT NULL");
                }
                if (!in_array('category', $ticketColNames, true)) {
                    $pdo->exec("ALTER TABLE maintenance_tickets ADD COLUMN category TEXT NOT NULL DEFAULT 'GENERAL'");
                }
                if (!in_array('is_escalated_to_landlord', $ticketColNames, true)) {
                    $pdo->exec("ALTER TABLE maintenance_tickets ADD COLUMN is_escalated_to_landlord INTEGER NOT NULL DEFAULT 0");
                }
                if (!in_array('escalated_at', $ticketColNames, true)) {
                    $pdo->exec("ALTER TABLE maintenance_tickets ADD COLUMN escalated_at DATETIME NULL");
                }
                if (!in_array('escalation_reason', $ticketColNames, true)) {
                    $pdo->exec("ALTER TABLE maintenance_tickets ADD COLUMN escalation_reason TEXT NULL");
                }

                // Populate ticket_code if null
                $pdo->exec("UPDATE maintenance_tickets SET ticket_code = 'TKT-2026-' || printf('%04d', id) WHERE ticket_code IS NULL");

                // 3. tenancy_history Table
                $pdo->exec("CREATE TABLE IF NOT EXISTS tenancy_history (
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
                )");

                // 4. ticket_messages Table
                $pdo->exec("CREATE TABLE IF NOT EXISTS ticket_messages (
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
                )");

                // 5. tenant_payments Table
                $pdo->exec("CREATE TABLE IF NOT EXISTS tenant_payments (
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
                )");

                // 6. properties.caretaker_delegation_mode
                $propCols = $pdo->query("PRAGMA table_info(properties)")->fetchAll();
                $propColNames = array_column($propCols, 'name');
                if (!in_array('caretaker_delegation_mode', $propColNames, true)) {
                    $pdo->exec("ALTER TABLE properties ADD COLUMN caretaker_delegation_mode TEXT NOT NULL DEFAULT 'SUPERADMIN_CONCIERGE'");
                }

                // Ensure Caretaker 3 assignment to Property 1 if exists
                $pdo->exec("INSERT OR IGNORE INTO property_caretaker_assignments (property_id, caretaker_id, can_manage_tickets, can_view_finances) VALUES (1, 3, 1, 0)");

                // 8. estates_directory Table
                $pdo->exec("CREATE TABLE IF NOT EXISTS estates_directory (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    title TEXT NOT NULL,
                    state TEXT NOT NULL,
                    district TEXT NULL,
                    zone_region TEXT NULL,
                    estate_category TEXT NULL,
                    market_availability TEXT NULL,
                    description TEXT NULL,
                    source TEXT NOT NULL DEFAULT 'DIRECTORY',
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE(title, state)
                )");
            }
        } catch (\Throwable $e) {
            // Ignore if schema already upgraded
        }
    }
}