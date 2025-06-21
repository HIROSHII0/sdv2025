<?php
session_start();
header('Content-Type: application/json');
include 'conexion.php'; // Debe definir $conn como conexión MySQLi

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "No autenticado"]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$tipo = $_POST['tipo'] ?? '';
$titulo = $_POST['titulo'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$precio = $_POST['precio'] ?? null;

// Validar campos requeridos
if (empty($tipo) || empty($titulo) || empty($descripcion)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Campos requeridos faltantes"]);
    exit;
}

// Normalizar precio
if ($precio === '' || $precio === null) {
    $precio = null;
} else {
    $precio = floatval($precio);
}

// Manejar subida de imagen con validación de tipo y tamaño
$nombreImagen = null;
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    $tipoArchivo = $_FILES['imagen']['type'];
    $tamanoMax = 5 * 1024 * 1024; // 5MB máximo

    if (!in_array($tipoArchivo, $permitidos)) {
        http_response_code(415);
        echo json_encode(["success" => false, "message" => "Tipo de imagen no permitido. Solo JPG, PNG, GIF."]);
        exit;
    }

    if ($_FILES['imagen']['size'] > $tamanoMax) {
        http_response_code(413);
        echo json_encode(["success" => false, "message" => "La imagen es demasiado grande. Máximo 5MB."]);
        exit;
    }

    $directorio = 'uploads/';
    if (!file_exists($directorio)) {
        mkdir($directorio, 0755, true);
    }

    $nombreTemporal = $_FILES['imagen']['tmp_name'];
    $nombreOriginal = basename($_FILES['imagen']['name']);
    $nombreImagen = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "_", $nombreOriginal);

    if (!move_uploaded_file($nombreTemporal, $directorio . $nombreImagen)) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error al subir la imagen"]);
        exit;
    }
}

// Preparar la consulta SQL según si precio es NULL o no
if ($precio === null) {
    $sql = "INSERT INTO publicaciones (usuario_id, tipo, titulo, descripcion, precio, imagen, fecha_publicacion)
            VALUES (?, ?, ?, ?, NULL, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error en prepare: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("issss", $usuario_id, $tipo, $titulo, $descripcion, $nombreImagen);
} else {
    $sql = "INSERT INTO publicaciones (usuario_id, tipo, titulo, descripcion, precio, imagen, fecha_publicacion)
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error en prepare: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("issdss", $usuario_id, $tipo, $titulo, $descripcion, $precio, $nombreImagen);
}

// Ejecutar y responder
if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al guardar: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
