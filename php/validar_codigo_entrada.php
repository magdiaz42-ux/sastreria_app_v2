<?php
include "conexion.php";

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// --- Validar método ---
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "mensaje" => "Método no permitido"]);
    exit;
}

// --- Recibir datos ---
$codigo = strtoupper(trim($_POST["codigo"] ?? ''));
$id_usuario = intval($_POST["id_usuario"] ?? 0);

if (empty($codigo) || !$id_usuario) {
    echo json_encode(["status" => "error", "mensaje" => "Datos incompletos"]);
    exit;
}

// --- Buscar el código en entradas ---
$sql = $conexion->prepare("SELECT id, estado FROM entradas WHERE codigo = ? LIMIT 1");
$sql->bind_param("s", $codigo);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "mensaje" => "Código no válido"]);
    exit;
}

$entrada = $result->fetch_assoc();

// --- Validar estado ---
if ($entrada["estado"] !== "disponible") {
    echo json_encode(["status" => "error", "mensaje" => "Este código ya fue utilizado"]);
    exit;
}

// --- Marcar entrada como usada ---
$updateEntrada = $conexion->prepare("UPDATE entradas SET estado = 'usado' WHERE id = ?");
$updateEntrada->bind_param("i", $entrada["id"]);
$updateEntrada->execute();

// --- Asignar cupones al usuario ---
$updateCupones = $conexion->prepare("
    UPDATE cupones 
    SET id_usuario = ?, estado = 'activo', fecha_asignacion = NOW()
    WHERE codigo_entrada = ?
");
$updateCupones->bind_param("is", $id_usuario, $codigo);
$updateCupones->execute();

$cupones_asignados = $conexion->affected_rows;

// --- Respuesta final ---
echo json_encode([
    "status" => "success",
    "mensaje" => "Código válido. Se asignaron $cupones_asignados cupones al usuario.",
    "cupones_asignados" => $cupones_asignados
]);
?>
