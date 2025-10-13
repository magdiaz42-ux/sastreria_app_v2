<?php
include 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

$id = intval($_POST['id_usuario'] ?? 0);

if ($id <= 0) {
    echo json_encode(["status" => "error", "mensaje" => "ID de usuario inválido."]);
    exit;
}

$stmt = $conexion->prepare("SELECT id, nombre FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["status" => "error", "mensaje" => "Sesión no válida."]);
} else {
    $u = $res->fetch_assoc();
    echo json_encode(["status" => "ok", "nombre" => $u["nombre"]]);
}
?>
