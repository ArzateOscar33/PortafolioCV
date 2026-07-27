<?php

/**
 * Vista administrativa del módulo Categorías.
 *
 * El contenido se carga posteriormente mediante AJAX.
 * Esta vista no realiza consultas directas a la base de datos.
 */

$csrfToken = (string) ($data['csrfToken'] ?? '');

$categoryMeta = $data['moduleMeta'] ?? [
    'eyebrow' => 'PROJECT_CLASS',
    'title' => 'Categorías de proyectos',
    'description' => 'Organiza el portafolio por tipo de solución o área técnica.',
    'action' => 'Nueva categoría',
    'icon' => 'fa-tags',
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
    class="categories-module"
    id="categoriesModule"
    data-list-url="<?= $escape(BASE_URL . 'categorias/listar') ?>"
    data-get-url="<?= $escape(BASE_URL . 'categorias/obtener') ?>"
    data-save-url="<?= $escape(BASE_URL . 'categorias/guardar') ?>"
    data-status-url="<?= $escape(BASE_URL . 'categorias/cambiarEstado') ?>">

    <!-- ==================================================
         ENCABEZADO
    =================================================== -->

    <section class="module-hero categories-module__hero">

        <div class="module-hero__icon">

            <i class="fa-solid <?= $escape(
                                    $categoryMeta['icon'] ?? 'fa-tags'
                                ) ?>"></i>

        </div>

        <div class="module-hero__content">

            <span class="panel-code">
                <?= $escape(
                    $categoryMeta['eyebrow'] ?? 'PROJECT_CLASS'
                ) ?>
            </span>

            <h2>
                <?= $escape(
                    $categoryMeta['title']
                        ?? 'Categorías de proyectos'
                ) ?>
            </h2>

            <p>
                <?= $escape(
                    $categoryMeta['description']
                        ?? 'Organiza los proyectos por categoría.'
                ) ?>
            </p>

        </div>

        <button
            class="admin-button admin-button--primary"
            id="btnNewCategory"
            type="button"
            data-bs-toggle="modal"
            data-bs-target="#categoryModal">

            <i class="fa-solid fa-plus"></i>

            <?= $escape(
                $categoryMeta['action'] ?? 'Nueva categoría'
            ) ?>

        </button>

    </section>

    <!-- ==================================================
         ESTADÍSTICAS
    =================================================== -->

    <section
        class="row g-3 mt-1"
        aria-label="Resumen de categorías">

        <div class="col-12 col-md-4">

            <article class="stat-card stat-card--cyan h-100">

                <div class="stat-card__head">

                    <span class="stat-card__icon">
                        <i class="fa-solid fa-tags"></i>
                    </span>

                    <span class="stat-card__signal">
                        TOTAL
                    </span>

                </div>

                <strong id="totalCategories">
                    0
                </strong>

                <span>
                    Categorías registradas
                </span>

            </article>

        </div>

        <div class="col-12 col-md-4">

            <article class="stat-card stat-card--green h-100">

                <div class="stat-card__head">

                    <span class="stat-card__icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>

                    <span class="stat-card__signal">
                        ONLINE
                    </span>

                </div>

                <strong id="activeCategories">
                    0
                </strong>

                <span>
                    Categorías activas
                </span>

            </article>

        </div>

        <div class="col-12 col-md-4">

            <article class="stat-card stat-card--pink h-100">

                <div class="stat-card__head">

                    <span class="stat-card__icon">
                        <i class="fa-solid fa-circle-pause"></i>
                    </span>

                    <span class="stat-card__signal">
                        OFFLINE
                    </span>

                </div>

                <strong id="inactiveCategories">
                    0
                </strong>

                <span>
                    Categorías inactivas
                </span>

            </article>

        </div>

    </section>

    <!-- ==================================================
         LISTADO
    =================================================== -->

    <section class="admin-panel categories-list-panel mt-3">

        <div
            class="d-flex flex-column flex-xl-row
                   gap-4 align-items-xl-end
                   justify-content-between">

            <div>

                <span class="panel-code">
                    CATEGORY_DATABASE
                </span>

                <h2>
                    Catálogo de categorías
                </h2>

                <p class="categories-list-description mb-0 mt-3">
                    Administra las clasificaciones utilizadas para
                    organizar los proyectos publicados en el portafolio.
                </p>

            </div>

            <div class="row g-2 align-items-end">

                <div class="col-12 col-md">

                    <label
                        class="form-label"
                        for="categorySearch">

                        Buscar categoría

                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>

                        <input
                            class="form-control"
                            id="categorySearch"
                            type="search"
                            placeholder="Nombre o slug..."
                            autocomplete="off">

                    </div>

                </div>

                <div class="col-12 col-sm-7 col-md-auto">

                    <label
                        class="form-label"
                        for="categoryStatusFilter">

                        Estado

                    </label>

                    <select
                        class="form-select"
                        id="categoryStatusFilter">

                        <option value="all">
                            Todas
                        </option>

                        <option value="active">
                            Activas
                        </option>

                        <option value="inactive">
                            Inactivas
                        </option>

                    </select>

                </div>

                <div class="col-12 col-sm-5 col-md-auto">

                    <button
                        class="admin-button admin-button--ghost w-100"
                        id="btnRefreshCategories"
                        type="button">

                        <i class="fa-solid fa-rotate"></i>

                        Actualizar

                    </button>

                </div>

            </div>

        </div>

        <div
            class="table-responsive mt-4"
            id="categoriesTableContainer">

            <table
                class="table table-hover align-middle mb-0
                       categories-table">

                <thead>

                    <tr>

                        <th scope="col">
                            Orden
                        </th>

                        <th scope="col">
                            Categoría
                        </th>

                        <th scope="col">
                            Slug
                        </th>

                        <th scope="col">
                            Estado
                        </th>

                        <th scope="col">
                            Actualización
                        </th>

                        <th
                            class="text-end"
                            scope="col">

                            Acciones

                        </th>

                    </tr>

                </thead>

                <tbody id="categoriesTableBody">

                    <tr class="categories-loading-row">

                        <td
                            class="text-center py-5"
                            colspan="6">

                            <div
                                class="d-flex flex-column
                                       align-items-center gap-2">

                                <span class="fs-4">
                                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                                </span>

                                <strong>
                                    Cargando categorías
                                </strong>

                                <small class="text-body-secondary">
                                    Esperando respuesta del servidor...
                                </small>

                            </div>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

        <!-- Estado vacío -->

        <div
            class="empty-state"
            id="categoriesEmptyState"
            hidden>

            <span>
                <i class="fa-solid fa-tags"></i>
            </span>

            <strong>
                No se encontraron categorías
            </strong>

            <p>
                Registra una nueva categoría o modifica los filtros
                utilizados en la búsqueda.
            </p>

            <button
                class="admin-button admin-button--primary"
                id="btnEmptyNewCategory"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#categoryModal">

                <i class="fa-solid fa-plus"></i>

                Nueva categoría

            </button>

        </div>

    </section>

    <!-- ==================================================
         MODAL DE REGISTRO Y EDICIÓN
    =================================================== -->

    <div
        class="modal fade"
        id="categoryModal"
        tabindex="-1"
        aria-labelledby="categoryModalTitle"
        aria-hidden="true">

        <div
            class="modal-dialog modal-xl
                   modal-dialog-centered
                   modal-dialog-scrollable">

            <div class="modal-content admin-panel p-0">

                <form
                    id="categoryForm"
                    method="post"
                    autocomplete="off"
                    novalidate>

                    <input
                        id="categoryId"
                        name="id_categoria"
                        type="hidden"
                        value="0">

                    <input
                        id="categoryCsrfToken"
                        name="csrf_token"
                        type="hidden"
                        value="<?= $escape($csrfToken) ?>">

                    <!-- Header modal -->

                    <div class="modal-header border-bottom">

                        <div>

                            <span class="panel-code">
                                CATEGORY_EDITOR
                            </span>

                            <h2
                                class="modal-title fs-5"
                                id="categoryModalTitle">

                                Nueva categoría

                            </h2>

                            <p
                                class="mb-0 mt-2 text-body-secondary"
                                id="categoryModalDescription">

                                Completa la información utilizada para
                                clasificar los proyectos.

                            </p>

                        </div>

                        <button
                            class="btn-close"
                            type="button"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar">
                        </button>

                    </div>

                    <!-- Cuerpo modal -->

                    <div class="modal-body">

                        <div
                            class="alert alert-danger d-none"
                            id="categoryFormFeedback"
                            role="alert">
                        </div>

                        <div class="row g-4">

                            <!-- Nombre -->

                            <div class="col-12 col-md-6">

                                <label
                                    class="form-label"
                                    for="categoryName">

                                    Nombre
                                    <span class="text-danger">*</span>

                                </label>

                                <input
                                    class="form-control"
                                    id="categoryName"
                                    name="nombre"
                                    type="text"
                                    maxlength="100"
                                    placeholder="Ejemplo: Desarrollo web"
                                    required>

                                <div class="form-text">
                                    Nombre público mostrado en el
                                    portafolio.
                                </div>

                                <div
                                    class="invalid-feedback"
                                    id="categoryNameError">

                                    Escribe el nombre de la categoría.

                                </div>

                            </div>

                            <!-- Slug -->

                            <div class="col-12 col-md-6">

                                <label
                                    class="form-label"
                                    for="categorySlug">

                                    Slug
                                    <span class="text-danger">*</span>

                                </label>

                                <div class="input-group">

                                    <input
                                        class="form-control"
                                        id="categorySlug"
                                        name="slug"
                                        type="text"
                                        maxlength="120"
                                        pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
                                        placeholder="desarrollo-web"
                                        spellcheck="false"
                                        required>

                                    <button
                                        class="btn btn-outline-secondary"
                                        id="btnGenerateCategorySlug"
                                        type="button"
                                        title="Generar slug">

                                        <i class="fa-solid fa-wand-magic-sparkles"></i>

                                    </button>

                                </div>

                                <div class="form-text">
                                    Utiliza minúsculas, números y guiones.
                                </div>

                                <div
                                    class="invalid-feedback"
                                    id="categorySlugError">

                                    Escribe un slug válido.

                                </div>

                            </div>

                            <!-- Descripción -->

                            <div class="col-12">

                                <div
                                    class="d-flex align-items-center
                                           justify-content-between">

                                    <label
                                        class="form-label"
                                        for="categoryDescription">

                                        Descripción

                                    </label>

                                    <small
                                        class="text-body-secondary"
                                        id="categoryDescriptionCounter">

                                        0 / 500

                                    </small>

                                </div>

                                <textarea
                                    class="form-control"
                                    id="categoryDescription"
                                    name="descripcion"
                                    maxlength="500"
                                    rows="4"
                                    placeholder="Describe qué tipo de proyectos pertenecen a esta categoría."></textarea>

                                <div
                                    class="invalid-feedback"
                                    id="categoryDescriptionError">
                                </div>

                            </div>

                            <!-- Color -->

                            <div class="col-12 col-md-6">

                                <label
                                    class="form-label"
                                    for="categoryColor">

                                    Color hexadecimal

                                </label>

                                <div class="input-group">

                                    <input
                                        class="form-control form-control-color"
                                        id="categoryColorPicker"
                                        type="color"
                                        value="#00F6FF"
                                        title="Seleccionar color">

                                    <input
                                        class="form-control"
                                        id="categoryColor"
                                        name="color_hexadecimal"
                                        type="text"
                                        maxlength="7"
                                        pattern="#[0-9A-Fa-f]{6}"
                                        placeholder="#00F6FF"
                                        spellcheck="false">

                                </div>

                                <div class="form-text">
                                    Formato esperado: #RRGGBB.
                                </div>

                                <div
                                    class="invalid-feedback"
                                    id="categoryColorError">

                                    Escribe un color hexadecimal válido.

                                </div>

                            </div>

                            <!-- Icono -->

                            <div class="col-12 col-md-6">

                                <label
                                    class="form-label"
                                    for="categoryIconClass">

                                    Clase del icono

                                </label>

                                <div class="input-group">

                                    <span
                                        class="input-group-text"
                                        id="categoryIconPreview">

                                        <i class="fa-solid fa-tags"></i>

                                    </span>

                                    <input
                                        class="form-control"
                                        id="categoryIconClass"
                                        name="clase_icono"
                                        type="text"
                                        maxlength="150"
                                        placeholder="fa-solid fa-tags"
                                        spellcheck="false">

                                </div>

                                <div class="form-text">
                                    Clase compatible con Font Awesome.
                                </div>

                                <div
                                    class="invalid-feedback"
                                    id="categoryIconClassError">
                                </div>

                            </div>

                            <!-- Orden -->

                            <div class="col-12 col-md-6">

                                <label
                                    class="form-label"
                                    for="categoryOrder">

                                    Orden de visualización

                                </label>

                                <input
                                    class="form-control"
                                    id="categoryOrder"
                                    name="orden_visualizacion"
                                    type="number"
                                    min="0"
                                    step="1"
                                    value="0">

                                <div class="form-text">
                                    Los números menores aparecen primero.
                                </div>

                                <div
                                    class="invalid-feedback"
                                    id="categoryOrderError">

                                    Introduce un número válido.

                                </div>

                            </div>

                            <!-- Estado -->

                            <div
                                class="col-12 col-md-6
                                       d-flex align-items-center">

                                <div class="form-check form-switch mt-3">

                                    <input
                                        class="form-check-input"
                                        id="categoryActive"
                                        name="activa"
                                        type="checkbox"
                                        role="switch"
                                        value="1"
                                        checked>

                                    <label
                                        class="form-check-label"
                                        for="categoryActive">

                                        Categoría activa

                                    </label>

                                    <div class="form-text">
                                        Solo las categorías activas se
                                        mostrarán públicamente.
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                    <!-- Footer modal -->

                    <div class="modal-footer border-top">

                        <button
                            class="admin-button admin-button--ghost"
                            id="btnCancelCategory"
                            type="button"
                            data-bs-dismiss="modal">

                            <i class="fa-solid fa-xmark"></i>

                            Cancelar

                        </button>

                        <button
                            class="admin-button admin-button--primary"
                            id="btnSaveCategory"
                            type="submit">

                            <i class="fa-solid fa-floppy-disk"></i>

                            <span>
                                Guardar categoría
                            </span>

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <!-- ==================================================
         MODAL PARA CAMBIAR ESTADO
    =================================================== -->

    <div
        class="modal fade"
        id="categoryStatusModal"
        tabindex="-1"
        aria-labelledby="categoryStatusModalTitle"
        aria-hidden="true">

        <div
            class="modal-dialog modal-dialog-centered">

            <div class="modal-content admin-panel p-0">

                <div class="modal-header border-bottom">

                    <div>

                        <span class="panel-code">
                            STATUS_CHANGE
                        </span>

                        <h2
                            class="modal-title fs-5"
                            id="categoryStatusModalTitle">

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

                <div class="modal-body text-center py-5">

                    <div class="fs-1 text-warning mb-3">

                        <i class="fa-solid fa-triangle-exclamation"></i>

                    </div>

                    <p
                        class="mb-0"
                        id="categoryStatusMessage">

                        ¿Deseas cambiar el estado de esta categoría?

                    </p>

                </div>

                <div class="modal-footer border-top">

                    <button
                        class="admin-button admin-button--ghost"
                        id="btnCancelCategoryStatus"
                        type="button"
                        data-bs-dismiss="modal">

                        Cancelar

                    </button>

                    <button
                        class="admin-button admin-button--primary"
                        id="btnConfirmCategoryStatus"
                        type="button">

                        Confirmar

                    </button>

                </div>

            </div>

        </div>

    </div>

    <!-- ==================================================
         NOTIFICACIONES
    =================================================== -->

    <div
        class="toast-container position-fixed
               bottom-0 end-0 p-3">

        <div
            class="toast"
            id="categoryNotification"
            role="status"
            aria-live="polite"
            aria-atomic="true">

            <div class="toast-header">

                <i
                    class="fa-solid fa-circle-check
                           text-success me-2">
                </i>

                <strong
                    class="me-auto"
                    id="categoryNotificationTitle">

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
                id="categoryNotificationMessage">
            </div>

        </div>

    </div>

</div>