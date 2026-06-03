document.addEventListener('alpine:init', () => {
    Alpine.data('paquetesProfesional', () => ({
        paquetes: [],
        servicios: [], // Para llenar el select al crear un paquete
        cargando: true,
        guardando: false,
        error: '',
        mensajeExito: '',
        mostrarModal: false,

        // Formulario limpio para un nuevo paquete
        form: {
            servicio_id: '',
            nombre: '',
            descripcion: '',
            cantidad_sesiones: 4, // Valor sugerido por defecto
            precio: '',
            validez_meses: 3,     // Valor sugerido por defecto
            activo: true
        },

        async init() {
            // Al arrancar, cargamos los paquetes y los servicios disponibles
            await this.cargarPaquetes();
            await this.cargarServiciosAux();
        },

        async cargarPaquetes() {
            this.cargando = true;
            this.error = '';
            try {
                const response = await fetch('/api/profesional/paquetes', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                });
                if (!response.ok) throw new Error('No se pudieron cargar los paquetes.');
                this.paquetes = await response.json();
            } catch (err) {
                this.error = err.message;
            } finally {
                this.cargando = false;
            }
        },

        // Cargamos los servicios del profesional para el elemento <select>
        async cargarServiciosAux() {
            try {
                const response = await fetch('/api/profesional/servicios', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();

                    // Si Laravel paginó los resultados, el array viene dentro de data.data.
                    // Si lo devolvió envuelto, viene en data.servicios. 
                    // Si viene puro, es simplemente data.
                    this.servicios = data.data || data.servicios || data;
                }
            } catch (err) {
                console.error('Error al cargar servicios para el select:', err);
            }
        },

        abrirModalCrear() {
            // Limpiamos el formulario antes de abrir
            this.form = {
                servicio_id: '',
                nombre: '',
                descripcion: '',
                cantidad_sesiones: 4,
                precio: '',
                validez_meses: 3,
                activo: true
            };
            this.error = '';
            this.mostrarModal = true;
        },

        async guardarPaquete() {
            this.guardando = true;
            this.error = '';
            this.mensajeExito = '';
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch('/api/profesional/paquetes', {
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
                if (!response.ok) {
                    // Si hay errores de validación de Laravel, mostramos el primero
                    throw new Error(data.error || data.message || 'Error al guardar el paquete.');
                }

                this.mensajeExito = '¡Paquete creado exitosamente!';
                this.mostrarModal = false;
                await this.cargarPaquetes(); // Recargamos la grilla
            } catch (err) {
                this.error = err.message;
            } finally {
                this.guardando = false;
            }
        },

        async toggleActivo(paquete) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(`/api/profesional/paquetes/${paquete.id}/toggle`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    const data = await response.json();
                    // Actualizamos el estado visualmente de forma inmediata
                    paquete.activo = data.activo;
                }
            } catch (err) {
                console.error('Error al cambiar estado del paquete:', err);
            }
        }
    }));
});