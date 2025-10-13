<?php
$to = "test@correo.com";
$subject = "Prueba directa MailHog mínima";
$message = "Esto es un test directo desde PHP (sin sendmail.exe).";
$headers = "From: no-reply@sastreria.local\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo "✅ Correo enviado correctamente.";
} else {
    echo "❌ Falló el envío.";
}
?>
