<?php
declare(strict_types=1);

namespace App\Domain\Caretaker\Services;

use App\Infrastructure\Database\Connection;
use PDO;

class CaretakerRoutingService
{
    public function __construct(private readonly Connection $db) {}

    public function getCaretakersForProperty(int $propertyId): array
    {
        $pdo = $this->db->getPdo();
        $sql = "SELECT u.id, u.full_name, u.email, u.phone_number
                FROM users u
                JOIN property_caretaker_assignments pca ON pca.caretaker_id = u.id
                WHERE pca.property_id = :property_id AND u.is_active = TRUE";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['property_id' => $propertyId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}