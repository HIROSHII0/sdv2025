<?php
session_start();
require_once 'conexion.php';

// Para desarrollo, mostrar errores (quitar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validar sesión o token
if (!isset($_SESSION['usuario_id'])) {
    if (isset($_COOKIE['token_login'])) {
        $token = $_COOKIE['token_login'];

        $stmt = $conn->prepare("SELECT usuario_id FROM tokens WHERE token = ? AND expiracion > NOW() LIMIT 1");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $_SESSION['usuario_id'] = $result->fetch_assoc()['usuario_id'];
        } else {
            setcookie('token_login', '', time() - 3600, '/', '', true, true);
            header('Location: login.html');
            exit;
        }
        $stmt->close();
    } else {
        header('Location: login.html');
        exit;
    }
}

// Inicializar variables
$usuario = [];
$nombre = 'Usuario';

// Obtener datos del usuario
try {
    $stmt = $conn->prepare("SELECT nombre_completo, correo FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        $nombre = htmlspecialchars($usuario['nombre_completo'], ENT_QUOTES, 'UTF-8');
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error fetching user data: " . $e->getMessage());
}

// Cargar notificaciones
$notificaciones = [];
try {
    $stmt = $conn->prepare("SELECT id, mensaje, fecha, leida 
                            FROM notificaciones 
                            WHERE usuario_id = ? 
                            ORDER BY fecha DESC 
                            LIMIT 10");

    if (!$stmt) {
        die("Error en la consulta SQL: " . $conn->error);
    }

    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $fecha = new DateTime($row['fecha']);
        $row['fecha_formateada'] = $fecha->format('d/m/Y H:i');
        $row['icono'] = 'fas fa-bell'; // Icono genérico
        $notificaciones[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Error loading notifications: " . $e->getMessage());
}

// Contar notificaciones no leídas
$no_leidas = 0;
foreach ($notificaciones as $notif) {
    if (isset($notif['leida']) && $notif['leida'] == 0) {
        $no_leidas++;
    }
}

$conn->close();

// Enviar respuesta JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'usuario' => $nombre,
    'notificaciones' => $notificaciones,
    'no_leidas' => $no_leidas
]);
