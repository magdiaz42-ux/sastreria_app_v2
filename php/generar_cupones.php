<?php
/**
 * ===================================
 * GENERAR CUPONES PARA NUEVO USUARIO
 * ===================================
 * Toma los cupones desde la tabla `cupones_base`:
 *  - 1 "Trago" activo (aleatorio)
 *  - 9 "Capricho" activos (aleatorios)
 * 
 * Requiere:
 *   - $conexion: conexión mysqli activa
 *   - $id_usuario: ID del usuario recién creado
 */

function generarCupones($conexion, $id_usuario) {
  if (!$conexion || !$id_usuario) return false;

  // --- 1️⃣ Traer 1 trago y 9 caprichos activos ---
  $trago = $conexion->query("SELECT * FROM cupones_base WHERE tipo='Trago' AND activo=1 ORDER BY RAND() LIMIT 1");
  $caprichos = $conexion->query("SELECT * FROM cupones_base WHERE tipo='Capricho' AND activo=1 ORDER BY RAND() LIMIT 9");

  $cupones = [];

  if ($trago && $trago->num_rows > 0) {
    $cupones[] = $trago->fetch_assoc();
  }

  if ($caprichos && $caprichos->num_rows > 0) {
    while ($row = $caprichos->fetch_assoc()) {
      $cupones[] = $row;
    }
  }

  // Si por alguna razón no se consiguen 10 cupones, se aborta
  if (count($cupones) === 0) {
    error_log("⚠️ No se encontraron cupones_base activos para generar.");
    return false;
  }

  // --- 2️⃣ Preparar sentencia para insertar cupones asignados ---
  $insert = $conexion->prepare("
    INSERT INTO cupones (
      id_usuario, id_base, nombre, descripcion, tipo, codigo_unico, usado, fecha_asignacion
    )
    VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
  ");

  if (!$insert) {
    error_log("❌ Error preparando inserción de cupones: " . $conexion->error);
    return false;
  }

  // --- 3️⃣ Insertar los cupones generados ---
  foreach ($cupones as $c) {
    // Generar un código único de 8 caracteres alfanuméricos
    $codigo_unico = strtoupper(substr(md5(uniqid($id_usuario . '_' . $c['id'], true)), 0, 8));

    $insert->bind_param(
      "iissss",
      $id_usuario,
      $c['id'],
      $c['nombre'],
      $c['descripcion'],
      $c['tipo'],
      $codigo_unico
    );

    if (!$insert->execute()) {
      error_log("⚠️ Error insertando cupón '{$c['nombre']}': " . $insert->error);
    }
  }

  $insert->close();
  return true;
}
?>
