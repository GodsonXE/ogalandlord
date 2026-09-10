<?php
declare(strict_types=1);

namespace App\Domain\Agreement\Services;

use DateTimeImmutable;
use RuntimeException;

class SigningTokenService
{
    public function __construct(private readonly string $appSecret = 'default-secret-key-change-in-production') {}

    public function generateToken(string $leaseUuid, int $validDays = 30): array
    {
        $randomEntropy = bin2hex(random_bytes(32));
        $expiresAt = (new DateTimeImmutable("+{$validDays} days"))->format('Y-m-d H:i:s');
        
        $payload = base64_encode(json_encode([
            'uuid'    => $leaseUuid,
            'expires' => $expiresAt,
            'entropy' => $randomEntropy,
        ], JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $payload, $this->appSecret);
        $plainToken = "{$payload}.{$signature}";
        $tokenHash = hash('sha256', $plainToken);

        return [
            'plain_token' => $plainToken,
            'token_hash'  => $tokenHash,
            'expires_at'  => $expiresAt,
        ];
    }

    public function verifyAndExtractHash(string $token): string
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            throw new RuntimeException('This signing link is invalid or incomplete.');
        }

        [$payload, $signature] = $parts;
        $expectedSignature = hash_hmac('sha256', $payload, $this->appSecret);

        if (!hash_equals($expectedSignature, $signature)) {
            throw new RuntimeException('This signing link has been tampered with or corrupted.');
        }

        $decodedRaw = base64_decode($payload, true);
        if ($decodedRaw === false) {
            throw new RuntimeException('Invalid signing token encoding.');
        }

        $data = json_decode($decodedRaw, true);
        if (!is_array($data) || empty($data['expires'])) {
            throw new RuntimeException('Malformed signing token payload.');
        }

        $expiresAt = $data['expires'];
        if (new DateTimeImmutable() > new DateTimeImmutable($expiresAt)) {
            throw new RuntimeException('This signing link has expired. Please request a fresh one.');
        }

        return hash('sha256', $token);
    }
}