<?php
include 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

// =========================
// ASIGNAR CUPONES POR CÓDIGO
// =========================

// Recibir datos
$codigoEntrada = strtoupper(trim($_POST['codigo'] ?? ''));
$usuario_id = intval($_POST['usuario_id'] ?? 0);

if (empty($codigoEntrada) || $usuario_id <= 0) {
    echo json_encode(["status" => "error", "mensaje" => "⚠️ Datos incompletos."]);
    exit;
}

// 1️⃣ Validar que la entrada exista y no haya sido usada
$stmt = $conexion->prepare("SELECT id, usado FROM entradas WHERE codigo = ? LIMIT 1");
$stmt->bind_param("s", $codigoEntrada);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["status" => "error", "mensaje" => "❌ Código inexistente o inválido."]);
    exit;
}

$entrada = $res->fetch_assoc();
if ($entrada['usado'] == 1) {
    echo json_encode(["status" => "error", "mensaje" => "⚠️ El código ya fue utilizado."]);
    exit;
}

// 2️⃣ Marcar el código como usado
$update = $conexion->prepare("UPDATE entradas SET usado = 1 WHERE id = ?");
$update->bind_param("i", $entrada['id']);
$update->execute();

// 3️⃣ Asignar cupón fijo (tipo 'trago')
$fijo = $conexion->query("SELECT id FROM cupones_base WHERE tipo = 'trago' LIMIT 1");
if ($fijo && $fijo->num_rows > 0) {
    $row = $fijo->fetch_assoc();
    $ins = $conexion->prepare("INSERT INTO cupones_asignados (usuario_id, cupon_base_id, codigo_qr, usado)
                               VALUES (?, ?, UUID(), 0)");
    $ins->bind_param("ii", $usuario_id, $row['id']);
    $ins->execute();
}

// 4️⃣ Asignar 9 cupones aleatorios distintos (excluyendo 'trago')
$rand = $conexion->query("SELECT id FROM cupones_base WHERE tipo <> 'trago' ORDER BY RAND() LIMIT 9");
while ($r = $rand->fetch_assoc()) {
    $ins = $conexion->prepare("INSERT INTO cupones_asignados (usuario_id, cupon_base_id, codigo_qr, usado)
                               VALUES (?, ?, UUID(), 0)");
    $ins->bind_param("ii", $usuario_id, $r['id']);
    $ins->execute();
}

// 5️⃣ Confirmar resultado
echo json_encode([
    "status" => "ok",
    "mensaje" => "🎁 Se asignaron 10 cupones correctamente (1 trago + 9 aleatorios)."
]);
?>
