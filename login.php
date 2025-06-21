<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso no autorizado.');
}

$nombre_usuario = trim($_POST['nombre_usuario'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';

if (empty($nombre_usuario) || empty($contrasena)) {
    die('Por favor, completa todos los campos. <a href="login.html">Volver</a>');
}

$stmt = $conn->prepare("SELECT id, contrasena, nombre_completo FROM usuarios WHERE nombre_usuario = ?");
if (!$stmt) {
    die('Error en la consulta: ' . $conn->error);
}

$stmt->bind_param("s", $nombre_usuario);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    die('Usuario no encontrado. <a href="login.html">Volver</a>');
}

$stmt->bind_result($id, $hash_contrasena, $nombre_completo);
$stmt->fetch();

if (password_verify($contrasena, $hash_contrasena)) {
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $id;
    $_SESSION['nombre_usuario'] = $nombre_usuario;
    $_SESSION['nombre_completo'] = $nombre_completo;

    $stmt->close();
    $conn->close();

    // Asegúrate de que este archivo exista
    header('Location: productos.php');
    exit;
} else {
    $stmt->close();
    $conn->close();
    die('Contraseña incorrecta. <a href="login.html">Volver</a>');
}
