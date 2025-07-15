<?php
header('Content-Type: application/json');
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

try {
    $usuario_id = $_SESSION['usuario_id'];
    $stmt = $conn->prepare("SELECT id, mensaje, fecha, leida FROM notificaciones WHERE usuario_id = ? ORDER BY fecha DESC LIMIT 10");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notificaciones = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode([
        'success' => true,
        'notificaciones' => $notificaciones
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor: ' . $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>