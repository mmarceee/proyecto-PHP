document.addEventListener('alpine:init', () => {
    Alpine.data('busquedaServicios', () => ({
        cargando: false,
        cargandoAgenda: false,
        query: '',
        categoriaSeleccionada: 'Todas las categorías',
        categorias: ['Todas las categorías'],
        menuCategoriasAbierto: false,
        profesionales: [],
        resenas: [],
        
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

        //Paquetes
        paqueteDisponible: null,
        usarPaquete: false,

        async init() {
            await this.cargarCategorias();
            
            //ESCUCHADORES EN TIEMPO REAL: Si el usuario escribe o cambia la categoría, busca solo
            this.$watch('query', () => this.ejecutarBusqueda());
            this.$watch('categoriaSeleccionada', () => this.ejecutarBusqueda());
            
            // Carga inicial al abrir la pantalla
            await this.ejecutarBusqueda();

            setInterval(() => {
                if (this.profesionalSeleccionado && !this.cargandoAgenda) {
                    this.cargarAgenda();
                }
            }, 30000);
        },
        
        async ejecutarBusqueda() {
            this.cargando = true;
            try {
                const params = new URLSearchParams({
                    q: this.query,
                    categoria: this.categoriaSeleccionada
                });

                const response = await fetch(`/api/servicios/buscar?${params.toString()}`, {
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
        
        async verDisponibilidad(profesional) {
            this.profesionalSeleccionado = profesional;
            this.servicioSeleccionado = null; // Reseteamos el servicio para obligar a elegir uno
            this.semana = [];
            this.error = '';
            this.mensajeExito = '';
            this.resenas = []; // Limpiamos reseñas anteriores
            
            window.dispatchEvent(new CustomEvent('filtrar-mapa', { detail: profesional.id }));

            this.$nextTick(() => { // Bloque para autoscrollear en pantallas chicas a la disponibilidad
                if (window.matchMedia('(max-width: 1279px)').matches) {
                    this.$refs.panelReserva?.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });

            // Consultar las calificaciones del profesional seleccionado
            try {
                const response = await fetch(`/api/profesionales/${profesional.id}/calificaciones`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.resenas = data.calificaciones ?? [];
                }
            } catch (err) {
                console.error('Error al cargar reseñas del profesional:', err);
            }

            
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

        async prepararReserva(fecha, hora, ocupado) {
            if (ocupado) return; 

            this.error = '';
            this.mensajeExito = '';
            this.fechaSeleccionada = fecha;
            this.horaSeleccionada = hora;
            this.paqueteDisponible = null; // Reiniciamos
            this.usarPaquete = false;      // Reiniciamos

            // Verificamos si el usuario tiene un paquete activo para ESTE servicio
            try {
                const response = await fetch(`/api/cliente/paquetes/verificar?servicio_id=${this.servicioSeleccionado.id}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.tiene_paquete) {
                        this.paqueteDisponible = data.paquete; // Guardamos el paquete para mostrarlo en el modal
                    }
                }
            } catch (err) {
                console.error('Error verificando paquetes:', err);
                // No detenemos el flujo si falla, simplemente sigue la reserva normal
            }
            
            this.showConfirmModal = true;
        },

        // Función para cancelar/cerrar
        cerrarModalReserva() {
            this.showConfirmModal = false;
            this.fechaSeleccionada = '';
            this.horaSeleccionada = '';
        },

        async ejecutarReserva() {
            try {
                const profesionalId = this.profesionalSeleccionado.id;
                const servicioId = this.servicioSeleccionado.id;
                const fecha = this.fechaSeleccionada;
                const horaInicio = this.horaSeleccionada;
                const compraPaqueteId = this.usarPaquete ? this.paqueteDisponible.id : null;

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                // CASO 1: Si agendamos gastando una sesión de un PAQUETE ya comprado
                if (compraPaqueteId) {
                    const responseReserva = await fetch('/api/paciente/agenda/reservar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            profesional_id: profesionalId,
                            servicio_id: servicioId,
                            fecha: fecha, 
                            hora_inicio: horaInicio,
                            compra_paquete_id: compraPaqueteId
                        })
                    });
                         
                    const dataReserva = await responseReserva.json();
                    
                    if (responseReserva.ok) {
                        this.mensajeExito = "Reserva generada con éxito usando tu paquete.";
                        setTimeout(() => { window.location.href = '/dashboard'; }, 1500);
                    } else {
                        throw new Error(dataReserva.message || dataReserva.error || "Error al procesar la reserva.");
                    }

                } else {
                    // CASO 2: Si es una reserva normal y debe pagarse con PAYPAL
                    this.mensajeExito = "Iniciando transacción segura. Redirigiendo a PayPal...";
                    
                    const responsePago = await fetch('/api/paypal/create-payment-reserva', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            profesional_id: profesionalId,
                            servicio_id: servicioId,
                            fecha: fecha,
                            hora_inicio: horaInicio
                        })
                    });

                    const dataPago = await responsePago.json();

                    if (!responsePago.ok) throw new Error(dataPago.error || 'Error conectando con PayPal');

                    // Redirección directa hacia la pasarela
                    window.location.href = dataPago.approval_url;
                }

            } catch (err) {
                this.error = err.message;
                this.cerrarModalReserva(); 
            }
        },
        async cargarCategorias() {
            try {
                const response = await fetch('/api/servicios/categorias', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
        
                const data = await response.json();
        
                this.categorias = [
                    'Todas las categorías',
                    ...(data.categorias ?? [])
                ];
            } catch (err) {
                console.error('Error al cargar categorías:', err);
                this.categorias = ['Todas las categorías'];
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