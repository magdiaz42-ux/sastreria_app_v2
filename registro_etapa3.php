<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registro - Etapa 3</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="registro-fondo">
  <div class="registro-contenedor etapa3">
    <h1 class="titulo-principal">Contanos de vos</h1>
    <p class="subtitulo">Esto es para poder sumarte en los juegos que hacemos</p>

    <form id="formEtapa3" action="guardar_registro3.php" method="POST">

     <div class="slider-container">
  <div class="slider" id="slider">

    <div class="slide pregunta">
      <h2>De los siguientes dulces, ¿cuál preferís?</h2>
      <div class="opciones">
        <button type="button" class="btn-opcion" data-pregunta="1" data-respuesta="Team Membrillo">Team Membrillo</button>
        <button type="button" class="btn-opcion" data-pregunta="1" data-respuesta="Team Batata">Team Batata</button>
      </div>
      <input type="hidden" name="pregunta1" id="pregunta1">
    </div>	

          <div class="slide pregunta">
            <h2>¿Qué comida te gusta más?</h2>
            <div class="opciones">
              <button type="button" class="btn-opcion" data-pregunta="2" data-respuesta="Team Salado">Team Salado</button>
              <button type="button" class="btn-opcion" data-pregunta="2" data-respuesta="Team Dulce">Team Dulce</button>
            </div>
            <input type="hidden" name="pregunta2" id="pregunta2">
          </div>

          <div class="slide pregunta">
            <h2>¿Qué te gusta más?</h2>
            <div class="opciones">
              <button type="button" class="btn-opcion" data-pregunta="3" data-respuesta="Team Invierno">Team Invierno</button>
              <button type="button" class="btn-opcion" data-pregunta="3" data-respuesta="Team Verano">Team Verano</button>
            </div>
            <input type="hidden" name="pregunta3" id="pregunta3">
          </div>

          <div class="slide pregunta">
            <h2>¿Qué preferís al salir?</h2>
            <div class="opciones">
              <button type="button" class="btn-opcion" data-pregunta="4" data-respuesta="Team Bar">Team Bar</button>
              <button type="button" class="btn-opcion" data-pregunta="4" data-respuesta="Team Casa">Team Casa</button>
            </div>
            <input type="hidden" name="pregunta4" id="pregunta4">
          </div>

          <div class="slide pregunta">
            <h2>¿Qué tipo de plan te va más?</h2>
            <div class="opciones">
              <button type="button" class="btn-opcion" data-pregunta="5" data-respuesta="Team Tranquilo">Team Tranquilo</button>
              <button type="button" class="btn-opcion" data-pregunta="5" data-respuesta="Team Fiesta">Team Fiesta</button>
            </div>
            <input type="hidden" name="pregunta5" id="pregunta5">
          </div>

        </div>
      </div>

      <!-- Indicadores -->
      <div class="indicadores" id="indicadores">
        <span class="indicador activo"></span>
        <span class="indicador"></span>
        <span class="indicador"></span>
        <span class="indicador"></span>
        <span class="indicador"></span>
      </div>

      <!-- Navegación -->
      <div class="navegacion">
        <button type="button" id="btn-prev" class="btn-nav">← Anterior</button>
        <button type="button" id="btn-next" class="btn-nav">Siguiente →</button>
        <button type="submit" id="btn-finalizar" class="btn-finalizar oculto">Finalizar registro</button>
      </div>
    </form>
  </div>

  <script src="assets/js/registro3.js"></script>
</body>
</html>
