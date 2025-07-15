<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "ventas2025");
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Error de conexión: " . $conn->connect_error]));
}

// Obtener datos del usuario
$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT * FROM datos_usuarios WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(json_encode(["success" => false, "message" => "Error en la preparación de la consulta: " . $conn->error]));
}

$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

// Valores por defecto
$defaultValues = [
    'telefono_movil' => '',
    'telefono_casa' => '',
    'direccion' => '',
    'provincia' => '',
    'ciudad' => '',
    'pais' => '',
    'codigo_postal' => '',
    'fecha_nacimiento' => '',
    'genero' => '',
    'estado_civil' => '',
    'tipo_documento' => '',
    'numero_documento' => '',
    'ocupacion' => '',
    'empresa' => '',
    'nivel_educativo' => '',
    'biografia' => '',
    'foto_perfil' => 'https://via.placeholder.com/100',
    'nombre_completo' => ''
];

if ($result->num_rows > 0) {
    $datos = $result->fetch_assoc();
    
    // Asignar datos a sesión con valores por defecto
    foreach ($defaultValues as $key => $value) {
        $_SESSION[$key] = $datos[$key] ?? $value;
    }
} else {
    // Si no hay datos, establecer valores por defecto
    foreach ($defaultValues as $key => $value) {
        $_SESSION[$key] = $value;
    }
}

$stmt->close();

// Manejo de la foto de perfil
$errorFoto = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil'])) {
    $archivo = $_FILES['foto_perfil'];
    $permitidos = ['image/jpeg', 'image/png', 'image/jpg'];
    
    if (in_array($archivo['type'], $permitidos)) {
        $rutaDestino = 'uploads/perfiles/';
        if (!is_dir($rutaDestino)) {
            mkdir($rutaDestino, 0755, true);
        }
        
        $nombreArchivo = 'perfil_' . $_SESSION['usuario_id'] . '_' . time() . '.' . pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $rutaCompleta = $rutaDestino . $nombreArchivo;
        
        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            // Actualizar en la base de datos
            $sqlUpdate = "UPDATE datos_usuarios SET foto_perfil = ? WHERE usuario_id = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            
            if ($stmtUpdate) {
                $stmtUpdate->bind_param("si", $rutaCompleta, $_SESSION['usuario_id']);
                $stmtUpdate->execute();
                $stmtUpdate->close();
                
                $_SESSION['foto_perfil'] = $rutaCompleta;
                header("Location: perfil.php");
                exit;
            } else {
                $errorFoto = "Error al actualizar la base de datos.";
            }
        } else {
            $errorFoto = "Error al subir la imagen.";
        }
    } else {
        $errorFoto = "Formato no permitido. Usa JPG o PNG.";
    }
}

$fotoPerfil = $_SESSION['foto_perfil'] ?? $defaultValues['foto_perfil'];
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Perfil Usuario</title>
<style>
  :root {
    --color-primario: #10b981;
    --color-oscuro: #1f2937;
    --color-fondo: #f9f9fb;
    --color-blanco: #fff;
    --color-error: #ef4444;
    --color-advertencia: #f59e0b;
  }
  
  * {
    margin: 0; 
    padding: 0; 
    box-sizing: border-box;
  }
  
  body {
    font-family: 'Montserrat', sans-serif;
    background-color: var(--color-fondo);
    color: #333;
    min-height: 100vh;
  }
  
  header {
    background-color: var(--color-oscuro);
    color: var(--color-blanco);
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .logo a {
    color: var(--color-primario);
    font-weight: 700;
    text-decoration: none;
    font-size: 1.5rem;
  }
  
  nav ul {
    list-style: none;
    display: flex;
    gap: 1rem;
  }
  
  nav ul li a {
    color: var(--color-blanco);
    text-decoration: none;
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
    font-weight: 600;
  }
  
  nav ul li a.active,
  nav ul li a:hover {
    background-color: var(--color-primario);
  }
  
  .usuario-info {
    color: var(--color-blanco);
    font-weight: 600;
  }
  
  .logout-btn {
    background: var(--color-error);
    color: var(--color-blanco);
    padding: 0.3rem 0.7rem;
    border-radius: 5px;
    text-decoration: none;
    margin-left: 1rem;
    font-weight: 600;
  }
  
  .perfil-container {
    display: flex;
    min-height: calc(100vh - 64px);
    margin: 1rem 2rem;
  }
  
  .perfil-sidebar {
    width: 260px;
    background-color: var(--color-oscuro);
    color: var(--color-blanco);
    padding: 2rem 1rem;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
  }
  
  .perfil-sidebar img {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--color-primario);
    cursor: pointer;
  }
  
  .nombre-usuario {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--color-primario);
    text-align: center;
  }
  
  .error-foto {
    color: var(--color-error);
    font-weight: 700;
    text-align: center;
  }
  
  .perfil-menu {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    width: 100%;
  }
  
  .filtro-btn {
    background: transparent;
    border: none;
    color: #e5e7eb;
    font-weight: 600;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    cursor: pointer;
    text-align: left;
    transition: background-color 0.3s;
  }
  
  .filtro-btn:hover,
  .filtro-btn.active {
    background-color: var(--color-primario);
    color: var(--color-blanco);
  }
  
  .perfil-main {
    flex: 1;
    background-color: var(--color-blanco);
    margin-left: 1rem;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
  }
  
  .publicaciones {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    max-height: 70vh;
    overflow-y: auto;
  }
  
  .publicacion-item {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: transform 0.3s ease;
    position: relative;
    display: flex;
    flex-direction: column;
  }
  
  .publicacion-item:hover {
    transform: translateY(-5px);
  }
  
  .publicacion-item img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 10px;
    margin-bottom: 0.7rem;
  }
  
  .publicacion-item .acciones-rapidas {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    gap: 5px;
  }
  
  .publicacion-item .acciones-rapidas button {
    background: rgba(0,0,0,0.7);
    border: none;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.3s;
  }
  
  .publicacion-item .acciones-rapidas button:hover {
    background: rgba(0,0,0,0.9);
  }
  
  .publicacion-oculta {
    opacity: 0.6;
    border-left: 4px solid var(--color-advertencia);
    position: relative;
  }
  
  .publicacion-oculta::after {
    content: 'OCULTA';
    position: absolute;
    top: 10px;
    left: 10px;
    background-color: var(--color-advertencia);
    color: white;
    padding: 2px 5px;
    border-radius: 3px;
    font-size: 0.8rem;
    font-weight: bold;
  }
  
  .publicacion-precio {
    font-weight: bold;
    color: var(--color-primario);
    margin-top: auto;
    padding-top: 0.5rem;
    font-size: 1.2rem;
  }
  
  .publicacion-detalle {
    font-size: 0.9rem;
    color: #666;
    margin-top: 0.3rem;
  }
  
  #btn-publicar {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: var(--color-primario);
    border: none;
    color: var(--color-blanco);
    font-size: 2rem;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(16, 185, 129, 0.6);
  }
  
  .modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.8);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
  }
  
  .modal-contenido {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
  }
  
  .modal-producto img {
    width: 100%;
    max-height: 300px;
    object-fit: contain;
    margin-bottom: 1rem;
  }
  
  .info-usuario-session-sidebar {
    margin-top: 1.5rem;
    padding: 1rem;
    background-color: #111827;
    border-radius: 8px;
    text-align: center;
    color: #10b981;
    font-weight: 600;
  }
  
  .acciones-modal {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
    flex-wrap: wrap;
  }
  
  .btn-accion {
    padding: 0.7rem 1.2rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    transition: opacity 0.3s;
    flex: 1;
    min-width: 120px;
    text-align: center;
  }
  
  .btn-accion:hover {
    opacity: 0.9;
  }
  
  .btn-editar {
    background-color: var(--color-primario);
    color: white;
  }
  
  .btn-ocultar {
    background-color: var(--color-advertencia);
    color: white;
  }
  
  .btn-mostrar {
    background-color: var(--color-primario);
    color: white;
  }
  
  .btn-eliminar {
    background-color: var(--color-error);
    color: white;
  }
  
  .form-edicion {
    display: none;
    margin-top: 1.5rem;
  }
  
  .form-edicion input,
  .form-edicion textarea,
  .form-edicion select {
    width: 100%;
    padding: 0.5rem;
    margin-bottom: 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
  }
  
  .form-edicion .guardar-cambios {
    background-color: var(--color-primario);
    color: white;
    padding: 0.7rem 1.2rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
  }
  
  /* Estilos para la sección de ajustes */
  .ajustes-container {
    display: none;
    flex: 1;
    background-color: var(--color-blanco);
    margin-left: 1rem;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
  }
  
  .ajustes-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .ajustes-form label {
    font-weight: 600;
    margin-bottom: 0.5rem;
  }
  
  .ajustes-form input,
  .ajustes-form select,
  .ajustes-form textarea {
    padding: 0.7rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-family: inherit;
  }
  
  .ajustes-form textarea {
    min-height: 100px;
    resize: vertical;
  }
  
  .btn-guardar-ajustes {
    background-color: var(--color-primario);
    color: white;
    padding: 0.8rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
    font-size: 1rem;
    margin-top: 1rem;
  }
</style>
</head>
<body>

<header>
  <div class="logo"><a href="index.php">EscolarMania</a></div>
  <nav>
    <ul>
      <li><a href="productos.php">Productos</a></li>
      <li><a href="mascotas.php">Mascotas</a></li>
      <li><a href="servicios.php">Servicios</a></li>
      <li><a href="perfil.php" class="active">Perfil</a></li>
      <li><a href="AJUSTES.php">AJUSTES</a></li>
    </ul>
  </nav>
  <div class="usuario-info">
    <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?>
    <a href="logout.php" class="logout-btn">Cerrar sesión</a>
  </div>
</header>

<div class="perfil-container">
  <aside class="perfil-sidebar">
    <div class="info-usuario-session-sidebar">
      <p>👤 <strong><?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></strong></p>
      <p>Sesión Activa</p>
    </div>

    <form method="POST" enctype="multipart/form-data" id="form-foto-perfil">
      <label for="input-foto" title="Cambiar foto de perfil">
        <img src="<?php echo htmlspecialchars($fotoPerfil); ?>" alt="Foto perfil" id="preview-foto" />
      </label>
      <input type="file" name="foto_perfil" id="input-foto" accept="image/*" style="display:none" />
      <?php if ($errorFoto): ?>
        <p class="error-foto"><?php echo $errorFoto; ?></p>
      <?php endif; ?>
    </form>
    
    <h2 class="nombre-usuario"><?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></h2>
    
    <nav class="perfil-menu">
      <button class="filtro-btn active" data-categoria="todo">Todo</button>
      <button class="filtro-btn" data-categoria="producto">Productos</button>
      <button class="filtro-btn" data-categoria="mascota">Mascotas</button>
      <button class="filtro-btn" data-categoria="servicio">Servicios</button>
      <button class="filtro-btn" id="btn-ajustes"></button>
       
    </nav>
    
    <div class="container">
      <div class="profile-container">
        <aside class="profile-sidebar">
          <h3 class="section-title">📝 Información personal</h3>
          <div class="info-item">
            <span class="info-label">📱 Teléfono</span>
            <div class="info-value"><?php echo htmlspecialchars($_SESSION['telefono_movil'] ?? 'No disponible'); ?></div>
          </div>
          
          <div class="info-item">
            <span class="info-label">📍 Dirección</span>
            <div class="info-value"><?php echo htmlspecialchars($_SESSION['direccion'] ?? 'No disponible'); ?></div>
          </div>
          
          <div class="info-item">
            <span class="info-label">💼 Ocupación</span>
            <div class="info-value"><?php echo htmlspecialchars($_SESSION['ocupacion'] ?? 'No disponible'); ?></div>
          </div>
        </aside>
      </div>
    </div>
  </aside>
  
  <main class="perfil-main">
    <h1>Mis Publicaciones</h1>
    <div id="publicaciones" class="publicaciones">
      <p>Cargando publicaciones...</p>
    </div>
  </main>
  
  <div class="ajustes-container" id="ajustes-container">
    <h1>Ajustes de Perfil</h1>
    <form class="ajustes-form" id="form-ajustes">
      <div>
        <label for="nombre">Nombre Completo</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? ''); ?>" required>
      </div>
      
      <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
      </div>
      
      <div>
        <label for="telefono_movil">Teléfono Móvil</label>
        <input type="tel" id="telefono_movil" name="telefono_movil" value="<?php echo htmlspecialchars($_SESSION['telefono_movil'] ?? ''); ?>">
      </div>
      
      <div>
        <label for="telefono_casa">Teléfono Fijo</label>
        <input type="tel" id="telefono_casa" name="telefono_casa" value="<?php echo htmlspecialchars($_SESSION['telefono_casa'] ?? ''); ?>">
      </div>
      
      <div>
        <label for="direccion">Dirección</label>
        <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($_SESSION['direccion'] ?? ''); ?>">
      </div>
      
      <div>
        <label for="ciudad">Ciudad</label>
        <input type="text" id="ciudad" name="ciudad" value="<?php echo htmlspecialchars($_SESSION['ciudad'] ?? ''); ?>">
      </div>
      
      <div>
        <label for="provincia">Provincia</label>
        <input type="text" id="provincia" name="provincia" value="<?php echo htmlspecialchars($_SESSION['provincia'] ?? ''); ?>">
      </div>
      
      <div>
        <label for="codigo_postal">Código Postal</label>
        <input type="text" id="codigo_postal" name="codigo_postal" value="<?php echo htmlspecialchars($_SESSION['codigo_postal'] ?? ''); ?>">
      </div>
      
      <div>
        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($_SESSION['fecha_nacimiento'] ?? ''); ?>">
      </div>
      
      <div>
        <label for="genero">Género</label>
        <select id="genero" name="genero">
          <option value="">Seleccionar...</option>
          <option value="masculino" <?php echo ($_SESSION['genero'] ?? '') === 'masculino' ? 'selected' : ''; ?>>Masculino</option>
          <option value="femenino" <?php echo ($_SESSION['genero'] ?? '') === 'femenino' ? 'selected' : ''; ?>>Femenino</option>
          <option value="otro" <?php echo ($_SESSION['genero'] ?? '') === 'otro' ? 'selected' : ''; ?>>Otro</option>
          <option value="prefiero_no_decir" <?php echo ($_SESSION['genero'] ?? '') === 'prefiero_no_decir' ? 'selected' : ''; ?>>Prefiero no decir</option>
        </select>
      </div>
      
      <div>
        <label for="ocupacion">Ocupación</label>
        <input type="text" id="ocupacion" name="ocupacion" value="<?php echo htmlspecialchars($_SESSION['ocupacion'] ?? ''); ?>">
      </div>
      
      <div>
        <label for="empresa">Empresa</label>
        <input type="text" id="empresa" name="empresa" value="<?php echo htmlspecialchars($_SESSION['empresa'] ?? ''); ?>">
      </div>
      
      <div>
        <label for="biografia">Biografía</label>
        <textarea id="biografia" name="biografia"><?php echo htmlspecialchars($_SESSION['biografia'] ?? ''); ?></textarea>
      </div>
      
      <button type="submit" class="btn-guardar-ajustes">Guardar Cambios</button>
    </form>
  </div>
</div>

<button id="btn-publicar" title="Nueva publicación">+</button>

<!-- Modal para crear publicación -->
<div id="modal-publicar" class="modal">
  <div class="modal-contenido">
    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
      <h2>Nueva publicación</h2>
      <button id="btn-cerrar-form" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
    </div>
    
    <form id="formulario-publicar" enctype="multipart/form-data">
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Tipo</label>
        <select id="tipo" name="tipo" required style="width: 100%; padding: 0.5rem;">
          <option value="">Seleccione...</option>
          <option value="producto">Producto</option>
          <option value="mascota">Mascota</option>
          <option value="servicio">Servicio</option>
        </select>
      </div>
      
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Título</label>
        <input type="text" id="titulo" name="titulo" required style="width: 100%; padding: 0.5rem;">
      </div>
      
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Descripción</label>
        <textarea id="descripcion" name="descripcion" required style="width: 100%; padding: 0.5rem; min-height: 100px;"></textarea>
      </div>
      
      <div id="campos-adicionales"></div>
      
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Imagen</label>
        <input type="file" id="imagen" name="imagen" accept="image/*" style="width: 100%;">
      </div>
      
      <button type="submit" style="margin-top: 1rem; padding: 0.7rem 1.5rem; background: var(--color-primario); color: white; border: none; border-radius: 5px; cursor: pointer;">Publicar</button>
    </form>
  </div>
</div>

<!-- Modal para ver/editar publicación -->
<div id="modal-producto" class="modal">
  <div class="modal-contenido">
    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
      <h2 id="modal-titulo"></h2>
      <button onclick="cerrarModalProducto()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">×</button>
    </div>
    
    <div id="vista-normal">
      <img id="modal-imagen" src="" alt="" style="width: 100%; max-height: 300px; object-fit: contain; margin-bottom: 1rem;">
      
      <div style="margin-bottom: 1rem;">
        <p id="modal-descripcion"></p>
      </div>
      
      <div id="modal-detalles"></div>
      
      <div class="acciones-modal">
        <button id="btn-editar" class="btn-accion btn-editar">✏️ Editar</button>
        <button id="btn-ocultar" class="btn-accion btn-ocultar">👁️ Ocultar</button>
        <button id="btn-eliminar" class="btn-accion btn-eliminar">🗑️ Eliminar</button>
      </div>
    </div>
    
    <div id="form-edicion" class="form-edicion">
      <h3>Editar Publicación</h3>
      
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Título</label>
        <input type="text" id="editar-titulo" style="width: 100%; padding: 0.5rem;">
      </div>
      
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Descripción</label>
        <textarea id="editar-descripcion" style="width: 100%; padding: 0.5rem; min-height: 100px;"></textarea>
      </div>
      
      <div id="editar-campos-adicionales"></div>
      
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Nueva Imagen (opcional)</label>
        <input type="file" id="editar-imagen" accept="image/*" style="width: 100%;">
      </div>
      
      <div style="display: flex; gap: 1rem;">
        <button id="btn-cancelar-edicion" style="padding: 0.7rem 1.2rem; background: #ccc; border: none; border-radius: 5px; cursor: pointer;">Cancelar</button>
        <button id="btn-guardar-cambios" class="guardar-cambios">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>

<script>
// Variables globales
let publicaciones = [];
let filtroActual = 'todo';
let publicacionActual = null;
let enAjustes = false;

// Elementos del DOM
const inputFoto = document.getElementById('input-foto');
const formFoto = document.getElementById('form-foto-perfil');
const btnPublicar = document.getElementById('btn-publicar');
const modalPublicar = document.getElementById('modal-publicar');
const btnCerrarForm = document.getElementById('btn-cerrar-form');
const formularioPublicar = document.getElementById('formulario-publicar');
const tipoSelect = document.getElementById('tipo');
const camposAdicionales = document.getElementById('campos-adicionales');
const publicacionesCont = document.getElementById('publicaciones');
const filtroBtns = document.querySelectorAll('.filtro-btn');
const modalProducto = document.getElementById('modal-producto');
const vistaNormal = document.getElementById('vista-normal');
const formEdicion = document.getElementById('form-edicion');
const btnEditar = document.getElementById('btn-editar');
const btnOcultar = document.getElementById('btn-ocultar');
const btnEliminar = document.getElementById('btn-eliminar');
const btnCancelarEdicion = document.getElementById('btn-cancelar-edicion');
const btnGuardarCambios = document.getElementById('btn-guardar-cambios');
const btnAjustes = document.getElementById('btn-ajustes');
const ajustesContainer = document.getElementById('ajustes-container');
const perfilMain = document.querySelector('.perfil-main');
const formAjustes = document.getElementById('form-ajustes');

// Event listeners
inputFoto.addEventListener('change', () => formFoto.submit());

btnPublicar.addEventListener('click', () => {
  modalPublicar.style.display = 'flex';
});

btnCerrarForm.addEventListener('click', () => {
  modalPublicar.style.display = 'none';
  formularioPublicar.reset();
  camposAdicionales.innerHTML = '';
});

modalPublicar.addEventListener('click', (e) => {
  if (e.target === modalPublicar) {
    modalPublicar.style.display = 'none';
  }
});

function cerrarModalProducto() {
  modalProducto.style.display = 'none';
  publicacionActual = null;
}

// Función para alternar entre publicaciones y ajustes
btnAjustes.addEventListener('click', () => {
  enAjustes = !enAjustes;
  
  if (enAjustes) {
    perfilMain.style.display = 'none';
    ajustesContainer.style.display = 'block';
    btnAjustes.classList.add('active');
  } else {
    perfilMain.style.display = 'block';
    ajustesContainer.style.display = 'none';
    btnAjustes.classList.remove('active');
  }
  
  // Actualizar botones de filtro
  filtroBtns.forEach(btn => {
    if (btn !== btnAjustes) {
      btn.classList.remove('active');
    }
  });
  
  if (!enAjustes) {
    document.querySelector(`.filtro-btn[data-categoria="${filtroActual}"]`).classList.add('active');
  }
});

function actualizarBotonOcultar(estaOculta) {
  if (estaOculta) {
    btnOcultar.textContent = '👁️ Mostrar';
    btnOcultar.classList.remove('btn-ocultar');
    btnOcultar.classList.add('btn-mostrar');
    btnOcultar.title = 'Hacer visible esta publicación';
  } else {
    btnOcultar.textContent = '👁️ Ocultar';
    btnOcultar.classList.remove('btn-mostrar');
    btnOcultar.classList.add('btn-ocultar');
    btnOcultar.title = 'Ocultar esta publicación';
  }
}

// Campos dinámicos según tipo de publicación
tipoSelect.addEventListener('change', () => {
  const tipo = tipoSelect.value;
  camposAdicionales.innerHTML = '';
  
  if(tipo === 'producto') {
    camposAdicionales.innerHTML = `
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Precio</label>
        <input type="number" name="precio" required style="width: 100%; padding: 0.5rem;" min="0" step="0.01">
      </div>
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Categoría</label>
        <select name="categoria_producto" style="width: 100%; padding: 0.5rem;">
          <option value="electronica">Electrónica</option>
          <option value="ropa">Ropa</option>
          <option value="hogar">Hogar</option>
          <option value="libros">Libros</option>
          <option value="otros">Otros</option>
        </select>
      </div>
    `;
  } else if(tipo === 'mascota') {
    camposAdicionales.innerHTML = `
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Edad (años)</label>
        <input type="number" name="edad" required style="width: 100%; padding: 0.5rem;" min="0">
      </div>
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Raza</label>
        <input type="text" name="raza" required style="width: 100%; padding: 0.5rem;">
      </div>
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Tamaño</label>
        <select name="tamano" style="width: 100%; padding: 0.5rem;">
          <option value="pequeno">Pequeño</option>
          <option value="mediano">Mediano</option>
          <option value="grande">Grande</option>
        </select>
      </div>
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Vacunado</label>
        <input type="checkbox" name="vacunado" value="1" style="width: auto;">
      </div>
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Desparasitado</label>
        <input type="checkbox" name="desparasitado" value="1" style="width: auto;">
      </div>
    `;
  } else if(tipo === 'servicio') {
    camposAdicionales.innerHTML = `
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Precio</label>
        <input type="number" name="precio" required style="width: 100%; padding: 0.5rem;" min="0" step="0.01">
      </div>
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Duración</label>
        <input type="text" name="duracion" style="width: 100%; padding: 0.5rem;">
      </div>
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Lugar</label>
        <input type="text" name="lugar" style="width: 100%; padding: 0.5rem;">
      </div>
    `;
  }
});

// Filtrar publicaciones
filtroBtns.forEach(btn => {
  if (btn !== btnAjustes) {
    btn.addEventListener('click', () => {
      if (enAjustes) return;
      
      filtroBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      filtroActual = btn.dataset.categoria;
      cargarPublicaciones();
    });
  }
});

// Cargar publicaciones
async function cargarPublicaciones() {
  publicacionesCont.innerHTML = '<p>Cargando publicaciones...</p>';
  
  try {
    const response = await fetch(`get_publicaciones.php?categoria=${filtroActual}`);
    const data = await response.json();
    
    if(data.success) {
      publicaciones = data.publicaciones;
      mostrarPublicaciones();
    } else {
      publicacionesCont.innerHTML = `<p>${data.message || 'Error al cargar publicaciones'}</p>`;
    }
  } catch (error) {
    publicacionesCont.innerHTML = '<p>Error de conexión</p>';
    console.error('Error al cargar publicaciones:', error);
  }
}

// Mostrar publicaciones en el HTML
function mostrarPublicaciones() {
  if(publicaciones.length === 0) {
    publicacionesCont.innerHTML = '<p>No hay publicaciones para mostrar</p>';
    return;
  }
  
  publicacionesCont.innerHTML = '';
  
  publicaciones.forEach(pub => {
    const pubElement = document.createElement('div');
    pubElement.className = 'publicacion-item';
    
    // Añadir clase si está oculta
    if (pub.visible === 0 || pub.visible === false) {
        pubElement.classList.add('publicacion-oculta');
    }
    
    // Construir el contenido de la publicación
    let contenido = `
      <div class="acciones-rapidas">
        <button onclick="mostrarModalProducto(${pub.id}); event.stopPropagation();" title="Ver detalles">👁️</button>
      </div>
      <img src="uploads/${pub.imagen || 'logo.jpg'}" alt="${pub.titulo}" onerror="this.src='uploads/logo.jpg'">
      <h3>${pub.titulo}</h3>
      <p>${pub.descripcion.substring(0, 50)}${pub.descripcion.length > 50 ? '...' : ''}</p>
    `;
    
    // Añadir detalles específicos según el tipo
    if (pub.tipo === 'producto' || pub.tipo === 'servicio') {
      contenido += `<div class="publicacion-precio">$${pub.precio || '0.00'}</div>`;
    } else if (pub.tipo === 'mascota') {
      contenido += `
        <div class="publicacion-detalle">Edad: ${pub.edad || 'No especificada'} años</div>
        <div class="publicacion-detalle">Raza: ${pub.raza || 'No especificada'}</div>
      `;
    }
    
    pubElement.innerHTML = contenido;
    pubElement.addEventListener('click', () => mostrarModalProducto(pub));
    publicacionesCont.appendChild(pubElement);
  });
}

// Mostrar modal con los detalles de la publicación
function mostrarModalProducto(pub) {
  publicacionActual = pub;
  
  document.getElementById('modal-titulo').textContent = pub.titulo;
  document.getElementById('modal-descripcion').textContent = pub.descripcion;
  
const img = document.getElementById('modal-imagen');
if (pub.imagen) {
  if (pub.imagen.startsWith('uploads/')) {
    img.src = pub.imagen;
  } else {
    img.src = 'uploads/' + pub.imagen;
  }
} else {
  img.src = 'uploads/logo.jpg';
}
img.alt = pub.titulo;
img.onerror = function() {
  this.src = 'uploads/logo.jpg';
};

  
  const detalles = document.getElementById('modal-detalles');
  detalles.innerHTML = '';
  
 if(pub.tipo === 'producto' || pub.tipo === 'servicio') {
  detalles.innerHTML = `<p><strong>Precio:</strong> $${pub.precio || 'No especificado'}</p>`;
  if(pub.tipo === 'servicio') {
    detalles.innerHTML += `
      <p><strong>Duración:</strong> ${pub.duracion || 'No especificada'}</p>
      <p><strong>Lugar:</strong> ${pub.lugar || 'No especificado'}</p>
      
    `;
  }
} else if(pub.tipo === 'mascota') {
  detalles.innerHTML = `
    <p><strong>Edad:</strong> ${pub.edad || 'No especificada'} años</p>
    <p><strong>Raza:</strong> ${pub.raza || 'No especificada'}</p>
    <p><strong>Tamaño:</strong> ${pub.tamano || 'No especificado'}</p>
    <p><strong>Vacunado:</strong> ${pub.vacunado ? 'Sí' : 'No'}</p>
    <p><strong>Desparasitado:</strong> ${pub.desparasitado ? 'Sí' : 'No'}</p>
  `;
}

  
  // Actualizar el botón según el estado
  const estaOculta = pub.visible === 0 || pub.visible === false;
  actualizarBotonOcultar(estaOculta);
  
  // Mostrar vista normal y ocultar formulario de edición
  vistaNormal.style.display = 'block';
  formEdicion.style.display = 'none';
  
  modalProducto.style.display = 'flex';
}

// Configurar botones de acciones
btnEditar.addEventListener('click', () => {
  // Llenar formulario de edición con los datos actuales
  document.getElementById('editar-titulo').value = publicacionActual.titulo;
  document.getElementById('editar-descripcion').value = publicacionActual.descripcion;
  
  const editarCampos = document.getElementById('editar-campos-adicionales');
  editarCampos.innerHTML = '';
  
  if(publicacionActual.tipo === 'producto' || publicacionActual.tipo === 'servicio') {
    editarCampos.innerHTML = `
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Precio</label>
        <input type="number" id="editar-precio" value="${publicacionActual.precio || ''}" style="width: 100%; padding: 0.5rem;" min="0" step="0.01">
      </div>
    `;
    if(publicacionActual.tipo === 'producto') {
      editarCampos.innerHTML += `
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.5rem;">Categoría</label>
          <select id="editar-categoria" style="width: 100%; padding: 0.5rem;">
            <option value="electronica" ${publicacionActual.categoria_producto === 'electronica' ? 'selected' : ''}>Electrónica</option>
            <option value="ropa" ${publicacionActual.categoria_producto === 'ropa' ? 'selected' : ''}>Ropa</option>
            <option value="hogar" ${publicacionActual.categoria_producto === 'hogar' ? 'selected' : ''}>Hogar</option>
            <option value="libros" ${publicacionActual.categoria_producto === 'libros' ? 'selected' : ''}>Libros</option>
            <option value="otros" ${publicacionActual.categoria_producto === 'otros' ? 'selected' : ''}>Otros</option>
          </select>
        </div>
      `;
    } else if(publicacionActual.tipo === 'servicio') {
      editarCampos.innerHTML += `
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.5rem;">Duración</label>
          <input type="text" id="editar-duracion" value="${publicacionActual.duracion || ''}" style="width: 100%; padding: 0.5rem;">
        </div>
        <div style="margin-bottom: 1rem;">
          <label style="display: block; margin-bottom: 0.5rem;">Lugar</label>
          <input type="text" id="editar-lugar" value="${publicacionActual.lugar || ''}" style="width: 100%; padding: 0.5rem;">
        </div>
      `;
    }
  } else if(publicacionActual.tipo === 'mascota') {
    editarCampos.innerHTML = `
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Edad (años)</label>
        <input type="number" id="editar-edad" value="${publicacionActual.edad || ''}" style="width: 100%; padding: 0.5rem;" min="0">
      </div>
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Raza</label>
        <input type="text" id="editar-raza" value="${publicacionActual.raza || ''}" style="width: 100%; padding: 0.5rem;">
      </div>
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Tamaño</label>
        <select id="editar-tamano" style="width: 100%; padding: 0.5rem;">
          <option value="pequeno" ${publicacionActual.tamano === 'pequeno' ? 'selected' : ''}>Pequeño</option>
          <option value="mediano" ${publicacionActual.tamano === 'mediano' ? 'selected' : ''}>Mediano</option>
          <option value="grande" ${publicacionActual.tamano === 'grande' ? 'selected' : ''}>Grande</option>
        </select>
      </div>
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Vacunado</label>
        <input type="checkbox" id="editar-vacunado" ${publicacionActual.vacunado ? 'checked' : ''} style="width: auto;">
      </div>
      <div style="margin-bottom: 1rem;">
        <label style="display: block; margin-bottom: 0.5rem;">Desparasitado</label>
        <input type="checkbox" id="editar-desparasitado" ${publicacionActual.desparasitado ? 'checked' : ''} style="width: auto;">
      </div>
    `;
  }
  
  // Cambiar a vista de edición
  vistaNormal.style.display = 'none';
  formEdicion.style.display = 'block';
});

btnCancelarEdicion.addEventListener('click', () => {
  // Volver a vista normal
  vistaNormal.style.display = 'block';
  formEdicion.style.display = 'none';
});

btnGuardarCambios.addEventListener('click', async () => {
  // Validar campos
  const nuevoTitulo = document.getElementById('editar-titulo').value;
  const nuevaDescripcion = document.getElementById('editar-descripcion').value;
  
  if(!nuevoTitulo || !nuevaDescripcion) {
    alert('El título y la descripción son obligatorios');
    return;
  }
  
  // Preparar datos para enviar
  const formData = new FormData();
  formData.append('id', publicacionActual.id);
  formData.append('titulo', nuevoTitulo);
  formData.append('descripcion', nuevaDescripcion);
  
  // Agregar campos específicos según el tipo
  if(publicacionActual.tipo === 'producto' || publicacionActual.tipo === 'servicio') {
    const nuevoPrecio = document.getElementById('editar-precio').value;
    formData.append('precio', nuevoPrecio);
    
    if(publicacionActual.tipo === 'producto') {
      const nuevaCategoria = document.getElementById('editar-categoria').value;
      formData.append('categoria_producto', nuevaCategoria);
    } else if(publicacionActual.tipo === 'servicio') {
      const nuevaDuracion = document.getElementById('editar-duracion').value;
      const nuevoLugar = document.getElementById('editar-lugar').value;
      formData.append('duracion', nuevaDuracion);
      formData.append('lugar', nuevoLugar);
    }
  } else if(publicacionActual.tipo === 'mascota') {
    const nuevaEdad = document.getElementById('editar-edad').value;
    const nuevaRaza = document.getElementById('editar-raza').value;
    const nuevoTamano = document.getElementById('editar-tamano').value;
    const nuevoVacunado = document.getElementById('editar-vacunado').checked ? '1' : '0';
    const nuevoDesparasitado = document.getElementById('editar-desparasitado').checked ? '1' : '0';
    
    formData.append('edad', nuevaEdad);
    formData.append('raza', nuevaRaza);
    formData.append('tamano', nuevoTamano);
    formData.append('vacunado', nuevoVacunado);
    formData.append('desparasitado', nuevoDesparasitado);
  }
  
  // Agregar nueva imagen si se seleccionó
  const nuevaImagen = document.getElementById('editar-imagen').files[0];
  if(nuevaImagen) {
    formData.append('imagen', nuevaImagen);
  }
  
  try {
    const response = await fetch('editar_publicacion.php', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if(data.success) {
      alert('Publicación actualizada con éxito');
      cargarPublicaciones();
      cerrarModalProducto();
    } else {
      alert(data.message || 'Error al actualizar publicación');
    }
  } catch (error) {
    alert('Error de conexión');
    console.error('Error al editar publicación:', error);
  }
});

// Manejar el botón de ocultar/mostrar
btnOcultar.addEventListener('click', async () => {
    const estaOculta = publicacionActual.visible === 0 || publicacionActual.visible === false;
    const accion = estaOculta ? 'mostrar' : 'ocultar';
    const mensaje = estaOculta ? 
        '¿Estás seguro de que quieres mostrar esta publicación?' : 
        '¿Estás seguro de que quieres ocultar esta publicación?';

    if(confirm(mensaje)) {
        try {
            const response = await fetch('ocultar_publicacion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id: publicacionActual.id,
                    accion: accion
                })
            });

            const data = await response.json();

            if(data.success) {
                alert(data.message);
                publicacionActual.visible = (accion === 'mostrar') ? 1 : 0;
                actualizarBotonOcultar(!estaOculta);
                if (filtroActual !== 'todo') {
                    cargarPublicaciones();
                }
            } else {
                alert(data.message || 'Error al cambiar visibilidad');
            }
        } catch (error) {
            alert('Error de conexión');
            console.error('Error al cambiar visibilidad:', error);
        }
    }
});

btnEliminar.addEventListener('click', async () => {
  if(confirm('¿Estás seguro de que quieres eliminar esta publicación? Esta acción no se puede deshacer.')) {
    try {
  const response = await fetch('eliminar_publicacion.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    id: publicacionActual.id
  })
});

      
      const data = await response.json();
      
      if(data.success) {
        alert('Publicación eliminada');
        cargarPublicaciones();
        cerrarModalProducto();
      } else {
        alert(data.message || 'Error al eliminar publicación');
      }
    } catch (error) {
      alert('Error de conexión');
      console.error('Error al eliminar publicación:', error);
    }
  }
});


// Enviar formulario de publicación
formularioPublicar.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(formularioPublicar);
  
  try {
    const response = await fetch('crear_publicacion.php', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if(data.success) {
      alert('Publicación creada con éxito');
      modalPublicar.style.display = 'none';
      formularioPublicar.reset();
      camposAdicionales.innerHTML = '';
      cargarPublicaciones();
    } else {
      alert(data.message || 'Error al crear publicación');
    }
  } catch (error) {
    alert('Error de conexión');
    console.error('Error al crear publicación:', error);
  }
});

// Enviar formulario de ajustes
formAjustes.addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(formAjustes);
  
  try {
    const response = await fetch('actualizar_ajustes.php', {
      method: 'POST',
      body: formData
    });
    
    const data = await response.json();
    
    if(data.success) {
      alert('Ajustes actualizados con éxito');
      // Actualizar datos en sesión
      if (data.nuevosDatos) {
        for (const key in data.nuevosDatos) {
          if (data.nuevosDatos.hasOwnProperty(key)) {
            $_SESSION[key] = data.nuevosDatos[key];
          }
        }
      }
    } else {
      alert(data.message || 'Error al actualizar ajustes');
    }
  } catch (error) {
    alert('Error de conexión');
    console.error('Error al actualizar ajustes:', error);
  }
});

// Cerrar modal al hacer clic fuera del contenido
modalProducto.addEventListener('click', (e) => {
  if (e.target === modalProducto) {
    cerrarModalProducto();
  }
});

// Carga inicial
document.addEventListener('DOMContentLoaded', () => {
  cargarPublicaciones();
});
</script>

</body>
</html>