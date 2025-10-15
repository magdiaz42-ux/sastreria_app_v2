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

if (empty($codigo)) {
    echo json_encode(["status" => "error", "mensaje" => "Código no recibido"]);
    exit;
}

// --- Buscar el código en la tabla de entradas ---
$sql = $conexion->prepare("SELECT id, usado, id_usuario FROM codigos_entrada WHERE codigo_unico = ? LIMIT 1");
$sql->bind_param("s", $codigo);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "mensaje" => "Código inexistente"]);
    exit;
}

$entrada = $result->fetch_assoc();

// --- Validar estado ---
if ($entrada["usado"] == 1) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Este código ya fue utilizado",
        "id_usuario" => $entrada["id_usuario"]
    ]);
    exit;
}

// --- Si el código está disponible ---
echo json_encode([
    "status" => "success",
    "mensaje" => "Código válido y disponible para registro",
    "id_codigo" => $entrada["id"]
]);
?>
