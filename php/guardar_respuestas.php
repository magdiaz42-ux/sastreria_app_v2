<?php
include "conexion.php";
header("Content-Type: application/json; charset=utf-8");

// --- Recibir datos ---
$id_usuario = intval($_POST['id_usuario'] ?? 0);
$respuestas_json = $_POST['respuestas'] ?? '';

if (!$id_usuario || !$respuestas_json) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

// Decodificar respuestas
$respuestas = json_decode($respuestas_json, true);
if (!is_array($respuestas)) {
    echo json_encode(["status" => "error", "message" => "Formato de respuestas inválido"]);
    exit;
}

// Asegurarse de que la tabla exista
$conexion->query("
    CREATE TABLE IF NOT EXISTS respuestas_usuario (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        pregunta VARCHAR(255) NOT NULL,
        respuesta VARCHAR(255) NOT NULL,
        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

$stmt = $conexion->prepare("INSERT INTO respuestas_usuario (id_usuario, pregunta, respuesta) VALUES (?, ?, ?)");

foreach ($respuestas as $r) {
    $pregunta = $r['pregunta'] ?? '';
    $respuesta = $r['respuesta'] ?? '';
    if ($pregunta && $respuesta) {
        $stmt->bind_param("iss", $id_usuario, $pregunta, $respuesta);
        $stmt->execute();
    }
}

echo json_encode(["status" => "success", "message" => "Respuestas guardadas correctamente"]);
$conexion->close();
?>
