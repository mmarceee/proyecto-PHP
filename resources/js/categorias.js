document.addEventListener('DOMContentLoaded', () => {
    const contenedor = document.querySelector('[data-categorias-admin]');

    if (!contenedor) {
        return;
    }

    const formCategoria = document.getElementById('formCategoria');
    const categoriaId = document.getElementById('categoriaId');
    const nombreCategoria = document.getElementById('nombreCategoria');
    const descripcionCategoria = document.getElementById('descripcionCategoria');

    const tituloFormulario = document.getElementById('tituloFormularioCategoria');
    const btnGuardar = document.getElementById('btnGuardarCategoria');
    const btnCancelarEdicion = document.getElementById('btnCancelarEdicionCategoria');
    const btnRecargar = document.getElementById('btnRecargarCategorias');

    const estadoCarga = document.getElementById('estadoCargaCategorias');
    const contenedorTabla = document.getElementById('contenedorTablaCategorias');
    const tablaCategorias = document.getElementById('tablaCategorias');
    const mensajeCategorias = document.getElementById('mensajeCategorias');

    let categorias = [];

    cargarCategorias();

    formCategoria.addEventListener('submit', guardarCategoria);

    btnCancelarEdicion.addEventListener('click', cancelarEdicion);

    btnRecargar.addEventListener('click', cargarCategorias);

    async function cargarCategorias() {
        mostrarEstadoCarga('Cargando categorías...');
        ocultarMensaje();
        contenedorTabla.classList.add('hidden');
        tablaCategorias.textContent = '';

        try {
            const response = await fetch('/api/admin/categorias', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const data = await response.json();

            if (!response.ok) {
                mostrarEstadoCarga('');
                mostrarMensaje(data.mensaje || 'No se pudieron cargar las categorías.', true);
                return;
            }

            categorias = data.categorias ?? [];

            renderizarCategorias();

        } catch (error) {
            console.error(error);
            mostrarEstadoCarga('');
            mostrarMensaje('Ocurrió un error al cargar las categorías.', true);
        }
    }

    async function guardarCategoria(event) {
        event.preventDefault();

        ocultarMensaje();

        const id = categoriaId.value;

        const datos = {
            nombre: nombreCategoria.value.trim(),
            descripcion: descripcionCategoria.value.trim(),
        };

        if (!datos.nombre) {
            mostrarMensaje('El nombre de la categoría es obligatorio.', true);
            return;
        }

        const url = id
            ? `/api/admin/categorias/${id}`
            : '/api/admin/categorias';

        const metodo = id ? 'PUT' : 'POST';

        try {
            btnGuardar.disabled = true;
            btnGuardar.textContent = id ? 'Guardando cambios...' : 'Guardando...';

            const response = await fetch(url, {
                method: metodo,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify(datos),
            });

            const data = await response.json();

            if (!response.ok) {
                mostrarErroresValidacion(data);
                return;
            }

            mostrarMensaje(data.mensaje || 'Categoría guardada correctamente.');
            limpiarFormulario();
            await cargarCategorias();

        } catch (error) {
            console.error(error);
            mostrarMensaje('Ocurrió un error al guardar la categoría.', true);
        } finally {
            btnGuardar.disabled = false;
            btnGuardar.textContent = categoriaId.value ? 'Guardar cambios' : 'Guardar categoría';
        }
    }

    function renderizarCategorias() {
        tablaCategorias.textContent = '';

        if (categorias.length === 0) {
            contenedorTabla.classList.add('hidden');
            mostrarEstadoCarga('Todavía no hay categorías creadas.');
            return;
        }

        categorias.forEach((categoria) => {
            const fila = document.createElement('tr');

            const celdaNombre = crearCeldaTexto(categoria.nombre, 'font-medium text-gray-900 dark:text-white');

            const descripcion = categoria.descripcion || 'Sin descripción';
            const celdaDescripcion = crearCeldaTexto(descripcion, 'text-gray-600 dark:text-gray-300');

            const celdaEstado = document.createElement('td');
            celdaEstado.className = 'px-4 py-3 text-sm';

            const estado = document.createElement('span');
            estado.textContent = categoria.activa ? 'Activa' : 'Inactiva';
            estado.className = categoria.activa
                ? 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800'
                : 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800';

            celdaEstado.appendChild(estado);

            const celdaAcciones = document.createElement('td');
            celdaAcciones.className = 'px-4 py-3 text-sm';

            const contenedorAcciones = document.createElement('div');
            contenedorAcciones.className = 'flex items-center justify-end gap-3';

            const btnEditar = document.createElement('button');
            btnEditar.type = 'button';
            btnEditar.dataset.requiresOnline = '';
            btnEditar.textContent = 'Editar';
            btnEditar.className = 'px-3 py-1 rounded-md bg-blue-600 text-white hover:bg-blue-700 font-semibold transition';
            btnEditar.addEventListener('click', () => editarCategoria(categoria.id));

            contenedorAcciones.appendChild(btnEditar);

            if (categoria.activa) {
                const btnDesactivar = document.createElement('button');
                btnDesactivar.type = 'button';
                btnDesactivar.dataset.requiresOnline = '';
                btnDesactivar.textContent = 'Desactivar';
                btnDesactivar.className = 'px-3 py-1 rounded-md bg-red-600 text-white hover:bg-red-700 font-semibold transition';
                btnDesactivar.addEventListener('click', () => desactivarCategoria(categoria.id));

                contenedorAcciones.appendChild(btnDesactivar);
            } else {
                const btnActivar = document.createElement('button');
                btnActivar.type = 'button';
                btnActivar.dataset.requiresOnline = '';
                btnActivar.textContent = 'Activar';
                btnActivar.className = 'px-3 py-1 rounded-md bg-green-600 text-white hover:bg-green-700 font-semibold transition';
                btnActivar.addEventListener('click', () => activarCategoria(categoria.id));

                contenedorAcciones.appendChild(btnActivar);
            }

            celdaAcciones.appendChild(contenedorAcciones);

            fila.appendChild(celdaNombre);
            fila.appendChild(celdaDescripcion);
            fila.appendChild(celdaEstado);
            fila.appendChild(celdaAcciones);


            tablaCategorias.appendChild(fila);
        });

        mostrarEstadoCarga('');
        contenedorTabla.classList.remove('hidden');
        window.dispatchEvent(new Event('offline-status:refresh'));
    }

    function crearCeldaTexto(texto, clasesExtra = '') {
        const celda = document.createElement('td');
        celda.className = `px-4 py-3 text-sm ${clasesExtra}`;

        celda.textContent = texto;

        return celda;
    }

    function editarCategoria(id) {
        const categoria = categorias.find((item) => item.id === id);

        if (!categoria) {
            mostrarMensaje('No se encontró la categoría seleccionada.', true);
            return;
        }

        categoriaId.value = categoria.id;
        nombreCategoria.value = categoria.nombre;
        descripcionCategoria.value = categoria.descripcion || '';

        tituloFormulario.textContent = 'Editar categoría';
        btnGuardar.textContent = 'Guardar cambios';
        btnCancelarEdicion.classList.remove('hidden');

        nombreCategoria.focus();
    }

    function cancelarEdicion() {
        limpiarFormulario();
        ocultarMensaje();
    }

    function limpiarFormulario() {
        categoriaId.value = '';
        formCategoria.reset();

        tituloFormulario.textContent = 'Nueva categoría';
        btnGuardar.textContent = 'Guardar categoría';
        btnCancelarEdicion.classList.add('hidden');
    }

    async function desactivarCategoria(id) {
        const confirmar = confirm('¿Seguro que querés desactivar esta categoría?');

        if (!confirmar) {
            return;
        }

        await cambiarEstadoCategoria(id, 'desactivar');
    }

    async function activarCategoria(id) {
        await cambiarEstadoCategoria(id, 'activar');
    }

    async function cambiarEstadoCategoria(id, accion) {
        ocultarMensaje();

        try {
            const response = await fetch(`/api/admin/categorias/${id}/${accion}`, {
                method: 'PATCH',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const data = await response.json();

            if (!response.ok) {
                mostrarMensaje(data.mensaje || 'No se pudo cambiar el estado de la categoría.', true);
                return;
            }

            mostrarMensaje(data.mensaje || 'Estado actualizado correctamente.');
            await cargarCategorias();

        } catch (error) {
            console.error(error);
            mostrarMensaje('Ocurrió un error al cambiar el estado de la categoría.', true);
        }
    }

    function mostrarErroresValidacion(data) {
        if (data.errors) {
            const primerCampo = Object.keys(data.errors)[0];

            if (primerCampo) {
                mostrarMensaje(data.errors[primerCampo][0], true);
                return;
            }
        }

        mostrarMensaje(data.mensaje || data.message || 'No se pudo guardar la categoría.', true);
    }

    function mostrarEstadoCarga(texto) {
        estadoCarga.textContent = texto;

        if (texto) {
            estadoCarga.classList.remove('hidden');
        } else {
            estadoCarga.classList.add('hidden');
        }
    }

    function mostrarMensaje(texto, esError = false) {
        mensajeCategorias.textContent = texto;
        mensajeCategorias.classList.remove('hidden');

        if (esError) {
            mensajeCategorias.className = 'mb-4 rounded-lg px-4 py-3 text-sm bg-red-100 text-red-700';
        } else {
            mensajeCategorias.className = 'mb-4 rounded-lg px-4 py-3 text-sm bg-green-100 text-green-700';
        }
    }

    function ocultarMensaje() {
        mensajeCategorias.textContent = '';
        mensajeCategorias.classList.add('hidden');
    }
});
