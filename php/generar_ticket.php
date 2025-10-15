<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

include "conexion.php";

// Compatibilidad con $con o $conexion
if (!isset($conexion) && isset($con)) $conexion = $con;

/**
 * Genera un código único de letras (A–Z) de longitud fija.
 */
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
  // Verificar si la tabla existe
  $tabla = $conexion->query("SHOW TABLES LIKE 'entradas'");
  if ($tabla->num_rows === 0) {
    throw new Exception("⚠️ La tabla 'entradas' no existe en la base de datos.");
  }

  // --- Recibir ID del cajero (opcional) ---
  $id_cajero = isset($_POST['id_cajero']) ? intval($_POST['id_cajero']) : null;

  // --- Generar código único ---
  $codigo = generarCodigo($conexion, 5);

  // --- Generar QR dinámico ---
  // Se codifica la URL para evitar errores de caracteres
  $baseUrl = "http://localhost/sastreria_app/registro/registro_paso1.html";
  $registroUrl = $baseUrl . "?codigo=" . urlencode($codigo);
  $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($registroUrl) . "&size=200x200";

  // --- Insertar ticket ---
  $stmt = $conexion->prepare("
    INSERT INTO entradas (codigo, estado, fecha_generado, id_cajero)
    VALUES (?, 'disponible', NOW(), ?)
  ");
  if (!$stmt) {
    throw new Exception("Error preparando INSERT: " . $conexion->error);
  }

  $stmt->bind_param("si", $codigo, $id_cajero);
  $stmt->execute();

  // --- Respuesta JSON ---
  echo json_encode([
    "status" => "ok",
    "mensaje" => "Ticket generado correctamente.",
    "codigo" => $codigo,
    "qr" => $qrUrl,
    "url_registro" => $registroUrl,
    "id_cajero" => $id_cajero
  ]);

} catch (Throwable $e) {
  echo json_encode([
    "status" => "error",
    "mensaje" => "Error generando ticket: " . $e->getMessage()
  ]);
}

$conexion->close();
?>
