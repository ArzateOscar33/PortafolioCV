<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cyberpunk | Portafolio de Desarrollo</title>
    <meta
        name="description"
        content="Portafolio profesional de desarrollo web, escritorio, redes, infraestructura y ciberseguridad.">
    <meta name="theme-color" content="#070711">

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        crossorigin="anonymous">

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        referrerpolicy="no-referrer">

    <!-- Fuentes -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Orbitron:wght@500;600;700;800;900&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="<?php BASE_URL  ?>Assets/css/Principal/style.css">
</head>

<body>
    <div class="cp-grid" aria-hidden="true"></div>
    <div class="cp-noise" aria-hidden="true"></div>

    <!-- Navegación -->
    <nav class="navbar navbar-expand-lg fixed-top cp-navbar" aria-label="Navegación principal">
        <div class="container">
            <a class="navbar-brand" href="#inicio">
                <span class="brand-mark" aria-hidden="true">
                    <i class="fa-solid fa-bolt"></i>
                </span>
                Cyberpunk
            </a>

            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNavbar"
                aria-controls="mainNavbar"
                aria-expanded="false"
                aria-label="Mostrar navegación">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav ms-auto align-items-lg-center">
                    <li class="nav-item"><a class="nav-link active" href="#inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#tecnologias">Tecnologías</a></li>
                    <li class="nav-item"><a class="nav-link" href="#acerca">Acerca de mí</a></li>
                    <li class="nav-item"><a class="nav-link" href="#proyectos">Proyectos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#servicios">Servicios</a></li>

                    <li class="nav-item ms-lg-2 mt-3 mt-lg-0">
                        <a class="cp-btn cp-btn-sm" href="#contacto">
                            <i class="fa-regular fa-calendar"></i>
                            Contactar
                        </a>
                    </li>
                    <li class="nav-item ms-lg-2 mt-3 mt-lg-0">
                        <a class="cp-btn cp-btn-sm" href="<?php BASE_URL; ?>login">
                            <i class="fa-regular fa-user"></i>
                            Iniciar Sesion
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <!-- Hero -->
        <section class="hero" id="inicio">
            <div class="floating-node" aria-hidden="true"></div>
            <div class="floating-node" aria-hidden="true"></div>
            <div class="floating-node" aria-hidden="true"></div>

            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-8">
                        <div class="hero-content">
                            <span class="hero-kicker">Ingeniería de software + infraestructura</span>

                            <h1 class="hero-title">
                                <span class="glitch" data-text="Código">Código</span><br>
                                <span class="outline">sin límites</span>
                            </h1>

                            <p class="hero-copy">
                                Diseño y construyo soluciones digitales con una mezcla de
                                <strong>frontend, backend, automatización, redes e infraestructura</strong>.
                                Este portafolio reúne proyectos reales, retos técnicos y resultados medibles.
                            </p>

                            <div class="hero-actions">
                                <a class="cp-btn" href="#proyectos">
                                    <i class="fa-solid fa-layer-group"></i>
                                    Explorar proyectos
                                </a>

                                <a class="cp-btn cp-btn-secondary" href="#contacto">
                                    <i class="fa-solid fa-satellite-dish"></i>
                                    Iniciar conexión
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="hero-terminal" aria-label="Resumen técnico">
                            <div class="terminal-head">
                                <span class="terminal-dot"></span>
                                <span class="terminal-dot"></span>
                                <span class="terminal-dot"></span>
                                <span class="terminal-title">portfolio.sys</span>
                            </div>

                            <div class="terminal-body">
                                <div class="terminal-line">
                                    <span class="terminal-prompt">root@cyberpunk:~$</span>
                                    <span class="terminal-command"> init portfolio</span>
                                </div>
                                <div class="terminal-line">
                                    <span class="terminal-ok">[OK]</span> Cargando experiencia profesional...
                                </div>
                                <div class="terminal-line">
                                    <span class="terminal-ok">[OK]</span> Vinculando proyectos y tecnologías...
                                </div>
                                <div class="terminal-line">
                                    <span class="terminal-warning">[INFO]</span> MVC + PHP + JavaScript
                                </div>
                                <div class="terminal-line">
                                    <span class="terminal-warning">[INFO]</span> Docker + Linux + Redes
                                </div>
                                <div class="terminal-line">
                                    <span class="terminal-prompt">status:</span>
                                    disponible para nuevos retos
                                </div>
                                <div class="terminal-line mt-3">
                                    <span class="terminal-prompt">root@cyberpunk:~$</span>
                                    <span class="cursor" aria-hidden="true"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Métricas -->
        <section class="metric-strip" aria-label="Métricas del portafolio">
            <div class="container">
                <div class="row g-3">
                    <div class="col-6 col-lg-3">
                        <div class="metric-card">
                            <span class="metric-value" data-counter="12">0</span>
                            <span class="metric-label">Proyectos</span>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="metric-card">
                            <span class="metric-value" data-counter="10">0</span>
                            <span class="metric-label">Tecnologías</span>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="metric-card">
                            <span class="metric-value" data-counter="5">0</span>
                            <span class="metric-label">Categorías</span>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="metric-card">
                            <span class="metric-value">24/7</span>
                            <span class="metric-label">Sistemas en línea</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tecnologías -->
        <section class="section" id="tecnologias">
            <div class="container">
                <div class="section-heading reveal-section">
                    <span class="section-code">// stack_tecnológico</span>
                    <h2 class="section-title">Tecnologías que <span>domino</span></h2>
                    <p class="section-copy">
                        Herramientas utilizadas para desarrollar, desplegar y mantener productos digitales.
                        En el backend, esta lista se alimentará desde el catálogo de tecnologías.
                    </p>
                </div>

                <div class="row g-3" id="technologyGrid"></div>
            </div>
        </section>

        <!-- Acerca de mí -->
        <section class="section" id="acerca">
            <div class="container">
                <div class="row g-4 align-items-stretch">
                    <div class="col-lg-5 reveal-section">
                        <div class="profile-frame">
                            <div class="profile-label">
                                <strong>Oscar Arzate</strong>
                                <span>Ingeniero en Sistemas</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7 reveal-section">
                        <div class="about-panel h-100">
                            <span class="section-code">// perfil_profesional</span>
                            <h2 class="section-title">Acerca de <span>mí</span></h2>

                            <p class="section-copy">
                                Soy ingeniero en sistemas enfocado en convertir necesidades operativas en
                                soluciones funcionales. Trabajo desde la arquitectura y la base de datos hasta
                                la interfaz, el despliegue y la administración de infraestructura.
                            </p>

                            <p class="section-copy">
                                Mi experiencia combina desarrollo web con PHP y JavaScript, administración
                                Linux, contenedores Docker, redes y automatización. Me interesa construir
                                sistemas mantenibles, seguros y preparados para crecer.
                            </p>

                            <ul class="about-list">
                                <li>
                                    <i class="fa-solid fa-code"></i>
                                    <span>Desarrollo de sistemas empresariales con arquitectura MVC.</span>
                                </li>
                                <li>
                                    <i class="fa-solid fa-server"></i>
                                    <span>Despliegue y operación de servicios sobre Linux y Docker.</span>
                                </li>
                                <li>
                                    <i class="fa-solid fa-diagram-project"></i>
                                    <span>Diseño de bases de datos, flujos operativos e integraciones.</span>
                                </li>
                                <li>
                                    <i class="fa-solid fa-shield-halved"></i>
                                    <span>Enfoque en seguridad, trazabilidad, respaldo y mantenibilidad.</span>
                                </li>
                            </ul>

                            <div class="hero-actions">
                                <a class="cp-btn" href="#contacto">
                                    <i class="fa-solid fa-paper-plane"></i>
                                    Hablemos de tu proyecto
                                </a>
                                <a class="cp-btn cp-btn-secondary" href="#" aria-label="Descargar currículum">
                                    <i class="fa-solid fa-file-arrow-down"></i>
                                    Descargar CV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Proyectos -->
        <section class="section" id="proyectos">
            <div class="container">
                <div class="section-heading reveal-section">
                    <span class="section-code">// archivo_de_proyectos</span>
                    <h2 class="section-title">Proyectos <span>destacados</span></h2>
                    <p class="section-copy">
                        Filtra por categoría y abre cualquier tarjeta para consultar su galería,
                        tecnologías, información, retos técnicos y enlaces disponibles.
                    </p>
                </div>

                <div
                    class="project-filters reveal-section"
                    id="projectFilters"
                    role="group"
                    aria-label="Filtrar proyectos por categoría"></div>

                <div class="row g-4" id="projectsGrid"></div>
            </div>
        </section>

        <!-- Servicios -->
        <section class="section" id="servicios">
            <div class="container">
                <div class="section-heading reveal-section">
                    <span class="section-code">// capacidades</span>
                    <h2 class="section-title">Servicios y <span>soluciones</span></h2>
                    <p class="section-copy">
                        Desde una idea inicial hasta un sistema desplegado, documentado y listo para operar.
                    </p>
                </div>

                <div class="services-grid">
                    <article class="service-card reveal-section">
                        <span class="service-number">01 / WEB</span>
                        <div class="service-icon"><i class="fa-solid fa-window-maximize"></i></div>
                        <h3 class="service-title">Sistemas web</h3>
                        <p class="service-copy">
                            Plataformas empresariales, paneles administrativos, portales de clientes,
                            APIs y automatización de procesos.
                        </p>
                    </article>

                    <article class="service-card reveal-section">
                        <span class="service-number">02 / INFRA</span>
                        <div class="service-icon"><i class="fa-solid fa-server"></i></div>
                        <h3 class="service-title">Infraestructura</h3>
                        <p class="service-copy">
                            Linux, Docker, reverse proxy, DNS local, almacenamiento y monitoreo.
                        </p>
                    </article>

                    <article class="service-card reveal-section">
                        <span class="service-number">03 / DATA</span>
                        <div class="service-icon"><i class="fa-solid fa-database"></i></div>
                        <h3 class="service-title">Bases de datos</h3>
                        <p class="service-copy">
                            Modelado relacional, consultas, vistas, integridad y optimización.
                        </p>
                    </article>

                    <article class="service-card reveal-section">
                        <span class="service-number">04 / SECURITY</span>
                        <div class="service-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <h3 class="service-title">Seguridad y redes</h3>
                        <p class="service-copy">
                            Segmentación, acceso remoto, endurecimiento básico, control de sesiones,
                            trazabilidad y protección de servicios.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Contacto -->
        <section class="section" id="contacto">
            <div class="container">
                <div class="contact-panel">
                    <div class="row g-5">
                        <div class="col-lg-5 reveal-section">
                            <span class="section-code">// canal_de_contacto</span>
                            <h2 class="section-title">Iniciemos una <span>conexión</span></h2>
                            <p class="section-copy">
                                Cuéntame qué necesitas construir, mejorar o automatizar. Puedes proponer una
                                fecha para una reunión presencial o en línea.
                            </p>

                            <div class="contact-info">
                                <div class="contact-item">
                                    <i class="fa-regular fa-envelope"></i>
                                    <span>correo@ejemplo.com</span>
                                </div>
                                <div class="contact-item">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span>Tijuana, Baja California, México</span>
                                </div>
                                <div class="contact-item">
                                    <i class="fa-regular fa-clock"></i>
                                    <span>Respuesta estimada: 24–48 horas</span>
                                </div>
                            </div>

                            <div class="social-links" aria-label="Redes profesionales">
                                <a class="social-link" href="#" aria-label="GitHub">
                                    <i class="fa-brands fa-github"></i>
                                </a>
                                <a class="social-link" href="#" aria-label="LinkedIn">
                                    <i class="fa-brands fa-linkedin-in"></i>
                                </a>
                                <a class="social-link" href="#" aria-label="WhatsApp">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </a>
                                <a class="social-link" href="#" aria-label="Correo electrónico">
                                    <i class="fa-regular fa-envelope"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-7 reveal-section">
                            <form id="contactForm" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="contactName">Nombre</label>
                                        <input
                                            class="form-control"
                                            id="contactName"
                                            name="name"
                                            type="text"
                                            placeholder="Tu nombre"
                                            required>
                                        <div class="invalid-feedback">Escribe tu nombre.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="contactEmail">Correo electrónico</label>
                                        <input
                                            class="form-control"
                                            id="contactEmail"
                                            name="email"
                                            type="email"
                                            placeholder="nombre@empresa.com"
                                            required>
                                        <div class="invalid-feedback">Escribe un correo válido.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="contactCompany">Empresa</label>
                                        <input
                                            class="form-control"
                                            id="contactCompany"
                                            name="company"
                                            type="text"
                                            placeholder="Opcional">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="contactService">Tipo de proyecto</label>
                                        <select class="form-select" id="contactService" name="service" required>
                                            <option value="" selected disabled>Selecciona una opción</option>
                                            <option value="web">Sistema web</option>
                                            <option value="desktop">Aplicación de escritorio</option>
                                            <option value="mobile">Aplicación móvil</option>
                                            <option value="infrastructure">Infraestructura y redes</option>
                                            <option value="security">Ciberseguridad</option>
                                            <option value="consulting">Consultoría técnica</option>
                                        </select>
                                        <div class="invalid-feedback">Selecciona el tipo de proyecto.</div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="meetingDate">Fecha sugerida</label>
                                        <input
                                            class="form-control"
                                            id="meetingDate"
                                            name="meetingDate"
                                            type="date">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label" for="meetingMode">Modalidad</label>
                                        <select class="form-select" id="meetingMode" name="meetingMode">
                                            <option value="online">En línea</option>
                                            <option value="onsite">Presencial</option>
                                            <option value="either">Cualquiera</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label" for="contactMessage">Mensaje</label>
                                        <textarea
                                            class="form-control"
                                            id="contactMessage"
                                            name="message"
                                            placeholder="Describe brevemente tu proyecto, problema u objetivo..."
                                            required></textarea>
                                        <div class="invalid-feedback">Escribe un mensaje.</div>
                                    </div>

                                    <div class="col-12">
                                        <button class="cp-btn w-100" type="submit">
                                            <i class="fa-solid fa-paper-plane"></i>
                                            Enviar solicitud
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="cp-footer">
        <div class="container">
            <div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center">
                <span class="footer-brand">Cyberpunk Portfolio</span>
                <span>© <span id="currentYear"></span> Oscar Arzate. Sistema operativo: estable.</span>
            </div>
        </div>
    </footer>

    <!-- Modal de proyecto -->
    <div
        class="modal fade"
        id="projectModal"
        tabindex="-1"
        aria-labelledby="projectModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <span class="section-code mb-1" id="projectModalCategory"></span>
                        <h2 class="modal-title fs-4 modal-project-title" id="projectModalTitle"></h2>
                    </div>
                    <button
                        class="btn-close"
                        type="button"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <img
                                class="modal-main-image"
                                id="projectModalMainImage"
                                src=""
                                alt="">
                            <div class="gallery-strip" id="projectModalGallery"></div>

                            <div class="video-placeholder mt-3" id="projectVideoPlaceholder">
                                <div>
                                    <i class="fa-solid fa-circle-play"></i>
                                    <span>El backend podrá insertar aquí videos subidos o enlaces externos.</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="detail-block mb-3">
                                <div class="detail-label">Descripción</div>
                                <p class="detail-text" id="projectModalDescription"></p>
                            </div>

                            <div class="detail-block mb-3">
                                <div class="detail-label">Tecnologías</div>
                                <div class="modal-tech-list" id="projectModalTechnologies"></div>
                            </div>

                            <div class="detail-block mb-3">
                                <div class="detail-label">Reto técnico</div>
                                <p class="detail-text" id="projectModalChallenge"></p>
                            </div>

                            <div class="detail-block mb-3">
                                <div class="detail-label">Resultado</div>
                                <p class="detail-text" id="projectModalResult"></p>
                            </div>

                            <div class="detail-block mb-3" id="projectModalClientBlock">
                                <div class="detail-label">Cliente / propietario</div>
                                <p class="detail-text" id="projectModalClient"></p>
                            </div>

                            <div
                                class="private-repo-message"
                                id="projectModalPrivateMessage"
                                role="status">
                                <i class="fa-solid fa-lock me-1"></i>
                                Oops, este repositorio es privado. Puedes revisar la galería de fotos de este proyecto.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <a
                        class="cp-btn cp-btn-secondary"
                        id="projectModalGithub"
                        href="#"
                        target="_blank"
                        rel="noopener noreferrer">
                        <i class="fa-brands fa-github"></i>
                        Ver repositorio
                    </a>

                    <a
                        class="cp-btn"
                        id="projectModalLive"
                        href="#"
                        target="_blank"
                        rel="noopener noreferrer">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        Abrir proyecto
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast de formulario -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div
            class="toast cp-toast"
            id="contactToast"
            role="status"
            aria-live="polite"
            aria-atomic="true">
            <div class="toast-header bg-transparent text-white border-0">
                <i class="fa-solid fa-circle-check me-2 text-success"></i>
                <strong class="me-auto">Solicitud registrada</strong>
                <button
                    class="btn-close"
                    type="button"
                    data-bs-dismiss="toast"
                    aria-label="Cerrar"></button>
            </div>
            <div class="toast-body">
                El frontend está funcionando. La conexión con PHP y correo se agregará en el backend.
            </div>
        </div>
    </div>

    <!-- Dependencias -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.2/anime.min.js"></script>

    <script src="<?php BASE_URL ?>Assets/js/Principal/index.js"> </script>
</body>
< /html>