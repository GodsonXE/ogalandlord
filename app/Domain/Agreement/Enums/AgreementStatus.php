<?php
declare(strict_types=1);

namespace App\Domain\Agreement\Enums;

enum AgreementStatus: string
{
    case DRAFT                        = 'DRAFT';
    case WAITING_FOR_TENANT_SIGNATURE = 'WAITING_FOR_TENANT_SIGNATURE';
    case SIGNED_OFFLINE               = 'SIGNED_OFFLINE';
    case ACTIVE_WITHOUT_CONTRACT      = 'ACTIVE_WITHOUT_CONTRACT';
    case FULLY_EXECUTED               = 'FULLY_EXECUTED';

    public function humanLabel(string $tenantName = 'Tenant'): string
    {
        return match($this) {
            self::DRAFT                        => 'Draft in preparation',
            self::WAITING_FOR_TENANT_SIGNATURE => "Waiting for {$tenantName} to sign",
            self::SIGNED_OFFLINE               => 'Signed offline (Document archived)',
            self::ACTIVE_WITHOUT_CONTRACT      => 'Active lease (No contract attached)',
            self::FULLY_EXECUTED               => 'Signed and fully verified',
        };
    }

    public function badgeClasses(): string
    {
        return match($this) {
            self::WAITING_FOR_TENANT_SIGNATURE => 'bg-amber-50 text-amber-700 border-amber-200/60',
            self::FULLY_EXECUTED               => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
            self::SIGNED_OFFLINE               => 'bg-slate-50 text-slate-700 border-slate-200/60',
            self::ACTIVE_WITHOUT_CONTRACT      => 'bg-blue-50 text-blue-700 border-blue-200/60',
            self::DRAFT                        => 'bg-zinc-50 text-zinc-600 border-zinc-200/60',
        };
    }
}