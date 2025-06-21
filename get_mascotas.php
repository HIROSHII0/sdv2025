<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "No autenticado. Por favor inicia sesión."
    ]);
    exit;
}

// Configurar MySQLi para mostrar errores automáticamente (modo desarrollo)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Conexión a la base de datos
$host = "localhost";
$usuario = "root";
$contrasena = "";
$base_de_datos = "ventas2025";

try {
    $conn = new mysqli($host, $usuario, $contrasena, $base_de_datos);
    $conn->set_charset("utf8mb4");

    $sql = "
        SELECT 
            p.id, 
            p.usuario_id, 
            p.tipo, 
            p.titulo AS nombre, 
            p.descripcion, 
            p.detalles, 
            p.imagen, 
            p.edad, 
            p.raza, 
            p.tamano, 
            p.vacunado, 
            p.desparasitado, 
            p.duracion, 
            p.lugar, 
            p.precio, 
            p.telefono, 
            p.estado_producto, 
            p.categoria_producto, 
            p.fecha_publicacion,
            u.nombre_usuario AS usuario_nombre
        FROM publicaciones p
        INNER JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.tipo = 'mascota'
        ORDER BY p.fecha_publicacion DESC
    ";

    $result = $conn->query($sql);
    $mascotas = [];

    while ($row = $result->fetch_assoc()) {
        // Validar imagen y ruta
        $ruta = 'uploads/' . basename($row['imagen']);
        if (!empty($row['imagen']) && file_exists($ruta)) {
            $row['imagen'] = $ruta;
        } else {
            $row['imagen'] = "https://via.placeholder.com/300x200?text=Sin+imagen";
        }

        // Convertir precio a número flotante
        $row['precio'] = is_numeric($row['precio']) ? (float)$row['precio'] : null;

        $mascotas[] = $row;
    }

    echo json_encode([
        "success" => true,
        "mascotas" => $mascotas
    ]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error en el servidor: " . $e->getMessage()
    ]);
} finally {
    if (isset($conn) && $conn->ping()) {
        $conn->close();
    }
}
