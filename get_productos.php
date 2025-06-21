<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "No autenticado"
    ]);
    exit;
}

$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_de_datos = "ventas2025";

$conn = new mysqli($host, $usuario, $contrasena, $base_de_datos);
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Error de conexión: " . $conn->connect_error
    ]);
    exit;
}

$sql = "
    SELECT 
        p.id,
        p.titulo AS nombre,
        p.descripcion,
        p.precio,
        p.imagen,
        u.nombre_usuario AS usuario_nombre
    FROM publicaciones p
    INNER JOIN usuarios u ON p.usuario_id = u.id
    WHERE p.tipo = 'producto'
    ORDER BY p.fecha_publicacion DESC
";

$resultado = $conn->query($sql);
if (!$resultado) {
    echo json_encode([
        "success" => false,
        "message" => "Error en la consulta: " . $conn->error
    ]);
    $conn->close();
    exit;
}

$productos = [];
while ($fila = $resultado->fetch_assoc()) {
    // Ruta relativa al navegador, normalmente uploads/ es accesible desde web
    if (!empty($fila['imagen'])) {
        // Si la imagen no tiene uploads/ al inicio, se lo agregamos
        if (strpos($fila['imagen'], 'uploads/') !== 0) {
            $fila['imagen'] = 'uploads/' . $fila['imagen'];
        }

        // Validar si el archivo existe en el servidor
        if (!file_exists($fila['imagen'])) {
            // Si no existe, ponemos imagen placeholder
            $fila['imagen'] = "https://via.placeholder.com/300x200?text=Sin+imagen";
        }
    } else {
        $fila['imagen'] = "https://via.placeholder.com/300x200?text=Sin+imagen";
    }

    $productos[] = $fila;
}

echo json_encode([
    "success" => true,
    "productos" => $productos
]);

$conn->close();
?>
