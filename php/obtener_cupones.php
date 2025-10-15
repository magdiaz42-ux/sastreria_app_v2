<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

include "conexion.php";

$id_usuario = intval($_GET['id_usuario'] ?? 0);

if (!$id_usuario) {
  echo json_encode([
    "status" => "error",
    "mensaje" => "ID de usuario no recibido."
  ]);
  exit;
}

try {
  // --- Obtener info del usuario ---
  $usuario = $conexion->prepare("SELECT nombre, avatar FROM usuarios WHERE id = ?");
  $usuario->bind_param("i", $id_usuario);
  $usuario->execute();
  $resUsuario = $usuario->get_result()->fetch_assoc();

  if (!$resUsuario) {
    echo json_encode(["status" => "error", "mensaje" => "Usuario no encontrado."]);
    exit;
  }

  // --- Obtener cupones ---
  $query = $conexion->prepare("
    SELECT 
      id, 
      nombre, 
      descripcion, 
      tipo, 
      codigo_unico, 
      usado, 
      fecha_asignacion
    FROM cupones 
    WHERE id_usuario = ?
    ORDER BY tipo = 'trago' DESC, fecha_asignacion ASC
  ");
  $query->bind_param("i", $id_usuario);
  $query->execute();
  $res = $query->get_result();

  $cupones = [];
  while ($row = $res->fetch_assoc()) {
    $cupones[] = $row;
  }

  echo json_encode([
    "status" => "ok",
    "usuario" => [
      "nombre" => $resUsuario["nombre"],
      "avatar" => $resUsuario["avatar"]
    ],
    "total" => count($cupones),
    "cupones" => $cupones
  ]);
} catch (Throwable $e) {
  echo json_encode([
    "status" => "error",
    "mensaje" => "Error al obtener cupones: " . $e->getMessage()
  ]);
}

$conexion->close();
?>
