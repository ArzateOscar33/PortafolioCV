<?php

$csrfToken = (string) ($data['csrfToken'] ?? '');
$configMeta = $data['moduleMeta'] ?? [
    'eyebrow' => 'SYSTEM_CONFIG',
    'title' => 'Configuración del portafolio',
    'description' => 'Administra identidad, perfil, contacto, SEO y redes sociales.',
    'icon' => 'fa-sliders',
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
    class="settings-module"
    id="settingsModule"
    data-load-url="<?= $escape(BASE_URL . 'configuracion/obtener') ?>"
    data-save-url="<?= $escape(BASE_URL . 'configuracion/guardar') ?>"
    data-social-get-url="<?= $escape(BASE_URL . 'configuracion/obtenerRed') ?>"
    data-social-save-url="<?= $escape(BASE_URL . 'configuracion/guardarRed') ?>"
    data-social-state-url="<?= $escape(BASE_URL . 'configuracion/cambiarEstadoRed') ?>">

    <section class="module-hero">
        <div class="module-hero__icon">
            <i class="fa-solid <?= $escape($configMeta['icon'] ?? 'fa-sliders') ?>"></i>
        </div>

        <div class="module-hero__content">
            <span class="panel-code">
                <?= $escape($configMeta['eyebrow'] ?? 'SYSTEM_CONFIG') ?>
            </span>

            <h2><?= $escape($configMeta['title'] ?? 'Configuración del portafolio') ?></h2>

            <p>
                <?= $escape($configMeta['description'] ?? 'Administra la información pública del sitio.') ?>
            </p>
        </div>

        <button
            class="admin-button admin-button--primary"
            id="btnSaveSettingsTop"
            type="button">
            <i class="fa-solid fa-floppy-disk"></i>
            Guardar cambios
        </button>
    </section>

    <section class="row g-3 mt-1" aria-label="Resumen de configuración">
        <div class="col-12 col-md-6 col-xl-3">
            <article class="stat-card stat-card--cyan h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon"><i class="fa-solid fa-sliders"></i></span>
                    <span class="stat-card__signal">CONFIG</span>
                </div>
                <strong id="totalSettings">0</strong>
                <span>Valores configurados</span>
            </article>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <article class="stat-card stat-card--green h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon"><i class="fa-solid fa-globe"></i></span>
                    <span class="stat-card__signal">PUBLIC</span>
                </div>
                <strong id="publicSettings">0</strong>
                <span>Valores públicos</span>
            </article>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <article class="stat-card stat-card--pink h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon"><i class="fa-solid fa-share-nodes"></i></span>
                    <span class="stat-card__signal">SOCIAL</span>
                </div>
                <strong id="activeSocials">0</strong>
                <span>Redes activas</span>
            </article>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <article class="stat-card stat-card--yellow h-100">
                <div class="stat-card__head">
                    <span class="stat-card__icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                    <span class="stat-card__signal">SYNC</span>
                </div>
                <strong class="fs-6" id="settingsUpdatedAt">Sin cambios</strong>
                <span>Última actualización</span>
            </article>
        </div>
    </section>

    <form
        class="admin-panel mt-3"
        id="siteSettingsForm"
        method="post"
        enctype="multipart/form-data"
        autocomplete="off"
        novalidate>

        <input
            id="settingsCsrfToken"
            name="csrf_token"
            type="hidden"
            value="<?= $escape($csrfToken) ?>">

        <div class="d-flex flex-column flex-xl-row gap-4 justify-content-between align-items-xl-center mb-4">
            <div>
                <span class="panel-code">PORTFOLIO_IDENTITY</span>
                <h2 class="mb-2">Contenido global</h2>
                <p class="text-body-secondary mb-0">
                    Los cambios se almacenan en <code>configuracion_sitio</code>.
                </p>
            </div>

            <div class="nav nav-pills gap-2" id="settingsTabs" role="tablist">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#settingsGeneral" type="button">General</button>
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#settingsHero" type="button">Hero</button>
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#settingsAbout" type="button">Acerca de mí</button>
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#settingsContact" type="button">Contacto</button>
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#settingsSeo" type="button">SEO y apariencia</button>
            </div>
        </div>

        <div class="alert alert-danger d-none" id="settingsFormFeedback" role="alert"></div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="settingsGeneral">
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="siteName">Nombre del sitio</label>
                        <input class="form-control" id="siteName" name="nombre_sitio" maxlength="120" placeholder="Cyberpunk Portfolio">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="fullName">Nombre completo</label>
                        <input class="form-control" id="fullName" name="nombre_completo" maxlength="150" placeholder="Oscar Arzate">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="professionalTitle">Título profesional</label>
                        <input class="form-control" id="professionalTitle" name="titulo_profesional" maxlength="150" placeholder="Ingeniero en Sistemas">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="footerText">Texto del pie de página</label>
                        <input class="form-control" id="footerText" name="footer_texto" maxlength="255" placeholder="Oscar Arzate. Todos los derechos reservados.">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="siteDescription">Descripción general del sitio</label>
                        <textarea class="form-control" id="siteDescription" name="descripcion_sitio" maxlength="500" rows="4" placeholder="Portafolio profesional de desarrollo e infraestructura."></textarea>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="settingsHero">
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label" for="heroKicker">Texto superior</label>
                        <input class="form-control" id="heroKicker" name="hero_kicker" maxlength="180" placeholder="Ingeniería de software + infraestructura">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="heroMainTitle">Título principal</label>
                        <input class="form-control" id="heroMainTitle" name="hero_titulo_principal" maxlength="100" placeholder="Código">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="heroHighlightedTitle">Título destacado</label>
                        <input class="form-control" id="heroHighlightedTitle" name="hero_titulo_destacado" maxlength="100" placeholder="sin límites">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="heroDescription">Descripción</label>
                        <textarea class="form-control" id="heroDescription" name="hero_descripcion" maxlength="1200" rows="5"></textarea>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="border rounded p-3 h-100">
                            <strong class="d-block mb-3">Botón principal</strong>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="heroPrimaryText">Texto</label>
                                    <input class="form-control" id="heroPrimaryText" name="hero_cta_principal_texto" maxlength="80" placeholder="Explorar proyectos">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="heroPrimaryUrl">URL o ancla</label>
                                    <input class="form-control" id="heroPrimaryUrl" name="hero_cta_principal_url" maxlength="500" placeholder="#proyectos">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="border rounded p-3 h-100">
                            <strong class="d-block mb-3">Botón secundario</strong>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="heroSecondaryText">Texto</label>
                                    <input class="form-control" id="heroSecondaryText" name="hero_cta_secundario_texto" maxlength="80" placeholder="Iniciar conexión">
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="heroSecondaryUrl">URL o ancla</label>
                                    <input class="form-control" id="heroSecondaryUrl" name="hero_cta_secundario_url" maxlength="500" placeholder="#contacto">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="settingsAbout">
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label" for="aboutTitle">Título de la sección</label>
                        <input class="form-control" id="aboutTitle" name="acerca_titulo" maxlength="120" placeholder="Acerca de mí">
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label" for="aboutParagraphOne">Primer párrafo</label>
                        <textarea class="form-control" id="aboutParagraphOne" name="acerca_parrafo_1" maxlength="2500" rows="7"></textarea>
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label" for="aboutParagraphTwo">Segundo párrafo</label>
                        <textarea class="form-control" id="aboutParagraphTwo" name="acerca_parrafo_2" maxlength="2500" rows="7"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="aboutSkills">Capacidades destacadas</label>
                        <textarea class="form-control" id="aboutSkills" name="acerca_habilidades" maxlength="5000" rows="6" placeholder="Una capacidad por línea"></textarea>
                        <div class="form-text">Se mostrarán como una lista. Máximo 12 elementos.</div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="border rounded p-3 h-100">
                            <label class="form-label" for="profilePhoto">Fotografía de perfil</label>
                            <input class="form-control" id="profilePhoto" name="foto_perfil_archivo" type="file" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">JPG, PNG o WEBP. Máximo 5 MB.</div>
                            <div class="mt-3" id="profilePhotoCurrent"></div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" id="removeProfilePhoto" name="eliminar_foto_perfil_ruta" type="checkbox" value="1">
                                <label class="form-check-label" for="removeProfilePhoto">Eliminar fotografía actual</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="border rounded p-3 h-100">
                            <label class="form-label" for="resumeFile">Currículum PDF</label>
                            <input class="form-control" id="resumeFile" name="cv_archivo" type="file" accept="application/pdf">
                            <div class="form-text">PDF. Máximo 10 MB.</div>
                            <div class="mt-3" id="resumeCurrent"></div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" id="removeResume" name="eliminar_cv_ruta" type="checkbox" value="1">
                                <label class="form-check-label" for="removeResume">Eliminar currículum actual</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="settingsContact">
                <div class="row g-4">
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="publicLocation">Ubicación</label>
                        <input class="form-control" id="publicLocation" name="ubicacion" maxlength="180" placeholder="Tijuana, Baja California, México">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="responseTime">Tiempo de respuesta</label>
                        <input class="form-control" id="responseTime" name="tiempo_respuesta" maxlength="80" placeholder="24-48 horas">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="publicEmail">Correo público</label>
                        <input class="form-control" id="publicEmail" name="correo_publico" maxlength="150" type="email" placeholder="contacto@dominio.com">
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label" for="publicPhone">Teléfono público</label>
                        <input class="form-control" id="publicPhone" name="telefono_publico" maxlength="50" placeholder="+52 664 000 0000">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="availabilityText">Mensaje de disponibilidad</label>
                        <input class="form-control" id="availabilityText" name="disponibilidad_texto" maxlength="180" placeholder="Disponible para nuevos proyectos">
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="availableProjects" name="disponible_proyectos" type="checkbox" value="1">
                            <label class="form-check-label" for="availableProjects">Disponible para proyectos</label>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="showPublicEmail" name="mostrar_correo" type="checkbox" value="1">
                            <label class="form-check-label" for="showPublicEmail">Mostrar correo</label>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" id="showPublicPhone" name="mostrar_telefono" type="checkbox" value="1">
                            <label class="form-check-label" for="showPublicPhone">Mostrar teléfono</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="settingsSeo">
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label" for="seoTitle">Título SEO</label>
                        <input class="form-control" id="seoTitle" name="seo_titulo" maxlength="160" placeholder="Oscar Arzate | Ingeniero en Sistemas">
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="seoDescription">Descripción SEO</label>
                        <textarea class="form-control" id="seoDescription" name="seo_descripcion" maxlength="320" rows="4"></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label" for="seoKeywords">Palabras clave</label>
                        <input class="form-control" id="seoKeywords" name="seo_palabras_clave" maxlength="500" placeholder="PHP, JavaScript, Docker, Linux, Tijuana">
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="border rounded p-3 h-100">
                            <label class="form-label" for="seoImage">Imagen para compartir</label>
                            <input class="form-control" id="seoImage" name="seo_imagen_archivo" type="file" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Recomendado: 1200 × 630 px. Máximo 5 MB.</div>
                            <div class="mt-3" id="seoImageCurrent"></div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" id="removeSeoImage" name="eliminar_seo_imagen_ruta" type="checkbox" value="1">
                                <label class="form-check-label" for="removeSeoImage">Eliminar imagen actual</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="border rounded p-3 h-100">
                            <label class="form-label" for="faviconFile">Favicon</label>
                            <input class="form-control" id="faviconFile" name="favicon_archivo" type="file" accept="image/png,image/x-icon">
                            <div class="form-text">PNG o ICO. Máximo 2 MB.</div>
                            <div class="mt-3" id="faviconCurrent"></div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" id="removeFavicon" name="eliminar_favicon_ruta" type="checkbox" value="1">
                                <label class="form-check-label" for="removeFavicon">Eliminar favicon actual</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <span class="panel-code">COLOR_SYSTEM</span>
                        <h3 class="fs-5 mt-2">Colores públicos</h3>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="primaryColor">Color primario</label>
                        <div class="input-group">
                            <input class="form-control form-control-color" id="primaryColorPicker" type="color" value="#00F6FF">
                            <input class="form-control" id="primaryColor" name="color_primario" maxlength="7" placeholder="#00F6FF">
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="secondaryColor">Color secundario</label>
                        <div class="input-group">
                            <input class="form-control form-control-color" id="secondaryColorPicker" type="color" value="#FF2BD6">
                            <input class="form-control" id="secondaryColor" name="color_secundario" maxlength="7" placeholder="#FF2BD6">
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="accentColor">Color de acento</label>
                        <div class="input-group">
                            <input class="form-control form-control-color" id="accentColorPicker" type="color" value="#F8F32B">
                            <input class="form-control" id="accentColor" name="color_acento" maxlength="7" placeholder="#F8F32B">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4 pt-4 border-top">
            <button class="admin-button admin-button--primary" id="btnSaveSettings" type="submit">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Guardar configuración</span>
            </button>
        </div>
    </form>

    <section class="admin-panel mt-3">
        <div class="d-flex flex-column flex-lg-row gap-3 justify-content-between align-items-lg-center">
            <div>
                <span class="panel-code">SOCIAL_NETWORKS</span>
                <h2 class="mb-2">Redes sociales</h2>
                <p class="text-body-secondary mb-0">
                    Controla enlaces, iconos, color, orden y visibilidad pública.
                </p>
            </div>

            <button
                class="admin-button admin-button--primary"
                id="btnNewSocial"
                type="button"
                data-bs-toggle="modal"
                data-bs-target="#socialModal">
                <i class="fa-solid fa-plus"></i>
                Nueva red social
            </button>
        </div>

        <div class="table-responsive mt-4">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Plataforma</th>
                        <th>URL</th>
                        <th>Estado</th>
                        <th>Actualización</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="socialTableBody">
                    <tr>
                        <td class="text-center py-5" colspan="6">
                            <i class="fa-solid fa-circle-notch fa-spin me-2"></i>
                            Cargando redes sociales...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="empty-state mt-4" id="socialEmptyState" hidden>
            <span><i class="fa-solid fa-share-nodes"></i></span>
            <strong>No hay redes sociales registradas</strong>
            <p>Agrega los perfiles profesionales que se mostrarán en la portada.</p>
        </div>
    </section>

    <div class="modal fade" id="socialModal" tabindex="-1" aria-labelledby="socialModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <form class="modal-content admin-panel p-0" id="socialForm" autocomplete="off" novalidate>
                <input id="socialId" name="id_red_social" type="hidden" value="0">
                <input name="csrf_token" type="hidden" value="<?= $escape($csrfToken) ?>">

                <div class="modal-header border-bottom">
                    <div>
                        <span class="panel-code">SOCIAL_EDITOR</span>
                        <h2 class="modal-title fs-5" id="socialModalTitle">Nueva red social</h2>
                    </div>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="socialFormFeedback" role="alert"></div>

                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="socialPlatform">Plataforma <span class="text-danger">*</span></label>
                            <input class="form-control" id="socialPlatform" name="plataforma" maxlength="60" placeholder="LinkedIn" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="socialLabel">Etiqueta</label>
                            <input class="form-control" id="socialLabel" name="etiqueta" maxlength="100" placeholder="LinkedIn profesional">
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="socialUrl">URL <span class="text-danger">*</span></label>
                            <input class="form-control" id="socialUrl" name="url" type="url" maxlength="1000" placeholder="https://www.linkedin.com/in/usuario" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="socialIcon">Clase de Font Awesome</label>
                            <div class="input-group">
                                <span class="input-group-text" id="socialIconPreview"><i class="fa-solid fa-link"></i></span>
                                <input class="form-control" id="socialIcon" name="clase_icono" maxlength="150" placeholder="fa-brands fa-linkedin-in">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="socialColor">Color</label>
                            <div class="input-group">
                                <input class="form-control form-control-color" id="socialColorPicker" type="color" value="#00F6FF">
                                <input class="form-control" id="socialColor" name="color_hexadecimal" maxlength="7" placeholder="#00F6FF">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="socialOrder">Orden</label>
                            <input class="form-control" id="socialOrder" name="orden_visualizacion" type="number" min="0" step="1" placeholder="Automático">
                        </div>

                        <div class="col-12 col-md-6 d-flex align-items-center">
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" id="socialActive" name="activa" type="checkbox" value="1" checked>
                                <label class="form-check-label" for="socialActive">Red social activa</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top">
                    <button class="admin-button admin-button--ghost" type="button" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark"></i>
                        Cancelar
                    </button>
                    <button class="admin-button admin-button--primary" id="btnSaveSocial" type="submit">
                        <i class="fa-solid fa-floppy-disk"></i>
                        <span>Guardar red social</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="socialStateModal" tabindex="-1" aria-labelledby="socialStateModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content admin-panel p-0">
                <div class="modal-header border-bottom">
                    <h2 class="modal-title fs-5" id="socialStateModalTitle">Cambiar estado</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center py-5">
                    <div class="fs-1 text-warning mb-3"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <p class="mb-0" id="socialStateMessage"></p>
                </div>
                <div class="modal-footer border-top">
                    <button class="admin-button admin-button--ghost" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="admin-button admin-button--primary" id="btnConfirmSocialState" type="button">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div class="toast" id="settingsNotification" role="status" aria-live="polite" aria-atomic="true">
            <div class="toast-header">
                <i class="fa-solid fa-circle-check text-success me-2" id="settingsNotificationIcon"></i>
                <strong class="me-auto" id="settingsNotificationTitle">Operación completada</strong>
                <button class="btn-close" type="button" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
            <div class="toast-body" id="settingsNotificationMessage"></div>
        </div>
    </div>
</div>
