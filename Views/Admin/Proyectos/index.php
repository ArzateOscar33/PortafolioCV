<?php

require_once dirname(__DIR__) . '/Template/admin-header.php';

$csrfToken = (string) ($data['csrfToken'] ?? '');
$meta = $data['moduleMeta'] ?? [];
$escape = $adminEscape;

?>

<div
    id="projectsModule"
    data-list-url="<?= $escape(BASE_URL . 'proyectos/listar') ?>"
    data-catalog-url="<?= $escape(BASE_URL . 'proyectos/catalogos') ?>"
    data-get-url="<?= $escape(BASE_URL . 'proyectos/obtener') ?>"
    data-save-url="<?= $escape(BASE_URL . 'proyectos/guardar') ?>"
    data-status-url="<?= $escape(BASE_URL . 'proyectos/cambiarBaja') ?>">

    <section class="module-hero">
        <div class="module-hero__icon">
            <i class="fa-solid <?= $escape($meta['icon'] ?? 'fa-diagram-project') ?>"></i>
        </div>

        <div class="module-hero__content">
            <span class="panel-code"><?= $escape($meta['eyebrow'] ?? 'PORTFOLIO_CORE') ?></span>
            <h2><?= $escape($meta['title'] ?? 'Gestión de proyectos') ?></h2>
            <p><?= $escape($meta['description'] ?? 'Administra los proyectos publicados en el portafolio.') ?></p>
        </div>

        <button
            class="admin-button admin-button--primary"
            id="btnNewProject"
            type="button">
            <i class="fa-solid fa-plus"></i>
            <?= $escape($meta['action'] ?? 'Nuevo proyecto') ?>
        </button>
    </section>

    <section class="row g-3 mt-1" aria-label="Resumen de proyectos">
        <div class="col-12 col-sm-6 col-xl-3">
            <article class="stat-card stat-card--cyan h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon"><i class="fa-solid fa-layer-group"></i></span>
                    <span class="stat-card__signal">TOTAL</span>
                </div>
                <strong id="totalProjects">0</strong>
                <span>Proyectos registrados</span>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="stat-card stat-card--green h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon"><i class="fa-solid fa-circle-check"></i></span>
                    <span class="stat-card__signal">ONLINE</span>
                </div>
                <strong id="activeProjects">0</strong>
                <span>Proyectos disponibles</span>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="stat-card stat-card--yellow h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon"><i class="fa-solid fa-globe"></i></span>
                    <span class="stat-card__signal">PUBLIC</span>
                </div>
                <strong id="publishedProjects">0</strong>
                <span>Proyectos publicados</span>
            </article>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <article class="stat-card stat-card--pink h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon"><i class="fa-solid fa-box-archive"></i></span>
                    <span class="stat-card__signal">OFFLINE</span>
                </div>
                <strong id="deletedProjects">0</strong>
                <span>Proyectos dados de baja</span>
            </article>
        </div>
    </section>

    <section class="admin-panel mt-3">
        <div class="d-flex flex-column flex-xxl-row gap-4 align-items-xxl-end justify-content-between">
            <div>
                <span class="panel-code">PROJECT_DATABASE</span>
                <h2>Catálogo de proyectos</h2>
                <p class="mb-0 mt-3 text-body-secondary">
                    Gestiona contenido, clasificación, stack tecnológico y publicación.
                </p>
            </div>

            <div class="row g-2 align-items-end">
                <div class="col-12 col-lg">
                    <label class="form-label" for="projectSearch">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input
                            class="form-control"
                            id="projectSearch"
                            type="search"
                            placeholder="Título, slug, resumen o cliente..."
                            autocomplete="off">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-auto">
                    <label class="form-label" for="projectStateFilter">Estado editorial</label>
                    <select class="form-select" id="projectStateFilter">
                        <option value="0">Todos</option>
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-lg-auto">
                    <label class="form-label" for="projectRecordFilter">Disponibilidad</label>
                    <select class="form-select" id="projectRecordFilter">
                        <option value="active">Disponibles</option>
                        <option value="deleted">Dados de baja</option>
                        <option value="all">Todos</option>
                    </select>
                </div>

                <div class="col-12 col-lg-auto">
                    <button class="admin-button admin-button--ghost w-100" id="btnRefreshProjects" type="button">
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
                        <th>Proyecto</th>
                        <th>Estado</th>
                        <th>Categorías</th>
                        <th>Tecnologías</th>
                        <th>Destacado</th>
                        <th>Actualización</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="projectsTableBody">
                    <tr>
                        <td class="text-center py-5" colspan="7">
                            <i class="fa-solid fa-circle-notch fa-spin me-2"></i>
                            Cargando proyectos...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="empty-state mt-4" id="projectsEmptyState" hidden>
            <span><i class="fa-solid fa-diagram-project"></i></span>
            <strong>No se encontraron proyectos</strong>
            <p>Registra un proyecto o modifica los filtros de búsqueda.</p>
        </div>
    </section>

    <div class="modal fade" id="projectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content admin-panel p-0">
                <form id="projectForm" novalidate>
                    <input id="projectId" name="id_proyecto" type="hidden" value="0">
                    <input name="csrf_token" type="hidden" value="<?= $escape($csrfToken) ?>">

                    <div class="modal-header border-bottom">
                        <div>
                            <span class="panel-code">PROJECT_EDITOR</span>
                            <h2 class="modal-title fs-5" id="projectModalTitle">Nuevo proyecto</h2>
                            <p class="mb-0 mt-2 text-body-secondary">
                                Configura la información general, clasificación, tecnologías
                                y enlaces públicos o privados del proyecto. La galería y los
                                videos se administran desde el módulo multimedia.
                            </p>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-danger d-none" id="projectFormFeedback"></div>

                        <div class="row g-3">
                            <div class="col-12 col-lg-7">
                                <label class="form-label" for="projectTitle">Título *</label>
                                <input
                                    class="form-control"
                                    id="projectTitle"
                                    name="titulo"
                                    maxlength="180"
                                    placeholder="Ejemplo: PacificNort Suite"
                                    required>
                            </div>

                            <div class="col-12 col-lg-5">
                                <label class="form-label" for="projectSlug">Slug *</label>
                                <div class="input-group">
                                    <input
                                        class="form-control"
                                        id="projectSlug"
                                        name="slug"
                                        maxlength="180"
                                        pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                                        required>
                                    <button class="btn btn-outline-secondary" id="btnGenerateProjectSlug" type="button" title="Generar slug">
                                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex justify-content-between gap-3">
                                    <label class="form-label" for="projectSummary">Resumen corto *</label>
                                    <small class="text-body-secondary" id="projectSummaryCounter">0 / 350</small>
                                </div>
                                <textarea
                                    class="form-control"
                                    id="projectSummary"
                                    name="resumen_corto"
                                    maxlength="350"
                                    rows="3"
                                    required></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="projectDescription">Descripción completa *</label>
                                <textarea
                                    class="form-control"
                                    id="projectDescription"
                                    name="descripcion"
                                    rows="7"
                                    placeholder="Explica el objetivo, arquitectura y funcionamiento del proyecto."
                                    required></textarea>
                            </div>

                            <div class="col-12 col-lg-6">
                                <label class="form-label" for="projectChallenge">Reto técnico</label>
                                <textarea
                                    class="form-control"
                                    id="projectChallenge"
                                    name="reto_tecnico"
                                    rows="5"
                                    placeholder="Problema técnico principal y restricciones."></textarea>
                            </div>

                            <div class="col-12 col-lg-6">
                                <label class="form-label" for="projectResult">Resultado</label>
                                <textarea
                                    class="form-control"
                                    id="projectResult"
                                    name="resultado"
                                    rows="5"
                                    placeholder="Impacto, mejoras o resultado conseguido."></textarea>
                            </div>

                            <!-- =====================================================
                                 ENLACES DEL PROYECTO
                            ====================================================== -->
                            <div class="col-12">
                                <section
                                    class="border rounded-3 p-3 p-lg-4"
                                    id="projectLinksSection"
                                    aria-labelledby="projectLinksTitle">

                                    <div
                                        class="d-flex flex-column flex-lg-row gap-3
                                               align-items-lg-start justify-content-between">

                                        <div>
                                            <span class="panel-code">PROJECT_LINKS</span>

                                            <h3
                                                class="fs-5 mb-2"
                                                id="projectLinksTitle">
                                                Enlaces del proyecto
                                            </h3>

                                            <p class="mb-0 text-body-secondary">
                                                Agrega el repositorio, sitio publicado,
                                                demostración, documentación o archivos
                                                de descarga disponibles.
                                            </p>
                                        </div>

                                        <button
                                            class="admin-button admin-button--ghost
                                                   flex-shrink-0"
                                            id="btnAddProjectLink"
                                            type="button">

                                            <i class="fa-solid fa-plus"></i>
                                            Agregar enlace
                                        </button>
                                    </div>

                                    <div
                                        class="alert alert-info mt-3 mb-0"
                                        role="note">

                                        <i
                                            class="fa-solid fa-circle-info me-2"
                                            aria-hidden="true">
                                        </i>

                                        Los enlaces públicos aparecerán como botones
                                        dentro del modal del proyecto. Los privados
                                        conservarán el registro, pero ocultarán la URL.
                                    </div>

                                    <div
                                        class="empty-state mt-3"
                                        id="projectLinksEmptyState">

                                        <span>
                                            <i class="fa-solid fa-link"></i>
                                        </span>

                                        <strong>Sin enlaces registrados</strong>

                                        <p class="mb-0">
                                            Puedes guardar el proyecto sin enlaces
                                            y agregarlos posteriormente.
                                        </p>
                                    </div>

                                    <div
                                        class="d-grid gap-3 mt-3"
                                        id="projectLinksList"
                                        aria-live="polite">
                                    </div>
                                </section>

                                <!--
                                    Compatibilidad temporal con proyectos.js anterior.

                                    Estos campos se eliminarán cuando el JavaScript
                                    deje de utilizar url_github y github_privado.
                                -->
                                <input
                                    id="projectGithub"
                                    name="url_github"
                                    type="hidden"
                                    value="">

                                <input
                                    id="projectGithubPrivate"
                                    name="github_privado"
                                    type="hidden"
                                    value="0">
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label" for="projectState">Estado *</label>
                                <select class="form-select" id="projectState" name="id_estado_proyecto" required></select>
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label" for="projectClient">Cliente</label>
                                <select class="form-select" id="projectClient" name="id_cliente">
                                    <option value="0">Proyecto personal / sin cliente</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label" for="projectStartDate">Fecha de inicio</label>
                                <input class="form-control" id="projectStartDate" name="fecha_inicio" type="date">
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label" for="projectEndDate">Fecha de finalización</label>
                                <input class="form-control" id="projectEndDate" name="fecha_fin" type="date">
                            </div>

                            <div class="col-12 col-lg-6">
                                <label class="form-label" for="projectCategories">Categorías *</label>
                                <select
                                    class="form-select"
                                    id="projectCategories"
                                    name="categorias[]"
                                    size="7"
                                    multiple
                                    required></select>
                                <div class="form-text">Puedes seleccionar varias categorías.</div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <label class="form-label" for="projectTechnologies">Tecnologías *</label>
                                <select
                                    class="form-select"
                                    id="projectTechnologies"
                                    name="tecnologias[]"
                                    size="7"
                                    multiple
                                    required></select>
                                <div class="form-text">El orden del catálogo se conserva como orden del stack.</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label" for="projectOrder">Orden de visualización</label>
                                <input
                                    class="form-control"
                                    id="projectOrder"
                                    name="orden_visualizacion"
                                    type="number"
                                    min="0"
                                    step="1"
                                    placeholder="Automático">
                            </div>

                            <div class="col-12 col-md-6 d-flex align-items-center">
                                <div class="form-check form-switch mt-3">
                                    <input
                                        class="form-check-input"
                                        id="projectFeatured"
                                        name="destacado"
                                        type="checkbox"
                                        role="switch"
                                        value="1">
                                    <label class="form-check-label" for="projectFeatured">Proyecto destacado</label>
                                    <div class="form-text">Los proyectos destacados aparecerán primero.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top">
                        <button class="admin-button admin-button--ghost" type="button" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button class="admin-button admin-button--primary" id="btnSaveProject" type="submit">
                            <i class="fa-solid fa-floppy-disk"></i>
                            Guardar proyecto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- =====================================================
         PLANTILLA DE ENLACE DINÁMICO
    ====================================================== -->
    <template id="projectLinkTemplate">
        <article
            class="project-link-item border rounded-3 p-3"
            data-project-link>

            <div
                class="d-flex gap-3 align-items-start
                       justify-content-between mb-3">

                <div class="d-flex gap-2 align-items-center">
                    <span
                        class="badge text-bg-secondary"
                        data-link-number>
                        1
                    </span>

                    <div>
                        <strong data-link-title>Nuevo enlace</strong>

                        <small
                            class="d-block text-body-secondary"
                            data-link-description>
                            Configura el destino y el texto del botón.
                        </small>
                    </div>
                </div>

                <button
                    class="btn btn-sm btn-outline-danger"
                    type="button"
                    data-remove-project-link
                    aria-label="Eliminar enlace"
                    title="Eliminar enlace">

                    <i
                        class="fa-solid fa-trash"
                        aria-hidden="true">
                    </i>
                </button>
            </div>

            <div class="row g-3">
                <div class="col-12 col-lg-4">
                    <label
                        class="form-label"
                        data-link-label="type">
                        Tipo de enlace *
                    </label>

                    <select
                        class="form-select"
                        name="enlaces[__INDEX__][id_tipo_enlace]"
                        data-link-field="type"
                        required>

                        <option value="">
                            Selecciona un tipo
                        </option>
                    </select>
                </div>

                <div class="col-12 col-lg-5">
                    <label
                        class="form-label"
                        data-link-label="label">
                        Etiqueta del botón
                    </label>

                    <input
                        class="form-control"
                        name="enlaces[__INDEX__][etiqueta]"
                        data-link-field="label"
                        maxlength="100"
                        placeholder="Ejemplo: Visitar sitio">
                </div>

                <div class="col-12 col-lg-3">
                    <label
                        class="form-label"
                        data-link-label="order">
                        Orden
                    </label>

                    <input
                        class="form-control"
                        name="enlaces[__INDEX__][orden_visualizacion]"
                        data-link-field="order"
                        type="number"
                        min="1"
                        step="1"
                        placeholder="Automático">
                </div>

                <div class="col-12">
                    <label
                        class="form-label"
                        data-link-label="url">
                        URL
                    </label>

                    <div class="input-group">
                        <span class="input-group-text">
                            <i
                                class="fa-solid fa-link"
                                data-link-icon
                                aria-hidden="true">
                            </i>
                        </span>

                        <input
                            class="form-control"
                            name="enlaces[__INDEX__][url]"
                            data-link-field="url"
                            type="url"
                            maxlength="1000"
                            inputmode="url"
                            autocomplete="url"
                            placeholder="https://ejemplo.com">
                    </div>

                    <div class="form-text" data-link-help>
                        Utiliza una dirección completa que comience con
                        https:// o http://.
                    </div>
                </div>

                <div class="col-12">
                    <div class="form-check form-switch">
                        <input
                            class="form-check-input"
                            name="enlaces[__INDEX__][es_privado]"
                            data-link-field="private"
                            type="checkbox"
                            role="switch"
                            value="1">

                        <label
                            class="form-check-label"
                            data-link-label="private">

                            <strong>Enlace privado</strong>

                            <span class="d-block text-body-secondary">
                                La URL se guardará para administración,
                                pero no será enviada a la vista pública.
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </article>
    </template>

    <div class="modal fade" id="projectStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content admin-panel p-0">
                <div class="modal-header border-bottom">
                    <h2 class="modal-title fs-5">Cambiar disponibilidad</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center py-5">
                    <i class="fa-solid fa-triangle-exclamation fs-1 text-warning mb-3"></i>
                    <p class="mb-0" id="projectStatusMessage"></p>
                </div>
                <div class="modal-footer border-top">
                    <button class="admin-button admin-button--ghost" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="admin-button admin-button--primary" id="btnConfirmProjectStatus" type="button">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast" id="projectNotification" role="status" aria-live="polite" aria-atomic="true">
            <div class="toast-header">
                <i class="fa-solid fa-circle-info me-2" id="projectNotificationIcon"></i>
                <strong class="me-auto">Proyectos</strong>
                <button class="btn-close" type="button" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
            <div class="toast-body" id="projectNotificationMessage"></div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/Template/admin-footer.php'; ?>