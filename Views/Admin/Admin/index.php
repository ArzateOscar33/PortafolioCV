<?php
$activeModule = $data['activeModule'] ?? 'dashboard';
$user = $data['user'] ?? ['name' => 'Administrador', 'role' => 'Superusuario', 'initials' => 'AD'];
$menu = $data['menu'] ?? [];
$moduleMeta = $data['moduleMeta'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#070711">
    <title><?= htmlspecialchars($data['title'] ?? 'Administración', ENT_QUOTES, 'UTF-8') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Orbitron:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="<?= BASE_URL ?>Assets/css/Admin/admin.css">

    <script>
        (function () {
            try {
                document.documentElement.dataset.theme = localStorage.getItem('cyberpunk-admin-theme') || 'dark';
            } catch (error) {
                document.documentElement.dataset.theme = 'dark';
            }
        })();
    </script>
</head>

<body>
    <div class="admin-grid" aria-hidden="true"></div>
    <div class="admin-noise" aria-hidden="true"></div>
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

    <aside class="admin-sidebar" id="adminSidebar" aria-label="Menú administrativo">
        <div class="sidebar-rail">
            <a class="admin-brand" href="<?= BASE_URL ?>admin/index" aria-label="Ir al panel principal">
                <span class="admin-brand__mark"><i class="fa-solid fa-bolt"></i></span>
                <span class="admin-brand__text">
                    <strong>CYBERPUNK</strong>
                    <small>CONTROL NODE</small>
                </span>
            </a>

            <section class="admin-user" aria-label="Usuario conectado">
                <div class="admin-user__avatar" aria-hidden="true">
                    <?= htmlspecialchars($user['initials'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="admin-user__identity">
                    <strong><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <span><?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <form class="admin-user__logout" action="<?= BASE_URL ?>admin/cerrarSesion" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrfToken'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" title="Cerrar sesión" aria-label="Cerrar sesión">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </section>

            <div class="sidebar-label"><span>Navegación</span></div>

            <nav class="admin-nav">
                <?php foreach ($menu as $moduleKey => $item): ?>
                    <?php $isActive = $activeModule === $moduleKey; ?>
                    <a
                        class="admin-nav__link<?= $isActive ? ' is-active' : '' ?>"
                        href="<?= BASE_URL . htmlspecialchars($item['route'], ENT_QUOTES, 'UTF-8') ?>"
                        <?= $isActive ? 'aria-current="page"' : '' ?>
                        title="<?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>">
                        <span class="admin-nav__icon"><i class="fa-solid <?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                        <span class="admin-nav__text"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php if (isset($item['badge'])): ?>
                            <span class="admin-nav__badge"><?= htmlspecialchars($item['badge'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="sidebar-footer">
                <button class="theme-toggle theme-toggle--sidebar" id="sidebarThemeToggle" type="button" aria-label="Cambiar tema">
                    <span class="theme-toggle__icon"><i class="fa-solid fa-moon"></i></span>
                    <span class="theme-toggle__text">Cambiar tema</span>
                </button>

                <a class="public-site-link" href="<?= BASE_URL ?>" target="_blank" rel="noopener noreferrer" title="Abrir portafolio">
                    <span><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
                    <span class="public-site-link__text">Ver portafolio</span>
                </a>
            </div>
        </div>
    </aside>

    <main class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar__start">
                <button class="mobile-menu-button" id="mobileMenuButton" type="button" aria-controls="adminSidebar" aria-expanded="false" aria-label="Abrir menú">
                    <i class="fa-solid fa-bars-staggered"></i>
                </button>

                <div>
                    <span class="page-kicker">ADMIN_INTERFACE / <?= strtoupper(htmlspecialchars($activeModule, ENT_QUOTES, 'UTF-8')) ?></span>
                    <h1><?= htmlspecialchars($data['pageTitle'] ?? 'Panel de control', ENT_QUOTES, 'UTF-8') ?></h1>
                </div>
            </div>

            <div class="admin-topbar__actions">
                <div class="system-status" title="Estado del panel">
                    <span class="system-status__dot"></span>
                    <span>Sistema operativo</span>
                </div>

                <time class="system-clock" id="systemClock" datetime=""></time>

                <button class="theme-toggle theme-toggle--top" id="topThemeToggle" type="button" aria-label="Cambiar a modo claro" title="Cambiar tema">
                    <i class="fa-solid fa-sun"></i>
                </button>
            </div>
        </header>

        <div class="admin-content">
            <?php if ($activeModule === 'dashboard'): ?>
                <section class="welcome-panel">
                    <div class="welcome-panel__content">
                        <span class="panel-code">SYS://WELCOME_BACK</span>
                        <h2>Bienvenido, <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <p>
                            Desde este nodo podrás administrar el contenido público de Cyberpunk.
                            La estructura ya está preparada para conectar cada módulo con sus controladores y modelos.
                        </p>
                        <div class="welcome-panel__actions">
                            <a class="admin-button admin-button--primary" href="<?= BASE_URL ?>admin/proyectos">
                                <i class="fa-solid fa-plus"></i>
                                Crear proyecto
                            </a>
                            <a class="admin-button admin-button--ghost" href="<?= BASE_URL ?>" target="_blank" rel="noopener noreferrer">
                                <i class="fa-solid fa-eye"></i>
                                Ver sitio público
                            </a>
                        </div>
                    </div>
                    <div class="welcome-panel__visual" aria-hidden="true">
                        <div class="core-orbit core-orbit--outer"></div>
                        <div class="core-orbit core-orbit--inner"></div>
                        <div class="core-symbol"><i class="fa-solid fa-code"></i></div>
                    </div>
                </section>

                <section class="stats-grid" aria-label="Resumen del portafolio">
                    <?php foreach ($data['stats'] ?? [] as $stat): ?>
                        <article class="stat-card stat-card--<?= htmlspecialchars($stat['accent'], ENT_QUOTES, 'UTF-8') ?>">
                            <div class="stat-card__head">
                                <span class="stat-card__icon"><i class="fa-solid <?= htmlspecialchars($stat['icon'], ENT_QUOTES, 'UTF-8') ?>"></i></span>
                                <span class="stat-card__signal">LIVE</span>
                            </div>
                            <strong><?= htmlspecialchars($stat['value'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span><?= htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8') ?></span>
                        </article>
                    <?php endforeach; ?>
                </section>

                <section class="dashboard-grid">
                    <article class="admin-panel quick-access-panel">
                        <div class="admin-panel__header">
                            <div>
                                <span class="panel-code">QUICK_ACCESS</span>
                                <h2>Acciones rápidas</h2>
                            </div>
                            <i class="fa-solid fa-bolt-lightning"></i>
                        </div>

                        <div class="quick-access-grid">
                            <a href="<?= BASE_URL ?>admin/proyectos">
                                <span><i class="fa-solid fa-diagram-project"></i></span>
                                <strong>Nuevo proyecto</strong>
                                <small>Registrar contenido</small>
                            </a>
                            <a href="<?= BASE_URL ?>admin/tecnologias">
                                <span><i class="fa-solid fa-microchip"></i></span>
                                <strong>Nueva tecnología</strong>
                                <small>Ampliar el stack</small>
                            </a>
                            <a href="<?= BASE_URL ?>admin/categorias">
                                <span><i class="fa-solid fa-tags"></i></span>
                                <strong>Nueva categoría</strong>
                                <small>Clasificar proyectos</small>
                            </a>
                            <a href="<?= BASE_URL ?>admin/multimedia">
                                <span><i class="fa-solid fa-cloud-arrow-up"></i></span>
                                <strong>Subir multimedia</strong>
                                <small>Fotos o videos</small>
                            </a>
                        </div>
                    </article>

                    <article class="admin-panel activity-panel">
                        <div class="admin-panel__header">
                            <div>
                                <span class="panel-code">ACTIVITY_LOG</span>
                                <h2>Actividad reciente</h2>
                            </div>
                            <span class="activity-panel__status">Sincronizado</span>
                        </div>

                        <div class="empty-state">
                            <span><i class="fa-solid fa-wave-square"></i></span>
                            <strong>Aún no hay actividad</strong>
                            <p>Las altas, ediciones y publicaciones aparecerán en esta bitácora.</p>
                        </div>
                    </article>
                </section>
            <?php elseif ($moduleMeta): ?>
                <section class="module-hero">
                    <div class="module-hero__icon">
                        <i class="fa-solid <?= htmlspecialchars($moduleMeta['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
                    </div>
                    <div class="module-hero__content">
                        <span class="panel-code"><?= htmlspecialchars($moduleMeta['eyebrow'], ENT_QUOTES, 'UTF-8') ?></span>
                        <h2><?= htmlspecialchars($moduleMeta['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <p><?= htmlspecialchars($moduleMeta['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <button class="admin-button admin-button--primary" type="button" disabled title="Se habilitará al implementar el CRUD">
                        <i class="fa-solid fa-plus"></i>
                        <?= htmlspecialchars($moduleMeta['action'], ENT_QUOTES, 'UTF-8') ?>
                    </button>
                </section>

                <section class="admin-panel module-placeholder">
                    <div class="module-placeholder__terminal">
                        <div class="terminal-bar">
                            <span></span><span></span><span></span>
                            <small>module://<?= htmlspecialchars($activeModule, ENT_QUOTES, 'UTF-8') ?></small>
                        </div>
                        <div class="terminal-body">
                            <p><span>&gt;</span> Inicializando módulo...</p>
                            <p><span>[OK]</span> Ruta MVC registrada.</p>
                            <p><span>[OK]</span> Navegación administrativa activa.</p>
                            <p><span>[PENDING]</span> Conectar modelo, base de datos y operaciones CRUD.</p>
                        </div>
                    </div>

                    <div class="module-placeholder__info">
                        <span class="panel-code">IMPLEMENTATION_SCOPE</span>
                        <h3>Estructura preparada</h3>
                        <p>Este acceso ya forma parte del menú y conserva el mismo layout administrativo.</p>
                        <ul>
                            <?php foreach ($moduleMeta['features'] as $feature): ?>
                                <li><i class="fa-solid fa-check"></i><?= htmlspecialchars($feature, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <script src="<?= BASE_URL ?>Assets/js/Admin/admin.js"></script>
</body>

</html>
