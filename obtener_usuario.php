<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_de_datos = "ventas2025";

$conn = new mysqli($host, $usuario, $contrasena, $base_de_datos);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error en la conexión a BD']);
    exit;
}

$stmt = $conn->prepare("SELECT nombre_usuario, correo FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit;
}

$usuario = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'usuario' => [
        'nombre_usuario' => $usuario['nombre_usuario'],
        'correo' => $usuario['correo']
    ]
]);
?>
