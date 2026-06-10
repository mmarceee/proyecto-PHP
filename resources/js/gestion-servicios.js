document.addEventListener('alpine:init', () => {
    Alpine.data('gestionServicios', () => ({
        servicios: [],
        categorias: [], 
        cargando: true,
        mostrarModal: false,
        modoEdicion: false,
        idServicioEdicion: null,
        
        // =====================================
        // VARIABLES DEL MAPA INTEGRADAS
        // =====================================
        mapa: null,
        marcador: null,

        form: {
            nombre: '',
            descripcion: '',
            precio: '',
            duracion: '60',
            modalidad: 'Virtual',
            bufferEntreTurnos: '0',
            categoria_id: '', 
            // Nuevos campos para LugarAtencion
            lugar_nombre: '',
            lugar_direccion: '',
            lugar_ciudad: '',
            lugar_departamento: '',
            latitud: -34.9011,
            longitud: -56.1645
        },
        
        mensajeExito: '',
        error: '',
        modalPoliticaOpen: false,
        formPolitica: { 
            tiempo_minimo_cancelacion: 24,
            permite_reprogramacion: true, 
            descripcion: '' 
        },

        async init() {
            await this.cargarServicios();
        },

        async cargarServicios() {
            this.cargando = true;
            try {
                const response = await fetch('/api/profesional/servicios', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                const data = await response.json();
                
                this.servicios = data.servicios;
                this.categorias = data.categorias; 
            } catch (err) {
                console.error('Error cargando servicios:', err);
            } finally {
                this.cargando = false;
            }
        },

        // =====================================
        // LÓGICA DE REACTIVIDAD DEL MAPA
        // =====================================
        cambiarModalidad() {
            if (this.form.modalidad === 'Presencial') {
                this.cargarMapa();
            }
        },

        cargarMapa() {
            // $nextTick espera a que Alpine termine de dibujar el HTML (el x-if del mapa)
            this.$nextTick(() => {
                // Si el div del mapa no existe en el DOM, no hacemos nada
                if (!this.$refs.mapaFormulario) return;

                if (!this.mapa) {
                    // Inicializar el mapa por primera vez
                    this.mapa = L.map(this.$refs.mapaFormulario).setView([this.form.latitud, this.form.longitud], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap'
                    }).addTo(this.mapa);
                    
                    this.marcador = L.marker([this.form.latitud, this.form.longitud], { draggable: true }).addTo(this.mapa);

                    this.marcador.on('dragend', (e) => {
                        const posicion = e.target.getLatLng();
                        this.form.latitud = posicion.lat.toFixed(8);
                        this.form.longitud = posicion.lng.toFixed(8);
                    });

                    this.mapa.on('click', (e) => {
                        this.marcador.setLatLng(e.latlng);
                        this.form.latitud = e.latlng.lat.toFixed(8);
                        this.form.longitud = e.latlng.lng.toFixed(8);
                    });
                } else {
                    // Si el mapa ya existía (ej: cerraste y abriste el modal), forzamos su redibujado
                    this.mapa.invalidateSize();
                    const nuevaPos = [this.form.latitud, this.form.longitud];
                    this.mapa.setView(nuevaPos, 13);
                    this.marcador.setLatLng(nuevaPos);
                }
            });
        },

        abrirModalCrear() {
            this.modoEdicion = false;
            this.idServicioEdicion = null;
            this.error = '';
            // Reseteamos el formulario completo
            this.form = { 
                nombre: '', descripcion: '', precio: '', duracion: '60', 
                modalidad: 'Virtual', bufferEntreTurnos: '0', categoria_id: '',
                lugar_nombre: '', lugar_direccion: '', lugar_ciudad: '', lugar_departamento: '',
                latitud: -34.9011, longitud: -56.1645
            };
            this.mostrarModal = true;
        },

        abrirModalEditar(servicio) {
            this.modoEdicion = true;
            this.idServicioEdicion = servicio.id;
            this.error = '';
            
            // Asignamos los datos del servicio
            this.form = {
                nombre: servicio.nombre,
                descripcion: servicio.descripcion ?? '',
                precio: servicio.precio,
                duracion: servicio.duracion.toString(),
                modalidad: servicio.modalidad,
                bufferEntreTurnos: (servicio.bufferEntreTurnos ?? 0).toString(),
                categoria_id: servicio.categoria_id.toString(),
                
                // Si el servicio ya tiene un lugar de atención asociado, lo cargamos.
                // Ajusta 'servicio.lugar_atencion' según cómo venga el JSON desde tu API.
                lugar_nombre: servicio.lugar_atencion?.nombre ?? '',
                lugar_direccion: servicio.lugar_atencion?.direccion ?? '',
                lugar_ciudad: servicio.lugar_atencion?.ciudad ?? '',
                lugar_departamento: servicio.lugar_atencion?.departamento ?? '',
                latitud: servicio.lugar_atencion?.latitud ?? -34.9011,
                longitud: servicio.lugar_atencion?.longitud ?? -56.1645
            };
            
            this.mostrarModal = true;
            
            // Si el servicio es presencial al abrir, cargamos el mapa inmediatamente
            if (this.form.modalidad === 'Presencial') {
                this.cargarMapa();
            }
        },

        async guardarServicio() {
            this.error = '';
            this.mensajeExito = '';
            
            const url = this.modoEdicion 
                ? `/api/profesional/servicios/${this.idServicioEdicion}` 
                : '/api/profesional/servicios';
                
            const metodo = this.modoEdicion ? 'PUT' : 'POST';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch(url, {
                    method: metodo,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Error al procesar el servicio.');

                this.mensajeExito = data.message;
                this.mostrarModal = false;
                await this.cargarServicios();
            } catch (err) {
                this.error = err.message;
            }
        },

        async eliminarServicio(id) {
            if (!confirm('¿Estás seguro de que quieres eliminar este servicio?')) return;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch(`/api/profesional/servicios/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin'
                });
                if (!response.ok) throw new Error('No se pudo eliminar.');
                await this.cargarServicios();
            } catch (err) {
                this.error = err.message;
            }
        },
        async abrirModalPolitica() {
            try {
                const res = await fetch('/api/profesional/politica-cancelacion', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (res.ok) {
                    const data = await res.json();
                    this.formPolitica = data;
                }
            } catch (e) { console.error(e); }
            this.modalPoliticaOpen = true;
        },
        async guardarPolitica() {
            try {
                const res = await fetch('/api/profesional/politica-cancelacion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(this.formPolitica)
                });
                if (res.ok) {
                    this.modalPoliticaOpen = false;
                    this.mensajeExito = "Política guardada correctamente";
                    setTimeout(() => this.mensajeExito = '', 3000);
                } else {
                    const error = await res.json();
                    this.error = error.message || 'Error al guardar';
                }
            } catch (e) {
                this.error = "Error de conexión";
            }
        },
    }));
});