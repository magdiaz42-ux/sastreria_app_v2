<?php
include 'conexion.php'; // tu archivo con la conexión a MySQL

$pregunta1 = $_POST['pregunta1'];
$pregunta2 = $_POST['pregunta2'];
$pregunta3 = $_POST['pregunta3'];
$pregunta4 = $_POST['pregunta4'];
$pregunta5 = $_POST['pregunta5'];

$query = "INSERT INTO respuestas_juego (pregunta1, pregunta2, pregunta3, pregunta4, pregunta5)
          VALUES ('$pregunta1', '$pregunta2', '$pregunta3', '$pregunta4', '$pregunta5')";

if (mysqli_query($conexion, $query)) {
    echo "<script>alert('🎉 ¡Registro completado con éxito!'); window.location='inicio.php';</script>";
} else {
    echo "Error: " . mysqli_error($conexion);
}
?>
