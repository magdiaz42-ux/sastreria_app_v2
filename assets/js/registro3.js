document.addEventListener("DOMContentLoaded", () => {
  const slider = document.getElementById("slider");
  const indicadores = document.querySelectorAll(".indicador");
  const btnPrev = document.getElementById("btn-prev");
  const btnNext = document.getElementById("btn-next");
  const btnFinalizar = document.getElementById("btn-finalizar");
  const botones = document.querySelectorAll(".btn-opcion");

  let indice = 0;
  const totalSlides = indicadores.length;
  const respuestas = {}; // guardamos en memoria también

  // --- MARCAR RESPUESTAS ---
  botones.forEach(boton => {
    boton.addEventListener("click", () => {
      const pregunta = boton.dataset.pregunta;
      const respuesta = boton.dataset.respuesta;
      const input = document.getElementById(`pregunta${pregunta}`);

      // desactivar otros del mismo grupo
      const grupo = boton.parentElement.querySelectorAll(".btn-opcion");
      grupo.forEach(b => b.classList.remove("activo"));
      boton.classList.add("activo");

      // guardar
      input.value = respuesta;
      respuestas[pregunta] = respuesta;
    });
  });

  // --- ACTUALIZAR SLIDER ---
  function actualizarSlider() {
    slider.style.transform = `translateX(-${indice * 100}%)`;

    indicadores.forEach((ind, i) => ind.classList.toggle("activo", i === indice));

    btnPrev.style.visibility = indice === 0 ? "hidden" : "visible";
    btnNext.style.visibility = indice === totalSlides - 1 ? "hidden" : "visible";
    btnFinalizar.classList.toggle("oculto", indice !== totalSlides - 1);
  }

  // --- VALIDAR ANTES DE AVANZAR ---
  function puedeAvanzar() {
    const actual = indice + 1; // ej: si estamos en 0 -> pregunta1
    const inputActual = document.getElementById(`pregunta${actual}`);
    if (!inputActual.value) {
      alert("⚠️ Por favor, seleccioná una opción antes de continuar.");
      return false;
    }
    return true;
  }

  // --- BOTONES ---
  btnNext.addEventListener("click", () => {
    if (puedeAvanzar() && indice < totalSlides - 1) {
      indice++;
      actualizarSlider();
    }
  });

  btnPrev.addEventListener("click", () => {
    if (indice > 0) {
      indice--;
      actualizarSlider();
    }
  });

  btnFinalizar.addEventListener("click", () => {
    if (!puedeAvanzar()) return; // valida la última también

    // opcional: validar que todas las preguntas tengan respuesta
    for (let i = 1; i <= totalSlides; i++) {
      if (!document.getElementById(`pregunta${i}`).value) {
        alert(`⚠️ Te falta responder la pregunta ${i}.`);
        return;
      }
    }

    document.getElementById("formEtapa3").submit();
  });

  actualizarSlider();
});
