/* ============================================
   main.js — Control principal de la WebApp
   ============================================ */

/* --- 1️⃣ REGISTRO DEL SERVICE WORKER (modo offline) --- */
if ('serviceWorker' in navigator) {
  navigator.serviceWorker
    .register('/sastreria_app/service-worker.js')
    .then(() => console.log('✅ Service Worker registrado correctamente'))
    .catch(err => console.error('❌ Error al registrar el Service Worker:', err));
}

/* --- 2️⃣ DETECTAR ESTADO DE CONEXIÓN --- */
const estadoConexion = document.createElement('div');
estadoConexion.id = 'estado-conexion';
estadoConexion.style.position = 'fixed';
estadoConexion.style.bottom = '15px';
estadoConexion.style.right = '15px';
estadoConexion.style.padding = '8px 14px';
estadoConexion.style.borderRadius = '8px';
estadoConexion.style.fontSize = '14px';
estadoConexion.style.fontWeight = 'bold';
estadoConexion.style.zIndex = '1000';
estadoConexion.style.transition = '0.5s all ease';
document.body.appendChild(estadoConexion);

// Actualiza el indicador según haya o no conexión
function actualizarEstadoConexion() {
  if (navigator.onLine) {
    estadoConexion.textContent = '🟢 En línea';
    estadoConexion.style.background = 'rgba(0, 255, 100, 0.2)';
    estadoConexion.style.color = '#00ffcc';
    estadoConexion.style.boxShadow = '0 0 10px #00ffcc55';
  } else {
    estadoConexion.textContent = '🔴 Sin conexión';
    estadoConexion.style.background = 'rgba(255, 50, 50, 0.2)';
    estadoConexion.style.color = '#ff5555';
    estadoConexion.style.boxShadow = '0 0 10px #ff555555';
  }
}

window.addEventListener('online', actualizarEstadoConexion);
window.addEventListener('offline', actualizarEstadoConexion);
actualizarEstadoConexion(); // ejecutar al cargar

/* --- 3️⃣ EFECTO DE CARGA / INICIO (visual) --- */
document.addEventListener('DOMContentLoaded', () => {
  const titulo = document.querySelector('.glow-text');
  if (titulo) {
    titulo.style.opacity = '0';
    titulo.style.transform = 'translateY(-20px)';
    setTimeout(() => {
      titulo.style.transition = 'all 1s ease';
      titulo.style.opacity = '1';
      titulo.style.transform = 'translateY(0)';
    }, 300);
  }

  const boton = document.querySelector('.btn-dark');
  if (boton) {
    boton.style.opacity = '0';
    setTimeout(() => {
      boton.style.transition = 'all 1s ease';
      boton.style.opacity = '1';
    }, 1000);
  }
});

/* --- 4️⃣ (FUTURO) SINCRONIZACIÓN DE DATOS --- */
// Más adelante se puede agregar aquí una función que:
// - Guarde datos en IndexedDB cuando no hay conexión
// - Los sincronice automáticamente con el servidor MySQL cuando vuelve la red
