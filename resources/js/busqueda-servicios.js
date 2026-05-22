document.addEventListener('alpine:init', () => {
    Alpine.data('busquedaServicios', () => ({
        cargando: false,
        cargandoAgenda: false,
        query: '',
        categoriaSeleccionada: 'Todas las categorías',
        menuCategoriasAbierto: false,
        profesionales: [],
        
        // Estados de selección modular
        profesionalSeleccionado: null,
        servicioSeleccionado: null,
        
        // Estados de la Agenda Rodante
        semana: [],
        fechaInicio: new Date().toISOString().split('T')[0],
        mensajeExito: '',
        error: '',

        async init() {
            //ESCUCHADORES EN TIEMPO REAL: Si el usuario escribe o cambia la categoría, busca solo
            this.$watch('query', () => this.ejecutarBusqueda());
            this.$watch('categoriaSeleccionada', () => this.ejecutarBusqueda());
            
            // Carga inicial al abrir la pantalla
            await this.ejecutarBusqueda();
        },

        async ejecutarBusqueda() {
            this.cargando = true;
            try {
                const response = await fetch(`/api/servicios/buscar?q=${this.query}&categoria=${this.categoriaSeleccionada}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                this.profesionales = data.profesionales;
            } catch (err) {
                console.error('Error al buscar:', err);
            } finally {
                this.cargando = false;
            }
        },

        verDisponibilidad(profesional) {
            this.profesionalSeleccionado = profesional;
            this.servicioSeleccionado = null; // Reseteamos el servicio para obligar a elegir uno
            this.semana = [];
            this.error = '';
            this.mensajeExito = '';
        },

        async seleccionarServicio(servicio) {
            this.servicioSeleccionado = servicio;
            this.fechaInicio = new Date().toISOString().split('T')[0]; // Reiniciamos a hoy
            await this.cargarAgenda();
        },

        async cargarAgenda() {
            if (!this.profesionalSeleccionado) return;
            this.cargandoAgenda = true;
            try {
                const response = await fetch(`/api/servicios/profesionales/${this.profesionalSeleccionado.id}/agenda?fecha=${this.fechaInicio}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                this.semana = data.semana;
            } catch (err) {
                console.error('Error al cargar agenda:', err);
            } finally {
                this.cargandoAgenda = false;
            }
        },

        async avanzarSemana() {
            let fecha = new Date(this.fechaInicio + 'T00:00:00');
            fecha.setDate(fecha.getDate() + 7);
            this.fechaInicio = fecha.toISOString().split('T')[0];
            await this.cargarAgenda();
        },

        async retrocederSemana() {
            let fecha = new Date(this.fechaInicio + 'T00:00:00');
            fecha.setDate(fecha.getDate() - 7);
            this.fechaInicio = fecha.toISOString().split('T')[0];
            await this.cargarAgenda();
        },

        async reservarTurno(fecha, hora, ocupado) {
            //CLÁUSULA DE GUARDIA: Si el bloque está ocupado, morimos acá
            if (ocupado) return; 

            if (!confirm(`¿Confirmas la reserva para el día ${fecha} a las ${hora} hs?`)) return;
            
            this.error = '';
            this.mensajeExito = '';
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch('/api/paciente/agenda/reservar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        profesional_id: this.profesionalSeleccionado.id,
                        servicio_id: this.servicioSeleccionado.id,
                        fecha: fecha,
                        hora_inicio: hora
                    })
                });

                const data = await response.json();
                // 🌟 Capturamos el error 422 del backend si falló la validación horaria
                if (!response.ok) throw new Error(data.error || 'No se pudo agendar.');

                this.mensajeExito = data.message;
                await this.cargarAgenda(); // Refrescamos grilla de inmediato
            } catch (err) {
                this.error = err.message;
            }
        }
    }));
});