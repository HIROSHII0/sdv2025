<?php
session_start();
require_once 'conexion.php';  // tu conexión a BD

// Leer JSON enviado
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$id = intval($data['id']);

// Opcional: verificar que el usuario tiene permiso para eliminar la publicación
$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

// Verificar que la publicación pertenece al usuario
$stmt = $conn->prepare("SELECT usuario_id FROM publicaciones WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($pub_usuario_id);
if (!$stmt->fetch() || $pub_usuario_id != $usuario_id) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar esta publicación']);
    exit;
}
$stmt->close();

// Eliminar publicación
$stmt = $conn->prepare("DELETE FROM publicaciones WHERE id = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar publicación']);
}
$stmt->close();
$conn->close();
