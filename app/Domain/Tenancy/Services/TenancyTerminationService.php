<?php
declare(strict_types=1);

namespace App\Domain\Tenancy\Services;

use App\Infrastructure\Database\Connection;
use DateTimeImmutable;
use PDO;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class TenancyTerminationService
{
    public function __construct(private readonly Connection $db) {}

    /**
     * Terminate an active tenancy, record duration of stay history, and vacate the unit for reassignment.
     */
    public function terminateTenancy(int $leaseId, int $terminatedByUserId, string $reason = 'End of Lease Term / Notice to Vacate'): array
    {
        $pdo = $this->db->getPdo();

        $stmt = $pdo->prepare("SELECT l.*, u.id as tenant_id, u.full_name as tenant_name, 
                                      u.email as tenant_email, u.phone_number as tenant_phone,
                                      un.id as unit_id, un.unit_number, un.apartment_type,
                                      p.id as property_id, p.title as property_title
                               FROM leases l
                               JOIN users u ON l.tenant_id = u.id
                               JOIN units un ON l.unit_id = un.id
                               JOIN properties p ON un.property_id = p.id
                               WHERE l.id = :id LIMIT 1");
        $stmt->execute(['id' => $leaseId]);
        $lease = $stmt->fetch();

        if (!$lease) {
            throw new RuntimeException("Lease record not found.");
        }

        // Calculate exact duration of stay
        $startDateStr = $lease['rent_start_date'] ?: date('Y-m-d');
        $start = new DateTimeImmutable($startDateStr);
        $today = new DateTimeImmutable();
        $interval = $start->diff($today);

        $parts = [];
        if ($interval->y > 0) $parts[] = $interval->y . ' ' . ($interval->y === 1 ? 'year' : 'years');
        if ($interval->m > 0) $parts[] = $interval->m . ' ' . ($interval->m === 1 ? 'month' : 'months');
        if ($interval->d > 0 || empty($parts)) $parts[] = $interval->d . ' ' . ($interval->d === 1 ? 'day' : 'days');

        $totalDays = $interval->days ?? 0;
        $durationOfStay = implode(', ', $parts) . " ({$totalDays} days total: {$startDateStr} to " . $today->format('Y-m-d') . ")";

        $now = date('Y-m-d H:i:s');
        $historyUuid = Uuid::uuid4()->toString();

        $pdo->beginTransaction();
        try {
            // 1. Insert immutable record into tenancy_history
            $hStmt = $pdo->prepare("INSERT INTO tenancy_history (
                uuid, property_id, unit_id, tenant_id, lease_id,
                tenant_name, tenant_email, tenant_phone,
                rent_amount, currency, rent_start_date, terminated_at,
                duration_of_stay, termination_reason, terminated_by_user_id
            ) VALUES (
                :uuid, :pid, :uid, :tid, :lid,
                :name, :email, :phone,
                :rent, :curr, :sdate, :tdate,
                :duration, :reason, :by_user
            )");

            $hStmt->execute([
                'uuid'     => $historyUuid,
                'pid'      => $lease['property_id'],
                'uid'      => $lease['unit_id'],
                'tid'      => $lease['tenant_id'],
                'lid'      => $leaseId,
                'name'     => $lease['tenant_name'],
                'email'    => $lease['tenant_email'],
                'phone'    => $lease['tenant_phone'],
                'rent'     => $lease['rent_amount'],
                'curr'     => $lease['currency'] ?? 'NGN',
                'sdate'    => $startDateStr,
                'tdate'    => $now,
                'duration' => $durationOfStay,
                'reason'   => trim($reason),
                'by_user'  => $terminatedByUserId,
            ]);

            $historyId = (int) $pdo->lastInsertId();

            // 2. Mark lease as TERMINATED
            $lUp = $pdo->prepare("UPDATE leases SET 
                agreement_status = 'TERMINATED',
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :lid");
            $lUp->execute(['lid' => $leaseId]);

            // 3. Mark unit as vacant for reassignment
            $uUp = $pdo->prepare("UPDATE units SET 
                is_occupied = 0,
                updated_at = CURRENT_TIMESTAMP
                WHERE id = :uid");
            $uUp->execute(['uid' => $lease['unit_id']]);

            $pdo->commit();

            return [
                'success'          => true,
                'history_id'       => $historyId,
                'unit_number'      => $lease['unit_number'],
                'tenant_name'      => $lease['tenant_name'],
                'duration_of_stay' => $durationOfStay,
                'message'          => "Tenancy terminated successfully. {$lease['tenant_name']} has been archived into former resident history, and {$lease['unit_number']} is now vacant and ready for new reassignment.",
            ];
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Retrieve complete history of former tenants for a property or entire landlord portfolio.
     */
    public function getFormerTenantsHistory(?int $propertyId = null, ?int $landlordId = null): array
    {
        $pdo = $this->db->getPdo();

        $sql = "SELECT th.*, un.unit_number, un.apartment_type,
                       p.title as property_title, p.city, p.state, p.landlord_id,
                       u.full_name as terminated_by_name
                FROM tenancy_history th
                JOIN units un ON th.unit_id = un.id
                JOIN properties p ON th.property_id = p.id
                LEFT JOIN users u ON th.terminated_by_user_id = u.id
                WHERE 1 = 1";

        $params = [];
        if ($propertyId !== null) {
            $sql .= " AND th.property_id = :pid";
            $params['pid'] = $propertyId;
        }
        if ($landlordId !== null) {
            $sql .= " AND p.landlord_id = :lid";
            $params['lid'] = $landlordId;
        }

        $sql .= " ORDER BY th.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
