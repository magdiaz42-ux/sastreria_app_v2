<?php
/**
 * CREA USUARIOS BASE - LA SASTRERÍA
 * Admin y Cajero iniciales
 */

header("Content-Type: text/plain; charset=utf-8");
include "conexion.php";

// --- USUARIOS A CREAR ---
$usuarios = [
  [
    "nombre" => "Administrador General",
    "telefono" => "1111111111",
    "email" => "admin@sastreria.com",
    "password" => password_hash("admin123", PASSWORD_DEFAULT),
    "rol" => "admin",
    "estado" => "activo"
  ],
  [
    "nombre" => "Cajero Principal",
    "telefono" => "2222222222",
    "email" => "cajero@sastreria.com",
    "password" => password_hash("cajero123", PASSWORD_DEFAULT),
    "rol" => "cajero",
    "estado" => "activo"
  ]
];

// --- CREAR TABLA SI NO EXISTE ---
$conexion->query("
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  telefono VARCHAR(30) UNIQUE,
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255) NOT NULL,
  avatar VARCHAR(255) DEFAULT NULL,
  selfie VARCHAR(255) DEFAULT NULL,
  rol ENUM('cliente','cajero','dj','cocinero','admin','duenio') DEFAULT 'cliente',
  estado ENUM('activo','inactivo','bloqueado') DEFAULT 'activo',
  fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// --- INSERTAR ---
foreach ($usuarios as $u) {
  $stmt = $conexion->prepare("
    INSERT INTO usuarios (nombre, telefono, email, password, rol, estado)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), password=VALUES(password), rol=VALUES(rol), estado=VALUES(estado)
  ");
  $stmt->bind_param("ssssss", $u["nombre"], $u["telefono"], $u["email"], $u["password"], $u["rol"], $u["estado"]);
  $stmt->execute();
  echo "✅ Usuario creado o actualizado: {$u['nombre']} ({$u['rol']})\n";
}

echo "\nListo. Probá loguearte:\n";
echo "👤 Admin:  admin@sastreria.com / admin123\n";
echo "💵 Cajero: cajero@sastreria.com / cajero123\n";

$conexion->close();
?>
