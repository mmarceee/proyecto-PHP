document.addEventListener('alpine:init', () => {
    Alpine.data('calendarioProfesional', () => ({
        cargando: true,
        proximasSesiones: [],
        showCancelModal: false,
        reservaACancelar: null,
        motivoCancelacion: '',

        async init() {
            await this.cargarConsultas();
        },

        async cargarConsultas() {
            this.cargando = true;
            try {
                const response = await fetch('/api/profesional/calendario-consultas', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });
                if (response.ok) {
                    const data = await response.json();
                    this.proximasSesiones = data.proximasSesiones || [];
                }
            } catch (error) {
                console.error('Error cargando consultas:', error);
            } finally {
                this.cargando = false;
            }
        },

        abrirModalCancelacion(reservaId) {
            this.reservaACancelar = reservaId;
            this.motivoCancelacion = '';
            this.showCancelModal = true;
        },

        cerrarModalCancelacion() {
            this.showCancelModal = false;
            this.reservaACancelar = null;
            this.motivoCancelacion = '';
        },

        async confirmarCancelacion() {
            if (this.motivoCancelacion.trim() === '') {
                alert('El motivo de cancelación es obligatorio.');
                return;
            }

            try {
                const response = await fetch(`/api/reservas/${this.reservaACancelar}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    body: JSON.stringify({ 
                        motivo_cancelacion: this.motivoCancelacion 
                    })
                });

                if (response.ok) {
                    this.cerrarModalCancelacion();
                    await this.cargarConsultas();
                } else {
                    const error = await response.json();
                    alert('Error al cancelar: ' + (error.message || 'Intenta nuevamente'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Hubo un problema al conectar con el servidor.');
            }
        }
    }));
});