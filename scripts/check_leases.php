<?php
$pdo = new PDO('sqlite:database/property_platform.sqlite');
$leases = $pdo->query("SELECT l.id, l.agreement_status, l.rent_amount, u.full_name, un.unit_number FROM leases l JOIN users u ON l.tenant_id = u.id JOIN units un ON l.unit_id = un.id ORDER BY l.id ASC")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($leases, JSON_PRETTY_PRINT) . PHP_EOL;
