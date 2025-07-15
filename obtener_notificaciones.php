<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];

if ($stmt = $conn->prepare("SELECT id, mensaje, fecha, leida 
                            FROM notificaciones 
                            WHERE usuario_id = ? 
                            ORDER BY fecha DESC 
                            LIMIT 10")) {
    $stmt->bind_param("i", $usuario_id);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta']);
        exit;
    }

    $result = $stmt->get_result();
    $notificaciones = [];

    while ($row = $result->fetch_assoc()) {
        // Formatear fecha en formato ISO 8601 para JavaScript
        $row['fecha'] = date('c', strtotime($row['fecha']));
        $notificaciones[] = $row;
    }

    echo json_encode(['success' => true, 'notificaciones' => $notificaciones]);

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta']);
}

$conn->close();
exit;
