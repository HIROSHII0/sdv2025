<?php
$host = "localhost";
$usuario = "root";
$contrasena = ""; // XAMPP no tiene contraseña por defecto
$base_de_datos = "ventas2025";

// Crear conexión
$conn = new mysqli($host, $usuario, $contrasena, $base_de_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
// echo "Conexión exitosa"; // Descomenta esta línea solo para probar
?>
