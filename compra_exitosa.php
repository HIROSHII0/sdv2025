<?php
$servicio = isset($_GET['servicio']) ? htmlspecialchars($_GET['servicio']) : 'el servicio';
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Compra exitosa</title></head>
<body>
<h1>Gracias por su compra</h1>
<p>La compra de <strong><?php echo $servicio; ?></strong> ha sido procesada exitosamente.</p>
<a href="servicios.php">Volver a servicios</a>
</body>
</html>
