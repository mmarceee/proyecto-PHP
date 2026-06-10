document.addEventListener('alpine:init', () => {
    Alpine.data('dashboardData', () => ({
        cargando: true,
        usuario: { nombre: '' },
        saludo: '',
        tipo: '',
        profesional: { tieneSolicitud: false, estado: '', pendiente: false, aprobado: false },
        adminPendingProfessionals: [],
        consultasHoy: [],
        reservasPendientes: [],
        proximasSesiones: [],
        selectedItem: null,
        showCancelModal: false,
        reservaACancelar: null,
        motivoCancelacion: '',
        showReprogramarModal: false,
        reservaAReprogramar: null,
        fechaReprogramacion: new Date().toISOString().split('T')[0],
        semanaReprogramacion: [],
        cargandoAgenda: false,

        async init() {
            await this.cargarDashboard();
            
            //  1. Iniciar la escucha de WebSockets apenas arranca el dashboard
            this.iniciarEscuchaRealtime();
        },

        async cargarDashboard() {
            this.cargando = true;
                try {
                    const response = await fetch('/api/dashboard', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    const contentType = response.headers.get('content-type');

                    if (!response.ok) {
                        const text = await response.text();
                        console.error('Error HTTP cargando dashboard:', response.status, text);
                        return;
                    }

                    if (!contentType || !contentType.includes('application/json')) {
                        const text = await response.text();
                        console.error('La respuesta NO es JSON. Laravel devolvió esto:', text);
                        return;
                    }

                    const data = await response.json();

                    console.log('Datos recibidos del dashboard:', data);

                    this.tipo = data.tipo ?? null;
                    this.saludo = data.saludo ?? '';
                    this.usuario = data.usuario ?? {
                        id: null,
                        nombre: '',
                        email: ''
                    };
                    this.profesional = data.profesional ?? {
                        tieneSolicitud: false,
                        estado: null,
                        pendiente: false,
                        aprobado: false
                    };
                    this.adminPendingProfessionals = data.datos?.profesionalesPendientes ?? [];
                    this.consultasHoy = data.datos?.consultasHoy ?? [];
                    this.reservasPendientes = data.datos?.reservasPendientes ?? [];
                    this.proximasSesiones = data.datos?.proximasSesiones ?? [];

                    if (this.tipo === 'profesional' && this.consultasHoy.length > 0) {
                        this.selectedItem = this.consultasHoy[0].id;
                    } else if (this.tipo === 'cliente' && this.proximasSesiones.length > 0) {
                        this.selectedItem = this.proximasSesiones[0].id;
                    } else {
                        this.selectedItem = null;
                    }

                } catch (error) {
                        console.error('Error cargando dashboard:', error);
                } finally {
                        this.cargando = false;
                }
        },

        esHoraDeSala(dateRaw, time) {
            if (!dateRaw || !time) return false;
            
            // Reconstruimos la fecha/hora de la sesión
            const [hours, minutes] = time.split(':');
            const sessionDate = new Date(`${dateRaw}T${hours}:${minutes}:00`);
            
            // Permitir entrar a la sala 5 minutos antes
            const allowedTime = new Date(sessionDate.getTime() - 5 * 60000);
            return new Date() >= allowedTime;
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
            // Validamos que el profesional haya escrito un motivo
            if (this.motivoCancelacion.trim() === '') {
                alert('El motivo de cancelación es obligatorio.');
                return; 
            }

            try {
                // Golpeamos tu endpoint DELETE en la API
                const response = await fetch(`/api/reservas/${this.reservaACancelar}`, {
                    method: 'DELETE',
                    credentials: 'same-origin',
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
                    await this.cargarDashboard();
                } else {
                    const error = await response.json();
                    alert('Error al cancelar: ' + (error.message || 'Intenta nuevamente'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Hubo un problema al conectar con el servidor.');
            }
        },

        async avanzarEstadoReserva(id) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(`/api/reservas/${id}/avanzar-estado`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin'
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.error || 'No se pudo actualizar el estado de la reserva');

                // REFRESCO INMEDIATO: Volvemos a pedir los datos a la API para que pinte el nuevo estado y cambie el botón al vuelo
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
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin'
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
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) throw new Error('No se pudo rechazar');

                this.adminPendingProfessionals = this.adminPendingProfessionals.filter(p => p.id !== id);
            } catch (error) {
                console.error(error);
                alert('No se pudo rechazar al profesional.');
            }
        },

        
        //FUNCIÓN CORREGIDA Y COMPLETA
        iniciarEscuchaRealtime() {
            if (!window.Echo) return;

            // --- 1. SI ES CLIENTE: Escucha cambios de estado ---
            if (this.tipo === 'cliente') {
                const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
                if (userId) {
                    console.log(`[WS CLIENTE] Sintonizando radio: 'usuario.${userId}'`);
                    window.Echo.private(`usuario.${userId}`)
                        .listen('.reserva.estado.cambiado', async (evento) => {
                            console.log(" [WS CLIENTE] ¡El profesional cambió el estado!", evento);
                            await this.cargarDashboard(); 
                        });
                }
            }

            // --- 2. SI ES PROFESIONAL: Escucha nuevas reservas (¡Lo que faltaba!) ---
            if (this.tipo === 'profesional') {
                const profesionalId = document.querySelector('meta[name="profesional-id"]')?.getAttribute('content');
                if (profesionalId) {
                    console.log(`[WS PROFESIONAL] Sintonizando radio de agenda: 'profesional.${profesionalId}'`);
                    window.Echo.private(`profesional.${profesionalId}`)
                        .listen('.agenda.modificada', async (evento) => {
                            console.log(" [WS PROFESIONAL] ¡Te cayó una nueva reserva u otra modificación de agenda!", evento);
                            await this.cargarDashboard(); 
                        })
                        .listen('.dashboard.actualizado', async (evento) => {
                            console.log(" [WS PROFESIONAL] ¡Un cliente reprogramó una reserva, actualizando dashboard!", evento);
                            await this.cargarDashboard(); 
                        });
                }
            }
        },

        get selectedProfessionalSession() {
            return this.consultasHoy.find(s => s.id === this.selectedItem) ?? { client_name: '', packages: [] };
        },

        get selectedClientReservation() {
            return this.proximasSesiones.find(r => r.id === this.selectedItem) ?? { professional_name: '', specialty: '', packages: [] };
        },
        
        showReprogramarModal: false,
        reservaAReprogramar: null,
        fechaReprogramacion: new Date().toISOString().split('T')[0],
        semanaReprogramacion: [],
        cargandoAgenda: false,
        showConfirmarReprogramacionModal: false,
        showExitoReprogramacionModal: false,
        showErrorReprogramacionModal: false,
        errorReprogramacionMensaje: '',
        fechaSeleccionadaConfirmacion: '',
        horaSeleccionadaConfirmacion: '',

        formatDate(dateStr) {
            if (!dateStr) return '';
            const [y, m, d] = dateStr.split('-');
            return `${d}-${m}-${y}`;
        },

        abrirModalReprogramar(reserva) {
            this.reservaAReprogramar = reserva;
            this.fechaReprogramacion = new Date().toISOString().split('T')[0];
            this.showReprogramarModal = true;
            this.cargarAgendaReprogramacion();
        },
        cerrarModalReprogramar() {
            this.showReprogramarModal = false;
            this.reservaAReprogramar = null;
        },
        async cargarAgendaReprogramacion() {
            if (!this.reservaAReprogramar) return;
            this.cargandoAgenda = true;
            try {
                const profId = this.reservaAReprogramar.professional_id;
                const res = await fetch(`/api/servicios/profesionales/${profId}/agenda?fecha=${this.fechaReprogramacion}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                });
                if (res.ok) {
                    const data = await res.json();
                    this.semanaReprogramacion = data.semana;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.cargandoAgenda = false;
            }
        },
        async avanzarSemanaReprogramacion() {
            let fecha = new Date(this.fechaReprogramacion + 'T00:00:00');
            fecha.setDate(fecha.getDate() + 7);
            this.fechaReprogramacion = fecha.toISOString().split('T')[0];
            await this.cargarAgendaReprogramacion();
        },
        async retrocederSemanaReprogramacion() {
            let fecha = new Date(this.fechaReprogramacion + 'T00:00:00');
            fecha.setDate(fecha.getDate() - 7);
            this.fechaReprogramacion = fecha.toISOString().split('T')[0];
            await this.cargarAgendaReprogramacion();
        },
        confirmarReprogramacion(fecha, hora) {
            this.fechaSeleccionadaConfirmacion = fecha;
            this.horaSeleccionadaConfirmacion = hora;
            this.showConfirmarReprogramacionModal = true;
        },

        async ejecutarReprogramacion() {
            try {
                const res = await fetch(`/api/reservas/${this.reservaAReprogramar.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        fecha: this.fechaSeleccionadaConfirmacion,
                        hora_inicio: this.horaSeleccionadaConfirmacion,
                        servicio_id: this.reservaAReprogramar.servicio_id
                    })
                });
                if (res.ok) {
                    this.showConfirmarReprogramacionModal = false;
                    this.cerrarModalReprogramar();
                    await this.cargarDashboard();
                    this.showExitoReprogramacionModal = true;
                } else {
                    const data = await res.json();
                    this.errorReprogramacionMensaje = "No se pudo reprogramar: " + (data.error || data.message || "Es posible que la política de cancelación no lo permita.");
                    this.showErrorReprogramacionModal = true;
                    this.showConfirmarReprogramacionModal = false;
                }
            } catch (e) {
                console.error(e);
            }
        },


    }));
});

