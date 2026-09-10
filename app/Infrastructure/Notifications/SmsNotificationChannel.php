<?php
declare(strict_types=1);

namespace App\Infrastructure\Notifications;

use App\Infrastructure\Notifications\Contracts\NotificationChannelInterface;

class SmsNotificationChannel implements NotificationChannelInterface
{
    public function send(array $payload): bool
    {
        // Production implementation integrates with Twilio / Termii
        return true;
    }
}