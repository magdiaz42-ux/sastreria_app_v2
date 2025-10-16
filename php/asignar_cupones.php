<?php
function generarCupones($conexion, $id_usuario) {
  if (!$conexion || !$id_usuario) return false;

  $trago = $conexion->query("SELECT * FROM cupones_base WHERE tipo='Trago' AND activo=1 ORDER BY RAND() LIMIT 1");
  $caprichos = $conexion->query("SELECT * FROM cupones_base WHERE tipo='Capricho' AND activo=1 ORDER BY RAND() LIMIT 9");

  $cupones = [];
  if ($trago && $trago->num_rows > 0) $cupones[] = $trago->fetch_assoc();
  if ($caprichos && $caprichos->num_rows > 0) while ($row = $caprichos->fetch_assoc()) $cupones[] = $row;
  if (count($cupones) === 0) return false;

  $insert = $conexion->prepare("
    INSERT INTO cupones (id_usuario, id_base, nombre, descripcion, tipo, codigo_unico, usado, fecha_asignacion)
    VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
  ");
  if (!$insert) return false;

  foreach ($cupones as $c) {
    $codigo_unico = strtoupper(substr(md5(uniqid($id_usuario . '_' . $c['id'], true)), 0, 8));
    $insert->bind_param("iissss",
      $id_usuario,
      $c['id'],
      $c['nombre'],
      $c['descripcion'],
      $c['tipo'],
      $codigo_unico
    );
    $insert->execute();
  }

  $insert->close();
  return true;
}
?>
