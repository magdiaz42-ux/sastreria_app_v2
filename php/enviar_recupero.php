<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db_conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// Verificar si llega el campo
$usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';

if (empty($usuario)) {
    echo json_encode(['success' => false, 'message' => 'Debes ingresar un correo o teléfono.']);
    exit;
}

try {
    // Buscar usuario por mail o teléfono
    $sql = "SELECT * FROM usuarios WHERE email = ? OR telefono = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $usuario, $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No se encontró un usuario con ese dato.']);
        exit;
    }

    $usuarioData = $resultado->fetch_assoc();
    $token = bin2hex(random_bytes(32));
    $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Guardar el token
    $sqlToken = "UPDATE usuarios SET token_recupero = ?, token_expira = ? WHERE id = ?";
    $stmtToken = $conn->prepare($sqlToken);
    $stmtToken->bind_param("ssi", $token, $expira, $usuarioData['id']);
    $stmtToken->execute();

    // Enviar el correo
    $destinatario = $usuarioData['email'];
    $asunto = "Recuperar contraseña - La Sastrería";
    $link = "http://localhost/sastreria_app/reset_password.html?token=" . urlencode($token);
    $mensaje = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Hola " . htmlspecialchars($usuarioData['nombre']) . " 👋</h2>
            <p>Hacé clic en el siguiente enlace para restablecer tu contraseña:</p>
            <a href='$link' style='display:inline-block;padding:10px 20px;background:#00fff2;color:#000;
               text-decoration:none;border-radius:5px;font-weight:bold;'>Restablecer Contraseña</a>
            <p>Si no solicitaste esto, podés ignorar este mensaje.</p>
        </body>
        </html>
    ";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: La Sastrería <no-reply@sastreria.local>\r\n";

    if (mail($destinatario, $asunto, $mensaje, $headers)) {
        echo json_encode(['success' => true, 'message' => '✅ Se envió el enlace de recuperación. Revisá tu MailHog.']);
    } else {
        echo json_encode(['success' => false, 'message' => '⚠️ Error al enviar el correo.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
