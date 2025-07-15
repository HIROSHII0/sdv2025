<?php
session_start();
require_once 'conexion.php';

// Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

// Obtener datos del usuario
$usuario = $conn->query("SELECT nombre_completo FROM usuarios WHERE id = {$_SESSION['usuario_id']} LIMIT 1")->fetch_assoc();
$nombre = $usuario['nombre_completo'] ?? 'Usuario';

// Cargar notificaciones no leídas
$notificaciones = $conn->query("SELECT id, mensaje, fecha, leida FROM notificaciones WHERE usuario_id = {$_SESSION['usuario_id']} ORDER BY fecha DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

// Cerrar conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mascotas | QuisqueyaClick</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
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

    .notification-icon {
      position: relative;
      cursor: pointer;
      margin-right: 15px;
      font-size: 1.2rem;
      color: #e5e7eb;
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
    .controls select {
      padding: 0.6rem 1rem;
      font-size: 1rem;
      border-radius: 30px;
      border: 2px solid #10b981;
      outline: none;
      transition: border-color 0.3s ease;
      min-width: 180px;
    }

    .controls input[type="text"]:focus,
    .controls select:focus {
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

    .mascotas-lista {
      max-width: 1200px;
      margin: 0 auto 4rem;
      padding: 0 1rem;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 1.5rem;
    }

    .mascotas-lista.list-view {
      grid-template-columns: 1fr;
    }

    .mascotas-lista.list-view .mascota-item {
      flex-direction: row;
      min-height: auto;
      padding-bottom: 0;
    }

    .mascotas-lista.list-view .mascota-item img {
      width: 180px;
      height: 180px;
      border-bottom: none;
      border-right: 3px solid #10b981;
    }

    .mascotas-lista.list-view .mascota-detalle {
      padding: 1.5rem;
    }

    .mascota-item {
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

    .mascota-item:hover {
      transform: translateY(-8px);
      box-shadow: 0 18px 50px rgba(16, 185, 129, 0.3);
    }

    .mascota-item img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-bottom: 3px solid #10b981;
      transition: transform 0.4s ease;
    }

    .mascota-item:hover img {
      transform: scale(1.1);
    }

    .mascota-detalle {
      padding: 1rem;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }

    .mascota-detalle h3 {
      color: #059669;
      font-weight: 700;
      font-size: 1.25rem;
      margin-bottom: 0.5rem;
    }

    .mascota-detalle p {
      font-size: 0.95rem;
      color: #555;
      margin-bottom: 0.5rem;
      flex-grow: 1;
    }

    .mascota-edad {
      color: #6b7280;
      font-size: 0.9rem;
    }

    .btn-adoptar {
      background-color: #f59e0b;
      color: white;
      border: none;
      padding: 0.7rem;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease;
      margin-top: auto;
    }

    .btn-adoptar:hover {
      background-color: #d97706;
    }

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


    .modal-edad {
      color: #6b7280;
      font-size: 0.9rem;
      margin-left: 0.5rem;
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

    .btn-adoptar-modal {
      background-color: #f59e0b;
      color: white;
    }

    .btn-adoptar-modal:hover {
      background-color: #d97706;
    }

    .btn-favorito {
      background-color: #10b981;
      color: white;
    }

    .btn-favorito:hover {
      background-color: #059669;
    }

    @keyframes fadeInLeft {
      from { opacity: 0; transform: translateX(-40px); }
      to { opacity: 1; transform: translateX(0); }
    }

    @keyframes fadeInRight {
      from { opacity: 0; transform: translateX(40px); }
      to { opacity: 1; transform: translateX(0); }
    }

    @media (max-width: 900px) {
      .hero-section {
        flex-direction: column;
      }

      .hero-text h1 {
        font-size: 2.25rem;
      }

      .mascotas-lista {
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

      .mascotas-lista.list-view .mascota-item {
        flex-direction: column;
      }

      .mascotas-lista.list-view .mascota-item img {
        width: 100%;
        height: 200px;
        border-right: none;
        border-bottom: 3px solid #10b981;
      }

      .notificaciones-dropdown {
        width: 280px;
        right: -50px;
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
        <li><a href="mascotas.php" class="active">Mascotas</a></li>
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
      <h1>Encuentra tu compañero perfecto</h1>
      <p>Descubre mascotas en adopción que están buscando un hogar lleno de amor.</p>
      <a href="publicar_mascota.php" class="btn-primary">Publicar mascota</a>
    </div>
    <div class="hero-image">
      <img src="imagenes/animales1.webp" class="activa" alt="Mascotas">
    </div>
  </main>

  <section class="controls" id="controls">
    <input type="text" id="search-input" placeholder="Buscar mascotas...">
    <select id="type-select">
      <option value="">Todas las mascotas</option>
      <option value="perro">Perro</option>
      <option value="gato">Gato</option>
      <option value="ave">Ave</option>
      <option value="otro">Otro</option>
    </select>
    <select id="age-select">
      <option value="">Cualquier edad</option>
      <option value="cachorro">Cachorro</option>
      <option value="joven">Joven</option>
      <option value="adulto">Adulto</option>
      <option value="senior">Senior</option>
    </select>
    <button id="viewToggle">Ver Lista</button>
  </section>

  <section class="mascotas-lista grid-view" id="mascotas-lista">
    <!-- Las mascotas se cargarán aquí con JavaScript -->
  </section>

  <nav id="pagination" class="paginador"></nav>
<!-- Modal de mascota -->
<div id="modal" class="modal-backdrop" role="dialog">
  <div class="modal-content">
    <header class="modal-header">
      <h2>Detalle Mascota</h2>
      <button id="modal-close" class="modal-close-btn">&times;</button>
    </header>
    <div class="modal-body">
      <img src="" alt="" class="modal-img" id="modal-img">
      <div class="modal-info">
        <h2 id="modal-nombre"></h2>
        <p id="modal-descripcion"></p>

        <p><strong>Publicado por:</strong> <span id="modal-dueno"></span></p>

        <div class="modal-meta">
          <p><strong>Edad:</strong> <span id="modal-edad"></span></p>
          <p><strong>Tamaño:</strong> <span id="modal-tamano"></span></p>
          <p><strong>Vacunado:</strong> <span id="modal-vacunado"></span></p>
          <p><strong>Desparasitado:</strong> <span id="modal-desparasitado"></span></p>
        </div>

        <div class="modal-actions">
          <button class="btn-adoptar-modal" id="btn-adoptar">
            <i class="fas fa-paw"></i> Adoptar
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
      const mascotasPorPagina = 12;
      let paginaActual = 1;
      let mascotas = [];
      let vistaGrid = true;
      
      // Elementos del DOM
      const contenedor = document.getElementById("mascotas-lista");
      const paginacion = document.getElementById("pagination");
      const searchInput = document.getElementById("search-input");
      const typeSelect = document.getElementById("type-select");
      const ageSelect = document.getElementById("age-select");
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
      const modalTipo = document.getElementById("modal-tipo");
      const modalEdad = document.getElementById("modal-edad");
      const modalDueno = document.getElementById("modal-dueno");
      const btnAdoptar = document.getElementById("btn-adoptar");
      const btnFavorito = document.getElementById("btn-favorito");
      
      // Cargar mascotas
      function cargarMascotas() {
        fetch("get_mascotas.php")
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              mascotas = data.mascotas;
              mostrarMascotas();
            } else {
              contenedor.innerHTML = `<p style="text-align:center; color:#666;">${data.message || "No se pudieron cargar las mascotas."}</p>`;
            }
          })
          .catch(error => {
            console.error("Error:", error);
            contenedor.innerHTML = "<p style='color:red; text-align:center;'>Error al cargar las mascotas.</p>";
          });
      }
      
      // Filtrar mascotas
      function filtrarMascotas() {
        const texto = searchInput.value.toLowerCase();
        const tipo = typeSelect.value;
        const edad = ageSelect.value;
        
        return mascotas.filter(m => {
          if (texto && !m.nombre.toLowerCase().includes(texto) && !m.descripcion.toLowerCase().includes(texto)) return false;
          if (tipo && m.tipo !== tipo) return false;
          if (edad && m.edad !== edad) return false;
          return true;
        });
      }
      
      // Mostrar mascotas en la página
      function mostrarMascotas() {
        const filtradas = filtrarMascotas();
        const totalPaginas = Math.ceil(filtradas.length / mascotasPorPagina);
        
        if (paginaActual > totalPaginas) paginaActual = 1;
        
        const inicio = (paginaActual - 1) * mascotasPorPagina;
        const fin = inicio + mascotasPorPagina;
        const visibles = filtradas.slice(inicio, fin);
        
        contenedor.innerHTML = "";
        
        if (visibles.length === 0) {
          contenedor.innerHTML = `<p style="text-align:center; color:#666;">No se encontraron mascotas.</p>`;
          paginacion.innerHTML = "";
          return;
        }
        
        visibles.forEach(m => {
          const div = document.createElement("div");
          div.className = "mascota-item";
          div.innerHTML = `
            <img src="${m.imagen}" alt="${m.nombre}" loading="lazy">
            <div class="mascota-detalle">
              <h3>${m.nombre}</h3>
              <p>${m.descripcion?.substring(0, 80) || 'Descripción no disponible'}...</p>
              <span class="mascota-tipo">${m.tipo}</span>
              <span class="mascota-edad">${m.edad}</span>
              <button class="btn-adoptar">Adoptar</button>
            </div>
          `;
          
          div.querySelector('.btn-adoptar').addEventListener('click', (e) => {
            e.stopPropagation();
            abrirModal(m);
          });
          
          div.addEventListener('click', () => abrirModal(m));
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
            mostrarMascotas();
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
        
        // Reajustar la visualización de las mascotas
        mostrarMascotas();
      }
      
 // Abrir modal de mascota
function abrirModal(mascota) {
  btnAdoptar.dataset.mascota = JSON.stringify(mascota);

  modalImg.src = mascota.imagen;
  modalImg.alt = mascota.nombre || 'Mascota';
  modalNombre.textContent = mascota.nombre || 'Sin nombre';
  modalDescripcion.textContent = mascota.descripcion || "Descripción no disponible";
  modalDueno.textContent = mascota.usuario_nombre || "Desconocido";

  modalEdad.textContent = mascota.edad || "No especificada";
  document.getElementById("modal-tamano").textContent = mascota.tamano || "No especificado";

  const vacunado = mascota.vacunado == 1 ? "Sí" : (mascota.vacunado == 0 ? "No" : "No especificado");
  const desparasitado = mascota.desparasitado == 1 ? "Sí" : (mascota.desparasitado == 0 ? "No" : "No especificado");

  document.getElementById("modal-vacunado").textContent = vacunado;
  document.getElementById("modal-desparasitado").textContent = desparasitado;

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
        
        // Verificar primero si la respuesta es JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Respuesta no JSON recibida:', text);
            throw new Error('El servidor no devolvió una respuesta JSON válida');
        }
        
        const data = await response.json();
        
        if (!data.success) {
            console.error('Error del servidor:', data.error || 'Error desconocido');
            return;
        }
        
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
        
    } catch (error) {
        console.error('Error al actualizar notificaciones:', error);
        // Opcional: mostrar mensaje al usuario
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudieron cargar las notificaciones',
            timer: 3000
        });
    }
}

// Función para actualizar el dropdown de notificaciones
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
// Procesar adopción
btnAdoptar.addEventListener("click", async () => {
  let mascota;
  try {
    mascota = JSON.parse(btnAdoptar.dataset.mascota);
  } catch (e) {
    console.error("Error al parsear datos de mascota:", e);
    return Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se pudo procesar la información de la mascota.",
      confirmButtonText: "Entendido"
    });
  }

  btnAdoptar.disabled = true;
  btnAdoptar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

  try {
    const response = await fetch('procesar_adopcion.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        publicacion_id: mascota.id,
        comprador_id: <?php echo $_SESSION['usuario_id']; ?>,
        nombre_mascota: mascota.nombre
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || `Error ${response.status}`);
    }

    if (data.success) {
      await Swal.fire({
        icon: 'success',
        title: '¡Solicitud enviada!',
        html: `Has solicitado adoptar a <strong>${mascota.nombre}</strong>.<br>
              El dueño ha sido notificado y pronto se pondrá en contacto contigo.`,
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
    btnAdoptar.disabled = false;
    btnAdoptar.innerHTML = '<i class="fas fa-paw"></i> Adoptar';
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
      
      searchInput.addEventListener("input", mostrarMascotas);
      typeSelect.addEventListener("change", mostrarMascotas);
      ageSelect.addEventListener("change", mostrarMascotas);
      viewToggle.addEventListener("click", cambiarVista);
      modalCloseBtn.addEventListener("click", cerrarModal);
      modal.addEventListener("click", (e) => {
        if (e.target === modal) cerrarModal();
      });
      
      // Cargar mascotas al inicio
      cargarMascotas();
      
      // Cargar notificaciones al inicio
      actualizarNotificaciones();
      
      // Actualizar notificaciones periódicamente (cada 30 segundos)
      setInterval(actualizarNotificaciones, 30000);
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>