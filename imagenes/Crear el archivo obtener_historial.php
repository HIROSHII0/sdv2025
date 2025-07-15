<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$query = "
    SELECT p.*, u.nombre_completo as nombre_usuario, h.fecha_compra
    FROM historial_compras h
    JOIN publicaciones p ON h.publicacion_id = p.id
    JOIN usuarios u ON p.usuario_id = u.id
    WHERE h.usuario_id = ? AND h.tipo = 'producto'
    ORDER BY h.fecha_compra DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$historial = [];
while ($row = $result->fetch_assoc()) {
    $historial[] = $row;
}

echo json_encode(['success' => true, 'historial' => $historial]);

$stmt->close();
$conn->close();
?>