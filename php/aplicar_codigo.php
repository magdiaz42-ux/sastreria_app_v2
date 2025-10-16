<?php
/**
 * ===========================================
 * APLICAR NUEVO CÓDIGO - LA SASTRERÍA 🍸
 * ===========================================
 * Compatible con la tabla ENTRADAS:
 *   estado = 'disponible' (válido)
 *   estado = 'usado' (ya utilizado)
 */

include_once "conexion.php";
include_once "generar_cupones.php";
header("Content-Type: application/json; charset=utf-8");

$usuario_id = intval($_POST["usuario_id"] ?? 0);
$codigo     = strtoupper(trim($_POST["codigo"] ?? ''));

if (!$usuario_id || !$codigo) {
  echo json_encode(["status" => "error", "mensaje" => "Datos incompletos."]);
  exit;
}

try {
  // --- Buscar el código en la tabla entradas ---
  $stmt = $conexion->prepare("SELECT id, estado FROM entradas WHERE codigo = ?");
  $stmt->bind_param("s", $codigo);
  $stmt->execute();
  $entrada = $stmt->get_result()->fetch_assoc();

  if (!$entrada) {
    echo json_encode(["status" => "error", "mensaje" => "❌ Código no encontrado."]);
    exit;
  }

  // --- Validar estado disponible ---
  $estado = strtolower(trim($entrada["estado"]));
  if ($estado !== "disponible") {
    echo json_encode(["status" => "error", "mensaje" => "⚠️ Este código ya fue usado o no es válido."]);
    exit;
  }

  // --- Marcar como usado ---
  $update = $conexion->prepare("UPDATE entradas SET estado='usado' WHERE id=?");
  $update->bind_param("i", $entrada["id"]);
  $update->execute();

  // --- Generar los 10 cupones ---
  $ok = generarCupones($conexion, $usuario_id);

  if ($ok) {
    echo json_encode(["status" => "ok", "mensaje" => "🎉 Nuevos cupones agregados a tu cuenta."]);
  } else {
    echo json_encode(["status" => "error", "mensaje" => "❌ No se pudieron generar los cupones."]);
  }

} catch (Throwable $e) {
  echo json_encode(["status" => "error", "mensaje" => "Error interno: " . $e->getMessage()]);
}

$conexion->close();
?>
