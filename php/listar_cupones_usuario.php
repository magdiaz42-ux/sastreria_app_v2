<?php
include 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['usuario_id'])) {
    echo json_encode([]);
    exit;
}

$usuario_id = intval($_GET['usuario_id']);

$sql = "SELECT 
            a.codigo_qr, 
            b.nombre, 
            b.descripcion, 
            b.tipo,
            a.usado
        FROM cupones_asignados AS a
        INNER JOIN cupones_base AS b ON a.cupon_base_id = b.id
        WHERE a.usuario_id = ?
        ORDER BY b.tipo, a.id DESC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$cupones = [];
while ($row = $result->fetch_assoc()) {
    $cupones[] = [
        "codigo_qr" => $row["codigo_qr"],
        "nombre" => $row["nombre"],
        "descripcion" => $row["descripcion"],
        "tipo" => $row["tipo"],
        "usado" => (bool)$row["usado"]
    ];
}

echo json_encode($cupones, JSON_UNESCAPED_UNICODE);
?>
