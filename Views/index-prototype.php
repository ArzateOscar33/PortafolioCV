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













    </main>





    <!-- Toast de formulario -->


    <!-- Dependencias -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.2/anime.min.js"></script>

    <script src="<?php BASE_URL ?>Assets/js/Principal/index.js"> </script>
    <script src="<?= BASE_URL ?>Assets/js/Principal/site-config.js"></script>
</body>
< /html>