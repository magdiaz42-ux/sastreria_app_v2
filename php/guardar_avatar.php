<?php
/**
 * GUARDAR AVATAR / SELFIE - LA SASTRERÍA
 * --------------------------------------
 * Guarda la imagen elegida (selfie o avatar predefinido)
 * en la base de datos de usuarios.
 */

header('Content-Type: application/json; charset=utf-8');
include_once "conexion.php";

$id_usuario = intval($_POST['id_usuario'] ?? 0);
$avatar     = trim($_POST['avatar'] ?? '');
$selfie     = $_POST['selfie'] ?? '';

if (!$id_usuario) {
  echo json_encode(["status" => "error", "mensaje" => "ID de usuario no recibido."]);
  exit;
}

$imgFinal = null;

try {
  // --- Procesar selfie (imagen base64) ---
  if ($selfie && str_starts_with($selfie, 'data:image')) {
    $carpeta = "../assets/img/selfies/";
    if (!file_exists($carpeta)) mkdir($carpeta, 0777, true);

    $nombreArchivo = "selfie_" . $id_usuario . "_" . time() . ".png";
    $rutaArchivo = $carpeta . $nombreArchivo;

    $data = explode(',', $selfie);
    $selfieDecoded = base64_decode(end($data));

    if (file_put_contents($rutaArchivo, $selfieDecoded) === false) {
      throw new Exception("No se pudo guardar la imagen en el servidor.");
    }

    $imgFinal = "assets/img/selfies/" . $nombreArchivo;

  } elseif ($avatar) {
    // --- Si el usuario elige avatar predefinido ---
    $imgFinal = $avatar;
  } else {
    // --- Si no se elige nada, asignar un avatar por defecto ---
    $imgFinal = "assets/img/avatars/avatar1.png";
  }

  // --- Actualizar usuario en la base de datos ---
  $sql = $conexion->prepare("UPDATE usuarios SET avatar = ? WHERE id = ?");
  $sql->bind_param("si", $imgFinal, $id_usuario);

  if ($sql->execute()) {
    echo json_encode([
      "status"    => "ok",
      "mensaje"   => "Imagen guardada correctamente.",
      "img_final" => $imgFinal
    ]);
  } else {
    throw new Exception("Error al actualizar base de datos: " . $conexion->error);
  }

} catch (Exception $e) {
  echo json_encode([
    "status"  => "error",
    "mensaje" => $e->getMessage()
  ]);
}

$conexion->close();
?>
