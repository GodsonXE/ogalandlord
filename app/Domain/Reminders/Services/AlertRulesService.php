<?php
declare(strict_types=1);

namespace App\Domain\Reminders\Services;

use App\Infrastructure\Database\Connection;
use PDO;
use Ramsey\Uuid\Uuid;

class AlertRulesService
{
    public function __construct(private readonly Connection $db) {}

    public function listRules(?string $scope = null, ?int $targetId = null): array
    {
        $pdo = $this->db->getPdo();
        $sql = "SELECT * FROM alert_rules WHERE 1=1";
        $params = [];

        if ($scope !== null && $scope !== 'ALL') {
            $sql .= " AND scope = :scope";
            $params['scope'] = strtoupper($scope);
        }
        if ($targetId !== null) {
            $sql .= " AND (target_id = :tid OR target_id IS NULL)";
            $params['tid'] = $targetId;
        }

        $sql .= " ORDER BY id ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveRule(array $data): array
    {
        $pdo = $this->db->getPdo();
        $id = !empty($data['id']) ? (int)$data['id'] : null;
        $scope = strtoupper(trim($data['scope'] ?? 'GLOBAL'));
        $targetId = !empty($data['target_id']) ? (int)$data['target_id'] : null;
        $name = trim($data['rule_name'] ?? 'Custom Alert Rule');
        $event = strtoupper(trim($data['trigger_event'] ?? 'RENT_DUE_T7'));
        $channels = trim($data['channels'] ?? 'SMS,EMAIL,IN_APP');
        $grace = (int)($data['grace_period_days'] ?? 7);
        $penalty = (float)($data['penalty_rate_percent'] ?? 1.5);
        $active = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE alert_rules SET
                    scope = :scope,
                    target_id = :tid,
                    rule_name = :name,
                    trigger_event = :evt,
                    channels = :ch,
                    grace_period_days = :grace,
                    penalty_rate_percent = :pen,
                    is_active = :act,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id
            ");
            $stmt->execute([
                'scope' => $scope,
                'tid' => $targetId,
                'name' => $name,
                'evt' => $event,
                'ch' => $channels,
                'grace' => $grace,
                'pen' => $penalty,
                'act' => $active,
                'id' => $id,
            ]);
            $ruleId = $id;
            $msg = "Alert rule '{$name}' updated successfully.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO alert_rules (
                    uuid, scope, target_id, rule_name, trigger_event,
                    channels, grace_period_days, penalty_rate_percent, is_active
                ) VALUES (
                    :u, :scope, :tid, :name, :evt, :ch, :grace, :pen, :act
                )
            ");
            $stmt->execute([
                'u' => Uuid::uuid4()->toString(),
                'scope' => $scope,
                'tid' => $targetId,
                'name' => $name,
                'evt' => $event,
                'ch' => $channels,
                'grace' => $grace,
                'pen' => $penalty,
                'act' => $active,
            ]);
            $ruleId = (int)$pdo->lastInsertId();
            $msg = "New alert rule '{$name}' created successfully.";
        }

        return [
            'success' => true,
            'message' => $msg,
            'rule_id' => $ruleId
        ];
    }

    public function triggerRuleSimulation(int $ruleId): array
    {
        $pdo = $this->db->getPdo();
        $rStmt = $pdo->prepare("SELECT * FROM alert_rules WHERE id = :id");
        $rStmt->execute(['id' => $ruleId]);
        $rule = $rStmt->fetch(PDO::FETCH_ASSOC);

        if (!$rule) {
            throw new \RuntimeException("Alert rule not found.");
        }

        // Count tenants in scope
        $tenantCount = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'TENANT'")->fetchColumn();

        return [
            'success' => true,
            'rule' => $rule,
            'dispatched_count' => $tenantCount,
            'channels_fired' => explode(',', $rule['channels']),
            'message' => "Simulated alert rule '{$rule['rule_name']}' successfully across {$tenantCount} resident endpoints via {$rule['channels']}."
        ];
    }

    public function createOrUpdateRule(array $data): array
    {
        $pdo = $this->db->getPdo();
        $res = $this->saveRule([
            'id' => $data['id'] ?? null,
            'scope' => $data['scope'] ?? 'GLOBAL',
            'target_id' => $data['target_id'] ?? $data['property_id'] ?? $data['landlord_id'] ?? null,
            'rule_name' => $data['rule_name'] ?? ($data['event_type'] ?? 'Custom Alert Rule'),
            'trigger_event' => $data['event_type'] ?? $data['trigger_event'] ?? 'RENT_DUE_T7',
            'channels' => isset($data['channel']) && $data['channel'] === 'MULTI_CHANNEL' ? 'SMS,EMAIL,IN_APP' : ($data['channels'] ?? $data['channel'] ?? 'SMS,EMAIL,IN_APP'),
            'grace_period_days' => $data['grace_period_days'] ?? 7,
            'penalty_rate_percent' => $data['penalty_rate_percent'] ?? 1.5,
            'is_active' => $data['is_active'] ?? 1,
        ]);

        $rule = $pdo->query("SELECT * FROM alert_rules WHERE id = {$res['rule_id']}")->fetch(PDO::FETCH_ASSOC);
        return $rule ?: ['id' => $res['rule_id'], 'success' => true];
    }

    public function simulateAlert(string|int $eventOrRuleId): array
    {
        $pdo = $this->db->getPdo();
        if (is_numeric($eventOrRuleId)) {
            $rule = $pdo->query("SELECT * FROM alert_rules WHERE id = " . (int)$eventOrRuleId)->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM alert_rules WHERE trigger_event = :evt ORDER BY id DESC LIMIT 1");
            $stmt->execute(['evt' => $eventOrRuleId]);
            $rule = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $channels = ['SMS', 'EMAIL', 'IN_APP'];
        if ($rule && !empty($rule['channels'])) {
            $parsed = array_map('trim', explode(',', $rule['channels']));
            if (!empty($parsed)) {
                $channels = $parsed;
            }
        }
        if (!in_array('SMS', $channels, true)) $channels[] = 'SMS';
        if (!in_array('IN_APP', $channels, true)) $channels[] = 'IN_APP';

        return [
            'success' => true,
            'event_type' => is_string($eventOrRuleId) ? $eventOrRuleId : ($rule['trigger_event'] ?? 'RENT_DUE_T7'),
            'simulated_channels' => $channels,
            'channels_fired' => $channels,
            'message' => "Smart alert rule simulation executed successfully across " . implode(', ', $channels)
        ];
    }
}
