<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit;
}

// Validar datos de entrada
$requiredFields = ['id', 'titulo', 'descripcion', 'duracion', 'lugar'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "El campo $field es requerido"]);
        exit;
    }
}

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "ventas2025");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos"]);
    exit;
}

// Procesar imagen si se subió una nueva
$imagenNombre = null;
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    $fileType = $_FILES['imagen']['type'];
    $fileSize = $_FILES['imagen']['size'];
    
    if (!in_array($fileType, $allowedTypes)) {
        http_response_code(415);
        echo json_encode(["success" => false, "message" => "Tipo de archivo no permitido. Solo JPG, PNG o GIF."]);
        exit;
    }
    
    if ($fileSize > $maxSize) {
        http_response_code(413);
        echo json_encode(["success" => false, "message" => "El archivo es demasiado grande. Máximo 5MB."]);
        exit;
    }
    
    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
    $imagenNombre = 'pub_' . time() . '_' . $_SESSION['usuario_id'] . '.' . $extension;
    
    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadDir . $imagenNombre)) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Error al subir la imagen"]);
        exit;
    }
}

// Obtener el tipo de publicación actual para validaciones específicas
$tipoPublicacion = null;
$stmtTipo = $conn->prepare("SELECT tipo FROM publicaciones WHERE id = ? AND usuario_id = ?");
if (!$stmtTipo) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al preparar consulta de tipo: " . $conn->error]);
    exit;
}
$stmtTipo->bind_param("ii", $_POST['id'], $_SESSION['usuario_id']);
$stmtTipo->execute();
$stmtTipo->bind_result($tipoPublicacion);
$stmtTipo->fetch();
$stmtTipo->close();

if (!$tipoPublicacion) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Publicación no encontrada o no pertenece al usuario"]);
    exit;
}

// Obtener y validar campos
$id = intval($_POST['id']);
$titulo = trim($_POST['titulo']);
$descripcion = trim($_POST['descripcion']);
$precio = isset($_POST['precio']) && $_POST['precio'] !== '' ? floatval($_POST['precio']) : null;
$edad = isset($_POST['edad']) && $_POST['edad'] !== '' ? intval($_POST['edad']) : null;
$raza = isset($_POST['raza']) ? trim($_POST['raza']) : null;
$vacunado = isset($_POST['vacunado']) ? (int)$_POST['vacunado'] : null; // 0 o 1 o null
$desparasitado = isset($_POST['desparasitado']) ? (int)$_POST['desparasitado'] : null; // 0 o 1 o null
$duracion = trim($_POST['duracion']);
$lugar = trim($_POST['lugar']);
$usuario_id = $_SESSION['usuario_id'];

// Validaciones específicas por tipo de publicación
if ($tipoPublicacion === 'mascota') {
    if ($edad === null) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "La edad es requerida para mascotas"]);
        exit;
    }
    if (empty($raza)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "La raza es requerida para mascotas"]);
        exit;
    }
    if ($vacunado !== null && !in_array($vacunado, [0,1])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Vacunado debe ser 0 o 1"]);
        exit;
    }
    if ($desparasitado !== null && !in_array($desparasitado, [0,1])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Desparasitado debe ser 0 o 1"]);
        exit;
    }
}

// Construir consulta SQL
$sql = "UPDATE publicaciones SET 
        titulo = ?, 
        descripcion = ?, 
        precio = ?, 
        edad = ?, 
        raza = ?, 
        vacunado = ?, 
        desparasitado = ?, 
        fecha_actualizacion = NOW(),
        duracion = ?, 
        lugar = ?";

if ($imagenNombre) {
    $sql .= ", imagen = ?";
}

$sql .= " WHERE id = ? AND usuario_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al preparar la consulta: " . $conn->error]);
    exit;
}

// Preparar bind_param dinámicamente
if ($imagenNombre) {
    // Tipos: s=string, d=double, i=integer
    $stmt->bind_param(
        "ssdiiissssiii",
        $titulo,
        $descripcion,
        $precio,
        $edad,
        $raza,
        $vacunado,
        $desparasitado,
        $duracion,
        $lugar,
        $imagenNombre,
        $id,
        $usuario_id
    );
} else {
    $stmt->bind_param(
        "ssdiiisssii",
        $titulo,
        $descripcion,
        $precio,
        $edad,
        $raza,
        $vacunado,
        $desparasitado,
        $duracion,
        $lugar,
        $id,
        $usuario_id
    );
}

// Ejecutar la actualización
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "success" => true, 
            "message" => "Publicación actualizada con éxito",
            "imagen" => $imagenNombre
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "No se realizaron cambios"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Error al actualizar publicación: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
