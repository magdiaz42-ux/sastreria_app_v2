<?php
header('Content-Type: application/json; charset=utf-8');
include "conexion.php";

file_put_contents("debug_post.txt", print_r($_POST, true)); // para testeo

function asignarCupones($usuario_id, $conn) {
    // Cupón fijo "trago"
    $fijo = $conn->query("SELECT id FROM cupones_base WHERE tipo='trago' LIMIT 1");
    if ($fijo && $fijo->num_rows > 0) {
        $row = $fijo->fetch_assoc();
        $conn->query("INSERT INTO cupones_asignados (usuario_id, cupon_base_id, codigo_qr, usado)
                      VALUES ($usuario_id, {$row['id']}, UUID(), 0)");
    }

    // Tres cupones aleatorios tipo capricho
    $aleatorios = $conn->query("SELECT id FROM cupones_base WHERE tipo <> 'trago' ORDER BY RAND() LIMIT 3");
    while ($r = $aleatorios->fetch_assoc()) {
        $conn->query("INSERT INTO cupones_asignados (usuario_id, cupon_base_id, codigo_qr, usado)
                      VALUES ($usuario_id, {$r['id']}, UUID(), 0)");
    }
}

// --- Datos recibidos del formulario ---
$nombre = $_POST['nombre'] ?? '';
$codigoPais = $_POST['codigoPais'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$codigoEntrada = strtoupper(trim($_POST['codigo_entrada'] ?? ''));

if (!$nombre || !$telefono || !$password || !$codigoEntrada) {
    echo json_encode([
        "status" => "error",
        "message" => "Faltan campos obligatorios.",
        "debug" => $_POST
    ]);
    exit;
}

$telefonoCompleto = $codigoPais . $telefono;

// --- Validar código de entrada ---
$stmt = $conn->prepare("SELECT id, usado FROM entradas WHERE codigo = ? LIMIT 1");
$stmt->bind_param("s", $codigoEntrada);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Código de entrada inválido o inexistente."]);
    exit;
}

$entrada = $result->fetch_assoc();
if ($entrada['usado'] == 1) {
    echo json_encode(["status" => "error", "message" => "El código ya fue utilizado."]);
    exit;
}

// --- Verificar duplicado de teléfono ---
$sqlCheck = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ?");
$sqlCheck->bind_param("s", $telefonoCompleto);
$sqlCheck->execute();
$result = $sqlCheck->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "El teléfono ya está registrado."]);
    exit;
}

// --- Registrar nuevo usuario ---
$hash = password_hash($password, PASSWORD_DEFAULT);
$sql = $conn->prepare("INSERT INTO usuarios (nombre, telefono, email, password) VALUES (?, ?, ?, ?)");
$sql->bind_param("ssss", $nombre, $telefonoCompleto, $email, $hash);

if ($sql->execute()) {
    $usuario_id = $conn->insert_id;
    $conn->query("UPDATE entradas SET usado = 1 WHERE id = {$entrada['id']}");
    asignarCupones($usuario_id, $conn);

    echo json_encode([
        "status" => "ok",
        "message" => "Registro exitoso y cupones asignados.",
        "id_usuario" => $usuario_id
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al guardar: " . $conn->error]);
}

$conn->close();
?>
