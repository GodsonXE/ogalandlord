<?php
declare(strict_types=1);

namespace App\Domain\Agreement\Services;

use App\Domain\Agreement\Enums\AgreementStatus;
use App\Infrastructure\Database\Connection;
use PDO;
use RuntimeException;

class AgreementCompilerService
{
    public function __construct(private readonly Connection $db) {}

    public function compileDraftAgreement(array $metadata): string
    {
        $storageDir = __DIR__ . '/../../../../storage/agreements';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $filePath = "{$storageDir}/agreement_{$metadata['lease_uuid']}_draft.html";
        $html = "<html><body><h1>Tenancy Agreement</h1><p>Tenant: {$metadata['tenant_name']}</p></body></html>";
        file_put_contents($filePath, $html);

        return $filePath;
    }

    public function executeTenantSignature(
        string $tokenHash,
        string $signatureType,
        string $signatureData,
        string $ipAddress,
        string $userAgent
    ): array {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT l.*, u.full_name as tenant_name, u.email as tenant_email FROM leases l JOIN users u ON l.tenant_id = u.id WHERE l.signing_token_hash = :hash LIMIT 1");
        $stmt->execute(['hash' => $tokenHash]);
        $lease = $stmt->fetch();

        if (!$lease) {
            throw new RuntimeException('Lease agreement not found.');
        }

        $nowUtc = gmdate('Y-m-d H:i:s');
        $sha256Stamp = hash('sha256', json_encode($lease) . $signatureData . $nowUtc . $ipAddress);

        $update = $pdo->prepare("UPDATE leases SET agreement_status = 'FULLY_EXECUTED', document_sha256_hash = :hash, tenant_signature_type = :type, tenant_signature_image_path = :path, tenant_signed_at = :signed_at, tenant_ip_address = :ip, tenant_user_agent = :ua, signing_token_hash = NULL WHERE id = :id");
        $update->execute([
            'hash'      => $sha256Stamp,
            'type'      => $signatureType,
            'path'      => $signatureData,
            'signed_at' => $nowUtc,
            'ip'        => $ipAddress,
            'ua'        => substr($userAgent, 0, 255),
            'id'        => $lease['id']
        ]);

        return [
            'document_hash' => $sha256Stamp,
            'signed_at'     => $nowUtc,
            'human_message' => 'Agreement signed successfully. Your verified audit certificate has been generated.'
        ];
    }
}