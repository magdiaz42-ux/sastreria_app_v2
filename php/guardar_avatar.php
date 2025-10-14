<?php
header('Content-Type: application/json; charset=utf-8');
include "conexion.php";

$id_usuario = $_POST['id_usuario'] ?? 0;
$avatar = $_POST['avatar'] ?? null;
$selfie = $_POST['selfie'] ?? null;

if (!$id_usuario) {
  echo json_encode(["status" => "error", "message" => "ID de usuario no recibido"]);
  exit;
}

$imgFinal = null;

// --- Procesar selfie ---
if ($selfie && str_starts_with($selfie, 'data:image')) {
  $carpeta = "../assets/img/selfies/";
  if (!file_exists($carpeta)) mkdir($carpeta, 0777, true);
  $nombreArchivo = "selfie_" . $id_usuario . "_" . time() . ".png";
  $rutaArchivo = $carpeta . $nombreArchivo;

  $data = explode(',', $selfie);
  $selfieDecoded = base64_decode(end($data));
  file_put_contents($rutaArchivo, $selfieDecoded);

  $imgFinal = "assets/img/selfies/" . $nombreArchivo;
} elseif ($avatar) {
  // Si el usuario eligió avatar predefinido
  $imgFinal = $avatar;
} else {
  $imgFinal = "assets/img/avatar1.png";
}

// --- Actualizar en base de datos ---
$sql = $conexion->prepare("UPDATE usuarios SET avatar = ? WHERE id = ?");
$sql->bind_param("si", $imgFinal, $id_usuario);

if ($sql->execute()) {
  echo json_encode(["status" => "success", "img_final" => $imgFinal]);
} else {
  echo json_encode(["status" => "error", "message" => $conexion->error]);
}

$conexion->close();
