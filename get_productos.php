<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "No autorizado. Por favor inicia sesión."
    ]);
    exit;
}

$sql = "
    SELECT 
        p.id,
        p.titulo,
        p.descripcion,
        p.precio_producto AS precio,
        p.imagen,
        p.usuario_id,
        u.nombre_usuario,
        p.tipo
    FROM publicaciones p
    INNER JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.tipo = 'producto'
    ORDER BY p.fecha_publicacion DESC
";

$resultado = $conn->query($sql);

$productos = [];

if ($resultado && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        // Verificar si la imagen existe y asignar ruta o imagen por defecto
        $ruta = 'uploads/' . basename($fila['imagen']);
        if (!empty($fila['imagen']) && file_exists($ruta)) {
            $fila['imagen'] = $ruta;
        } else {
            $fila['imagen'] = "https://via.placeholder.com/300x200?text=Sin+imagen";
        }

        $fila['precio'] = floatval($fila['precio']);
        $productos[] = $fila;
    }
}

echo json_encode([
    "success" => true,
    "productos" => $productos
]);

$conn->close();
