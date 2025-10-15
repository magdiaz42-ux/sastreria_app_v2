<?php
/**
 * GUARDAR REGISTRO - LA SASTRERÍA (ETAPA 1)
 * Registra usuario y valida el código de entrada.
 * No genera cupones todavía (eso se hace en el paso 3).
 */

header('Content-Type: application/json; charset=utf-8');
include "conexion.php";

try {
    // --- Datos recibidos ---
    $nombre        = trim($_POST['nombre'] ?? '');
    $codigoPais    = trim($_POST['codigoPais'] ?? '+54');
    $telefono      = trim($_POST['telefono'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $password      = trim($_POST['password'] ?? '');
    $codigoEntrada = strtoupper(trim($_POST['codigo_entrada'] ?? ''));

    // --- Validación básica ---
    if (!$nombre || !$telefono || !$password || !$codigoEntrada) {
        throw new Exception("Faltan campos obligatorios.");
    }

    // --- Normalizar teléfono ---
    $telefonoCompleto = preg_replace('/\D/', '', $codigoPais . $telefono);

    // --- Verificar duplicado de teléfono ---
    $checkTel = $conexion->prepare("SELECT id FROM usuarios WHERE telefono = ?");
    $checkTel->bind_param("s", $telefonoCompleto);
    $checkTel->execute();
    $resTel = $checkTel->get_result();
    if ($resTel->num_rows > 0) {
        throw new Exception("El teléfono ya está registrado.");
    }

    // --- Validar código de entrada ---
    $stmt = $conexion->prepare("SELECT id, estado FROM entradas WHERE codigo = ? LIMIT 1");
    $stmt->bind_param("s", $codigoEntrada);
    $stmt->execute();
    $entrada = $stmt->get_result()->fetch_assoc();

    if (!$entrada) {
        throw new Exception("Código de entrada inexistente.");
    }

    if (!in_array($entrada['estado'], ['disponible', 'pendiente'])) {
        throw new Exception("El código ya fue utilizado o no es válido.");
    }

    // --- Crear usuario ---
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $rol = "cliente";
    $estado = "activo";

    $insert = $conexion->prepare("
        INSERT INTO usuarios (nombre, telefono, email, password, rol, estado)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $insert->bind_param("ssssss", $nombre, $telefonoCompleto, $email, $hash, $rol, $estado);
    $insert->execute();

    if ($insert->affected_rows <= 0) {
        throw new Exception("No se pudo registrar el usuario.");
    }

    $usuario_id = $conexion->insert_id;

    // --- Marcar código de entrada como 'pendiente' (reservado) ---
    $update = $conexion->prepare("UPDATE entradas SET estado='pendiente' WHERE id=?");
    $update->bind_param("i", $entrada['id']);
    $update->execute();

    echo json_encode([
        "status" => "ok",
        "mensaje" => "Usuario registrado correctamente.",
        "id_usuario" => $usuario_id
    ]);

} catch (Throwable $e) {
    echo json_encode([
        "status" => "error",
        "mensaje" => $e->getMessage()
    ]);
}

$conexion->close();
?>
