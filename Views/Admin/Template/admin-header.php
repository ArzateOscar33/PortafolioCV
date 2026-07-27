<?php

/**
 * Layout superior del panel administrativo.
 *
 * La vista que incluye este archivo debe recibir el arreglo $data
 * desde el controlador.
 */

if (!isset($data) || !is_array($data)) {
    $data = [];
}

$activeModule = $data['activeModule'] ?? 'dashboard';

$user = $data['user'] ?? [
    'name' => 'Administrador',
    'role' => 'Superusuario',
    'initials' => 'AD',
];

$menu = $data['menu'] ?? [];
$moduleMeta = $data['moduleMeta'] ?? null;

$adminEscape = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
};

$additionalStyles = $data['styles'] ?? [];

if (!is_array($additionalStyles)) {
    $additionalStyles = [];
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta
        name="theme-color"
        content="#070711">

    <title>
        <?= $adminEscape(
            $data['title'] ?? 'Administración'
        ) ?>
    </title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Orbitron:wght@500;600;700;800&display=swap"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        referrerpolicy="no-referrer">
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        crossorigin="anonymous">

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>Assets/css/Admin/admin.css">


    <?php foreach ($additionalStyles as $stylesheet): ?>

        <?php
        $stylesheet = ltrim(
            (string) $stylesheet,
            '/'
        );
        ?>

        <link
            rel="stylesheet"
            href="<?= $adminEscape(
                        BASE_URL . $stylesheet
                    ) ?>">

    <?php endforeach; ?>

    <script>
        (() => {
            let theme = "dark";

            try {
                theme =
                    localStorage.getItem(
                        "cyberpunk-admin-theme"
                    ) || "dark";
            } catch (error) {
                theme = "dark";
            }

            document.documentElement.dataset.theme = theme;
            document.documentElement.dataset.bsTheme = theme;
        })();
    </script>
</head>

<body>

    <div
        class="admin-grid"
        aria-hidden="true">
    </div>

    <div
        class="admin-noise"
        aria-hidden="true">
    </div>

    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
        aria-hidden="true">
    </div>

    <!-- ==============================================
         SIDEBAR
    =============================================== -->

    <aside
        class="admin-sidebar"
        id="adminSidebar"
        aria-label="Menú administrativo">

        <div class="sidebar-rail">

            <a
                class="admin-brand"
                href="<?= BASE_URL ?>admin/index"
                aria-label="Ir al panel principal">

                <span class="admin-brand__mark">
                    <i class="fa-solid fa-bolt"></i>
                </span>

                <span class="admin-brand__text">
                    <strong>CYBERPUNK</strong>
                    <small>CONTROL NODE</small>
                </span>

            </a>

            <!-- Usuario -->

            <section
                class="admin-user"
                aria-label="Usuario conectado">

                <div
                    class="admin-user__avatar"
                    aria-hidden="true">
                    <?= $adminEscape(
                        $user['initials'] ?? 'AD'
                    ) ?>
                </div>

                <div class="admin-user__identity">

                    <strong>
                        <?= $adminEscape(
                            $user['name'] ?? 'Administrador'
                        ) ?>
                    </strong>

                    <span>
                        <?= $adminEscape(
                            $user['role'] ?? 'Sin rol'
                        ) ?>
                    </span>

                </div>

                <form
                    class="admin-user__logout"
                    action="<?= BASE_URL ?>login/salir"
                    method="POST">

                    <input
                        type="hidden"
                        name="csrf_token_sesion"
                        value="<?= $adminEscape(
                                    $data['csrfTokenSesion'] ?? ''
                                ) ?>">

                    <button
                        type="submit"
                        title="Cerrar sesión"
                        aria-label="Cerrar sesión">

                        <i class="fa-solid fa-arrow-right-from-bracket"></i>

                        <span>
                            Cerrar sesión
                        </span>

                    </button>

                </form>

            </section>

            <div class="sidebar-label">
                <span>Navegación</span>
            </div>

            <!-- Menú -->

            <nav class="admin-nav">

                <?php foreach ($menu as $moduleKey => $item): ?>

                    <?php
                    $isActive = $activeModule === $moduleKey;
                    ?>

                    <a
                        class="admin-nav__link<?= $isActive ? ' is-active' : '' ?>"
                        href="<?= $adminEscape(
                                    BASE_URL . ($item['route'] ?? '')
                                ) ?>"
                        <?= $isActive ? 'aria-current="page"' : '' ?>
                        title="<?= $adminEscape(
                                    $item['label'] ?? ''
                                ) ?>">

                        <span class="admin-nav__icon">

                            <i class="fa-solid <?= $adminEscape(
                                                    $item['icon'] ?? ''
                                                ) ?>"></i>

                        </span>

                        <span class="admin-nav__text">
                            <?= $adminEscape(
                                $item['label'] ?? ''
                            ) ?>
                        </span>

                        <?php if (isset($item['badge'])): ?>

                            <span class="admin-nav__badge">
                                <?= $adminEscape(
                                    $item['badge']
                                ) ?>
                            </span>

                        <?php endif; ?>

                    </a>

                <?php endforeach; ?>

            </nav>

            <!-- Pie del sidebar -->

            <div class="sidebar-footer">

                <button
                    class="theme-toggle theme-toggle--sidebar"
                    id="sidebarThemeToggle"
                    type="button"
                    aria-label="Cambiar tema">

                    <span class="theme-toggle__icon">
                        <i class="fa-solid fa-moon"></i>
                    </span>

                    <span class="theme-toggle__text">
                        Cambiar tema
                    </span>

                </button>

                <a
                    class="public-site-link"
                    href="<?= BASE_URL ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    title="Abrir portafolio">

                    <span>
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </span>

                    <span class="public-site-link__text">
                        Ver portafolio
                    </span>

                </a>

            </div>

        </div>

    </aside>

    <!-- ==============================================
         CONTENIDO PRINCIPAL
    =============================================== -->

    <main class="admin-main">

        <header class="admin-topbar">

            <div class="admin-topbar__start">

                <button
                    class="mobile-menu-button"
                    id="mobileMenuButton"
                    type="button"
                    aria-controls="adminSidebar"
                    aria-expanded="false"
                    aria-label="Abrir menú">

                    <i class="fa-solid fa-bars-staggered"></i>

                </button>

                <div>

                    <span class="page-kicker">
                        ADMIN_INTERFACE /
                        <?= $adminEscape(
                            strtoupper($activeModule)
                        ) ?>
                    </span>

                    <h1>
                        <?= $adminEscape(
                            $data['pageTitle']
                                ?? 'Panel de control'
                        ) ?>
                    </h1>

                </div>

            </div>

            <div class="admin-topbar__actions">

                <div
                    class="system-status"
                    title="Estado del panel">

                    <span class="system-status__dot"></span>

                    <span>
                        Sistema operativo
                    </span>

                </div>

                <time
                    class="system-clock"
                    id="systemClock"
                    datetime="">
                </time>

                <button
                    class="theme-toggle theme-toggle--top"
                    id="topThemeToggle"
                    type="button"
                    aria-label="Cambiar a modo claro"
                    title="Cambiar tema">

                    <i class="fa-solid fa-sun"></i>

                </button>

            </div>

        </header>

        <div class="admin-content">