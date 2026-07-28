<?php

/**
 * Vista principal del panel administrativo.
 *
 * El encabezado abre:
 * - El documento HTML.
 * - El body.
 * - El sidebar.
 * - El topbar.
 * - El contenedor .admin-content.
 */
require_once dirname(__DIR__)
    . '/Template/admin-header.php';

?>

<?php if ($activeModule === 'dashboard'): ?>

    <!-- ==================================================
         BIENVENIDA
    =================================================== -->

    <section class="welcome-panel">

        <div class="welcome-panel__content">

            <span class="panel-code">
                SYS://WELCOME_BACK
            </span>

            <h2>
                Bienvenido,
                <?= $adminEscape(
                    $user['name'] ?? 'Administrador'
                ) ?>
            </h2>

            <p>
                Desde este nodo podrás administrar el contenido público
                de Cyberpunk. La estructura ya está preparada para
                conectar cada módulo con sus controladores y modelos.
            </p>

            <div class="welcome-panel__actions">

                <a
                    class="admin-button admin-button--primary"
                    href="<?= BASE_URL ?>admin/proyectos">

                    <i class="fa-solid fa-plus"></i>

                    Crear proyecto

                </a>

                <a
                    class="admin-button admin-button--ghost"
                    href="<?= BASE_URL ?>"
                    target="_blank"
                    rel="noopener noreferrer">

                    <i class="fa-solid fa-eye"></i>

                    Ver sitio público

                </a>

            </div>

        </div>

        <div
            class="welcome-panel__visual"
            aria-hidden="true">

            <div class="core-orbit core-orbit--outer"></div>

            <div class="core-orbit core-orbit--inner"></div>

            <div class="core-symbol">
                <i class="fa-solid fa-code"></i>
            </div>

        </div>

    </section>

    <!-- ==================================================
         ESTADÍSTICAS
    =================================================== -->

    <section
        class="stats-grid"
        aria-label="Resumen del portafolio">

        <?php foreach ($data['stats'] ?? [] as $stat): ?>

            <article
                class="stat-card stat-card--<?= $adminEscape(
                                                $stat['accent'] ?? 'cyan'
                                            ) ?>">

                <div class="stat-card__head">

                    <span class="stat-card__icon">

                        <i class="fa-solid <?= $adminEscape(
                                                $stat['icon'] ?? 'fa-chart-line'
                                            ) ?>"></i>

                    </span>

                    <span class="stat-card__signal">
                        LIVE
                    </span>

                </div>

                <strong>
                    <?= $adminEscape(
                        $stat['value'] ?? '0'
                    ) ?>
                </strong>

                <span>
                    <?= $adminEscape(
                        $stat['label'] ?? ''
                    ) ?>
                </span>

            </article>

        <?php endforeach; ?>

    </section>

    <!-- ==================================================
         ACCIONES RÁPIDAS Y ACTIVIDAD
    =================================================== -->

    <section class="dashboard-grid">

        <article class="admin-panel quick-access-panel">

            <div class="admin-panel__header">

                <div>

                    <span class="panel-code">
                        QUICK_ACCESS
                    </span>

                    <h2>
                        Acciones rápidas
                    </h2>

                </div>

                <i class="fa-solid fa-bolt-lightning"></i>

            </div>

            <div class="quick-access-grid">

                <a href="<?= BASE_URL ?>admin/proyectos">

                    <span>
                        <i class="fa-solid fa-diagram-project"></i>
                    </span>

                    <strong>
                        Nuevo proyecto
                    </strong>

                    <small>
                        Registrar contenido
                    </small>

                </a>

                <a href="<?= BASE_URL ?>admin/tecnologias">

                    <span>
                        <i class="fa-solid fa-microchip"></i>
                    </span>

                    <strong>
                        Nueva tecnología
                    </strong>

                    <small>
                        Ampliar el stack
                    </small>

                </a>

                <a href="<?= BASE_URL ?>admin/categorias">

                    <span>
                        <i class="fa-solid fa-tags"></i>
                    </span>

                    <strong>
                        Nueva categoría
                    </strong>

                    <small>
                        Clasificar proyectos
                    </small>

                </a>

                <a href="<?= BASE_URL ?>admin/multimedia">

                    <span>
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </span>

                    <strong>
                        Subir multimedia
                    </strong>

                    <small>
                        Fotos o videos
                    </small>

                </a>

            </div>

        </article>

        <article class="admin-panel activity-panel">

            <div class="admin-panel__header">

                <div>

                    <span class="panel-code">
                        ACTIVITY_LOG
                    </span>

                    <h2>
                        Actividad reciente
                    </h2>

                </div>

                <span class="activity-panel__status">
                    Sincronizado
                </span>

            </div>

            <div class="empty-state">

                <span>
                    <i class="fa-solid fa-wave-square"></i>
                </span>

                <strong>
                    Aún no hay actividad
                </strong>

                <p>
                    Las altas, ediciones y publicaciones aparecerán
                    en esta bitácora.
                </p>

            </div>

        </article>

    </section>
<?php elseif ($activeModule === 'proyectos'): ?>

    <?php
    require dirname(__DIR__)
        . '/Proyectos/index.php';
    ?>

<?php elseif ($activeModule === 'tecnologias'): ?>
<?php elseif ($activeModule === 'categorias'): ?>

    <?php

    /**
     * Contenido específico del módulo Categorías.
     */
    require dirname(__DIR__)
        . '/Categorias/index.php';

    ?>
<?php elseif ($activeModule === 'clientes'): ?>

    <?php
    require dirname(__DIR__)
        . '/Clientes/index.php';
    ?>
<?php elseif ($activeModule === 'multimedia'): ?>

    <?php
    require dirname(__DIR__)
        . '/Multimedia/index.php';
    ?>

<?php elseif ($activeModule === 'mensajes'): ?>

    <?php
    require dirname(__DIR__)
        . '/Mensajes/index.php';
    ?>

<?php elseif ($activeModule === 'configuracion'): ?>

    <?php
    require dirname(__DIR__)
        . '/Configuracion/index.php';
    ?>
<?php elseif ($moduleMeta): ?>

    <!-- ==================================================
         ENCABEZADO DE MÓDULOS PENDIENTES
    =================================================== -->

    <section class="module-hero">

        <div class="module-hero__icon">

            <i class="fa-solid <?= $adminEscape(
                                    $moduleMeta['icon'] ?? 'fa-cube'
                                ) ?>"></i>

        </div>

        <div class="module-hero__content">

            <span class="panel-code">
                <?= $adminEscape(
                    $moduleMeta['eyebrow'] ?? 'ADMIN_MODULE'
                ) ?>
            </span>

            <h2>
                <?= $adminEscape(
                    $moduleMeta['title'] ?? 'Módulo administrativo'
                ) ?>
            </h2>

            <p>
                <?= $adminEscape(
                    $moduleMeta['description'] ?? ''
                ) ?>
            </p>

        </div>

        <button
            class="admin-button admin-button--primary"
            type="button"
            disabled
            title="Se habilitará al implementar el CRUD">

            <i class="fa-solid fa-plus"></i>

            <?= $adminEscape(
                $moduleMeta['action'] ?? 'Nueva entrada'
            ) ?>

        </button>

    </section>

    <!-- ==================================================
         PLACEHOLDER DE MÓDULOS PENDIENTES
    =================================================== -->

    <section class="admin-panel module-placeholder">

        <div class="module-placeholder__terminal">

            <div class="terminal-bar">

                <span></span>
                <span></span>
                <span></span>

                <small>
                    module://<?= $adminEscape($activeModule) ?>
                </small>

            </div>

            <div class="terminal-body">

                <p>
                    <span>&gt;</span>
                    Inicializando módulo...
                </p>

                <p>
                    <span>[OK]</span>
                    Ruta MVC registrada.
                </p>

                <p>
                    <span>[OK]</span>
                    Navegación administrativa activa.
                </p>

                <p>
                    <span>[PENDING]</span>
                    Conectar modelo, base de datos y operaciones CRUD.
                </p>

            </div>

        </div>

        <div class="module-placeholder__info">

            <span class="panel-code">
                IMPLEMENTATION_SCOPE
            </span>

            <h3>
                Estructura preparada
            </h3>

            <p>
                Este acceso ya forma parte del menú y conserva
                el mismo layout administrativo.
            </p>

            <?php
            $moduleFeatures = $moduleMeta['features'] ?? [];
            ?>

            <?php if (!empty($moduleFeatures)): ?>

                <ul>

                    <?php foreach ($moduleFeatures as $feature): ?>

                        <li>

                            <i class="fa-solid fa-check"></i>

                            <?= $adminEscape($feature) ?>

                        </li>

                    <?php endforeach; ?>

                </ul>

            <?php endif; ?>

        </div>

    </section>

<?php else: ?>

    <!-- ==================================================
         MÓDULO NO CONFIGURADO
    =================================================== -->

    <section class="admin-panel">

        <div class="empty-state">

            <span>
                <i class="fa-solid fa-triangle-exclamation"></i>
            </span>

            <strong>
                Módulo no configurado
            </strong>

            <p>
                No se encontró información para construir esta sección
                del panel administrativo.
            </p>

            <a
                class="admin-button admin-button--primary"
                href="<?= BASE_URL ?>admin/index">

                <i class="fa-solid fa-arrow-left"></i>

                Volver al panel

            </a>

        </div>

    </section>

<?php endif; ?>

<?php

/**
 * El footer cierra:
 * - .admin-content
 * - main
 * - body
 * - html
 *
 * También carga admin.js y los scripts adicionales del módulo.
 */
require_once dirname(__DIR__)
    . '/Template/admin-footer.php';

?>