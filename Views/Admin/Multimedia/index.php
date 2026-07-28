<?php

$csrfToken = (string) ($data['csrfToken'] ?? '');

$multimediaMeta = $data['moduleMeta'] ?? [
    'eyebrow' => 'MEDIA_VAULT',
    'title' => 'Biblioteca multimedia',
    'description' => 'Administra las imágenes y videos vinculados a tus proyectos.',
    'action' => 'Subir multimedia',
    'icon' => 'fa-photo-film',
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
    #multimediaModule .media-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
    }

    #multimediaModule .media-card {
        overflow: hidden;
        border: 1px solid var(--admin-border-soft);
        background: var(--admin-surface-elevated);
    }

    #multimediaModule .media-card__preview,
    #multimediaModule .media-form-preview {
        position: relative;
        display: grid;
        overflow: hidden;
        place-items: center;
        background: color-mix(in srgb, var(--admin-bg) 82%, black);
    }

    #multimediaModule .media-card__preview {
        aspect-ratio: 16 / 10;
    }

    #multimediaModule .media-card__preview img,
    #multimediaModule .media-card__preview video,
    #multimediaModule .media-form-preview img,
    #multimediaModule .media-form-preview video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #multimediaModule .media-card__placeholder {
        display: grid;
        min-height: 100%;
        gap: .6rem;
        place-items: center;
        align-content: center;
        padding: 1.5rem;
        color: var(--admin-muted);
        text-align: center;
    }

    #multimediaModule .media-card__placeholder i {
        color: var(--admin-cyan);
        font-size: 2rem;
    }

    #multimediaModule .media-card__body {
        padding: 1rem;
    }

    #multimediaModule .media-card__title {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    #multimediaModule .media-form-preview {
        min-height: 220px;
        max-height: 360px;
        border: 1px dashed var(--admin-border);
    }

    #multimediaModule .media-form-preview__empty {
        padding: 2rem;
        color: var(--admin-muted);
        text-align: center;
    }

    #multimediaModule .media-form-preview__empty i {
        display: block;
        margin-bottom: .75rem;
        color: var(--admin-cyan);
        font-size: 2.4rem;
    }

    #multimediaModule .media-cover-badge {
        position: absolute;
        z-index: 2;
        top: .75rem;
        left: .75rem;
    }

    #multimediaModule .media-status-badge {
        position: absolute;
        z-index: 2;
        top: .75rem;
        right: .75rem;
    }

    #mediaModal .modal-content {
        max-height: calc(100dvh - 2rem);
    }

    #mediaModal .modal-body {
        overflow-y: auto;
    }
</style>

<div
    id="multimediaModule"
    data-base-url="<?= $escape(BASE_URL) ?>"
    data-catalogs-url="<?= $escape(BASE_URL . 'multimedia/catalogos') ?>"
    data-list-url="<?= $escape(BASE_URL . 'multimedia/listar') ?>"
    data-get-url="<?= $escape(BASE_URL . 'multimedia/obtener') ?>"
    data-save-url="<?= $escape(BASE_URL . 'multimedia/guardar') ?>"
    data-status-url="<?= $escape(BASE_URL . 'multimedia/cambiarEstado') ?>"
    data-cover-url="<?= $escape(BASE_URL . 'multimedia/cambiarPortada') ?>">

    <section class="module-hero">
        <div class="module-hero__icon">
            <i class="fa-solid <?= $escape(
                $multimediaMeta['icon'] ?? 'fa-photo-film'
            ) ?>"></i>
        </div>

        <div class="module-hero__content">
            <span class="panel-code">
                <?= $escape(
                    $multimediaMeta['eyebrow'] ?? 'MEDIA_VAULT'
                ) ?>
            </span>

            <h2>
                <?= $escape(
                    $multimediaMeta['title'] ?? 'Biblioteca multimedia'
                ) ?>
            </h2>

            <p>
                <?= $escape(
                    $multimediaMeta['description']
                        ?? 'Administra imágenes y videos de tus proyectos.'
                ) ?>
            </p>
        </div>

        <button
            class="admin-button admin-button--primary"
            id="btnNewMedia"
            type="button">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <?= $escape(
                $multimediaMeta['action'] ?? 'Subir multimedia'
            ) ?>
        </button>
    </section>

    <section class="row g-3 mt-1" aria-label="Resumen multimedia">
        <div class="col-6 col-xl-3">
            <article class="stat-card stat-card--cyan h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon">
                        <i class="fa-solid fa-photo-film"></i>
                    </span>
                    <span class="stat-card__signal">TOTAL</span>
                </div>
                <strong id="totalMedia">0</strong>
                <span>Archivos registrados</span>
            </article>
        </div>

        <div class="col-6 col-xl-3">
            <article class="stat-card stat-card--green h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon">
                        <i class="fa-solid fa-image"></i>
                    </span>
                    <span class="stat-card__signal">IMAGE</span>
                </div>
                <strong id="totalImages">0</strong>
                <span>Imágenes activas</span>
            </article>
        </div>

        <div class="col-6 col-xl-3">
            <article class="stat-card stat-card--pink h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon">
                        <i class="fa-solid fa-video"></i>
                    </span>
                    <span class="stat-card__signal">VIDEO</span>
                </div>
                <strong id="totalVideos">0</strong>
                <span>Videos activos</span>
            </article>
        </div>

        <div class="col-6 col-xl-3">
            <article class="stat-card stat-card--yellow h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon">
                        <i class="fa-solid fa-box-archive"></i>
                    </span>
                    <span class="stat-card__signal">OFFLINE</span>
                </div>
                <strong id="inactiveMedia">0</strong>
                <span>Archivos inactivos</span>
            </article>
        </div>
    </section>

    <section class="admin-panel mt-3">
        <div class="d-flex flex-column gap-4">
            <div>
                <span class="panel-code">PROJECT_MEDIA_LIBRARY</span>
                <h2>Galería de proyectos</h2>
                <p class="mb-0 mt-3 text-body-secondary">
                    La baja es lógica: el registro y el archivo permanecen
                    disponibles para restaurarlos posteriormente.
                </p>
            </div>

            <div class="row g-2 align-items-end">
                <div class="col-12 col-xl">
                    <label class="form-label" for="mediaSearch">
                        Buscar
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input
                            class="form-control"
                            id="mediaSearch"
                            type="search"
                            placeholder="Título, descripción o proyecto..."
                            autocomplete="off">
                    </div>
                </div>

                <div class="col-12 col-md-5 col-xl-3">
                    <label class="form-label" for="mediaProjectFilter">
                        Proyecto
                    </label>
                    <select class="form-select" id="mediaProjectFilter">
                        <option value="0">Todos los proyectos</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <label class="form-label" for="mediaTypeFilter">
                        Tipo
                    </label>
                    <select class="form-select" id="mediaTypeFilter">
                        <option value="all">Todos</option>
                        <option value="imagen">Imágenes</option>
                        <option value="video">Videos</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <label class="form-label" for="mediaStatusFilter">
                        Estado
                    </label>
                    <select class="form-select" id="mediaStatusFilter">
                        <option value="all">Todos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                    </select>
                </div>

                <div class="col-12 col-md-auto">
                    <button
                        class="admin-button admin-button--ghost w-100"
                        id="btnRefreshMedia"
                        type="button">
                        <i class="fa-solid fa-rotate"></i>
                        Actualizar
                    </button>
                </div>
            </div>
        </div>

        <div class="media-grid mt-4" id="mediaGrid"></div>

        <div class="empty-state mt-4" id="mediaEmptyState" hidden>
            <span><i class="fa-solid fa-photo-film"></i></span>
            <strong>No se encontraron archivos multimedia</strong>
            <p>
                Sube una imagen o video, o modifica los filtros utilizados.
            </p>
            <button
                class="admin-button admin-button--primary"
                id="btnEmptyNewMedia"
                type="button">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                Subir multimedia
            </button>
        </div>
    </section>

    <div
        class="modal fade"
        id="mediaModal"
        tabindex="-1"
        aria-labelledby="mediaModalTitle"
        aria-hidden="true">
        <div
            class="modal-dialog modal-xl modal-dialog-centered
                   modal-dialog-scrollable">
            <form
                class="modal-content admin-panel p-0"
                id="mediaForm"
                method="post"
                enctype="multipart/form-data"
                autocomplete="off"
                novalidate>

                <input
                    id="mediaId"
                    name="id_multimedia"
                    type="hidden"
                    value="0">

                <input
                    id="mediaCsrfToken"
                    name="csrf_token"
                    type="hidden"
                    value="<?= $escape($csrfToken) ?>">

                <div class="modal-header border-bottom">
                    <div>
                        <span class="panel-code">MEDIA_EDITOR</span>
                        <h2 class="modal-title fs-5" id="mediaModalTitle">
                            Subir multimedia
                        </h2>
                        <p class="mb-0 mt-2 text-body-secondary">
                            Sube un archivo local o registra una URL externa.
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
                        id="mediaFormFeedback"
                        role="alert">
                    </div>

                    <div class="row g-4">
                        <div class="col-12 col-lg-7">
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label" for="mediaProject">
                                        Proyecto
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select
                                        class="form-select"
                                        id="mediaProject"
                                        name="id_proyecto"
                                        required>
                                        <option value="">Selecciona un proyecto</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Selecciona el proyecto relacionado.
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label
                                        class="form-label"
                                        for="mediaStorageType">
                                        Almacenamiento
                                    </label>
                                    <select
                                        class="form-select"
                                        id="mediaStorageType"
                                        name="tipo_almacenamiento">
                                        <option value="local">Archivo local</option>
                                        <option value="externo">URL externa</option>
                                    </select>
                                </div>

                                <div
                                    class="col-12 col-md-6 d-none"
                                    id="mediaExternalTypeGroup">
                                    <label class="form-label" for="mediaType">
                                        Tipo multimedia
                                    </label>
                                    <select
                                        class="form-select"
                                        id="mediaType"
                                        name="id_tipo_multimedia">
                                        <option value="">Selecciona el tipo</option>
                                    </select>
                                </div>

                                <div class="col-12" id="mediaFileGroup">
                                    <label class="form-label" for="mediaFile">
                                        Imagen o video
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        class="form-control"
                                        id="mediaFile"
                                        name="archivo"
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime">
                                    <div class="form-text">
                                        Imágenes hasta 12 MB. Videos hasta 100 MB.
                                        Al editar, déjalo vacío para conservar el archivo.
                                    </div>
                                </div>

                                <div
                                    class="col-12 d-none"
                                    id="mediaExternalUrlGroup">
                                    <label
                                        class="form-label"
                                        for="mediaExternalUrl">
                                        URL externa
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        class="form-control"
                                        id="mediaExternalUrl"
                                        name="url_externa"
                                        type="url"
                                        maxlength="1000"
                                        placeholder="https://...">
                                    <div class="form-text">
                                        Puede ser una imagen, un video directo o una página externa.
                                    </div>
                                </div>

                                <div class="col-12 col-md-7">
                                    <label class="form-label" for="mediaTitle">
                                        Título
                                    </label>
                                    <input
                                        class="form-control"
                                        id="mediaTitle"
                                        name="titulo"
                                        type="text"
                                        maxlength="180"
                                        placeholder="Ejemplo: Dashboard principal">
                                </div>

                                <div class="col-12 col-md-5">
                                    <label class="form-label" for="mediaOrder">
                                        Orden
                                    </label>
                                    <input
                                        class="form-control"
                                        id="mediaOrder"
                                        name="orden_visualizacion"
                                        type="number"
                                        min="0"
                                        step="1"
                                        placeholder="Automático">
                                </div>

                                <div class="col-12">
                                    <label
                                        class="form-label"
                                        for="mediaAlternativeText">
                                        Texto alternativo
                                    </label>
                                    <input
                                        class="form-control"
                                        id="mediaAlternativeText"
                                        name="texto_alternativo"
                                        type="text"
                                        maxlength="255"
                                        placeholder="Describe brevemente lo que muestra la imagen">
                                    <div class="form-text">
                                        Mejora la accesibilidad y el posicionamiento de imágenes.
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <label class="form-label" for="mediaDescription">
                                            Descripción
                                        </label>
                                        <small
                                            class="text-body-secondary"
                                            id="mediaDescriptionCounter">
                                            0 / 500
                                        </small>
                                    </div>
                                    <textarea
                                        class="form-control"
                                        id="mediaDescription"
                                        name="descripcion"
                                        maxlength="500"
                                        rows="3"
                                        placeholder="Contexto de esta captura o video..."></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="mediaThumbnail">
                                        Miniatura opcional
                                    </label>
                                    <input
                                        class="form-control"
                                        id="mediaThumbnail"
                                        name="ruta_miniatura"
                                        type="text"
                                        maxlength="500"
                                        placeholder="Assets/... o https://...">
                                    <div class="form-text">
                                        Útil para videos externos; no se genera automáticamente.
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="form-check form-switch">
                                        <input
                                            class="form-check-input"
                                            id="mediaCover"
                                            name="es_portada"
                                            type="checkbox"
                                            value="1">
                                        <label
                                            class="form-check-label"
                                            for="mediaCover">
                                            Portada del proyecto
                                        </label>
                                        <div class="form-text">
                                            Solo puede existir una imagen de portada activa.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="form-check form-switch">
                                        <input
                                            class="form-check-input"
                                            id="mediaActive"
                                            name="activo"
                                            type="checkbox"
                                            value="1"
                                            checked>
                                        <label
                                            class="form-check-label"
                                            for="mediaActive">
                                            Archivo activo
                                        </label>
                                        <div class="form-text">
                                            Los inactivos no se mostrarán públicamente.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-5">
                            <span class="panel-code">LIVE_PREVIEW</span>
                            <h3 class="fs-6 mt-2">Vista previa</h3>
                            <div class="media-form-preview mt-3" id="mediaPreview">
                                <div class="media-form-preview__empty">
                                    <i class="fa-solid fa-photo-film"></i>
                                    Selecciona un archivo o escribe una URL.
                                </div>
                            </div>
                            <div
                                class="small text-body-secondary mt-3"
                                id="mediaFileDetails">
                                Sin archivo seleccionado.
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
                        id="btnSaveMedia"
                        type="submit">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Guardar multimedia</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div
            class="toast"
            id="mediaNotification"
            role="status"
            aria-live="polite"
            aria-atomic="true">
            <div class="toast-header">
                <i
                    class="fa-solid fa-circle-check text-success me-2"
                    id="mediaNotificationIcon">
                </i>
                <strong class="me-auto" id="mediaNotificationTitle">
                    Operación completada
                </strong>
                <button
                    class="btn-close"
                    type="button"
                    data-bs-dismiss="toast"
                    aria-label="Cerrar">
                </button>
            </div>
            <div class="toast-body" id="mediaNotificationMessage"></div>
        </div>
    </div>
</div>
