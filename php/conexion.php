<?php
// ==========================
// CONEXIÓN BASE DE DATOS - LA SASTRERÍA
// ==========================
//
// ⚠️ IMPORTANTE: NO imprimas nada aquí si todo está OK.
// Este archivo se "include" desde otros PHP.
// Si querés probar la conexión, creá un php/health.php aparte.
//

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit(0); // Preflight CORS
}

// ---------- CONFIG DB ----------
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sastreria_db"; // ← Asegurate de usar este nombre real

// ---------- CONEXIÓN ----------
mysqli_report(MYSQLI_REPORT_OFF); // Desactiva reportes automáticos
$conn = @new mysqli($host, $user, $pass, $db);

// Alias retro-compatibles
$conexion = $conn;
$con = $conn;

// ---------- ERRORES DE CONEXIÓN ----------
if (!$conn || $conn->connect_errno) {
  http_response_code(500);
  echo json_encode([
    "status"  => "error",
    "message" => "❌ Error al conectar a la base de datos: " . ($conn?->connect_error ?? 'sin detalle'),
    "code"    => $conn?->connect_errno
  ]);
  exit;
}

// ---------- CHARSET ----------
if (!$conn->set_charset("utf8mb4")) {
  error_log("⚠️ Error al establecer UTF-8: " . $conn->error);
}

// ---------- FUNCIÓN OPCIONAL ----------
/**
 * Retorna una nueva conexión mysqli.
 * Útil cuando se necesita una conexión aislada o dentro de funciones.
 */
function getConexion() {
  $host = "localhost";
  $user = "root";
  $pass = "";
  $db   = "sastreria_db";

  mysqli_report(MYSQLI_REPORT_OFF);
  $dbConn = @new mysqli($host, $user, $pass, $db);

  if (!$dbConn || $dbConn->connect_errno) {
    http_response_code(500);
    echo json_encode([
      "status"  => "error",
      "message" => "❌ Error al conectar (getConexion): " . ($dbConn?->connect_error ?? 'sin detalle'),
      "code"    => $dbConn?->connect_errno
    ]);
    exit;
  }

  $dbConn->set_charset("utf8mb4");
  return $dbConn;
}

// ✅ Si llegamos acá, la conexión está OK y lista para usarse con:
//    $conn, $conexion, $con o getConexion()
?>
