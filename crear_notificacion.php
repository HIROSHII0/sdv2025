<?php
function crearNotificacion($conn, $usuario_id, $mensaje, $tipo = 'sistema', $enlace = null, $publicacion_id = null) {
    $query = "INSERT INTO notificaciones (usuario_id, mensaje, tipo, enlace, publicacion_id) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssi", $usuario_id, $mensaje, $tipo, $enlace, $publicacion_id);
    $success = $stmt->execute();
    $notificacion_id = $stmt->insert_id;
    $stmt->close();
    return $notificacion_id;
}
?>