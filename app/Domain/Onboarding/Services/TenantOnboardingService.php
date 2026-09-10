<?php
declare(strict_types=1);

namespace App\Domain\Onboarding\Services;

use App\Domain\Agreement\Enums\AgreementMode;
use App\Domain\Agreement\Enums\AgreementStatus;
use App\Domain\Agreement\Services\AgreementCompilerService;
use App\Domain\Agreement\Services\SigningTokenService;
use App\Domain\Onboarding\DTOs\TenantOnboardingDTO;
use App\Infrastructure\Database\Connection;
use PDO;
use Ramsey\Uuid\Uuid;

class TenantOnboardingService
{
    public function __construct(
        private readonly Connection $db,
        private readonly AgreementCompilerService $compiler,
        private readonly SigningTokenService $tokenService
    ) {}

    public function onboard(TenantOnboardingDTO $dto): array
    {
        $pdo = $this->db->getPdo();
        $pdo->beginTransaction();

        try {
            // Resolve or insert tenant
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $dto->tenantEmail]);
            $existing = $stmt->fetch();

            if ($existing) {
                $tenantId = (int) $existing['id'];
            } else {
                $uuid = Uuid::uuid4()->toString();
                $passHash = password_hash('password123', PASSWORD_BCRYPT);
                $ins = $pdo->prepare("INSERT INTO users (uuid, full_name, email, phone_number, password_hash, role) VALUES (:u, :n, :e, :p, :pwd, 'TENANT')");
                $ins->execute([
                    'u'   => $uuid,
                    'n'   => $dto->tenantFullName,
                    'e'   => $dto->tenantEmail,
                    'p'   => $dto->tenantPhone,
                    'pwd' => $passHash
                ]);
                $tenantId = (int) $pdo->lastInsertId();
            }

            // 1. Resolve or Create Property (Multi-Location Support)
            $propertyId = $dto->propertyId;
            if (!empty($dto->newPropertyTitle)) {
                $landlordId = $dto->landlordId ?: 2;
                $pCheck = $pdo->prepare("SELECT id FROM properties WHERE title = ? AND landlord_id = ? LIMIT 1");
                $pCheck->execute([$dto->newPropertyTitle, $landlordId]);
                $existingProp = $pCheck->fetch();
                if ($existingProp) {
                    $propertyId = (int)$existingProp['id'];
                } else {
                    $propUuid = Uuid::uuid4()->toString();
                    $pStmt = $pdo->prepare("INSERT INTO properties (uuid, landlord_id, title, address_line_1, city, state, country) VALUES (?, ?, ?, ?, ?, ?, 'Nigeria')");
                    $pStmt->execute([
                        $propUuid,
                        $landlordId,
                        $dto->newPropertyTitle,
                        $dto->newPropertyAddress ?? 'Prime Commercial/Residential Corridor',
                        $dto->newPropertyCity ?? 'Abuja',
                        $dto->newPropertyState ?? 'FCT'
                    ]);
                    $propertyId = (int) $pdo->lastInsertId();

                    // Assign caretaker Musa Danjuma (ID 3) by default if exists
                    $cStmt = $pdo->prepare("INSERT OR IGNORE INTO property_caretaker_assignments (property_id, caretaker_id, can_manage_tickets, can_view_finances) VALUES (?, 3, 1, 1)");
                    $cStmt->execute([$propertyId]);
                }
            }

            if (empty($propertyId)) {
                $propertyId = (int) ($pdo->query("SELECT id FROM properties ORDER BY id ASC LIMIT 1")->fetchColumn() ?: 1);
            }

            // 2. Resolve or Create Unit with Apartment Type and Rooms Count
            $unitId = $dto->unitId;
            $apartmentType = $dto->apartmentType ?: '2-Bedroom Apartment';
            $roomsCount = $dto->roomsCount ?: 2;
            $unitNumber = $dto->unitNumber ?: 'Unit ' . rand(1, 99) . 'A';

            if ($unitId > 0) {
                // Update apartment type and rooms count if specified
                $updUnit = $pdo->prepare("UPDATE units SET apartment_type = :at, rooms_count = :rc, is_occupied = 1 WHERE id = :id");
                $updUnit->execute(['at' => $apartmentType, 'rc' => $roomsCount, 'id' => $unitId]);
            } else {
                // Check if unit number exists in this property
                $checkUnit = $pdo->prepare("SELECT id FROM units WHERE property_id = ? AND unit_number = ? LIMIT 1");
                $checkUnit->execute([$propertyId, $unitNumber]);
                $existingUnit = $checkUnit->fetch();

                if ($existingUnit) {
                    $unitId = (int) $existingUnit['id'];
                    $updUnit = $pdo->prepare("UPDATE units SET apartment_type = :at, rooms_count = :rc, is_occupied = 1 WHERE id = :id");
                    $updUnit->execute(['at' => $apartmentType, 'rc' => $roomsCount, 'id' => $unitId]);
                } else {
                    $unitUuid = Uuid::uuid4()->toString();
                    $insUnit = $pdo->prepare("INSERT INTO units (uuid, property_id, unit_number, apartment_type, rooms_count, default_rent_amount, currency, is_occupied)
                                             VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
                    $insUnit->execute([
                        $unitUuid,
                        $propertyId,
                        $unitNumber,
                        $apartmentType,
                        $roomsCount,
                        $dto->rentAmount,
                        $dto->currency
                    ]);
                    $unitId = (int) $pdo->lastInsertId();
                }
            }

            $leaseUuid = Uuid::uuid4()->toString();
            $initialStatus = match ($dto->agreementMode) {
                AgreementMode::GENERATE_AND_SIGN => AgreementStatus::WAITING_FOR_TENANT_SIGNATURE,
                AgreementMode::UPLOAD_EXISTING   => AgreementStatus::SIGNED_OFFLINE,
                AgreementMode::SKIP_AGREEMENT    => AgreementStatus::ACTIVE_WITHOUT_CONTRACT,
            };

            $tokenPlain = null;
            $tokenHash = null;
            $tokenExp = null;
            $pdfPath = null;

            if ($dto->agreementMode === AgreementMode::GENERATE_AND_SIGN) {
                $tokenData = $this->tokenService->generateToken($leaseUuid);
                $tokenPlain = $tokenData['plain_token'];
                $tokenHash = $tokenData['token_hash'];
                $tokenExp = $tokenData['expires_at'];
                $pdfPath = $this->compiler->compileDraftAgreement([
                    'lease_uuid'  => $leaseUuid,
                    'tenant_name' => $dto->tenantFullName
                ]);
            }

            $sql = "INSERT INTO leases (
                uuid, unit_id, tenant_id, agreement_mode, agreement_status,
                rent_amount, currency, rent_start_date, rent_due_date,
                emergency_contact_name, emergency_contact_relationship, emergency_contact_phone,
                signing_token_hash, signing_token_expires_at, generated_pdf_path, uploaded_agreement_path
            ) VALUES (
                :uuid, :unit_id, :tenant_id, :mode, :status,
                :rent, :curr, :start, :due,
                :ename, :erel, :ephone,
                :thash, :texp, :pdf, :upld
            )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'uuid'    => $leaseUuid,
                'unit_id' => $unitId,
                'tenant_id' => $tenantId,
                'mode'    => $dto->agreementMode->value,
                'status'  => $initialStatus->value,
                'rent'    => $dto->rentAmount,
                'curr'    => $dto->currency,
                'start'   => $dto->rentStartDate,
                'due'     => $dto->rentDueDate,
                'ename'   => $dto->emergencyName,
                'erel'    => $dto->emergencyRelationship,
                'ephone'  => $dto->emergencyPhone,
                'thash'   => $tokenHash,
                'texp'    => $tokenExp,
                'pdf'     => $pdfPath,
                'upld'    => $dto->uploadedFilePath
            ]);

            $leaseId = (int) $pdo->lastInsertId();
            $pdo->commit();

            $signingUrl = $tokenPlain ? "/sign?token=" . urlencode($tokenPlain) : null;
            $humanMessage = match ($dto->agreementMode) {
                AgreementMode::GENERATE_AND_SIGN => "Tenant added successfully. We sent a signing link to {$dto->tenantFullName}.",
                AgreementMode::UPLOAD_EXISTING   => "Tenant added successfully and physical agreement was archived.",
                AgreementMode::SKIP_AGREEMENT    => "Tenant added successfully and active rent tracking has begun.",
            };

            return [
                'tenant_id'          => $tenantId,
                'lease_id'           => $leaseId,
                'signing_url'        => $signingUrl,
                'portal_login_url'   => '/login?role=tenant',
                'portal_email'       => $dto->tenantEmail,
                'temporary_password' => 'password123',
                'human_message'      => $humanMessage . " Login credentials for the Tenant Portal have been dispatched to {$dto->tenantEmail}."
            ];
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}