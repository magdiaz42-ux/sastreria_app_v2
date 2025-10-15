<?php
/**
 * ASIGNAR CUPONES - LA SASTRERÍA (versión unificada)
 * --------------------------------------------------
 * Genera 10 cupones por usuario (1 fijo + 9 caprichos),
 * marca el código de entrada como usado y activa el usuario.
 */

header("Content-Type: application/json; charset=utf-8");
include_once "conexion.php";

// Compatibilidad $con / $conexion
if (!isset($conexion) && isset($con)) $conexion = $con;

// --- Recibir datos ---
$usuario_id = intval($_POST["usuario_id"] ?? 0);
$codigo     = strtoupper(trim($_POST["codigo"] ?? ''));

if (!$usuario_id || !$codigo) {
  echo json_encode(["status" => "error", "mensaje" => "Datos incompletos."]);
  exit;
}

try {
  // --- Validar usuario ---
  $checkUser = $conexion->prepare("SELECT id, estado FROM usuarios WHERE id = ? LIMIT 1");
  $checkUser->bind_param("i", $usuario_id);
  $checkUser->execute();
  $user = $checkUser->get_result()->fetch_assoc();

  if (!$user) {
    throw new Exception("Usuario inexistente.");
  }

  // --- Validar entrada ---
  $stmt = $conexion->prepare("SELECT id, estado FROM entradas WHERE codigo = ? LIMIT 1");
  $stmt->bind_param("s", $codigo);
  $stmt->execute();
  $entrada = $stmt->get_result()->fetch_assoc();

  if (!$entrada) {
    throw new Exception("Código de entrada no encontrado.");
  }

  if ($entrada["estado"] === "usado") {
    echo json_encode(["status" => "ok", "mensaje" => "El código ya fue utilizado."]);
    exit;
  }

  // --- Verificar si ya tiene cupones ---
  $checkCupones = $conexion->prepare("SELECT COUNT(*) AS total FROM cupones WHERE id_usuario = ? AND codigo_entrada = ?");
  $checkCupones->bind_param("is", $usuario_id, $codigo);
  $checkCupones->execute();
  $totalExistente = $checkCupones->get_result()->fetch_assoc()["total"] ?? 0;

  if ($totalExistente >= 10) {
    echo json_encode(["status" => "ok", "mensaje" => "Ya tiene cupones asignados."]);
    exit;
  }

  // --- Helper: generar código único ---
  function generarCodigo($conexion, $longitud = 8) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    do {
      $codigo = '';
      for ($i = 0; $i < $longitud; $i++) {
        $codigo .= $chars[random_int(0, strlen($chars) - 1)];
      }
      $verif = $conexion->prepare("SELECT 1 FROM cupones WHERE codigo_unico = ? LIMIT 1");
      $verif->bind_param("s", $codigo);
      $verif->execute();
      $existe = $verif->get_result()->num_rows > 0;
    } while ($existe);
    return $codigo;
  }

  // --- Transacción principal ---
  $conexion->begin_transaction();

  // --- Base de cupones ---
  $cupones = [];

  // Si hay tabla base, usarla
  $existeBase = $conexion->query("SHOW TABLES LIKE 'cupones_base'");
  if ($existeBase && $existeBase->num_rows > 0) {
    $baseFijo = $conexion->query("SELECT * FROM cupones_base WHERE tipo='trago' AND activo=1 ORDER BY RAND() LIMIT 1");
    $baseCap = $conexion->query("SELECT * FROM cupones_base WHERE tipo='capricho' AND activo=1 ORDER BY RAND() LIMIT 9");
    if ($baseFijo && $baseFijo->num_rows > 0) $cupones[] = $baseFijo->fetch_assoc();
    while ($r = $baseCap->fetch_assoc()) $cupones[] = $r;
  } else {
    // Si no existe tabla base, usar lista por defecto
    $cupones = [
      ["nombre" => "Trago de bienvenida", "tipo" => "trago", "descripcion" => "Primer trago gratis"],
      ["nombre" => "Capricho 1", "tipo" => "capricho", "descripcion" => "Cupón sorpresa"],
      ["nombre" => "Capricho 2", "tipo" => "capricho", "descripcion" => "Cupón sorpresa"],
      ["nombre" => "Capricho 3", "tipo" => "capricho", "descripcion" => "Cupón sorpresa"],
      ["nombre" => "Capricho 4", "tipo" => "capricho", "descripcion" => "Cupón sorpresa"],
      ["nombre" => "Capricho 5", "tipo" => "capricho", "descripcion" => "Cupón sorpresa"],
      ["nombre" => "Capricho 6", "tipo" => "capricho", "descripcion" => "Cupón sorpresa"],
      ["nombre" => "Capricho 7", "tipo" => "capricho", "descripcion" => "Cupón sorpresa"],
      ["nombre" => "Capricho 8", "tipo" => "capricho", "descripcion" => "Cupón sorpresa"],
      ["nombre" => "Capricho 9", "tipo" => "capricho", "descripcion" => "Cupón sorpresa"]
    ];
  }

  // --- Insertar cupones ---
  $ins = $conexion->prepare("
    INSERT INTO cupones (
      id_usuario, codigo_entrada, codigo_unico, nombre, descripcion, tipo, usado, estado, fecha_asignacion
    ) VALUES (?, ?, ?, ?, ?, ?, 0, 'activo', NOW())
  ");

  foreach ($cupones as $c) {
    $codigo_unico = generarCodigo($conexion);
    $nombre = $c["nombre"];
    $desc = $c["descripcion"];
    $tipo = $c["tipo"];
    $ins->bind_param("isssss", $usuario_id, $codigo, $codigo_unico, $nombre, $desc, $tipo);
    $ins->execute();
  }

  // --- Marcar entrada como usada ---
  $updEntrada = $conexion->prepare("UPDATE entradas SET estado='usado' WHERE id = ?");
  $updEntrada->bind_param("i", $entrada["id"]);
  $updEntrada->execute();

  // --- Activar usuario ---
  $conexion->query("UPDATE usuarios SET estado='activo' WHERE id = $usuario_id");

  $conexion->commit();

  echo json_encode([
    "status" => "ok",
    "mensaje" => "🎉 10 cupones asignados correctamente y usuario activado."
  ]);

} catch (Throwable $e) {
  $conexion->rollback();
  echo json_encode([
    "status" => "error",
    "mensaje" => "Fallo asignando cupones: " . $e->getMessage()
  ]);
}

$conexion->close();
?>
