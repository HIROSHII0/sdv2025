<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['estado'], $data['pedido_id'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$estado = $data['estado'];
$pedido_id = (int)$data['pedido_id'];
$usuario_id = $_SESSION['usuario_id'];

$estados_validos = ['en_proceso', 'en_camino', 'entregado'];
if (!in_array($estado, $estados_validos)) {
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit;
}

$conn = new mysqli("localhost", "root", "", "ventas2025");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

// Solo actualizar pedido que pertenece al usuario
$sql = "UPDATE pedidos SET estado = ? WHERE id = ? AND usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $estado, $pedido_id, $usuario_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
}

$stmt->close();
$conn->close();
?>
