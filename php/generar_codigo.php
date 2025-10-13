<?php
include 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

// Función para generar un código aleatorio de 5 letras
function generarCodigo() {
    $letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $codigo = '';
    for ($i = 0; $i < 5; $i++) {
        $codigo .= $letras[random_int(0, strlen($letras) - 1)];
    }
    return $codigo;
}

// Generar un nuevo código único
do {
    $codigo = generarCodigo();
    $existe = $conexion->query("SELECT id FROM entradas WHERE codigo = '$codigo'")->num_rows > 0;
} while ($existe);

// Guardar en la base
$stmt = $conexion->prepare("INSERT INTO entradas (codigo, usado, fecha_generado) VALUES (?, 0, NOW())");
$stmt->bind_param("s", $codigo);
$stmt->execute();

// URL del registro (CAMBIÁ ESTA a tu dominio real o localhost)
$urlRegistro = "http://localhost/sastreria/registro.html?codigo=" . urlencode($codigo);

// QR usando API gratuita
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($urlRegistro);

// Devolver los datos al HTML
echo json_encode([
    "status" => "ok",
    "codigo" => $codigo,
    "url" => $urlRegistro,
    "qr" => $qrUrl
]);
?>
