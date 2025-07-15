<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
    exit;
}

$query = $conn->prepare("SELECT id, tipo, titulo, descripcion, detalles, imagen, edad, raza, tamano, vacunado, desparasitado, lugar, precio, telefono, fecha_publicacion FROM publicaciones WHERE usuario_id = ?");
if (!$query) {
    echo json_encode(['success' => false, 'error' => 'Error en la consulta SQL: ' . $conn->error]);
    exit;
}

$query->bind_param("i", $_SESSION['usuario_id']);
$query->execute();
$result = $query->get_result();

$publicaciones = [];
while ($row = $result->fetch_assoc()) {
    // Si quieres modificar la ruta de la imagen para que venga desde "uploads/", puedes hacerlo aquí:
    $row['imagen'] = 'uploads/' . $row['imagen'];
    $publicaciones[] = $row;
}

echo json_encode(['success' => true, 'publicaciones' => $publicaciones]);

$query->close();
$conn->close();
?>
