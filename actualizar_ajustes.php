<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "ventas2025");
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']));
}

// Recoger datos del formulario
$nombre = $_POST['nombre'] ?? '';
$correo = $_POST['correo'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$biografia = $_POST['biografia'] ?? '';
$foto_perfil = null;

// Validar datos obligatorios
if (empty($nombre) || empty($correo)) {
    echo json_encode(['success' => false, 'message' => 'Nombre y correo son obligatorios']);
    exit;
}

// Procesar imagen si se envió
if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
    $extension = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
    $nombreArchivo = uniqid('perfil_') . "." . $extension;
    $rutaDestino = 'uploads/' . $nombreArchivo;

    // Verificar tipo de imagen
    $tiposPermitidos = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($extension), $tiposPermitidos)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de imagen no permitido']);
        exit;
    }

    // Mover imagen a la carpeta de destino
    if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $rutaDestino)) {
        $foto_perfil = $rutaDestino;
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al subir la imagen']);
        exit;
    }
}

// Preparar SQL dinámico
$usuario_id = $_SESSION['usuario_id'];
$sql = "UPDATE datos_usuarios SET 
        nombre_completo = ?,
        correo = ?,
        telefono = ?,
        direccion = ?,
        biografia = ?";

if ($foto_perfil) {
    $sql .= ", foto_perfil = ?";
}

$sql .= " WHERE usuario_id = ?";

// Preparar y ejecutar sentencia
if ($foto_perfil) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $nombre, $correo, $telefono, $direccion, $biografia, $foto_perfil, $usuario_id);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nombre, $correo, $telefono, $direccion, $biografia, $usuario_id);
}

if ($stmt->execute()) {
    // Actualizar variables de sesión
    $_SESSION['nombre_completo'] = $nombre;
    $_SESSION['correo'] = $correo;
    $_SESSION['telefono'] = $telefono;
    $_SESSION['direccion'] = $direccion;
    $_SESSION['biografia'] = $biografia;
    if ($foto_perfil) {
        $_SESSION['foto_perfil'] = $foto_perfil;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Datos actualizados correctamente',
        'nuevosDatos' => [
            'nombre_completo' => $nombre,
            'correo' => $correo,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'biografia' => $biografia,
            'foto_perfil' => $foto_perfil ?? ($_SESSION['foto_perfil'] ?? null)
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar los datos']);
}

$stmt->close();
$conn->close();
?>
