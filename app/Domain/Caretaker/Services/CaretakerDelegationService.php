<?php
declare(strict_types=1);

namespace App\Domain\Caretaker\Services;

use App\Infrastructure\Database\Connection;
use PDO;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use InvalidArgumentException;

class CaretakerDelegationService
{
    private Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Get current caretaker delegation status for a property.
     */
    public function getPropertyCaretaker(int $propertyId, ?int $landlordId = null, bool $isSuperAdmin = false): array
    {
        $pdo = $this->db->getPdo();

        $whereClause = ($landlordId === null || $isSuperAdmin) ? "p.id = :pid" : "p.id = :pid AND p.landlord_id = :lid";
        $params = ['pid' => $propertyId];
        if ($landlordId !== null && !$isSuperAdmin) {
            $params['lid'] = $landlordId;
        }

        $stmt = $pdo->prepare("SELECT p.id, p.title, p.address_line_1, p.city, p.state, p.caretaker_delegation_mode,
                                      u.id as caretaker_id, u.full_name as caretaker_name, u.email as caretaker_email, u.phone_number as caretaker_phone,
                                      pca.assigned_at
                               FROM properties p
                               LEFT JOIN property_caretaker_assignments pca ON pca.property_id = p.id
                               LEFT JOIN users u ON pca.caretaker_id = u.id
                               WHERE {$whereClause}
                               LIMIT 1");
        $stmt->execute($params);
        $prop = $stmt->fetch();
        if (!$prop) {
            throw new RuntimeException("Property not found or unauthorized to inspect.");
        }

        return $prop;
    }

    /**
     * Update caretaker delegation: Nominate custom caretaker OR delegate to SuperAdmin concierge.
     */
    public function updateDelegation(int $propertyId, int $landlordId, string $mode, ?array $caretakerData = null, bool $isSuperAdmin = false): array
    {
        $pdo = $this->db->getPdo();

        // 1. Verify Property ownership
        $pStmt = $pdo->prepare("SELECT id, title, city, state FROM properties WHERE id = :pid AND (landlord_id = :lid OR :is_admin = 1) LIMIT 1");
        $pStmt->execute(['pid' => $propertyId, 'lid' => $landlordId, 'is_admin' => $isSuperAdmin ? 1 : 0]);
        $prop = $pStmt->fetch();

        if (!$prop) {
            throw new RuntimeException("Property not found or unauthorized to manage this property.");
        }

        $delegationMode = strtoupper(trim($mode));
        if (!in_array($delegationMode, ['CUSTOM_CARETAKER', 'SUPERADMIN_CONCIERGE'], true)) {
            throw new InvalidArgumentException("Invalid delegation mode. Must be CUSTOM_CARETAKER or SUPERADMIN_CONCIERGE.");
        }

        $pdo->beginTransaction();

        try {
            $caretakerId = null;
            $caretakerName = null;
            $caretakerEmail = null;

            if ($delegationMode === 'CUSTOM_CARETAKER') {
                $cEmail = strtolower(trim($caretakerData['email'] ?? ''));
                $cName = trim($caretakerData['name'] ?? 'Nominated Caretaker');
                $cPhone = trim($caretakerData['phone'] ?? '+2348000000000');

                if (empty($cEmail) || !filter_var($cEmail, FILTER_VALIDATE_EMAIL)) {
                    throw new InvalidArgumentException("A valid caretaker email address is required.");
                }

                // Check if caretaker user exists
                $chk = $pdo->prepare("SELECT id, full_name, email FROM users WHERE email = :email LIMIT 1");
                $chk->execute(['email' => $cEmail]);
                $existing = $chk->fetch();

                if ($existing) {
                    $caretakerId = (int)$existing['id'];
                    $caretakerName = $existing['full_name'];
                    $caretakerEmail = $existing['email'];
                } else {
                    $cUuid = Uuid::uuid4()->toString();
                    $cPass = password_hash('password123', PASSWORD_BCRYPT);
                    $ins = $pdo->prepare("INSERT INTO users (uuid, full_name, email, phone_number, password_hash, role, is_active)
                                          VALUES (:uuid, :name, :email, :phone, :hash, 'CARETAKER', 1)");
                    $ins->execute([
                        'uuid'  => $cUuid,
                        'name'  => $cName,
                        'email' => $cEmail,
                        'phone' => $cPhone,
                        'hash'  => $cPass,
                    ]);
                    $caretakerId = (int)$pdo->lastInsertId();
                    $caretakerName = $cName;
                    $caretakerEmail = $cEmail;
                }

                // Remove previous assignments for this property
                $pdo->prepare("DELETE FROM property_caretaker_assignments WHERE property_id = :pid")->execute(['pid' => $propertyId]);

                // Assign new custom caretaker
                $assignStmt = $pdo->prepare("INSERT INTO property_caretaker_assignments (property_id, caretaker_id, can_manage_tickets, can_view_finances)
                                            VALUES (:pid, :cid, 1, 0)");
                $assignStmt->execute(['pid' => $propertyId, 'cid' => $caretakerId]);

                $message = "Custom caretaker '{$caretakerName}' successfully nominated for {$prop['title']}.";
            } else {
                // SUPERADMIN_CONCIERGE
                $adminUser = $pdo->query("SELECT id, full_name, email FROM users WHERE role = 'SUPERADMIN' LIMIT 1")->fetch();
                $adminId = $adminUser ? (int)$adminUser['id'] : 1;
                $caretakerId = $adminId;
                $caretakerName = $adminUser['full_name'] ?? 'Oga Landlord SuperAdmin Operations Team';
                $caretakerEmail = $adminUser['email'] ?? 'superadmin@ogalandlord.ng';

                // Remove previous assignments
                $pdo->prepare("DELETE FROM property_caretaker_assignments WHERE property_id = :pid")->execute(['pid' => $propertyId]);

                // Assign SuperAdmin concierge
                $assignStmt = $pdo->prepare("INSERT INTO property_caretaker_assignments (property_id, caretaker_id, can_manage_tickets, can_view_finances)
                                            VALUES (:pid, :cid, 1, 1)");
                $assignStmt->execute(['pid' => $propertyId, 'cid' => $adminId]);

                $message = "Oga Landlord SuperAdmin Concierge designated to handle physical maintenance and tenant supervision for {$prop['title']}.";
            }

            // Update property delegation mode
            $upProp = $pdo->prepare("UPDATE properties SET caretaker_delegation_mode = :mode, updated_at = CURRENT_TIMESTAMP WHERE id = :pid");
            $upProp->execute(['mode' => $delegationMode, 'pid' => $propertyId]);

            $pdo->commit();

            return [
                'success'                  => true,
                'mode'                     => $delegationMode,
                'property_id'              => $propertyId,
                'caretaker_delegation_mode'=> $delegationMode,
                'caretaker_id'             => $caretakerId,
                'caretaker_name'           => $caretakerName,
                'caretaker_email'          => $caretakerEmail,
                'message'                  => $message,
            ];
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
