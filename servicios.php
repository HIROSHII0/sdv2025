<?php
session_start();
require_once 'conexion.php';

// Validar sesión o cookie (simplificado)
if (!isset($_SESSION['usuario_id']) && isset($_COOKIE['token_login'])) {
    $token = $_COOKIE['token_login'];
    $sql = "SELECT usuario_id FROM tokens WHERE token = ? AND expiracion > NOW() LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($usuario_id);
        $stmt->fetch();
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

// Obtener nombre usuario
$nombreUsuario = "Usuario";
$sql = "SELECT nombre_completo FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$stmt->bind_result($nombre_completo);
if ($stmt->fetch()) {
    $nombreUsuario = $nombre_completo;
}
$stmt->close();

// Paginación
$items_por_pagina = 40; // 4 columnas x 10 filas = 40 servicios
$pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($pagina < 1) $pagina = 1;
$offset = ($pagina - 1) * $items_por_pagina;

// Obtener total de servicios
$sql_total = "SELECT COUNT(*) FROM publicaciones WHERE tipo = 'servicio'";
$result_total = $conn->query($sql_total);
$total_servicios = $result_total->fetch_row()[0];

// Obtener servicios para la página actual
$sql_servicios = "SELECT id, titulo, descripcion, precio, imagen FROM publicaciones WHERE tipo = 'servicio' ORDER BY fecha_publicacion DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql_servicios);
$stmt->bind_param("ii", $items_por_pagina, $offset);
$stmt->execute();
$result = $stmt->get_result();

$servicios = [];
while ($row = $result->fetch_assoc()) {
    // Si no hay imagen, usar placeholder
    $img_url = $row['imagen'] ? $row['imagen'] : "https://via.placeholder.com/300x200?text=Sin+imagen";

    $servicios[] = [
        'id' => $row['id'],
        'titulo' => $row['titulo'],
        'descripcion' => $row['descripcion'],
        'precio' => $row['precio'],
        'imagen' => $img_url
    ];
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Servicios - QuisqueyaClick</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    /* Reset y estilos generales */
    * {
      margin: 0; padding: 0; box-sizing: border-box;
    }
    body {
      font-family: 'Montserrat', sans-serif;
      background: #f9f9fb;
      color: #333;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    header {
      background-color: #111827;
      padding: 1rem 2rem;
    }
    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 1200px;
      margin: 0 auto;
      color: #f9fafb;
    }
    .logo a {
      color: #10b981;
      font-weight: 700;
      font-size: 1.5rem;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .logo a i {
      font-size: 1.6rem;
    }
    ul.menu {
      list-style: none;
      display: flex;
      gap: 2rem;
      font-weight: 600;
    }
    ul.menu li a {
      color: #d1d5db;
      text-decoration: none;
      padding: 0.3rem 0.5rem;
      border-radius: 4px;
      transition: background-color 0.3s ease;
    }
    ul.menu li a:hover,
    ul.menu li a.active {
      background-color: #10b981;
      color: #fff;
    }
    .usuario-info {
      display: flex;
      align-items: center;
      gap: 1rem;
      font-weight: 600;
    }
    .usuario-info span {
      color: #d1d5db;
    }
    .logout-btn {
      background: #10b981;
      padding: 0.4rem 1rem;
      border-radius: 20px;
      color: white;
      font-weight: 700;
      text-decoration: none;
      transition: background-color 0.3s ease;
    }
    .logout-btn:hover {
      background-color: #059669;
    }

    main {
      flex-grow: 1;
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 1rem;
    }
    h1 {
      text-align: center;
      font-weight: 800;
      font-size: 2.5rem;
      margin-bottom: 2rem;
      color: #111827;
      letter-spacing: 0.5px;
    }

    /* Grid de servicios 4 columnas */
    .servicios-lista {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1.8rem;
    }
    .servicio-card {
      background: white;
      border-radius: 10px;
      box-shadow: 0 3px 10px rgb(0 0 0 / 0.1);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transition: transform 0.3s ease;
      cursor: pointer;
    }
    .servicio-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 10px 25px rgb(16 185 129 / 0.25);
    }
    .servicio-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      border-bottom: 2px solid #10b981;
    }
    .servicio-content {
      padding: 1rem 1.2rem;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    .servicio-title {
      font-weight: 700;
      font-size: 1.25rem;
      margin-bottom: 0.6rem;
      color: #111827;
    }
    .servicio-desc {
      flex-grow: 1;
      font-size: 0.9rem;
      color: #4b5563;
      margin-bottom: 1rem;
    }
    .servicio-precio {
      font-weight: 700;
      color: #10b981;
      font-size: 1.1rem;
      text-align: right;
    }

    /* Paginación */
    .pagination {
      margin-top: 2.5rem;
      text-align: center;
    }
    .pagination a {
      display: inline-block;
      margin: 0 0.4rem;
      padding: 0.5rem 0.9rem;
      background-color: #10b981;
      color: white;
      border-radius: 50%;
      font-weight: 700;
      text-decoration: none;
      transition: background-color 0.3s ease;
    }
    .pagination a:hover {
      background-color: #059669;
    }
    .pagination a.active {
      background-color: #047857;
      cursor: default;
    }
  </style>
</head>
<body>

<header>
  <nav class="navbar" role="navigation" aria-label="Menú principal">
    <div class="logo">
      <a href="index.php"><i class="fas fa-store"></i> QuisqueyaClick</a>
    </div>
    <ul class="menu">
      <li><a href="productos.php">Productos</a></li>
      <li><a href="mascotas.php">Mascotas</a></li>
      <li><a href="servicios.php" class="active" aria-current="page">Servicios</a></li>
      <li><a href="perfil.php">Perfil</a></li>
      <li><a href="contacto.php">Contacto</a></li>
    </ul>
    <div class="usuario-info">
      <span aria-label="Usuario"><?php echo htmlspecialchars($nombreUsuario); ?></span>
      <a href="logout.php" class="logout-btn">Cerrar sesión</a>
    </div>
  </nav>
</header>

<main>
  <h1>Servicios Disponibles</h1>

  <section class="servicios-lista" aria-live="polite" aria-label="Listado de servicios disponibles">
    <?php if (count($servicios) === 0): ?>
      <p style="text-align:center; font-size:1.2rem; color:#6b7280;">No hay servicios disponibles.</p>
    <?php else: ?>
      <?php foreach ($servicios as $servicio): ?>
        <article class="servicio-card" tabindex="0" aria-label="<?php echo htmlspecialchars($servicio['titulo']); ?>">
          <img src="<?php echo htmlspecialchars($servicio['imagen']); ?>" alt="Imagen de <?php echo htmlspecialchars($servicio['titulo']); ?>" />
          <div class="servicio-content">
            <h2 class="servicio-title"><?php echo htmlspecialchars($servicio['titulo']); ?></h2>
            <p class="servicio-desc"><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
            <p class="servicio-precio">$<?php echo number_format($servicio['precio'], 2); ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <?php
  $total_paginas = ceil($total_servicios / $items_por_pagina);
  if ($total_paginas > 1): ?>
    <nav class="pagination" aria-label="Paginación servicios">
      <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
        <a href="?page=<?php echo $i; ?>" class="<?php echo ($i === $pagina) ? 'active' : ''; ?>" <?php echo ($i === $pagina) ? 'aria-current="page"' : ''; ?>>
          <?php echo $i; ?>
        </a>
      <?php endfor; ?>
    </nav>
  <?php endif; ?>

</main>

<footer style="background:#111827; color:#d1d5db; padding:1.5rem 0; text-align:center;">
  <div style="max-width:1200px; margin:0 auto; font-size:0.9rem;">
    <p>© 2025 QuisqueyaClick. Todos los derechos reservados.</p>
  </div>
</footer>

</body>
</html>
