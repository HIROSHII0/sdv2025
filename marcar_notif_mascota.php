<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$notificacion_id = $data['id'] ?? null;

if (!$notificacion_id) {
    header('HTTP/1.1 400 Bad Request');
    exit;
}

// Verificar que la notificación pertenece al usuario y es de mascotas
$query = $conn->prepare("UPDATE notificaciones SET leida = 1 
                        WHERE id = ? AND usuario_id = ? 
                        AND (mensaje LIKE '%[Mascota]%' OR mensaje LIKE '%masc-%')");
$query->bind_param("ii", $notificacion_id, $_SESSION['usuario_id']);
$success = $query->execute();

header('Content-Type: application/json');
echo json_encode([
    'success' => $success,
    'message' => $success ? 'Notificación marcada como leída' : 'Error al marcar notificación'
]);

$query->close();
$conn->close();
?>