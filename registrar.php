<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Acceso no autorizado.');
}

$nombre_completo = trim($_POST['nombre_completo']);
$correo = trim($_POST['correo']);
$nombre_usuario = trim($_POST['nombre_usuario']);
$contrasena = $_POST['contrasena'];
$confirmar = $_POST['confirmar'];

if ($contrasena !== $confirmar) {
    die("Las contraseñas no coinciden. <a href='registro.html'>Volver</a>");
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    die("Correo no válido. <a href='registro.html'>Volver</a>");
}

$hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE nombre_usuario = ? OR correo = ?");
$stmt->bind_param("ss", $nombre_usuario, $correo);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    die("El nombre de usuario o el correo ya están registrados. <a href='registro.html'>Volver</a>");
}
$stmt->close();

$stmt = $conn->prepare("INSERT INTO usuarios (nombre_completo, correo, nombre_usuario, contrasena) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $nombre_completo, $correo, $nombre_usuario, $hash_contrasena);

if ($stmt->execute()) {
    header("Location: login.html");
    exit;
} else {
    echo "Error al registrar: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
