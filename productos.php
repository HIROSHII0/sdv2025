<?php
session_start();
require_once 'conexion.php';

// Validar sesión o cookie
if (!isset($_SESSION['usuario_id'])) {
    if (isset($_COOKIE['token_login'])) {
        $token = $_COOKIE['token_login'];
        $query = $conn->query("SELECT usuario_id FROM tokens WHERE token = '$token' AND expiracion > NOW() LIMIT 1");
        if ($query->num_rows > 0) {
            $_SESSION['usuario_id'] = $query->fetch_assoc()['usuario_id'];
        } else {
            setcookie('token_login', '', time() - 3600, '/');
            header('Location: login.html');
            exit;
        }
    } else {
        header('Location: login.html');
        exit;
    }
}

// Obtener datos del usuario
$usuario = $conn->query("SELECT nombre_completo FROM usuarios WHERE id = {$_SESSION['usuario_id']} LIMIT 1")->fetch_assoc();
$nombre = $usuario['nombre_completo'] ?? 'Usuario';

// Cargar notificaciones no leídas
$notificaciones = $conn->query("SELECT id, mensaje, fecha, leida FROM notificaciones WHERE usuario_id = {$_SESSION['usuario_id']} ORDER BY fecha DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Productos | QuisqueyaClick</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* === Reset y configuración general === */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Montserrat', sans-serif;
      background: #f9f9fb;
      color: #333;
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* === Navbar === */
    header {
      background: #1f2937;
      position: sticky;
      top: 0;
      z-index: 999;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    .navbar {
      max-width: 1200px;
      margin: 0 auto;
      padding: 1rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      color: #fff;
    }

    .logo a {
      font-size: 1.6rem;
      font-weight: 700;
      color: #10b981;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .logo i {
      font-size: 1.8rem;
      color: #34d399;
      transition: transform 0.3s ease;
    }

    .logo a:hover i {
      transform: rotate(20deg);
    }

    .menu {
      list-style: none;
      display: flex;
      gap: 1.5rem;
    }

    .menu li a {
      color: #e5e7eb;
      font-weight: 600;
      text-decoration: none;
      padding: 0.5rem 0.75rem;
      border-radius: 6px;
      transition: background-color 0.3s ease, color 0.3s ease;
      position: relative;
    }

    .menu li a.active,
    .menu li a:hover {
      background-color: #10b981;
      color: #fff;
      box-shadow: 0 4px 8px rgba(16, 185, 129, 0.4);
    }

    .usuario-info {
      display: flex;
      align-items: center;
      gap: 1rem;
      font-weight: 600;
    }

    .usuario-info span {
      color: #e5e7eb;
    }

    .logout-btn {
      color: #e5e7eb;
      background-color: #10b981;
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      text-decoration: none;
      font-weight: 700;
      transition: background-color 0.3s ease;
    }

    .logout-btn:hover {
      background-color: #059669;
    }

    /* === Notificaciones === */
    .notification-icon {
      position: relative;
      cursor: pointer;
      margin-right: 15px;
      font-size: 1.2rem;
    }

    .notificacion-count {
      background-color: #ef4444;
      color: white;
      font-size: 0.75rem;
      font-weight: bold;
      padding: 0.2em 0.5em;
      border-radius: 50%;
      position: absolute;
      top: -5px;
      right: -5px;
      display: none;
    }

    .notificaciones-dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 2.5rem;
      background: white;
      color: #111;
      width: 320px;
      box-shadow: 0 4px 8px rgba(0,0,0,.15);
      border-radius: 6px;
      z-index: 1000;
      max-height: 400px;
      overflow-y: auto;
    }

    .notificacion-item {
      padding: .75rem 1rem;
      border-bottom: 1px solid #eee;
      font-size: .9rem;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .notificacion-item:hover {
      background-color: #f5f5f5;
    }

    .notificacion-item.no-leida {
      background-color: #f0fdf4;
      font-weight: 500;
    }

    .notificacion-item p {
      margin-bottom: 0.25rem;
    }

    .notificacion-item small {
      color: #666;
      font-size: 0.8rem;
    }

    .notificaciones-header {
      padding: 1rem;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #f9f9f9;
    }

    .notificaciones-header h3 {
      margin: 0;
      font-size: 1.1rem;
    }

    .cerrar-notificaciones {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: #666;
    }

   /* === Reset y configuración general === */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    

    body {
      font-family: 'Montserrat', sans-serif;
      background: #f9f9fb;
      color: #333;
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* === Navbar === */
    header {
      background: #1f2937;
      position: sticky;
      top: 0;
      z-index: 999;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    .navbar {
      max-width: 1200px;
      margin: 0 auto;
      padding: 1rem 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      color: #fff;
    }

    .logo a {
      font-size: 1.6rem;
      font-weight: 700;
      color: #10b981;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .logo i {
      font-size: 1.8rem;
      color: #34d399;
      transition: transform 0.3s ease;
    }

    .logo a:hover i {
      transform: rotate(20deg);
    }

    .menu {
      list-style: none;
      display: flex;
      gap: 1.5rem;
    }

    .menu li a {
      color: #e5e7eb;
      font-weight: 600;
      text-decoration: none;
      padding: 0.5rem 0.75rem;
      border-radius: 6px;
      transition: background-color 0.3s ease, color 0.3s ease;
      position: relative;
    }

    .menu li a.active,
    .menu li a:hover {
      background-color: #10b981;
      color: #fff;
      box-shadow: 0 4px 8px rgba(16, 185, 129, 0.4);
    }

    .usuario-info {
      display: flex;
      align-items: center;
      gap: 1rem;
      font-weight: 600;
    }

    .usuario-info span {
      color: #e5e7eb;
    }

    .logout-btn {
      color: #e5e7eb;
      background-color: #10b981;
      padding: 0.3rem 0.8rem;
      border-radius: 20px;
      text-decoration: none;
      font-weight: 700;
      transition: background-color 0.3s ease;
    }

    .logout-btn:hover {
      background-color: #059669;
    }

    /* === Notificaciones === */
    .notification-icon {
      position: relative;
      cursor: pointer;
      margin-right: 15px;
    }

    .notificacion-count {
      background-color: #ef4444;
      color: white;
      font-size: 0.75rem;
      font-weight: bold;
      padding: 0.2em 0.5em;
      border-radius: 50%;
      position: absolute;
      top: -5px;
      right: -5px;
    }

    .notificaciones-dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 2.5rem;
      background: white;
      color: #111;
      width: 320px;
      box-shadow: 0 4px 8px rgba(0,0,0,.15);
      border-radius: 6px;
      z-index: 1000;
      max-height: 400px;
      overflow-y: auto;
    }

    .notificacion-item {
      padding: .75rem 1rem;
      border-bottom: 1px solid #eee;
      font-size: .9rem;
      cursor: pointer;
      transition: background-color 0.2s;
    }

    .notificacion-item:hover {
      background-color: #f5f5f5;
    }

    .notificacion-item p {
      margin-bottom: 0.25rem;
    }

    .notificacion-item small {
      color: #666;
      font-size: 0.8rem;
    }

    .notificaciones-header {
      padding: 1rem;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #f9f9f9;
    }

    .notificaciones-header h3 {
      margin: 0;
      font-size: 1.1rem;
    }

    .cerrar-notificaciones {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: #666;
    }

    /* === Hero Section === */
    .hero-section {
      max-width: 1200px;
      margin: 2rem auto 4rem;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 2rem;
      padding: 0 1rem;
    }

    .hero-text {
      flex: 1 1 400px;
      animation: fadeInLeft 1s ease forwards;
    }

    .hero-text h1 {
      font-size: 2.75rem;
      font-weight: 800;
      margin-bottom: 1rem;
      color: #111827;
      line-height: 1.1;
    }

    .hero-text p {
      font-size: 1.15rem;
      margin-bottom: 2rem;
      color: #374151;
    }

    .btn-primary {
      background-color: #10b981;
      color: white;
      padding: 0.75rem 2rem;
      border: none;
      border-radius: 30px;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
      transition: background-color 0.3s ease, transform 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn-primary:hover {
      background-color: #059669;
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(5, 150, 105, 0.5);
    }

    .hero-image {
      flex: 1 1 400px;
      text-align: center;
      animation: fadeInRight 1s ease forwards;
    }

    .hero-image img {
      width: 100%;
      max-width: 450px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(16, 185, 129, 0.25);
      transition: transform 0.4s ease;
    }

    .hero-image img:hover {
      transform: scale(1.05) rotate(1.5deg);
    }

    /* === Controls Section (Filtros y búsqueda) === */
    .controls {
      max-width: 1200px;
      margin: 0 auto 2rem;
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
      padding: 0 1rem;
    }

    .controls input[type="text"],
    .controls select,
    .controls input[type="number"] {
      padding: 0.6rem 1rem;
      font-size: 1rem;
      border-radius: 30px;
      border: 2px solid #10b981;
      outline: none;
      transition: border-color 0.3s ease;
      min-width: 180px;
    }

    .controls input[type="text"]:focus,
    .controls select:focus,
    .controls input[type="number"]:focus {
      border-color: #059669;
      box-shadow: 0 0 8px #10b981aa;
    }

    #viewToggle {
      background-color: #10b981;
      color: white;
      border: none;
      padding: 0.6rem 1.2rem;
      border-radius: 30px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    #viewToggle:hover {
      background-color: #059669;
    }

    /* === Productos List === */
    .productos-lista {
      max-width: 1200px;
      margin: 0 auto 4rem;
      padding: 0 1rem;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 1.5rem;
    }

    /* Vista de lista */
    .productos-lista.list-view {
      grid-template-columns: 1fr;
    }

    .productos-lista.list-view .producto-item {
      flex-direction: row;
      min-height: auto;
      padding-bottom: 0;
    }

    .productos-lista.list-view .producto-item img {
      width: 180px;
      height: 180px;
      border-bottom: none;
      border-right: 3px solid #10b981;
    }

    .productos-lista.list-view .producto-detalle {
      padding: 1.5rem;
    }

    /* Estilos generales para cada producto */
    .producto-item {
      background: white;
      border-radius: 20px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
      user-select: none;
      min-height: 350px;
      padding-bottom: 1rem;
    }

    .producto-item:hover {
      transform: translateY(-8px);
      box-shadow: 0 18px 50px rgba(16, 185, 129, 0.3);
    }

    .producto-item img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-bottom: 3px solid #10b981;
      transition: transform 0.4s ease;
    }

    .producto-item:hover img {
      transform: scale(1.1);
    }

    .producto-detalle {
      padding: 1rem;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }

    .producto-detalle h3 {
      color: #059669;
      font-weight: 700;
      font-size: 1.25rem;
      margin-bottom: 0.5rem;
    }

    .producto-detalle p {
      font-size: 0.95rem;
      color: #555;
      margin-bottom: 0.5rem;
      flex-grow: 1;
    }

    .precio {
      font-weight: bold;
      color: #10b981;
      font-size: 1.2rem;
      margin: 0.5rem 0;
    }

    .btn-comprar {
      background-color: #10b981;
      color: white;
      border: none;
      padding: 0.7rem;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin-top: auto;
    }

    .btn-comprar:hover {
      background-color: #059669;
    }

    /* === Pagination === */
    .paginador {
      max-width: 1200px;
      margin: 0 auto 3rem;
      display: flex;
      justify-content: center;
      gap: 0.5rem;
      flex-wrap: wrap;
      padding: 0 1rem;
    }

    .paginador button {
      background-color: #10b981;
      border: none;
      color: white;
      padding: 0.5rem 0.9rem;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
      transition: background-color 0.3s ease;
    }

    .paginador button:hover:not(:disabled) {
      background-color: #059669;
      box-shadow: 0 8px 20px rgba(5, 150, 105, 0.5);
    }

    .paginador button:disabled {
      background-color: #a5d6a7;
      cursor: default;
    }

    .paginador button.active {
      background-color: #059669;
      box-shadow: 0 10px 25px rgba(5, 150, 105, 0.7);
      cursor: default;
    }

    /* === Modal detalle producto === */
    .modal-backdrop {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0,0,0,0.6);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1100;
      padding: 1rem;
    }

    .modal-backdrop.active {
      display: flex;
      animation: fadeIn 0.3s ease forwards;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .modal-content {
      background: white;
      border-radius: 15px;
      max-width: 500px;
      width: 100%;
      box-shadow: 0 15px 40px rgba(16, 185, 129, 0.3);
      overflow: hidden;
      animation: scaleIn 0.3s ease forwards;
    }

    @keyframes scaleIn {
      from { transform: scale(0.9); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }

    .modal-header {
      background: #10b981;
      color: white;
      padding: 1rem 1.5rem;
      font-size: 1.3rem;
      font-weight: 600;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-close-btn {
      background: transparent;
      border: none;
      font-size: 1.8rem;
      color: white;
      cursor: pointer;
      transition: color 0.3s ease;
    }

    .modal-close-btn:hover {
      color: #d1fae5;
    }

    .modal-body {
      padding: 1.5rem;
    }

    .modal-img {
      width: 100%;
      max-height: 300px;
      object-fit: contain;
      border-radius: 8px;
      margin-bottom: 1.5rem;
    }

    .modal-info h2 {
      color: #10b981;
      font-weight: 700;
      font-size: 1.8rem;
      margin-bottom: 0.5rem;
    }

    .modal-info p {
      margin-bottom: 1rem;
      color: #444;
    }

    .modal-info .precio {
      font-size: 1.5rem;
      font-weight: 700;
      color: #10b981;
      margin: 1rem 0;
    }

    .modal-actions {
      display: flex;
      gap: 1rem;
      margin-top: 1.5rem;
    }

    .modal-actions button {
      flex: 1;
      padding: 0.8rem;
      font-weight: 600;
      font-size: 1rem;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .btn-adoptar {
      background-color: #10b981;
      color: white;
    }

    .btn-adoptar:hover {
      background-color: #059669;
    }

    .btn-favorito {
      background-color: #f59e0b;
      color: white;
    }

    .btn-favorito:hover {
      background-color: #d97706;
    }

    /* === Animaciones === */
    @keyframes fadeInLeft {
      from { opacity: 0; transform: translateX(-40px); }
      to { opacity: 1; transform: translateX(0); }
    }

    @keyframes fadeInRight {
      from { opacity: 0; transform: translateX(40px); }
      to { opacity: 1; transform: translateX(0); }
    }

    /* === Responsive === */
    @media (max-width: 900px) {
      .hero-section {
        flex-direction: column;
      }

      .hero-text h1 {
        font-size: 2.25rem;
      }

      .productos-lista {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      }
    }

    @media (max-width: 768px) {
      .navbar {
        flex-wrap: wrap;
        padding: 1rem;
      }

      .menu {
        flex-direction: column;
        width: 100%;
        margin-top: 1rem;
        display: none;
      }

      .menu.open {
        display: flex;
      }

      .menu-toggle {
        display: block;
      }

      .controls {
        flex-direction: column;
        align-items: stretch;
      }

      .controls input,
      .controls select,
      .controls button {
        width: 100%;
      }

      .productos-lista.list-view .producto-item {
        flex-direction: column;
      }

      .productos-lista.list-view .producto-item img {
        width: 100%;
        height: 200px;
        border-right: none;
        border-bottom: 3px solid #10b981;
      }
    }
  </style>
</head>
<body>
  <header>
    <nav class="navbar">
      <div class="logo">
        <a href="index.php"><i class="fas fa-store"></i> QuisqueyaClick</a>
      </div>
      
      <ul class="menu" id="main-menu">
        <li><a href="productos.php" class="active">Productos</a></li>
        <li><a href="mascotas.php">Mascotas</a></li>
        <li><a href="servicios.php">Servicios</a></li>
        <li><a href="perfil.php">Perfil</a></li>
      </ul>
      
      <div class="usuario-info">
        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($nombre); ?></span>
        <div class="notification-icon" id="btn-notificaciones">
          <i class="fas fa-bell"></i>
          <span class="notificacion-count" id="notificacion-count"></span>
          <div class="notificaciones-dropdown" id="notificaciones-dropdown">
            <div class="notificaciones-header">
              <h3>Notificaciones</h3>
              <button class="cerrar-notificaciones">&times;</button>
            </div>
            <div id="notificaciones-list">
              <?php if (count($notificaciones) > 0): ?>
                <?php foreach($notificaciones as $n): ?>
                  <div class="notificacion-item <?php echo $n['leida'] == 0 ? 'no-leida' : ''; ?>" data-id="<?php echo $n['id']; ?>">
                    <p><?php echo htmlspecialchars($n['mensaje']); ?></p>
                    <small><?php echo date('d/m/Y H:i', strtotime($n['fecha'])); ?></small>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="notificacion-item">No hay notificaciones nuevas</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <a href="logout.php" class="logout-btn">Cerrar sesión</a>
      </div>
      
      <button class="menu-toggle" id="menu-toggle" aria-label="Abrir menú">
        <i class="fas fa-bars"></i>
      </button>
    </nav>
  </header>

  <main class="hero-section">
    <div class="hero-text">
      <h1>Explora nuestros productos</h1>
      <p>Encuentra artículos únicos publicados por otros usuarios. Compara precios, descubre ofertas y adquiere lo que necesitas en un solo lugar.</p>
      <a href="perfil.php" class="btn-primary">Publicar producto</a>
    </div>
    <div class="hero-image">
    <img src="imagenes/logo.jpg" class="activa" alt="Productos">
    </div>
  </main>

  <section class="controls" id="controls">
    <input type="text" id="search-input" placeholder="Buscar productos...">
    <select id="sort-select">
      <option value="">Ordenar por</option>
      <option value="name-asc">Nombre (A-Z)</option>
      <option value="name-desc">Nombre (Z-A)</option>
      <option value="price-asc">Precio (menor a mayor)</option>
      <option value="price-desc">Precio (mayor a menor)</option>
    </select>
    <input type="number" id="price-min" placeholder="Precio mínimo" min="0">
    <input type="number" id="price-max" placeholder="Precio máximo" min="0">
    <button id="viewToggle">Ver Lista</button>
  </section>

  <section class="productos-lista grid-view" id="productos-lista">
    <!-- Los productos se cargarán aquí con JavaScript -->
  </section>

  <nav id="pagination" class="paginador"></nav>

  <!-- Modal de producto -->
  <div id="modal" class="modal-backdrop" role="dialog">
    <div class="modal-content">
      <header class="modal-header">
        <h2>Detalle Producto</h2>
        <button id="modal-close" class="modal-close-btn">&times;</button>
      </header>
      <div class="modal-body">
        <img src="" alt="" class="modal-img" id="modal-img">
        <div class="modal-info">
          <h2 id="modal-nombre"></h2>
          <p id="modal-descripcion"></p>
          <p><strong>Vendido por:</strong> <span id="modal-vendedor"></span></p>
          <p class="precio" id="modal-precio"></p>
          <div class="modal-actions">
            <button class="btn-adoptar" id="btn-comprar">
              <i class="fas fa-shopping-cart"></i> Comprar
            </button>
            <button class="btn-favorito" id="btn-favorito">
              <i class="far fa-heart"></i> Favorito
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Variables globales
      const productosPorPagina = 12;
      let paginaActual = 1;
      let productos = [];
      let vistaGrid = true;
      
      // Elementos del DOM
      const contenedor = document.getElementById("productos-lista");
      const paginacion = document.getElementById("pagination");
      const searchInput = document.getElementById("search-input");
      const sortSelect = document.getElementById("sort-select");
      const priceMin = document.getElementById("price-min");
      const priceMax = document.getElementById("price-max");
      const viewToggle = document.getElementById("viewToggle");
      const menuToggle = document.getElementById("menu-toggle");
      const mainMenu = document.getElementById("main-menu");
      const notifIcon = document.getElementById("btn-notificaciones");
      const notifDropdown = document.getElementById("notificaciones-dropdown");
      const notifList = document.getElementById("notificaciones-list");
      const notifCount = document.getElementById("notificacion-count");
      
      // Modal elementos
      const modal = document.getElementById("modal");
      const modalCloseBtn = document.getElementById("modal-close");
      const modalImg = document.getElementById("modal-img");
      const modalNombre = document.getElementById("modal-nombre");
      const modalDescripcion = document.getElementById("modal-descripcion");
      const modalPrecio = document.getElementById("modal-precio");
      const btnComprar = document.getElementById("btn-comprar");
      const btnFavorito = document.getElementById("btn-favorito");
      
      // Cargar productos
      function cargarProductos() {
        fetch("get_productos.php")
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              productos = data.productos;
              mostrarProductos();
            } else {
              contenedor.innerHTML = `<p style="text-align:center; color:#666;">${data.message || "No se pudieron cargar los productos."}</p>`;
            }
          })
          .catch(error => {
            console.error("Error:", error);
            contenedor.innerHTML = "<p style='color:red; text-align:center;'>Error al cargar los productos.</p>";
          });
      }
      
      // Filtrar y ordenar productos
      function filtrarYOrdenar() {
        const texto = searchInput.value.toLowerCase();
        const orden = sortSelect.value;
        const min = parseFloat(priceMin.value) || 0;
        const max = parseFloat(priceMax.value) || Infinity;
        
        let filtrados = productos.filter(p => {
          if (texto && !p.titulo.toLowerCase().includes(texto)) return false;
          if (p.precio < min) return false;
          if (p.precio > max) return false;
          return true;
        });
        
        switch (orden) {
          case "name-asc":
            filtrados.sort((a, b) => a.titulo.localeCompare(b.titulo));
            break;
          case "name-desc":
            filtrados.sort((a, b) => b.titulo.localeCompare(a.titulo));
            break;
          case "price-asc":
            filtrados.sort((a, b) => a.precio - b.precio);
            break;
          case "price-desc":
            filtrados.sort((a, b) => b.precio - a.precio);
            break;
        }
        
        return filtrados;
      }
      
      // Mostrar productos en la página
      function mostrarProductos() {
        const filtrados = filtrarYOrdenar();
        const totalPaginas = Math.ceil(filtrados.length / productosPorPagina);
        
        if (paginaActual > totalPaginas) paginaActual = 1;
        
        const inicio = (paginaActual - 1) * productosPorPagina;
        const fin = inicio + productosPorPagina;
        const visibles = filtrados.slice(inicio, fin);
        
        contenedor.innerHTML = "";
        
        if (visibles.length === 0) {
          contenedor.innerHTML = `<p style="text-align:center; color:#666;">No se encontraron productos.</p>`;
          paginacion.innerHTML = "";
          return;
        }
        
        visibles.forEach(p => {
          const div = document.createElement("div");
          div.className = "producto-item";
          div.innerHTML = `
            <img src="${p.imagen}" alt="${p.titulo}" loading="lazy">
            <div class="producto-detalle">
              <h3>${p.titulo}</h3>
              <p>${p.descripcion?.substring(0, 80) || 'Descripción no disponible'}...</p>
              <p class="precio">$${p.precio.toFixed(2)}</p>
              <button class="btn-comprar">Comprar</button>
            </div>
          `;
          
          div.querySelector('.btn-comprar').addEventListener('click', (e) => {
            e.stopPropagation();
            abrirModal(p);
          });
          
          div.addEventListener('click', () => abrirModal(p));
          contenedor.appendChild(div);
        });
        
        mostrarPaginacion(totalPaginas);
      }
      
      // Mostrar paginación
      function mostrarPaginacion(totalPaginas) {
        paginacion.innerHTML = "";
        
        if (totalPaginas <= 1) return;
        
        for (let i = 1; i <= totalPaginas; i++) {
          const btn = document.createElement("button");
          btn.textContent = i;
          btn.disabled = i === paginaActual;
          if (i === paginaActual) btn.classList.add("active");
          
          btn.addEventListener("click", () => {
            paginaActual = i;
            mostrarProductos();
            window.scrollTo({ top: 0, behavior: 'smooth' });
          });
          
          paginacion.appendChild(btn);
        }
      }
      
      // Cambiar vista entre grid y lista
      function cambiarVista() {
        vistaGrid = !vistaGrid;
        contenedor.classList.toggle("grid-view");
        contenedor.classList.toggle("list-view");
        
        if (vistaGrid) {
          viewToggle.textContent = "Ver Lista";
        } else {
          viewToggle.textContent = "Ver Cuadrícula";
        }
        
        // Reajustar la visualización de los productos
        mostrarProductos();
      }
      
      // Abrir modal de producto
      function abrirModal(producto) {
        btnComprar.dataset.producto = JSON.stringify(producto);
        modalImg.src = producto.imagen;
        modalImg.alt = producto.titulo;
        modalNombre.textContent = producto.titulo;
        modalDescripcion.textContent = producto.descripcion || "Descripción no disponible";
        modalPrecio.textContent = `$${producto.precio.toFixed(2)}`;
        document.getElementById("modal-vendedor").textContent = producto.nombre_usuario || "Desconocido";

        modal.classList.add("active");
      }

      // Cerrar modal
      function cerrarModal() {
        modal.classList.remove("active");
      }
      
      // Función para actualizar notificaciones
      async function actualizarNotificaciones() {
        try {
          const response = await fetch('obtener_notificaciones.php');
          const data = await response.json();
          
          if (data.success) {
            // Actualizar el contador
            const noLeidas = data.notificaciones.filter(n => n.leida == 0).length;
            if (noLeidas > 0) {
              notifCount.textContent = noLeidas;
              notifCount.style.display = 'block';
            } else {
              notifCount.style.display = 'none';
            }
            
            // Actualizar el dropdown si está abierto
            if (notifDropdown.style.display === 'block') {
              actualizarDropdownNotificaciones(data.notificaciones);
            }
          }
        } catch (error) {
          console.error('Error al actualizar notificaciones:', error);
        }
      }
      
      function actualizarDropdownNotificaciones(notificaciones) {
        notifList.innerHTML = '';
        
        if (notificaciones.length > 0) {
          notificaciones.forEach(n => {
            const fecha = new Date(n.fecha);
            const notifItem = document.createElement('div');
            notifItem.className = `notificacion-item ${n.leida == 0 ? 'no-leida' : ''}`;
            notifItem.dataset.id = n.id;
            notifItem.innerHTML = `
              <p>${n.mensaje}</p>
              <small>${fecha.toLocaleDateString()} ${fecha.toLocaleTimeString()}</small>
            `;
            notifList.appendChild(notifItem);
          });
        } else {
          notifList.innerHTML = '<div class="notificacion-item">No hay notificaciones nuevas</div>';
        }
      }
      
      // Manejar clic en notificaciones
      notifDropdown.addEventListener('click', async function(e) {
        const notifItem = e.target.closest('.notificacion-item');
        
        if (notifItem && notifItem.dataset.id) {
          try {
            // Marcar como leída
            const response = await fetch('marcar_notificacion.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({ id: notifItem.dataset.id })
            });
            
            const data = await response.json();
            if (data.success) {
              notifItem.classList.remove('no-leida');
              // Actualizar contador
              const currentCount = parseInt(notifCount.textContent) || 0;
              if (currentCount > 1) {
                notifCount.textContent = currentCount - 1;
              } else {
                notifCount.style.display = 'none';
              }
            }
          } catch (error) {
            console.error('Error al marcar notificación:', error);
          }
        }
        
        if (e.target.classList.contains('cerrar-notificaciones')) {
          notifDropdown.style.display = 'none';
        }
      });
      
      // Mostrar/ocultar dropdown de notificaciones
      notifIcon.addEventListener('click', (e) => {
        e.stopPropagation();
        notifDropdown.style.display = notifDropdown.style.display === 'block' ? 'none' : 'block';
        if (notifDropdown.style.display === 'block') {
          actualizarNotificaciones();
        }
      });
      
      // Cerrar dropdown al hacer clic fuera
      document.addEventListener('click', (e) => {
        if (!notifIcon.contains(e.target)) {
          notifDropdown.style.display = 'none';
        }
      });
      
      // Procesar compra
      btnComprar.addEventListener("click", async () => {
        const producto = JSON.parse(btnComprar.dataset.producto);
        
        btnComprar.disabled = true;
        btnComprar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        
        try {
          const response = await fetch('procesar_compra.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              producto_id: producto.id,
              vendedor_id: producto.usuario_id,
              titulo: producto.titulo
            })
          });

          const data = await response.json();

          if (!response.ok) {
            throw new Error(data.message || `Error ${response.status}`);
          }

          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: '¡Compra exitosa!',
              html: `Has comprado <strong>${producto.titulo}</strong>.<br>
                    El vendedor ha sido notificado y pronto se pondrá en contacto contigo.`,
              confirmButtonText: 'Aceptar'
            });
            cerrarModal();
            
            // Actualizar notificaciones después de una compra
            actualizarNotificaciones();
          } else {
            throw new Error(data.message || 'Error al procesar la compra');
          }
        } catch (error) {
          console.error('Error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error en la compra',
            text: error.message,
            confirmButtonText: 'Entendido'
          });
        } finally {
          btnComprar.disabled = false;
          btnComprar.innerHTML = '<i class="fas fa-shopping-cart"></i> Comprar';
        }
      });
      
      btnFavorito.addEventListener("click", function() {
        const icon = this.querySelector('i');
        if (icon.classList.contains('far')) {
          icon.classList.replace('far', 'fas');
          this.innerHTML = '<i class="fas fa-heart"></i> En favoritos';
        } else {
          icon.classList.replace('fas', 'far');
          this.innerHTML = '<i class="far fa-heart"></i> Favorito';
        }
      });
      
      // Event listeners
      menuToggle.addEventListener('click', () => {
        mainMenu.classList.toggle('open');
      });
      
      searchInput.addEventListener("input", mostrarProductos);
      sortSelect.addEventListener("change", mostrarProductos);
      priceMin.addEventListener("input", mostrarProductos);
      priceMax.addEventListener("input", mostrarProductos);
      viewToggle.addEventListener("click", cambiarVista);
      modalCloseBtn.addEventListener("click", cerrarModal);
      modal.addEventListener("click", (e) => {
        if (e.target === modal) cerrarModal();
      });
      
      // Cargar productos al inicio
      cargarProductos();
      
      // Cargar notificaciones al inicio
      actualizarNotificaciones();
      
      // Actualizar notificaciones periódicamente (cada 30 segundos)
      setInterval(actualizarNotificaciones, 30000);
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>