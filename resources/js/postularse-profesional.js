document.addEventListener('alpine:init', () => {
    Alpine.data('formularioPostulacion', () => ({
        formulario: {
            especialidad: '',
            descripcion: '',
            nombre_comercial: ''
        },
        errores: {},
        mensajeExito: '',
        mensajeError: '',
        cargando: false,
        redireccionando: false,

        // Evitamos que entre si ya tiene solicitud
        async verificarEstado() {
            const response = await fetch('/api/dashboard', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            const data = await response.json();
            
            if (data.profesional.tieneSolicitud) {
                this.redireccionando = true;
                window.location.href = '/dashboard';
            }
        },

        async enviarPostulacion() {
            this.cargando = true;
            this.errores = {};
            this.mensajeError = '';
            this.mensajeExito = '';

            try {
                // Buscamos el token CSRF que Laravel inyecta en el HTML
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                const response = await fetch('/api/profesionales/postularse', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(this.formulario)
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        this.errores = data.errors; // Errores de validación
                    } else {
                        throw new Error(data.message || 'Error al enviar la solicitud.');
                    }
                    return;
                }

                this.mensajeExito = 'Tu solicitud ha sido enviada. Un administrador la revisará pronto.';
                
                // Redirigir al dashboard después de 2 segundos
                setTimeout(() => {
                    window.location.href = '/dashboard';
                }, 2000);

            } catch (error) {
                console.error(error);
                this.mensajeError = error.message;
            } finally {
                this.cargando = false;
            }
        }
    }));
});