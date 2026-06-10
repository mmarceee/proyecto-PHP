document.addEventListener('alpine:init', () => {
    Alpine.data('paquetesVendidosProfesional', () => ({
        ventas: [],
        cargando: true,
        error: '',

        async init() {
            await this.cargarVentas();
        },

        async cargarVentas() {
            this.cargando = true;
            this.error = '';

            try {
                const response = await fetch('/api/profesional/paquetes/vendidos', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('No se pudieron cargar los paquetes vendidos.');
                }

                this.ventas = await response.json();
            } catch (err) {
                this.error = err.message;
            } finally {
                this.cargando = false;
            }
        },

        nombreCliente(venta) {
            const user = venta.cliente?.user;

            if (!user) {
                return 'Cliente sin datos';
            }

            return `${user.name ?? ''} ${user.last_name ?? ''}`.trim() || 'Cliente sin nombre';
        },

        correoCliente(venta) {
            return venta.cliente?.user?.email ?? 'Sin correo';
        },

        nombrePaquete(venta) {
            return venta.paquete_servicio?.nombre ?? 'Paquete sin nombre';
        },

        nombreServicio(venta) {
            return venta.paquete_servicio?.servicio?.nombre ?? 'Servicio no especificado';
        },

        fechaCompra(venta) {
            if (!venta.fecha_compra) {
                return 'Sin fecha';
            }

            return new Date(venta.fecha_compra).toLocaleDateString('es-UY');
        }
    }));
});