<?php
header('Content-Type: application/json; charset=utf-8');
ob_start();

include "conexion.php"; // Asegurate de que el archivo correcto se llame así

$id_usuario = $_POST['id_usuario'] ?? 0;
$avatar = $_POST['avatar'] ?? null;
$selfie = $_POST['selfie'] ?? null;

if (!$id_usuario) {
    echo json_encode(["status" => "error", "message" => "ID de usuario no recibido"]);
    exit;
}

// --- Procesar selfie si existe ---
if ($selfie) {
    $carpeta = "../assets/img/selfies/";
    if (!file_exists($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    $nombreArchivo = "selfie_" . $id_usuario . "_" . time() . ".png";
    $rutaArchivo = $carpeta . $nombreArchivo;

    // Quitar encabezado base64 y guardar
    $data = str_replace('data:image/png;base64,', '', $selfie);
    $data = str_replace(' ', '+', $data);
    $data = base64_decode($data);
    file_put_contents($rutaArchivo, $data);

    $selfie = "assets/img/selfies/" . $nombreArchivo; // Ruta relativa para front-end
}

// --- Actualizar en DB ---
$sql = $conexion->prepare("UPDATE usuarios SET avatar = ?, selfie = ? WHERE id = ?");
$sql->bind_param("ssi", $avatar, $selfie, $id_usuario);

if ($sql->execute()) {
    // Definir qué imagen usar por defecto en la app
    $imgFinal = $selfie ? $selfie : ($avatar ?: "assets/img/avatar1.png");

    echo json_encode([
        "status" => "success",
        "message" => "Avatar/selfie guardados correctamente",
        "img_final" => $imgFinal
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al guardar: " . $conexion->error]);
}

$conexion->close();
?>
