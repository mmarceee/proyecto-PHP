document.addEventListener('alpine:init', () => {
    Alpine.data('agendaProfesional', () => ({
        semana: [],
        cargando: true,
        error: '',
        mensajeExito: '',

        //Rastrea qué fecha comanda el inicio de la grilla de 7 días
        fechaInicio: new Date().toISOString().split('T')[0],

        mostrarModalReglas: false,
        guardandoReglas: false,
        mostrarModalExcepcion: false,
        guardandoExcepcion: false,
        mostrarModalDesbloquear: false,
        fechaADesbloquear: '',

        formReglas: [
            { dia_semana: 0, nombre: 'Domingo', activo: false, hora_inicio: '08:00', hora_fin: '16:00', duracion_turno: 60, buffer_tiempo: 0 },
            { dia_semana: 1, nombre: 'Lunes', activo: false, hora_inicio: '08:00', hora_fin: '16:00', duracion_turno: 60, buffer_tiempo: 0 },
            { dia_semana: 2, nombre: 'Martes', activo: false, hora_inicio: '08:00', hora_fin: '16:00', duracion_turno: 60, buffer_tiempo: 0 },
            { dia_semana: 3, nombre: 'Miércoles', activo: false, hora_inicio: '08:00', hora_fin: '16:00', duracion_turno: 60, buffer_tiempo: 0 },
            { dia_semana: 4, nombre: 'Jueves', activo: false, hora_inicio: '08:00', hora_fin: '16:00', duracion_turno: 60, buffer_tiempo: 0 },
            { dia_semana: 5, nombre: 'Viernes', activo: false, hora_inicio: '08:00', hora_fin: '16:00', duracion_turno: 60, buffer_tiempo: 0 },
            { dia_semana: 6, nombre: 'Sábado', activo: false, hora_inicio: '08:00', hora_fin: '16:00', duracion_turno: 60, buffer_tiempo: 0 }
        ],

        formExcepcion: { fecha: '', tipo: 'no_disponible', motivo: '' },

        async init() {
            await this.cargarAgenda();
            // AGREGADO: Inicializamos la escucha en tiempo real de forma segura
            this.iniciarEscuchaRealtime();
        },

        async cargarAgenda() {
            this.cargando = true;
            this.error = '';
            try {
                //Enviamos la fecha actual del estado a la API
                const response = await fetch(`/api/profesional/agenda?fecha=${this.fechaInicio}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                if (!response.ok) throw new Error('No se pudo sincronizar la agenda.');
                
                const data = await response.json();
                this.semana = data.semana;

                if (data.reglas_actuales && data.reglas_actuales.length > 0) {
                    this.formReglas.forEach(r => {
                        const encontrada = data.reglas_actuales.find(x => x.dia_semana === r.dia_semana);
                        if (encontrada) {
                            r.activo = true;
                            r.hora_inicio = encontrada.hora_inicio;
                            r.hora_fin = encontrada.hora_fin;
                            r.duracion_turno = encontrada.duracion_turno;
                            r.buffer_tiempo = encontrada.buffer_tiempo;
                        } else {
                            r.activo = false;
                        }
                    });
                }
            } catch (err) {
                console.error(err);
                this.error = 'Ocurrió un error al cargar el calendario.';
            } finally {
                this.cargando = false;
            }
        },

        //Salta 7 días al futuro
        async semanaSiguiente() {
            let fecha = new Date(this.fechaInicio + 'T00:00:00');
            fecha.setDate(fecha.getDate() + 7);
            this.fechaInicio = fecha.toISOString().split('T')[0];
            await this.cargarAgenda();
        },

        //Vuelve 7 días al pasado
        async semanaAnterior() {
            let fecha = new Date(this.fechaInicio + 'T00:00:00');
            fecha.setDate(fecha.getDate() - 7);
            this.fechaInicio = fecha.toISOString().split('T')[0];
            await this.cargarAgenda();
        },

        //Vuelve clavadito al día de hoy
        async volverAHoy() {
            this.fechaInicio = new Date().toISOString().split('T')[0];
            await this.cargarAgenda();
        },

        async guardarReglasBase() {
            this.guardandoReglas = true;
            this.error = '';
            this.mensajeExito = '';
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch('/api/profesional/agenda/reglas', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ reglas: this.formReglas })
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Error al guardar.');

                this.mensajeExito = 'Disponibilidad base guardada correctamente.';
                this.mostrarModalReglas = false;
                await this.cargarAgenda();
            } catch (err) {
                this.error = err.message;
            } finally {
                this.guardandoReglas = false;
            }
        },

        async guardarBloqueoDia() {
            this.guardandoExcepcion = true;
            this.error = '';
            this.mensajeExito = '';
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch('/api/profesional/agenda/excepciones', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(this.formExcepcion)
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'No se pudo registrar el bloqueo.');

                this.mensajeExito = 'El día seleccionado ha sido bloqueado con éxito.';
                this.mostrarModalExcepcion = false;
                this.formExcepcion.motivo = '';
                await this.cargarAgenda();
            } catch (err) {
                this.error = err.message;
            } finally {
                this.guardandoExcepcion = false;
            }
        },

        bloquearDia(fecha) {
            this.formExcepcion.fecha = fecha;
            this.mostrarModalExcepcion = true;
        },

        confirmarDesbloqueo(fecha) {
            this.fechaADesbloquear = fecha;
            this.mostrarModalDesbloquear = true;
        },

        async desbloquearDia() {
            this.mostrarModalDesbloquear = false;
            this.cargando = true;
            this.error = '';
            this.mensajeExito = '';
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(`/api/profesional/agenda/excepciones?fecha=${this.fechaADesbloquear}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin'
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'No se pudo procesar.');

                this.mensajeExito = 'Día desbloqueado correctamente.';
                await this.cargarAgenda();
            } catch (err) {
                this.error = err.message;
                this.cargando = false;
            }
        },

        /**
         * AGREGADO: Escucha en tiempo real mediante WebSockets
         * No interfiere con las funciones nativas de la aplicación.
         */
        iniciarEscuchaRealtime() {
            // Buscamos el ID del profesional desde el meta tag configurado en el HTML
            const profesionalId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');

            if (!profesionalId) {
                console.warn("WebSocket Advertencia: No se encontró <meta name='user-id'>. No se pudo enlazar el canal en tiempo real.");
                return;
            }

            // Validamos que Laravel Echo esté cargado globalmente en el navegador
            if (window.Echo) {
                window.Echo.private(`profesional.${profesionalId}`)
                    .listen('.agenda.modificada', async (evento) => {
                        console.log("¡Cambio detectado en la agenda vía Sockets! Re-sincronizando grilla...", evento);
                        
                        // Re-ejecuta el método original de los gurises para actualizar la UI limpiamente
                        await this.cargarAgenda();
                    });
            }
        }
    }));
});