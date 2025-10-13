<?php
require_once("conexion.php");
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/recupero.log');

$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $pass = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE token_recupero = ? AND token_expira > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        echo "<script>alert('⚠️ Enlace inválido o expirado.');location.href='../recuperar.html';</script>";
        exit;
    }

    $user = $res->fetch_assoc();
    $hash = password_hash($pass, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE usuarios SET password=?, token_recupero=NULL, token_expira=NULL WHERE id=?");
    $stmt->bind_param("si", $hash, $user['id']);
    $stmt->execute();

    echo "<script>alert('✅ Contraseña actualizada correctamente');location.href='../login.html';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Restablecer Contraseña - La Sastrería</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      background: url('../assets/img/fondo-hexagonos.jpg') no-repeat center center fixed;
      background-size: cover;
      font-family: 'Poppins', sans-serif;
      height: 100vh;
      overflow: hidden;
    }
    .overlay {
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.65);
      display: flex; justify-content: center; align-items: center;
    }
    .contenedor {
      background: rgba(0,0,0,0.8);
      border-radius: 40px;
      padding: 40px;
      width: 90%; max-width: 450px;
      text-align: center;
      box-shadow: 0 0 25px rgba(0,255,242,0.6);
      animation: fadeIn 1s ease;
    }
    h2 {
      color: #00fff2;
      font-size: 2em;
      margin-bottom: 20px;
      text-shadow: 0 0 10px #00fff2;
    }
    input {
      width: 100%; padding: 12px;
      margin-bottom: 20px;
      border-radius: 12px;
      border: 2px solid rgba(0,255,242,0.4);
      background: rgba(255,255,255,0.05);
      color: #fff;
      font-size: 1em;
      outline: none;
      transition: all 0.3s ease;
    }
    input:focus {
      border-color: #00fff2;
      box-shadow: 0 0 12px #00fff2;
    }
    button {
      background: linear-gradient(90deg, #4b00ff, #00fff2);
      border: none;
      color: #fff;
      font-weight: 600;
      padding: 12px 35px;
      border-radius: 25px;
      box-shadow: 0 0 25px rgba(0,255,242,0.6);
      cursor: pointer;
      width: 100%;
    }
  </style>
</head>
<body>
  <div class="overlay">
    <div class="contenedor">
      <h2>Restablecer Contraseña</h2>
      <form method="POST">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <input type="password" name="password" placeholder="Nueva contraseña" required>
        <button type="submit">Actualizar Contraseña</button>
      </form>
    </div>
  </div>
</body>
</html>
