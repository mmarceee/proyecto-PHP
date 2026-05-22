document.addEventListener('alpine:init', () => {
    Alpine.data('dashboardData', () => ({
        cargando: true,
        usuario: { nombre: '' },
        saludo: '',
        tipo: '',
        profesional: { tieneSolicitud: false, estado: '', pendiente: false, aprobado: false },
        adminPendingProfessionals: [],
        consultasHoy: [],
        proximasSesiones: [],
        selectedItem: null,

        async cargarDashboard() {
            this.cargando = true;
            try {
                const response = await fetch('/api/dashboard', {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    throw new Error('Error al obtener datos del dashboard');
                }

                const data = await response.json();

                this.usuario = data.usuario;
                this.saludo = data.saludo;
                this.tipo = data.tipo;
                this.profesional = data.profesional;
                this.adminPendingProfessionals = data.datos.profesionalesPendientes;
                this.consultasHoy = data.datos.consultasHoy;
                this.proximasSesiones = data.datos.proximasSesiones;

                // Inicializar selección automática si hay datos
                if (this.tipo === 'profesional' && this.consultasHoy.length > 0) {
                    this.selectedItem = this.consultasHoy[0].id;
                } else if (this.tipo === 'cliente' && this.proximasSesiones.length > 0) {
                    this.selectedItem = this.proximasSesiones[0].id;
                }

            } catch (error) {
                console.error('Error cargando el dashboard:', error);
            } finally {
                this.cargando = false;
            }
        },

        async confirmarReserva(id) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(`/api/reservas/${id}/confirmar`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'No se pudo confirmar la reserva');

                // 🔥 REFRESCO REACTIVO: Volvemos a cargar los datos del dashboard instantáneamente
                await this.cargarDashboard();

            } catch (error) {
                console.error(error);
                alert(error.message || 'Ocurrió un error al confirmar la reserva.');
            }
        },

        async avanzarEstadoReserva(id) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(`/api/reservas/${id}/avanzar-estado`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'No se pudo actualizar el estado de la reserva');

                // REFRESCO INMEDIATO: Volvemos a pedir los datos a la API 
                // para que pinte el nuevo estado y cambie el botón al vuelo
                await this.cargarDashboard();

            } catch (error) {
                console.error(error);
                alert(error.message || 'Ocurrió un error al actualizar el estado.');
            }
        },

        async aprobarProfesional(id) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(`/api/admin/profesionales/${id}/aprobar`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (!response.ok) throw new Error('No se pudo aprobar');

                this.adminPendingProfessionals = this.adminPendingProfessionals.filter(p => p.id !== id);
            } catch (error) {
                console.error(error);
                alert('No se pudo aprobar al profesional.');
            }
        },

        async rechazarProfesional(id) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(`/api/admin/profesionales/${id}/rechazar`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (!response.ok) throw new Error('No se pudo rechazar');

                this.adminPendingProfessionals = this.adminPendingProfessionals.filter(p => p.id !== id);
            } catch (error) {
                console.error(error);
                alert('No se pudo rechazar al profesional.');
            }
        },

        get selectedProfessionalSession() {
            return this.consultasHoy.find(s => s.id === this.selectedItem) ?? { client_name: '', packages: [] };
        },

        get selectedClientReservation() {
            return this.proximasSesiones.find(r => r.id === this.selectedItem) ?? { professional_name: '', specialty: '', packages: [] };
        }
    }));
});