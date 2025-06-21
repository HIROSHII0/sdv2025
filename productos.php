<?php
session_start();
require_once 'conexion.php';

// Validar sesión con cookie si no hay sesión activa
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

// Redirigir si no hay sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

// Obtener nombre del usuario desde la base de datos
$nombreUsuario = 'Usuario';
$sql = "SELECT nombre_completo FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$stmt->bind_result($nombre_completo);
if ($stmt->fetch()) {
    $nombreUsuario = $nombre_completo;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Productos - QuisqueyaClick</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="productos.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>

<!-- Navbar -->
<header>
  <nav class="navbar">
    <div class="logo">
      <a href="index.php"><i class="fas fa-store"></i> QuisqueyaClick</a>
    </div>
    <ul class="menu">
      <li><a href="productos.php" class="active">Productos</a></li>
      <li><a href="mascotas.php">Mascotas</a></li>
      <li><a href="servicios.php">Servicios</a></li>
      <li><a href="perfil.php">Perfil</a></li>
      <li><a href="contacto.php">Contacto</a></li>
    </ul>
    <div class="usuario-info">
      <span>👤 <?php echo htmlspecialchars($nombreUsuario); ?></span>
      <a href="logout.php" class="logout-btn">Cerrar sesión</a>
    </div>
    <button class="menu-toggle" aria-label="Abrir menú"><i class="fas fa-bars"></i></button>
  </nav>
</header>

<section class="hero-section">
  <div class="hero-text">
    <h1>Productos de nuestra comunidad</h1>
    <p>Encuentra las mejores ofertas y productos únicos.</p>
    <a href="#productos-lista" class="btn-primary">Ver Productos</a>
  </div>
  <div class="hero-image">
    <img src="uploads/maxresdefault.jpg" alt="Productos destacados" />
  </div>
</section>

<!-- Contenido Principal -->
<main style="max-width: 1200px; margin: 2rem auto 4rem; padding: 0 1rem;">
  <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 1.5rem; color: #111827; text-align: center;">Catálogo de productos</h1>
  
  <div class="controls" style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-bottom: 2rem;">
    <input type="text" id="search-input" placeholder="Buscar productos..." style="font-size: 1rem; padding: 8px;" />
    <select id="sort-select" style="font-size: 1rem; padding: 8px;">
      <option value="">Ordenar por</option>
      <option value="name-asc">Nombre (A-Z)</option>
      <option value="name-desc">Nombre (Z-A)</option>
      <option value="price-asc">Precio (menor a mayor)</option>
      <option value="price-desc">Precio (mayor a menor)</option>
    </select>
    <input type="number" id="price-min" placeholder="Precio mínimo" min="0" style="width: 120px; padding: 8px;" />
    <input type="number" id="price-max" placeholder="Precio máximo" min="0" style="width: 120px; padding: 8px;" />
    <button id="viewToggle" style="cursor: pointer; background-color: #10b981; color: white; border: none; padding: 8px 15px;">Cambiar vista</button>
  </div>

  <div class="productos-lista" id="productos-lista"></div>

  <nav class="paginador" id="pagination" aria-label="Paginación de productos" 
       style="margin-top: 2rem; display: flex; justify-content: center; gap: 0.5rem;">
    <!-- Botones de paginación se generan dinámicamente aquí -->
  </nav>
</main>

<!-- Modal producto -->
<div id="modal-producto" class="modal oculto" role="dialog" aria-modal="true" aria-labelledby="modal-titulo" aria-describedby="modal-descripcion" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index: 1000;">
  <div class="modal-contenido" style="background:#fff; padding: 1.5rem; border-radius: 12px; max-width: 480px; width: 90%; position: relative;">
    <button id="btn-cerrar-modal" aria-label="Cerrar modal" style="position:absolute; top:10px; right:15px; font-size:1.5rem; background:none; border:none; cursor:pointer;">&times;</button>
    <img id="modal-imagen" src="" alt="Imagen producto" style="width: 100%; max-height: 300px; object-fit: contain; border-radius: 12px; margin-bottom: 1rem;" />
    <h2 id="modal-titulo"></h2>
    <p id="modal-descripcion"></p>
    <p><strong>Precio:</strong> <span id="modal-precio"></span></p>

    <div style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: center;">
      <button id="btn-favorito" class="btn-tertiary" aria-label="Agregar a favoritos"><i class="fas fa-heart"></i> Favorito</button>
      <button id="btn-comprar" class="btn-tertiary" aria-label="Comprar producto"><i class="fas fa-shopping-cart"></i> Comprar</button>
      <button id="btn-guardar" class="btn-tertiary" aria-label="Guardar producto"><i class="fas fa-bookmark"></i> Guardar</button>
    </div>
  </div>
</div>

<!-- Footer -->
<footer style="background: #1f2937; color: #d1d5db; padding: 1.5rem 0; text-align: center;">
  <div style="max-width: 1200px; margin: 0 auto; font-size: 0.9rem;">
    <p>© 2025 QuisqueyaClick. Todos los derechos reservados.</p>
  </div>
</footer>

<!-- JS -->
<script src="productos.js"></script>
</body>
</html>
