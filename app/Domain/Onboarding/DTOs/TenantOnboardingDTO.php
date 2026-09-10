<?php
declare(strict_types=1);

namespace App\Domain\Onboarding\DTOs;

use App\Domain\Agreement\Enums\AgreementMode;
use InvalidArgumentException;

final readonly class TenantOnboardingDTO
{
    public function __construct(
        public string $tenantFullName,
        public string $tenantEmail,
        public string $tenantPhone,
        public int $unitId,
        public float $rentAmount,
        public string $currency,
        public string $rentStartDate,
        public string $rentDueDate,
        public string $emergencyName,
        public string $emergencyRelationship,
        public string $emergencyPhone,
        public AgreementMode $agreementMode,
        public ?string $uploadedFilePath = null,
        public ?string $landlordSignatureBase64 = null,
        public ?int $propertyId = null,
        public ?string $newPropertyTitle = null,
        public ?string $newPropertyAddress = null,
        public ?string $newPropertyCity = null,
        public ?string $newPropertyState = null,
        public ?string $unitNumber = null,
        public ?string $apartmentType = null,
        public ?int $roomsCount = null,
        public ?int $landlordId = null
    ) {
        if ($this->rentAmount <= 0) {
            throw new InvalidArgumentException('Rent amount must be greater than zero.');
        }

        if (!filter_var($this->tenantEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Please provide a valid email address.');
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            tenantFullName: trim($data['tenant_name'] ?? ''),
            tenantEmail: strtolower(trim($data['tenant_email'] ?? '')),
            tenantPhone: trim($data['tenant_phone'] ?? ''),
            unitId: (int) ($data['unit_id'] ?? 0),
            rentAmount: (float) ($data['rent_amount'] ?? 0.0),
            currency: strtoupper($data['currency'] ?? 'NGN'),
            rentStartDate: $data['rent_start_date'] ?? date('Y-m-d'),
            rentDueDate: $data['rent_due_date'] ?? date('Y-m-d', strtotime('+1 year')),
            emergencyName: trim($data['emergency_name'] ?? ''),
            emergencyRelationship: trim($data['emergency_relationship'] ?? ''),
            emergencyPhone: trim($data['emergency_phone'] ?? ''),
            agreementMode: AgreementMode::from($data['agreement_mode'] ?? 'GENERATE_AND_SIGN'),
            uploadedFilePath: $data['uploaded_file_path'] ?? null,
            landlordSignatureBase64: $data['landlord_signature'] ?? null,
            propertyId: !empty($data['property_id']) ? (int) $data['property_id'] : null,
            newPropertyTitle: !empty($data['new_property_title']) ? trim($data['new_property_title']) : null,
            newPropertyAddress: !empty($data['new_property_address']) ? trim($data['new_property_address']) : null,
            newPropertyCity: !empty($data['new_property_city']) ? trim($data['new_property_city']) : null,
            newPropertyState: !empty($data['new_property_state']) ? trim($data['new_property_state']) : null,
            unitNumber: !empty($data['unit_number']) ? trim($data['unit_number']) : null,
            apartmentType: !empty($data['apartment_type']) ? trim($data['apartment_type']) : null,
            roomsCount: !empty($data['rooms_count']) ? (int) $data['rooms_count'] : null,
            landlordId: !empty($data['landlord_id']) ? (int) $data['landlord_id'] : null
        );
    }
}