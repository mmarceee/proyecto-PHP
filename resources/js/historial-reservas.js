document.addEventListener('alpine:init', () => {
    Alpine.data('historialReservas', () => ({
        reservas: [],
        cargando: true,
        
        // Modal de Calificación
        mostrarModal: false,
        reservaSeleccionada: null,
        estrellasHover: 0, // Para el efecto visual al pasar el mouse
        form: {
            puntuacion: 0,
            comentario: ''
        },
        mensaje: '',
        error: '',

        async init() {
            await this.cargarHistorial();
        },

        async cargarHistorial() {
            try {
                const response = await fetch('/api/reservas/historial', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                
                this.reservas = await response.json();
            } catch (err) {
                console.error("Error cargando el historial:", err);
            } finally {
                this.cargando = false;
            }
        },

        abrirModalCalificacion(reserva) {
            this.reservaSeleccionada = reserva;
            this.form = { puntuacion: 0, comentario: '' };
            this.estrellasHover = 0;
            this.mensaje = '';
            this.error = '';
            this.mostrarModal = true;
        },

        async enviarCalificacion() {
            if (this.form.puntuacion === 0) {
                this.error = 'Por favor, selecciona una puntuación de 1 a 5 estrellas.';
                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const url = `/api/reservas/${this.reservaSeleccionada.id}/calificar`;

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (!response.ok) throw new Error(data.message || 'Error al calificar.');

                this.mensaje = '¡Gracias por tu valoración!';
                
                // Actualizamos el estado visual para ocultar el botón
                this.reservaSeleccionada.ya_calificado = true;
                
                setTimeout(() => {
                    this.mostrarModal = false;
                }, 1500);

            } catch (err) {
                this.error = err.message;
            }
        }
    }));
});