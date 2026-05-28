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

        //Modal reservar turno
        showConfirmModal: false,
        fechaSeleccionada: '',
        horaSeleccionada: '',

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
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
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

            window.dispatchEvent(new CustomEvent('filtrar-mapa', { detail: profesional.id }));
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
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
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

        //Función que reemplaza a el antiguo @click en la grilla
        prepararReserva(fecha, hora, ocupado) {
            // CLÁUSULA DE GUARDIA: Si el bloque está ocupado, morimos acá
            if (ocupado) return; 

            // Limpiamos mensajes anteriores y guardamos los datos
            this.error = '';
            this.mensajeExito = '';
            this.fechaSeleccionada = fecha;
            this.horaSeleccionada = hora;
            
            // Abrimos el modal elegante
            this.showConfirmModal = true;
        },

        // 3. Función para cancelar/cerrar
        cerrarModalReserva() {
            this.showConfirmModal = false;
            this.fechaSeleccionada = '';
            this.horaSeleccionada = '';
        },

        // 4. Tu lógica de fetch intacta (Se llama desde el botón ACEPTAR del modal)
        async ejecutarReserva() {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch('/api/paciente/agenda/reservar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        profesional_id: this.profesionalSeleccionado.id,
                        servicio_id: this.servicioSeleccionado.id,
                        // Usamos las variables que guardamos en prepararReserva()
                        fecha: this.fechaSeleccionada, 
                        hora_inicio: this.horaSeleccionada
                    })
                });

                const data = await response.json();
                
                if (!response.ok) throw new Error(data.error || 'No se pudo agendar.');

                // Si todo sale bien:
                this.mensajeExito = data.message;
                this.cerrarModalReserva(); // Cerramos el modal
                await this.cargarAgenda(); // Refrescamos grilla de inmediato
                
            } catch (err) {
                this.error = err.message;
                this.cerrarModalReserva(); // Cerramos el modal para que el usuario pueda ver el mensaje de error en pantalla
            }
        },
    }));

    // ==========================================
    // NUEVO: COMPONENTE DEL MAPA DE BÚSQUEDA
    // ==========================================
    Alpine.data('mapaBusqueda', () => ({
        mapa: null,
        marcadores: [], 
        lugares: [],    

        async iniciarMapa() {
            this.mapa = L.map(this.$refs.mapaBusqueda).setView([-34.9011, -56.1645], 7); 
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(this.mapa);
            
            await this.cargarLugares();
        },

        async cargarLugares() {
            try {
                const response = await fetch('/api/lugares-atencion', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                // Si hay un error 500 o 403, atrapamos el JSON real para poder leerlo
                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || `Error HTTP: ${response.status}`);
                }

                this.lugares = await response.json();
                this.dibujarPines(this.lugares); 
            } catch (error) {
                console.error("Error cargando ubicaciones de la API:", error);
            }
        },

        dibujarPines(lugaresFiltrados) {
            this.marcadores.forEach(m => this.mapa.removeLayer(m));
            this.marcadores = [];

            if (lugaresFiltrados.length === 0) return;

            const bounds = L.latLngBounds();

            lugaresFiltrados.forEach(lugar => {
                const marker = L.marker([lugar.latitud, lugar.longitud]).addTo(this.mapa);
                const nombreMostrar = lugar.profesional.nombre_comercial ?? `${lugar.profesional.nombre} ${lugar.profesional.apellido}`;
                
                marker.bindPopup(`
                    <div class="text-sm p-1 text-slate-800">
                        <strong class="text-blue-600 font-bold block">${nombreMostrar}</strong>
                        <span class="font-medium">${lugar.nombre}</span><br>
                        <span class="text-gray-500 text-xs block mt-1">${lugar.direccion}, ${lugar.ciudad}</span>
                    </div>
                `);
                
                this.marcadores.push(marker);
                bounds.extend([lugar.latitud, lugar.longitud]);
            });

            if (this.marcadores.length > 0) {
                this.mapa.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
            }
        },

        filtrarPorProfesional(profesionalId) {
            if (!profesionalId) {
                this.dibujarPines(this.lugares); 
            } else {
                const filtrados = this.lugares.filter(l => l.profesional_id === profesionalId);
                this.dibujarPines(filtrados);
            }
        }
    })); 
});