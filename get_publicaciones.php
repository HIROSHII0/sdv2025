<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "No autorizado."]);
    exit;
}

$conn = new mysqli("localhost", "root", "", "ventas2025");
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

// Consulta mejorada con manejo de valores nulos
if ($categoria === 'todo') {
    $sql = "SELECT 
                p.*, 
                u.nombre_completo AS usuario_nombre,
                COALESCE(p.imagen, 'default_product.jpg') AS imagen,
                COALESCE(p.precio, 0) AS precio,
                COALESCE(p.descripcion, 'Sin descripción') AS descripcion
            FROM publicaciones p
            LEFT JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.usuario_id = ?
            ORDER BY p.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
} else {
    $sql = "SELECT 
                p.*, 
                u.nombre_completo AS usuario_nombre,
                COALESCE(p.imagen, 'default_product.jpg') AS imagen,
                COALESCE(p.precio, 0) AS precio,
                COALESCE(p.descripcion, 'Sin descripción') AS descripcion
            FROM publicaciones p
            LEFT JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.usuario_id = ? AND p.tipo = ?
            ORDER BY p.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id_usuario, $categoria);
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error en consulta."]);
    $stmt->close();
    $conn->close();
    exit;
}

$result = $stmt->get_result();
$publicaciones = [];

while ($row = $result->fetch_assoc()) {
    // No anteponer nada, usar la ruta tal cual
    // Si está vacío, poner imagen por defecto:
    if (empty($row['imagen'])) {
        $row['imagen'] = 'uploads/logo.jpg';  // o la que uses como default
    }
    $publicaciones[] = $row;
}


echo json_encode([
    "success" => true,
    "publicaciones" => $publicaciones
]);

$stmt->close();
$conn->close();
?>