<?php
declare(strict_types=1);

namespace App\Domain\Tenant\Services;

use App\Infrastructure\Database\Connection;
use PDO;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class TenantPortalService
{
    public function __construct(private readonly Connection $db) {}

    /**
     * Retrieve complete dashboard state for an active resident.
     */
    public function getTenantDashboardData(int $tenantId): array
    {
        $pdo = $this->db->getPdo();

        // 1. Tenant User Profile
        $uStmt = $pdo->prepare("SELECT id, uuid, full_name, email, phone_number, role, created_at FROM users WHERE id = :id LIMIT 1");
        $uStmt->execute(['id' => $tenantId]);
        $tenant = $uStmt->fetch();
        if (!$tenant) {
            throw new RuntimeException("Tenant account not found.");
        }

        // 2. Active Lease & Unit
        $lStmt = $pdo->prepare("SELECT l.*, un.unit_number, un.apartment_type, un.rooms_count, un.default_rent_amount,
                                       p.id as property_id, p.title as property_title, p.address_line_1, p.city, p.state, p.country,
                                       p.landlord_id
                                FROM leases l
                                JOIN units un ON l.unit_id = un.id
                                JOIN properties p ON un.property_id = p.id
                                WHERE l.tenant_id = :tid AND l.agreement_status != 'TERMINATED'
                                ORDER BY l.id DESC LIMIT 1");
        $lStmt->execute(['tid' => $tenantId]);
        $activeLease = $lStmt->fetch();

        // 3. Landlord details
        $landlord = null;
        if ($activeLease && !empty($activeLease['landlord_id'])) {
            $llStmt = $pdo->prepare("SELECT id, full_name, email, phone_number FROM users WHERE id = :lid LIMIT 1");
            $llStmt->execute(['lid' => $activeLease['landlord_id']]);
            $landlord = $llStmt->fetch();
        }

        // 4. Assigned Caretaker details
        $caretaker = null;
        if ($activeLease && !empty($activeLease['property_id'])) {
            $cStmt = $pdo->prepare("SELECT u.id, u.full_name, u.email, u.phone_number
                                    FROM property_caretaker_assignments pca
                                    JOIN users u ON pca.caretaker_id = u.id
                                    WHERE pca.property_id = :pid LIMIT 1");
            $cStmt->execute(['pid' => $activeLease['property_id']]);
            $caretaker = $cStmt->fetch();
        }

        // 5. Maintenance Tickets
        $tStmt = $pdo->prepare("SELECT t.*, un.unit_number,
                                       (SELECT COUNT(*) FROM ticket_messages m WHERE m.ticket_id = t.id) as message_count,
                                       (SELECT m.created_at FROM ticket_messages m WHERE m.ticket_id = t.id ORDER BY m.id DESC LIMIT 1) as last_message_at
                                FROM maintenance_tickets t
                                JOIN units un ON t.unit_id = un.id
                                WHERE t.tenant_id = :tid
                                ORDER BY t.id DESC");
        $tStmt->execute(['tid' => $tenantId]);
        $tickets = $tStmt->fetchAll();

        // 6. Payments History
        $pStmt = $pdo->prepare("SELECT * FROM tenant_payments WHERE tenant_id = :tid ORDER BY id DESC");
        $pStmt->execute(['tid' => $tenantId]);
        $payments = $pStmt->fetchAll();

        // 7. Standard Levies and Bills catalog for this estate
        $pendingBills = $this->calculateUpcomingBills($tenantId, $activeLease, $payments);

        return [
            'tenant'       => $tenant,
            'lease'        => $activeLease,
            'landlord'     => $landlord,
            'caretaker'    => $caretaker,
            'tickets'      => $tickets,
            'payments'     => $payments,
            'pending_bills'=> $pendingBills,
        ];
    }

    /**
     * Compute standard ongoing charges and upcoming estate dues.
     */
    private function calculateUpcomingBills(int $tenantId, ?array $lease, array $recordedPayments): array
    {
        if (!$lease) {
            return [];
        }

        $bills = [];

        // Check if annual rent has confirmed payment
        $hasPaidRent = false;
        foreach ($recordedPayments as $p) {
            if ($p['payment_type'] === 'RENT' && $p['status'] === 'CONFIRMED') {
                $hasPaidRent = true;
                break;
            }
        }

        if (!$hasPaidRent && ($lease['agreement_status'] ?? '') !== 'FULLY_EXECUTED') {
            $bills[] = [
                'id'               => 'bill-rent',
                'title'            => 'Annual Residency Rent (' . ($lease['unit_number'] ?? 'Unit') . ')',
                'payment_type'     => 'RENT',
                'beneficiary_type' => 'LANDLORD',
                'amount'           => (float) $lease['rent_amount'],
                'currency'         => 'NGN',
                'due_date'         => $lease['rent_due_date'] ?? date('Y-m-d', strtotime('+30 days')),
                'description'      => 'Annual lease rental fee payable to property owner.',
                'beneficiary_name' => 'Chief Ibrahim Bello',
                'bank_name'        => 'Zenith Bank PLC',
                'account_number'   => '1014529088',
                'account_name'     => 'Bello Real Estate Holdings',
            ];
        }

        // Estate Service Charge / Facilities Levy
        $bills[] = [
            'id'               => 'bill-service-charge',
            'title'            => 'Estate Facilities & Security Levy (Q4)',
            'payment_type'     => 'ESTATE_LEVY',
            'beneficiary_type' => 'ESTATE',
            'amount'           => 45000.00,
            'currency'         => 'NGN',
            'due_date'         => date('Y-m-d', strtotime('+14 days')),
            'description'      => 'Covers 24/7 uniformed estate security, streetlighting, and perimeter power.',
            'beneficiary_name' => 'Estate Central Executives',
            'bank_name'        => 'Guaranty Trust Bank (GTBank)',
            'account_number'   => '0234891102',
            'account_name'     => 'PHDL Unity Estate Facility Account',
        ];

        // Waste Management & Environmental Dues
        $bills[] = [
            'id'               => 'bill-waste-sanitation',
            'title'            => 'Environmental Sanitation & Waste Evacuation',
            'payment_type'     => 'WASTE_DISPOSAL',
            'beneficiary_type' => 'ESTATE',
            'amount'           => 12000.00,
            'currency'         => 'NGN',
            'due_date'         => date('Y-m-d', strtotime('+20 days')),
            'description'      => 'AEPB certified weekly trash evacuation and drain desilting.',
            'beneficiary_name' => 'Estate Environmental Committee',
            'bank_name'        => 'Access Bank PLC',
            'account_number'   => '0041289945',
            'account_name'     => 'Unity Environmental Services',
        ];

        return $bills;
    }

    /**
     * Lodge a new maintenance complaint or resident concern.
     */
    public function createMaintenanceTicket(int $tenantId, array $data): array
    {
        $pdo = $this->db->getPdo();

        // 1. Resolve tenant's unit and property
        $lStmt = $pdo->prepare("SELECT l.unit_id, un.property_id, u.full_name as tenant_name
                                FROM leases l
                                JOIN units un ON l.unit_id = un.id
                                JOIN users u ON l.tenant_id = u.id
                                WHERE l.tenant_id = :tid AND l.agreement_status != 'TERMINATED'
                                ORDER BY l.id DESC LIMIT 1");
        $lStmt->execute(['tid' => $tenantId]);
        $lease = $lStmt->fetch();

        if (!$lease) {
            throw new RuntimeException("No active residency found for this tenant.");
        }

        $propertyId = (int) $lease['property_id'];
        $unitId = (int) $lease['unit_id'];
        $tenantName = $lease['tenant_name'];

        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
        $category = strtoupper(trim($data['category'] ?? 'GENERAL'));
        $priority = strtoupper(trim($data['priority'] ?? 'MEDIUM'));

        if (empty($title) || empty($description)) {
            throw new RuntimeException("Please provide a title and detailed description of the issue.");
        }

        // Generate unique code: TKT-2026-XXXX
        $count = (int) $pdo->query("SELECT COUNT(*) FROM maintenance_tickets")->fetchColumn();
        $ticketCode = sprintf('TKT-2026-%04d', $count + 1);
        $uuid = Uuid::uuid4()->toString();

        $photoUrl = !empty($data['photo_url']) ? trim($data['photo_url']) : null;

        $stmt = $pdo->prepare("INSERT INTO maintenance_tickets (
            uuid, property_id, unit_id, tenant_id, ticket_code, category,
            title, description, priority, status, is_escalated_to_landlord, photo_url
        ) VALUES (
            :uuid, :pid, :uid, :tid, :code, :cat, :title, :desc, :pri, 'OPEN', 0, :photo
        )");

        $stmt->execute([
            'uuid'  => $uuid,
            'pid'   => $propertyId,
            'uid'   => $unitId,
            'tid'   => $tenantId,
            'code'  => $ticketCode,
            'cat'   => $category,
            'title' => $title,
            'desc'  => $description,
            'pri'   => $priority,
            'photo' => $photoUrl,
        ]);

        $ticketId = (int) $pdo->lastInsertId();

        // Add initial opening message into chat thread
        $this->postTicketMessage(
            ticketId: $ticketId,
            senderId: $tenantId,
            senderRole: 'TENANT',
            senderName: $tenantName,
            message: $description
        );

        return [
            'success'     => true,
            'ticket_id'   => $ticketId,
            'ticket_code' => $ticketCode,
            'message'     => "Ticket {$ticketCode} created successfully. Assigned to estate caretaker.",
        ];
    }

    /**
     * Retrieve a ticket and its entire conversation thread with privacy filtering.
     */
    public function getTicketWithMessages(int $ticketId, ?int $requestingUserId = null, ?string $requestingRole = null): array
    {
        $pdo = $this->db->getPdo();

        $tStmt = $pdo->prepare("SELECT t.*, un.unit_number, un.apartment_type,
                                       p.title as property_title, p.address_line_1, p.city, p.state, p.landlord_id,
                                       u.full_name as tenant_name, u.email as tenant_email, u.phone_number as tenant_phone
                                FROM maintenance_tickets t
                                JOIN units un ON t.unit_id = un.id
                                JOIN properties p ON t.property_id = p.id
                                JOIN users u ON t.tenant_id = u.id
                                WHERE t.id = :id LIMIT 1");
        $tStmt->execute(['id' => $ticketId]);
        $ticket = $tStmt->fetch();

        if (!$ticket) {
            throw new RuntimeException("Ticket not found.");
        }

        // PRIVACY RULE: Landlord can ONLY view conversation if Caretaker escalated it
        if ($requestingRole === 'LANDLORD') {
            if ((int)$ticket['is_escalated_to_landlord'] !== 1 && $requestingUserId !== (int)$ticket['tenant_id']) {
                throw new RuntimeException("Private ticket between Tenant and Caretaker. This ticket has not been escalated to the Landlord.");
            }
        }

        // Fetch thread messages
        $mStmt = $pdo->prepare("SELECT * FROM ticket_messages WHERE ticket_id = :tid ORDER BY id ASC");
        $mStmt->execute(['tid' => $ticketId]);
        $messages = $mStmt->fetchAll();

        return [
            'ticket'   => $ticket,
            'messages' => $messages,
        ];
    }

    /**
     * Post a new message into the ticket's chat thread.
     */
    public function postTicketMessage(
        int $ticketId,
        int $senderId,
        string $senderRole,
        string $senderName,
        string $message,
        ?string $attachment = null,
        bool $isInternal = false
    ): array {
        $pdo = $this->db->getPdo();

        $stmt = $pdo->prepare("INSERT INTO ticket_messages (
            ticket_id, sender_id, sender_role, sender_name, message, attachment_path, is_internal_note
        ) VALUES (
            :tid, :sid, :role, :name, :msg, :att, :int
        )");

        $stmt->execute([
            'tid'  => $ticketId,
            'sid'  => $senderId,
            'role' => strtoupper($senderRole),
            'name' => $senderName,
            'msg'  => trim($message),
            'att'  => $attachment,
            'int'  => $isInternal ? 1 : 0,
        ]);

        $pdo->prepare("UPDATE maintenance_tickets SET updated_at = CURRENT_TIMESTAMP WHERE id = :id")->execute(['id' => $ticketId]);

        return [
            'success'    => true,
            'message_id' => (int) $pdo->lastInsertId(),
        ];
    }

    /**
     * Caretaker tags or escalates ticket to the Landlord for review/approval.
     */
    public function escalateTicketToLandlord(int $ticketId, int $caretakerId, string $reason): array
    {
        $pdo = $this->db->getPdo();

        // Verify caretaker name
        $cStmt = $pdo->prepare("SELECT full_name FROM users WHERE id = :id LIMIT 1");
        $cStmt->execute(['id' => $caretakerId]);
        $caretaker = $cStmt->fetch();
        $caretakerName = $caretaker['full_name'] ?? 'Assigned Caretaker';

        $stmt = $pdo->prepare("UPDATE maintenance_tickets SET 
            is_escalated_to_landlord = 1,
            escalated_at = CURRENT_TIMESTAMP,
            escalation_reason = :reason,
            status = 'ESCALATED_TO_LANDLORD',
            updated_at = CURRENT_TIMESTAMP
            WHERE id = :id");
        $stmt->execute([
            'reason' => trim($reason),
            'id'     => $ticketId,
        ]);

        // Post a system announcement in the thread
        $systemMsg = "⚡ [ESCALATED TO LANDLORD] Caretaker {$caretakerName} has tagged the Landlord for review and financial authorization.\nNote: \"{$reason}\"";
        $this->postTicketMessage(
            ticketId: $ticketId,
            senderId: $caretakerId,
            senderRole: 'CARETAKER',
            senderName: $caretakerName . ' (Caretaker)',
            message: $systemMsg,
            isInternal: false
        );

        return [
            'success' => true,
            'message' => 'Ticket successfully escalated to Landlord. Landlord has been granted access to review the concern.',
        ];
    }

    /**
     * Caretaker/Tenant changes ticket status (e.g. IN_PROGRESS, RESOLVED, CLOSED).
     */
    public function updateTicketStatus(int $ticketId, string $status, string $updaterRole, string $updaterName): array
    {
        $pdo = $this->db->getPdo();
        $newStatus = strtoupper(trim($status));

        $stmt = $pdo->prepare("UPDATE maintenance_tickets SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $stmt->execute(['status' => $newStatus, 'id' => $ticketId]);

        $this->postTicketMessage(
            ticketId: $ticketId,
            senderId: 0,
            senderRole: $updaterRole,
            senderName: 'System Notice',
            message: "Status changed to '{$newStatus}' by {$updaterName} ({$updaterRole})."
        );

        return ['success' => true, 'status' => $newStatus];
    }

    /**
     * Submit payment (Paystack / Flutterwave / Bank Transfer).
     */
    public function createPayment(int $tenantId, array $data): array
    {
        $pdo = $this->db->getPdo();

        // 1. Resolve active lease
        $lStmt = $pdo->prepare("SELECT l.id as lease_id, un.property_id
                                FROM leases l
                                JOIN units un ON l.unit_id = un.id
                                WHERE l.tenant_id = :tid AND l.agreement_status != 'TERMINATED'
                                ORDER BY l.id DESC LIMIT 1");
        $lStmt->execute(['tid' => $tenantId]);
        $lease = $lStmt->fetch();

        if (!$lease) {
            throw new RuntimeException("Cannot process payment without an active residency.");
        }

        $leaseId = (int) $lease['lease_id'];
        $propertyId = (int) $lease['property_id'];

        $paymentType = strtoupper(trim($data['payment_type'] ?? 'RENT'));
        $beneficiaryType = strtoupper(trim($data['beneficiary_type'] ?? ($paymentType === 'RENT' ? 'LANDLORD' : 'ESTATE')));
        $title = trim($data['title'] ?? 'Residential Rent Payment');
        $amount = (float) ($data['amount'] ?? 0);
        $method = strtoupper(trim($data['payment_method'] ?? 'BANK_TRANSFER'));

        if ($amount <= 0) {
            throw new RuntimeException("Payment amount must be greater than zero.");
        }

        $gatewayRef = !empty($data['gateway_reference']) ? trim($data['gateway_reference']) : ('REF-' . strtoupper(substr(bin2hex(random_bytes(6)), 0, 10)));
        $transferSender = !empty($data['bank_transfer_sender_name']) ? trim($data['bank_transfer_sender_name']) : null;
        $transferRef = !empty($data['bank_transfer_reference']) ? trim($data['bank_transfer_reference']) : null;
        $receiptNumber = 'RCT-2026-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 7));
        $uuid = Uuid::uuid4()->toString();

        // Estate-directed levies bypass landlord confirmation if paid via gateway or direct
        $initialStatus = 'PENDING_CONFIRMATION';
        $confirmedByRole = null;
        $confirmedAt = null;

        if ($beneficiaryType === 'ESTATE' && in_array($method, ['PAYSTACK', 'FLUTTERWAVE'], true)) {
            $initialStatus = 'CONFIRMED';
            $confirmedByRole = 'ESTATE_GATEWAY_AUTO';
            $confirmedAt = date('Y-m-d H:i:s');
        }

        $stmt = $pdo->prepare("INSERT INTO tenant_payments (
            uuid, lease_id, tenant_id, property_id, payment_type, beneficiary_type,
            title, amount, currency, payment_method, gateway_reference,
            bank_transfer_sender_name, bank_transfer_reference, status,
            confirmed_by_role, confirmed_at, receipt_number, notes
        ) VALUES (
            :uuid, :lid, :tid, :pid, :ptype, :btype,
            :title, :amt, 'NGN', :method, :gref,
            :tsender, :tref, :status,
            :crole, :cat, :receipt, :notes
        )");

        $stmt->execute([
            'uuid'    => $uuid,
            'lid'     => $leaseId,
            'tid'     => $tenantId,
            'pid'     => $propertyId,
            'ptype'   => $paymentType,
            'btype'   => $beneficiaryType,
            'title'   => $title,
            'amt'     => $amount,
            'method'  => $method,
            'gref'    => $gatewayRef,
            'tsender' => $transferSender,
            'tref'    => $transferRef,
            'status'  => $initialStatus,
            'crole'   => $confirmedByRole,
            'cat'     => $confirmedAt,
            'receipt' => $receiptNumber,
            'notes'   => $data['notes'] ?? null,
        ]);

        $paymentId = (int) $pdo->lastInsertId();

        $message = ($initialStatus === 'CONFIRMED')
            ? "Payment verified directly with Estate Central Office. Official receipt issued."
            : "Payment recorded successfully. Awaiting confirmation by Caretaker or Landlord.";

        return [
            'success'        => true,
            'payment_id'     => $paymentId,
            'receipt_number' => $receiptNumber,
            'status'         => $initialStatus,
            'message'        => $message,
        ];
    }

    /**
     * Caretaker or Landlord confirms and verifies an incoming payment.
     */
    public function confirmPayment(int $paymentId, int $confirmerId, string $confirmerRole, ?string $notes = null): array
    {
        $pdo = $this->db->getPdo();

        $stmt = $pdo->prepare("SELECT * FROM tenant_payments WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $paymentId]);
        $payment = $stmt->fetch();

        if (!$payment) {
            throw new RuntimeException("Payment record not found.");
        }

        $now = date('Y-m-d H:i:s');
        $up = $pdo->prepare("UPDATE tenant_payments SET 
            status = 'CONFIRMED',
            confirmed_by_user_id = :cid,
            confirmed_by_role = :role,
            confirmed_at = :cat,
            notes = COALESCE(:notes, notes),
            updated_at = CURRENT_TIMESTAMP
            WHERE id = :id");

        $up->execute([
            'cid'   => $confirmerId,
            'role'  => strtoupper($confirmerRole),
            'cat'   => $now,
            'notes' => $notes,
            'id'    => $paymentId,
        ]);

        // If it was Rent, ensure lease is fully marked executed and extend period
        if ($payment['payment_type'] === 'RENT') {
            $pdo->prepare("UPDATE leases SET agreement_status = 'FULLY_EXECUTED' WHERE id = :lid")
                ->execute(['lid' => $payment['lease_id']]);

            // Sync with rent_payments ledger
            $rpCheck = $pdo->prepare("SELECT id FROM rent_payments WHERE gateway_reference = :ref LIMIT 1");
            $rpCheck->execute(['ref' => $payment['gateway_reference'] ?: $payment['receipt_number']]);
            if (!$rpCheck->fetch()) {
                $insRp = $pdo->prepare("INSERT INTO rent_payments (
                    uuid, lease_id, amount, currency, payment_method, gateway_reference, payment_status,
                    payment_date, covered_period_start, covered_period_end, receipt_number
                ) VALUES (
                    ?, ?, ?, 'NGN', ?, ?, 'PAID', ?, ?, ?, ?
                )");
                $insRp->execute([
                    Uuid::uuid4()->toString(),
                    $payment['lease_id'],
                    $payment['amount'],
                    $payment['payment_method'],
                    $payment['gateway_reference'] ?: $payment['receipt_number'],
                    $now,
                    date('Y-m-d'),
                    date('Y-m-d', strtotime('+1 year')),
                    $payment['receipt_number'],
                ]);
            }
        }

        return [
            'success'        => true,
            'receipt_number' => $payment['receipt_number'],
            'message'        => "Payment successfully confirmed by {$confirmerRole}.",
        ];
    }
}
