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
                        <a
                            class="cp-btn cp-btn-sm"
                            href="<?= BASE_URL ?>login">

                            <i class="fa-regular fa-user"></i>
                            Iniciar sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>