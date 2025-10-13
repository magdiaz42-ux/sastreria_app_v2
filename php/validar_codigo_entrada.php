<?php
include 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

// Obtener datos del POST
$codigoEntrada = strtoupper(trim($_POST['codigo'] ?? ''));
$usuario_id = intval($_POST['usuario_id'] ?? 0);

if (empty($codigoEntrada) || $usuario_id <= 0) {
  echo json_encode(["status" => "error", "mensaje" => "Datos incompletos"]);
  exit;
}

// Validar entrada (pero sin marcarla usada todavía)
$check = $conexion->prepare("SELECT id, usado FROM entradas WHERE codigo = ?");
$check->bind_param("s", $codigoEntrada);
$check->execute();
$result = $check->get_result();

if ($result->num_rows == 0) {
  echo json_encode(["status" => "error", "mensaje" => "Código inválido"]);
  exit;
}

$row = $result->fetch_assoc();
if ($row['usado'] == 1) {
  echo json_encode(["status" => "error", "mensaje" => "El código ya fue utilizado."]);
  exit;
}

// 🚀 Asignar cupones (1 trago + 9 aleatorios)
$conexion->begin_transaction();

try {
  // 1️⃣ Cupón fijo (trago)
  $fijo = $conexion->query("SELECT id FROM cupones_base WHERE tipo = 'trago' LIMIT 1");
  if ($fijo && $fijo->num_rows > 0) {
    $f = $fijo->fetch_assoc();
    $insert = $conexion->prepare("INSERT INTO cupones_asignados (usuario_id, cupon_base_id, codigo_qr, usado) VALUES (?, ?, UUID(), 0)");
    $insert->bind_param("ii", $usuario_id, $f['id']);
    $insert->execute();
  }

  // 2️⃣ 9 cupones aleatorios (no tipo trago)
  $caprichos = $conexion->query("SELECT id FROM cupones_base WHERE tipo <> 'trago' ORDER BY RAND() LIMIT 9");
  while ($rowC = $caprichos->fetch_assoc()) {
    $insert = $conexion->prepare("INSERT INTO cupones_asignados (usuario_id, cupon_base_id, codigo_qr, usado) VALUES (?, ?, UUID(), 0)");
    $insert->bind_param("ii", $usuario_id, $rowC['id']);
    $insert->execute();
  }

  // ✅ recién ahora marcamos el código como usado
  $update = $conexion->prepare("UPDATE entradas SET usado = 1 WHERE codigo = ?");
  $update->bind_param("s", $codigoEntrada);
  $update->execute();

  $conexion->commit();

  echo json_encode(["status" => "ok", "mensaje" => "10 cupones asignados correctamente (1 trago + 9 aleatorios)"]);

} catch (Exception $e) {
  $conexion->rollback();
  echo json_encode(["status" => "error", "mensaje" => "Error al asignar cupones: " . $e->getMessage()]);
}
?>
