document.addEventListener('alpine:init', () => {
    
    // ==========================================
    // COMPONENTE 1: Actualizar Información
    // ==========================================
    Alpine.data('perfilForm', (nombreInicial, emailInicial) => ({
        cargando: false,
        name: nombreInicial,
        email: emailInicial,
        mensaje: '',
        error: '',

        async guardarPerfil() {
            this.cargando = true;
            this.mensaje = '';
            this.error = '';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch('/api/profile/info', {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ name: this.name, email: this.email })
                });

                if (!response.ok) throw new Error('Error al actualizar perfil');
                
                const data = await response.json();
                this.mensaje = data.mensaje;
                setTimeout(() => { this.mensaje = ''; }, 3000);
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