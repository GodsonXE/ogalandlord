<?php
declare(strict_types=1);

namespace App\Domain\Community\Services;

use App\Infrastructure\Database\Connection;
use PDO;
use Ramsey\Uuid\Uuid;

class CommunityService
{
    public function __construct(private readonly Connection $db) {}

    public function listAnnouncements(?int $propertyId = null): array
    {
        $pdo = $this->db->getPdo();
        $sql = "
            SELECT a.*, p.title AS property_title, u.full_name AS sender_name
            FROM community_announcements a
            LEFT JOIN properties p ON a.property_id = p.id
            JOIN users u ON a.sender_id = u.id
            WHERE 1=1
        ";
        $params = [];
        if ($propertyId !== null) {
            $sql .= " AND (a.property_id = :pid OR a.property_id IS NULL)";
            $params['pid'] = $propertyId;
        }
        $sql .= " ORDER BY a.created_at DESC LIMIT 50";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createAnnouncement(array $data): array
    {
        $pdo = $this->db->getPdo();
        $propertyId = !empty($data['property_id']) ? (int)$data['property_id'] : null;
        $senderId = (int)($data['sender_id'] ?? 1);
        $senderRole = strtoupper(trim($data['sender_role'] ?? 'CARETAKER'));
        $title = trim($data['title'] ?? '');
        $message = trim($data['message'] ?? '');
        $category = strtoupper(trim($data['category'] ?? 'GENERAL'));
        $priority = strtoupper(trim($data['priority'] ?? 'NORMAL'));
        $channels = trim($data['dispatch_channels'] ?? 'NOTICE_BOARD,SMS,EMAIL');

        if (empty($title) || empty($message)) {
            throw new \InvalidArgumentException("Announcement title and message are required.");
        }

        $uuid = Uuid::uuid4()->toString();
        $stmt = $pdo->prepare("
            INSERT INTO community_announcements (
                uuid, property_id, sender_id, sender_role, title, message,
                category, priority, dispatch_channels
            ) VALUES (
                :u, :pid, :sid, :srole, :title, :msg, :cat, :prio, :ch
            )
        ");
        $stmt->execute([
            'u' => $uuid,
            'pid' => $propertyId,
            'sid' => $senderId,
            'srole' => $senderRole,
            'title' => $title,
            'msg' => $message,
            'cat' => $category,
            'prio' => $priority,
            'ch' => $channels,
        ]);

        return [
            'success' => true,
            'message' => "Announcement '{$title}' successfully published and broadcasted to residents via {$channels}.",
            'announcement_id' => (int)$pdo->lastInsertId()
        ];
    }

    public function listBookings(?int $propertyId = null, ?int $tenantId = null): array
    {
        $pdo = $this->db->getPdo();
        $sql = "
            SELECT b.*, p.title AS property_title, u.full_name AS tenant_name, u.email AS tenant_email, u.phone_number AS tenant_phone,
                   r.full_name AS reviewer_name
            FROM facility_bookings b
            JOIN properties p ON b.property_id = p.id
            JOIN users u ON b.tenant_id = u.id
            LEFT JOIN users r ON b.reviewed_by_id = r.id
            WHERE 1=1
        ";
        $params = [];
        if ($propertyId !== null) {
            $sql .= " AND b.property_id = :pid";
            $params['pid'] = $propertyId;
        }
        if ($tenantId !== null) {
            $sql .= " AND b.tenant_id = :tid";
            $params['tid'] = $tenantId;
        }

        $sql .= " ORDER BY b.booking_date DESC, b.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createBooking(array $data): array
    {
        $pdo = $this->db->getPdo();
        $propertyId = (int)($data['property_id'] ?? 1);
        $tenantId = (int)($data['tenant_id'] ?? 4);
        $facility = strtoupper(trim($data['facility_name'] ?? 'ESTATE_CLUBHOUSE'));
        $date = trim($data['booking_date'] ?? date('Y-m-d', strtotime('+2 days')));
        $slot = trim($data['time_slot'] ?? '14:00 - 18:00');
        $guestCount = (int)($data['guest_count'] ?? 1);
        $purpose = trim($data['purpose'] ?? 'Personal gathering');

        $uuid = Uuid::uuid4()->toString();
        $stmt = $pdo->prepare("
            INSERT INTO facility_bookings (
                uuid, property_id, tenant_id, facility_name, booking_date, time_slot,
                guest_count, purpose, status
            ) VALUES (
                :u, :pid, :tid, :fac, :bdate, :slot, :guests, :purpose, 'PENDING'
            )
        ");
        $stmt->execute([
            'u' => $uuid,
            'pid' => $propertyId,
            'tid' => $tenantId,
            'fac' => $facility,
            'bdate' => $date,
            'slot' => $slot,
            'guests' => $guestCount,
            'purpose' => $purpose,
        ]);

        return [
            'success' => true,
            'status' => 'PENDING',
            'message' => "Booking request for {$facility} on {$date} ({$slot}) submitted. Awaiting manager approval.",
            'booking_id' => (int)$pdo->lastInsertId(),
        ];
    }

    public function updateBookingStatus(int $bookingId, string $status, int $reviewedById = 1, ?string $notes = null): array
    {
        $pdo = $this->db->getPdo();
        $validStatus = in_array(strtoupper($status), ['APPROVED', 'DECLINED', 'CANCELLED'], true)
            ? strtoupper($status)
            : 'APPROVED';

        $stmt = $pdo->prepare("
            UPDATE facility_bookings SET
                status = :status,
                reviewed_by_id = :rev,
                reviewed_at = CURRENT_TIMESTAMP,
                review_notes = :notes
            WHERE id = :bid
        ");
        $stmt->execute([
            'status' => $validStatus,
            'rev' => $reviewedById,
            'notes' => $notes,
            'bid' => $bookingId,
        ]);

        return [
            'success' => true,
            'status' => $validStatus,
            'message' => "Facility booking #{$bookingId} marked as {$validStatus}.",
        ];
    }
}
