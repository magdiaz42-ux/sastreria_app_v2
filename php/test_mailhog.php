<?php
$to = "test@correo.com";
$subject = "Prueba de MailHog";
$message = "Este es un correo de prueba enviado desde PHP con MailHog.";
$headers = "From: La Sastrería <no-reply@sastreria.local>\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo "✅ Correo enviado correctamente.";
} else {
    echo "❌ Falló el envío.";
}
?>
