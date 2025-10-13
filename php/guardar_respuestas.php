<?php
include "db_conexion.php";

$id_usuario = $_POST['id_usuario'] ?? 0;
$respuestas = $_POST['respuestas'] ?? '';

if (!$id_usuario || !$respuestas) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

$respuestas = json_decode($respuestas, true);

foreach ($respuestas as $r) {
    $pregunta = $r['pregunta'];
    $respuesta = $r['respuesta'];

    $sql = $conn->prepare("INSERT INTO respuestas_usuario (id_usuario, pregunta, respuesta) VALUES (?, ?, ?)");
    $sql->bind_param("iss", $id_usuario, $pregunta, $respuesta);
    $sql->execute();
}

echo json_encode(["status" => "success", "message" => "Respuestas guardadas correctamente"]);
$conn->close();
?>
