<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['estado' => 'error', 'mensaje' => 'Debes iniciar sesión para comprar']);
    exit;
}

$datos = json_decode(file_get_contents('php://input'), true);

// Validación básica
if (empty($datos['publicacion_id']) || !isset($datos['cantidad'])) {
    echo json_encode(['estado' => 'error', 'mensaje' => 'Datos incompletos']);
    exit;
}

$publicacion_id = intval($datos['publicacion_id']);
$cantidad = intval($datos['cantidad']);
$comprador_id = $_SESSION['usuario_id'];

// Obtener datos de la publicación
$consulta = $conn->prepare("SELECT 
    p.id, p.usuario_id as vendedor_id, p.titulo, p.precio_producto, 
    u.nombre_usuario as vendedor_nombre,
    du.nombre_completo as vendedor_nombre_completo
    FROM publicaciones p
    JOIN usuarios u ON p.usuario_id = u.id
    JOIN datos_usuarios du ON p.usuario_id = du.usuario_id
    WHERE p.id = ? AND p.tipo = 'producto'");

$consulta->bind_param("i", $publicacion_id);
$consulta->execute();
$resultado = $consulta->get_result();

if ($resultado->num_rows === 0) {
    echo json_encode(['estado' => 'error', 'mensaje' => 'Producto no disponible']);
    exit;
}

$publicacion = $resultado->fetch_assoc();
$consulta->close();

// Calcular total
$monto_total = $publicacion['precio_producto'] * $cantidad;

// Obtener datos del comprador
$consulta = $conn->prepare("SELECT nombre_completo FROM datos_usuarios WHERE usuario_id = ?");
$consulta->bind_param("i", $comprador_id);
$consulta->execute();
$comprador = $consulta->get_result()->fetch_assoc();
$consulta->close();

// Registrar notificación para el VENDEDOR
$mensaje_vendedor = sprintf(
    "¡Venta realizada! %s compró %d unidad(es) de '%s' por RD$%d",
    $comprador['nombre_completo'],
    $cantidad,
    $publicacion['titulo'],
    $monto_total
);

// Registrar notificación para el COMPRADOR
$mensaje_comprador = sprintf(
    "Compra confirmada: %d unidad(es) de '%s' a %s por RD$%d",
    $cantidad,
    $publicacion['titulo'],
    $publicacion['vendedor_nombre_completo'],
    $monto_total
);

// Insertar ambas notificaciones en una sola consulta
$stmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, mensaje) VALUES (?, ?), (?, ?)");
$stmt->bind_param("isis", 
    $publicacion['vendedor_id'], $mensaje_vendedor,
    $comprador_id, $mensaje_comprador
);

if ($stmt->execute()) {
    echo json_encode([
        'estado' => 'éxito',
        'mensaje' => 'Compra registrada correctamente',
        'detalles' => [
            'producto' => $publicacion['titulo'],
            'vendedor' => $publicacion['vendedor_nombre_completo'],
            'total' => $monto_total
        ]
    ]);
} else {
    echo json_encode(['estado' => 'error', 'mensaje' => 'Error al registrar la transacción']);
}

$stmt->close();
$conn->close();
?>