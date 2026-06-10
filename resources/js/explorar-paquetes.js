document.addEventListener('alpine:init', () => {
    Alpine.data('explorarPaquetes', () => ({
        paquetes: [],
        cargando: true,
        comprandoId: null, // Para el spinner del botón
        mensajeExito: '',
        error: '',
        
        // --- VARIABLES PARA EL MODAL ---
        showModalConfirmacion: false,
        paqueteAComprar: null,

        async init() {
            await this.cargarDisponibles();
        },

        async cargarDisponibles() {
            this.cargando = true;
            try {
                const response = await fetch('/api/cliente/paquetes/disponibles', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (response.ok) {
                    this.paquetes = await response.json();
                }
            } catch (err) {
                this.error = 'No se pudieron cargar los paquetes disponibles.';
            } finally {
                this.cargando = false;
            }
        },

        // --- FUNCIONES PARA CONTROLAR EL MODAL ---
        abrirModalConfirmacion(paqueteId) {
            this.paqueteAComprar = paqueteId;
            this.showModalConfirmacion = true;
        },

        cerrarModalConfirmacion() {
            this.showModalConfirmacion = false;
            this.paqueteAComprar = null;
        },

        // --- FUNCIÓN COMPRAR ---
        async comprar() {
            const paqueteId = this.paqueteAComprar;
            if (!paqueteId) return;

            this.cerrarModalConfirmacion(); // Cerramos el modal antes de redirigir
            this.comprandoId = paqueteId;
            this.error = '';
            this.mensajeExito = '';

            try {
                this.mensajeExito = 'Iniciando transacción segura. Redirigiendo a PayPal...';
                
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                const responsePago = await fetch('/api/paypal/create-payment-paquete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        paquete_id: paqueteId
                    })
                });

                const dataPago = await responsePago.json();

                if (!responsePago.ok) throw new Error(dataPago.error || 'Error conectando con PayPal');

                // Nos vamos a PayPal para autorizar la compra
                window.location.href = dataPago.approval_url;

            } catch (err) {
                this.error = err.message;
            } finally {
                this.comprandoId = null;
            }
        }
    }));
});