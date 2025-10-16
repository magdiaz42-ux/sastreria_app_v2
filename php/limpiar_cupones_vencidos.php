<?php
/**
 * ===========================================
 * LIMPIAR CUPONES VENCIDOS - LA SASTRERÍA 🍸
 * ===========================================
 * Elimina o marca los cupones con más de 24 hs
 * desde su fecha_asignacion.
 * 
 * Puedes probar manualmente llamando:
 * php/limpiar_cupones_vencidos.php?test=1
 */

include_once "conexion.php";
header("Content-Type: application/json; charset=utf-8");

// --- Si se pasa ?test=1, simulamos que hoy es +25hs adelante ---
$modoTest = isset($_GET["test"]) && $_GET["test"] == "1";

// --- Seleccionar cupones vencidos (más de 24 hs) ---
$query = $modoTest
  ? "SELECT id FROM cupones WHERE TIMESTAMPDIFF(HOUR, fecha_asignacion, DATE_ADD(NOW(), INTERVAL -25 HOUR)) > 24"
  : "SELECT id FROM cupones WHERE TIMESTAMPDIFF(HOUR, fecha_asignacion, NOW()) > 24";

$res = $conexion->query($query);
$total = $res ? $res->num_rows : 0;

if ($total === 0) {
  echo json_encode([
    "status" => "ok",
    "mensaje" => $modoTest
      ? "Modo prueba: no hay cupones que simulen vencidos (fecha_asignacion reciente)."
      : "No hay cupones vencidos en este momento.",
    "eliminados" => 0
  ]);
  exit;
}

// --- Borrar cupones vencidos ---
$del = $conexion->query($modoTest
  ? "DELETE FROM cupones WHERE TIMESTAMPDIFF(HOUR, fecha_asignacion, DATE_ADD(NOW(), INTERVAL -25 HOUR)) > 24"
  : "DELETE FROM cupones WHERE TIMESTAMPDIFF(HOUR, fecha_asignacion, NOW()) > 24"
);

echo json_encode([
  "status" => "ok",
  "mensaje" => $modoTest
    ? "🧹 Limpieza de prueba completada (simulando +25 hs)."
    : "🧹 Cupones vencidos eliminados correctamente.",
  "eliminados" => $total
]);

$conexion->close();
?>
