<?php
/**
 * GUARDAR RESPUESTAS - LA SASTRERÍA (versión unificada)
 * -----------------------------------------------------
 * Guarda las respuestas del paso 3 en formato JSON.
 * Marca al usuario como 'activo' y listo para generar cupones.
 */

header("Content-Type: application/json; charset=utf-8");
include_once "conexion.php";

try {
  $id_usuario = intval($_POST['id_usuario'] ?? 0);
  $respuestas_json = $_POST['respuestas'] ?? '';

  if (!$id_usuario || !$respuestas_json) {
    throw new Exception("Datos incompletos.");
  }

  $respuestas = json_decode($respuestas_json, true);
  if (!is_array($respuestas)) {
    throw new Exception("Formato de respuestas inválido.");
  }

  // --- Crear tabla si no existe (solo la primera vez) ---
  $conexion->query("
    CREATE TABLE IF NOT EXISTS respuestas (
      id INT AUTO_INCREMENT PRIMARY KEY,
      id_usuario INT NOT NULL,
      respuestas_json JSON NOT NULL,
      fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
  ");

  // --- Insertar registro JSON ---
  $stmt = $conexion->prepare("INSERT INTO respuestas (id_usuario, respuestas_json) VALUES (?, ?)");
  $jsonData = json_encode($respuestas, JSON_UNESCAPED_UNICODE);
  $stmt->bind_param("is", $id_usuario, $jsonData);
  $stmt->execute();

  // --- Actualizar estado del usuario ---
  $conexion->query("UPDATE usuarios SET estado = 'activo' WHERE id = $id_usuario");

  echo json_encode([
    "status" => "ok",
    "mensaje" => "Respuestas guardadas correctamente."
  ]);

} catch (Throwable $e) {
  echo json_encode([
    "status" => "error",
    "mensaje" => $e->getMessage()
  ]);
}

$conexion->close();
?>
