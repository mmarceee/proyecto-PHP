document.addEventListener('alpine:init', () => {
    Alpine.data('gestionServicios', () => ({
        servicios: [],
        categorias: [], //Guardará las categorías que vengan de la API
        cargando: true,
        mostrarModal: false,
        modoEdicion: false,
        idServicioEdicion: null,
        
        form: {
            nombre: '',
            descripcion: '',
            precio: '',
            duracion: '60',
            modalidad: 'Virtual',
            bufferEntreTurnos: '0',
            categoria_servicio_id: '' //Campo reactivo para el select
        },
        
        mensajeExito: '',
        error: '',

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
                this.categorias = data.categorias; //Guardamos las categorías de la base de datos
            } catch (err) {
                console.error('Error cargando servicios:', err);
            } finally {
                this.cargando = false;
            }
        },

        abrirModalCrear() {
            this.modoEdicion = false;
            this.idServicioEdicion = null;
            this.error = '';
            this.form = { 
                nombre: '', 
                descripcion: '', 
                precio: '', 
                duracion: '60', 
                modalidad: 'Virtual', 
                bufferEntreTurnos: '0',
                categoria_servicio_id: '' // Nace vacío para forzar la selección
            };
            this.mostrarModal = true;
        },

        abrirModalEditar(servicio) {
            this.modoEdicion = true;
            this.idServicioEdicion = servicio.id;
            this.error = '';
            this.form = {
                nombre: servicio.nombre,
                descripcion: servicio.descripcion ?? '',
                precio: servicio.precio,
                duracion: servicio.duracion.toString(),
                modalidad: servicio.modalidad,
                bufferEntreTurnos: (servicio.bufferEntreTurnos ?? 0).toString(),
                categoria_servicio_id: servicio.categoria_servicio_id.toString() //Seteamos la categoría actual
            };
            this.mostrarModal = true;
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
        }
    }));
});