<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = "";

// Inicializar array de datos con valores por defecto (sin correo editable)
$datos = [
    'nombre_completo' => '',
    'correo' => '',
    'telefono_movil' => '',
    'telefono_casa' => '',
    'direccion' => '',
    'ciudad' => '',
    'provincia' => '',
    'pais' => '',
    'codigo_postal' => '',
    'genero' => '',
    'fecha_nacimiento' => '',
    'estado_civil' => '',
    'tipo_documento' => '',
    'numero_documento' => '',
    'ocupacion' => '',
    'empresa' => '',
    'nivel_educativo' => '',
    'biografia' => '',
    'foto_perfil' => ''
];

$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger datos del formulario (sin correo editable)
    $datos_post = [
        'nombre_completo' => $_POST['nombre_completo'] ?? '',
        'telefono_movil' => $_POST['telefono_movil'] ?? '',
        'telefono_casa' => $_POST['telefono_casa'] ?? '',
        'direccion' => $_POST['direccion'] ?? '',
        'ciudad' => $_POST['ciudad'] ?? '',
        'provincia' => $_POST['provincia'] ?? '',
        'pais' => $_POST['pais'] ?? '',
        'codigo_postal' => $_POST['codigo_postal'] ?? '',
        'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? '',
        'genero' => $_POST['genero'] ?? '',
        'estado_civil' => $_POST['estado_civil'] ?? '',
        'tipo_documento' => $_POST['tipo_documento'] ?? '',
        'numero_documento' => $_POST['numero_documento'] ?? '',
        'ocupacion' => $_POST['ocupacion'] ?? '',
        'empresa' => $_POST['empresa'] ?? '',
        'nivel_educativo' => $_POST['nivel_educativo'] ?? '',
        'biografia' => $_POST['biografia'] ?? '',
        'foto_perfil' => $datos['foto_perfil'] // Mantener el valor actual por defecto
    ];

    // Procesar foto de perfil
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['foto_perfil']['tmp_name'];
        $file_name = basename($_FILES['foto_perfil']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed)) {
            $new_name = "user_" . $usuario_id . "." . $file_ext;
            $target_path = $upload_dir . $new_name;

            if (move_uploaded_file($file_tmp, $target_path)) {
                $datos_post['foto_perfil'] = "uploads/" . $new_name;
            } else {
                $mensaje = "Error al subir la foto.";
            }
        } else {
            $mensaje = "Formato de foto no permitido. Usa jpg, jpeg, png o gif.";
        }
    }

    // Verificar si ya existen datos del usuario
    $sql_check = "SELECT id, correo, foto_perfil FROM datos_usuarios WHERE usuario_id = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $correo_actual, $foto_actual);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        // Actualizar datos existentes (correo se mantiene igual)
        if (empty($datos_post['foto_perfil'])) {
            $datos_post['foto_perfil'] = $foto_actual;
        }

        $sql_update = "UPDATE datos_usuarios SET 
            nombre_completo=?, telefono_movil=?, telefono_casa=?, 
            direccion=?, ciudad=?, provincia=?, pais=?, codigo_postal=?, 
            fecha_nacimiento=?, genero=?, estado_civil=?, tipo_documento=?, 
            numero_documento=?, ocupacion=?, empresa=?, nivel_educativo=?, 
            biografia=?, foto_perfil=? 
            WHERE usuario_id=?";
        
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ssssssssssssssssssi", 
            $datos_post['nombre_completo'], $datos_post['telefono_movil'], 
            $datos_post['telefono_casa'], $datos_post['direccion'], $datos_post['ciudad'], 
            $datos_post['provincia'], $datos_post['pais'], $datos_post['codigo_postal'], 
            $datos_post['fecha_nacimiento'], $datos_post['genero'], $datos_post['estado_civil'], 
            $datos_post['tipo_documento'], $datos_post['numero_documento'], 
            $datos_post['ocupacion'], $datos_post['empresa'], $datos_post['nivel_educativo'], 
            $datos_post['biografia'], $datos_post['foto_perfil'], $usuario_id);
        
        if ($stmt_update->execute()) {
            $mensaje = "Datos actualizados correctamente.";
            $datos = $datos_post; // Actualizar array de datos con los nuevos valores
            $datos['correo'] = $correo_actual; // Mantener correo original en datos
        } else {
            $mensaje = "Error al actualizar datos: " . $stmt_update->error;
        }
        $stmt_update->close();
    } else {
        // Insertar nuevos datos (correo debe provenir de otro lado, aquí se usa correo actual de usuario para evitar edición)
        // Para inserción, lo ideal es que el correo ya esté registrado en otro lado, o sea tomado del usuario
        // Asumimos que existe una tabla usuarios con correo, lo consultamos:

        $sql_correo = "SELECT correo FROM usuarios WHERE id = ?";
        $stmt_correo = $conn->prepare($sql_correo);
        $stmt_correo->bind_param("i", $usuario_id);
        $stmt_correo->execute();
        $stmt_correo->bind_result($correo_usuario);
        $stmt_correo->fetch();
        $stmt_correo->close();

        $correo_a_insertar = $correo_usuario ?? '';

        $sql_insert = "INSERT INTO datos_usuarios (
            usuario_id, nombre_completo, correo, telefono_movil, telefono_casa, 
            direccion, ciudad, provincia, pais, codigo_postal, 
            fecha_nacimiento, genero, estado_civil, tipo_documento, 
            numero_documento, ocupacion, empresa, nivel_educativo, 
            biografia, foto_perfil
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("isssssssssssssssssss", 
            $usuario_id, $datos_post['nombre_completo'], $correo_a_insertar, 
            $datos_post['telefono_movil'], $datos_post['telefono_casa'], 
            $datos_post['direccion'], $datos_post['ciudad'], $datos_post['provincia'], 
            $datos_post['pais'], $datos_post['codigo_postal'], $datos_post['fecha_nacimiento'], 
            $datos_post['genero'], $datos_post['estado_civil'], $datos_post['tipo_documento'], 
            $datos_post['numero_documento'], $datos_post['ocupacion'], $datos_post['empresa'], 
            $datos_post['nivel_educativo'], $datos_post['biografia'], $datos_post['foto_perfil']);
        
        if ($stmt_insert->execute()) {
            $mensaje = "Datos guardados correctamente.";
            $datos = $datos_post;
            $datos['correo'] = $correo_a_insertar;
        } else {
            $mensaje = "Error al guardar datos: " . $stmt_insert->error;
        }
        $stmt_insert->close();
    }
    $stmt->close();
}

// Obtener datos actuales del usuario
$sql = "SELECT 
    nombre_completo, correo, telefono_movil, telefono_casa, direccion, 
    ciudad, provincia, pais, codigo_postal, fecha_nacimiento, genero, 
    estado_civil, tipo_documento, numero_documento, ocupacion, empresa, 
    nivel_educativo, biografia, foto_perfil 
    FROM datos_usuarios WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $datos_db = $resultado->fetch_assoc();
    $datos = array_merge($datos, $datos_db);
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Perfil Futurista</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700&family=Orbitron:wght@500;700&display=swap" rel="stylesheet" />
  <style>
    :root {
      --primary: #00f7ff;
      --secondary: #7b2dff;
      --dark: #0a0a15;
      --darker: #050510;
      --light: #e0e0ff;
      --neon-glow: 0 0 10px rgba(0, 247, 255, 0.8),
                   0 0 20px rgba(0, 247, 255, 0.6),
                   0 0 30px rgba(0, 247, 255, 0.4);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Montserrat', sans-serif;
      background-color: var(--darker);
      color: var(--light);
      min-height: 100vh;
      background-image: 
        radial-gradient(circle at 25% 25%, rgba(123, 45, 255, 0.15) 0%, transparent 50%),
        radial-gradient(circle at 75% 75%, rgba(0, 247, 255, 0.15) 0%, transparent 50%);
      overflow-x: hidden;
    }

    /* Efecto de partículas */
    body::before {
      content: "";
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: 
        radial-gradient(circle at 20% 30%, rgba(0, 247, 255, 0.05) 0%, transparent 2%) 0 0,
        radial-gradient(circle at 80% 70%, rgba(123, 45, 255, 0.05) 0%, transparent 2%) 0 0;
      background-size: 200px 200px;
      pointer-events: none;
      z-index: -1;
      animation: particleMove 100s infinite linear;
    }

    @keyframes particleMove {
      100% {
        background-position: 1000px 1000px, -1000px -1000px;
      }
    }

    /* Barra superior futurista */
    .topbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 70px;
      background: rgba(10, 10, 25, 0.8);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      display: flex;
      align-items: center;
      padding: 0 2rem;
      box-shadow: 0 0 15px rgba(0, 247, 255, 0.2);
      z-index: 1000;
      border-bottom: 1px solid rgba(0, 247, 255, 0.1);
    }

    .topbar a {
      color: var(--light);
      text-decoration: none;
      margin-right: 1.5rem;
      padding: 0.5rem 1rem;
      border-radius: 6px;
      font-weight: 600;
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
    }

    .topbar a::before {
      content: "";
      position: absolute;
      bottom: 0;
      left: 0;
      width: 0;
      height: 2px;
      background: var(--primary);
      transition: width 0.3s ease;
    }

    .topbar a:hover {
      color: var(--primary);
      text-shadow: 0 0 8px var(--primary);
    }

    .topbar a:hover::before {
      width: 100%;
    }

    .topbar a.active {
      color: var(--primary);
      text-shadow: 0 0 8px var(--primary);
    }

    .topbar a.active::before {
      width: 100%;
    }

    .topbar a.logout {
      margin-left: auto;
      color: #ff4d7b;
    }

    .topbar a.logout:hover {
      text-shadow: 0 0 8px #ff4d7b;
    }

    .topbar a.logout::before {
      background: #ff4d7b;
    }

    /* Contenedor principal holográfico */
    .container-ajustes {
      max-width: 600px;
      background: rgba(10, 10, 25, 0.7);
      margin: 100px auto;
      border-radius: 20px;
      padding: 2.5rem;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(0, 247, 255, 0.2);
      box-shadow: 0 0 30px rgba(0, 247, 255, 0.1),
                  inset 0 0 20px rgba(0, 247, 255, 0.05);
      position: relative;
      overflow: hidden;
      transition: transform 0.5s ease, box-shadow 0.5s ease;
    }

    .container-ajustes::before {
      content: "";
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: linear-gradient(
        to bottom right,
        transparent 45%,
        rgba(0, 247, 255, 0.03) 50%,
        transparent 55%
      );
      animation: holographicEffect 6s infinite linear;
      pointer-events: none;
      z-index: -1;
    }

    @keyframes holographicEffect {
      0% {
        transform: rotate(0deg) translate(-10%, -10%);
      }
      100% {
        transform: rotate(360deg) translate(-10%, -10%);
      }
    }

    .container-ajustes:hover {
      transform: translateY(-5px);
      box-shadow: 0 0 40px rgba(0, 247, 255, 0.2),
                  inset 0 0 20px rgba(0, 247, 255, 0.1);
    }

    /* Título con efecto neón */
    h2 {
      font-family: 'Orbitron', sans-serif;
      font-size: 2.5rem;
      text-align: center;
      margin-bottom: 1.5rem;
      color: var(--primary);
      text-shadow: var(--neon-glow);
      letter-spacing: 2px;
      position: relative;
      animation: neonPulse 2s infinite alternate;
    }

    @keyframes neonPulse {
      from {
        text-shadow: var(--neon-glow);
      }
      to {
        text-shadow: 0 0 15px rgba(0, 247, 255, 0.9),
                    0 0 30px rgba(0, 247, 255, 0.7),
                    0 0 45px rgba(0, 247, 255, 0.5);
      }
    }

    /* Mensaje flotante */
    .mensaje {
      background: rgba(0, 247, 255, 0.1);
      color: var(--primary);
      padding: 1rem;
      border-radius: 10px;
      text-align: center;
      margin-bottom: 1.5rem;
      border: 1px solid rgba(0, 247, 255, 0.3);
      animation: floatUp 0.5s ease-out;
      box-shadow: 0 0 15px rgba(0, 247, 255, 0.2);
    }

    @keyframes floatUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Foto de perfil con efecto 3D */
    .foto-perfil-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-bottom: 2rem;
      perspective: 1000px;
    }

    .foto-perfil-container img {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid var(--primary);
      box-shadow: var(--neon-glow);
      transition: all 0.5s ease;
      transform-style: preserve-3d;
    }

    .foto-perfil-container:hover img {
      transform: rotateY(20deg) scale(1.05);
      box-shadow: 0 0 25px var(--primary),
                 0 0 50px rgba(0, 247, 255, 0.5);
    }

    .foto-perfil-container input[type="file"] {
      margin-top: 1rem;
      background: rgba(0, 247, 255, 0.1);
      color: var(--primary);
      padding: 0.5rem 1rem;
      border-radius: 20px;
      border: 1px dashed var(--primary);
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
      font-weight: 600;
    }

    .foto-perfil-container input[type="file"]:hover {
      background: rgba(0, 247, 255, 0.2);
      box-shadow: 0 0 15px rgba(0, 247, 255, 0.3);
    }

    /* Formulario futurista */
    form {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
    }

    .full-width {
      grid-column: span 2;
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: var(--light);
      text-shadow: 0 0 5px rgba(0, 247, 255, 0.3);
      font-size: 0.9rem;
    }

    input[type="text"],
    input[type="date"],
    select,
    textarea {
      width: 100%;
      padding: 12px 15px;
      background: rgba(10, 10, 30, 0.7);
      border: 1px solid rgba(0, 247, 255, 0.3);
      border-radius: 8px;
      color: var(--light);
      font-family: 'Montserrat', sans-serif;
      transition: all 0.3s ease;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }

    input[type="text"]:focus,
    input[type="date"]:focus,
    select:focus,
    textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 15px rgba(0, 247, 255, 0.4);
      background: rgba(10, 10, 40, 0.9);
    }

    textarea {
      min-height: 120px;
      resize: vertical;
    }

    /* Botón con efecto de energía */
    button {
      grid-column: span 2;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      color: var(--dark);
      border: none;
      padding: 15px;
      font-size: 1.1rem;
      font-weight: 700;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      z-index: 1;
      font-family: 'Orbitron', sans-serif;
      letter-spacing: 1px;
      text-transform: uppercase;
      box-shadow: 0 0 15px rgba(0, 247, 255, 0.5);
    }

    button::before {
      content: "";
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: 0.5s;
      z-index: -1;
    }

    button:hover {
      transform: translateY(-3px);
      box-shadow: 0 0 25px rgba(0, 247, 255, 0.8);
    }

    button:hover::before {
      left: 100%;
    }

    /* Efecto para selects */
    select {
      appearance: none;
      background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%2300f7ff'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right 10px center;
      background-size: 15px;
    }

    /* Responsive */
    @media (max-width: 768px) {
      form {
        grid-template-columns: 1fr;
      }
      
      .full-width {
        grid-column: span 1;
      }
      
      .container-ajustes {
        margin: 100px 20px;
        padding: 1.5rem;
      }
      
      h2 {
        font-size: 2rem;
      }
    }

    /* Efecto de carga para inputs */
    @keyframes inputGlow {
      0%, 100% {
        border-color: rgba(0, 247, 255, 0.3);
      }
      50% {
        border-color: var(--primary);
      }
    }

    input:focus, select:focus, textarea:focus {
      animation: inputGlow 2s infinite;
    }
  </style>
</head>
<body>

<div class="topbar">
  <a href="perfil.php" class="active">Perfil</a>
  <a href="logout.php" class="logout">Cerrar sesión</a>
</div>

<main class="container-ajustes">
    <h2>MI PERFIL FUTURISTA</h2>
    
    <div class="foto-perfil-container" aria-label="Foto de perfil">
      <?php if (!empty($datos['foto_perfil'])): ?>
        <img src="<?= htmlspecialchars($datos['foto_perfil']) ?>" alt="Foto de perfil" loading="lazy" />
      <?php else: ?>
        <img src="https://via.placeholder.com/150?text=Perfil" alt="Sin foto de perfil" loading="lazy" />
      <?php endif; ?>
      <input type="file" name="foto_perfil" accept="image/*" aria-label="Subir foto de perfil" form="form-ajustes" />
    </div>

    <form id="form-ajustes" method="post" enctype="multipart/form-data" novalidate aria-label="Formulario de ajustes de usuario">
      <?php if ($mensaje): ?>
        <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
      <?php endif; ?>

      <!-- Mostrar el correo como texto estático, no editable -->
      <div class="full-width">
        <label>Correo Electrónico:</label>
        <p style="padding: 12px 15px; background: rgba(10,10,30,0.7); border-radius: 8px; color: var(--light); user-select: text;">
          <?= htmlspecialchars($datos['correo']) ?: 'No registrado' ?>
        </p>
      </div>

      <div>
        <label for="telefono_movil">Teléfono Móvil:</label>
        <input type="text" id="telefono_movil" name="telefono_movil" value="<?= htmlspecialchars($datos['telefono_movil']) ?>">
      </div>

      <div>
        <label for="telefono_casa">Teléfono de Casa:</label>
        <input type="text" id="telefono_casa" name="telefono_casa" value="<?= htmlspecialchars($datos['telefono_casa']) ?>">
      </div>

      <div class="full-width">
        <label for="direccion">Dirección:</label>
        <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($datos['direccion']) ?>">
      </div>

      <div>
        <label for="provincia">Provincia:</label>
        <input type="text" id="provincia" name="provincia" value="<?= htmlspecialchars($datos['provincia']) ?>">
      </div>

      <div>
        <label for="ciudad">Ciudad:</label>
        <input type="text" id="ciudad" name="ciudad" value="<?= htmlspecialchars($datos['ciudad']) ?>">
      </div>

      <div>
        <label for="pais">País:</label>
        <input type="text" id="pais" name="pais" value="<?= htmlspecialchars($datos['pais']) ?>">
      </div>

      <div>
        <label for="codigo_postal">Código Postal:</label>
        <input type="text" id="codigo_postal" name="codigo_postal" value="<?= htmlspecialchars($datos['codigo_postal']) ?>">
      </div>

      <div>
        <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($datos['fecha_nacimiento']) ?>">
      </div>

      <div>
        <label for="genero">Género:</label>
        <select id="genero" name="genero">
          <option value="">Seleccione</option>
          <option value="Masculino" <?= $datos['genero'] === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
          <option value="Femenino" <?= $datos['genero'] === 'Femenino' ? 'selected' : '' ?>>Femenino</option>
          <option value="Otro" <?= $datos['genero'] === 'Otro' ? 'selected' : '' ?>>Otro</option>
        </select>
      </div>

      <div>
        <label for="estado_civil">Estado Civil:</label>
        <select id="estado_civil" name="estado_civil">
          <option value="">Seleccione</option>
          <option value="Soltero" <?= $datos['estado_civil'] === 'Soltero' ? 'selected' : '' ?>>Soltero</option>
          <option value="Casado" <?= $datos['estado_civil'] === 'Casado' ? 'selected' : '' ?>>Casado</option>
          <option value="Divorciado" <?= $datos['estado_civil'] === 'Divorciado' ? 'selected' : '' ?>>Divorciado</option>
          <option value="Viudo" <?= $datos['estado_civil'] === 'Viudo' ? 'selected' : '' ?>>Viudo</option>
          <option value="Otro" <?= $datos['estado_civil'] === 'Otro' ? 'selected' : '' ?>>Otro</option>
        </select>
      </div>

      <div>
        <label for="tipo_documento">Tipo de Documento:</label>
        <select id="tipo_documento" name="tipo_documento">
          <option value="">Seleccione</option>
          <option value="Cédula" <?= $datos['tipo_documento'] === 'Cédula' ? 'selected' : '' ?>>Cédula</option>
          <option value="Pasaporte" <?= $datos['tipo_documento'] === 'Pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
          <option value="Licencia" <?= $datos['tipo_documento'] === 'Licencia' ? 'selected' : '' ?>>Licencia</option>
          <option value="Otro" <?= $datos['tipo_documento'] === 'Otro' ? 'selected' : '' ?>>Otro</option>
        </select>
      </div>

      <div>
        <label for="numero_documento">Número de Documento:</label>
        <input type="text" id="numero_documento" name="numero_documento" value="<?= htmlspecialchars($datos['numero_documento']) ?>">
      </div>

      <div>
        <label for="ocupacion">Ocupación:</label>
        <input type="text" id="ocupacion" name="ocupacion" value="<?= htmlspecialchars($datos['ocupacion']) ?>">
      </div>

      <div>
        <label for="empresa">Empresa:</label>
        <input type="text" id="empresa" name="empresa" value="<?= htmlspecialchars($datos['empresa']) ?>">
      </div>

      <div>
        <label for="nivel_educativo">Nivel Educativo:</label>
        <select id="nivel_educativo" name="nivel_educativo">
          <option value="">Seleccione</option>
          <option value="Primaria" <?= $datos['nivel_educativo'] === 'Primaria' ? 'selected' : '' ?>>Primaria</option>
          <option value="Secundaria" <?= $datos['nivel_educativo'] === 'Secundaria' ? 'selected' : '' ?>>Secundaria</option>
          <option value="Técnico" <?= $datos['nivel_educativo'] === 'Técnico' ? 'selected' : '' ?>>Técnico</option>
          <option value="Universitario" <?= $datos['nivel_educativo'] === 'Universitario' ? 'selected' : '' ?>>Universitario</option>
          <option value="Postgrado" <?= $datos['nivel_educativo'] === 'Postgrado' ? 'selected' : '' ?>>Postgrado</option>
        </select>
      </div>

      <div class="full-width">
        <label for="biografia">Biografía:</label>
        <textarea id="biografia" name="biografia"><?= htmlspecialchars($datos['biografia']) ?></textarea>
      </div>

      <button type="submit">GUARDAR CAMBIOS</button>
    </form>
</main>

</body>
</html>
