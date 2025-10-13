<?php
header('Content-Type: application/json; charset=utf-8');
include "conexion.php";

// ✅ Cambiamos $conn → $conexion (porque tu conexion.php usa esa variable)

// Función para generar un código aleatorio de 5 letras (A-Z)
function generarCodigo($longitud = 5) {
  $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $out = '';
  for ($i = 0; $i < $longitud; $i++) {
    $out .= $chars[random_int(0, strlen($chars) - 1)];
  }
  return $out;
}

$codigo = generarCodigo();

// Inserta la entrada como "no usada"
$stmt = $conexion->prepare("INSERT INTO entradas (codigo, usado) VALUES (?, 0)");
$stmt->bind_param("s", $codigo);

if ($stmt->execute()) {
  // URL del registro (ajustá si lo movés de carpeta)
  $urlRegistro = "http://localhost/sastreria_app/registro.html";

  // QR del registro
  $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($urlRegistro);

  echo json_encode([
    "status" => "ok",
    "codigo" => $codigo,
    "qr" => $qrUrl,
    "url" => $urlRegistro
  ]);
  exit;
} else {
  http_response_code(500);
  echo json_encode([
    "status" => "error",
    "message" => "Error al guardar el código: " . $conexion->error
  ]);
}
