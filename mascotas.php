<?php
session_start();
require_once 'conexion.php';

// Validar sesión usando token si no está activa
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

// Obtener nombre del usuario
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Mascotas - QuisqueyaClick</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="mascotas.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    .productos-lista {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 1.5rem;
    }
    .producto-item.mascota-item {
      border: 1px solid #ddd;
      padding: 1rem;
      border-radius: 8px;
      background-color: #fff;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      max-width: 320px;
      flex: 1 1 280px;
      transition: box-shadow 0.3s ease;
    }
    .producto-item.mascota-item:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .producto-item.mascota-item img {
      max-width: 100%;
      border-radius: 8px;
      margin-bottom: 0.8rem;
      object-fit: cover;
      height: 200px;
      width: 100%;
    }
    .producto-item.mascota-item h2 {
      font-size: 1.25rem;
      margin-bottom: 0.5rem;
      color: #2c3e50;
    }
    .producto-item.mascota-item p {
      margin: 0.3rem 0;
      font-size: 0.9rem;
      color: #555;
    }
    .controls input, .controls select {
      font-size: 1rem;
      padding: 8px;
      border-radius: 5px;
      border: 1px solid #ccc;
      min-width: 200px;
    }
    .pagination button {
      padding: 0.5rem 0.9rem;
      margin: 0 0.2rem;
      border: none;
      border-radius: 6px;
      background: #2563eb;
      color: white;
      cursor: pointer;
    }
    .pagination button:disabled {
      background: #9ca3af;
      cursor: not-allowed;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <header>
    <nav class="navbar">
      <div class="logo">
        <a href="index.php"><i class="fas fa-store"></i> QuisqueyaClick</a>
      </div>
      <ul class="menu">
        <li><a href="productos.php">Productos</a></li>
        <li><a href="mascotas.php" class="active">Mascotas</a></li>
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

  <!-- Hero -->
  <section class="hero-section" style="display:flex; align-items:center; justify-content:center; padding: 2rem; gap: 2rem; flex-wrap: wrap;">
    <div class="hero-text" style="max-width: 500px;">
      <h1>Explora mascotas en adopción</h1>
      <p>Encuentra tu próximo compañero fiel hoy mismo.</p>
      <a href="#mascotas-lista" class="btn-primary" style="padding: 0.6rem 1.2rem; background:#2563eb; color:#fff; border-radius: 6px; text-decoration:none;">Ver Mascotas</a>
    </div>
    <div class="hero-image" style="max-width: 400px;">
      <img src="uploads/mascotas_banner.jpg" alt="Mascotas destacadas" style="width: 100%; border-radius: 12px; object-fit: cover;" />
    </div>
  </section>

  <!-- Contenido -->
  <main style="max-width: 1200px; margin: 2rem auto 4rem; padding: 0 1rem;">
    <h1 style="font-size: 2rem; font-weight: 700; margin-bottom: 1.5rem; color: #111827; text-align: center;">Mascotas disponibles</h1>
    <div class="controls" style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-bottom: 2rem;">
      <input type="text" id="search-input" placeholder="Buscar por nombre o raza..." />
      <select id="sort-select" title="Ordenar mascotas">
        <option value="">Ordenar por</option>
        <option value="name-asc">Nombre (A-Z)</option>
        <option value="name-desc">Nombre (Z-A)</option>
      </select>
    </div>
    <div class="productos-lista" id="mascotas-lista"></div>
    <div class="pagination" id="pagination" style="text-align: center; margin-top: 2rem;"></div>
  </main>

  <!-- Footer -->
  <footer style="background: #1f2937; color: #d1d5db; padding: 1.5rem 0; text-align: center;">
    <div style="max-width: 1200px; margin: 0 auto; font-size: 0.9rem;">
      <p>© 2025 QuisqueyaClick. Todos los derechos reservados.</p>
    </div>
  </footer>

  <!-- Script separado -->
  <script src="mascotas.js"></script>
  <!-- Modal de detalles de mascota -->
<div id="modal-mascota" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:1000;">
  <div style="background:#fff; padding:1.5rem; border-radius:12px; max-width:500px; width:90%; position:relative;">
    <button id="btn-cerrar-modal-mascota" style="position:absolute; top:10px; right:15px; font-size:1.5rem; background:none; border:none;">&times;</button>
    <img id="modal-imagen-mascota" src="" alt="Imagen mascota" style="width:100%; max-height:300px; object-fit:cover; border-radius:12px; margin-bottom:1rem;" />
    <h2 id="modal-titulo-mascota" style="margin-bottom: 1rem;"></h2>
    <p><strong>Edad:</strong> <span id="modal-edad-mascota"></span></p>
    <p><strong>Raza:</strong> <span id="modal-raza-mascota"></span></p>
    <p><strong>Tamaño:</strong> <span id="modal-tamano-mascota"></span></p>
    <p><strong>Descripción:</strong> <span id="modal-descripcion-mascota"></span></p>
    <p><strong>Subido por:</strong> <span id="modal-usuario-mascota"></span></p>
    <p><strong>Precio:</strong> <span id="modal-precio-mascota"></span></p>
  <div style="display:flex; gap: 1rem; margin-top:1.5rem; flex-wrap: wrap;">
  <button id="btn-favorito-mascota" style="flex:1; background: #fbbf24; color:#000; border:none; padding:0.6rem 1rem; border-radius:8px; cursor:pointer;">
    ⭐ Favorito
  </button>
  <button id="btn-guardar-mascota" style="flex:1; background: #3b82f6; color:#fff; border:none; padding:0.6rem 1rem; border-radius:8px; cursor:pointer;">
    💾 Guardar
  </button>
  <button id="btn-adoptar-mascota" style="flex:1; background: #10b981; color:#fff; border:none; padding:0.6rem 1rem; border-radius:8px; cursor:pointer;">
    🐾 Adoptar
  </button>
</div>

  </div>
  
</div>

</body>
</html>
