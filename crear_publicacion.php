<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    die(json_encode(['success' => false, 'message' => 'No autorizado']));
}

$response = ['success' => false, 'message' => ''];

try {
    // ────────────────────────────
    // 1. Validación básica de campos requeridos
    // ────────────────────────────
    $requiredFields = ['tipo', 'titulo', 'descripcion'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            die(json_encode([
                'success' => false,
                'message' => "El campo $field es requerido"
            ]));
        }
    }

    $usuario_id  = $_SESSION['usuario_id'];
    $tipo        = $_POST['tipo'];
    $titulo      = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];

    // ────────────────────────────
    // 2. Validación específica para servicios
    // ────────────────────────────
    if ($tipo === 'servicio') {
        if (empty($_POST['duracion']) || empty($_POST['lugar'])) {
            die(json_encode([
                'success' => false,
                'message' => 'Para servicios, la duración y el lugar son obligatorios'
            ]));
        }
    }

    // ────────────────────────────
    // 3. Gestión de la imagen
    // ────────────────────────────
    $imagen = 'logo.jpg'; // Imagen por defecto

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $directorio = 'uploads/';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }

        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $ext_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $ext_permitidas)) {
            // Validar tamaño máximo (5MB)
            if ($_FILES['imagen']['size'] <= 5 * 1024 * 1024) {
                $nombreArchivo = 'pub_' . $usuario_id . '_' . time() . '.' . $ext;
                $ruta = $directorio . $nombreArchivo;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)) {
                    $imagen = $nombreArchivo;
                }
            } else {
                $response['message'] = 'La imagen es demasiado grande (máximo 5MB)';
                echo json_encode($response);
                exit;
            }
        }
    }

    // ────────────────────────────
    // 4. Procesamiento de campos opcionales
    // ────────────────────────────
    $edad = !empty($_POST['edad']) ? intval($_POST['edad']) : null;
    $raza = $_POST['raza'] ?? null;
    $tamano = $_POST['tamano'] ?? null;
    $vacunado = isset($_POST['vacunado']) ? 1 : 0;
    $desparasitado = isset($_POST['desparasitado']) ? 1 : 0;
    $duracion = $_POST['duracion'] ?? null;
    $lugar = $_POST['lugar'] ?? null;
    $precio = !empty($_POST['precio']) ? floatval($_POST['precio']) : null;
    $estado_producto = $_POST['estado_producto'] ?? null;
    $categoria_producto = $_POST['categoria_producto'] ?? null;
    
$vacunado = isset($_POST['vacunado']) ? (int)$_POST['vacunado'] : null;
$desparasitado = isset($_POST['desparasitado']) ? (int)$_POST['desparasitado'] : null;


    // Si no es un servicio, limpiar campos específicos
    if ($tipo !== 'servicio') {
        $duracion = null;
        $lugar = null;
    }

    // ────────────────────────────
    // 5. Preparar y ejecutar la consulta SQL
    // ────────────────────────────
    $stmt = $conn->prepare(
        "INSERT INTO publicaciones (
            usuario_id, tipo, titulo, descripcion, imagen,
            edad, raza, tamano, vacunado, desparasitado,
            duracion, lugar, precio, estado_producto, categoria_producto,
            fecha_publicacion, visible
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?,
            NOW(), 1
        )"
    );

    if (!$stmt) {
        throw new Exception('Error en prepare: ' . $conn->error);
    }

    // Tipos: i = entero, s = string, d = double
    $stmt->bind_param(
        "issssssssiissss",
        $usuario_id,
        $tipo,
        $titulo,
        $descripcion,
        $imagen,
        $edad,
        $raza,
        $tamano,
        $vacunado,
        $desparasitado,
        $duracion,
        $lugar,
        $precio,
        $estado_producto,
        $categoria_producto
    );

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Publicación creada con éxito';
        $response['id'] = $stmt->insert_id;
    } else {
        throw new Exception('Error al ejecutar: ' . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error en crear_publicacion.php: ' . $e->getMessage());
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($response);
?>