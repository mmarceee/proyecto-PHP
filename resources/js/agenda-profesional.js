document.addEventListener('alpine:init', () => {
    Alpine.data('agendaProfesional', () => ({
        semana: [],
        cargando: true,
        error: '',
        mensajeExito: '',

        async init() {
            await this.cargarAgenda();
        },

        async cargarAgenda() {
            this.cargando = true;
            try {
                const response = await fetch('/api/profesional/agenda', {
                    headers: { 'Accept': 'application/json' }
                });
                
                if (!response.ok) throw new Error('No se pudo cargar la agenda.');
                
                const data = await response.json();
                this.semana = data.semana;
            } catch (err) {
                console.error(err);
                this.error = 'Error al sincronizar los horarios de la semana.';
            } finally {
                this.cargando = false;
            }
        },

        async bloquearDia(fecha) {
            this.mensajeExito = '';
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                // Simulación de bloqueo por API (Excepciones)
                alert('Solicitando bloqueo para la fecha: ' + fecha);
                
                this.mensajeExito = 'Día bloqueado correctamente.';
                await this.cargarAgenda(); // Recargamos para ver los cambios
            } catch (err) {
                this.error = 'No se pudo bloquear el día seleccionado.';
            }
        }
    }));
});