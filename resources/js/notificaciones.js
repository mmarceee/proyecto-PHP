document.addEventListener('alpine:init', () => {
    Alpine.data('notificacionesDropdown', () => ({
        abierto: false,
        notificaciones: [],
        count: 0,
        cargando: false,

        async init() {
            await this.cargarNotificaciones();
            await this.cargarCount();
            this.iniciarEscuchaRealtime();
        },

        async cargarNotificaciones() {
            this.cargando = true;

            try {
                const response = await fetch('/api/notificaciones', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'No se pudieron cargar las notificaciones');
                }

                this.notificaciones = data.data ?? [];
            } catch (error) {
                console.error('Error cargando notificaciones:', error);
            } finally {
                this.cargando = false;
            }
        },

        async cargarCount() {
            try {
                const response = await fetch('/api/notificaciones/no-leidas', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'No se pudo cargar el contador');
                }

                this.count = data.count ?? 0;
            } catch (error) {
                console.error('Error cargando contador de notificaciones:', error);
            }
        },

        alternarPanel() {
            this.abierto = !this.abierto;
        },


        formatearFecha(fecha) {
            if (!fecha) {
                return '';
            }

            const fechaNotificacion = new Date(fecha);
            const ahora = new Date();
            const diferenciaMs = ahora - fechaNotificacion;
            const diferenciaMinutos = Math.floor(diferenciaMs / 60000);

            if (diferenciaMinutos < 1) {
                return 'Recién';
            }

            if (diferenciaMinutos < 60) {
                return `Hace ${diferenciaMinutos} min`;
            }

            const diferenciaHoras = Math.floor(diferenciaMinutos / 60);

            if (diferenciaHoras < 24) {
                return `Hace ${diferenciaHoras} h`;
            }

            return fechaNotificacion.toLocaleDateString('es-UY', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            });
        },

        async marcarComoLeida(id) {

            const notificacion = this.notificaciones.find((item) => item.id === id);

            if (!notificacion || notificacion.leida) {
                return;
            }

            try {
                const response = await fetch(`/api/notificaciones/${id}/leer`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'No se pudo marcar la notificación como leída');
                }

                this.notificaciones = this.notificaciones.map((notificacion) => {
                    if (notificacion.id !== id) {
                        return notificacion;
                    }

                    return data.data;
                });

                await this.cargarCount();
            } catch (error) {
                console.error('Error marcando notificación como leída:', error);
            }
        },

        async marcarTodasComoLeidas() {
            try {
                const response = await fetch('/api/notificaciones/leer-todas', {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'No se pudieron marcar todas como leídas');
                }

                this.notificaciones = this.notificaciones.map((notificacion) => ({
                    ...notificacion,
                    leida: true,
                }));

                this.count = 0;
            } catch (error) {
                console.error('Error marcando todas las notificaciones como leídas:', error);
            }
        },

        iniciarEscuchaRealtime() {
            if (!window.Echo) {
                return;
            }

            const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');

            if (!userId) {
                return;
            }

            window.Echo.private(`usuario.${userId}`)
                .listen('.notificacion.creada', (evento) => {
                        console.log('[WS NOTIFICACION] Llegó una notificación:', evento);
                    const notificacion = evento.notificacion;

                    this.notificaciones = [
                        notificacion,
                        ...this.notificaciones, //operador spread, significa “expandí los elementos de este array acá”, sino crea un array dentro del array
                    ].slice(0, 20);

                    if (!notificacion.leida) {
                        this.count++;
                    }
                });
        },

    }));
});