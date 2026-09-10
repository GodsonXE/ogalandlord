<?php
declare(strict_types=1);

namespace App\Domain\Agreement\Enums;

enum AgreementMode: string
{
    case GENERATE_AND_SIGN = 'GENERATE_AND_SIGN';
    case UPLOAD_EXISTING   = 'UPLOAD_EXISTING';
    case SKIP_AGREEMENT    = 'SKIP_AGREEMENT';

    public function humanLabel(): string
    {
        return match($this) {
            self::GENERATE_AND_SIGN => 'Generate digital agreement for electronic signature',
            self::UPLOAD_EXISTING   => 'Attach an already signed physical contract',
            self::SKIP_AGREEMENT    => 'Begin tenancy immediately without formal agreement',
        };
    }
}