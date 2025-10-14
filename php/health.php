<?php
include "conexion.php";

if ($conexion && $conexion->ping()) {
    echo "✅ Conexión exitosa con la base de datos sastreria_db.";
} else {
    echo "❌ Error de conexión.";
}
?>
