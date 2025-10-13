<?php
// Conexión a MySQL (ajustá los datos según tu entorno)
$servername = "localhost";
$username = "root";
$password = "";
$database = "sastreria_db";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}
?>
