<?php
require_once("conexion.php");
header("Content-Type: application/json; charset=UTF-8");

// Decodificar datos enviados desde fetch()
$data = json_decode(file_get_contents("php://input"), true);

// Validaciones
$nombre = trim($data["nombre"] ?? "");
$telefono_pais = trim($data["telefono_pais"] ?? "");
$telefono_num = trim($data["telefono_num"] ?? "");
$email = trim($data["email"] ?? "");
$password = trim($data["password"] ?? "");
$confirmar = trim($data["confirmar"] ?? "");

if (empty($nombre) || empty($telefono_pais) || empty($telefono_num) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Faltan campos obligatorios"]);
    exit;
}
if ($password !== $confirmar) {
    echo json_encode(["status" => "error", "message" => "Las contraseñas no coinciden"]);
    exit;
}

// Unificar teléfono completo
$telefono = $telefono_pais . $telefono_num;

// Verificar si ya existe ese teléfono
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ?");
$stmt->bind_param("s", $telefono);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "El teléfono ya está registrado"]);
    exit;
}

// Encriptar contraseña
$hash = password_hash($password, PASSWORD_BCRYPT);

// Insertar usuario
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, telefono, email, password, fecha_registro) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssss", $nombre, $telefono, $email, $hash);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Usuario registrado correctamente"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al registrar el usuario: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
