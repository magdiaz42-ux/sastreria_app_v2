<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

include "conexion.php";

try {
  // Asegurar que la tabla tenga columna "rol" (si no, la agregamos automáticamente)
  $conexion->query("
    ALTER TABLE usuarios
    ADD COLUMN IF NOT EXISTS rol VARCHAR(50) DEFAULT 'cliente'
  ");

  $sql = "SELECT id, nombre, telefono, email, rol FROM usuarios ORDER BY id DESC";
  $res = $conexion->query($sql);

  $usuarios = [];
  while ($fila = $res->fetch_assoc()) {
    $usuarios[] = $fila;
  }

  echo json_encode($usuarios ?: []);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode([
    "status" => "error",
    "message" => "Error al obtener usuarios: " . $e->getMessage()
  ]);
}

$conexion->close();
?>
