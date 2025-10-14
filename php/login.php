<?php
// ====================================
// LOGIN BACKEND - LA SASTRERÍA (versión estable)
// ====================================

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

include 'conexion.php';

// --- VERIFICAR MÉTODO ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

// --- DATOS DEL FORMULARIO ---
$usuario = trim($_POST['usuario'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($usuario) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Faltan datos obligatorios."]);
    exit;
}

// --- BUSCAR USUARIO POR TELÉFONO O EMAIL ---
$sql = "SELECT id, nombre, telefono, email, password, avatar, selfie 
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

// --- DETERMINAR IMAGEN FINAL (según prioridad) ---
$img_final = "assets/img/avatars/avatar1.png"; // valor por defecto

if (!empty($usuarioData['selfie'])) {
    // Si la ruta contiene "../" o "php/", la corregimos
    $selfiePath = str_replace(["../", "php/"], "", $usuarioData['selfie']);
    $img_final = $selfiePath;
} elseif (!empty($usuarioData['avatar'])) {
    $avatarPath = str_replace(["../", "php/"], "", $usuarioData['avatar']);
    $img_final = $avatarPath;
}

// --- FORMATEAR RESPUESTA ---
$response = [
    "status" => "success",
    "message" => "Inicio de sesión exitoso.",
    "id_usuario" => $usuarioData['id'],
    "nombre" => $usuarioData['nombre'],
    "telefono" => $usuarioData['telefono'],
    "email" => $usuarioData['email'],
    "avatar" => $img_final
];

echo json_encode($response);

$stmt->close();
$conexion->close();
?>
