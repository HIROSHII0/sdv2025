<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "No autorizado."]);
    exit;
}

$host = "localhost";
$user = "root";
$pass = "";
$db = "ventas2025";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al conectar con base de datos"]);
    exit;
}
$conn->set_charset("utf8");

$id_usuario = $_SESSION['usuario_id'];
$categoria = $_GET['categoria'] ?? 'todo';

$categoriasPermitidas = ['todo', 'producto', 'mascota', 'servicio'];
if (!in_array($categoria, $categoriasPermitidas)) {
    $categoria = 'todo';
}

if ($categoria === 'todo') {
    $sql = "SELECT * FROM publicaciones WHERE usuario_id = ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
} else {
    $sql = "SELECT * FROM publicaciones WHERE usuario_id = ? AND tipo = ? ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id_usuario, $categoria);
}

if (!$stmt->execute()) {
    http_response_code(500);
    // No enviar $stmt->error en producción
    echo json_encode(["success" => false, "message" => "Error en consulta."]);
    $stmt->close();
    $conn->close();
    exit;
}

$result = $stmt->get_result();

$publicaciones = [];
while ($row = $result->fetch_assoc()) {
    $publicaciones[] = $row;
}

echo json_encode([
    "success" => true,
    "publicaciones" => $publicaciones
]);

$stmt->close();
$conn->close();
