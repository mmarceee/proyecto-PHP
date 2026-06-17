document.addEventListener('alpine:init', () => {
    Alpine.data('gestionResenas', () => ({
        cargando: true,
        error: null,
        resenas: [],
        filtroTipo: 'todas',

        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        },

        async cargarResenas() {
            this.cargando = true;
            try {
                const response = await fetch('/api/admin/calificaciones', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('No se pudieron obtener las reseñas.');
                }

                const data = await response.json();
                this.resenas = data.calificaciones ?? [];
            } catch (err) {
                console.error(err);
                this.error = 'Ocurrió un error al cargar las reseñas de la base de datos.';
            } finally {
                this.cargando = false;
            }
        },

        resenasFiltradas() {
            if (this.filtroTipo === 'todas') {
                return this.resenas;
            }
            return this.resenas.filter(r => r.tipo_calificacion === this.filtroTipo);
        },

        async eliminarResena(id) {
            if (!confirm('¿Estás seguro de que quieres eliminar esta reseña? El promedio de reputación del profesional se recalculará automáticamente.')) {
                return;
            }

            try {
                const response = await fetch(`/api/admin/calificaciones/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken()
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('Error al intentar eliminar la reseña.');
                }

                this.resenas = this.resenas.filter(r => r.id !== id);
                alert('Reseña eliminada con éxito.');
            } catch (err) {
                console.error(err);
                alert('No se pudo eliminar la reseña. Inténtalo de nuevo.');
            }
        }
    }));
});