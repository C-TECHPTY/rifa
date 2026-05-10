<?php
declare(strict_types=1);

require_once __DIR__ . '/../admin/includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $admin = require_admin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Método no permitido.');
    }
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        throw new RuntimeException('Sesión expirada.');
    }

    $payload = json_decode($_POST['subscription'] ?? '', true);
    if (!is_array($payload) || empty($payload['endpoint'])) {
        throw new RuntimeException('Suscripción inválida.');
    }

    $pdo = db();
    $endpoint = (string) $payload['endpoint'];
    $p256dh = $payload['keys']['p256dh'] ?? null;
    $auth = $payload['keys']['auth'] ?? null;

    $find = $pdo->prepare('SELECT id FROM push_subscriptions WHERE endpoint = ? LIMIT 1');
    $find->execute([$endpoint]);
    $existingId = (int) ($find->fetchColumn() ?: 0);

    if ($existingId) {
        $stmt = $pdo->prepare('UPDATE push_subscriptions SET admin_id = ?, p256dh_key = ?, auth_token = ?, user_agent = ?, active = 1 WHERE id = ?');
        $stmt->execute([$admin['id'], $p256dh, $auth, substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255), $existingId]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO push_subscriptions (admin_id, endpoint, p256dh_key, auth_token, user_agent) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$admin['id'], $endpoint, $p256dh, $auth, substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)]);
    }

    echo json_encode(['ok' => true, 'message' => 'Suscripción Web Push guardada. El envío se activará en una fase futura.']);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
}
