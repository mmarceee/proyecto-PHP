document.addEventListener('alpine:init', () => {
    Alpine.data('gestionUsuarios', (usuariosUrl, usuariosBaseUrl) => ({
        cargando: true,
        error: null,
        usuarios: [],

        csrfToken() {
            return document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content');
        },

        async cargarUsuarios() {
            try {
                const response = await fetch(usuariosUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    const text = await response.text();
                    console.error('Error cargando usuarios:', response.status, text);
                    this.error = 'No se pudieron cargar los usuarios.';
                    return;
                }

                const data = await response.json();

                this.usuarios = data.usuarios ?? [];

            } catch (error) {
                console.error('Error cargando usuarios:', error);
                this.error = 'Ocurrió un error al cargar los usuarios.';
            } finally {
                this.cargando = false;
            }
        },

        async bloquearUsuario(id) {
            await this.actualizarUsuario(`${usuariosBaseUrl}/${id}/bloquear`, 'bloqueado');
        },

        async desbloquearUsuario(id) {
            await this.actualizarUsuario(`${usuariosBaseUrl}/${id}/desbloquear`, 'activo');
        },

        async hacerAdmin(id) {
            try {
                const response = await fetch(`${usuariosBaseUrl}/${id}/hacer-admin`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken()
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    const text = await response.text();
                    console.error('Error haciendo admin:', response.status, text);
                    alert('No se pudo convertir el usuario en administrador.');
                    return;
                }

                this.usuarios = this.usuarios.map(usuario => {
                    if (usuario.id === id) {
                        return {
                            ...usuario,
                            es_admin: true
                        };
                    }

                    return usuario;
                });

            } catch (error) {
                console.error('Error haciendo admin:', error);
                alert('Ocurrió un error al convertir el usuario en administrador.');
            }
        },

        async actualizarUsuario(url, nuevoEstado) {
            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken()
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    const text = await response.text();
                    console.error('Error actualizando usuario:', response.status, text);
                    alert('No se pudo actualizar el usuario.');
                    return;
                }

                this.usuarios = this.usuarios.map(usuario => {
                    if (usuario.id === this.obtenerIdDesdeUrl(url)) {
                        return {
                            ...usuario,
                            estado_usuario: nuevoEstado
                        };
                    }

                    return usuario;
                });

            } catch (error) {
                console.error('Error actualizando usuario:', error);
                alert('Ocurrió un error al actualizar el usuario.');
            }
        },

        obtenerIdDesdeUrl(url) {
            const partes = url.split('/');
            return Number(partes[partes.length - 2]);
        }
    }));
});