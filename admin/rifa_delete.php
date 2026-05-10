<?php
require_once __DIR__ . '/includes/auth.php';

require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf($_POST['_csrf'] ?? null)) {
    $_SESSION['flash'] = 'Solicitud inválida.';
    redirect('rifas.php');
}

$id = (int) ($_POST['id'] ?? 0);
$confirm = trim($_POST['confirm'] ?? '');

if ($confirm !== 'ELIMINAR') {
    $_SESSION['flash'] = 'Para eliminar debes escribir ELIMINAR.';
    redirect('rifas.php');
}

$stmt = $pdo->prepare('SELECT title FROM raffles WHERE id = ?');
$stmt->execute([$id]);
$title = $stmt->fetchColumn();

if (!$title) {
    $_SESSION['flash'] = 'Rifa no encontrada.';
    redirect('rifas.php');
}

audit_log($pdo, 'raffle_deleted', 'raffle', $id, ['title' => $title]);
$delete = $pdo->prepare('DELETE FROM raffles WHERE id = ?');
$delete->execute([$id]);

$_SESSION['flash'] = 'Rifa eliminada. Las reservas/números relacionados también se eliminaron.';
redirect('rifas.php');

