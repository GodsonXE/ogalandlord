<?php
declare(strict_types=1);

namespace App\Domain\Reminders\Services;

use App\Domain\Caretaker\Services\CaretakerRoutingService;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Notifications\EmailNotificationChannel;
use App\Infrastructure\Notifications\SmsNotificationChannel;
use DateTimeImmutable;
use PDO;

class RentReminderService
{
    public function __construct(
        private readonly Connection $db,
        private readonly EmailNotificationChannel $mailer,
        private readonly SmsNotificationChannel $sms,
        private readonly CaretakerRoutingService $caretakerRouter
    ) {}

    public function executeScheduledRun(bool $dryRun = false): array
    {
        $pdo = $this->db->getPdo();
        $today = new DateTimeImmutable('today');

        $sql = "SELECT l.id as lease_id, l.rent_due_date, l.rent_amount, l.currency,
                       u.id as tenant_id, u.full_name as tenant_name, u.email as tenant_email, u.phone_number as tenant_phone,
                       un.unit_number, p.id as property_id,
                       (SELECT COUNT(*) FROM rent_payments rp 
                        WHERE rp.lease_id = l.id 
                          AND rp.payment_status = 'PAID' 
                          AND rp.covered_period_end >= l.rent_due_date) as is_paid_ahead
                FROM leases l
                JOIN users u ON l.tenant_id = u.id
                JOIN units un ON l.unit_id = un.id
                JOIN properties p ON un.property_id = p.id
                WHERE l.agreement_status != 'DRAFT'";

        $stmt = $pdo->query($sql);
        $leases = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $scanned = 0;
        $halted = 0;
        $dispatched = 0;

        foreach ($leases as $lease) {
            $scanned++;

            // Auto-Halt check
            if ((int) $lease['is_paid_ahead'] > 0) {
                $halted++;
                continue;
            }

            $dueDate = new DateTimeImmutable($lease['rent_due_date']);
            $daysOffset = (int) $today->diff($dueDate)->format('%r%a');

            $phase = match (true) {
                $daysOffset === 30                   => 'T_MINUS_30',
                $daysOffset === 7                    => 'T_MINUS_7',
                $daysOffset <= 6 && $daysOffset >= 0 => 'T_MINUS_DAILY',
                $daysOffset < 0                      => 'OVERDUE',
                default                              => null,
            };

            if ($phase === null) continue;

            $caretakers = $this->caretakerRouter->getCaretakersForProperty((int) $lease['property_id']);

            if (!$dryRun) {
                $this->mailer->send([
                    'to'      => $lease['tenant_email'],
                    'cc'      => array_column($caretakers, 'email'),
                    'subject' => "Rent update for {$lease['unit_number']}",
                    'body'    => "Rent due in {$daysOffset} days."
                ]);
                $dispatched++;
            }
        }

        return [
            'scanned_leases' => $scanned,
            'halted_paid'    => $halted,
            'dispatched'     => $dispatched,
            'mode'           => $dryRun ? 'DRY_RUN' : 'LIVE'
        ];
    }
}