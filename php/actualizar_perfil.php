<?php
// ==========================
// ACTUALIZAR PERFIL / CONTRASEÑA - LA SASTRERÍA
// ==========================

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

include "conexion.php"; // Asegurate que $conexion esté definido ahí

// --- Verificar método ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit;
}

// --- Obtener datos ---
$id_usuario = isset($_POST["id_usuario"]) ? intval($_POST["id_usuario"]) : 0;
$campo = isset($_POST["campo"]) ? trim($_POST["campo"]) : "";
$valor = isset($_POST["valor"]) ? trim($_POST["valor"]) : "";

// --- Verificar caso de actualización de contraseña ---
$password_actual = $_POST["password_actual"] ?? null;
$password_nueva = $_POST["password_nueva"] ?? null;
$password_confirmar = $_POST["password_confirmar"] ?? null;

// --- Caso: actualización de contraseña ---
if ($password_actual && $password_nueva && $password_confirmar) {

    if ($password_nueva !== $password_confirmar) {
        echo json_encode(["status" => "error", "message" => "Las contraseñas nuevas no coinciden."]);
        exit;
    }

    if (strlen($password_nueva) < 6) {
        echo json_encode(["status" => "error", "message" => "La nueva contraseña debe tener al menos 6 caracteres."]);
        exit;
    }

    // Verificar contraseña actual
    $stmt = $conexion->prepare("SELECT password FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Usuario no encontrado."]);
        exit;
    }

    $user = $result->fetch_assoc();
    if (!password_verify($password_actual, $user["password"])) {
        echo json_encode(["status" => "error", "message" => "La contraseña actual es incorrecta."]);
        exit;
    }

    // Actualizar contraseña
    $hashNuevo = password_hash($password_nueva, PASSWORD_DEFAULT);
    $stmtUpdate = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
    $stmtUpdate->bind_param("si", $hashNuevo, $id_usuario);

    if ($stmtUpdate->execute()) {
        echo json_encode(["status" => "success", "message" => "Contraseña actualizada correctamente."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al actualizar la contraseña."]);
    }

    $stmtUpdate->close();
    $conexion->close();
    exit;
}

// --- Caso: actualización de datos del perfil ---
if ($id_usuario <= 0 || $campo === "" || $valor === "") {
    echo json_encode(["status" => "error", "message" => "Datos incompletos."]);
    exit;
}

$camposPermitidos = ["nombre", "telefono", "email"];
if (!in_array($campo, $camposPermitidos)) {
    echo json_encode(["status" => "error", "message" => "Campo no permitido."]);
    exit;
}

$sql = "UPDATE usuarios SET $campo = ? WHERE id = ?";
$stmt = $conexion->prepare($sql);
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Error en la preparación: " . $conexion->error]);
    exit;
}

$stmt->bind_param("si", $valor, $id_usuario);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Campo actualizado correctamente."]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al actualizar: " . $conexion->error]);
}

$stmt->close();
$conexion->close();
?>
