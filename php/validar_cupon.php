<?php
include 'conexion.php';
header('Content-Type: text/html; charset=utf-8');

// ===============================
// VALIDAR CUPÓN POR CÓDIGO QR
// ===============================

$codigo_qr = trim($_GET['codigo_qr'] ?? '');

function renderMensaje($estado, $titulo, $detalle = '', $color = '#00fff2') {
    echo "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Validación de Cupón | La Sastrería</title>
        <link rel='stylesheet' href='../assets/css/style.css'>
        <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap' rel='stylesheet'>
        <style>
            body {
                background: url('../assets/img/fondo-hexagonos.jpg') no-repeat center center fixed;
                background-size: cover;
                height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                font-family: 'Poppins', sans-serif;
                overflow: hidden;
            }
            .overlay {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.7);
                z-index: 0;
            }
            .mensaje {
                position: relative;
                background: rgba(0, 0, 0, 0.85);
                border-radius: 25px;
                padding: 40px 30px;
                width: 90%;
                max-width: 420px;
                text-align: center;
                color: #fff;
                box-shadow: 0 0 25px rgba(0, 255, 242, 0.3);
                z-index: 2;
                animation: fadeIn 0.5s ease-in-out;
            }
            .estado {
                font-size: 3rem;
                margin-bottom: 15px;
                color: {$color};
                text-shadow: 0 0 25px {$color};
            }
            h1 {
                font-size: 1.6rem;
                color: {$color};
                text-shadow: 0 0 10px {$color};
                margin-bottom: 10px;
            }
            p {
                color: #ccc;
                font-size: 1rem;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: scale(0.95); }
                to { opacity: 1; transform: scale(1); }
            }
        </style>
    </head>
    <body>
        <div class='overlay'></div>
        <div class='mensaje'>
            <div class='estado'>{$estado}</div>
            <h1>{$titulo}</h1>
            <p>{$detalle}</p>
        </div>
    </body>
    </html>
    ";
    exit;
}

// Si no se recibió el código
if (empty($codigo_qr)) {
    renderMensaje('⚠️', 'Código no recibido', 'No se recibió ningún código de cupón.', '#ffaa00');
}

// Buscar el cupón
$stmt = $conexion->prepare("SELECT c.id, c.usado, b.nombre, b.tipo 
                            FROM cupones_asignados AS c
                            INNER JOIN cupones_base AS b ON c.cupon_base_id = b.id
                            WHERE c.codigo_qr = ? LIMIT 1");
$stmt->bind_param("s", $codigo_qr);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    renderMensaje('❌', 'Cupón no encontrado', 'El código no existe o es inválido.', '#ff3333');
}

$cupon = $result->fetch_assoc();

// Ya usado
if ($cupon['usado'] == 1) {
    renderMensaje('⚠️', 'Cupón ya utilizado', "Cupón: <strong>{$cupon['nombre']}</strong>", '#ffaa00');
}

// Marcar como usado
$update = $conexion->prepare("UPDATE cupones_asignados SET usado = 1 WHERE id = ?");
$update->bind_param("i", $cupon['id']);
$update->execute();

// Mostrar resultado exitoso
renderMensaje('✅', 'Cupón validado correctamente', "Cupón: <strong>{$cupon['nombre']}</strong><br>Tipo: {$cupon['tipo']}", '#00fff2');
?>
