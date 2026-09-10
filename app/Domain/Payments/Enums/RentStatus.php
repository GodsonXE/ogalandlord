<?php
declare(strict_types=1);

namespace App\Domain\Payments\Enums;

use DateTimeImmutable;

enum RentStatus: string
{
    case PAID       = 'PAID';
    case UPCOMING   = 'UPCOMING';
    case DUE_TODAY  = 'DUE_TODAY';
    case OVERDUE    = 'OVERDUE';
    case PROCESSING = 'PROCESSING';

    public static function fromDueDate(DateTimeImmutable $dueDate, bool $isPaid): self
    {
        if ($isPaid) {
            return self::PAID;
        }

        $now = new DateTimeImmutable('today');
        $interval = (int) $now->diff($dueDate)->format('%r%a');

        return match (true) {
            $interval > 0  => self::UPCOMING,
            $interval === 0 => self::DUE_TODAY,
            default        => self::OVERDUE,
        };
    }

    public function humanLabel(int $daysOffset = 0): string
    {
        return match($this) {
            self::PAID       => 'Rent is fully up to date',
            self::DUE_TODAY  => 'Rent is due today',
            self::UPCOMING   => "Rent is due in {$daysOffset} day" . ($daysOffset === 1 ? '' : 's'),
            self::OVERDUE    => "Rent was due " . abs($daysOffset) . " day" . (abs($daysOffset) === 1 ? '' : 's') . " ago",
            self::PROCESSING => 'Payment is currently confirming',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::PAID       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            self::UPCOMING   => 'bg-slate-50 text-slate-600 border-slate-200',
            self::DUE_TODAY  => 'bg-amber-50 text-amber-700 border-amber-200',
            self::OVERDUE    => 'bg-rose-50 text-rose-700 border-rose-200',
            self::PROCESSING => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        };
    }
}