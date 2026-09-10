<?php
declare(strict_types=1);

namespace App\Domain\Artisan\Services;

use App\Infrastructure\Database\Connection;
use PDO;
use Ramsey\Uuid\Uuid;

class ArtisanService
{
    public function __construct(private readonly Connection $db) {}

    public function listArtisans(?int $landlordId = null, ?string $tradeSkill = null): array
    {
        $pdo = $this->db->getPdo();
        $sql = "SELECT * FROM artisans WHERE 1=1";
        $params = [];

        if ($tradeSkill !== null && $tradeSkill !== '' && $tradeSkill !== 'ALL') {
            $sql .= " AND trade_skill = :skill";
            $params['skill'] = $tradeSkill;
        }

        $sql .= " ORDER BY rating DESC, jobs_completed DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createArtisan(array $data): array
    {
        $pdo = $this->db->getPdo();
        $name = trim($data['full_name'] ?? '');
        $phone = trim($data['phone_number'] ?? '');
        $email = trim($data['email'] ?? '');
        $skill = strtoupper(trim($data['trade_skill'] ?? 'PLUMBING'));
        $rate = (float)($data['hourly_rate'] ?? 5000.00);
        $city = trim($data['city'] ?? 'Abuja');
        $state = trim($data['state'] ?? 'FCT');
        $landlordId = isset($data['landlord_id']) ? (int)$data['landlord_id'] : null;
        $propertyId = isset($data['property_id']) ? (int)$data['property_id'] : null;

        if (empty($name) || empty($phone)) {
            throw new \InvalidArgumentException("Artisan full name and phone number are required.");
        }

        $uuid = Uuid::uuid4()->toString();
        $stmt = $pdo->prepare("
            INSERT INTO artisans (
                uuid, landlord_id, property_id, full_name, phone_number, email,
                trade_skill, rating, jobs_completed, hourly_rate, is_verified, is_available,
                city, state
            ) VALUES (
                :u, :lid, :pid, :name, :phone, :email,
                :skill, 5.0, 0, :rate, 1, 1,
                :city, :state
            )
        ");
        $stmt->execute([
            'u' => $uuid,
            'lid' => $landlordId,
            'pid' => $propertyId,
            'name' => $name,
            'phone' => $phone,
            'email' => $email ?: null,
            'skill' => $skill,
            'rate' => $rate,
            'city' => $city,
            'state' => $state,
        ]);

        $newId = (int)$pdo->lastInsertId();
        return [
            'success' => true,
            'message' => "Artisan {$name} added successfully to your trusted network.",
            'artisan_id' => $newId
        ];
    }

    public function assignToTicket(int $ticketId, int $artisanId, ?float $estimatedCost = null, ?string $notes = null): array
    {
        $pdo = $this->db->getPdo();

        // 1. Get Artisan
        $aStmt = $pdo->prepare("SELECT * FROM artisans WHERE id = :aid");
        $aStmt->execute(['aid' => $artisanId]);
        $artisan = $aStmt->fetch(PDO::FETCH_ASSOC);
        if (!$artisan) {
            throw new \RuntimeException("Artisan not found.");
        }

        // 2. Update Ticket
        $upStmt = $pdo->prepare("
            UPDATE maintenance_tickets SET
                artisan_id = :aid,
                assigned_vendor_name = :aname,
                assigned_vendor_phone = :aphone,
                estimated_cost = :cost,
                status = 'TECHNICIAN_DISPATCHED',
                work_status = 'ARTISAN_DISPATCHED',
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :tid
        ");
        $upStmt->execute([
            'aid' => $artisanId,
            'aname' => $artisan['full_name'],
            'aphone' => $artisan['phone_number'],
            'cost' => $estimatedCost,
            'tid' => $ticketId,
        ]);

        // 3. Log a system ticket message
        $tStmt = $pdo->prepare("
            INSERT INTO ticket_messages (
                ticket_id, sender_id, sender_role, sender_name, message, is_internal_note
            ) VALUES (
                :tid, 1, 'SYSTEM', 'Oga Landlord Dispatcher', :msg, 0
            )
        ");
        $msg = "🛠️ Work Request Assigned to Artisan: {$artisan['full_name']} ({$artisan['trade_skill']}). Phone: {$artisan['phone_number']}. Estimated: ₦" . number_format($estimatedCost ?? 0, 2);
        if ($notes) {
            $msg .= " — Instructions: {$notes}";
        }
        $tStmt->execute([
            'tid' => $ticketId,
            'msg' => $msg,
        ]);

        return [
            'success' => true,
            'message' => "Artisan {$artisan['full_name']} successfully dispatched to ticket #{$ticketId}.",
            'artisan' => $artisan
        ];
    }

    public function updateTicketWorkStatus(int $ticketId, string $status, ?float $actualCost = null): array
    {
        $pdo = $this->db->getPdo();

        $mainStatus = match($status) {
            'RESOLVED', 'COMPLETED' => 'COMPLETED',
            'IN_PROGRESS', 'WORKING' => 'IN_REVIEW',
            'ARTISAN_DISPATCHED' => 'TECHNICIAN_DISPATCHED',
            default => 'REPORTED',
        };

        $upStmt = $pdo->prepare("
            UPDATE maintenance_tickets SET
                work_status = :wstatus,
                status = :mstatus,
                actual_cost = COALESCE(:cost, actual_cost),
                resolved_at = CASE WHEN :wstatus = 'RESOLVED' THEN CURRENT_TIMESTAMP ELSE resolved_at END,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :tid
        ");
        $upStmt->execute([
            'wstatus' => $status,
            'mstatus' => $mainStatus,
            'cost' => $actualCost,
            'tid' => $ticketId,
        ]);

        // If completed and artisan was assigned, increment their jobs_completed
        if ($status === 'RESOLVED' || $status === 'COMPLETED') {
            $t = $pdo->query("SELECT artisan_id, property_id, unit_id, title FROM maintenance_tickets WHERE id = {$ticketId}")->fetch(PDO::FETCH_ASSOC);
            if ($t && !empty($t['artisan_id'])) {
                $pdo->exec("UPDATE artisans SET jobs_completed = jobs_completed + 1 WHERE id = {$t['artisan_id']}");
                
                // Also auto-record into property_expenses if actualCost > 0
                if ($actualCost !== null && $actualCost > 0) {
                    $expStmt = $pdo->prepare("
                        INSERT INTO property_expenses (
                            uuid, property_id, unit_id, recorded_by_id, category, title, description,
                            amount, artisan_id, expense_date
                        ) VALUES (
                            :uuid, :pid, :uid, 1, 'ARTISAN_REPAIR', :title, :desc,
                            :amt, :aid, CURRENT_DATE
                        )
                    ");
                    $expStmt->execute([
                        'uuid' => Uuid::uuid4()->toString(),
                        'pid' => $t['property_id'],
                        'uid' => $t['unit_id'],
                        'title' => "Resolved Ticket: {$t['title']}",
                        'desc' => "Artisan settlement payout upon job verification",
                        'amt' => $actualCost,
                        'aid' => $t['artisan_id'],
                    ]);
                }
            }
        }

        return [
            'success' => true,
            'work_status' => $status,
            'message' => "Ticket #{$ticketId} work status updated to {$status}."
        ];
    }
}
