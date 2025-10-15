<?php
// ==========================
// CONEXIÓN BASE DE DATOS - LA SASTRERÍA
// ==========================

// ⚠️ Este archivo solo define la conexión y nunca imprime nada salvo error fatal.

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  // Maneja preflight CORS en caso de que se llame directamente
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
  header('Access-Control-Allow-Headers: Content-Type');
  exit(0);
}

// ---------- CONFIG DB ----------
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sastreria_db"; // Asegurate que el nombre coincida exactamente con tu base

// ---------- CONEXIÓN ----------
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($host, $user, $pass, $db);

// Alias de compatibilidad
$conexion = $conn;

// ---------- MANEJO DE ERROR ----------
if (!$conn || $conn->connect_errno) {
  http_response_code(500);
  // Devuelve JSON solo si se accede directamente (no si se incluye)
  if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
      "status"  => "error",
      "message" => "❌ Error al conectar a la base de datos: " . ($conn?->connect_error ?? 'sin detalle'),
      "code"    => $conn?->connect_errno
    ]);
  } else {
    // Si se incluye desde otro PHP, lanzamos una excepción en vez de imprimir
    throw new Exception("Error de conexión con la base de datos: " . ($conn?->connect_error ?? 'sin detalle'));
  }
  exit;
}

// ---------- CHARSET ----------
if (!$conn->set_charset("utf8mb4")) {
  error_log("⚠️ Error al establecer UTF-8: " . $conn->error);
}

// ✅ Conexión OK: $conexion y $conn están listos para usar.
