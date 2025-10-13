<?php include('includes/db.php'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro - Etapa 1</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="registro-body">

<div class="registro-container">
  <h1 class="glow-text">Registro</h1>
  <p class="subtitle">Completá tus datos para comenzar la experiencia</p>

  <form id="formEtapa1" method="POST" action="registro_guardar.php" class="form-panel">

    <!-- NOMBRE -->
    <div class="form-section">
      <label class="section-title">Nombre o Apodo <span>*</span></label>
      <input type="text" name="nombre" id="nombre" placeholder="Tu nombre o apodo" required>
    </div>

    <!-- TELEFONO DIVIDIDO -->
    <div class="form-section">
      <label class="section-title">Teléfono <span>*</span></label>
      <div class="telefono-grid">
        <div class="telefono-prefix">
          <span class="prefix-label">+</span>
          <input type="number" name="codigo_pais" id="codigo_pais" placeholder="54" required>
        </div>
        <input type="tel" name="telefono" id="telefono" placeholder="91112345678" required>
      </div>
      <small>Incluí tu código de país y tu número con código de área. Usaremos este número para enviarte alertas de eventos y juegos.</small>
    </div>

    <!-- EMAIL -->
    <div class="form-section">
      <label class="section-title">Email (opcional)</label>
      <input type="email" name="email" id="email" placeholder="tu@correo.com">
      <small>Recibirás novedades, sorteos y cronogramas exclusivos.</small>
    </div>

    <!-- CONTRASEÑAS -->
    <div class="form-section two-cols">
      <div>
        <label class="section-title">Contraseña <span>*</span></label>
        <input type="password" name="password" id="password" placeholder="********" required>
      </div>
      <div>
        <label class="section-title">Confirmar Contraseña <span>*</span></label>
        <input type="password" name="confirmar" id="confirmar" placeholder="********" required>
      </div>
      <small id="error-pass" class="error">Las contraseñas no coinciden ⚠️</small>
    </div>

    <!-- BOTÓN -->
    <div class="btn-section">
      <button type="submit" class="btn-dark">Continuar</button>
    </div>
  </form>
</div>

<script src="assets/js/registro.js"></script>
</body>
</html>
