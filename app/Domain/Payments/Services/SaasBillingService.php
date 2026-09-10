<?php
declare(strict_types=1);

namespace App\Domain\Payments\Services;

use App\Infrastructure\Database\Connection;
use PDO;

class SaasBillingService
{
    public function __construct(private readonly Connection $db) {}

    public function calculateLandlordAnnualInvoice(int $landlordId): array
    {
        $pdo = $this->db->getPdo();

        $stmt = $pdo->prepare("SELECT * FROM saas_subscriptions WHERE landlord_id = :id LIMIT 1");
        $stmt->execute(['id' => $landlordId]);
        $sub = $stmt->fetch();

        $model = $sub['billing_model'] ?? 'PER_UNIT';
        $unitRate = (float) ($sub['rate_per_unit'] ?? 3000.00);
        $propertyRate = (float) ($sub['rate_per_property'] ?? 25000.00);

        $unitCountStmt = $pdo->prepare("SELECT COUNT(u.id) FROM units u JOIN properties p ON u.property_id = p.id WHERE p.landlord_id = :landlord_id");
        $unitCountStmt->execute(['landlord_id' => $landlordId]);
        $totalUnits = (int) $unitCountStmt->fetchColumn();

        $propCountStmt = $pdo->prepare("SELECT COUNT(id) FROM properties WHERE landlord_id = :landlord_id");
        $propCountStmt->execute(['landlord_id' => $landlordId]);
        $totalProperties = (int) $propCountStmt->fetchColumn();

        if ($model === 'PER_UNIT') {
            $totalAmount = $totalUnits * $unitRate;
            $breakdown = "{$totalUnits} managed units × ₦" . number_format($unitRate, 2);
        } else {
            $totalAmount = $totalProperties * $propertyRate;
            $breakdown = "{$totalProperties} managed estates/properties × ₦" . number_format($propertyRate, 2);
        }

        return [
            'landlord_id'      => $landlordId,
            'billing_model'    => $model,
            'total_units'      => $totalUnits,
            'total_properties' => $totalProperties,
            'annual_fee'       => $totalAmount,
            'currency'         => 'NGN',
            'breakdown_label'  => $breakdown,
            'human_summary'    => "Annual platform fee: ₦" . number_format($totalAmount, 2) . " based on {$breakdown}."
        ];
    }
}