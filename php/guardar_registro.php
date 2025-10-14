<?php
header('Content-Type: application/json; charset=utf-8');
include "conexion.php";

// --- Datos recibidos del formulario ---
$nombre = trim($_POST['nombre'] ?? '');
$codigoPais = trim($_POST['codigoPais'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$codigoEntrada = strtoupper(trim($_POST['codigo_entrada'] ?? ''));

if (!$nombre || !$telefono || !$password || !$codigoEntrada) {
    echo json_encode([
        "status" => "error",
        "message" => "Faltan campos obligatorios."
    ]);
    exit;
}

$telefonoCompleto = $codigoPais . $telefono;

// --- Verificar duplicado de teléfono ---
$sqlCheck = $conexion->prepare("SELECT id FROM usuarios WHERE telefono = ?");
$sqlCheck->bind_param("s", $telefonoCompleto);
$sqlCheck->execute();
$result = $sqlCheck->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "El teléfono ya está registrado."]);
    exit;
}

// --- Validar que el código de entrada exista y esté disponible ---
$stmt = $conexion->prepare("SELECT id, estado FROM entradas WHERE codigo = ? LIMIT 1");
$stmt->bind_param("s", $codigoEntrada);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Código de entrada inexistente."]);
    exit;
}

$entrada = $res->fetch_assoc();
if ($entrada['estado'] !== 'disponible') {
    echo json_encode(["status" => "error", "message" => "El código ya fue utilizado."]);
    exit;
}

// --- Registrar nuevo usuario ---
$hash = password_hash($password, PASSWORD_DEFAULT);
$sql = $conexion->prepare("INSERT INTO usuarios (nombre, telefono, email, password) VALUES (?, ?, ?, ?)");
$sql->bind_param("ssss", $nombre, $telefonoCompleto, $email, $hash);

if ($sql->execute()) {
    $usuario_id = $conexion->insert_id;

    echo json_encode([
        "status" => "success",
        "message" => "Usuario registrado correctamente.",
        "id_usuario" => $usuario_id
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al registrar: " . $conexion->error]);
}

$conexion->close();
?>
