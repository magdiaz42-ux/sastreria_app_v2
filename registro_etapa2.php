<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro - Etapa 2</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="registro-body">

  <div class="registro-container etapa2">
    <h1 class="glow-text">Tomate una foto </h1>
    <p class="subtitle">Sacate una selfie o elegí tu avatar</p>

    <!-- CÁMARA -->
    <div class="camera-container">
      <video id="video" autoplay playsinline></video>
      <div class="oval-frame"></div>
      <div class="camera-light"></div>
    </div>

    <!-- BOTÓN CAPTURAR -->
    <div class="btn-section">
      <button id="btn-capturar" class="btn-dark">Capturar selfie</button>
    </div>

    <!-- PREVIEW -->
    <canvas id="canvas" style="display:none;"></canvas>
    <div id="preview" class="preview"></div>

    <hr class="divider">

    <!-- AVATARES -->
    <h3 class="glow-text">O elegí un avatar:</h3>
    <div class="avatar-container" id="avatars"></div>

    <div class="btn-section">
      <button id="btn-continuar" class="btn-dark">Continuar</button>
    </div>
  </div>

  <script src="assets/js/registro2.js"></script>
</body>
</html>
