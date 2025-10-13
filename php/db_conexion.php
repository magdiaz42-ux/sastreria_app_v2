<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "sastreria_db";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
  die(json_encode([
    "status" => "error",
    "message" => "Error de conexión: " . $conn->connect_error
  ]));
}

$conn->set_charset("utf8");
?>
