<?php
/**
 * ==========================================
 * REGISTRAR USUARIO - LA SASTRERÍA (FINAL)
 * ==========================================
 * - Valida código en tablas `entradas` o `codigos_entrada`
 * - Registra usuario nuevo
 * - Genera 10 cupones desde `cupones_base`
 * - Devuelve JSON limpio (sin HTML ni warnings)
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit(0);
}

try {
  include_once "conexion.php";
  include_once "generar_cupones.php";

  // --- Recibir datos del formulario ---
  $nombre   = trim($_POST['nombre'] ?? '');
  $telefono = trim($_POST['telefono'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $codigo   = strtoupper(trim($_POST['codigo_entrada'] ?? ''));

  // --- Validaciones básicas ---
  if (!$nombre || !$telefono || !$password || !$codigo) {
    throw new Exception("Faltan datos obligatorios");
  }

  // --- Verificar si el teléfono ya está registrado ---
  $exists = $conexion->prepare("SELECT id FROM usuarios WHERE telefono = ? LIMIT 1");
  $exists->bind_param("s", $telefono);
  $exists->execute();
  if ($exists->get_result()->num_rows > 0) {
    throw new Exception("El teléfono ya está registrado");
  }

  // --- Buscar el código en ENTRADAS o CODIGOS_ENTRADA ---
  $origen = null;
  $codeId = null;
  $estado = null;

  // Buscar primero en ENTRADAS
  $q1 = $conexion->prepare("SELECT id, estado FROM entradas WHERE UPPER(codigo) = ? LIMIT 1");
  $q1->bind_param("s", $codigo);
  $q1->execute();
  $r1 = $q1->get_result()->fetch_assoc();

  if ($r1) {
    $origen = "entradas";
    $codeId = intval($r1["id"]);
    $estado = strtolower($r1["estado"]);
  } else {
    // Buscar en CODIGOS_ENTRADA
    $q2 = $conexion->prepare("SELECT id, usado FROM codigos_entrada WHERE UPPER(codigo_unico) = ? LIMIT 1");
    $q2->bind_param("s", $codigo);
    $q2->execute();
    $r2 = $q2->get_result()->fetch_assoc();

    if ($r2) {
      $origen = "codigos_entrada";
      $codeId = intval($r2["id"]);
      $estado = intval($r2["usado"]) === 1 ? "usado" : "disponible";
    }
  }

  // --- Validar existencia y disponibilidad ---
  if (!$origen) {
    throw new Exception("Código inválido o inexistente");
  }

  if ($estado === "usado") {
    throw new Exception("Este código ya fue utilizado");
  }

  // --- Crear usuario nuevo ---
  $pass_hash = password_hash($password, PASSWORD_BCRYPT);
  $stmt = $conexion->prepare("
    INSERT INTO usuarios (nombre, telefono, email, password, codigo_entrada, fecha_registro)
    VALUES (?, ?, ?, ?, ?, NOW())
  ");
  $stmt->bind_param("sssss", $nombre, $telefono, $email, $pass_hash, $codigo);

  if (!$stmt->execute()) {
    throw new Exception("Error al registrar usuario: " . $stmt->error);
  }

  $id_usuario = $stmt->insert_id;

  // --- Marcar el código como usado en su tabla de origen ---
  if ($origen === "entradas") {
    $upd = $conexion->prepare("UPDATE entradas SET estado = 'usado', id_usuario = ? WHERE id = ?");
  } else {
    $upd = $conexion->prepare("UPDATE codigos_entrada SET usado = 1, id_usuario = ? WHERE id = ?");
  }
  $upd->bind_param("ii", $id_usuario, $codeId);
  $upd->execute();

  // --- Generar 10 cupones desde cupones_base ---
  if (!generarCupones($conexion, $id_usuario)) {
    throw new Exception("Error al generar cupones para el usuario.");
  }

  // --- Éxito ---
  echo json_encode([
    "status" => "ok",
    "id_usuario" => $id_usuario,
    "mensaje" => "✅ Registro exitoso. Se asignaron tus cupones."
  ]);
} catch (Throwable $e) {
  // --- Captura de error controlada ---
  http_response_code(500);
  echo json_encode([
    "status" => "error",
    "mensaje" => "❌ " . $e->getMessage()
  ]);
}

if (isset($conexion) && $conexion instanceof mysqli) {
  $conexion->close();
}
?>
