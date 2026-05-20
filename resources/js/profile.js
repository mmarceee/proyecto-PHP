document.addEventListener('alpine:init', () => {
    
    // ==========================================
    // COMPONENTE 1: Actualizar Información
    // ==========================================
    Alpine.data('perfilForm', (datosIniciales) => ({
        cargando: false,

        name: datosIniciales.name || '',
        apellido: datosIniciales.apellido || '',
        telefono: datosIniciales.telefono || '',
        email: datosIniciales.email || '',
        descripcion: datosIniciales.descripcion || '',
        nombre_comercial: datosIniciales.nombre_comercial || '',

        esProfesionalAprobado: datosIniciales.esProfesionalAprobado || false,

        mensaje: '',
        error: '',
        aviso: '',

        original: {
            name: datosIniciales.name || '',
            apellido: datosIniciales.apellido || '',
            telefono: datosIniciales.telefono || '',
            descripcion: datosIniciales.descripcion || '',
            nombre_comercial: datosIniciales.nombre_comercial || '',
        },

        hayCambios() {
            const limpiar = (valor) => (valor ?? '').trim();

            if (limpiar(this.name) !== limpiar(this.original.name)) return true;
            if (limpiar(this.apellido) !== limpiar(this.original.apellido)) return true;
            if (limpiar(this.telefono) !== limpiar(this.original.telefono)) return true;

            if (this.esProfesionalAprobado) {
                if (limpiar(this.descripcion) !== limpiar(this.original.descripcion)) return true;
                if (limpiar(this.nombre_comercial) !== limpiar(this.original.nombre_comercial)) return true;
            }

            return false;
        },

       async guardarPerfil() {
            this.cargando = true;
            this.mensaje = '';
            this.aviso = '';
            this.error = '';

            if (!this.hayCambios()) {
                this.aviso = 'No había cambios para guardar.';
                this.cargando = false;

                setTimeout(() => {
                    this.aviso = '';
                }, 3000);

                return;
            }

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const body = {
                    name: this.name,
                    apellido: this.apellido,
                    telefono: this.telefono,
                };

                if (this.esProfesionalAprobado) {
                    body.descripcion = this.descripcion;
                    body.nombre_comercial = this.nombre_comercial;
                }

                const response = await fetch('/api/profile/info', {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(body)
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.error = Object.values(data.errors).flat().join(' ');
                        return;
                    }

                    throw new Error(data.mensaje || 'Error al actualizar perfil');
                }

                this.mensaje = data.mensaje;

                this.original = {
                    name: this.name,
                    apellido: this.apellido,
                    telefono: this.telefono,
                    direccion: this.direccion,
                    descripcion: this.descripcion,
                    nombre_comercial: this.nombre_comercial,
                };

                setTimeout(() => {
                    this.mensaje = '';
                }, 3000);

            } catch (error) {
                console.error(error);
                this.error = 'Ocurrió un error al actualizar el perfil.';
            } finally {
                this.cargando = false;
            }
        }
    }));

    // ==========================================
    // COMPONENTE 2: Cambiar Contraseña
    // ==========================================
    Alpine.data('passwordForm', () => ({
        cargando: false,
        current_password: '',
        password: '',
        password_confirmation: '',
        mensaje: '',
        errores: {},

        async guardarPassword() {
            this.cargando = true;
            this.mensaje = '';
            this.errores = {};

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch('/api/profile/password', {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        current_password: this.current_password,
                        password: this.password,
                        password_confirmation: this.password_confirmation
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    if (response.status === 422) {
                        this.errores = data.errors;
                        return;
                    }
                    throw new Error('Error al actualizar contraseña.');
                }

                this.mensaje = data.mensaje;
                this.current_password = '';
                this.password = '';
                this.password_confirmation = '';
                setTimeout(() => { this.mensaje = ''; }, 3000);

            } catch (error) {
                console.error(error);
                this.errores = { general: ['Ocurrió un error inesperado.'] };
            } finally {
                this.cargando = false;
            }
        }
    }));

    // ==========================================
    // COMPONENTE 3: Eliminar Cuenta
    // ==========================================
    Alpine.data('deleteAccountForm', () => ({
        cargando: false,
        password: '',
        errores: {},

        async borrarCuenta() {
            this.cargando = true;
            this.errores = {};

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch('/api/profile', {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ password: this.password })
                });

                if (!response.ok) {
                    const data = await response.json();
                    if (response.status === 422) {
                        this.errores = data.errors;
                        return;
                    }
                    throw new Error('Error al eliminar cuenta.');
                }

                // Si se borró bien, lo mandamos al inicio
                window.location.href = '/';

            } catch (error) {
                console.error(error);
                this.errores = { password: ['Ocurrió un error al intentar eliminar la cuenta.'] };
            } finally {
                this.cargando = false;
            }
        }
    }));
});