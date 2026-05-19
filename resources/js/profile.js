//
// Esperamos a que toda la página web se haya cargado en el navegador
document.addEventListener('DOMContentLoaded', () => {
    const botonGuardar = document.getElementById('btn-guardar-api');
    
    // Si el botón existe en esta pantalla, le asignamos el evento de clic
    if (botonGuardar) {
        botonGuardar.addEventListener('click', guardarPerfilPorAPI);
    }
});

function guardarPerfilPorAPI() {
    // Capturamos los datos del formulario usando los ID de los inputs
    const nombreIngresado = document.getElementById('name').value;
    const emailIngresado = document.getElementById('email').value;
    
    // Capturamos el token de seguridad de Laravel
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta) {
        console.error('Error: No se encontró el meta tag del CSRF token.');
        return;
    }
    const csrfToken = csrfMeta.getAttribute('content');

    // Ocultamos el mensaje de éxito antes de hacer la petición
    const mensajeExito = document.getElementById('mensaje-exito');
    if (mensajeExito) mensajeExito.classList.add('hidden');

    // Hacemos el fetch a nuestra API local
    fetch('/api/profile/info', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        credentials: 'same-origin', // Clave para enviar las cookies de sesión
        body: JSON.stringify({
            name: nombreIngresado,
            email: emailIngresado
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error del servidor: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log("Respuesta de la API:", data);
        
        // Mostramos el cartel verde de éxito
        if (mensajeExito) {
            mensajeExito.classList.remove('hidden');
            setTimeout(() => {
                mensajeExito.classList.add('hidden');
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Hubo un error contactando a la API:', error);
        alert("No se pudo guardar. Revisa la consola roja.");
    });
}