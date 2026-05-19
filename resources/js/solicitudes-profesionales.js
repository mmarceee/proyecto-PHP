document.addEventListener('alpine:init', () => {
    Alpine.data('solicitudesProfesionales', () => ({
        cargando: true,
        solicitudes: [],
        mensaje: '',
        error: '',

        async cargarSolicitudes() {
            try {
                const response = await fetch('/api/dashboard', {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('No se pudieron cargar las solicitudes.');
                }

                const data = await response.json();

                if (data.tipo !== 'admin') {
                    window.location.href = '/dashboard';
                    return;
                }

                this.solicitudes = data.datos.profesionalesPendientes ?? [];

            } catch (error) {
                console.error(error);
                this.error = 'No se pudieron cargar las solicitudes profesionales.';
            } finally {
                this.cargando = false;
            }
        },

        async aceptar(id) {
            await this.cambiarEstado(id, 'aprobar', 'Profesional aprobado correctamente.');
        },

        async rechazar(id) {
            await this.cambiarEstado(id, 'rechazar', 'Solicitud profesional rechazada.');
        },

        async cambiarEstado(id, accion, mensajeExito) {
            this.mensaje = '';
            this.error = '';

            try {
                // Buscamos el token CSRF que Laravel inyecta en el meta tag del HTML por defecto
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const response = await fetch(`/api/profesionales/${id}/${accion}`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (!response.ok) {
                    throw new Error('No se pudo procesar la solicitud.');
                }

                this.solicitudes = this.solicitudes.filter(
                    solicitud => solicitud.id !== id
                );

                this.mensaje = mensajeExito;

            } catch (error) {
                console.error(error);
                this.error = 'Ocurrió un error al procesar la solicitud.';
            }
        }
    }));
});