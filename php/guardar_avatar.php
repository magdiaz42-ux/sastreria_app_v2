<?php
header('Content-Type: application/json; charset=utf-8');
include "conexion.php";

// --- Recibir datos del cliente ---
$id_usuario = intval($_POST['id_usuario'] ?? 0);
$avatar = $_POST['avatar'] ?? null;
$selfie = $_POST['selfie'] ?? null;

if (!$id_usuario) {
  echo json_encode(["status" => "error", "message" => "ID de usuario no recibido"]);
  exit;
}

$imgFinal = null;

// --- Si se envió selfie (base64) ---
if (!empty($selfie) && str_starts_with($selfie, 'data:image')) {
  $carpeta = "../assets/img/selfies/";
  
  // Crear carpeta si no existe
  if (!file_exists($carpeta)) {
    mkdir($carpeta, 0777, true);
  }

  // Nombre único del archivo
  $nombreArchivo = "selfie_" . $id_usuario . "_" . time() . ".png";
  $rutaArchivo = $carpeta . $nombreArchivo;

  // Decodificar y guardar
  $data = explode(',', $selfie);
  $imagenDecodificada = base64_decode(end($data));

  if (file_put_contents($rutaArchivo, $imagenDecodificada) === false) {
    echo json_encode(["status" => "error", "message" => "Error al guardar la imagen."]);
    exit;
  }

  // Ruta pública para mostrar en el front
  $imgFinal = "assets/img/selfies/" . $nombreArchivo;
}

// --- Si eligió avatar predefinido ---
elseif (!empty($avatar)) {
  $imgFinal = $avatar;
}

// --- Imagen por defecto ---
else {
  $imgFinal = "assets/img/avatars/avatar1.png";
}

// --- Actualizar en base de datos ---
$sql = $conexion->prepare("UPDATE usuarios SET avatar = ? WHERE id = ?");
$sql->bind_param("si", $imgFinal, $id_usuario);

if ($sql->execute()) {
  echo json_encode([
    "status" => "success",
    "message" => "Avatar guardado correctamente.",
    "img_final" => $imgFinal
  ]);
} else {
  echo json_encode([
    "status" => "error",
    "message" => "Error al actualizar el avatar en la base de datos: " . $conexion->error
  ]);
}

$conexion->close();
?>
