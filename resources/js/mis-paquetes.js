document.addEventListener('alpine:init', () => {
    Alpine.data('misPaquetesCliente', () => ({
        compras: [],
        cargando: true,
        error: '',

        async init() {
            await this.cargarMisPaquetes();
        },

        async cargarMisPaquetes() {
            this.cargando = true;
            try {
                const response = await fetch('/api/cliente/paquetes', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!response.ok) throw new Error('No se pudieron cargar tus paquetes.');
                this.compras = await response.json();
            } catch (err) {
                this.error = err.message;
            } finally {
                this.cargando = false;
            }
        },

        // Matemáticas para la barra de progreso
        calcularPorcentaje(disponibles, total) {
            const consumidas = total - disponibles;
            return (consumidas / total) * 100;
        },

        // Calcula la fecha de vencimiento sumando los meses a la fecha de compra
        calcularVencimiento(fechaCompra, mesesValidez) {
            if (!fechaCompra || !mesesValidez) return 'Sin vencimiento';
            
            const fecha = new Date(fechaCompra);
            fecha.setMonth(fecha.getMonth() + mesesValidez);
            
            return fecha.toLocaleDateString('es-ES', { 
                day: '2-digit', 
                month: 'short', 
                year: 'numeric' 
            });
        }
    }));
});