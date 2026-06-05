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
            if (!confirm('¿Estás seguro de que deseas adquirir este paquete de sesiones?')) return;

            this.comprandoId = paqueteId;
            this.error = '';
            this.mensajeExito = '';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(`/api/cliente/paquetes/${paqueteId}/comprar`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin'
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'Error al procesar la compra.');

                this.mensajeExito = '¡Compra exitosa! El paquete ya está en tu cuenta.';
                
                // Opcional: Redirigir al dashboard después de 2 segundos
                setTimeout(() => {
                    window.location.href = '/dashboard';
                }, 2000);

            } catch (err) {
                this.error = err.message;
            } finally {
                this.comprandoId = null;
            }
        }
    }));
});