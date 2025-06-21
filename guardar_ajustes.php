<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$nombre = $_POST['nombre'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$biografia = $_POST['biografia'] ?? '';

$sql = "UPDATE usuarios SET nombre_completo = ?, telefono = ?, direccion = ?, biografia = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $nombre, $telefono, $direccion, $biografia, $usuario_id);
$stmt->execute();
$stmt->close();

header("Location: ajustes.php?exito=1");
exit;
