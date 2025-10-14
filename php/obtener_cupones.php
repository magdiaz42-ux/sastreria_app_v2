<?php
// ===========================================
// OBTENER CUPONES DEL USUARIO - LA SASTRERÍA
// ===========================================

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit(0); // Preflight CORS
}

// ==========================
// CONEXIÓN A LA BASE DE DATOS
// ==========================
include "conexion.php";

// ==========================
// VALIDAR PARÁMETRO
// ==========================
$id_usuario = intval($_GET['id_usuario'] ?? 0);

if ($id_usuario <= 0) {
    echo json_encode(["status" => "error", "mensaje" => "ID de usuario no recibido o inválido"]);
    exit;
}

// ==========================
// CONSULTA DE CUPONES
// ==========================
// Tabla esperada: cupones (id, id_usuario, nombre, descripcion, tipo, codigo_unico, usado, estado, fecha_asignado)
try {
    $sql = $conexion->prepare("
        SELECT 
            nombre,
            descripcion,
            tipo,
            codigo_unico,
            usado,
            estado
        FROM cupones
        WHERE id_usuario = ?
        ORDER BY tipo DESC, id ASC
    ");

    if (!$sql) {
        throw new Exception("Error en la preparación de la consulta SQL");
    }

    $sql->bind_param("i", $id_usuario);
    $sql->execute();
    $res = $sql->get_result();

    $cupones = [];
    while ($row = $res->fetch_assoc()) {
        $cupones[] = [
            "nombre" => $row["nombre"] ?: ucfirst($row["tipo"]),
            "descripcion" => $row["descripcion"] ?: "Cupón disponible",
            "tipo" => strtolower($row["tipo"]), // minúsculas para clases CSS
            "codigo_unico" => $row["codigo_unico"],
            "estado" => $row["estado"],
            "usado" => (int)$row["usado"]
        ];
    }

    if (!empty($cupones)) {
        echo json_encode([
            "status" => "ok",
            "total" => count($cupones),
            "cupones" => $cupones
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            "status" => "vacio",
            "mensaje" => "Este usuario aún no tiene cupones asignados."
        ], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "mensaje" => "Error al obtener cupones: " . $e->getMessage()
    ]);
}

$conexion->close();
?>
