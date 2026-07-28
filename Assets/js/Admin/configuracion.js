(() => {
    'use strict';

    const module = document.getElementById('settingsModule');

    if (!module) {
        return;
    }

    const $ = (selector, context = document) => context.querySelector(selector);
    const form = $('#siteSettingsForm');
    const socialForm = $('#socialForm');
    const socialTableBody = $('#socialTableBody');
    const socialModal = bootstrap.Modal.getOrCreateInstance($('#socialModal'));
    const socialStateModal = bootstrap.Modal.getOrCreateInstance($('#socialStateModal'));
    const toast = bootstrap.Toast.getOrCreateInstance($('#settingsNotification'), {
        delay: 4200,
    });

    const fieldMap = {
        nombre_sitio: '#siteName',
        nombre_completo: '#fullName',
        titulo_profesional: '#professionalTitle',
        descripcion_sitio: '#siteDescription',
        footer_texto: '#footerText',
        hero_kicker: '#heroKicker',
        hero_titulo_principal: '#heroMainTitle',
        hero_titulo_destacado: '#heroHighlightedTitle',
        hero_descripcion: '#heroDescription',
        hero_cta_principal_texto: '#heroPrimaryText',
        hero_cta_principal_url: '#heroPrimaryUrl',
        hero_cta_secundario_texto: '#heroSecondaryText',
        hero_cta_secundario_url: '#heroSecondaryUrl',
        acerca_titulo: '#aboutTitle',
        acerca_parrafo_1: '#aboutParagraphOne',
        acerca_parrafo_2: '#aboutParagraphTwo',
        ubicacion: '#publicLocation',
        correo_publico: '#publicEmail',
        telefono_publico: '#publicPhone',
        tiempo_respuesta: '#responseTime',
        disponibilidad_texto: '#availabilityText',
        seo_titulo: '#seoTitle',
        seo_descripcion: '#seoDescription',
        seo_palabras_clave: '#seoKeywords',
        color_primario: '#primaryColor',
        color_secundario: '#secondaryColor',
        color_acento: '#accentColor',
    };

    const booleanMap = {
        disponible_proyectos: '#availableProjects',
        mostrar_correo: '#showPublicEmail',
        mostrar_telefono: '#showPublicPhone',
    };

    const colorPairs = [
        ['#primaryColor', '#primaryColorPicker'],
        ['#secondaryColor', '#secondaryColorPicker'],
        ['#accentColor', '#accentColorPicker'],
        ['#socialColor', '#socialColorPicker'],
    ];

    let pendingSocialState = null;
    let currentConfig = {};

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function baseUrl() {
        return module.dataset.loadUrl.replace(/configuracion\/obtener.*$/i, '');
    }

    function publicPath(path) {
        if (!path) {
            return '';
        }

        if (/^https?:\/\//i.test(path)) {
            return path;
        }

        return `${baseUrl()}${String(path).replace(/^\/+/, '')}`;
    }

    function formatDate(value) {
        if (!value) {
            return 'Sin cambios';
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
            throw new Error(data.msg || 'No fue posible completar la operación.');
        }

        return data;
    }

    function notify(message, success = true) {
        $('#settingsNotificationMessage').textContent = message;
        $('#settingsNotificationTitle').textContent = success
            ? 'Operación completada'
            : 'Operación no completada';

        $('#settingsNotificationIcon').className = success
            ? 'fa-solid fa-circle-check text-success me-2'
            : 'fa-solid fa-circle-exclamation text-danger me-2';

        toast.show();
    }

    function setButtonLoading(button, loading, normalText) {
        if (!button) {
            return;
        }

        button.disabled = loading;
        const span = button.querySelector('span');

        if (span) {
            span.textContent = loading ? 'Guardando...' : normalText;
        }

        const icon = button.querySelector('i');

        if (icon) {
            icon.className = loading
                ? 'fa-solid fa-circle-notch fa-spin'
                : 'fa-solid fa-floppy-disk';
        }
    }

    function populateSettings(config) {
        currentConfig = config || {};

        Object.entries(fieldMap).forEach(([key, selector]) => {
            const input = $(selector);

            if (input) {
                input.value = config[key] ?? '';
            }
        });

        Object.entries(booleanMap).forEach(([key, selector]) => {
            const input = $(selector);

            if (input) {
                input.checked = Boolean(config[key]);
            }
        });

        $('#aboutSkills').value = Array.isArray(config.acerca_habilidades)
            ? config.acerca_habilidades.join('\n')
            : '';

        syncAllColors();
        renderCurrentFiles(config);
    }

    function renderCurrentFiles(config) {
        renderImageCurrent(
            '#profilePhotoCurrent',
            config.foto_perfil_ruta,
            'Fotografía actual'
        );

        renderDocumentCurrent(
            '#resumeCurrent',
            config.cv_ruta,
            'Abrir currículum actual'
        );

        renderImageCurrent(
            '#seoImageCurrent',
            config.seo_imagen_ruta,
            'Imagen SEO actual'
        );

        renderImageCurrent(
            '#faviconCurrent',
            config.favicon_ruta,
            'Favicon actual',
            true
        );
    }

    function renderImageCurrent(selector, path, label, compact = false) {
        const container = $(selector);

        if (!path) {
            container.innerHTML = '<span class="text-body-secondary small">Sin archivo actual</span>';
            return;
        }

        container.innerHTML = `
            <a
                class="d-inline-flex align-items-center gap-3 text-decoration-none"
                href="${escapeHtml(publicPath(path))}"
                target="_blank"
                rel="noopener noreferrer">
                <img
                    src="${escapeHtml(publicPath(path))}"
                    alt="${escapeHtml(label)}"
                    style="width:${compact ? '48px' : '92px'};height:${compact ? '48px' : '68px'};object-fit:cover;border-radius:8px;">
                <span class="small">${escapeHtml(label)}</span>
            </a>
        `;
    }

    function renderDocumentCurrent(selector, path, label) {
        const container = $(selector);

        if (!path) {
            container.innerHTML = '<span class="text-body-secondary small">Sin archivo actual</span>';
            return;
        }

        container.innerHTML = `
            <a
                class="btn btn-sm btn-outline-secondary"
                href="${escapeHtml(publicPath(path))}"
                target="_blank"
                rel="noopener noreferrer">
                <i class="fa-solid fa-file-pdf me-1"></i>
                ${escapeHtml(label)}
            </a>
        `;
    }

    function renderSocials(socials) {
        const empty = $('#socialEmptyState');

        if (!socials.length) {
            socialTableBody.innerHTML = '';
            empty.hidden = false;
            return;
        }

        empty.hidden = true;

        socialTableBody.innerHTML = socials.map((social) => {
            const active = Number(social.activa) === 1;
            const icon = social.clase_icono || 'fa-solid fa-link';
            const color = social.color_hexadecimal || 'currentColor';

            return `
                <tr>
                    <td><strong>${Number(social.orden_visualizacion || 0)}</strong></td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <span class="fs-4" style="color:${escapeHtml(color)}">
                                <i class="${escapeHtml(icon)}"></i>
                            </span>
                            <div>
                                <strong>${escapeHtml(social.plataforma)}</strong>
                                <div class="small text-body-secondary">
                                    ${escapeHtml(social.etiqueta || 'Sin etiqueta')}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <a
                            class="small"
                            href="${escapeHtml(social.url)}"
                            target="_blank"
                            rel="noopener noreferrer">
                            ${escapeHtml(social.url)}
                        </a>
                    </td>
                    <td>
                        <span class="badge ${active ? 'text-bg-success' : 'text-bg-secondary'}">
                            ${active ? 'Activa' : 'Inactiva'}
                        </span>
                    </td>
                    <td class="small text-body-secondary">
                        ${escapeHtml(formatDate(social.actualizada_en))}
                    </td>
                    <td>
                        <div class="d-flex justify-content-end gap-1">
                            <button
                                class="btn btn-sm btn-outline-primary"
                                type="button"
                                data-action="edit-social"
                                data-id="${Number(social.id_red_social)}"
                                title="Editar">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button
                                class="btn btn-sm btn-outline-secondary"
                                type="button"
                                data-action="state-social"
                                data-id="${Number(social.id_red_social)}"
                                data-name="${escapeHtml(social.plataforma)}"
                                data-active="${active ? '0' : '1'}"
                                title="${active ? 'Dar de baja' : 'Activar'}">
                                <i class="fa-solid ${active ? 'fa-pause' : 'fa-play'}"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function loadSettings() {
        try {
            const data = await request(module.dataset.loadUrl);
            populateSettings(data.configuracion || {});
            renderSocials(data.redes || []);

            $('#totalSettings').textContent = data.resumen?.total_configuraciones ?? 0;
            $('#publicSettings').textContent = data.resumen?.publicas ?? 0;
            $('#activeSocials').textContent = data.resumen?.redes_activas ?? 0;
            $('#settingsUpdatedAt').textContent = formatDate(
                data.resumen?.ultima_actualizacion
            );
        } catch (error) {
            notify(error.message, false);
        }
    }

    async function saveSettings(event) {
        event?.preventDefault();
        const feedback = $('#settingsFormFeedback');

        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        feedback.classList.add('d-none');
        feedback.textContent = '';
        form.classList.remove('was-validated');

        const button = $('#btnSaveSettings');
        setButtonLoading(button, true, 'Guardar configuración');
        $('#btnSaveSettingsTop').disabled = true;

        try {
            const data = await request(module.dataset.saveUrl, {
                method: 'POST',
                body: new FormData(form),
            });

            notify(data.msg || 'Configuración guardada.');
            resetFileInputs();
            await loadSettings();
        } catch (error) {
            feedback.textContent = error.message;
            feedback.classList.remove('d-none');
            notify(error.message, false);
        } finally {
            setButtonLoading(button, false, 'Guardar configuración');
            $('#btnSaveSettingsTop').disabled = false;
        }
    }

    function resetFileInputs() {
        [
            '#profilePhoto',
            '#resumeFile',
            '#seoImage',
            '#faviconFile',
        ].forEach((selector) => {
            $(selector).value = '';
        });

        [
            '#removeProfilePhoto',
            '#removeResume',
            '#removeSeoImage',
            '#removeFavicon',
        ].forEach((selector) => {
            $(selector).checked = false;
        });
    }

    function resetSocialForm() {
        socialForm.reset();
        socialForm.classList.remove('was-validated');
        $('#socialId').value = '0';
        $('#socialActive').checked = true;
        $('#socialModalTitle').textContent = 'Nueva red social';
        $('#btnSaveSocial span').textContent = 'Guardar red social';
        $('#socialFormFeedback').classList.add('d-none');
        $('#socialFormFeedback').textContent = '';
        $('#socialColor').value = '#00F6FF';
        syncColor('#socialColor', '#socialColorPicker');
        updateSocialIconPreview();
    }

    async function editSocial(id) {
        try {
            const social = await request(
                `${module.dataset.socialGetUrl}/${Number(id)}`
            );

            resetSocialForm();
            $('#socialId').value = social.id_red_social || 0;
            $('#socialPlatform').value = social.plataforma || '';
            $('#socialLabel').value = social.etiqueta || '';
            $('#socialUrl').value = social.url || '';
            $('#socialIcon').value = social.clase_icono || '';
            $('#socialColor').value = social.color_hexadecimal || '#00F6FF';
            $('#socialOrder').value = social.orden_visualizacion ?? '';
            $('#socialActive').checked = Number(social.activa) === 1;
            $('#socialModalTitle').textContent = 'Editar red social';
            $('#btnSaveSocial span').textContent = 'Guardar cambios';
            syncColor('#socialColor', '#socialColorPicker');
            updateSocialIconPreview();
            socialModal.show();
        } catch (error) {
            notify(error.message, false);
        }
    }

    async function saveSocial(event) {
        event.preventDefault();

        if (!socialForm.checkValidity()) {
            socialForm.classList.add('was-validated');
            return;
        }

        const feedback = $('#socialFormFeedback');
        const button = $('#btnSaveSocial');
        feedback.classList.add('d-none');
        setButtonLoading(button, true, 'Guardar red social');

        try {
            const data = await request(module.dataset.socialSaveUrl, {
                method: 'POST',
                body: new FormData(socialForm),
            });

            socialModal.hide();
            notify(data.msg || 'Red social guardada.');
            await loadSettings();
        } catch (error) {
            feedback.textContent = error.message;
            feedback.classList.remove('d-none');
            notify(error.message, false);
        } finally {
            setButtonLoading(button, false, $('#socialId').value === '0'
                ? 'Guardar red social'
                : 'Guardar cambios');
        }
    }

    function askSocialState(button) {
        pendingSocialState = {
            id: Number(button.dataset.id),
            active: Number(button.dataset.active),
        };

        const action = pendingSocialState.active === 1
            ? 'activar'
            : 'dar de baja';

        $('#socialStateMessage').textContent =
            `¿Deseas ${action} la red social “${button.dataset.name}”?`;

        socialStateModal.show();
    }

    async function confirmSocialState() {
        if (!pendingSocialState) {
            return;
        }

        const data = new FormData();
        data.append('csrf_token', $('#settingsCsrfToken').value);
        data.append('id_red_social', String(pendingSocialState.id));
        data.append('activa', String(pendingSocialState.active));

        const button = $('#btnConfirmSocialState');
        button.disabled = true;

        try {
            const response = await request(module.dataset.socialStateUrl, {
                method: 'POST',
                body: data,
            });

            socialStateModal.hide();
            notify(response.msg || 'Estado actualizado.');
            await loadSettings();
        } catch (error) {
            notify(error.message, false);
        } finally {
            button.disabled = false;
            pendingSocialState = null;
        }
    }

    function syncColor(textSelector, pickerSelector) {
        const text = $(textSelector);
        const picker = $(pickerSelector);
        const value = text.value.trim();

        if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
            picker.value = value;
        }
    }

    function syncAllColors() {
        colorPairs.forEach(([text, picker]) => syncColor(text, picker));
    }

    function bindColorPair(textSelector, pickerSelector) {
        const text = $(textSelector);
        const picker = $(pickerSelector);

        picker.addEventListener('input', () => {
            text.value = picker.value.toUpperCase();
        });

        text.addEventListener('input', () => {
            text.value = text.value.toUpperCase();
            syncColor(textSelector, pickerSelector);
        });
    }

    function updateSocialIconPreview() {
        const className = $('#socialIcon').value.trim() || 'fa-solid fa-link';
        $('#socialIconPreview').innerHTML = `<i class="${escapeHtml(className)}"></i>`;
    }

    form.addEventListener('submit', saveSettings);
    socialForm.addEventListener('submit', saveSocial);

    $('#btnSaveSettingsTop').addEventListener('click', () => {
        form.requestSubmit();
    });

    $('#btnNewSocial').addEventListener('click', resetSocialForm);
    $('#btnConfirmSocialState').addEventListener('click', confirmSocialState);
    $('#socialIcon').addEventListener('input', updateSocialIconPreview);

    socialTableBody.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-action]');

        if (!button) {
            return;
        }

        if (button.dataset.action === 'edit-social') {
            editSocial(button.dataset.id);
        } else if (button.dataset.action === 'state-social') {
            askSocialState(button);
        }
    });

    colorPairs.forEach(([text, picker]) => bindColorPair(text, picker));

    loadSettings();
})();
