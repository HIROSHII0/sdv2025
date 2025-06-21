<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$search = isset($_GET['search']) ? "%{$_GET['search']}%" : '%';
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
$price_min = isset($_GET['price_min']) ? floatval($_GET['price_min']) : 0;
$price_max = isset($_GET['price_max']) ? floatval($_GET['price_max']) : 1000000;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$items_per_page = 6;
$offset = ($page - 1) * $items_per_page;

// Query: filtrar por tipo servicio, buscar en titulo y descripcion, y rango precio_producto (según tu estructura)
$sql = "SELECT SQL_CALC_FOUND_ROWS id, titulo, descripcion, imagen, precio_producto AS precio
        FROM publicaciones
        WHERE tipo = 'servicio' 
          AND (titulo LIKE ? OR descripcion LIKE ?)
          AND precio_producto BETWEEN ? AND ?
        ";

$order_sql = "";
switch ($sort) {
    case "name-asc": $order_sql = "ORDER BY titulo ASC"; break;
    case "name-desc": $order_sql = "ORDER BY titulo DESC"; break;
    case "price-asc": $order_sql = "ORDER BY precio_producto ASC"; break;
    case "price-desc": $order_sql = "ORDER BY precio_producto DESC"; break;
    default: $order_sql = "ORDER BY fecha_publicacion DESC"; break;
}

$sql .= " $order_sql LIMIT ?, ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssddii", $search, $search, $price_min, $price_max, $offset, $items_per_page);
$stmt->execute();
$result = $stmt->get_result();

$servicios = [];
while ($row = $result->fetch_assoc()) {
    // Si no tiene imagen, poner una por defecto
    if (empty($row['imagen'])) {
        $row['imagen'] = 'assets/img/default-service.jpg'; // Ajusta ruta a imagen por defecto
    }
    $servicios[] = $row;
}

$total_result = $conn->query("SELECT FOUND_ROWS() AS total");
$total = $total_result->fetch_assoc()['total'];

$stmt->close();

header('Content-Type: application/json');
echo json_encode([
    "servicios" => $servicios,
    "total" => intval($total),
    "page" => $page,
    "items_per_page" => $items_per_page
]);
