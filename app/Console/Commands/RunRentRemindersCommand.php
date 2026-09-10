<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Reminders\Services\RentReminderService;

class RunRentRemindersCommand
{
    public function __construct(private readonly RentReminderService $reminderService) {}

    public function handle(array $args = []): int
    {
        $dryRun = in_array('--dry-run', $args, true);

        echo "[" . date('Y-m-d H:i:s') . "] Running automated rent reminders...\n";
        $result = $this->reminderService->executeScheduledRun($dryRun);

        echo "  Scanned: " . $result['scanned_leases'] . "\n";
        echo "  Halted (Already Paid): " . $result['halted_paid'] . "\n";
        echo "  Dispatched: " . $result['dispatched'] . "\n";
        return 0;
    }
}