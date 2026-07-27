<?php

require_once dirname(__DIR__) . '/Template/admin-header.php';

$csrfToken = (string) ($data['csrfToken'] ?? '');
$meta = $data['moduleMeta'] ?? [];
$escape = $adminEscape;

?>

<div
    id="technologiesModule"
    data-list-url="<?= $escape(BASE_URL . 'tecnologias/listar') ?>"
    data-get-url="<?= $escape(BASE_URL . 'tecnologias/obtener') ?>"
    data-save-url="<?= $escape(BASE_URL . 'tecnologias/guardar') ?>"
    data-status-url="<?= $escape(BASE_URL . 'tecnologias/cambiarEstado') ?>">

    <section class="module-hero">
        <div class="module-hero__icon">
            <i class="fa-solid <?= $escape($meta['icon'] ?? 'fa-microchip') ?>"></i>
        </div>

        <div class="module-hero__content">
            <span class="panel-code"><?= $escape($meta['eyebrow'] ?? 'TECH_STACK') ?></span>
            <h2><?= $escape($meta['title'] ?? 'Catálogo de tecnologías') ?></h2>
            <p><?= $escape($meta['description'] ?? 'Administra el stack utilizado en tus proyectos.') ?></p>
        </div>

        <button
            class="admin-button admin-button--primary"
            id="btnNewTechnology"
            type="button"
            data-bs-toggle="modal"
            data-bs-target="#technologyModal">
            <i class="fa-solid fa-plus"></i>
            Nueva tecnología
        </button>
    </section>

    <section class="row g-3 mt-1" aria-label="Resumen de tecnologías">
        <div class="col-12 col-md-4">
            <article class="stat-card stat-card--cyan h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon"><i class="fa-solid fa-microchip"></i></span>
                    <span class="stat-card__signal">TOTAL</span>
                </div>
                <strong id="totalTechnologies">0</strong>
                <span>Tecnologías registradas</span>
            </article>
        </div>

        <div class="col-12 col-md-4">
            <article class="stat-card stat-card--green h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon"><i class="fa-solid fa-circle-check"></i></span>
                    <span class="stat-card__signal">ONLINE</span>
                </div>
                <strong id="activeTechnologies">0</strong>
                <span>Tecnologías activas</span>
            </article>
        </div>

        <div class="col-12 col-md-4">
            <article class="stat-card stat-card--pink h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon"><i class="fa-solid fa-circle-pause"></i></span>
                    <span class="stat-card__signal">OFFLINE</span>
                </div>
                <strong id="inactiveTechnologies">0</strong>
                <span>Tecnologías inactivas</span>
            </article>
        </div>
    </section>

    <section class="admin-panel mt-3">
        <div class="d-flex flex-column flex-xl-row gap-4 align-items-xl-end justify-content-between">
            <div>
                <span class="panel-code">TECH_DATABASE</span>
                <h2>Stack tecnológico</h2>
                <p class="mb-0 mt-3 text-body-secondary">
                    Crea, edita y da de baja lógica a las tecnologías disponibles para tus proyectos.
                </p>
            </div>

            <div class="row g-2 align-items-end">
                <div class="col-12 col-md">
                    <label class="form-label" for="technologySearch">Buscar</label>
                    <input
                        class="form-control"
                        id="technologySearch"
                        type="search"
                        placeholder="Nombre, slug o área..."
                        autocomplete="off">
                </div>

                <div class="col-12 col-sm-7 col-md-auto">
                    <label class="form-label" for="technologyStatusFilter">Estado</label>
                    <select class="form-select" id="technologyStatusFilter">
                        <option value="all">Todas</option>
                        <option value="active">Activas</option>
                        <option value="inactive">Inactivas</option>
                    </select>
                </div>

                <div class="col-12 col-sm-5 col-md-auto">
                    <button
                        class="admin-button admin-button--ghost w-100"
                        id="btnRefreshTechnologies"
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
                        <th>Orden</th>
                        <th>Tecnología</th>
                        <th>Slug</th>
                        <th>Icono</th>
                        <th>Estado</th>
                        <th>Actualización</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="technologiesTableBody">
                    <tr>
                        <td class="text-center py-5" colspan="7">
                            <i class="fa-solid fa-circle-notch fa-spin me-2"></i>
                            Cargando tecnologías...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="empty-state mt-4" id="technologiesEmptyState" hidden>
            <span><i class="fa-solid fa-microchip"></i></span>
            <strong>No se encontraron tecnologías</strong>
            <p>Registra una nueva tecnología o cambia los filtros de búsqueda.</p>
        </div>
    </section>

    <div class="modal fade" id="technologyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content admin-panel p-0">
                <form id="technologyForm" novalidate>
                    <input id="technologyId" name="id_tecnologia" type="hidden" value="0">
                    <input name="csrf_token" type="hidden" value="<?= $escape($csrfToken) ?>">

                    <div class="modal-header border-bottom">
                        <div>
                            <span class="panel-code">TECH_EDITOR</span>
                            <h2 class="modal-title fs-5" id="technologyModalTitle">Nueva tecnología</h2>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-danger d-none" id="technologyFormFeedback"></div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label" for="technologyName">Nombre *</label>
                                <input class="form-control" id="technologyName" name="nombre" maxlength="100" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="technologySlug">Slug *</label>
                                <div class="input-group">
                                    <input
                                        class="form-control"
                                        id="technologySlug"
                                        name="slug"
                                        maxlength="120"
                                        pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                                        required>
                                    <button class="btn btn-outline-secondary" id="btnGenerateTechnologySlug" type="button">
                                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="technologyArea">Nivel o área</label>
                                <input
                                    class="form-control"
                                    id="technologyArea"
                                    name="nivel_o_area"
                                    maxlength="80"
                                    placeholder="Frontend, Backend, DevOps...">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="technologyOrder">Orden</label>
                                <input
                                    class="form-control"
                                    id="technologyOrder"
                                    name="orden_visualizacion"
                                    type="number"
                                    min="0"
                                    step="1"
                                    placeholder="Automático">
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="technologyColor">Color hexadecimal *</label>
                                <div class="input-group">
                                    <input class="form-control form-control-color" id="technologyColorPicker" type="color" value="#00F6FF">
                                    <input
                                        class="form-control"
                                        id="technologyColor"
                                        name="color_hexadecimal"
                                        maxlength="7"
                                        pattern="#[0-9A-Fa-f]{6}"
                                        value="#00F6FF"
                                        required>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="technologyIconType">Tipo de icono *</label>
                                <select class="form-select" id="technologyIconType" name="tipo_icono" required>
                                    <option value="fontawesome">Font Awesome</option>
                                    <option value="imagen">URL o ruta de imagen</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="technologyIconValue" id="technologyIconValueLabel">
                                    Clase de Font Awesome *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text" id="technologyIconPreview">
                                        <i class="fa-solid fa-code"></i>
                                    </span>
                                    <input
                                        class="form-control"
                                        id="technologyIconValue"
                                        name="valor_icono"
                                        maxlength="500"
                                        placeholder="fa-brands fa-php"
                                        required>
                                </div>
                                <div class="form-text" id="technologyIconHelp">
                                    Ejemplo: fa-brands fa-php
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input
                                        class="form-check-input"
                                        id="technologyActive"
                                        name="activa"
                                        type="checkbox"
                                        role="switch"
                                        value="1"
                                        checked>
                                    <label class="form-check-label" for="technologyActive">
                                        Tecnología activa
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button class="admin-button admin-button--ghost" type="button" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button class="admin-button admin-button--primary" id="btnSaveTechnology" type="submit">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Guardar tecnología
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="technologyStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content admin-panel p-0">
                <div class="modal-header border-bottom">
                    <h2 class="modal-title fs-5">Cambiar estado</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center py-5">
                    <i class="fa-solid fa-triangle-exclamation fs-1 text-warning mb-3"></i>
                    <p class="mb-0" id="technologyStatusMessage"></p>
                </div>
                <div class="modal-footer border-top">
                    <button class="admin-button admin-button--ghost" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="admin-button admin-button--primary" id="btnConfirmTechnologyStatus" type="button">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast" id="technologyNotification" role="status" aria-live="polite" aria-atomic="true">
            <div class="toast-header">
                <i class="fa-solid fa-circle-info me-2" id="technologyNotificationIcon"></i>
                <strong class="me-auto">Tecnologías</strong>
                <button class="btn-close" type="button" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
            <div class="toast-body" id="technologyNotificationMessage"></div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/Template/admin-footer.php'; ?>
