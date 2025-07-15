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

// Cargar servicios (tipo = 'servicio')
$servicios = [];
$sql = "SELECT 
    p.id, p.titulo, p.descripcion, p.duracion, p.lugar, p.imagen, p.precio_producto AS precio,
    u.nombre_usuario
FROM publicaciones p
INNER JOIN usuarios u ON p.usuario_id = u.id
WHERE p.tipo = 'servicio' AND p.visible = 1";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Manejo de imágenes
        $ruta = 'uploads/' . basename($row['imagen']);
        $row['imagen'] = (!empty($row['imagen']) && file_exists($ruta)) ? $ruta : "https://via.placeholder.com/300x200?text=Sin+imagen";
        
        // Formatear precio
        $row['precio'] = is_numeric($row['precio']) ? number_format((float)$row['precio'], 2) : '0.00';
        
        // Asegurar campos opcionales
        $row['detalles'] = $row['detalles'] ?? '';
        $row['duracion'] = $row['duracion'] ?? 'No especificada';
        $row['telefono'] = $row['telefono'] ?? 'No disponible';
 $row['duracion'] = $row['duracion'] ?? '';
        $row['lugar'] = $row['lugar'] ?? 'No especificado';
        
        $servicios[] = $row;
    }
    $result->free();
}

// Cargar notificaciones no leídas
$notificaciones = [];
$sqlNot = "SELECT id, mensaje, fecha, leida FROM notificaciones WHERE usuario_id = ? ORDER BY fecha DESC LIMIT 10";
$stmtNot = $conn->prepare($sqlNot);
if ($stmtNot) {
    $stmtNot->bind_param("i", $_SESSION['usuario_id']);
    $stmtNot->execute();
    $resNot = $stmtNot->get_result();
    while ($row = $resNot->fetch_assoc()) {
        $notificaciones[] = $row;
    }
    $stmtNot->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Servicios | QuisqueyaClick</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
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

    /* === Main Content === */
    main {
      max-width: 1200px;
      margin: 2rem auto 4rem;
      padding: 0 1rem;
      flex: 1;
    }

    h1 {
      text-align: center;
      font-size: 2.25rem;
      margin-bottom: 2rem;
      color: #111827;
    }

    /* === Controls Section === */
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

    /* === Servicios List === */
    .servicios-lista {
      max-width: 1200px;
      margin: 0 auto 4rem;
      padding: 0 1rem;
    }

    .servicios-lista.grid-view {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.5rem;
    }

    .servicios-lista.list-view {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .servicio-card {
      background: white;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      cursor: pointer;
    }

    .servicios-lista.grid-view .servicio-card {
      display: flex;
      flex-direction: column;
      min-height: 380px;
    }

    .servicios-lista.list-view .servicio-card {
      display: flex;
      flex-direction: row;
      align-items: center;
    }

    .servicios-lista.list-view .servicio-card img {
      width: 200px;
      height: 150px;
      border-radius: 12px 0 0 12px;
    }

    .servicio-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 30px rgba(16, 185, 129, 0.2);
    }

    .servicio-card img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-bottom: 3px solid #10b981;
      transition: transform 0.4s ease;
    }

    .servicio-card:hover img {
      transform: scale(1.05);
    }

    .servicio-contenido {
      padding: 1.25rem;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }

    .servicio-contenido h3 {
      color: #059669;
      font-weight: 700;
      font-size: 1.25rem;
      margin-bottom: 0.5rem;
    }

    .servicio-contenido p {
      font-size: 0.95rem;
      color: #555;
      margin-bottom: 0.5rem;
      flex-grow: 1;
    }

    .servicio-precio {
      font-weight: 700;
      color: #10b981;
      font-size: 1.2rem;
      margin: 0.5rem 0;
    }

    .servicio-publicador {
      font-size: 0.85rem;
      color: #6b7280;
      font-style: italic;
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
      margin-top: 0.5rem;
      width: 100%;
    }

    .btn-comprar:hover {
      background-color: #059669;
    }

    /* === Modal === */
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
      object-fit: cover;
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

    .modal-meta {
      margin: 1rem 0;
    }

    .modal-precio {
      font-weight: 700;
      color: #10b981;
      font-size: 1.5rem;
    }

    .modal-publicador {
      color: #6b7280;
      font-size: 0.9rem;
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

    .btn-comprar-modal {
      background-color: #10b981;
      color: white;
    }

    .btn-comprar-modal:hover {
      background-color: #059669;
    }

    /* === Toast === */
    #toast-container {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 10000;
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 10px;
    }

    .toast {
      background: #10b981;
      color: white;
      padding: 12px 20px;
      border-radius: 6px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      font-weight: 600;
      cursor: default;
      transition: transform 0.5s ease, opacity 0.5s ease;
      opacity: 1;
      user-select: none;
    }

    /* === Responsive === */
    @media (max-width: 900px) {
      .servicios-lista.grid-view {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
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

      .servicios-lista.list-view .servicio-card {
        flex-direction: column;
      }

      .servicios-lista.list-view .servicio-card img {
        width: 100%;
        height: 200px;
        border-radius: 12px 12px 0 0;
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
        <li><a href="productos.php">Productos</a></li>
        <li><a href="mascotas.php">Mascotas</a></li>
        <li><a href="servicios.php" class="active">Servicios</a></li>
        <li><a href="perfil.php">Perfil</a></li>
      </ul>
      
      <div class="usuario-info">
        <span>👤 <?php echo htmlspecialchars($nombre); ?></span>
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
                <?php echo "<pre>"; print_r($servicios); echo "</pre>"; ?>

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

  <main>
    <h1>Servicios disponibles</h1>

    <div class="controls">
      <input type="text" id="search-input" placeholder="Buscar servicios...">
      <select id="sort-select">
        <option value="">Ordenar por</option>
        <option value="name-asc">Nombre (A-Z)</option>
        <option value="name-desc">Nombre (Z-A)</option>
        <option value="price-asc">Precio (menor a mayor)</option>
        <option value="price-desc">Precio (mayor a menor)</option>
      </select>
      <input type="number" id="price-min" placeholder="Precio mínimo" min="0">
      <input type="number" id="price-max" placeholder="Precio máximo" min="0">
      <button id="viewToggle">Vista lista</button>
    </div>
<section class="servicios-lista grid-view" id="servicios-lista">
  <?php if (empty($servicios)): ?>
    <p style="text-align:center; color:#666;">No hay servicios disponibles actualmente.</p>
  <?php else: ?>
    <?php foreach($servicios as $s): ?>
<article class="servicio-card"
  data-id="<?php echo $s['id']; ?>"
  data-titulo="<?php echo htmlspecialchars($s['titulo']); ?>"
  data-descripcion="<?php echo htmlspecialchars($s['descripcion']); ?>"
  data-duracion="<?php echo htmlspecialchars($s['duracion']); ?>"
  data-lugar="<?php echo htmlspecialchars($s['lugar']); ?>"
  data-precio="<?php echo $s['precio']; ?>"
  data-publicador="<?php echo htmlspecialchars($s['nombre_usuario']); ?>"
  data-imagen="<?php echo htmlspecialchars($s['imagen']); ?>">

  
  <img src="<?php echo htmlspecialchars($s['imagen']); ?>" alt="<?php echo htmlspecialchars($s['titulo']); ?>">
  <div class="servicio-contenido">
    <h3><?php echo htmlspecialchars($s['titulo']); ?></h3>
    <p><?php echo htmlspecialchars($s['descripcion']); ?></p>
    <p><strong>Duración:</strong> <?php echo htmlspecialchars($s['duracion']); ?></p>
    <p><strong>Lugar:</strong> <?php echo htmlspecialchars($s['lugar']); ?></p>
    <p class="servicio-precio">$<?php echo $s['precio']; ?></p>
    <p class="servicio-publicador">Publicado por: <?php echo htmlspecialchars($s['nombre_usuario']); ?></p>
    <button class="btn-comprar">Contratar servicio</button>
  </div>
</article>

    <?php endforeach; ?>
  <?php endif; ?>
</section>

  </main>

  <!-- Modal de servicio -->
  <div id="modal" class="modal-backdrop" role="dialog">
    <div class="modal-content">
      <header class="modal-header">
        <h2>Detalle del servicio</h2>
        <button id="modal-close" class="modal-close-btn">&times;</button>
      </header>
      <div class="modal-body">
        <img src="" alt="" class="modal-img" id="modal-img">
        <div class="modal-info">
          <h2 id="modal-nombre"></h2>
          <p id="modal-descripcion"></p>
          <div class="modal-meta">
            <span class="modal-precio" id="modal-precio"></span>
            <span class="modal-publicador" id="modal-publicador"></span>
          </div>
          <div class="modal-actions">
            <button class="btn-comprar-modal" id="btn-comprar">
              <i class="fas fa-shopping-cart"></i> Contratar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast container -->
  <div id="toast-container"></div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Variables globales
      let vistaGrid = true;
      
      // Elementos del DOM
      const serviciosLista = document.getElementById("servicios-lista");
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
      const modalPublicador = document.getElementById("modal-publicador");
      const btnComprar = document.getElementById("btn-comprar");
      
      // Toast container
      const toastContainer = document.getElementById('toast-container');
      
      // Mostrar toast
      function showToast(message, duration = 3000) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        toastContainer.appendChild(toast);

        setTimeout(() => {
          toast.style.opacity = '0';
          toast.style.transform = 'translateY(-20px)';
          setTimeout(() => {
            toastContainer.removeChild(toast);
          }, 500);
        }, duration);
      }
      
      // Cambiar vista entre grid y lista
      function cambiarVista() {
        vistaGrid = !vistaGrid;
        
        if (vistaGrid) {
          serviciosLista.classList.remove("list-view");
          serviciosLista.classList.add("grid-view");
          viewToggle.textContent = "Vista lista";
        } else {
          serviciosLista.classList.remove("grid-view");
          serviciosLista.classList.add("list-view");
          viewToggle.textContent = "Vista cuadrícula";
        }
      }
      
      // Filtrar servicios
      function filtrarServicios() {
        const texto = searchInput.value.toLowerCase();
        const min = parseFloat(priceMin.value) || 0;
        const max = parseFloat(priceMax.value) || Infinity;
        
        Array.from(serviciosLista.children).forEach(card => {
          if (card.tagName === 'ARTICLE') {
            const titulo = card.querySelector('h3').textContent.toLowerCase();
            const descripcion = card.querySelector('p').textContent.toLowerCase();
            const precio = parseFloat(card.querySelector('.servicio-precio').textContent.replace('$', ''));
            
            const coincideTexto = titulo.includes(texto) || descripcion.includes(texto);
            const coincidePrecio = precio >= min && precio <= max;
            
            card.style.display = (coincideTexto && coincidePrecio) ? '' : 'none';
          }
        });
        
        ordenarServicios();
      }
      
      // Ordenar servicios
      function ordenarServicios() {
        const orden = sortSelect.value;
        if (!orden) return;
        
        const cards = Array.from(serviciosLista.children).filter(c => c.style.display !== 'none');
        
        cards.sort((a, b) => {
          const tituloA = a.querySelector('h3').textContent.toLowerCase();
          const tituloB = b.querySelector('h3').textContent.toLowerCase();
          const precioA = parseFloat(a.querySelector('.servicio-precio').textContent.replace('$', ''));
          const precioB = parseFloat(b.querySelector('.servicio-precio').textContent.replace('$', ''));
          
          switch(orden) {
            case 'name-asc': return tituloA.localeCompare(tituloB);
            case 'name-desc': return tituloB.localeCompare(tituloA);
            case 'price-asc': return precioA - precioB;
            case 'price-desc': return precioB - precioA;
            default: return 0;
          }
        });
        
        cards.forEach(card => serviciosLista.appendChild(card));
      }
      
      // Abrir modal de servicio
function abrirModal(card) {
  modalNombre.textContent = card.dataset.titulo;
  modalDescripcion.innerHTML = `
    <p>${card.dataset.descripcion}</p>
    <hr>
    <strong>Duración:</strong> ${card.dataset.duracion || 'No especificada'}<br>
    <strong>Lugar:</strong> ${card.dataset.lugar || 'No especificado'}
  `;
  modalPrecio.textContent = `$${card.dataset.precio}`;
  modalPublicador.textContent = `Publicado por: ${card.dataset.publicador}`;
  modalImg.src = card.dataset.imagen;
  modalImg.onerror = () => {
    modalImg.src = 'https://via.placeholder.com/300x200?text=Imagen+no+disponible';
  };
  btnComprar.dataset.servicioId = card.dataset.id;
  modal.classList.add('active');
}




      
      // Cerrar modal
      function cerrarModal() {
        modal.classList.remove('active');
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
      
      // Procesar contratación de servicio
      btnComprar.addEventListener("click", async () => {
        const servicioId = btnComprar.dataset.servicioId;
        
        btnComprar.disabled = true;
        btnComprar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        
        try {
          const response = await fetch('procesar_servicio.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              servicio_id: servicioId,
              titulo: modalNombre.textContent
            })
          });

          const data = await response.json();

          if (!response.ok) {
            throw new Error(data.message || `Error ${response.status}`);
          }

          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: '¡Solicitud enviada!',
              html: `Has solicitado el servicio <strong>${modalNombre.textContent}</strong>.<br>
                    El proveedor ha sido notificado y pronto se pondrá en contacto contigo.`,
              confirmButtonText: 'Aceptar'
            });
            cerrarModal();
            
            // Actualizar notificaciones después de una solicitud
            actualizarNotificaciones();
          } else {
            throw new Error(data.message || 'Error al procesar la solicitud');
          }
        } catch (error) {
          console.error('Error:', error);
          Swal.fire({
            icon: 'error',
            title: 'Error en la solicitud',
            text: error.message,
            confirmButtonText: 'Entendido'
          });
        } finally {
          btnComprar.disabled = false;
          btnComprar.innerHTML = '<i class="fas fa-shopping-cart"></i> Contratar';
        }
      });
      
      // Event listeners
      menuToggle.addEventListener('click', () => {
        mainMenu.classList.toggle('open');
      });
      
      searchInput.addEventListener('input', filtrarServicios);
      sortSelect.addEventListener('change', ordenarServicios);
      priceMin.addEventListener('input', filtrarServicios);
      priceMax.addEventListener('input', filtrarServicios);
      viewToggle.addEventListener('click', cambiarVista);
      
      serviciosLista.addEventListener('click', (e) => {
        const card = e.target.closest('.servicio-card');
        const btn = e.target.closest('.btn-comprar');
        
        if (btn && card) {
          e.stopPropagation();
          abrirModal(card);
        } else if (card) {
          abrirModal(card);
        }
      });
      
      modalCloseBtn.addEventListener('click', cerrarModal);
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          cerrarModal();
        }
      });
      
      // Cargar notificaciones al inicio
      actualizarNotificaciones();
      
      // Actualizar notificaciones periódicamente (cada 30 segundos)
      setInterval(actualizarNotificaciones, 30000);
    });
  </script>
</body>
</html>