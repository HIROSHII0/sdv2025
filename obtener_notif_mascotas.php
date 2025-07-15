<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener las 10 notificaciones más recientes del usuario
$query = $conn->prepare("SELECT id, mensaje, fecha, leida 
                         FROM notificaciones 
                         WHERE usuario_id = ? 
                         ORDER BY fecha DESC 
                         LIMIT 10");
$query->bind_param("i", $usuario_id);
$query->execute();
$result = $query->get_result();

$notificaciones = [];
while ($row = $result->fetch_assoc()) {
    // Formatear fecha
    $fecha = new DateTime($row['fecha']);
    $row['fecha_formateada'] = $fecha->format('d/m/Y H:i');
    
    // Icono por defecto
    $row['icono'] = 'fas fa-bell';

    $notificaciones[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'notificaciones' => $notificaciones
]);

$query->close();
$conn->close();
?>
