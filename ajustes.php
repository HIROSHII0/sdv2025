<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.html');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = "";

$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefono_movil = $_POST['telefono_movil'] ?? '';
    $telefono_casa = $_POST['telefono_casa'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $provincia = $_POST['provincia'] ?? '';
    $ciudad = $_POST['ciudad'] ?? '';
    $pais = $_POST['pais'] ?? '';
    $codigo_postal = $_POST['codigo_postal'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $estado_civil = $_POST['estado_civil'] ?? '';
    $tipo_documento = $_POST['tipo_documento'] ?? '';
    $numero_documento = $_POST['numero_documento'] ?? '';
    $ocupacion = $_POST['ocupacion'] ?? '';
    $empresa = $_POST['empresa'] ?? '';
    $nivel_educativo = $_POST['nivel_educativo'] ?? '';
    $biografia = $_POST['biografia'] ?? '';

    $foto_perfil = null;
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['foto_perfil']['tmp_name'];
        $file_name = basename($_FILES['foto_perfil']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_ext, $allowed)) {
            $new_name = "user_" . $usuario_id . "." . $file_ext;
            $target_path = $upload_dir . $new_name;

            if (move_uploaded_file($file_tmp, $target_path)) {
                $foto_perfil = "uploads/" . $new_name;
            } else {
                $mensaje = "Error al subir la foto.";
            }
        } else {
            $mensaje = "Formato de foto no permitido. Usa jpg, jpeg, png o gif.";
        }
    }

    $sql_check = "SELECT id, foto_perfil FROM datos_usuarios WHERE usuario_id = ?";
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $foto_actual);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        if (!$foto_perfil) $foto_perfil = $foto_actual;

        $sql_update = "UPDATE datos_usuarios SET telefono_movil=?, telefono_casa=?, direccion=?, provincia=?, ciudad=?, pais=?, codigo_postal=?, fecha_nacimiento=?, genero=?, estado_civil=?, tipo_documento=?, numero_documento=?, ocupacion=?, empresa=?, nivel_educativo=?, biografia=?, foto_perfil=? WHERE usuario_id=?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sssssssssssssssssi", $telefono_movil, $telefono_casa, $direccion, $provincia, $ciudad, $pais, $codigo_postal, $fecha_nacimiento, $genero, $estado_civil, $tipo_documento, $numero_documento, $ocupacion, $empresa, $nivel_educativo, $biografia, $foto_perfil, $usuario_id);
        if ($stmt_update->execute()) {
            $mensaje = "Datos actualizados correctamente.";
        } else {
            $mensaje = "Error al actualizar datos.";
        }
        $stmt_update->close();
    } else {
        $sql_insert = "INSERT INTO datos_usuarios (usuario_id, telefono_movil, telefono_casa, direccion, provincia, ciudad, pais, codigo_postal, fecha_nacimiento, genero, estado_civil, tipo_documento, numero_documento, ocupacion, empresa, nivel_educativo, biografia, foto_perfil) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("isssssssssssssssss", $usuario_id, $telefono_movil, $telefono_casa, $direccion, $provincia, $ciudad, $pais, $codigo_postal, $fecha_nacimiento, $genero, $estado_civil, $tipo_documento, $numero_documento, $ocupacion, $empresa, $nivel_educativo, $biografia, $foto_perfil);
        if ($stmt_insert->execute()) {
            $mensaje = "Datos guardados correctamente.";
        } else {
            $mensaje = "Error al guardar datos.";
        }
        $stmt_insert->close();
    }
    $stmt->close();
}

$sql = "SELECT telefono_movil, telefono_casa, direccion, provincia, ciudad, pais, codigo_postal, fecha_nacimiento, genero, estado_civil, tipo_documento, numero_documento, ocupacion, empresa, nivel_educativo, biografia, foto_perfil FROM datos_usuarios WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$datos = $resultado->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Ajustes de Usuario</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <style>
  /* Reset */
*,
*::before,
*::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: 'Montserrat', sans-serif;
  background-color: #121212;
  color: #e0e0e0;
  min-height: 100vh;
  padding-top: 64px; /* espacio para barra superior */
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

/* Barra de acceso rápido (topbar) */
.topbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 64px;
  background-color: #1f2937; /* gris oscuro azulado */
  display: flex;
  align-items: center;
  padding: 0 2rem;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.7);
  z-index: 1000;
  font-weight: 600;
  font-size: 1rem;
}

.topbar a {
  color: #9ca3af; /* gris claro */
  text-decoration: none;
  margin-right: 1.8rem;
  padding: 0.4rem 0.8rem;
  border-radius: 8px;
  transition: background-color 0.3s ease, color 0.3s ease;
  user-select: none;
}

.topbar a:hover,
.topbar a.active {
  background-color: #2563eb; /* azul brillante */
  color: #fff;
  box-shadow: 0 0 10px #2563ebaa;
}

.topbar a.logout {
  margin-left: auto;
  background-color: transparent;
}

.topbar a.logout:hover {
  background-color: #dc2626; /* rojo */
  color: #fff;
  box-shadow: 0 0 10px #dc2626aa;
}

/* Contenedor principal */
.container-ajustes {
  max-width: 480px;
  background-color: #1e293b; /* gris azulado oscuro */
  margin: 2rem auto 4rem;
  border-radius: 16px;
  box-shadow: 0 6px 30px rgba(0, 0, 0, 0.9);
  padding: 2.5rem 2rem 3rem;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

/* Título */
h2 {
  font-size: 2rem;
  font-weight: 800;
  text-align: center;
  color: #60a5fa; /* azul claro */
  user-select: none;
}

/* Mensaje */
p.mensaje {
  text-align: center;
  font-weight: 700;
  font-size: 1.1rem;
  color: #22c55e; /* verde */
  user-select: none;
}

/* Foto perfil */
.foto-perfil-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.8rem;
  user-select: none;
}

.foto-perfil-container img {
  width: 130px;
  height: 130px;
  border-radius: 50%;
  border: 4px solid #2563eb;
  object-fit: cover;
  box-shadow: 0 4px 15px #2563ebaa;
  transition: box-shadow 0.3s ease;
  cursor: default;
}

.foto-perfil-container img:hover {
  box-shadow: 0 6px 25px #2563ebcc;
}

.foto-perfil-container input[type="file"] {
  cursor: pointer;
  color: #60a5fa;
  font-weight: 600;
  font-size: 0.9rem;
  background-color: transparent;
  border: none;
  text-align: center;
  transition: color 0.3s ease;
}

.foto-perfil-container input[type="file"]:hover {
  color: #93c5fd;
}

/* Formulario - vertical */
form {
  display: flex;
  flex-direction: column;
  gap: 1.3rem;
}

/* Label */
label {
  font-weight: 700;
  font-size: 0.95rem;
  color: #cbd5e1; /* gris claro */
  user-select: none;
}

/* Inputs y select */
input[type="text"],
input[type="date"],
select,
textarea {
  width: 100%;
  padding: 12px 16px;
  font-size: 1rem;
  border-radius: 12px;
  border: 2px solid #334155; /* gris oscuro */
  background-color: #0f172a; /* fondo input */
  color: #e0e0e0;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
  font-family: 'Montserrat', sans-serif;
  resize: vertical;
}

input[type="text"]:focus,
input[type="date"]:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: #2563eb;
  box-shadow: 0 0 12px #2563ebaa;
}

/* Textarea */
textarea {
  min-height: 110px;
  font-family: 'Montserrat', sans-serif;
  line-height: 1.4;
  color: #e0e0e0;
}

/* Botón */
button {
  margin-top: 1rem;
  padding: 14px 0;
  font-size: 1.25rem;
  font-weight: 800;
  border-radius: 14px;
  border: none;
  cursor: pointer;
  background: linear-gradient(90deg, #2563eb, #60a5fa);
  color: white;
  box-shadow: 0 6px 20px rgba(37, 99, 235, 0.6);
  transition: background-color 0.3s ease, box-shadow 0.3s ease, transform 0.15s ease;
  user-select: none;
}

button:hover {
  background: linear-gradient(90deg, #1d4ed8, #3b82f6);
  box-shadow: 0 8px 28px rgba(29, 78, 216, 0.8);
  transform: translateY(-2px);
}

button:active {
  transform: translateY(0);
  box-shadow: 0 6px 18px rgba(29, 78, 216, 0.6);
}

/* Responsive: pantalla pequeña */
@media (max-width: 520px) {
  .container-ajustes {
    margin: 1rem;
    padding: 2rem 1.5rem 2.5rem;
    width: auto;
  }
  
  h2 {
    font-size: 1.75rem;
  }
}

  </style>
</head>
<body>

<div class="topbar">
  <a href="perfil.php" class="active">Perfil</a>
  <a href="logout.php" class="logout">Cerrar sesión</a>
</div>


  <main class="container-ajustes">

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

      <label for="telefono_movil">Teléfono Móvil:</label>
      <input type="text" id="telefono_movil" name="telefono_movil" value="<?= htmlspecialchars($datos['telefono_movil'] ?? '') ?>">

      <label for="telefono_casa">Teléfono de Casa:</label>
      <input type="text" id="telefono_casa" name="telefono_casa" value="<?= htmlspecialchars($datos['telefono_casa'] ?? '') ?>">

      <label for="direccion">Dirección:</label>
      <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($datos['direccion'] ?? '') ?>">

      <label for="provincia">Provincia:</label>
      <input type="text" id="provincia" name="provincia" value="<?= htmlspecialchars($datos['provincia'] ?? '') ?>">

      <label for="ciudad">Ciudad:</label>
      <input type="text" id="ciudad" name="ciudad" value="<?= htmlspecialchars($datos['ciudad'] ?? '') ?>">

      <label for="pais">País:</label>
      <input type="text" id="pais" name="pais" value="<?= htmlspecialchars($datos['pais'] ?? '') ?>">

      <label for="codigo_postal">Código Postal:</label>
      <input type="text" id="codigo_postal" name="codigo_postal" value="<?= htmlspecialchars($datos['codigo_postal'] ?? '') ?>">

      <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
      <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($datos['fecha_nacimiento'] ?? '') ?>">

      <label for="genero">Género:</label>
      <select id="genero" name="genero">
        <option value="">Seleccione</option>
        <option value="Masculino" <?= ($datos['genero'] ?? '') === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
        <option value="Femenino" <?= ($datos['genero'] ?? '') === 'Femenino' ? 'selected' : '' ?>>Femenino</option>
        <option value="Otro" <?= ($datos['genero'] ?? '') === 'Otro' ? 'selected' : '' ?>>Otro</option>
      </select>

      <label for="estado_civil">Estado Civil:</label>
      <select id="estado_civil" name="estado_civil">
        <option value="">Seleccione</option>
        <option value="Soltero" <?= ($datos['estado_civil'] ?? '') === 'Soltero' ? 'selected' : '' ?>>Soltero</option>
        <option value="Casado" <?= ($datos['estado_civil'] ?? '') === 'Casado' ? 'selected' : '' ?>>Casado</option>
        <option value="Divorciado" <?= ($datos['estado_civil'] ?? '') === 'Divorciado' ? 'selected' : '' ?>>Divorciado</option>
        <option value="Viudo" <?= ($datos['estado_civil'] ?? '') === 'Viudo' ? 'selected' : '' ?>>Viudo</option>
        <option value="Otro" <?= ($datos['estado_civil'] ?? '') === 'Otro' ? 'selected' : '' ?>>Otro</option>
      </select>

      <label for="tipo_documento">Tipo de Documento:</label>
      <select id="tipo_documento" name="tipo_documento">
        <option value="">Seleccione</option>
        <option value="Cédula" <?= ($datos['tipo_documento'] ?? '') === 'Cédula' ? 'selected' : '' ?>>Cédula</option>
        <option value="Pasaporte" <?= ($datos['tipo_documento'] ?? '') === 'Pasaporte' ? 'selected' : '' ?>>Pasaporte</option>
        <option value="Licencia" <?= ($datos['tipo_documento'] ?? '') === 'Licencia' ? 'selected' : '' ?>>Licencia</option>
        <option value="Otro" <?= ($datos['tipo_documento'] ?? '') === 'Otro' ? 'selected' : '' ?>>Otro</option>
      </select>

      <label for="numero_documento">Número de Documento:</label>
      <input type="text" id="numero_documento" name="numero_documento" value="<?= htmlspecialchars($datos['numero_documento'] ?? '') ?>">

      <label for="ocupacion">Ocupación:</label>
      <input type="text" id="ocupacion" name="ocupacion" value="<?= htmlspecialchars($datos['ocupacion'] ?? '') ?>">

      <label for="empresa">Empresa:</label>
      <input type="text" id="empresa" name="empresa" value="<?= htmlspecialchars($datos['empresa'] ?? '') ?>">

      <label for="nivel_educativo">Nivel Educativo:</label>
      <select id="nivel_educativo" name="nivel_educativo">
        <option value="">Seleccione</option>
        <option value="Primaria" <?= ($datos['nivel_educativo'] ?? '') === 'Primaria' ? 'selected' : '' ?>>Primaria</option>
        <option value="Secundaria" <?= ($datos['nivel_educativo'] ?? '') === 'Secundaria' ? 'selected' : '' ?>>Secundaria</option>
        <option value="Técnico" <?= ($datos['nivel_educativo'] ?? '') === 'Técnico' ? 'selected' : '' ?>>Técnico</option>
        <option value="Universitario" <?= ($datos['nivel_educativo'] ?? '') === 'Universitario' ? 'selected' : '' ?>>Universitario</option>
        <option value="Postgrado" <?= ($datos['nivel_educativo'] ?? '') === 'Postgrado' ? 'selected' : '' ?>>Postgrado</option>
      </select>

      <label for="biografia" class="full-width">Biografía:</label>
      <textarea id="biografia" name="biografia" class="full-width"><?= htmlspecialchars($datos['biografia'] ?? '') ?></textarea>

      <button type="submit">Guardar Cambios</button>
    </form>

  </main>

</body>
</html>
