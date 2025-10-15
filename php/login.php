<?php
// ====================================
// LOGIN BACKEND - LA SASTRERÍA (v3 estable con roles y estado)
// ====================================

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// 🔸 Responder al preflight de CORS (en móviles o apps híbridas)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
  http_response_code(200);
  exit;
}

include 'conexion.php';

// Compatibilidad con $con o $conexion
if (!isset($conexion) && isset($con)) $conexion = $con;

// --- VERIFICAR MÉTODO ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo json_encode(["status" => "error", "message" => "Método no permitido."]);
  exit;
}

// --- DATOS DEL FORMULARIO ---
$usuario = trim($_POST['usuario'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($usuario === '' || $password === '') {
  echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios."]);
  exit;
}

// --- BUSCAR USUARIO POR TELÉFONO O EMAIL ---
$sql = "SELECT id, nombre, telefono, email, password, avatar, selfie, rol, estado 
        FROM usuarios 
        WHERE telefono = ? OR email = ? 
        LIMIT 1";

$stmt = $conexion->prepare($sql);
if (!$stmt) {
  echo json_encode(["status" => "error", "message" => "Error en la consulta: " . $conexion->error]);
  exit;
}

$stmt->bind_param("ss", $usuario, $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo json_encode(["status" => "error", "message" => "Usuario no encontrado."]);
  $stmt->close();
  $conexion->close();
  exit;
}

$usuarioData = $result->fetch_assoc();

// --- VERIFICAR CONTRASEÑA ---
if (!password_verify($password, $usuarioData['password'])) {
  echo json_encode(["status" => "error", "message" => "Contraseña incorrecta."]);
  $stmt->close();
  $conexion->close();
  exit;
}

// --- VALIDAR ESTADO ---
if (!empty($usuarioData['estado']) && strtolower($usuarioData['estado']) !== 'activo') {
  echo json_encode(["status" => "error", "message" => "Tu cuenta no está activa o fue bloqueada."]);
  $stmt->close();
  $conexion->close();
  exit;
}

// --- DETERMINAR IMAGEN FINAL (según prioridad) ---
$img_final = "assets/img/avatars/avatar1.png"; // por defecto

if (!empty($usuarioData['selfie'])) {
  $img_final = str_replace(["../", "php/"], "", $usuarioData['selfie']);
} elseif (!empty($usuarioData['avatar'])) {
  $img_final = str_replace(["../", "php/"], "", $usuarioData['avatar']);
}

// --- FORMATEAR RESPUESTA ---
$response = [
  "status" => "success",
  "message" => "Inicio de sesión exitoso.",
  "id_usuario" => (int)$usuarioData['id'],
  "nombre" => $usuarioData['nombre'],
  "telefono" => $usuarioData['telefono'],
  "email" => $usuarioData['email'],
  "avatar" => $img_final,
  "rol" => $usuarioData['rol'] ?? 'cliente',
  "estado" => $usuarioData['estado'] ?? 'activo'
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);

$stmt->close();
$conexion->close();
?>
