<?php

$csrfToken = (string) ($data['csrfToken'] ?? '');
$meta = $data['moduleMeta'] ?? [
    'eyebrow' => 'CLIENT_DATABASE',
    'title' => 'Gestión de clientes',
    'description' => 'Administra las empresas y personas vinculadas con tus proyectos.',
    'action' => 'Nuevo cliente',
    'icon' => 'fa-building-user',
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

<style>
    #clientModal .modal-content {
        max-height: calc(100vh - 2rem);
        max-height: calc(100dvh - 2rem);
        overflow: hidden;
    }

    #clientModal #clientForm {
        display: flex;
        min-height: 0;
        max-height: calc(100vh - 2rem);
        max-height: calc(100dvh - 2rem);
        flex-direction: column;
        overflow: hidden;
    }

    #clientModal .modal-body {
        min-height: 0;
        flex: 1 1 auto;
        overflow-y: auto;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
    }
</style>

<div
    id="clientsModule"
    data-list-url="<?= $escape(BASE_URL . 'clientes/listar') ?>"
    data-get-url="<?= $escape(BASE_URL . 'clientes/obtener') ?>"
    data-save-url="<?= $escape(BASE_URL . 'clientes/guardar') ?>"
    data-state-url="<?= $escape(BASE_URL . 'clientes/cambiarEstado') ?>"
    data-record-url="<?= $escape(BASE_URL . 'clientes/cambiarBaja') ?>">

    <section class="module-hero">
        <div class="module-hero__icon">
            <i class="fa-solid <?= $escape($meta['icon'] ?? 'fa-building-user') ?>"></i>
        </div>

        <div class="module-hero__content">
            <span class="panel-code">
                <?= $escape($meta['eyebrow'] ?? 'CLIENT_DATABASE') ?>
            </span>

            <h2>
                <?= $escape($meta['title'] ?? 'Gestión de clientes') ?>
            </h2>

            <p>
                <?= $escape(
                    $meta['description']
                    ?? 'Administra los clientes vinculados con tus proyectos.'
                ) ?>
            </p>
        </div>

        <button
            class="admin-button admin-button--primary"
            id="btnNewClient"
            type="button">
            <i class="fa-solid fa-plus"></i>
            <?= $escape($meta['action'] ?? 'Nuevo cliente') ?>
        </button>
    </section>

    <section class="row g-3 mt-1" aria-label="Resumen de clientes">
        <div class="col-12 col-sm-6 col-xl-3">
            <article class="stat-card stat-card--cyan h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon">
                        <i class="fa-solid fa-address-book"></i>
                    </span>
                    <span class="stat-card__signal">TOTAL</span>
                </div>
                <strong id="totalClients">0</strong>
                <span>Clientes registrados</span>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="stat-card stat-card--green h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>
                    <span class="stat-card__signal">ONLINE</span>
                </div>
                <strong id="activeClients">0</strong>
                <span>Clientes activos</span>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="stat-card stat-card--yellow h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon">
                        <i class="fa-solid fa-circle-pause"></i>
                    </span>
                    <span class="stat-card__signal">PAUSED</span>
                </div>
                <strong id="inactiveClients">0</strong>
                <span>Clientes inactivos</span>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="stat-card stat-card--pink h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon">
                        <i class="fa-solid fa-box-archive"></i>
                    </span>
                    <span class="stat-card__signal">OFFLINE</span>
                </div>
                <strong id="deletedClients">0</strong>
                <span>Clientes dados de baja</span>
            </article>
        </div>
    </section>

    <section class="admin-panel mt-3">
        <div class="d-flex flex-column flex-xxl-row gap-4 align-items-xxl-end justify-content-between">
            <div>
                <span class="panel-code">CLIENT_DIRECTORY</span>
                <h2>Directorio de clientes</h2>
                <p class="mb-0 mt-3 text-body-secondary">
                    Los clientes activos aparecerán automáticamente en el formulario de Proyectos.
                </p>
            </div>

            <div class="row g-2 align-items-end">
                <div class="col-12 col-lg">
                    <label class="form-label" for="clientSearch">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input
                            class="form-control"
                            id="clientSearch"
                            type="search"
                            placeholder="Nombre, razón social, correo o teléfono..."
                            autocomplete="off">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-auto">
                    <label class="form-label" for="clientTypeFilter">Tipo</label>
                    <select class="form-select" id="clientTypeFilter">
                        <option value="all">Todos</option>
                        <option value="empresa">Empresa</option>
                        <option value="persona">Persona</option>
                        <option value="organizacion">Organización</option>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-lg-auto">
                    <label class="form-label" for="clientStateFilter">Estado</label>
                    <select class="form-select" id="clientStateFilter">
                        <option value="all">Todos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-lg-auto">
                    <label class="form-label" for="clientRecordFilter">Disponibilidad</label>
                    <select class="form-select" id="clientRecordFilter">
                        <option value="active">Disponibles</option>
                        <option value="deleted">Dados de baja</option>
                        <option value="all">Todos</option>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-lg-auto">
                    <button
                        class="admin-button admin-button--ghost w-100"
                        id="btnRefreshClients"
                        type="button">
                        <i class="fa-solid fa-rotate"></i>
                        Actualizar
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive mt-4">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Contacto</th>
                        <th>Proyectos</th>
                        <th>Estado</th>
                        <th>Actualización</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="clientsTableBody">
                    <tr>
                        <td class="text-center py-5" colspan="7">
                            <i class="fa-solid fa-circle-notch fa-spin me-2"></i>
                            Cargando clientes...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="empty-state mt-4" id="clientsEmptyState" hidden>
            <span><i class="fa-solid fa-building-user"></i></span>
            <strong>No se encontraron clientes</strong>
            <p>Registra un cliente o modifica los filtros de búsqueda.</p>
        </div>
    </section>

    <div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content admin-panel p-0">
                <form id="clientForm" novalidate>
                    <input id="clientId" name="id_cliente" type="hidden" value="0">
                    <input name="csrf_token" type="hidden" value="<?= $escape($csrfToken) ?>">

                    <div class="modal-header border-bottom">
                        <div>
                            <span class="panel-code">CLIENT_EDITOR</span>
                            <h2 class="modal-title fs-5" id="clientModalTitle">
                                Nuevo cliente
                            </h2>
                            <p class="mb-0 mt-2 text-body-secondary">
                                Registra la información general utilizada para identificar al cliente.
                            </p>
                        </div>
                        <button
                            class="btn-close"
                            type="button"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar">
                        </button>
                    </div>

                    <div class="modal-body">
                        <div
                            class="alert alert-danger d-none"
                            id="clientFormFeedback"
                            role="alert">
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label" for="clientType">
                                    Tipo de cliente *
                                </label>
                                <select
                                    class="form-select"
                                    id="clientType"
                                    name="tipo_cliente"
                                    required>
                                    <option value="empresa">Empresa</option>
                                    <option value="persona">Persona</option>
                                    <option value="organizacion">Organización</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-8">
                                <label class="form-label" for="clientName">
                                    Nombre para mostrar *
                                </label>
                                <input
                                    class="form-control"
                                    id="clientName"
                                    name="nombre_mostrar"
                                    maxlength="150"
                                    placeholder="Ejemplo: PacificNort"
                                    required>
                                <div class="form-text">
                                    Este nombre aparecerá en el módulo de Proyectos.
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="clientLegalName">
                                    Razón social
                                </label>
                                <input
                                    class="form-control"
                                    id="clientLegalName"
                                    name="razon_social"
                                    maxlength="180"
                                    placeholder="Ejemplo: PacificNort Logistics, S.A. de C.V.">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="clientEmail">
                                    Correo electrónico
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fa-solid fa-envelope"></i>
                                    </span>
                                    <input
                                        class="form-control"
                                        id="clientEmail"
                                        name="correo"
                                        type="email"
                                        maxlength="150"
                                        placeholder="contacto@empresa.com">
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="clientPhone">
                                    Teléfono
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fa-solid fa-phone"></i>
                                    </span>
                                    <input
                                        class="form-control"
                                        id="clientPhone"
                                        name="telefono"
                                        type="tel"
                                        maxlength="30"
                                        placeholder="+52 664 000 0000">
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <label class="form-label" for="clientWebsite">
                                    Sitio web
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fa-solid fa-globe"></i>
                                    </span>
                                    <input
                                        class="form-control"
                                        id="clientWebsite"
                                        name="sitio_web"
                                        type="url"
                                        maxlength="500"
                                        placeholder="https://empresa.com">
                                </div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <label class="form-label" for="clientLogo">
                                    Ruta o URL del logotipo
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fa-solid fa-image"></i>
                                    </span>
                                    <input
                                        class="form-control"
                                        id="clientLogo"
                                        name="ruta_logotipo"
                                        maxlength="255"
                                        placeholder="Assets/img/clientes/logo.png">
                                </div>
                                <div class="form-text">
                                    La carga de archivos puede integrarse posteriormente.
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-between gap-3">
                                    <label class="form-label" for="clientNotes">
                                        Notas internas
                                    </label>
                                    <small
                                        class="text-body-secondary"
                                        id="clientNotesCounter">
                                        0 / 5000
                                    </small>
                                </div>
                                <textarea
                                    class="form-control"
                                    id="clientNotes"
                                    name="notas"
                                    rows="5"
                                    maxlength="5000"
                                    placeholder="Información relevante para la administración del cliente."></textarea>
                                <div class="form-text">
                                    Estas notas no deben mostrarse públicamente.
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input
                                        class="form-check-input"
                                        id="clientActive"
                                        name="activo"
                                        type="checkbox"
                                        role="switch"
                                        value="1"
                                        checked>
                                    <label class="form-check-label" for="clientActive">
                                        Cliente activo
                                    </label>
                                    <div class="form-text">
                                        Solo los clientes activos y disponibles aparecen al registrar proyectos.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button
                            class="admin-button admin-button--ghost"
                            type="button"
                            data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark"></i>
                            Cancelar
                        </button>

                        <button
                            class="admin-button admin-button--primary"
                            id="btnSaveClient"
                            type="submit">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Guardar cliente</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="clientActionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content admin-panel p-0">
                <div class="modal-header border-bottom">
                    <div>
                        <span class="panel-code">CLIENT_ACTION</span>
                        <h2 class="modal-title fs-5" id="clientActionTitle">
                            Confirmar operación
                        </h2>
                    </div>
                    <button
                        class="btn-close"
                        type="button"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar">
                    </button>
                </div>

                <div class="modal-body text-center py-5">
                    <div class="fs-1 text-warning mb-3" id="clientActionIcon">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <p class="mb-0" id="clientActionMessage">
                        ¿Deseas realizar esta operación?
                    </p>
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
                        id="btnConfirmClientAction"
                        type="button">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div
            class="toast"
            id="clientNotification"
            role="status"
            aria-live="polite"
            aria-atomic="true">
            <div class="toast-header">
                <i
                    class="fa-solid fa-circle-check text-success me-2"
                    id="clientNotificationIcon">
                </i>
                <strong class="me-auto" id="clientNotificationTitle">
                    Operación completada
                </strong>
                <button
                    class="btn-close"
                    type="button"
                    data-bs-dismiss="toast"
                    aria-label="Cerrar">
                </button>
            </div>
            <div class="toast-body" id="clientNotificationMessage"></div>
        </div>
    </div>
</div>
