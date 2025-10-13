/* ===================================================
   registro2.js — Cámara + luz + selección de avatar
   =================================================== */

document.addEventListener('DOMContentLoaded', () => {
  const video = document.getElementById('video');
  const canvas = document.getElementById('canvas');
  const btnCapturar = document.getElementById('btn-capturar');
  const preview = document.getElementById('preview');
  const btnContinuar = document.getElementById('btn-continuar');
  const avatarsContainer = document.getElementById('avatars');
  const lightOverlay = document.querySelector('.camera-light');

  let selfieBase64 = null;
  let avatarSeleccionado = null;

  /* === INICIAR CÁMARA === */
  async function iniciarCamara() {
    try {
      const constraints = {
        video: {
          facingMode: "user",
          width: { ideal: 720 },
          height: { ideal: 1280 },
        }
      };
      const stream = await navigator.mediaDevices.getUserMedia(constraints);
      video.srcObject = stream;
      setTimeout(() => detectarOscuridad(video), 2000);
    } catch (err) {
      alert("⚠️ No se pudo acceder a la cámara. Permití el acceso o elegí un avatar.");
      console.error(err);
    }
  }

  iniciarCamara();

  /* === DETECTAR OSCURIDAD === */
  function detectarOscuridad(videoElement) {
    const tempCanvas = document.createElement('canvas');
    const ctx = tempCanvas.getContext('2d');
    tempCanvas.width = 64;
    tempCanvas.height = 64;

    function evaluar() {
      ctx.drawImage(videoElement, 0, 0, 64, 64);
      const data = ctx.getImageData(0, 0, 64, 64).data;
      let sum = 0;
      for (let i = 0; i < data.length; i += 4) {
        sum += (data[i] + data[i + 1] + data[i + 2]) / 3;
      }
      const avg = sum / (data.length / 4);
      if (avg < 50) {
        lightOverlay.classList.add('active');
      } else {
        lightOverlay.classList.remove('active');
      }
      requestAnimationFrame(evaluar);
    }

    evaluar();
  }

  /* === CAPTURAR SELFIE === */
  btnCapturar.addEventListener('click', () => {
    const context = canvas.getContext('2d');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    selfieBase64 = canvas.toDataURL('image/png');
    preview.innerHTML = `<img src="${selfieBase64}" alt="Tu selfie">`;
  });

  /* === AVATARES === */
  const totalAvatares = 5;
  for (let i = 1; i <= totalAvatares; i++) {
    const img = document.createElement('img');
    img.src = `assets/img/avatars/avatar${i}.png`;
    img.alt = `Avatar ${i}`;
    img.addEventListener('click', () => {
      document.querySelectorAll('.avatar-container img').forEach(a => a.classList.remove('selected'));
      img.classList.add('selected');
      avatarSeleccionado = img.src;
      selfieBase64 = null;
    });
    avatarsContainer.appendChild(img);
  }

  /* === CONTINUAR === */
  btnContinuar.addEventListener('click', () => {
    if (!selfieBase64 && !avatarSeleccionado) {
      alert("⚠️ Tenés que sacar una selfie o elegir un avatar para continuar.");
      return;
    }
    localStorage.setItem('sastreria_selfie', selfieBase64 || avatarSeleccionado);
    window.location.href = "registro_etapa3.php";
  });
});
