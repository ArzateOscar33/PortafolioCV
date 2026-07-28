(() => {
    'use strict';

    const module = document.querySelector('#clientsModule');

    if (!module) {
        return;
    }

    const $ = (selector) => module.querySelector(selector);
    const form = $('#clientForm');
    const tableBody = $('#clientsTableBody');
    const clientModal = new bootstrap.Modal($('#clientModal'));
    const actionModal = new bootstrap.Modal($('#clientActionModal'));
    const toast = new bootstrap.Toast($('#clientNotification'));
    const csrfToken = form.querySelector('[name="csrf_token"]').value;

    let pendingAction = null;
    let searchTimer = null;

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function typeLabel(type) {
        const labels = {
            empresa: 'Empresa',
            persona: 'Persona',
            organizacion: 'Organización',
        };

        return labels[type] || type || 'Sin tipo';
    }

    function formatDate(value) {
        if (!value) {
            return 'Sin registro';
        }

        const normalized = String(value).replace(' ', 'T');
        const date = new Date(normalized);

        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return new Intl.DateTimeFormat('es-MX', {
            dateStyle: 'medium',
            timeStyle: 'short',
        }).format(date);
    }

    async function request(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            },
        });

        const text = await response.text();
        let data = {};

        try {
            data = text ? JSON.parse(text) : {};
        } catch (error) {
            throw new Error('El servidor devolvió una respuesta no válida.');
        }

        if (!response.ok || data.status === false) {
            throw new Error(
                data.msg || 'No fue posible completar la operación.'
            );
        }

        return data;
    }

    function notify(message, success = true) {
        $('#clientNotificationMessage').textContent = message;
        $('#clientNotificationTitle').textContent = success
            ? 'Operación completada'
            : 'Operación no completada';

        const icon = $('#clientNotificationIcon');
        icon.className = success
            ? 'fa-solid fa-circle-check text-success me-2'
            : 'fa-solid fa-circle-exclamation text-danger me-2';

        toast.show();
    }

    function setLoading() {
        tableBody.innerHTML = `
            <tr>
                <td class="text-center py-5" colspan="7">
                    <i class="fa-solid fa-circle-notch fa-spin me-2"></i>
                    Cargando clientes...
                </td>
            </tr>
        `;
    }

    function renderClients(clients) {
        const emptyState = $('#clientsEmptyState');

        if (!clients.length) {
            tableBody.innerHTML = '';
            emptyState.hidden = false;
            return;
        }

        emptyState.hidden = true;

        tableBody.innerHTML = clients.map((client) => {
            const deleted = Boolean(client.eliminado_en);
            const active = Number(client.activo) === 1;
            const website = client.sitio_web
                ? `
                    <a
                        class="btn btn-sm btn-outline-secondary"
                        href="${escapeHtml(client.sitio_web)}"
                        target="_blank"
                        rel="noopener noreferrer"
                        title="Abrir sitio web">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                `
                : '';

            const stateBadge = deleted
                ? '<span class="badge text-bg-danger">Baja</span>'
                : active
                    ? '<span class="badge text-bg-success">Activo</span>'
                    : '<span class="badge text-bg-secondary">Inactivo</span>';

            const stateButton = deleted
                ? ''
                : `
                    <button
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        data-action="state"
                        data-id="${Number(client.id_cliente)}"
                        data-name="${escapeHtml(client.nombre_mostrar)}"
                        data-active="${active ? '0' : '1'}"
                        title="${active ? 'Desactivar' : 'Activar'}">
                        <i class="fa-solid ${active ? 'fa-pause' : 'fa-play'}"></i>
                    </button>
                `;

            const recordButton = deleted
                ? `
                    <button
                        class="btn btn-sm btn-outline-success"
                        type="button"
                        data-action="restore"
                        data-id="${Number(client.id_cliente)}"
                        data-name="${escapeHtml(client.nombre_mostrar)}"
                        title="Restaurar">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                `
                : `
                    <button
                        class="btn btn-sm btn-outline-danger"
                        type="button"
                        data-action="delete"
                        data-id="${Number(client.id_cliente)}"
                        data-name="${escapeHtml(client.nombre_mostrar)}"
                        data-projects="${Number(client.total_proyectos || 0)}"
                        title="Dar de baja">
                        <i class="fa-solid fa-box-archive"></i>
                    </button>
                `;

            const editButton = deleted
                ? ''
                : `
                    <button
                        class="btn btn-sm btn-outline-primary"
                        type="button"
                        data-action="edit"
                        data-id="${Number(client.id_cliente)}"
                        title="Editar">
                        <i class="fa-solid fa-pen"></i>
                    </button>
                `;

            const contactLines = [
                client.correo
                    ? `<div><i class="fa-solid fa-envelope me-1"></i>${escapeHtml(client.correo)}</div>`
                    : '',
                client.telefono
                    ? `<div><i class="fa-solid fa-phone me-1"></i>${escapeHtml(client.telefono)}</div>`
                    : '',
            ].filter(Boolean).join('');

            return `
                <tr class="${deleted ? 'opacity-75' : ''}">
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <span class="d-inline-flex align-items-center justify-content-center fs-4">
                                <i class="fa-solid ${client.tipo_cliente === 'persona' ? 'fa-user' : 'fa-building'}"></i>
                            </span>
                            <div>
                                <strong>${escapeHtml(client.nombre_mostrar)}</strong>
                                <div class="small text-body-secondary">
                                    ${escapeHtml(client.razon_social || 'Sin razón social')}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge text-bg-dark">
                            ${escapeHtml(typeLabel(client.tipo_cliente))}
                        </span>
                    </td>
                    <td class="small">
                        ${contactLines || '<span class="text-body-secondary">Sin datos</span>'}
                    </td>
                    <td>
                        <strong>${Number(client.total_proyectos || 0)}</strong>
                        <span class="text-body-secondary small">proyectos</span>
                    </td>
                    <td>${stateBadge}</td>
                    <td class="small text-body-secondary">
                        ${escapeHtml(formatDate(client.actualizado_en))}
                    </td>
                    <td>
                        <div class="d-flex justify-content-end gap-1 flex-wrap">
                            ${website}
                            ${editButton}
                            ${stateButton}
                            ${recordButton}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function loadClients() {
        setLoading();

        const params = new URLSearchParams({
            busqueda: $('#clientSearch').value.trim(),
            tipo: $('#clientTypeFilter').value,
            estado: $('#clientStateFilter').value,
            registro: $('#clientRecordFilter').value,
        });

        try {
            const data = await request(
                `${module.dataset.listUrl}?${params.toString()}`
            );

            renderClients(data.clientes || []);
            $('#totalClients').textContent = data.resumen?.total ?? 0;
            $('#activeClients').textContent = data.resumen?.activos ?? 0;
            $('#inactiveClients').textContent = data.resumen?.inactivos ?? 0;
            $('#deletedClients').textContent = data.resumen?.bajas ?? 0;
        } catch (error) {
            tableBody.innerHTML = `
                <tr>
                    <td class="text-center py-5 text-danger" colspan="7">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        ${escapeHtml(error.message)}
                    </td>
                </tr>
            `;
            notify(error.message, false);
        }
    }

    function updateNotesCounter() {
        $('#clientNotesCounter').textContent =
            `${$('#clientNotes').value.length} / 5000`;
    }

    function resetForm() {
        form.reset();
        form.classList.remove('was-validated');
        $('#clientId').value = '0';
        $('#clientType').value = 'empresa';
        $('#clientActive').checked = true;
        $('#clientModalTitle').textContent = 'Nuevo cliente';
        $('#btnSaveClient span').textContent = 'Guardar cliente';
        $('#clientFormFeedback').classList.add('d-none');
        $('#clientFormFeedback').textContent = '';
        updateNotesCounter();
    }

    function openNewClient() {
        resetForm();
        clientModal.show();
        setTimeout(() => $('#clientName').focus(), 180);
    }

    async function editClient(id) {
        try {
            const client = await request(
                `${module.dataset.getUrl}/${encodeURIComponent(id)}`
            );

            resetForm();
            $('#clientId').value = client.id_cliente || 0;
            $('#clientType').value = client.tipo_cliente || 'empresa';
            $('#clientName').value = client.nombre_mostrar || '';
            $('#clientLegalName').value = client.razon_social || '';
            $('#clientEmail').value = client.correo || '';
            $('#clientPhone').value = client.telefono || '';
            $('#clientWebsite').value = client.sitio_web || '';
            $('#clientLogo').value = client.ruta_logotipo || '';
            $('#clientNotes').value = client.notas || '';
            $('#clientActive').checked = Number(client.activo) === 1;
            $('#clientModalTitle').textContent = 'Editar cliente';
            $('#btnSaveClient span').textContent = 'Guardar cambios';
            updateNotesCounter();
            clientModal.show();
        } catch (error) {
            notify(error.message, false);
        }
    }

    function showFormError(message) {
        const feedback = $('#clientFormFeedback');
        feedback.textContent = message;
        feedback.classList.remove('d-none');
    }

    async function saveClient(event) {
        event.preventDefault();
        form.classList.add('was-validated');

        if (!form.checkValidity()) {
            return;
        }

        const button = $('#btnSaveClient');
        const original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = `
            <i class="fa-solid fa-circle-notch fa-spin"></i>
            <span>Guardando...</span>
        `;

        try {
            const data = await request(module.dataset.saveUrl, {
                method: 'POST',
                body: new FormData(form),
            });

            clientModal.hide();
            notify(data.msg || 'Cliente guardado correctamente.');
            await loadClients();
        } catch (error) {
            showFormError(error.message);
        } finally {
            button.disabled = false;
            button.innerHTML = original;
        }
    }

    function prepareAction(button) {
        const id = Number(button.dataset.id || 0);
        const name = button.dataset.name || 'este cliente';
        const action = button.dataset.action;

        if (action === 'state') {
            const active = button.dataset.active === '1';
            pendingAction = {
                endpoint: module.dataset.stateUrl,
                fields: {
                    id_cliente: id,
                    activo: active ? '1' : '0',
                },
            };

            $('#clientActionTitle').textContent = active
                ? 'Activar cliente'
                : 'Desactivar cliente';
            $('#clientActionMessage').textContent = active
                ? `¿Deseas activar a ${name}? Volverá a aparecer en el formulario de Proyectos.`
                : `¿Deseas desactivar a ${name}? Los proyectos existentes conservarán la relación.`;
        } else if (action === 'delete') {
            const projects = Number(button.dataset.projects || 0);
            pendingAction = {
                endpoint: module.dataset.recordUrl,
                fields: {
                    id_cliente: id,
                    accion: 'baja',
                },
            };

            $('#clientActionTitle').textContent = 'Dar de baja al cliente';
            $('#clientActionMessage').textContent = projects > 0
                ? `${name} tiene ${projects} proyecto(s) vinculado(s). La baja será lógica y esas relaciones se conservarán.`
                : `¿Deseas dar de baja a ${name}? El registro podrá restaurarse posteriormente.`;
        } else if (action === 'restore') {
            pendingAction = {
                endpoint: module.dataset.recordUrl,
                fields: {
                    id_cliente: id,
                    accion: 'restaurar',
                },
            };

            $('#clientActionTitle').textContent = 'Restaurar cliente';
            $('#clientActionMessage').textContent =
                `¿Deseas restaurar a ${name}? Quedará activo y disponible para nuevos proyectos.`;
        } else {
            return;
        }

        actionModal.show();
    }

    async function confirmAction() {
        if (!pendingAction) {
            return;
        }

        const button = $('#btnConfirmClientAction');
        const original = button.innerHTML;
        const formData = new FormData();

        Object.entries(pendingAction.fields).forEach(([key, value]) => {
            formData.append(key, value);
        });
        formData.append('csrf_token', csrfToken);

        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Procesando';

        try {
            const data = await request(pendingAction.endpoint, {
                method: 'POST',
                body: formData,
            });

            actionModal.hide();
            notify(data.msg || 'Operación completada.');
            await loadClients();
        } catch (error) {
            notify(error.message, false);
        } finally {
            button.disabled = false;
            button.innerHTML = original;
            pendingAction = null;
        }
    }

    $('#btnNewClient').addEventListener('click', openNewClient);
    $('#btnRefreshClients').addEventListener('click', loadClients);
    $('#btnConfirmClientAction').addEventListener('click', confirmAction);
    $('#clientNotes').addEventListener('input', updateNotesCounter);
    form.addEventListener('submit', saveClient);

    tableBody.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action]');

        if (!button) {
            return;
        }

        if (button.dataset.action === 'edit') {
            editClient(button.dataset.id);
            return;
        }

        prepareAction(button);
    });

    $('#clientSearch').addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadClients, 350);
    });

    [
        '#clientTypeFilter',
        '#clientStateFilter',
        '#clientRecordFilter',
    ].forEach((selector) => {
        $(selector).addEventListener('change', loadClients);
    });

    loadClients();
})();
