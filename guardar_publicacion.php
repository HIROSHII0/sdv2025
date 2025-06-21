<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_de_datos = "ventas2025";

$conn = new mysqli($host, $usuario, $contrasena, $base_de_datos);
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Conexión fallida: " . $conn->connect_error
    ]);
    exit;
}

// Verificar sesión
$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    echo json_encode([
        "success" => false,
        "message" => "Usuario no autenticado."
    ]);
    exit;
}

// Recibir datos POST y sanitizar
$tipo = $_POST['tipo'] ?? null;
$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$detalles = trim($_POST['detalles'] ?? '');

// Campos específicos (usa null si no viene o está vacío)
$edad = !empty($_POST['edad']) ? trim($_POST['edad']) : null;
$raza = !empty($_POST['raza']) ? trim($_POST['raza']) : null;
$tamano = !empty($_POST['tamano']) ? trim($_POST['tamano']) : null;
$vacunado = isset($_POST['vacunado']) && ($_POST['vacunado'] == "sí" || $_POST['vacunado'] == "1") ? 1 : 0;
$desparasitado = isset($_POST['desparasitado']) && ($_POST['desparasitado'] == "sí" || $_POST['desparasitado'] == "1") ? 1 : 0;

$duracion = !empty($_POST['duracion']) ? trim($_POST['duracion']) : null;
$lugar = !empty($_POST['lugar']) ? trim($_POST['lugar']) : null;
$precio = isset($_POST['precio']) && $_POST['precio'] !== '' ? floatval($_POST['precio']) : null;
$telefono = !empty($_POST['telefono']) ? trim($_POST['telefono']) : null;

$precio_producto = isset($_POST['precio_producto']) && $_POST['precio_producto'] !== '' ? floatval($_POST['precio_producto']) : null;
$estado_producto = !empty($_POST['estado_producto']) ? trim($_POST['estado_producto']) : null;
$categoria_producto = !empty($_POST['categoria_producto']) ? trim($_POST['categoria_producto']) : null;

// Validar campos obligatorios
if (!$tipo || !$titulo || !$descripcion) {
    echo json_encode([
        "success" => false,
        "message" => "Faltan datos obligatorios."
    ]);
    exit;
}

// Procesar imagen subida (opcional)
$imagen_nombre = null;
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['imagen']['tmp_name'];
    $original_name = basename($_FILES['imagen']['name']);
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    // Validar extensión
    $ext_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $ext_permitidas)) {
        echo json_encode([
            "success" => false,
            "message" => "Tipo de archivo no permitido."
        ]);
        exit;
    }

    $nuevo_nombre = uniqid('img_') . '.' . $ext;
    $destino = "uploads/" . $nuevo_nombre;

    if (!move_uploaded_file($tmp_name, $destino)) {
        echo json_encode([
            "success" => false,
            "message" => "Error al subir la imagen."
        ]);
        exit;
    }

    $imagen_nombre = $nuevo_nombre;
}

// Preparar consulta
$sql = "INSERT INTO publicaciones 
    (usuario_id, tipo, titulo, descripcion, detalles, imagen,
    edad, raza, tamano, vacunado, desparasitado,
    duracion, lugar, precio, telefono,
    precio_producto, estado_producto, categoria_producto, fecha_publicacion)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Error en la preparación de la consulta: " . $conn->error
    ]);
    exit;
}

// Parámetros: i=integer, s=string, d=double (float)
$stmt->bind_param(
    "issssssssiiissdiss",
    $usuario_id,
    $tipo,
    $titulo,
    $descripcion,
    $detalles,
    $imagen_nombre,
    $edad,
    $raza,
    $tamano,
    $vacunado,
    $desparasitado,
    $duracion,
    $lugar,
    $precio,
    $telefono,
    $precio_producto,
    $estado_producto,
    $categoria_producto
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Publicación guardada con éxito."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error al guardar publicación: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
