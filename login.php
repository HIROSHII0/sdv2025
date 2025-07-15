<?php
session_start();
require_once 'conexion.php';

// Solo permite acceso via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso no autorizado.');
}

// Limpieza básica de entradas
$nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';

// Validar que no estén vacíos
if (empty($nombre_usuario) || empty($contrasena)) {
    header('Location: login.html?error=camposvacios');
    exit;
}

// Preparar consulta para evitar inyección SQL
$stmt = $conn->prepare("SELECT id, contrasena, nombre_completo FROM usuarios WHERE nombre_usuario = ?");
if (!$stmt) {
    // Error en la preparación de la consulta
    error_log('Error en prepare: ' . $conn->error);
    header('Location: login.html?error=errorconsulta');
    exit;
}

// Asociar parámetro y ejecutar
$stmt->bind_param("s", $nombre_usuario);
$stmt->execute();
$stmt->store_result();

// Verificar si usuario existe
if ($stmt->num_rows === 0) {
    $stmt->close();
    header('Location: login.html?error=noexiste');
    exit;
}

// Obtener datos
$stmt->bind_result($id, $hash_contrasena, $nombre_completo);
$stmt->fetch();

// Verificar contraseña con hash almacenado
if (password_verify($contrasena, $hash_contrasena)) {
    // Regenerar sesión para seguridad
    session_regenerate_id(true);

    // Guardar datos en sesión
    $_SESSION['usuario_id'] = $id;
    $_SESSION['nombre_usuario'] = $nombre_usuario;
    $_SESSION['nombre_completo'] = $nombre_completo;

    $stmt->close();
    $conn->close();

    // Redirigir a página segura
    header('Location: productos.php');
    exit;
} else {
    // Contraseña incorrecta
    $stmt->close();
    $conn->close();
    header('Location: login.html?error=contraseña');
    exit;
}
?>
