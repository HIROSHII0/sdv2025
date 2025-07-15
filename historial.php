<?php
session_start();
require_once 'conexion.php';

// Validar sesión o token
if (!isset($_SESSION['usuario_id']) && isset($_COOKIE['token_login'])) {
    $token = $_COOKIE['token_login'];
    $stmt = $conn->prepare("SELECT usuario_id FROM tokens WHERE token = ? AND expiracion > NOW() LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->bind_result($usuario_id);
    if ($stmt->fetch()) {
        $_SESSION['usuario_id'] = $usuario_id;
    } else {
        setcookie('token_login', '', time() - 3600, '/');
    }
    $stmt->close();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

// Establecer charset utf8 para conexión
$conn->set_charset("utf8");

// Obtener nombre del usuario
$nombre = 'Usuario';
$stmt = $conn->prepare("SELECT nombre_completo FROM usuarios WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$stmt->bind_result($nombre_usuario);
if ($stmt->fetch()) {
    $nombre = $nombre_usuario;
}
$stmt->close();

// Cargar historial de compras
$sqlHistorial = "SELECT hc.id, hc.fecha_compra, hc.precio, p.titulo 
                 FROM historial_compras hc 
                 INNER JOIN publicaciones p ON hc.producto_id = p.id 
                 WHERE hc.usuario_id = ? 
                 ORDER BY hc.fecha_compra DESC";
$stmtHistorial = $conn->prepare($sqlHistorial);

if ($stmtHistorial === false) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

$stmtHistorial->bind_param("i", $_SESSION['usuario_id']);
$stmtHistorial->execute();
$resultHistorial = $stmtHistorial->get_result();

$compras = [];
while ($row = $resultHistorial->fetch_assoc()) {
    $compras[] = $row;
}

$stmtHistorial->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Historial de Compras | QuisqueyaClick</title>
<link rel="stylesheet" href="perfil.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
  body { font-family: 'Montserrat', sans-serif; background: #f9f9fb; margin: 0; padding: 0; }
  .navbar { background: #111827; color: white; padding: 1rem; display: flex; justify-content: space-between; align-items: center; }
  .navbar .logo a { color: #10b981; font-size: 1.4rem; font-weight: bold; text-decoration: none; }
  .navbar ul { list-style: none; display: flex; gap:1.5rem; margin:0; padding:0; }
  .navbar ul li a { color:#d1d5db; text-decoration:none; font-weight:600; padding:.3rem .6rem; border-radius:4px; transition:.3s; }
  .navbar ul li a:hover, .navbar ul li a.active { color:#fff; background:#10b981; }
  .usuario-info { display:flex; gap:1rem; align-items:center; font-weight:600; position:relative; }
  .notification-icon { position:relative; cursor:pointer; }
  .notification-icon .notificacion-count {
    position:absolute; top:-5px; right:-10px; background:red; color:#fff; border-radius:50%; padding:2px 6px; font-size:.75rem;
    font-weight:700; user-select:none;
  }
  .notificaciones-dropdown {
    display:none; position:absolute; right:0; top:2.5rem; background:#fff; color:#111; width:320px;
    box-shadow:0 4px 8px rgba(0,0,0,.15); border-radius:6px; z-index:1000; max-height:300px; overflow-y:auto;
  }
  .notificacion-item { padding:.5rem 1rem; border-bottom:1px solid #eee; font-size:.9rem; }
  .notificacion-item:last-child { border-bottom:none; }
  .logout-btn { background:#10b981; color:#fff; padding:.3rem 1rem; border-radius:20px; text-decoration:none; font-weight:700; transition:.3s; }
  .logout-btn:hover { background:#059669; }
  main { max-width:1200px; margin:2rem auto; padding:0 1rem; }
  h1 { text-align:center; font-size:2rem; margin-bottom:1.5rem; color:#111827; }
  .historial-container { padding: 20px; }
  .compra { border: 1px solid #ccc; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; background: #fff; }
  .compra h3 { margin: 0; }
  .compra p { margin: 0.5rem 0; }
</style>
</head>
<body>

<header>
  <div class="logo"><a href="index.php"><i>Quisqueyaclick</i></a></div>
  <nav>
    <ul>
      <li><a href="productos.php">Productos</a></li>
      <li><a href="mascotas.php">Mascotas</a></li>
      <li><a href="servicios.php">Servicios</a></li>
      <li><a href="perfil.php">Perfil</a></li>
      <li><a href="ajustes.php">Ajustes</a></li>
    </ul>
  </nav>
  <div class="usuario-info">
    👤 <?php echo htmlspecialchars($nombre); ?>
    <a href="logout.php" class="logout-btn" id="btn-logout">Cerrar sesión</a>
  </div>
</header>

<main>
  <h1>Historial de Compras</h1>
  <div class="historial-container">
    <?php if (empty($compras)): ?>
      <p>No has realizado ninguna compra aún.</p>
    <?php else: ?>
      <?php foreach ($compras as $compra): ?>
        <div class="compra">
          <h3><?php echo htmlspecialchars($compra['titulo']); ?></h3>
          <p><strong>Precio:</strong> $<?php echo number_format($compra['precio'], 2); ?></p>
          <p><strong>Fecha de compra:</strong> <?php echo date("d/m/Y", strtotime($compra['fecha_compra'])); ?></p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</main>

</body>
</html>
