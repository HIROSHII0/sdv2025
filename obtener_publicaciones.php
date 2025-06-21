<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'conexion.php';

header('Content-Type: application/json');

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Usuario no autenticado"
    ]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Preparar consulta (selecciona solo campos que usarás)
$sql = "SELECT id, tipo, titulo, descripcion, precio, imagen, fecha_publicacion FROM publicaciones WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Error en la preparación de la consulta: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("i", $usuario_id);

if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Error al ejecutar la consulta: " . $stmt->error
    ]);
    exit;
}

$resultado = $stmt->get_result();

$publicaciones = [];

while ($fila = $resultado->fetch_assoc()) {
    $publicaciones[] = $fila;
}

$stmt->close();
$conn->close();

echo json_encode([
    "success" => true,
    "publicaciones" => $publicaciones
]);
?>
