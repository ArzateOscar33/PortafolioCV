'use strict';

(() => {
    const module = document.querySelector('#multimediaModule');

    if (!module) {
        return;
    }

    const $ = (selector) => module.querySelector(selector);
    const mediaGrid = $('#mediaGrid');
    const form = $('#mediaForm');
    const modalElement = $('#mediaModal');
    const notificationElement = $('#mediaNotification');
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const toast = bootstrap.Toast.getOrCreateInstance(notificationElement);

    let projects = [];
    let types = [];
    let previewObjectUrl = null;
    let editingRecord = null;
    let searchTimer = null;

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function normalizeBaseUrl(value) {
        return String(value || '').replace(/\/+$/, '') + '/';
    }

    function publicUrl(record) {
        if (record.tipo_almacenamiento === 'externo') {
            return record.url_externa || '';
        }

        if (!record.ruta_archivo) {
            return '';
        }

        return normalizeBaseUrl(module.dataset.baseUrl)
            + String(record.ruta_archivo).replace(/^\/+/, '');
    }

    function thumbnailUrl(record) {
        if (!record.ruta_miniatura) {
            return '';
        }

        if (/^https?:\/\//i.test(record.ruta_miniatura)) {
            return record.ruta_miniatura;
        }

        return normalizeBaseUrl(module.dataset.baseUrl)
            + String(record.ruta_miniatura).replace(/^\/+/, '');
    }

    function isDirectVideoUrl(url) {
        return /\.(mp4|webm|mov)(?:[?#].*)?$/i.test(url || '');
    }

    function formatBytes(value) {
        const bytes = Number(value || 0);

        if (!bytes) {
            return 'Tamaño no disponible';
        }

        const units = ['B', 'KB', 'MB', 'GB'];
        const position = Math.min(
            Math.floor(Math.log(bytes) / Math.log(1024)),
            units.length - 1
        );

        return `${(bytes / (1024 ** position)).toFixed(
            position === 0 ? 0 : 1
        )} ${units[position]}`;
    }

    function formatDate(value) {
        if (!value) {
            return 'Sin registro';
        }

        const date = new Date(String(value).replace(' ', 'T'));

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
        $('#mediaNotificationMessage').textContent = message;
        $('#mediaNotificationTitle').textContent = success
            ? 'Operación completada'
            : 'Operación no completada';

        $('#mediaNotificationIcon').className = success
            ? 'fa-solid fa-circle-check text-success me-2'
            : 'fa-solid fa-circle-exclamation text-danger me-2';

        toast.show();
    }

    function setLoading() {
        $('#mediaEmptyState').hidden = true;
        mediaGrid.innerHTML = `
            <div class="admin-panel text-center py-5">
                <i class="fa-solid fa-circle-notch fa-spin me-2"></i>
                Cargando biblioteca multimedia...
            </div>
        `;
    }

    function previewMarkup(record, compact = false) {
        const source = publicUrl(record);
        const miniature = thumbnailUrl(record);
        const alt = escapeHtml(
            record.texto_alternativo
                || record.titulo
                || record.proyecto_titulo
                || 'Archivo multimedia'
        );

        if (record.tipo_slug === 'imagen' && source) {
            return `
                <img
                    src="${escapeHtml(source)}"
                    alt="${alt}"
                    loading="lazy">
            `;
        }

        if (record.tipo_slug === 'video' && source) {
            if (
                record.tipo_almacenamiento === 'local'
                || isDirectVideoUrl(source)
            ) {
                return `
                    <video
                        src="${escapeHtml(source)}"
                        ${miniature ? `poster="${escapeHtml(miniature)}"` : ''}
                        ${compact ? 'muted' : 'controls'}
                        preload="metadata">
                    </video>
                `;
            }

            if (miniature) {
                return `
                    <a
                        class="d-block w-100 h-100"
                        href="${escapeHtml(source)}"
                        target="_blank"
                        rel="noopener noreferrer">
                        <img
                            src="${escapeHtml(miniature)}"
                            alt="${alt}"
                            loading="lazy">
                    </a>
                `;
            }

            return `
                <a
                    class="media-card__placeholder text-decoration-none"
                    href="${escapeHtml(source)}"
                    target="_blank"
                    rel="noopener noreferrer">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>Abrir video externo</span>
                </a>
            `;
        }

        return `
            <div class="media-card__placeholder">
                <i class="fa-solid fa-file-circle-question"></i>
                <span>Vista previa no disponible</span>
            </div>
        `;
    }

    function renderMedia(records) {
        const emptyState = $('#mediaEmptyState');

        if (!records.length) {
            mediaGrid.innerHTML = '';
            emptyState.hidden = false;
            return;
        }

        emptyState.hidden = true;

        mediaGrid.innerHTML = records.map((record) => {
            const active = Number(record.activo) === 1;
            const cover = Number(record.es_portada) === 1;
            const image = record.tipo_slug === 'imagen';
            const stateTitle = active ? 'Dar de baja' : 'Restaurar';
            const stateIcon = active ? 'fa-box-archive' : 'fa-rotate-left';
            const stateClass = active
                ? 'btn-outline-danger'
                : 'btn-outline-success';
            const coverTitle = cover
                ? 'Quitar portada'
                : 'Establecer como portada';
            const coverIcon = cover ? 'fa-star-half-stroke' : 'fa-star';

            return `
                <article class="media-card ${active ? '' : 'opacity-75'}">
                    <div class="media-card__preview">
                        ${cover
                            ? '<span class="badge text-bg-warning media-cover-badge"><i class="fa-solid fa-star me-1"></i>Portada</span>'
                            : ''}
                        <span class="badge ${active ? 'text-bg-success' : 'text-bg-secondary'} media-status-badge">
                            ${active ? 'Activo' : 'Inactivo'}
                        </span>
                        ${previewMarkup(record, true)}
                    </div>

                    <div class="media-card__body">
                        <div class="d-flex justify-content-between gap-3">
                            <div class="min-w-0">
                                <span class="badge text-bg-dark mb-2">
                                    <i class="fa-solid ${image ? 'fa-image' : 'fa-video'} me-1"></i>
                                    ${escapeHtml(record.tipo_nombre)}
                                </span>
                                <strong class="media-card__title d-block">
                                    ${escapeHtml(
                                        record.titulo
                                            || `${record.tipo_nombre} sin título`
                                    )}
                                </strong>
                                <div class="small text-body-secondary mt-1">
                                    ${escapeHtml(record.proyecto_titulo)}
                                </div>
                            </div>
                            <span class="small text-body-secondary">
                                #${Number(record.orden_visualizacion || 0)}
                            </span>
                        </div>

                        <div class="small text-body-secondary mt-3">
                            <div>${escapeHtml(formatBytes(record.tamano_bytes))}</div>
                            <div>${escapeHtml(formatDate(record.actualizado_en))}</div>
                        </div>

                        <div class="d-flex justify-content-end gap-1 flex-wrap mt-3">
                            ${image && active
                                ? `
                                    <button
                                        class="btn btn-sm btn-outline-warning"
                                        type="button"
                                        data-action="cover"
                                        data-id="${Number(record.id_multimedia)}"
                                        data-cover="${cover ? '0' : '1'}"
                                        title="${coverTitle}">
                                        <i class="fa-solid ${coverIcon}"></i>
                                    </button>
                                `
                                : ''}
                            <button
                                class="btn btn-sm btn-outline-primary"
                                type="button"
                                data-action="edit"
                                data-id="${Number(record.id_multimedia)}"
                                title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button
                                class="btn btn-sm ${stateClass}"
                                type="button"
                                data-action="state"
                                data-id="${Number(record.id_multimedia)}"
                                data-active="${active ? '0' : '1'}"
                                data-title="${escapeHtml(record.titulo || record.tipo_nombre)}"
                                title="${stateTitle}">
                                <i class="fa-solid ${stateIcon}"></i>
                            </button>
                        </div>
                    </div>
                </article>
            `;
        }).join('');
    }

    async function loadCatalogs() {
        const data = await request(module.dataset.catalogsUrl);
        projects = data.proyectos || [];
        types = data.tipos || [];

        const projectOptions = projects.map((project) => `
            <option value="${Number(project.id_proyecto)}">
                ${escapeHtml(project.titulo)}
            </option>
        `).join('');

        $('#mediaProject').innerHTML = `
            <option value="">Selecciona un proyecto</option>
            ${projectOptions}
        `;

        $('#mediaProjectFilter').innerHTML = `
            <option value="0">Todos los proyectos</option>
            ${projectOptions}
        `;

        $('#mediaType').innerHTML = `
            <option value="">Selecciona el tipo</option>
            ${types.map((type) => `
                <option value="${Number(type.id_tipo_multimedia)}">
                    ${escapeHtml(type.nombre)}
                </option>
            `).join('')}
        `;
    }

    async function loadMedia() {
        setLoading();

        const params = new URLSearchParams({
            busqueda: $('#mediaSearch').value.trim(),
            id_proyecto: $('#mediaProjectFilter').value,
            tipo: $('#mediaTypeFilter').value,
            estado: $('#mediaStatusFilter').value,
        });

        try {
            const data = await request(
                `${module.dataset.listUrl}?${params.toString()}`
            );

            renderMedia(data.multimedia || []);
            $('#totalMedia').textContent = data.resumen?.total ?? 0;
            $('#totalImages').textContent = data.resumen?.imagenes ?? 0;
            $('#totalVideos').textContent = data.resumen?.videos ?? 0;
            $('#inactiveMedia').textContent = data.resumen?.inactivos ?? 0;
        } catch (error) {
            mediaGrid.innerHTML = `
                <div class="admin-panel text-center py-5 text-danger">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    ${escapeHtml(error.message)}
                </div>
            `;
            notify(error.message, false);
        }
    }

    function clearPreviewObjectUrl() {
        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = null;
        }
    }

    function emptyPreview() {
        clearPreviewObjectUrl();
        $('#mediaPreview').innerHTML = `
            <div class="media-form-preview__empty">
                <i class="fa-solid fa-photo-film"></i>
                Selecciona un archivo o escribe una URL.
            </div>
        `;
        $('#mediaFileDetails').textContent = 'Sin archivo seleccionado.';
    }

    function renderFormPreview(record) {
        clearPreviewObjectUrl();
        $('#mediaPreview').innerHTML = previewMarkup(record, false);
        $('#mediaFileDetails').textContent = record.tipo_almacenamiento === 'local'
            ? `${record.tipo_mime || 'Archivo local'} · ${formatBytes(record.tamano_bytes)}`
            : 'Recurso almacenado mediante URL externa.';
    }

    function previewSelectedFile() {
        const file = $('#mediaFile').files[0];

        if (!file) {
            if (editingRecord) {
                renderFormPreview(editingRecord);
            } else {
                emptyPreview();
            }
            return;
        }

        clearPreviewObjectUrl();
        previewObjectUrl = URL.createObjectURL(file);

        if (file.type.startsWith('image/')) {
            $('#mediaPreview').innerHTML = `
                <img src="${previewObjectUrl}" alt="Vista previa">
            `;
            $('#mediaCover').disabled = false;
        } else if (file.type.startsWith('video/')) {
            $('#mediaPreview').innerHTML = `
                <video src="${previewObjectUrl}" controls preload="metadata"></video>
            `;
            $('#mediaCover').checked = false;
            $('#mediaCover').disabled = true;
        } else {
            emptyPreview();
            return;
        }

        $('#mediaFileDetails').textContent =
            `${file.name} · ${formatBytes(file.size)} · ${file.type}`;
    }

    function previewExternalUrl() {
        const url = $('#mediaExternalUrl').value.trim();
        const selectedType = types.find(
            (type) => String(type.id_tipo_multimedia) === $('#mediaType').value
        );

        if (!url || !selectedType) {
            emptyPreview();
            return;
        }

        const record = {
            tipo_almacenamiento: 'externo',
            url_externa: url,
            ruta_miniatura: $('#mediaThumbnail').value.trim(),
            tipo_slug: selectedType.slug,
            tipo_nombre: selectedType.nombre,
            titulo: $('#mediaTitle').value.trim(),
            texto_alternativo: $('#mediaAlternativeText').value.trim(),
        };

        renderFormPreview(record);
        $('#mediaCover').disabled = selectedType.slug !== 'imagen';

        if (selectedType.slug !== 'imagen') {
            $('#mediaCover').checked = false;
        }
    }

    function toggleStorageFields() {
        const external = $('#mediaStorageType').value === 'externo';

        $('#mediaFileGroup').classList.toggle('d-none', external);
        $('#mediaExternalUrlGroup').classList.toggle('d-none', !external);
        $('#mediaExternalTypeGroup').classList.toggle('d-none', !external);
        $('#mediaFile').required = !external && !editingRecord;
        $('#mediaExternalUrl').required = external;
        $('#mediaType').required = external;

        if (external) {
            previewExternalUrl();
        } else {
            $('#mediaCover').disabled = Boolean(
                editingRecord && editingRecord.tipo_slug === 'video'
            );
            previewSelectedFile();
        }
    }

    function updateDescriptionCounter() {
        $('#mediaDescriptionCounter').textContent =
            `${$('#mediaDescription').value.length} / 500`;
    }

    function resetForm() {
        form.reset();
        form.classList.remove('was-validated');
        editingRecord = null;
        $('#mediaId').value = '0';
        $('#mediaStorageType').value = 'local';
        $('#mediaActive').checked = true;
        $('#mediaCover').checked = false;
        $('#mediaCover').disabled = false;
        $('#mediaModalTitle').textContent = 'Subir multimedia';
        $('#btnSaveMedia span').textContent = 'Guardar multimedia';
        $('#mediaFormFeedback').classList.add('d-none');
        $('#mediaFormFeedback').textContent = '';
        updateDescriptionCounter();
        emptyPreview();
        toggleStorageFields();
    }

    function openNewMedia() {
        resetForm();

        const selectedProject = $('#mediaProjectFilter').value;

        if (selectedProject !== '0') {
            $('#mediaProject').value = selectedProject;
        }

        modal.show();
    }

    async function editMedia(id) {
        try {
            const record = await request(
                `${module.dataset.getUrl}/${Number(id)}`
            );

            resetForm();
            editingRecord = record;
            $('#mediaId').value = record.id_multimedia;
            $('#mediaProject').value = record.id_proyecto;
            $('#mediaStorageType').value = record.tipo_almacenamiento;
            $('#mediaType').value = record.id_tipo_multimedia;
            $('#mediaExternalUrl').value = record.url_externa || '';
            $('#mediaTitle').value = record.titulo || '';
            $('#mediaOrder').value = record.orden_visualizacion ?? 0;
            $('#mediaAlternativeText').value = record.texto_alternativo || '';
            $('#mediaDescription').value = record.descripcion || '';
            $('#mediaThumbnail').value = record.ruta_miniatura || '';
            $('#mediaCover').checked = Number(record.es_portada) === 1;
            $('#mediaActive').checked = Number(record.activo) === 1;
            $('#mediaModalTitle').textContent = 'Editar multimedia';
            $('#btnSaveMedia span').textContent = 'Guardar cambios';
            updateDescriptionCounter();
            toggleStorageFields();
            renderFormPreview(record);
            modal.show();
        } catch (error) {
            notify(error.message, false);
        }
    }

    async function saveMedia(event) {
        event.preventDefault();
        form.classList.add('was-validated');

        if (!form.checkValidity()) {
            return;
        }

        const saveButton = $('#btnSaveMedia');
        const originalHtml = saveButton.innerHTML;
        saveButton.disabled = true;
        saveButton.innerHTML = `
            <i class="fa-solid fa-circle-notch fa-spin"></i>
            <span>Guardando...</span>
        `;

        try {
            const data = await request(module.dataset.saveUrl, {
                method: 'POST',
                body: new FormData(form),
            });

            modal.hide();
            notify(data.msg || 'Archivo multimedia guardado.');
            await loadMedia();
        } catch (error) {
            $('#mediaFormFeedback').textContent = error.message;
            $('#mediaFormFeedback').classList.remove('d-none');
        } finally {
            saveButton.disabled = false;
            saveButton.innerHTML = originalHtml;
        }
    }

    async function changeState(button) {
        const active = button.dataset.active === '1';
        const title = button.dataset.title || 'este archivo';
        const question = active
            ? `¿Deseas restaurar ${title}?`
            : `¿Deseas dar de baja ${title}? El archivo físico no se eliminará.`;

        if (!window.confirm(question)) {
            return;
        }

        const data = new FormData();
        data.append('csrf_token', $('#mediaCsrfToken').value);
        data.append('id_multimedia', button.dataset.id);
        data.append('activo', active ? '1' : '0');

        try {
            const response = await request(module.dataset.statusUrl, {
                method: 'POST',
                body: data,
            });

            notify(response.msg);
            await loadMedia();
        } catch (error) {
            notify(error.message, false);
        }
    }

    async function changeCover(button) {
        const cover = button.dataset.cover === '1';
        const question = cover
            ? '¿Deseas establecer esta imagen como portada del proyecto?'
            : '¿Deseas quitar esta imagen como portada?';

        if (!window.confirm(question)) {
            return;
        }

        const data = new FormData();
        data.append('csrf_token', $('#mediaCsrfToken').value);
        data.append('id_multimedia', button.dataset.id);
        data.append('es_portada', cover ? '1' : '0');

        try {
            const response = await request(module.dataset.coverUrl, {
                method: 'POST',
                body: data,
            });

            notify(response.msg);
            await loadMedia();
        } catch (error) {
            notify(error.message, false);
        }
    }

    $('#btnNewMedia').addEventListener('click', openNewMedia);
    $('#btnEmptyNewMedia').addEventListener('click', openNewMedia);
    $('#btnRefreshMedia').addEventListener('click', loadMedia);
    form.addEventListener('submit', saveMedia);
    $('#mediaStorageType').addEventListener('change', toggleStorageFields);
    $('#mediaFile').addEventListener('change', previewSelectedFile);
    $('#mediaExternalUrl').addEventListener('input', previewExternalUrl);
    $('#mediaType').addEventListener('change', previewExternalUrl);
    $('#mediaThumbnail').addEventListener('input', () => {
        if ($('#mediaStorageType').value === 'externo') {
            previewExternalUrl();
        }
    });
    $('#mediaDescription').addEventListener('input', updateDescriptionCounter);

    $('#mediaSearch').addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(loadMedia, 350);
    });

    [
        '#mediaProjectFilter',
        '#mediaTypeFilter',
        '#mediaStatusFilter',
    ].forEach((selector) => {
        $(selector).addEventListener('change', loadMedia);
    });

    mediaGrid.addEventListener('click', (event) => {
        const button = event.target.closest('[data-action]');

        if (!button) {
            return;
        }

        if (button.dataset.action === 'edit') {
            editMedia(button.dataset.id);
        } else if (button.dataset.action === 'state') {
            changeState(button);
        } else if (button.dataset.action === 'cover') {
            changeCover(button);
        }
    });

    modalElement.addEventListener('hidden.bs.modal', () => {
        clearPreviewObjectUrl();
    });

    (async () => {
        try {
            await loadCatalogs();
            await loadMedia();
        } catch (error) {
            mediaGrid.innerHTML = `
                <div class="admin-panel text-center py-5 text-danger">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    ${escapeHtml(error.message)}
                </div>
            `;
            notify(error.message, false);
        }
    })();
})();
