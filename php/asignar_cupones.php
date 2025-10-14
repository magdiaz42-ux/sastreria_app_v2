<?php
include "conexion.php";
header("Content-Type: application/json; charset=utf-8");

$usuario_id = intval($_POST["usuario_id"] ?? 0);
$codigo      = strtoupper(trim($_POST["codigo"] ?? ''));

if (!$usuario_id || !$codigo) {
  echo json_encode(["status" => "error", "mensaje" => "Datos incompletos"]);
  exit;
}

// ------- validar entrada --------
$st = $conexion->prepare("SELECT id, estado FROM entradas WHERE codigo = ? LIMIT 1");
$st->bind_param("s", $codigo);
$st->execute();
$entrada = $st->get_result()->fetch_assoc();
if (!$entrada) {
  echo json_encode(["status" => "error", "mensaje" => "Código de entrada no encontrado."]);
  exit;
}
if (!in_array($entrada["estado"], ["disponible","usado"])) {
  echo json_encode(["status" => "error", "mensaje" => "El código no es válido para asignar cupones."]);
  exit;
}

// ------- idempotencia: si ya se asignaron cupones para este código+usuario, no reasignar -------
$chk = $conexion->prepare("SELECT COUNT(*) c FROM cupones WHERE codigo_entrada=? AND id_usuario=?");
$chk->bind_param("si", $codigo, $usuario_id);
$chk->execute();
$exist = $chk->get_result()->fetch_assoc()["c"] ?? 0;
if ($exist >= 10) {
  echo json_encode(["status"=>"ok","mensaje"=>"Ya tenía cupones asignados","total"=>$exist, "idempotente"=>true]);
  exit;
}

// ------- helper: generar código único y garantizar unicidad -------
function generarCodigo($conexion, $longitud = 5){
  $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  do {
    $cod = '';
    for ($i=0;$i<$longitud;$i++) $cod .= $chars[random_int(0, strlen($chars)-1)];
    $q = $conexion->prepare("SELECT 1 FROM cupones WHERE codigo_unico=? LIMIT 1");
    $q->bind_param("s", $cod);
    $q->execute();
    $existe = $q->get_result()->num_rows > 0;
  } while($existe);
  return $cod;
}

$conexion->begin_transaction();
try {
  // 1 fijo (trago)
  $fijo = $conexion->query("SELECT * FROM cupones_base WHERE tipo='trago' AND activo=1 ORDER BY RAND() LIMIT 1")->fetch_assoc();

  // 9 aleatorios (capricho)
  $aleatorios = $conexion->query("SELECT * FROM cupones_base WHERE tipo='capricho' AND activo=1 ORDER BY RAND() LIMIT 9");

  $a_insertar = [];
  if ($fijo) $a_insertar[] = $fijo;
  while ($r = $aleatorios->fetch_assoc()) $a_insertar[] = $r;
  // asegurar 10
  $a_insertar = array_slice($a_insertar, 0, 10);

  // si el usuario ya tenía algunos (por un intento previo parcial), completar hasta 10
  if ($exist > 0 && $exist < 10) {
    $faltan = 10 - $exist;
    // evitar volver a insertar el trago si ya existe
    $tieneTrago = $conexion
      ->query("SELECT 1 FROM cupones WHERE codigo_entrada='$codigo' AND id_usuario=$usuario_id AND tipo='trago' LIMIT 1")
      ->num_rows > 0;

    $a_insertar = [];
    if (!$tieneTrago) {
      $fijo2 = $conexion->query("SELECT * FROM cupones_base WHERE tipo='trago' AND activo=1 ORDER BY RAND() LIMIT 1")->fetch_assoc();
      if ($fijo2) { $a_insertar[] = $fijo2; $faltan--; }
    }
    if ($faltan > 0) {
      $resCap = $conexion->query("SELECT * FROM cupones_base WHERE tipo='capricho' AND activo=1 ORDER BY RAND() LIMIT $faltan");
      while ($r = $resCap->fetch_assoc()) $a_insertar[] = $r;
    }
  }

  $ins = $conexion->prepare("
    INSERT INTO cupones (
      codigo_entrada, id_usuario, id_base, codigo_unico,
      nombre, descripcion, tipo, estado, fecha_asignacion, usado
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, 'activo', NOW(), 0)
  ");

  $insertados = 0;
  foreach ($a_insertar as $c) {
    $codigo_unico = generarCodigo($conexion, 5);
    $ins->bind_param(
      "siissss",
      $codigo,
      $usuario_id,
      $c["id"],
      $codigo_unico,
      $c["nombre"],
      $c["descripcion"],
      $c["tipo"]
    );
    $ins->execute();
    $insertados++;
  }

  // marcar entrada como usada (idempotente)
  $upd = $conexion->prepare("UPDATE entradas SET estado='usado' WHERE id=?");
  $upd->bind_param("i", $entrada["id"]);
  $upd->execute();

  $conexion->commit();

  // total real asignado para este código+usuario
  $chk2 = $conexion->prepare("SELECT COUNT(*) c FROM cupones WHERE codigo_entrada=? AND id_usuario=?");
  $chk2->bind_param("si", $codigo, $usuario_id);
  $chk2->execute();
  $total = $chk2->get_result()->fetch_assoc()["c"] ?? 0;

  // clamp por seguridad (no debería pasar)
  if ($total > 10) $total = 10;

  echo json_encode([
    "status" => "ok",
    "mensaje" => "Asignación realizada",
    "insertados_ahora" => $insertados,
    "total_asignado" => $total
  ]);
} catch (Throwable $e) {
  $conexion->rollback();
  echo json_encode(["status"=>"error","mensaje"=>"Fallo asignando cupones: ".$e->getMessage()]);
}

$conexion->close();
