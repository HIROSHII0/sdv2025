<?php
session_start();
require_once 'conexion.php';

// Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autorizado. Por favor inicia sesión."]);
    exit;
}

// Parámetros
$search = isset($_GET['search']) ? "%{$_GET['search']}%" : '%';
$sort = $_GET['sort'] ?? '';
$price_min = isset($_GET['price_min']) ? floatval($_GET['price_min']) : 0;
$price_max = isset($_GET['price_max']) ? floatval($_GET['price_max']) : 1000000;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$items_per_page = 6;
$offset = ($page - 1) * $items_per_page;

// Consulta SQL
$sql = "SELECT SQL_CALC_FOUND_ROWS 
    p.id, 
    p.titulo, 
    p.descripcion, 
    p.imagen, 
    p.precio_producto AS precio,
    p.duracion, -- <-- añade esto
    p.lugar,    -- <-- si también quieres mostrar lugar
    u.nombre_usuario
FROM publicaciones p
INNER JOIN usuarios u ON p.usuario_id = u.id
...

        WHERE p.tipo = 'servicio' 
          AND (p.titulo LIKE ? OR p.descripcion LIKE ?)
          AND p.precio_producto BETWEEN ? AND ?";

// Ordenamiento
switch ($sort) {
    case "name-asc":  $sql .= " ORDER BY p.titulo ASC"; break;
    case "name-desc": $sql .= " ORDER BY p.titulo DESC"; break;
    case "price-asc": $sql .= " ORDER BY p.precio_producto ASC"; break;
    case "price-desc":$sql .= " ORDER BY p.precio_producto DESC"; break;
    default:          $sql .= " ORDER BY p.fecha_publicacion DESC"; break;
}

$sql .= " LIMIT ?, ?";

// Preparar y ejecutar
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["error" => "Error al preparar la consulta: " . $conn->error]);
    exit;
}

$stmt->bind_param("ssddii", $search, $search, $price_min, $price_max, $offset, $items_per_page);
$stmt->execute();
$result = $stmt->get_result();

// Procesar resultados
$servicios = [];
while ($row = $result->fetch_assoc()) {
    $ruta = 'uploads/' . basename($row['imagen']);
    $row['imagen'] = (!empty($row['imagen']) && file_exists($ruta)) ? $ruta : "https://via.placeholder.com/300x200?text=Sin+imagen";
    $row['precio'] = is_numeric($row['precio']) ? (float)$row['precio'] : 0.00;
    $servicios[] = $row;
}

// Total
$total_result = $conn->query("SELECT FOUND_ROWS() AS total");
$total = $total_result ? $total_result->fetch_assoc()['total'] : 0;

// Cierre
$stmt->close();
$conn->close();

// Respuesta
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    "success" => true,
    "servicios" => $servicios,
    "total" => intval($total),
    "page" => $page,
    "items_per_page" => $items_per_page,
    "total_pages" => ceil($total / $items_per_page)
], JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
?>
