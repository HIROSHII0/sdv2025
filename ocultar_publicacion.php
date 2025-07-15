<?php
require_once 'conexion.php';

// Obtener y decodificar el JSON
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Validar que se recibieron los datos necesarios
if (!$data || !isset($data['id']) || !isset($data['accion'])) {
    echo json_encode(["success" => false, "message" => "Datos inválidos"]);
    exit;
}

$id = intval($data['id']);
$accion = $data['accion'];

// Determinar el nuevo valor para la columna visible
$nueva_visibilidad = ($accion === 'ocultar') ? 0 : 1;

// Actualizar la visibilidad en la base de datos
$stmt = $conn->prepare("UPDATE publicaciones SET visible = ? WHERE id = ?");
$stmt->bind_param("ii", $nueva_visibilidad, $id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => $accion === 'ocultar' ? "Publicación oculta exitosamente" : "Publicación mostrada exitosamente"
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Error al actualizar visibilidad"]);
}

$stmt->close();
$conn->close();
?>
