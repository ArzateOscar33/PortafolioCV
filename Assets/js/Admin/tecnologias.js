(() => {
    'use strict';

    const module = document.getElementById('technologiesModule');

    if (!module) {
        return;
    }

    const $ = (selector) => module.querySelector(selector);
    const tableBody = $('#technologiesTableBody');
    const emptyState = $('#technologiesEmptyState');
    const form = $('#technologyForm');
    const modal = bootstrap.Modal.getOrCreateInstance($('#technologyModal'));
    const statusModal = bootstrap.Modal.getOrCreateInstance($('#technologyStatusModal'));
    const toast = bootstrap.Toast.getOrCreateInstance($('#technologyNotification'));
    const csrfToken = form.querySelector('[name="csrf_token"]').value;

    let pendingStatus = null;
    let searchTimer = null;

    const escapeHtml = (value = '') => String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const slugify = (value) => String(value)
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

    async function request(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...(options.headers || {}),
            },
        });

        const data = await response.json().catch(() => ({
            status: false,
            msg: 'El servidor devolvió una respuesta no válida.',
        }));

        if (!response.ok || data.status === false) {
            throw new Error(data.msg || 'No fue posible completar la operación.');
        }

        return data;
    }

    function notify(message, type = 'success') {
        const icon = $('#technologyNotificationIcon');
        const classes = {
            success: 'fa-circle-check text-success',
            error: 'fa-circle-xmark text-danger',
            warning: 'fa-triangle-exclamation text-warning',
        };

        icon.className = `fa-solid me-2 ${classes[type] || classes.success}`;
        $('#technologyNotificationMessage').textContent = message;
        toast.show();
    }

    function iconMarkup(technology) {
        if (technology.tipo_icono === 'imagen') {
            return `<img src="${escapeHtml(technology.valor_icono)}" alt="" width="24" height="24" style="object-fit:contain">`;
        }

        return `<i class="${escapeHtml(technology.valor_icono)}"></i>`;
    }

    function renderRows(technologies) {
        emptyState.hidden = technologies.length > 0;

        if (!technologies.length) {
            tableBody.innerHTML = '';
            return;
        }

        tableBody.innerHTML = technologies.map((technology) => {
            const active = Number(technology.activa) === 1;
            const area = technology.nivel_o_area
                ? `<small class="text-body-secondary d-block">${escapeHtml(technology.nivel_o_area)}</small>`
                : '';

            return `
                <tr>
                    <td>${Number(technology.orden_visualizacion) || 0}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded"
                                  style="width:42px;height:42px;color:${escapeHtml(technology.color_hexadecimal)};background:${escapeHtml(technology.color_hexadecimal)}18;border:1px solid ${escapeHtml(technology.color_hexadecimal)}66">
                                ${iconMarkup(technology)}
                            </span>
                            <div>
                                <strong>${escapeHtml(technology.nombre)}</strong>
                                ${area}
                            </div>
                        </div>
                    </td>
                    <td><code>${escapeHtml(technology.slug)}</code></td>
                    <td>${technology.tipo_icono === 'imagen' ? 'Imagen' : 'Font Awesome'}</td>
                    <td>
                        <span class="badge ${active ? 'text-bg-success' : 'text-bg-secondary'}">
                            ${active ? 'Activa' : 'Inactiva'}
                        </span>
                    </td>
                    <td>${escapeHtml(technology.actualizada_en || '')}</td>
                    <td class="text-end text-nowrap">
                        <button class="btn btn-sm btn-outline-info" type="button" data-action="edit" data-id="${technology.id_tecnologia}" title="Editar">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn btn-sm ${active ? 'btn-outline-danger' : 'btn-outline-success'}" type="button" data-action="status" data-id="${technology.id_tecnologia}" data-name="${escapeHtml(technology.nombre)}" data-active="${active ? 1 : 0}" title="${active ? 'Dar de baja' : 'Activar'}">
                            <i class="fa-solid ${active ? 'fa-power-off' : 'fa-rotate-left'}"></i>
                        </button>
                    </td>
                </tr>`;
        }).join('');
    }

    function updateSummary(summary = {}) {
        $('#totalTechnologies').textContent = Number(summary.total) || 0;
        $('#activeTechnologies').textContent = Number(summary.activas) || 0;
        $('#inactiveTechnologies').textContent = Number(summary.inactivas) || 0;
    }

    async function loadTechnologies() {
        const params = new URLSearchParams({
            busqueda: $('#technologySearch').value.trim(),
            estado: $('#technologyStatusFilter').value,
        });

        tableBody.innerHTML = `
            <tr><td class="text-center py-5" colspan="7">
                <i class="fa-solid fa-circle-notch fa-spin me-2"></i>Cargando tecnologías...
            </td></tr>`;

        try {
            const data = await request(`${module.dataset.listUrl}?${params}`);
            renderRows(data.tecnologias || []);
            updateSummary(data.resumen);
        } catch (error) {
            tableBody.innerHTML = `
                <tr><td class="text-center py-5 text-danger" colspan="7">
                    ${escapeHtml(error.message)}
                </td></tr>`;
        }
    }

    function updateIconPreview() {
        const type = $('#technologyIconType').value;
        const value = $('#technologyIconValue').value.trim();
        const preview = $('#technologyIconPreview');

        if (type === 'imagen') {
            $('#technologyIconValueLabel').textContent = 'URL o ruta de imagen *';
            $('#technologyIconValue').placeholder = 'Assets/img/tecnologias/php.svg';
            $('#technologyIconHelp').textContent = 'Puede ser una URL completa o una ruta pública del proyecto.';
            preview.innerHTML = value
                ? `<img src="${escapeHtml(value)}" alt="" width="24" height="24" style="object-fit:contain">`
                : '<i class="fa-solid fa-image"></i>';
        } else {
            $('#technologyIconValueLabel').textContent = 'Clase de Font Awesome *';
            $('#technologyIconValue').placeholder = 'fa-brands fa-php';
            $('#technologyIconHelp').textContent = 'Ejemplo: fa-brands fa-php';
            preview.innerHTML = `<i class="${escapeHtml(value || 'fa-solid fa-code')}"></i>`;
        }
    }

    function resetForm() {
        form.reset();
        form.classList.remove('was-validated');
        $('#technologyId').value = '0';
        $('#technologyColor').value = '#00F6FF';
        $('#technologyColorPicker').value = '#00F6FF';
        $('#technologyActive').checked = true;
        $('#technologyModalTitle').textContent = 'Nueva tecnología';
        $('#technologyFormFeedback').classList.add('d-none');
        updateIconPreview();
    }

    async function editTechnology(id) {
        try {
            const technology = await request(`${module.dataset.getUrl}/${id}`);
            resetForm();

            $('#technologyId').value = technology.id_tecnologia;
            $('#technologyName').value = technology.nombre || '';
            $('#technologySlug').value = technology.slug || '';
            $('#technologyArea').value = technology.nivel_o_area || '';
            $('#technologyOrder').value = technology.orden_visualizacion ?? '';
            $('#technologyColor').value = technology.color_hexadecimal || '#00F6FF';
            $('#technologyColorPicker').value = technology.color_hexadecimal || '#00F6FF';
            $('#technologyIconType').value = technology.tipo_icono || 'fontawesome';
            $('#technologyIconValue').value = technology.valor_icono || '';
            $('#technologyActive').checked = Number(technology.activa) === 1;
            $('#technologyModalTitle').textContent = 'Editar tecnología';
            updateIconPreview();
            modal.show();
        } catch (error) {
            notify(error.message, 'error');
        }
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        form.classList.add('was-validated');

        if (!form.checkValidity()) {
            return;
        }

        const button = $('#btnSaveTechnology');
        const original = button.innerHTML;
        const data = new FormData(form);
        data.set('activa', $('#technologyActive').checked ? '1' : '0');
        button.disabled = true;
        button.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Guardando...';

        try {
            const result = await request(module.dataset.saveUrl, {
                method: 'POST',
                body: data,
            });
            modal.hide();
            notify(result.msg);
            await loadTechnologies();
        } catch (error) {
            const feedback = $('#technologyFormFeedback');
            feedback.textContent = error.message;
            feedback.classList.remove('d-none');
        } finally {
            button.disabled = false;
            button.innerHTML = original;
        }
    });

    tableBody.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-action]');

        if (!button) {
            return;
        }

        if (button.dataset.action === 'edit') {
            editTechnology(button.dataset.id);
            return;
        }

        pendingStatus = {
            id: button.dataset.id,
            name: button.dataset.name,
            active: button.dataset.active === '1',
        };

        $('#technologyStatusMessage').textContent = pendingStatus.active
            ? `¿Deseas dar de baja a ${pendingStatus.name}?`
            : `¿Deseas activar a ${pendingStatus.name}?`;
        statusModal.show();
    });

    $('#btnConfirmTechnologyStatus').addEventListener('click', async () => {
        if (!pendingStatus) {
            return;
        }

        const data = new FormData();
        data.set('csrf_token', csrfToken);
        data.set('id_tecnologia', pendingStatus.id);
        data.set('activa', pendingStatus.active ? '0' : '1');

        try {
            const result = await request(module.dataset.statusUrl, {
                method: 'POST',
                body: data,
            });
            statusModal.hide();
            notify(result.msg);
            pendingStatus = null;
            await loadTechnologies();
        } catch (error) {
            notify(error.message, 'error');
        }
    });

    $('#btnNewTechnology').addEventListener('click', resetForm);
    $('#btnGenerateTechnologySlug').addEventListener('click', () => {
        $('#technologySlug').value = slugify($('#technologyName').value);
    });
    $('#technologyIconType').addEventListener('change', updateIconPreview);
    $('#technologyIconValue').addEventListener('input', updateIconPreview);
    $('#technologyColorPicker').addEventListener('input', (event) => {
        $('#technologyColor').value = event.target.value.toUpperCase();
    });
    $('#technologyColor').addEventListener('input', (event) => {
        const color = event.target.value.toUpperCase();
        event.target.value = color;

        if (/^#[0-9A-F]{6}$/.test(color)) {
            $('#technologyColorPicker').value = color;
        }
    });
    $('#btnRefreshTechnologies').addEventListener('click', loadTechnologies);
    $('#technologyStatusFilter').addEventListener('change', loadTechnologies);
    $('#technologySearch').addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadTechnologies, 300);
    });

    loadTechnologies();
})();
