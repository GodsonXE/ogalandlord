<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Caretaker\Services\CaretakerRoutingService;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Notifications\EmailNotificationChannel;
use DateTimeImmutable;
use PDO;

class PaymentWebhookController
{
    public function __construct(
        private readonly Connection $db,
        private readonly EmailNotificationChannel $mailer,
        private readonly CaretakerRoutingService $caretakerRouter,
        private readonly string $secret = 'webhook-secret'
    ) {}

    public function handle(): void
    {
        header('Content-Type: application/json');
        $raw = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

        if (!hash_equals(hash_hmac('sha512', $raw, $this->secret), $signature)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid signature']);
            return;
        }

        $data = json_decode($raw, true);
        $reference = $data['data']['reference'] ?? ('ref_' . bin2hex(random_bytes(8)));
        $leaseId = (int) ($data['data']['metadata']['lease_id'] ?? 1);
        $amount = (float) (($data['data']['amount'] ?? 0) / 100);

        $pdo = $this->db->getPdo();
        
        // Idempotency check
        $stmt = $pdo->prepare("SELECT id FROM processed_webhooks WHERE idempotency_key = :k");
        $stmt->execute(['k' => $reference]);
        if ($stmt->fetch()) {
            echo json_encode(['status' => 'already_processed']);
            return;
        }

        $pdo->beginTransaction();
        try {
            $insWh = $pdo->prepare("INSERT INTO processed_webhooks (idempotency_key, gateway_provider, event_type, payload_hash) VALUES (:k, 'PAYSTACK', 'charge.success', :h)");
            $insWh->execute(['k' => $reference, 'h' => hash('sha256', $raw)]);

            $lStmt = $pdo->prepare("SELECT l.*, un.property_id, u.email as tenant_email FROM leases l JOIN units un ON l.unit_id = un.id JOIN users u ON l.tenant_id = u.id WHERE l.id = :id FOR UPDATE");
            $lStmt->execute(['id' => $leaseId]);
            $lease = $lStmt->fetch();

            $nextDue = (new DateTimeImmutable($lease['rent_due_date']))->modify('+1 year')->format('Y-m-d');
            $rcp = 'RCP-' . strtoupper(bin2hex(random_bytes(4)));

            $pay = $pdo->prepare("INSERT INTO rent_payments (uuid, lease_id, amount, currency, payment_method, gateway_reference, payment_status, covered_period_start, covered_period_end, receipt_number) VALUES (:u, :lid, :amt, 'NGN', 'ONLINE_GATEWAY', :ref, 'PAID', :start, :end, :rcp)");
            $pay->execute([
                'u' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                'lid' => $leaseId,
                'amt' => $amount,
                'ref' => $reference,
                'start' => $lease['rent_due_date'],
                'end' => $nextDue,
                'rcp' => $rcp
            ]);

            $upd = $pdo->prepare("UPDATE leases SET rent_due_date = :next WHERE id = :id");
            $upd->execute(['next' => $nextDue, 'id' => $leaseId]);

            $pdo->commit();
            echo json_encode(['status' => 'success', 'receipt' => $rcp, 'renewed_until' => $nextDue]);
        } catch (\Throwable $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}