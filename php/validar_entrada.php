<?php
// ============================
// VALIDAR CÓDIGO DE ENTRADA (JSON para registro.html)
// ============================

header('Content-Type: application/json; charset=utf-8');
include "conexion.php";

// Recibir el código
$codigo = strtoupper(trim($_POST["codigo"] ?? ""));

// Verificar que venga algo
if (!$codigo) {
  echo json_encode(["status" => "error", "mensaje" => "Datos incompletos."]);
  exit;
}

// Validar que tenga 5 letras (solo A-Z)
if (!preg_match('/^[A-Z]{5}$/', $codigo)) {
  echo json_encode(["status" => "error", "mensaje" => "Código inválido. Debe tener 5 letras."]);
  exit;
}

// Buscar el código en la base
$stmt = $conn->prepare("SELECT id, usado FROM entradas WHERE codigo = ? LIMIT 1");
$stmt->bind_param("s", $codigo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo json_encode(["status" => "error", "mensaje" => "El código no existe o no fue generado."]);
  exit;
}

$entrada = $result->fetch_assoc();

// Verificar si ya fue usado
if ((int)$entrada["usado"] === 1) {
  echo json_encode(["status" => "error", "mensaje" => "El código ya fue utilizado."]);
  exit;
}

// Si todo está correcto
echo json_encode(["status" => "ok", "mensaje" => "Código válido y disponible."]);
exit;
?>
