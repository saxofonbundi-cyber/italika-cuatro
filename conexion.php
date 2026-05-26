<?php
// Configuración de la base de datos
$host = "localhost";
$user = "dev_user";
$password = "Devuser¿2026"; // <-- Pon tu contraseña real de MySQL aquí
$database = "italikacuatro";          // <-- Pon el nombre real de tu BD aquí

$conexion = mysqli_connect($host, $user, $password, $database);

if (!$conexion) {
    die("Error crítico de conexión: " . mysqli_connect_error());
}
?>