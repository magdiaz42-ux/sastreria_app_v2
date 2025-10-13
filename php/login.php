<?php
// ====================================
// LOGIN BACKEND - LA SASTRERÍA (mejorado)
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
$sql = "SELECT id, nombre, telefono, email, password, avatar, selfie FROM usuarios 
        WHERE telefono = ? OR email = ? LIMIT 1";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $usuario, $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Usuario no encontrado."]);
    exit;
}

$usuarioData = $result->fetch_assoc();

// --- VERIFICAR CONTRASEÑA ---
if (!password_verify($password, $usuarioData['password'])) {
    echo json_encode(["status" => "error", "message" => "Contraseña incorrecta."]);
    exit;
}

// --- DETERMINAR IMAGEN FINAL ---
$img_final = null;
if (!empty($usuarioData['selfie'])) {
    $img_final = $usuarioData['selfie'];
} elseif (!empty($usuarioData['avatar'])) {
    $img_final = $usuarioData['avatar'];
} else {
    $img_final = "assets/img/avatars/avatar1.png"; // Imagen por defecto
}

// --- RESPUESTA EXITOSA ---
echo json_encode([
    "status" => "success",
    "message" => "Inicio de sesión exitoso.",
    "id_usuario" => $usuarioData['id'],
    "nombre" => $usuarioData['nombre'],
    "telefono" => $usuarioData['telefono'],
    "email" => $usuarioData['email'],
    "avatar" => $img_final
]);

$stmt->close();
$conexion->close();
?>
