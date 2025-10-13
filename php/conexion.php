<?php
// ==========================
// CONEXIÓN BASE DE DATOS - LA SASTRERÍA
// ==========================

// ⚠️ IMPORTANTE: NO imprimas nada aquí. Este archivo se "include" desde otros PHP.
// Si querés probar la conexión, creá un php/health.php aparte.

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  exit(0); // preflight CORS
}

// ---------- CONFIG DB ----------
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sastreria_db";  // ← Asegurate que este sea TU nombre real de base

// ---------- CONEXIÓN ----------
mysqli_report(MYSQLI_REPORT_OFF); // desactiva excepciones para poder chequear manualmente
$conn = @new mysqli($host, $user, $pass, $db);

// Alias retro-compatible para código que usa $conexion
$conexion = $conn;

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
  // no cortamos la ejecución; solo log
  error_log("⚠️ Error al establecer UTF-8: " . $conn->error);
}

// Si llegamos acá, hay conexión OK y $conn / $conexion están listos.
