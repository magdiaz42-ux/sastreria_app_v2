/* =======================================================
   registro.js — Validación de registro Etapa 1
   ======================================================= */

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('formEtapa1');
  const pass = document.getElementById('password');
  const confirmar = document.getElementById('confirmar');
  const errorPass = document.getElementById('error-pass');

  form.addEventListener('submit', (e) => {
    // Validar contraseñas
    if (pass.value !== confirmar.value) {
      e.preventDefault();
      errorPass.style.display = 'block';
      confirmar.style.border = '2px solid #ff5555';
      return;
    } else {
      errorPass.style.display = 'none';
      confirmar.style.border = '1px solid #00ffcc';
    }

    // Validar teléfono y código de país
    const codigoPais = document.getElementById('codigo_pais').value.trim();
    const telefono = document.getElementById('telefono').value.trim();
    const regexTelefono = /^\d{6,15}$/;

    if (!codigoPais || isNaN(codigoPais) || parseInt(codigoPais) <= 0) {
      e.preventDefault();
      alert("⚠️ Ingresá un código de país válido (por ejemplo 54 para Argentina).");
      return;
    }

    if (!regexTelefono.test(telefono)) {
      e.preventDefault();
      alert("⚠️ Ingresá un número de teléfono válido (solo dígitos, sin espacios ni símbolos).");
      return;
    }
  });
});
