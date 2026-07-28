<?php

$csrfToken = (string) ($data['csrfToken'] ?? '');

$messageMeta = $data['moduleMeta'] ?? [
    'eyebrow' => 'INBOX_LINK',
    'title' => 'Mensajes de contacto',
    'description' => 'Consulta y administra las solicitudes enviadas desde el portafolio.',
    'action' => 'Actualizar bandeja',
    'icon' => 'fa-envelope',
];

$escape = isset($adminEscape) && is_callable($adminEscape)
    ? $adminEscape
    : static function ($value): string {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES,
            'UTF-8'
        );
    };

?>

<div
    class="messages-module"
    id="messagesModule"
    data-catalogs-url="<?= $escape(BASE_URL . 'mensajes/catalogos') ?>"
    data-list-url="<?= $escape(BASE_URL . 'mensajes/listar') ?>"
    data-get-url="<?= $escape(BASE_URL . 'mensajes/obtener') ?>"
    data-read-url="<?= $escape(BASE_URL . 'mensajes/marcarLeida') ?>"
    data-status-url="<?= $escape(BASE_URL . 'mensajes/cambiarEstado') ?>"
    data-contact-url="<?= $escape(BASE_URL . 'mensajes/vincularContacto') ?>">

    <section class="module-hero">
        <div class="module-hero__icon">
            <i class="fa-solid <?= $escape(
                $messageMeta['icon'] ?? 'fa-envelope'
            ) ?>"></i>
        </div>

        <div class="module-hero__content">
            <span class="panel-code">
                <?= $escape(
                    $messageMeta['eyebrow'] ?? 'INBOX_LINK'
                ) ?>
            </span>

            <h2>
                <?= $escape(
                    $messageMeta['title']
                        ?? 'Mensajes de contacto'
                ) ?>
            </h2>

            <p>
                <?= $escape(
                    $messageMeta['description']
                        ?? 'Administra las solicitudes recibidas.'
                ) ?>
            </p>
        </div>

        <button
            class="admin-button admin-button--primary"
            id="btnRefreshMessages"
            type="button">
            <i class="fa-solid fa-rotate"></i>
            Actualizar bandeja
        </button>
    </section>

    <section
        class="row g-3 mt-1"
        aria-label="Resumen de mensajes">

        <div class="col-6 col-xl-3">
            <article class="stat-card stat-card--cyan h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon">
                        <i class="fa-solid fa-inbox"></i>
                    </span>
                    <span class="stat-card__signal">TOTAL</span>
                </div>
                <strong id="totalMessages">0</strong>
                <span>Solicitudes recibidas</span>
            </article>
        </div>

        <div class="col-6 col-xl-3">
            <article class="stat-card stat-card--pink h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <span class="stat-card__signal">NEW</span>
                </div>
                <strong id="newMessages">0</strong>
                <span>Mensajes nuevos</span>
            </article>
        </div>

        <div class="col-6 col-xl-3">
            <article class="stat-card stat-card--yellow h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </span>
                    <span class="stat-card__signal">TRACK</span>
                </div>
                <strong id="trackingMessages">0</strong>
                <span>En seguimiento</span>
            </article>
        </div>

        <div class="col-6 col-xl-3">
            <article class="stat-card stat-card--green h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon">
                        <i class="fa-solid fa-paper-plane"></i>
                    </span>
                    <span class="stat-card__signal">DONE</span>
                </div>
                <strong id="answeredMessages">0</strong>
                <span>Respondidos</span>
            </article>
        </div>
    </section>

    <section class="admin-panel mt-3">
        <div
            class="d-flex flex-column gap-4
                   flex-xxl-row align-items-xxl-end
                   justify-content-between">

            <div>
                <span class="panel-code">MESSAGE_DATABASE</span>
                <h2>Bandeja de solicitudes</h2>
                <p class="mb-0 mt-3 text-body-secondary">
                    Revisa el contenido, vincula contactos y controla
                    el avance de cada solicitud sin eliminar registros.
                </p>
            </div>

            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-6 col-xxl-auto">
                    <label
                        class="form-label"
                        for="messageSearch">
                        Buscar
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input
                            class="form-control"
                            id="messageSearch"
                            type="search"
                            placeholder="Nombre, correo, empresa o mensaje..."
                            autocomplete="off">
                    </div>
                </div>

                <div class="col-6 col-md-3 col-xxl-auto">
                    <label
                        class="form-label"
                        for="messageStateFilter">
                        Estado
                    </label>
                    <select
                        class="form-select"
                        id="messageStateFilter">
                        <option value="all">Todos</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xxl-auto">
                    <label
                        class="form-label"
                        for="messageServiceFilter">
                        Servicio
                    </label>
                    <select
                        class="form-select"
                        id="messageServiceFilter">
                        <option value="0">Todos</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xxl-auto">
                    <label
                        class="form-label"
                        for="messageOriginFilter">
                        Origen
                    </label>
                    <select
                        class="form-select"
                        id="messageOriginFilter">
                        <option value="all">Todos</option>
                        <option value="formulario_web">Formulario web</option>
                        <option value="manual">Manual</option>
                        <option value="referido">Referido</option>
                        <option value="otro">Otro</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xxl-auto">
                    <label
                        class="form-label"
                        for="messageDateFrom">
                        Desde
                    </label>
                    <input
                        class="form-control"
                        id="messageDateFrom"
                        type="date">
                </div>

                <div class="col-6 col-md-3 col-xxl-auto">
                    <label
                        class="form-label"
                        for="messageDateTo">
                        Hasta
                    </label>
                    <input
                        class="form-control"
                        id="messageDateTo"
                        type="date">
                </div>

                <div class="col-6 col-md-3 col-xxl-auto">
                    <button
                        class="admin-button admin-button--ghost w-100"
                        id="btnClearMessageFilters"
                        type="button">
                        <i class="fa-solid fa-filter-circle-xmark"></i>
                        Limpiar
                    </button>
                </div>
            </div>
        </div>

        <div
            class="table-responsive mt-4"
            id="messagesTableContainer">

            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">Remitente</th>
                        <th scope="col">Solicitud</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Vinculación</th>
                        <th scope="col">Fecha</th>
                        <th
                            class="text-end"
                            scope="col">
                            Acciones
                        </th>
                    </tr>
                </thead>

                <tbody id="messagesTableBody">
                    <tr>
                        <td
                            class="text-center py-5"
                            colspan="6">
                            <i class="fa-solid fa-circle-notch fa-spin me-2"></i>
                            Cargando mensajes...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            class="empty-state"
            id="messagesEmptyState"
            hidden>
            <span>
                <i class="fa-solid fa-envelope-open"></i>
            </span>
            <strong>No se encontraron mensajes</strong>
            <p>
                Modifica los filtros o espera una nueva solicitud
                desde el formulario público.
            </p>
        </div>
    </section>

    <!-- Detalle -->
    <div
        class="modal fade"
        id="messageDetailModal"
        tabindex="-1"
        aria-labelledby="messageDetailTitle"
        aria-hidden="true">

        <div
            class="modal-dialog modal-xl
                   modal-dialog-centered
                   modal-dialog-scrollable">

            <div class="modal-content admin-panel p-0">
                <div class="modal-header border-bottom">
                    <div>
                        <span class="panel-code">MESSAGE_READER</span>
                        <h2
                            class="modal-title fs-5"
                            id="messageDetailTitle">
                            Detalle del mensaje
                        </h2>
                    </div>

                    <button
                        class="btn-close"
                        type="button"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar">
                    </button>
                </div>

                <div class="modal-body">
                    <input
                        id="messageDetailId"
                        type="hidden"
                        value="0">

                    <div class="row g-4">
                        <div class="col-12 col-lg-8">
                            <article class="admin-panel h-100">
                                <div
                                    class="d-flex flex-column
                                           flex-md-row gap-3
                                           justify-content-between">
                                    <div>
                                        <span class="panel-code">
                                            SUBJECT
                                        </span>
                                        <h3
                                            class="h5 mt-2"
                                            id="messageDetailSubject">
                                        </h3>
                                    </div>

                                    <div id="messageDetailState"></div>
                                </div>

                                <hr>

                                <p
                                    class="mb-0"
                                    id="messageDetailBody"
                                    style="white-space: pre-wrap;">
                                </p>
                            </article>
                        </div>

                        <div class="col-12 col-lg-4">
                            <article class="admin-panel h-100">
                                <span class="panel-code">SENDER_PROFILE</span>

                                <h3
                                    class="h5 mt-2 mb-3"
                                    id="messageDetailName">
                                </h3>

                                <dl class="row mb-0 small">
                                    <dt class="col-4">Correo</dt>
                                    <dd
                                        class="col-8 text-break"
                                        id="messageDetailEmail">
                                    </dd>

                                    <dt class="col-4">Teléfono</dt>
                                    <dd
                                        class="col-8"
                                        id="messageDetailPhone">
                                    </dd>

                                    <dt class="col-4">Empresa</dt>
                                    <dd
                                        class="col-8"
                                        id="messageDetailCompany">
                                    </dd>

                                    <dt class="col-4">Servicio</dt>
                                    <dd
                                        class="col-8"
                                        id="messageDetailService">
                                    </dd>

                                    <dt class="col-4">Fecha</dt>
                                    <dd
                                        class="col-8"
                                        id="messageDetailPreferredDate">
                                    </dd>

                                    <dt class="col-4">Modalidad</dt>
                                    <dd
                                        class="col-8"
                                        id="messageDetailMode">
                                    </dd>
                                </dl>
                            </article>
                        </div>

                        <div class="col-12 col-lg-6">
                            <article class="admin-panel h-100">
                                <span class="panel-code">CRM_LINK</span>
                                <h3 class="h6 mt-2">Cliente y contacto</h3>

                                <p
                                    class="mb-3 text-body-secondary"
                                    id="messageDetailLinkedContact">
                                </p>

                                <button
                                    class="admin-button admin-button--ghost"
                                    id="btnLinkMessageContact"
                                    type="button">
                                    <i class="fa-solid fa-link"></i>
                                    Vincular contacto
                                </button>
                            </article>
                        </div>

                        <div class="col-12 col-lg-6">
                            <article class="admin-panel h-100">
                                <span class="panel-code">TRACE_DATA</span>
                                <h3 class="h6 mt-2">Información técnica</h3>

                                <dl class="row mb-0 small">
                                    <dt class="col-4">Origen</dt>
                                    <dd
                                        class="col-8"
                                        id="messageDetailOrigin">
                                    </dd>

                                    <dt class="col-4">Dirección IP</dt>
                                    <dd
                                        class="col-8"
                                        id="messageDetailIp">
                                    </dd>

                                    <dt class="col-4">Recibido</dt>
                                    <dd
                                        class="col-8"
                                        id="messageDetailCreated">
                                    </dd>

                                    <dt class="col-4">Cita</dt>
                                    <dd
                                        class="col-8"
                                        id="messageDetailAppointment">
                                    </dd>
                                </dl>
                            </article>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button
                        class="admin-button admin-button--ghost"
                        id="btnCopyMessage"
                        type="button">
                        <i class="fa-solid fa-copy"></i>
                        Copiar mensaje
                    </button>

                    <a
                        class="admin-button admin-button--ghost"
                        id="btnMessageWhatsapp"
                        href="#"
                        target="_blank"
                        rel="noopener noreferrer"
                        hidden>
                        <i class="fa-brands fa-whatsapp"></i>
                        WhatsApp
                    </a>

                    <a
                        class="admin-button admin-button--primary"
                        id="btnReplyMessage"
                        href="#">
                        <i class="fa-solid fa-reply"></i>
                        Responder por correo
                    </a>

                    <button
                        class="admin-button admin-button--primary"
                        id="btnChangeMessageState"
                        type="button">
                        <i class="fa-solid fa-list-check"></i>
                        Cambiar estado
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado -->
    <div
        class="modal fade"
        id="messageStateModal"
        tabindex="-1"
        aria-labelledby="messageStateModalTitle"
        aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">
            <form
                class="modal-content admin-panel p-0"
                id="messageStateForm">

                <input
                    name="csrf_token"
                    type="hidden"
                    value="<?= $escape($csrfToken) ?>">

                <input
                    id="messageStateId"
                    name="id_solicitud_contacto"
                    type="hidden"
                    value="0">

                <div class="modal-header border-bottom">
                    <div>
                        <span class="panel-code">WORKFLOW_STATE</span>
                        <h2
                            class="modal-title fs-5"
                            id="messageStateModalTitle">
                            Cambiar estado
                        </h2>
                    </div>

                    <button
                        class="btn-close"
                        type="button"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar">
                    </button>
                </div>

                <div class="modal-body">
                    <label
                        class="form-label"
                        for="messageState">
                        Nuevo estado
                    </label>

                    <select
                        class="form-select"
                        id="messageState"
                        name="estado"
                        required>
                    </select>
                </div>

                <div class="modal-footer border-top">
                    <button
                        class="admin-button admin-button--ghost"
                        type="button"
                        data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button
                        class="admin-button admin-button--primary"
                        id="btnSaveMessageState"
                        type="submit">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Guardar estado
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Vincular contacto -->
    <div
        class="modal fade"
        id="messageContactModal"
        tabindex="-1"
        aria-labelledby="messageContactModalTitle"
        aria-hidden="true">

        <div
            class="modal-dialog modal-lg
                   modal-dialog-centered">

            <form
                class="modal-content admin-panel p-0"
                id="messageContactForm">

                <input
                    name="csrf_token"
                    type="hidden"
                    value="<?= $escape($csrfToken) ?>">

                <input
                    id="messageContactMessageId"
                    name="id_solicitud_contacto"
                    type="hidden"
                    value="0">

                <div class="modal-header border-bottom">
                    <div>
                        <span class="panel-code">CRM_BINDING</span>
                        <h2
                            class="modal-title fs-5"
                            id="messageContactModalTitle">
                            Vincular contacto
                        </h2>
                    </div>

                    <button
                        class="btn-close"
                        type="button"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar">
                    </button>
                </div>

                <div class="modal-body">
                    <label
                        class="form-label"
                        for="messageContact">
                        Contacto registrado
                    </label>

                    <select
                        class="form-select"
                        id="messageContact"
                        name="id_contacto_cliente">
                        <option value="0">
                            Sin contacto vinculado
                        </option>
                    </select>

                    <div class="form-text mt-2">
                        Solo aparecen contactos activos pertenecientes
                        a clientes disponibles.
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <a
                        class="admin-button admin-button--ghost"
                        href="<?= $escape(BASE_URL . 'admin/clientes') ?>">
                        <i class="fa-solid fa-building"></i>
                        Administrar clientes
                    </a>

                    <button
                        class="admin-button admin-button--primary"
                        id="btnSaveMessageContact"
                        type="submit">
                        <i class="fa-solid fa-link"></i>
                        Guardar vinculación
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div
        class="toast-container position-fixed
               bottom-0 end-0 p-3">
        <div
            class="toast"
            id="messageNotification"
            role="status"
            aria-live="polite"
            aria-atomic="true">

            <div class="toast-header">
                <i
                    class="fa-solid fa-circle-check
                           text-success me-2"
                    id="messageNotificationIcon">
                </i>

                <strong
                    class="me-auto"
                    id="messageNotificationTitle">
                    Operación completada
                </strong>

                <button
                    class="btn-close"
                    type="button"
                    data-bs-dismiss="toast"
                    aria-label="Cerrar">
                </button>
            </div>

            <div
                class="toast-body"
                id="messageNotificationBody">
            </div>
        </div>
    </div>
</div>
