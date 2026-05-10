<?php
declare(strict_types=1);

require_once __DIR__ . '/../admin/includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

try {
    require_admin();
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Método no permitido.');
    }
    if (!verify_csrf($_POST['_csrf'] ?? null)) {
        throw new RuntimeException('Sesión expirada.');
    }

    $reservationId = (int) ($_POST['reservation_id'] ?? 0);
    $pdo = db();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT * FROM reservations WHERE id = ? FOR UPDATE');
    $stmt->execute([$reservationId]);
    $reservation = $stmt->fetch();
    if (!$reservation || $reservation['status'] !== 'pending') {
        throw new RuntimeException('Solo se pueden cancelar reservas pendientes.');
    }

    $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?")->execute([$reservationId]);
    $pdo->prepare("UPDATE raffle_numbers SET status = 'available', reservation_id = NULL, reserved_until = NULL WHERE reservation_id = ? AND status = 'reserved'")
        ->execute([$reservationId]);
    $pdo->prepare("UPDATE payments SET status = 'rejected' WHERE reservation_id = ? AND status = 'pending'")
        ->execute([$reservationId]);
    $pdo->prepare("UPDATE payment_receipts SET status = 'rejected' WHERE reservation_id = ? AND status = 'pending'")
        ->execute([$reservationId]);

    audit_log($pdo, 'reservation_cancelled', 'reservation', $reservationId);
    $pdo->commit();

    echo json_encode(['ok' => true, 'message' => 'Reserva cancelada y números liberados.']);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
}
