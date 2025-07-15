<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Registrar la compra en tu base de datos (ajusta según tu estructura)
$stmt = $conn->prepare("INSERT INTO compras (comprador_id, producto_id, fecha_compra, estado) VALUES (?, ?, NOW(), 'pendiente')");
$stmt->bind_param("ii", $_SESSION['usuario_id'], $data['producto_id']);
$stmt->execute();
$compra_id = $stmt->insert_id;
$stmt->close();

// Crear notificación para el vendedor
$mensaje = "¡Tu producto '".$conn->real_escape_string($data['titulo'])."' ha sido comprado! ID de compra: $compra_id";
$stmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, mensaje, fecha) VALUES (?, ?, NOW())");
$stmt->bind_param("is", $data['vendedor_id'], $mensaje);
$stmt->execute();
$stmt->close();

// También puedes enviar un correo electrónico al vendedor aquí si lo deseas

echo json_encode(['success' => true, 'message' => 'Compra registrada correctamente']);
$conn->close();
?>