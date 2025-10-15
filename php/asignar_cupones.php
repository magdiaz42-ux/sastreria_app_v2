<?php
include_once "conexion.php";
include_once "generar_cupones.php";

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

// === PARÁMETROS ===
$id_usuario = intval($_POST["id_usuario"] ?? 0);
$codigo = strtoupper(trim($_POST["codigo"] ?? ''));

if (!$id_usuario) {
  echo json_encode(["status" => "error", "mensaje" => "❌ Falta el ID de usuario"]);
  exit;
}

// === VALIDAR EXISTENCIA DEL USUARIO ===
$userCheck = $conexion->prepare("SELECT id FROM usuarios WHERE id = ? LIMIT 1");
$userCheck->bind_param("i", $id_usuario);
$userCheck->execute();
$user = $userCheck->get_result()->fetch_assoc();
$userCheck->close();

if (!$user) {
  echo json_encode(["status" => "error", "mensaje" => "❌ Usuario inexistente"]);
  exit;
}

// === VALIDAR CÓDIGO DE ENTRADA (opcional, si se envía) ===
if ($codigo) {
  $checkEntrada = $conexion->prepare("SELECT id, estado FROM entradas WHERE UPPER(codigo)=? LIMIT 1");
  $checkEntrada->bind_param("s", $codigo);
  $checkEntrada->execute();
  $entrada = $checkEntrada->get_result()->fetch_assoc();
  $checkEntrada->close();

  if (!$entrada) {
    echo json_encode(["status" => "error", "mensaje" => "⚠️ Código de entrada no válido"]);
    exit;
  }
  if (strtolower($entrada["estado"]) === "usado") {
    echo json_encode(["status" => "error", "mensaje" => "⚠️ Este código ya fue utilizado"]);
    exit;
  }

  // Marcar el código como usado
  $upd = $conexion->prepare("UPDATE entradas SET estado='usado', id_usuario=? WHERE id=?");
  $upd->bind_param("ii", $id_usuario, $entrada["id"]);
  $upd->execute();
  $upd->close();
}

// === VERIFICAR SI YA TIENE CUPONES ===
$check = $conexion->prepare("SELECT COUNT(*) AS total FROM cupones WHERE id_usuario = ?");
$check->bind_param("i", $id_usuario);
$check->execute();
$total = ($check->get_result()->fetch_assoc()["total"]) ?? 0;
$check->close();

if ($total >= 10) {
  echo json_encode([
    "status" => "ok",
    "mensaje" => "El usuario ya tiene $total cupones asignados.",
    "cupones_asignados" => $total
  ]);
  exit;
}

// === GENERAR LOS CUPONES QUE FALTAN ===
try {
  generarCupones($conexion, $id_usuario);

  // Confirmar cantidad real
  $check2 = $conexion->prepare("SELECT COUNT(*) AS total FROM cupones WHERE id_usuario = ?");
  $check2->bind_param("i", $id_usuario);
  $check2->execute();
  $nuevoTotal = ($check2->get_result()->fetch_assoc()["total"]) ?? 0;
  $check2->close();

  echo json_encode([
    "status" => "ok",
    "mensaje" => "✅ Cupones asignados correctamente",
    "cupones_asignados" => $nuevoTotal
  ]);
} catch (Throwable $e) {
  echo json_encode([
    "status" => "error",
    "mensaje" => "❌ Error generando cupones: " . $e->getMessage()
  ]);
}
?>
