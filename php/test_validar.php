<?php
// ===========================================
// TEST RÁPIDO PARA validar_codigo_entrada.php
// ===========================================

// ⚙️ Código de ejemplo — podés cambiarlo por uno real de la tabla "entradas"
$codigo_prueba = "ABCDE";

// 🔹 Inicializamos la solicitud POST localmente
$url = "http://localhost/sastreria_app/php/validar_codigo_entrada.php";

// 🔹 Datos a enviar
$data = ["codigo" => $codigo_prueba];

// 🔹 Configurar CURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// 🔹 Ejecutar y capturar respuesta
$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

// 🔹 Mostrar resultados
header("Content-Type: text/html; charset=utf-8");

echo "<h2>🧩 Test validar_codigo_entrada.php</h2>";
echo "<p>Enviando código: <strong>{$codigo_prueba}</strong></p>";

if ($error) {
    echo "<p style='color:red;'>❌ Error en CURL: {$error}</p>";
} else {
    echo "<pre style='background:#111;color:#0f0;padding:15px;border-radius:10px;'>";
    echo htmlspecialchars($response);
    echo "</pre>";
}

echo "<hr><p>✅ Si ves <code>{\"status\":\"ok\"}</code>, el validador funciona correctamente.</p>";
echo "<p>⚠️ Si ves <code>{\"status\":\"error\"}</code>, revisá el mensaje devuelto (puede ser que el código no exista o esté usado).</p>";
?>
