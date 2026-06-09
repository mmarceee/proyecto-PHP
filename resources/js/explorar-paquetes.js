document.addEventListener('alpine:init', () => {
    Alpine.data('explorarPaquetes', () => ({
        paquetes: [],
        cargando: true,
        comprandoId: null, // Para el spinner del botón
        mensajeExito: '',
        error: '',

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

                async comprar(paqueteId) {
            // Confirmación básica antes de comprar
            if (!confirm('¿Estás seguro de que deseas adquirir este paquete de sesiones mediante PayPal?')) return;

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