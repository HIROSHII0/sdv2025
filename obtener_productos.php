<?php
session_start();
header('Content-Type: application/json');

// Conexión a la base de datos
$servername = "localhost"; // Cambia esto si es necesario
$username = "tu_usuario";
$password = "tu_contraseña";
$dbname = "ventas2025";

$conexion = new mysqli($servername, $username, $password, $dbname);

if ($conexion->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Error de conexión a la base de datos: " . $conexion->connect_error
    ]);
    exit;
}

// Consulta que une productos con usuarios para obtener el nombre del que subió
$sql = "SELECT p.*, u.nombre AS usuario_nombre 
        FROM publicaciones p 
        LEFT JOIN usuarios u ON p.usuario_id = u.id 
        WHERE p.tipo = 'producto' 
        ORDER BY p.id DESC";

$result = $conexion->query($sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Error en la consulta: " . $conexion->error
    ]);
    exit;
}

$productos[] = [
    "id" => $fila['id'],
    "titulo" => $fila['titulo'],
    "descripcion" => $fila['descripcion'],
    "precio" => $fila['precio'],
    "imagen" => 'uploads/' . $fila['imagen'], // Ruta relativa completa
    "usuario_nombre" => $fila['usuario_nombre'] ?? "Anónimo"
];


echo json_encode([
    "success" => true,
    "productos" => $productos
]);

$conexion->close();
?>
