<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

$servername = "localhost"; 
$username = "root";
$password = ""; // XAMPP por defecto no tiene contraseña
$dbname = "ventas2025";

$conn = new mysqli($servername, $username, $password, $dbname);


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$usuario_id = $_SESSION['usuario_id'];
$sql = "SELECT * FROM datos_usuarios WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$datos = $result->fetch_assoc();

if ($datos) {
    // Asignar variables a sesión para usar en el HTML
    $_SESSION['telefono'] = $datos['telefono_movil'] ?? '';
    $_SESSION['direccion'] = $datos['direccion'] ?? '';
    $_SESSION['provincia'] = $datos['provincia'] ?? '';
    $_SESSION['ciudad'] = $datos['ciudad'] ?? '';
    $_SESSION['pais'] = $datos['pais'] ?? '';
    $_SESSION['codigo_postal'] = $datos['codigo_postal'] ?? '';
    $_SESSION['fecha_nacimiento'] = $datos['fecha_nacimiento'] ?? '';
    $_SESSION['genero'] = $datos['genero'] ?? '';
    $_SESSION['estado_civil'] = $datos['estado_civil'] ?? '';
    $_SESSION['tipo_documento'] = $datos['tipo_documento'] ?? '';
    $_SESSION['numero_documento'] = $datos['numero_documento'] ?? '';
    $_SESSION['ocupacion'] = $datos['ocupacion'] ?? '';
    $_SESSION['empresa'] = $datos['empresa'] ?? '';
    $_SESSION['nivel_educativo'] = $datos['nivel_educativo'] ?? '';
    $_SESSION['biografia'] = $datos['biografia'] ?? '';
    $_SESSION['foto_perfil'] = $datos['foto_perfil'] ?? $_SESSION['foto_perfil'] ?? 'https://via.placeholder.com/100';
} else {
    // No hay datos en datos_usuarios para este usuario, puedes asignar valores por defecto si quieres
    $_SESSION['telefono'] = '';
    $_SESSION['direccion'] = '';
    // ...
}

$stmt->close();
$conn->close();

// Después sigue el resto de tu código que ya tienes, por ejemplo:
$fotoPerfil = $_SESSION['foto_perfil'] ?? 'https://via.placeholder.com/100';

// Manejar subida de foto perfil
$errorFoto = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil'])) {
    $archivo = $_FILES['foto_perfil'];
    $permitidos = ['image/jpeg', 'image/png', 'image/jpg'];
    if (in_array($archivo['type'], $permitidos)) {
        $rutaDestino = 'uploads/perfiles/';
        if (!is_dir($rutaDestino)) mkdir($rutaDestino, 0755, true);
        $nombreArchivo = 'perfil_' . $_SESSION['usuario_id'] . '_' . time() . '.' . pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $rutaCompleta = $rutaDestino . $nombreArchivo;
        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            $_SESSION['foto_perfil'] = $rutaCompleta;
            header("Location: perfil.php");
            exit;
        } else {
            $errorFoto = "Error al subir la imagen.";
        }
    } else {
        $errorFoto = "Formato no permitido. Usa JPG o PNG.";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Perfil Usuario</title>
<style>
  /* === CSS resumido para perfil === */
  :root {
    --color-primario: #10b981;
    --color-oscuro: #1f2937;
    --color-fondo: #f9f9fb;
    --color-blanco: #fff;
    --color-error: #ef4444;
  }
  * {
    margin: 0; padding: 0; box-sizing: border-box;
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
  header .logo a {
    color: var(--color-primario);
    font-weight: 700;
    text-decoration: none;
    font-size: 1.5rem;
  }
  header nav ul {
    list-style: none;
    display: flex;
    gap: 1rem;
  }
  header nav ul li a {
    color: var(--color-blanco);
    text-decoration: none;
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
    font-weight: 600;
  }
  header nav ul li a.active,
  header nav ul li a:hover {
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
  .mensaje-sesion {
    color: var(--color-primario);
    font-weight: 600;
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
    user-select: none;
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
    display: flex;
    flex-direction: column;
  }
  .cabecera {
    margin-bottom: 1.5rem;
  }
  .publicaciones {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    max-height: 70vh;
    overflow-y: auto;
  }
  .publicacion-item {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
    background: #fefefe;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: transform 0.3s ease;
  }
  .publicacion-item:hover {
    transform: translateY(-5px);
  }
  .publicacion-item img {
    max-width: 100%;
    max-height: 180px;
    border-radius: 10px;
    margin-bottom: 0.7rem;
    object-fit: cover;
  }
  .publicacion-item h3 {
    color: var(--color-primario);
    margin-bottom: 0.5rem;
  }
  /* Boton flotante */
  #btn-publicar {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background-color: var(--color-primario);
    border: none;
    font-size: 2.5rem;
    color: var(--color-blanco);
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    user-select: none;
    z-index: 3000;
  }
  #btn-publicar:hover {
    background-color: #059669;
  }
  /* Modal */
  .modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.3);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 4000;
  }
  .modal.oculto {
    display: none;
  }
  .modal-contenido {
    background: var(--color-blanco);
    border-radius: 8px;
    padding: 1.5rem 2rem;
    width: 100%;
    max-width: 420px;
    max-height: 90vh;
    overflow-y: auto;
    box-sizing: border-box;
    color: #222;
  }
  .form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
  }
  .form-header h2 {
    margin: 0;
    font-weight: 600;
  }
  #btn-cerrar-form {
    border: none;
    background: transparent;
    font-size: 1.5rem;
    color: #888;
    cursor: pointer;
  }
  #btn-cerrar-form:hover {
    color: #444;
  }
  form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  label {
    font-weight: 500;
    font-size: 0.9rem;
    color: #444;
  }
  input, select, textarea {
    padding: 0.5rem 0.6rem;
    font-size: 1rem;
    border-radius: 4px;
    border: 1px solid #ccc;
    outline-offset: 2px;
  }
  input:focus, select:focus, textarea:focus {
    border-color: var(--color-primario);
    outline: none;
  }
  .btn-enviar {
    background: var(--color-primario);
    color: var(--color-blanco);
    border: none;
    padding: 0.7rem 1rem;
    font-weight: 600;
    border-radius: 20px;
    cursor: pointer;
    align-self: flex-start;
    transition: background-color 0.3s;
  }
  .btn-enviar:hover {
    background: #059669;
  }
  /* Zona Drop */
  .zona-drop {
    border: 2px dashed var(--color-primario);
    padding: 2rem;
    border-radius: 20px;
    text-align: center;
    cursor: pointer;
    color: var(--color-primario);
    transition: border-color 0.3s;
  }
  .zona-drop.highlight {
    border-color: #059669;
  }
  #nombre-archivo {
    margin-top: 0.5rem;
    font-weight: 600;
    color: #4b5563;
  }
  .preview-imagen {
    margin-top: 1rem;
    max-width: 100%;
    max-height: 200px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }
  .info-usuario-session-sidebar {
  margin-top: 1.5rem;
  padding: 1rem;
  background-color: #111827; /* más oscuro que sidebar */
  border-radius: 8px;
  text-align: center;
  color: #10b981; /* verde primario */
  font-weight: 600;
  user-select: none;
  box-shadow: 0 0 8px rgb(16 185 129 / 0.3);
}

.info-usuario-session-sidebar p {
  margin: 0.3rem 0;
  font-size: 0.95rem;
}

</style>
</head>
<body>

<header>
  <div class="logo"><a href="index.php"><i>EscolarMania</i></a></div>
  <nav>
    <ul>
      <li><a href="productos.php">Productos</a></li>
      <li><a href="mascotas.php">Mascotas</a></li>
      <li><a href="servicios.php">Servicios</a></li>
      <li><a href="perfil.php" class="active">Perfil</a></li>
      <li><a href="ajustes.php">ajustes</a></li>
    </ul>
  </nav>
  <div class="usuario-info">
    👤 <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?>
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
    </form>
    <h2 class="nombre-usuario"><?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></h2>
    <p class="mensaje-sesion">Sesión activa</p>
    <?php if ($errorFoto): ?>
      <p class="error-foto"><?php echo $errorFoto; ?></p>
    <?php endif; ?>
    <nav class="perfil-menu">
      <button class="filtro-btn active" data-categoria="todo">Todo</button>
      <button class="filtro-btn" data-categoria="producto">Productos</button>
      <button class="filtro-btn" data-categoria="mascota">Mascotas</button>
      <button class="filtro-btn" data-categoria="servicio">Servicios</button>
      </nav>
    <div class="info-adicional">
      <hr style="margin: 1rem 0; border: 0; border-top: 1px solid #10b981;">
      <h3 style="color: #10b981; margin-bottom: 0.5rem;">Información del Usuario</h3>
      <p><strong>Correo:</strong> <?php echo htmlspecialchars($_SESSION['correo'] ?? 'No disponible'); ?></p>
      <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($_SESSION['telefono'] ?? 'No disponible'); ?></p>
      <p><strong>Dirección:</strong> <?php echo htmlspecialchars($_SESSION['direccion'] ?? 'No disponible'); ?></p>
            <p><strong>Descripcion:</strong> <?php echo htmlspecialchars($_SESSION['direccion'] ?? 'No disponible'); ?></p>
    </div>
  </aside>
  <main class="perfil-main">
    <div class="cabecera">
      <h1>Mis Publicaciones</h1>
    </div>
    <section id="publicaciones" class="publicaciones">
      <p>Cargando publicaciones...</p>
    </section>
  </main>
</div>

<button id="btn-publicar" title="Nueva publicación">+</button>

<!-- Modal para crear publicación -->
<div id="modal-publicar" class="modal oculto" role="dialog" aria-modal="true" aria-labelledby="titulo-modal">
  <div class="modal-contenido">
    <div class="form-header">
      <h2 id="titulo-modal">Nueva publicación</h2>
      <button id="btn-cerrar-form" aria-label="Cerrar">&times;</button>
    </div>
    <form id="formulario-publicar">
      <label for="tipo">Tipo</label>
      <select id="tipo" name="tipo" required>
        <option value="">Seleccione...</option>
        <option value="producto">Producto</option>
        <option value="mascota">Mascota</option>
        <option value="servicio">Servicio</option>
      </select>

      <label for="titulo">Título</label>
      <input type="text" id="titulo" name="titulo" required />

      <label for="descripcion">Descripción</label>
      <textarea id="descripcion" name="descripcion" rows="3" required></textarea>

      <div id="campos-adicionales"></div>

      <div class="zona-drop" id="zonaDrop" tabindex="0" role="button" aria-label="Subir imagen">
        <i class="icono-subida">📤</i>
        <p>Haz clic o arrastra una imagen aquí</p>
        <p id="nombre-archivo">Ningún archivo seleccionado</p>
        <img id="preview-imagen" class="preview-imagen oculto" alt="Vista previa" />
        <input type="file" id="archivo" name="imagen" accept="image/*" style="display:none" />
      </div>

      <button type="submit" class="btn-enviar">Publicar</button>
    </form>
  </div>
</div>

<script>
  // Foto perfil subir automático
  const inputFoto = document.getElementById('input-foto');
  const formFoto = document.getElementById('form-foto-perfil');
  inputFoto.addEventListener('change', () => formFoto.submit());

  // Modal control
  const btnPublicar = document.getElementById('btn-publicar');
  const modalPublicar = document.getElementById('modal-publicar');
  const btnCerrarForm = document.getElementById('btn-cerrar-form');
  const formularioPublicar = document.getElementById('formulario-publicar');
  const zonaDrop = document.getElementById('zonaDrop');
  const inputArchivo = document.getElementById('archivo');
  const nombreArchivo = document.getElementById('nombre-archivo');
  const previewImagen = document.getElementById('preview-imagen');
  const tipoSelect = document.getElementById('tipo');
  const camposAdicionales = document.getElementById('campos-adicionales');
  const publicacionesCont = document.getElementById('publicaciones');
  const filtroBtns = document.querySelectorAll('.filtro-btn');

  // Mostrar modal
  btnPublicar.addEventListener('click', () => {
    modalPublicar.classList.remove('oculto');
  });
  // Cerrar modal
  btnCerrarForm.addEventListener('click', () => {
    modalPublicar.classList.add('oculto');
    formularioPublicar.reset();
    previewImagen.classList.add('oculto');
    nombreArchivo.textContent = 'Ningún archivo seleccionado';
    camposAdicionales.innerHTML = '';
  });
  // Cerrar modal al click fuera
  modalPublicar.addEventListener('click', (e) => {
    if (e.target === modalPublicar) btnCerrarForm.click();
  });

  // Campos dinámicos según tipo seleccionado
  tipoSelect.addEventListener('change', () => {
    const tipo = tipoSelect.value;
    camposAdicionales.innerHTML = '';
    if(tipo === 'producto') {
      camposAdicionales.innerHTML = `
        <label for="precio">Precio</label>
        <input type="number" id="precio" name="precio" min="0" step="0.01" required />
      `;
    } else if(tipo === 'mascota') {
      camposAdicionales.innerHTML = `
        <label for="edad">Edad (años)</label>
        <input type="number" id="edad" name="edad" min="0" required />
        <label for="raza">Raza</label>
        <input type="text" id="raza" name="raza" required />
      `;
    } else if(tipo === 'servicio') {
      camposAdicionales.innerHTML = `
        <label for="precio_servicio">Precio</label>
        <input type="number" id="precio_servicio" name="precio_servicio" min="0" step="0.01" required />
      `;
    }
  });

  // Zona drop
  zonaDrop.addEventListener('click', () => inputArchivo.click());
  zonaDrop.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      inputArchivo.click();
    }
  });
  zonaDrop.addEventListener('dragover', e => {
    e.preventDefault();
    zonaDrop.classList.add('highlight');
  });
  zonaDrop.addEventListener('dragleave', e => {
    zonaDrop.classList.remove('highlight');
  });
  zonaDrop.addEventListener('drop', e => {
    e.preventDefault();
    zonaDrop.classList.remove('highlight');
    if(e.dataTransfer.files.length) {
      inputArchivo.files = e.dataTransfer.files;
      mostrarArchivo();
    }
  });
  inputArchivo.addEventListener('change', mostrarArchivo);
  function mostrarArchivo(){
    if(inputArchivo.files.length > 0) {
      nombreArchivo.textContent = inputArchivo.files[0].name;
      const reader = new FileReader();
      reader.onload = e => {
        previewImagen.src = e.target.result;
        previewImagen.classList.remove('oculto');
      };
      reader.readAsDataURL(inputArchivo.files[0]);
    } else {
      nombreArchivo.textContent = 'Ningún archivo seleccionado';
      previewImagen.classList.add('oculto');
      previewImagen.src = '';
    }
  }

  // Cargar publicaciones con fetch
  async function cargarPublicaciones(categoria = 'todo') {
    publicacionesCont.innerHTML = '<p>Cargando publicaciones...</p>';
    try {
      const res = await fetch(`get_publicaciones.php?categoria=${categoria}`, { credentials: 'same-origin' });
      const data = await res.json();
      if(data.success) {
        mostrarPublicaciones(data.publicaciones);
      } else {
        publicacionesCont.innerHTML = `<p>${data.message || 'Error al cargar publicaciones.'}</p>`;
      }
    } catch (error) {
      publicacionesCont.innerHTML = `<p>Error de red.</p>`;
    }
  }

  // Mostrar publicaciones en HTML
  function mostrarPublicaciones(publicaciones) {
    if(publicaciones.length === 0) {
      publicacionesCont.innerHTML = '<p>No hay publicaciones para mostrar.</p>';
      return;
    }
    publicacionesCont.innerHTML = '';
    publicaciones.forEach(pub => {
      const div = document.createElement('div');
      div.className = 'publicacion-item';
      div.tabIndex = 0;
      div.innerHTML = `
        <img src="${pub.imagen ? pub.imagen : 'https://via.placeholder.com/300x180?text=Sin+imagen'}" alt="${pub.titulo}" />
        <h3>${pub.titulo}</h3>
        <p>${pub.descripcion}</p>
      `;
      publicacionesCont.appendChild(div);
    });
  }

  // Enviar formulario publicar
  formularioPublicar.addEventListener('submit', async e => {
    e.preventDefault();
    const formData = new FormData(formularioPublicar);
    // Ajustar campos según tipo
    const tipo = formData.get('tipo');
    if(!tipo) {
      alert('Seleccione un tipo válido');
      return;
    }
    // Validar campos según tipo
    if(tipo === 'producto' && !formData.get('precio')) {
      alert('Ingrese precio para producto');
      return;
    }
    if(tipo === 'mascota' && (!formData.get('edad') || !formData.get('raza'))) {
      alert('Complete edad y raza para mascota');
      return;
    }
    if(tipo === 'servicio' && !formData.get('precio_servicio')) {
      alert('Ingrese precio para servicio');
      return;
    }

    try {
      const res = await fetch('crear_publicacion.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });
      const data = await res.json();
      if(data.success) {
        alert('Publicación creada');
        formularioPublicar.reset();
        previewImagen.classList.add('oculto');
        nombreArchivo.textContent = 'Ningún archivo seleccionado';
        modalPublicar.classList.add('oculto');
        cargarPublicaciones(filtroActual);
      } else {
        alert(data.message || 'Error al crear publicación');
      }
    } catch (error) {
      alert('Error de red al enviar publicación');
    }
  });

  // Filtrar publicaciones
  let filtroActual = 'todo';
  filtroBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filtroBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      filtroActual = btn.dataset.categoria;
      cargarPublicaciones(filtroActual);
    });
  });

  // Carga inicial
  cargarPublicaciones();

</script>

</body>
</html>
