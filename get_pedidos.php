<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Conexión a BD
$conn = new mysqli("localhost", "root", "", "ventas2025");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

// Consulta pedidos para el usuario
$sql = "SELECT id, producto, estado FROM pedidos WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$pedidos = [];
while ($row = $result->fetch_assoc()) {
    $pedidos[] = [
        'id' => $row['id'],
        'producto' => $row['producto'],
        'estado' => $row['estado']
    ];
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'pedidos' => $pedidos]);
?>
