<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Onboarding\DTOs\TenantOnboardingDTO;
use App\Domain\Onboarding\Services\TenantOnboardingService;

class TenantOnboardingController
{
    public function __construct(private readonly TenantOnboardingService $service) {}

    public function store(): void
    {
        header('Content-Type: application/json');
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? [];

        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (empty($data['landlord_id']) && !empty($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'LANDLORD') {
                $data['landlord_id'] = (int) $_SESSION['user_id'];
            }
            $dto = TenantOnboardingDTO::fromArray($data);
            $result = $this->service->onboard($dto);
            echo json_encode($result);
        } catch (\Throwable $e) {
            http_response_code(422);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}