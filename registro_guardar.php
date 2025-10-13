<?php
include('includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = trim($_POST['nombre']);
  $codigo_pais = trim($_POST['codigo_pais']);
  $telefono_num = trim($_POST['telefono']);
  $email = trim($_POST['email']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // Combinar número final con prefijo internacional
  $telefono_completo = '+' . $codigo_pais . ' ' . $telefono_num;

  // Verificar duplicado por número completo
  $check = $conn->prepare("SELECT id FROM usuarios WHERE telefono = ?");
  $check->bind_param("s", $telefono_completo);
  $check->execute();
  $check->store_result();

  if ($check->num_rows > 0) {
    echo "<script>alert('⚠️ Este número ya está registrado.'); window.location.href='registro.php';</script>";
    exit;
  }

  // Insertar nuevo usuario
  $stmt = $conn->prepare("INSERT INTO usuarios (nombre, telefono, email, password) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $nombre, $telefono_completo, $email, $password);

  if ($stmt->execute()) {
    header("Location: registro_etapa2.php?telefono=" . urlencode($telefono_completo));
  } else {
    echo "<script>alert('Error al guardar los datos.'); window.location.href='registro.php';</script>";
  }

  $stmt->close();
  $conn->close();
}
?>
