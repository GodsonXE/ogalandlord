<?php
declare(strict_types=1);

namespace App\Infrastructure\Notifications\Contracts;

interface NotificationChannelInterface
{
    public function send(array $payload): bool;
}