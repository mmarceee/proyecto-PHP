import './profile';
import './videollamada';
import './dashboard-usuario';
import './busqueda-servicios';
import './gestion-servicios';
import './postularse-profesional';
import './gestion-usuarios';
import './agenda-profesional';
import './historial-reservas';
import './solicitudes-profesionales';
import './categorias';
import './echo';
import './paquetes-profesional'
import './explorar-paquetes';
import './notificaciones';
import './mis-paquetes';
import './paquetes-vendidos-profesional';
import './offline-status';
import './calendario-profesional';

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js').catch((error) => {
            console.error('Error al registrar el service worker:', error);
        });
    });
}
