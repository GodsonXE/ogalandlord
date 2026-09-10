<?php
declare(strict_types=1);

namespace App\Domain\Payment\Services;

use App\Infrastructure\Database\Connection;
use PDO;
use Ramsey\Uuid\Uuid;

class PaymentSettingsService
{
    public function __construct(private readonly Connection $db) {}

    public function getSettings(int $landlordId): array
    {
        $pdo = $this->db->getPdo();
        $stmt = $pdo->prepare("SELECT * FROM landlord_payment_settings WHERE landlord_id = :lid LIMIT 1");
        $stmt->execute(['lid' => $landlordId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            // Default baseline settings
            return [
                'landlord_id' => $landlordId,
                'bank_name' => 'Zenith Bank Plc',
                'account_number' => '1012345678',
                'account_name' => 'Oga Landlord Escrow / Bello Estates',
                'gateway_provider' => 'PAYSTACK',
                'paystack_public_key' => 'pk_test_sample_key',
                'paystack_secret_key' => 'sk_test_sample_key',
                'flutterwave_public_key' => '',
                'flutterwave_secret_key' => '',
                'settlement_preference' => 'DIRECT_BANK',
                'auto_receipt_generation' => 1,
            ];
        }

        return $row;
    }

    public function saveSettings(int $landlordId, array $data): array
    {
        $pdo = $this->db->getPdo();
        $existing = $this->getSettings($landlordId);

        $bankName = trim($data['bank_name'] ?? $existing['bank_name']);
        $accountNumber = trim($data['account_number'] ?? $existing['account_number']);
        $accountName = trim($data['account_name'] ?? $existing['account_name']);
        $gateway = trim($data['gateway_provider'] ?? $existing['gateway_provider']);
        $paystackPub = trim($data['paystack_public_key'] ?? ($existing['paystack_public_key'] ?? ''));
        $paystackSec = trim($data['paystack_secret_key'] ?? ($existing['paystack_secret_key'] ?? ''));
        $flutterwavePub = trim($data['flutterwave_public_key'] ?? ($existing['flutterwave_public_key'] ?? ''));
        $flutterwaveSec = trim($data['flutterwave_secret_key'] ?? ($existing['flutterwave_secret_key'] ?? ''));
        $settlement = trim($data['settlement_preference'] ?? $existing['settlement_preference']);
        $autoReceipt = isset($data['auto_receipt_generation']) ? (int)$data['auto_receipt_generation'] : 1;

        $check = $pdo->prepare("SELECT id FROM landlord_payment_settings WHERE landlord_id = :lid");
        $check->execute(['lid' => $landlordId]);

        if ($check->fetch()) {
            $stmt = $pdo->prepare("
                UPDATE landlord_payment_settings SET
                    bank_name = :bn,
                    account_number = :an,
                    account_name = :name,
                    gateway_provider = :gw,
                    paystack_public_key = :pk,
                    paystack_secret_key = :sk,
                    flutterwave_public_key = :flw_pk,
                    flutterwave_secret_key = :flw_sk,
                    settlement_preference = :set,
                    auto_receipt_generation = :rec,
                    updated_at = CURRENT_TIMESTAMP
                WHERE landlord_id = :lid
            ");
            $stmt->execute([
                'bn' => $bankName,
                'an' => $accountNumber,
                'name' => $accountName,
                'gw' => $gateway,
                'pk' => $paystackPub,
                'sk' => $paystackSec,
                'flw_pk' => $flutterwavePub,
                'flw_sk' => $flutterwaveSec,
                'set' => $settlement,
                'rec' => $autoReceipt,
                'lid' => $landlordId,
            ]);
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO landlord_payment_settings (
                    uuid, landlord_id, bank_name, account_number, account_name,
                    gateway_provider, paystack_public_key, paystack_secret_key,
                    flutterwave_public_key, flutterwave_secret_key, settlement_preference,
                    auto_receipt_generation
                ) VALUES (
                    :uuid, :lid, :bn, :an, :name, :gw, :pk, :sk, :flw_pk, :flw_sk, :set, :rec
                )
            ");
            $stmt->execute([
                'uuid' => Uuid::uuid4()->toString(),
                'lid' => $landlordId,
                'bn' => $bankName,
                'an' => $accountNumber,
                'name' => $accountName,
                'gw' => $gateway,
                'pk' => $paystackPub,
                'sk' => $paystackSec,
                'flw_pk' => $flutterwavePub,
                'flw_sk' => $flutterwaveSec,
                'set' => $settlement,
                'rec' => $autoReceipt,
            ]);
        }

        return [
            'success' => true,
            'message' => 'Payment gateway and bank account settings saved successfully.',
            'settings' => $this->getSettings($landlordId)
        ];
    }

    public function getPublicPaymentDetails(int $propertyId): array
    {
        $pdo = $this->db->getPdo();
        $pStmt = $pdo->prepare("SELECT landlord_id, title FROM properties WHERE id = :pid");
        $pStmt->execute(['pid' => $propertyId]);
        $prop = $pStmt->fetch(PDO::FETCH_ASSOC);

        $landlordId = $prop ? (int)$prop['landlord_id'] : 2;
        $settings = $this->getSettings($landlordId);

        return [
            'property_id' => $propertyId,
            'property_title' => $prop['title'] ?? 'Managed Property',
            'bank_name' => $settings['bank_name'] ?? 'Zenith Bank Plc',
            'account_number' => $settings['account_number'] ?? '1012345678',
            'account_name' => $settings['account_name'] ?? 'Oga Landlord / Estate Settlement',
            'settlement_preference' => $settings['settlement_preference'] ?? 'DIRECT_BANK',
            'gateway_provider' => $settings['gateway_provider'] ?? 'PAYSTACK',
            'paystack_public_key' => $settings['paystack_public_key'] ?? '',
            'auto_receipt_generation' => (bool)($settings['auto_receipt_generation'] ?? 1),
        ];
    }
}
