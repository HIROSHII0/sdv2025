<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if ($id) {
    $conn->query("UPDATE notificaciones SET leida = 1 WHERE id = $id AND usuario_id = {$_SESSION['usuario_id']}");
}

echo json_encode(['success' => true]);
$conn->close();
?>