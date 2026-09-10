<?php
declare(strict_types=1);

namespace App\Domain\SuperAdmin\Services;

use App\Infrastructure\Database\Connection;
use PDO;
use Ramsey\Uuid\Uuid;

class SuperAdminDataService
{
    public function __construct(private readonly Connection $db) {}

    /**
     * Retrieve all tenants across all landlords and properties.
     */
    public function getAllTenants(): array
    {
        $pdo = $this->db->getPdo();
        $sql = "SELECT u.id, u.id as tenant_id, u.uuid as tenant_uuid, 
                       u.full_name, u.full_name as tenant_name, 
                       u.email, u.email as tenant_email, 
                       u.phone_number, u.phone_number as tenant_phone, 
                       u.created_at as registered_at,
                       l.id as lease_id, l.uuid as lease_uuid, l.agreement_status, l.agreement_mode,
                       l.rent_amount, l.currency, l.rent_start_date, l.rent_due_date,
                       l.emergency_contact_name, l.emergency_contact_phone, l.document_sha256_hash,
                       un.id as unit_id, un.unit_number, un.apartment_type, un.rooms_count,
                       p.id as property_id, p.title as property_title, p.address_line_1, p.city, p.state,
                       landlord.full_name as landlord_name, landlord.email as landlord_email
                FROM users u
                LEFT JOIN leases l ON l.tenant_id = u.id
                LEFT JOIN units un ON l.unit_id = un.id
                LEFT JOIN properties p ON un.property_id = p.id
                LEFT JOIN users landlord ON p.landlord_id = landlord.id
                WHERE u.role = 'TENANT'
                ORDER BY u.id DESC";

        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieve all landlords with aggregate metrics across all their properties.
     */
    public function getAllLandlords(): array
    {
        $pdo = $this->db->getPdo();
        $sql = "SELECT u.id, u.id as landlord_id, u.uuid as landlord_uuid, 
                       u.full_name, u.full_name as landlord_name,
                       u.email, u.email as landlord_email, 
                       u.phone_number, u.phone_number as landlord_phone, 
                       u.created_at as registered_at,
                       COUNT(DISTINCT p.id) as properties_count,
                       COUNT(DISTINCT un.id) as total_units,
                       COUNT(DISTINCT un.id) as units_count,
                       COUNT(DISTINCT l.id) as active_leases,
                       COUNT(DISTINCT l.id) as active_leases_count,
                       COALESCE(SUM(un.default_rent_amount), 0) as total_annual_rent_roll,
                       COALESCE(SUM(un.default_rent_amount), 0) as portfolio_est_value,
                       GROUP_CONCAT(DISTINCT p.title) as estates_list,
                       GROUP_CONCAT(DISTINCT p.city) as property_cities
                FROM users u
                LEFT JOIN properties p ON p.landlord_id = u.id
                LEFT JOIN units un ON un.property_id = p.id
                LEFT JOIN leases l ON l.unit_id = un.id
                WHERE u.role = 'LANDLORD'
                GROUP BY u.id
                ORDER BY u.id ASC";

        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate RFC 4180 compliant CSV string for all tenants.
     */
    public function exportTenantsCsv(): string
    {
        $tenants = $this->getAllTenants();
        $fh = fopen('php://memory', 'r+');

        fputcsv($fh, [
            'Tenant ID',
            'Full Name',
            'Email Address',
            'Phone Number',
            'Property / Estate',
            'Location (City, State)',
            'Unit / Flat Number',
            'Apartment Type',
            'Rooms Count',
            'Annual Rent (NGN)',
            'Agreement Status',
            'Rent Due Date',
            'Landlord Name',
            'Emergency Contact Name',
            'Emergency Contact Phone',
            'Audit Hash'
        ]);

        foreach ($tenants as $t) {
            fputcsv($fh, [
                $t['tenant_id'],
                $t['tenant_name'],
                $t['tenant_email'],
                $t['tenant_phone'],
                $t['property_title'] ?? 'Unassigned',
                ($t['city'] ?? '') . ($t['state'] ? ', ' . $t['state'] : ''),
                $t['unit_number'] ?? 'N/A',
                $t['apartment_type'] ?? 'N/A',
                $t['rooms_count'] ?? 2,
                number_format((float)($t['rent_amount'] ?? 0), 2, '.', ''),
                $t['agreement_status'] ?? 'PENDING',
                $t['rent_due_date'] ?? 'N/A',
                $t['landlord_name'] ?? 'Chief Ibrahim Bello',
                $t['emergency_contact_name'] ?? '',
                $t['emergency_contact_phone'] ?? '',
                $t['document_sha256_hash'] ?? 'N/A'
            ]);
        }

        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv ?: '';
    }

    /**
     * Generate RFC 4180 compliant CSV string for all landlords.
     */
    public function exportLandlordsCsv(): string
    {
        $landlords = $this->getAllLandlords();
        $fh = fopen('php://memory', 'r+');

        fputcsv($fh, [
            'Landlord ID',
            'Full Name',
            'Email Address',
            'Phone Number',
            'Managed Estates Count',
            'Managed Locations',
            'Total Portfolio Units',
            'Active Leases',
            'Projected SaaS ARR (NGN)',
            'Status'
        ]);

        foreach ($landlords as $l) {
            $units = (int)($l['units_count'] ?? 0);
            $annualFee = $units * 3000.0;
            fputcsv($fh, [
                $l['landlord_id'],
                $l['landlord_name'],
                $l['landlord_email'],
                $l['landlord_phone'],
                $l['properties_count'],
                $l['property_cities'] ?? 'Abuja',
                $units,
                $l['active_leases_count'],
                number_format($annualFee, 2, '.', ''),
                'ACTIVE'
            ]);
        }

        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv ?: '';
    }

    /**
     * Downloadable template CSV for Tenants
     */
    public function getTenantsCsvTemplate(): string
    {
        $fh = fopen('php://memory', 'r+');
        fputcsv($fh, [
            'Full Name',
            'Email Address',
            'Phone Number',
            'Property Name',
            'City',
            'State',
            'Unit Number',
            'Apartment Type',
            'Rooms Count',
            'Rent Amount',
            'Rent Due Date',
            'Emergency Contact Name',
            'Emergency Contact Phone'
        ]);
        fputcsv($fh, [
            'Olumide Jacobs',
            'olumide.jacobs@example.com',
            '+2348031112233',
            'Bello Crest Heights, Lekki',
            'Lekki',
            'Lagos',
            'Unit C3',
            '3-Bedroom Luxury Apartment',
            '3',
            '4500000',
            date('Y-m-d', strtotime('+1 year')),
            'Kemi Jacobs',
            '+2348039998877'
        ]);
        fputcsv($fh, [
            'Halima Danladi',
            'halima.danladi@example.com',
            '+2348023334455',
            'PHDL Unity Estate, Idu',
            'Abuja',
            'FCT',
            'Unit 5A',
            '2-Bedroom Apartment',
            '2',
            '2500000',
            date('Y-m-d', strtotime('+1 year')),
            'Aliyu Danladi',
            '+2348028887766'
        ]);
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv ?: '';
    }

    /**
     * Downloadable template CSV for Landlords
     */
    public function getLandlordsCsvTemplate(): string
    {
        $fh = fopen('php://memory', 'r+');
        fputcsv($fh, [
            'Full Name',
            'Email Address',
            'Phone Number',
            'Property Title',
            'Address',
            'City',
            'State'
        ]);
        fputcsv($fh, [
            'Senator David Adeleke',
            'senator.adeleke@ogalandlord.ng',
            '+2348035556677',
            'Adeleke Royal Enclave, Ikoyi',
            '10 Bourdillon Road',
            'Ikoyi',
            'Lagos'
        ]);
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv ?: '';
    }

    /**
     * Batch import tenants from uploaded CSV string.
     */
    public function importTenantsFromCsv(string $csvContent): array
    {
        $pdo = $this->db->getPdo();
        $lines = preg_split("/\r\n|\n|\r/", trim($csvContent));
        if (empty($lines)) {
            return ['success' => false, 'error' => 'The uploaded CSV file is empty.'];
        }

        $header = str_getcsv(array_shift($lines));
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        $pdo->beginTransaction();
        try {
            foreach ($lines as $lineIndex => $line) {
                if (trim($line) === '') continue;
                $row = str_getcsv($line);
                if (count($row) < 4) {
                    $skippedCount++;
                    continue;
                }

                $fullName = trim($row[0] ?? '');
                $email = strtolower(trim($row[1] ?? ''));
                $phone = trim($row[2] ?? '');
                $propTitle = trim($row[3] ?? 'PHDL Unity Estate, Idu');
                $city = trim($row[4] ?? 'Abuja');
                $state = trim($row[5] ?? 'FCT');
                $unitNumber = trim($row[6] ?? 'Unit ' . ($lineIndex + 1));
                $apartmentType = trim($row[7] ?? '2-Bedroom Apartment');
                $roomsCount = (int)($row[8] ?? 2) ?: 2;
                $rentAmount = (float)($row[9] ?? 2500000.0);
                $rentDueDate = trim($row[10] ?? '') ?: date('Y-m-d', strtotime('+1 year'));
                $emergencyName = trim($row[11] ?? 'Family Contact');
                $emergencyPhone = trim($row[12] ?? $phone);

                if (empty($fullName) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skippedCount++;
                    $errors[] = "Row " . ($lineIndex + 2) . ": Invalid name or email address ($email).";
                    continue;
                }

                // 1. Resolve or create property
                $pStmt = $pdo->prepare("SELECT id FROM properties WHERE title = ? LIMIT 1");
                $pStmt->execute([$propTitle]);
                $prop = $pStmt->fetch();
                if ($prop) {
                    $propertyId = (int)$prop['id'];
                } else {
                    $pIns = $pdo->prepare("INSERT INTO properties (uuid, landlord_id, title, address_line_1, city, state, country)
                                           VALUES (?, 2, ?, ?, ?, ?, 'Nigeria')");
                    $pIns->execute([
                        Uuid::uuid4()->toString(),
                        $propTitle,
                        "Plot 1, $city Road",
                        $city,
                        $state
                    ]);
                    $propertyId = (int)$pdo->lastInsertId();
                }

                // 2. Resolve or create unit
                $uStmt = $pdo->prepare("SELECT id FROM units WHERE property_id = ? AND unit_number = ? LIMIT 1");
                $uStmt->execute([$propertyId, $unitNumber]);
                $existingUnit = $uStmt->fetch();
                if ($existingUnit) {
                    $unitId = (int)$existingUnit['id'];
                    $pdo->prepare("UPDATE units SET apartment_type = ?, rooms_count = ?, is_occupied = 1 WHERE id = ?")
                        ->execute([$apartmentType, $roomsCount, $unitId]);
                } else {
                    $uIns = $pdo->prepare("INSERT INTO units (uuid, property_id, unit_number, apartment_type, rooms_count, default_rent_amount, currency, is_occupied)
                                           VALUES (?, ?, ?, ?, ?, ?, 'NGN', 1)");
                    $uIns->execute([
                        Uuid::uuid4()->toString(),
                        $propertyId,
                        $unitNumber,
                        $apartmentType,
                        $roomsCount,
                        $rentAmount
                    ]);
                    $unitId = (int)$pdo->lastInsertId();
                }

                // 3. Resolve or create tenant user
                $userStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $userStmt->execute([$email]);
                $userRow = $userStmt->fetch();
                if ($userRow) {
                    $tenantId = (int)$userRow['id'];
                } else {
                    $userIns = $pdo->prepare("INSERT INTO users (uuid, full_name, email, phone_number, role) VALUES (?, ?, ?, ?, 'TENANT')");
                    $userIns->execute([
                        Uuid::uuid4()->toString(),
                        $fullName,
                        $email,
                        $phone
                    ]);
                    $tenantId = (int)$pdo->lastInsertId();
                }

                // 4. Check if tenant already has a lease on this unit (Duplicate Detection)
                $lCheck = $pdo->prepare("SELECT id FROM leases WHERE tenant_id = ? AND unit_id = ? LIMIT 1");
                $lCheck->execute([$tenantId, $unitId]);
                if ($lCheck->fetch()) {
                    $skippedCount++;
                    continue;
                }

                // 5. Create active/pending lease
                $leaseUuid = Uuid::uuid4()->toString();
                $lIns = $pdo->prepare("INSERT INTO leases (
                    uuid, unit_id, tenant_id, agreement_mode, agreement_status,
                    rent_amount, currency, rent_start_date, rent_due_date,
                    emergency_contact_name, emergency_contact_relationship, emergency_contact_phone
                ) VALUES (
                    ?, ?, ?, 'GENERATE_AND_SIGN', 'WAITING_FOR_TENANT_SIGNATURE',
                    ?, 'NGN', ?, ?,
                    ?, 'Emergency Contact', ?
                )");
                $lIns->execute([
                    $leaseUuid,
                    $unitId,
                    $tenantId,
                    $rentAmount,
                    date('Y-m-d'),
                    $rentDueDate,
                    $emergencyName,
                    $emergencyPhone
                ]);

                $importedCount++;
            }

            $pdo->commit();

            return [
                'success' => true,
                'imported_count' => $importedCount,
                'inserted' => $importedCount,
                'skipped_count' => $skippedCount,
                'skipped_duplicates' => $skippedCount,
                'errors' => $errors,
                'message' => "Successfully imported $importedCount tenants. $skippedCount skipped."
            ];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return [
                'success' => false,
                'error' => 'Database error importing tenants: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Batch import landlords from uploaded CSV string.
     */
    public function importLandlordsFromCsv(string $csvContent): array
    {
        $pdo = $this->db->getPdo();
        $lines = preg_split("/\r\n|\n|\r/", trim($csvContent));
        if (empty($lines)) {
            return ['success' => false, 'error' => 'The uploaded CSV file is empty.'];
        }

        array_shift($lines); // skip header
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        $pdo->beginTransaction();
        try {
            foreach ($lines as $lineIndex => $line) {
                if (trim($line) === '') continue;
                $row = str_getcsv($line);
                if (count($row) < 3) {
                    $skippedCount++;
                    continue;
                }

                $fullName = trim($row[0] ?? '');
                $email = strtolower(trim($row[1] ?? ''));
                $phone = trim($row[2] ?? '');
                $propTitle = trim($row[3] ?? '');
                $address = trim($row[4] ?? 'Commercial District');
                $city = trim($row[5] ?? 'Abuja');
                $state = trim($row[6] ?? 'FCT');

                if (empty($fullName) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skippedCount++;
                    $errors[] = "Row " . ($lineIndex + 2) . ": Invalid landlord name or email ($email).";
                    continue;
                }

                // Check or create user
                $uStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $uStmt->execute([$email]);
                $userRow = $uStmt->fetch();

                if ($userRow) {
                    $landlordId = (int)$userRow['id'];
                } else {
                    $ins = $pdo->prepare("INSERT INTO users (uuid, full_name, email, phone_number, role, password_hash) 
                                          VALUES (?, ?, ?, ?, 'LANDLORD', ?)");
                    $ins->execute([
                        Uuid::uuid4()->toString(),
                        $fullName,
                        $email,
                        $phone,
                        password_hash('password123', PASSWORD_BCRYPT)
                    ]);
                    $landlordId = (int)$pdo->lastInsertId();
                }

                // If property title is provided, create property
                if (!empty($propTitle)) {
                    $pCheck = $pdo->prepare("SELECT id FROM properties WHERE title = ? AND landlord_id = ? LIMIT 1");
                    $pCheck->execute([$propTitle, $landlordId]);
                    if (!$pCheck->fetch()) {
                        $pIns = $pdo->prepare("INSERT INTO properties (uuid, landlord_id, title, address_line_1, city, state, country)
                                               VALUES (?, ?, ?, ?, ?, ?, 'Nigeria')");
                        $pIns->execute([
                            Uuid::uuid4()->toString(),
                            $landlordId,
                            $propTitle,
                            $address,
                            $city,
                            $state
                        ]);
                    }
                }

                $importedCount++;
            }

            $pdo->commit();

            return [
                'success' => true,
                'imported_count' => $importedCount,
                'inserted' => $importedCount,
                'skipped_count' => $skippedCount,
                'skipped_duplicates' => $skippedCount,
                'errors' => $errors,
                'message' => "Successfully imported $importedCount landlords. $skippedCount skipped."
            ];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            return [
                'success' => false,
                'error' => 'Database error importing landlords: ' . $e->getMessage()
            ];
        }
    }

    public function updateLandlordProfile(int|array $landlordIdOrData, array $data = []): array
    {
        $pdo = $this->db->getPdo();
        if (is_array($landlordIdOrData)) {
            $data = $landlordIdOrData;
            $landlordId = (int)($data['id'] ?? $data['landlord_id'] ?? 0);
        } else {
            $landlordId = $landlordIdOrData;
        }

        $fullName = trim($data['full_name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $phone = trim($data['phone_number'] ?? '');
        $company = trim($data['company_name'] ?? '');
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        $adminNotes = trim($data['admin_notes'] ?? '');
        $ratePerUnit = isset($data['rate_per_unit']) ? (float)$data['rate_per_unit'] : null;

        if (empty($fullName) || empty($email) || empty($phone)) {
            throw new \InvalidArgumentException("Landlord full name, email, and phone number are required.");
        }

        $stmt = $pdo->prepare("
            UPDATE users SET
                full_name = :name,
                email = :email,
                phone_number = :phone,
                company_name = :company,
                is_active = :active,
                rate_per_unit = COALESCE(:rate, rate_per_unit),
                admin_notes = :notes,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND role = 'LANDLORD'
        ");
        $stmt->execute([
            'name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'company' => $company ?: null,
            'active' => $isActive,
            'rate' => $ratePerUnit,
            'notes' => $adminNotes ?: null,
            'id' => $landlordId,
        ]);

        if ($ratePerUnit !== null) {
            $subStmt = $pdo->prepare("UPDATE saas_subscriptions SET rate_per_unit = :rate WHERE landlord_id = :lid");
            $subStmt->execute(['rate' => $ratePerUnit, 'lid' => $landlordId]);
        }

        return [
            'success' => true,
            'message' => "Landlord profile for {$fullName} successfully updated by SuperAdmin."
        ];
    }

    public function updateTenantKyc(int $tenantId, string $status, ?string $nin = null, ?string $bvn = null, ?string $notes = null): array
    {
        $pdo = $this->db->getPdo();
        $validStatus = in_array(strtoupper($status), ['VERIFIED', 'PENDING', 'REJECTED', 'FLAGGED'], true)
            ? strtoupper($status)
            : 'VERIFIED';

        $stmt = $pdo->prepare("
            UPDATE users SET
                kyc_status = :status,
                kyc_nin = COALESCE(:nin, kyc_nin),
                id_number = COALESCE(:nin, id_number),
                kyc_bvn = COALESCE(:bvn, kyc_bvn),
                admin_notes = COALESCE(:notes, admin_notes),
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id AND role = 'TENANT'
        ");
        $stmt->execute([
            'status' => $validStatus,
            'nin' => $nin,
            'bvn' => $bvn,
            'notes' => $notes,
            'id' => $tenantId,
        ]);

        return [
            'success' => true,
            'kyc_status' => $validStatus,
            'message' => "Tenant KYC status updated to {$validStatus}."
        ];
    }

    public function getEstateDeepOperations(int $propertyId): array
    {
        $pdo = $this->db->getPdo();

        // 1. Estate Header
        $pStmt = $pdo->prepare("
            SELECT p.*, u.full_name AS landlord_name, u.email AS landlord_email, u.phone_number AS landlord_phone
            FROM properties p
            JOIN users u ON p.landlord_id = u.id
            WHERE p.id = :pid
        ");
        $pStmt->execute(['pid' => $propertyId]);
        $estate = $pStmt->fetch(PDO::FETCH_ASSOC);

        if (!$estate) {
            throw new \RuntimeException("Estate not found.");
        }

        // 2. Units & Leases
        $uStmt = $pdo->prepare("
            SELECT u.*, l.rent_amount, l.agreement_status, l.rent_due_date,
                   t.full_name AS tenant_name, t.email AS tenant_email, t.phone_number AS tenant_phone, t.kyc_status
            FROM units u
            LEFT JOIN leases l ON u.id = l.unit_id AND l.agreement_status != 'TERMINATED'
            LEFT JOIN users t ON l.tenant_id = t.id
            WHERE u.property_id = :pid
            ORDER BY u.unit_number ASC
        ");
        $uStmt->execute(['pid' => $propertyId]);
        $units = $uStmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Caretaker
        $cStmt = $pdo->prepare("
            SELECT u.*, a.can_manage_tickets, a.can_view_finances
            FROM property_caretaker_assignments a
            JOIN users u ON a.caretaker_id = u.id
            WHERE a.property_id = :pid
            LIMIT 1
        ");
        $cStmt->execute(['pid' => $propertyId]);
        $caretaker = $cStmt->fetch(PDO::FETCH_ASSOC);

        // 4. Tickets
        $tStmt = $pdo->prepare("
            SELECT t.*, u.unit_number, tn.full_name AS tenant_name, art.full_name AS artisan_name, art.trade_skill
            FROM maintenance_tickets t
            JOIN units u ON t.unit_id = u.id
            JOIN users tn ON t.tenant_id = tn.id
            LEFT JOIN artisans art ON t.artisan_id = art.id
            WHERE t.property_id = :pid
            ORDER BY t.created_at DESC
        ");
        $tStmt->execute(['pid' => $propertyId]);
        $tickets = $tStmt->fetchAll(PDO::FETCH_ASSOC);

        // 5. Expenses
        $eStmt = $pdo->prepare("
            SELECT e.*, a.full_name AS artisan_name
            FROM property_expenses e
            LEFT JOIN artisans a ON e.artisan_id = a.id
            WHERE e.property_id = :pid
            ORDER BY e.expense_date DESC
        ");
        $eStmt->execute(['pid' => $propertyId]);
        $expenses = $eStmt->fetchAll(PDO::FETCH_ASSOC);

        // 6. Facility Bookings
        $fStmt = $pdo->prepare("
            SELECT b.*, t.full_name AS tenant_name
            FROM facility_bookings b
            JOIN users t ON b.tenant_id = t.id
            WHERE b.property_id = :pid
            ORDER BY b.booking_date DESC
        ");
        $fStmt->execute(['pid' => $propertyId]);
        $bookings = $fStmt->fetchAll(PDO::FETCH_ASSOC);

        // 7. Notice Board
        $nStmt = $pdo->prepare("
            SELECT * FROM community_announcements
            WHERE property_id = :pid OR property_id IS NULL
            ORDER BY created_at DESC
        ");
        $nStmt->execute(['pid' => $propertyId]);
        $announcements = $nStmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'property' => $estate,
            'estate' => $estate,
            'caretaker' => $caretaker,
            'units' => $units,
            'tickets' => $tickets,
            'expenses' => $expenses,
            'facility_bookings' => $bookings,
            'bookings' => $bookings,
            'announcements' => $announcements,
        ];
    }
}
