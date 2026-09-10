<?php
declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Infrastructure\Database\Connection;
use PDO;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use InvalidArgumentException;

class LandlordRegistrationService
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Register a new Landlord, list their initial property anywhere in Nigeria,
     * auto-generate units, and configure caretaker delegation.
     */
    public function register(array $data): array
    {
        $pdo = $this->db->getPdo();

        // 1. Validate Landlord Personal Information
        $fullName = trim($data['full_name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $phone = trim($data['phone_number'] ?? '');
        $password = $data['password'] ?? '';
        $passwordConfirm = $data['password_confirmation'] ?? ($data['password_confirm'] ?? $password);

        if (empty($fullName)) {
            throw new InvalidArgumentException("Full name is required.");
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("A valid email address is required.");
        }
        if (empty($phone)) {
            throw new InvalidArgumentException("Phone number is required.");
        }
        if (strlen($password) < 6) {
            throw new InvalidArgumentException("Password must be at least 6 characters long.");
        }
        if ($password !== $passwordConfirm) {
            throw new InvalidArgumentException("Password confirmation does not match.");
        }

        // Check if email already registered
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            throw new InvalidArgumentException("An account with this email address already exists. Please log in.");
        }

        // 2. Normalize and Validate Properties List
        $rawProperties = $data['properties'] ?? null;
        if (!is_array($rawProperties) || empty($rawProperties)) {
            $rawProperties = [$data];
        }

        $propertiesToCreate = [];
        foreach ($rawProperties as $pData) {
            if (!is_array($pData)) continue;
            $propTitle = trim($pData['property_title'] ?? ($pData['title'] ?? ''));
            if (empty($propTitle) && count($rawProperties) === 1) {
                $propTitle = 'My Residential Estate';
            }
            if (empty($propTitle)) {
                continue;
            }

            $address = trim($pData['address_line_1'] ?? ($pData['address'] ?? '1 Property Avenue'));
            $city = trim($pData['city'] ?? 'Abuja');
            $stateRaw = trim($pData['state'] ?? 'Abuja');
            $state = match (strtoupper($stateRaw)) {
                'FCT', 'ABUJA', 'ABUJA FCT' => 'Abuja',
                'LAGOS' => 'Lagos',
                default => $stateRaw
            };
            $unitsCount = max(1, min(100, (int)($pData['units_count'] ?? ($pData['total_units'] ?? 4))));
            $aptType = trim($pData['apartment_type'] ?? '2-Bedroom Apartment');
            $defaultRent = max(100000, (float)($pData['default_rent_amount'] ?? ($pData['default_rent'] ?? 2500000.00)));
            $delegationMode = strtoupper(trim($pData['caretaker_delegation_mode'] ?? ($data['caretaker_delegation_mode'] ?? 'SUPERADMIN_CONCIERGE')));
            if (!in_array($delegationMode, ['CUSTOM_CARETAKER', 'SUPERADMIN_CONCIERGE'], true)) {
                $delegationMode = 'SUPERADMIN_CONCIERGE';
            }

            $caretakerEmail = trim($pData['caretaker_email'] ?? ($pData['nominee_email'] ?? ($data['caretaker_email'] ?? ($data['nominee_email'] ?? ''))));
            $caretakerName = trim($pData['caretaker_name'] ?? ($pData['nominee_name'] ?? ($data['caretaker_name'] ?? ($data['nominee_name'] ?? 'Nominated Caretaker'))));
            $caretakerPhone = trim($pData['caretaker_phone'] ?? ($pData['nominee_phone'] ?? ($data['caretaker_phone'] ?? ($data['nominee_phone'] ?? '+2348000000000'))));

            $roomsCount = 2;
            if (preg_match('/(\d+)\s*-?\s*bed/i', $aptType, $m)) {
                $roomsCount = (int)$m[1];
            } elseif (stripos($aptType, 'self') !== false || stripos($aptType, 'studio') !== false) {
                $roomsCount = 1;
            }

            $propertiesToCreate[] = [
                'title'           => $propTitle,
                'address'         => $address,
                'city'            => $city,
                'state'           => $state,
                'units_count'     => $unitsCount,
                'apt_type'        => $aptType,
                'rooms_count'     => $roomsCount,
                'default_rent'    => $defaultRent,
                'delegation_mode' => $delegationMode,
                'caretaker_email' => $caretakerEmail,
                'caretaker_name'  => $caretakerName,
                'caretaker_phone' => $caretakerPhone,
            ];
        }

        if (empty($propertiesToCreate)) {
            $propertiesToCreate[] = [
                'title'           => 'My Residential Estate',
                'address'         => '1 Property Avenue',
                'city'            => 'Abuja',
                'state'           => 'Abuja',
                'units_count'     => 4,
                'apt_type'        => '2-Bedroom Apartment',
                'rooms_count'     => 2,
                'default_rent'    => 2500000.00,
                'delegation_mode' => 'SUPERADMIN_CONCIERGE',
                'caretaker_email' => '',
                'caretaker_name'  => 'Nominated Caretaker',
                'caretaker_phone' => '+2348000000000',
            ];
        }

        // 2b. Check & Update Estates Directory for Lagos & Abuja precisely for each property
        foreach ($propertiesToCreate as &$propItem) {
            $st = $propItem['state'];
            $pt = $propItem['title'];
            $pc = $propItem['city'];
            $pa = $propItem['address'];
            $estateMetadata = null;

            if (in_array($st, ['Abuja', 'Lagos'], true)) {
                $chkDir = $pdo->prepare("SELECT * FROM estates_directory WHERE LOWER(title) = LOWER(?) AND state = ? LIMIT 1");
                $chkDir->execute([$pt, $st]);
                $estateMetadata = $chkDir->fetch(PDO::FETCH_ASSOC);

                if (!$estateMetadata) {
                    // If inputted estate doesn't exist, add the new estate to the current Database of estates for Lagos & Abuja precisely
                    $insDir = $pdo->prepare("INSERT INTO estates_directory (
                        title, state, district, zone_region, estate_category, market_availability, description, source
                    ) VALUES (?, ?, ?, ?, 'Landlord Registered', 'Sale & Rent', ?, 'LANDLORD_ADDED')");
                    $insDir->execute([
                        $pt,
                        $st,
                        $pc ?: ($st === 'Lagos' ? 'Lagos Island / Mainland' : 'Abuja Phase 1 / Outer'),
                        $pa ?: 'Prime Residential Area',
                        "Community estate registered by {$fullName} on Oga Landlord."
                    ]);

                    $estateMetadata = [
                        'district'            => $pc,
                        'zone_region'         => $pa,
                        'estate_category'     => 'Landlord Registered',
                        'market_availability' => 'Sale & Rent',
                        'description'         => "Community estate registered by {$fullName} on Oga Landlord."
                    ];
                }
            }
            $propItem['metadata'] = $estateMetadata;
        }
        unset($propItem);

        // Begin transaction
        $pdo->beginTransaction();

        try {
            // 3. Create Landlord User
            $landlordUuid = Uuid::uuid4()->toString();
            $passHash = password_hash($password, PASSWORD_BCRYPT);
            $uStmt = $pdo->prepare("INSERT INTO users (uuid, full_name, email, phone_number, password_hash, role, is_active)
                                    VALUES (:uuid, :name, :email, :phone, :hash, 'LANDLORD', 1)");
            $uStmt->execute([
                'uuid'  => $landlordUuid,
                'name'  => $fullName,
                'email' => $email,
                'phone' => $phone,
                'hash'  => $passHash,
            ]);
            $landlordId = (int)$pdo->lastInsertId();

            // Prepare Statements for Properties, Units, and Caretaker Assignments
            $pStmt = $pdo->prepare("INSERT INTO properties (
                uuid, landlord_id, title, address_line_1, city, state, country,
                district, zone_region, estate_category, market_availability, description,
                caretaker_delegation_mode
            ) VALUES (
                :uuid, :lid, :title, :addr, :city, :state, 'Nigeria',
                :district, :zone, :category, :avail, :desc,
                :mode
            )");

            $unitStmt = $pdo->prepare("INSERT INTO units (uuid, property_id, unit_number, apartment_type, rooms_count, default_rent_amount, is_occupied)
                                      VALUES (:uuid, :pid, :unum, :atype, :rcount, :rent, 0)");

            $adminUser = $pdo->query("SELECT id, full_name, email FROM users WHERE role = 'SUPERADMIN' LIMIT 1")->fetch();
            $adminId = $adminUser ? (int)$adminUser['id'] : 1;

            $createdProperties = [];
            $totalUnitsCreated = 0;

            foreach ($propertiesToCreate as $pInfo) {
                $propUuid = Uuid::uuid4()->toString();
                $meta = $pInfo['metadata'] ?? [];

                $pStmt->execute([
                    'uuid'     => $propUuid,
                    'lid'      => $landlordId,
                    'title'    => $pInfo['title'],
                    'addr'     => $pInfo['address'],
                    'city'     => $pInfo['city'],
                    'state'    => $pInfo['state'],
                    'district' => $meta['district'] ?? $pInfo['city'],
                    'zone'     => $meta['zone_region'] ?? $pInfo['address'],
                    'category' => $meta['estate_category'] ?? 'Residential',
                    'avail'    => $meta['market_availability'] ?? 'Sale & Rent',
                    'desc'     => $meta['description'] ?? "Estate listed by {$fullName}",
                    'mode'     => $pInfo['delegation_mode'],
                ]);
                $propertyId = (int)$pdo->lastInsertId();

                // Generate Units
                $uCount = $pInfo['units_count'];
                for ($i = 1; $i <= $uCount; $i++) {
                    $unitUuid = Uuid::uuid4()->toString();
                    $unitLabel = ($uCount <= 10) ? sprintf('Unit %d%s', ceil($i / 2), ($i % 2 === 1 ? 'A' : 'B')) : sprintf('Flat %02d', $i);
                    $unitStmt->execute([
                        'uuid'   => $unitUuid,
                        'pid'    => $propertyId,
                        'unum'   => $unitLabel,
                        'atype'  => $pInfo['apt_type'],
                        'rcount' => $pInfo['rooms_count'],
                        'rent'   => $pInfo['default_rent'],
                    ]);
                }
                $totalUnitsCreated += $uCount;

                // Handle Caretaker Assignment for this property
                $caretakerInfo = null;
                $delegationMode = $pInfo['delegation_mode'];
                $cEmail = strtolower($pInfo['caretaker_email']);

                if ($delegationMode === 'CUSTOM_CARETAKER' && !empty($cEmail)) {
                    $cName = $pInfo['caretaker_name'] ?: 'Nominated Caretaker';
                    $cPhone = $pInfo['caretaker_phone'] ?: '+2348000000000';

                    $chkC = $pdo->prepare("SELECT id, full_name, email FROM users WHERE email = :email LIMIT 1");
                    $chkC->execute(['email' => $cEmail]);
                    $existingCaretaker = $chkC->fetch();

                    if ($existingCaretaker) {
                        $caretakerId = (int)$existingCaretaker['id'];
                        $caretakerInfo = $existingCaretaker;
                    } else {
                        $cUuid = Uuid::uuid4()->toString();
                        $cPass = password_hash('password123', PASSWORD_BCRYPT);
                        $insC = $pdo->prepare("INSERT INTO users (uuid, full_name, email, phone_number, password_hash, role, is_active)
                                               VALUES (:uuid, :name, :email, :phone, :hash, 'CARETAKER', 1)");
                        $insC->execute([
                            'uuid'  => $cUuid,
                            'name'  => $cName,
                            'email' => $cEmail,
                            'phone' => $cPhone,
                            'hash'  => $cPass,
                        ]);
                        $caretakerId = (int)$pdo->lastInsertId();
                        $caretakerInfo = [
                            'id'        => $caretakerId,
                            'full_name' => $cName,
                            'email'     => $cEmail,
                        ];
                    }

                    $bindStmt = $pdo->prepare("INSERT OR REPLACE INTO property_caretaker_assignments (property_id, caretaker_id, can_manage_tickets, can_view_finances)
                                              VALUES (:pid, :cid, 1, 0)");
                    $bindStmt->execute(['pid' => $propertyId, 'cid' => $caretakerId]);
                } else {
                    $bindStmt = $pdo->prepare("INSERT OR REPLACE INTO property_caretaker_assignments (property_id, caretaker_id, can_manage_tickets, can_view_finances)
                                              VALUES (:pid, :cid, 1, 1)");
                    $bindStmt->execute(['pid' => $propertyId, 'cid' => $adminId]);

                    $caretakerInfo = [
                        'id'        => $adminId,
                        'full_name' => $adminUser['full_name'] ?? 'SuperAdmin Concierge',
                        'email'     => $adminUser['email'] ?? 'superadmin@ogalandlord.ng',
                    ];
                }

                $createdProperties[] = [
                    'id'                        => $propertyId,
                    'title'                     => $pInfo['title'],
                    'address_line_1'            => $pInfo['address'],
                    'city'                      => $pInfo['city'],
                    'state'                     => $pInfo['state'],
                    'units_count'               => $uCount,
                    'caretaker_delegation_mode' => $delegationMode,
                    'caretaker'                 => $caretakerInfo,
                ];
            }

            $pdo->commit();

            $firstProp = $createdProperties[0] ?? [];
            return [
                'success'        => true,
                'user_id'        => $landlordId,
                'property_id'    => $firstProp['id'] ?? 0,
                'property_ids'   => array_column($createdProperties, 'id'),
                'units_created'  => $totalUnitsCreated,
                'caretaker_mode' => $firstProp['caretaker_delegation_mode'] ?? 'SUPERADMIN_CONCIERGE',
                'user'           => [
                    'id'           => $landlordId,
                    'uuid'         => $landlordUuid,
                    'full_name'    => $fullName,
                    'email'        => $email,
                    'phone_number' => $phone,
                    'role'         => 'LANDLORD',
                ],
                'property'       => $firstProp,
                'properties'     => $createdProperties,
                'message'        => count($createdProperties) === 1
                    ? "Landlord account created successfully! Your property '{$firstProp['title']}' in {$firstProp['city']}, {$firstProp['state']} has been listed with {$firstProp['units_count']} units."
                    : "Landlord account created successfully! " . count($createdProperties) . " properties onboarded across Nigeria with {$totalUnitsCreated} total units.",
            ];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw new RuntimeException("Registration failed: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
