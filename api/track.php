<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/includes/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('{"ok":false}'); }
$payload = json_decode((string) file_get_contents('php://input'), true) ?: [];
$type = ($payload['type'] ?? '') === 'product' ? 'product' : 'view';
$productId = $type === 'product' ? max(0, (int) ($payload['product_id'] ?? 0)) : 0;
if ($type === 'product' && !$productId) { http_response_code(422); exit('{"ok":false}'); }
$key = 'tracked_' . $type . '_' . $productId . '_' . date('Y-m-d');
if (empty($_SESSION[$key])) {
  $q = db()->prepare("INSERT INTO analytics_daily(event_date,event_type,product_id,event_count) VALUES(CURDATE(),?,?,1) ON DUPLICATE KEY UPDATE event_count=event_count+1");
  $q->execute([$type,$productId]); $_SESSION[$key] = 1;
}
echo '{"ok":true}';
