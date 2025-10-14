<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

include "conexion.php";

function generarCodigo($conexion, $longitud = 5) {
  $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  do {
    $codigo = '';
    for ($i = 0; $i < $longitud; $i++) {
      $codigo .= $chars[random_int(0, strlen($chars) - 1)];
    }

    $check = $conexion->prepare("SELECT id FROM entradas WHERE codigo = ? LIMIT 1");
    if (!$check) {
      throw new Exception("Error preparando SELECT: " . $conexion->error);
    }

    $check->bind_param("s", $codigo);
    $check->execute();
    $exists = $check->get_result()->num_rows > 0;
  } while ($exists);

  return $codigo;
}

try {
  // Validar que la tabla exista
  $tabla = $conexion->query("SHOW TABLES LIKE 'entradas'");
  if ($tabla->num_rows === 0) {
    throw new Exception("⚠️ La tabla 'entradas' no existe en la base de datos activa.");
  }

  $codigo = generarCodigo($conexion, 5);

  // Generar URL del QR
  $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?data=http://192.168.1.109/sastreria_app/registro.html%3Fcodigo=$codigo&size=200x200";

  // Insertar el nuevo ticket
  $stmt = $conexion->prepare("INSERT INTO entradas (codigo, estado, fecha_generado) VALUES (?, 'disponible', NOW())");
  if (!$stmt) {
    throw new Exception("Error preparando INSERT: " . $conexion->error);
  }

  $stmt->bind_param("s", $codigo);
  $stmt->execute();

  echo json_encode([
    "status" => "ok",
    "codigo" => $codigo,
    "qr" => $qrUrl
  ]);
} catch (Throwable $e) {
  echo json_encode([
    "status" => "error",
    "mensaje" => "Error generando ticket: " . $e->getMessage()
  ]);
}

$conexion->close();
?>
