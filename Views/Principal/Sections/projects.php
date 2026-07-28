<?php

/*
 * Proyectos y categorías entregados por HomeController.
 */
$proyectos = is_array($data['proyectos'] ?? null)
    ? $data['proyectos']
    : [];

$categoriasProyectos = is_array(
    $data['categoriasProyectos'] ?? null
)
    ? $data['categoriasProyectos']
    : [];

/**
 * Convierte una entrada multimedia en una URL utilizable.
 *
 * Puede recibir:
 * - Una URL externa.
 * - Una ruta local relativa al proyecto.
 */
$resolveProjectMediaUrl = static function ($archivo): string {
    if (!is_array($archivo)) {
        return '';
    }

    $urlExterna = trim((string) (
        $archivo['url_externa'] ?? ''
    ));

    if ($urlExterna !== '') {
        /*
         * Solo admitimos URLs HTTP o HTTPS.
         */
        if (preg_match('~^https?://~i', $urlExterna)) {
            return $urlExterna;
        }

        return '';
    }

    $rutaArchivo = trim((string) (
        $archivo['ruta_archivo'] ?? ''
    ));

    if ($rutaArchivo === '') {
        return '';
    }

    /*
     * Si por alguna razón ruta_archivo contiene
     * una URL absoluta, también la aceptamos.
     */
    if (preg_match('~^https?://~i', $rutaArchivo)) {
        return $rutaArchivo;
    }

    return rtrim(BASE_URL, '/')
        . '/'
        . ltrim($rutaArchivo, '/');
};

/**
 * Localiza un tipo de enlace dentro de los enlaces
 * relacionados con un proyecto.
 */
$findProjectLink = static function (
    array $enlaces,
    string $tipo
): ?array {
    foreach ($enlaces as $enlace) {
        if (!is_array($enlace)) {
            continue;
        }

        if (($enlace['tipo_slug'] ?? '') === $tipo) {
            return $enlace;
        }
    }

    return null;
};

/**
 * Valida colores hexadecimales antes de colocarlos
 * dentro de un atributo style.
 */
$normalizeProjectColor = static function (
    $color,
    string $fallback = '#00F6FF'
): string {
    $color = strtoupper(trim((string) $color));

    if (!preg_match('/^#[0-9A-F]{6}$/', $color)) {
        return $fallback;
    }

    return $color;
};

?>

<!-- =====================================================
     PROYECTOS
====================================================== -->

<section
    class="section"
    id="proyectos">

    <div class="container">

        <!-- Encabezado de sección -->
        <div class="section-heading reveal-section">

            <span class="section-code">
                // archivo_de_proyectos
            </span>

            <h2 class="section-title">
                Proyectos <span>destacados</span>
            </h2>

            <p class="section-copy">
                Filtra por categoría y abre cualquier tarjeta para
                consultar su galería, tecnologías, retos y resultados.
            </p>

        </div>

        <!-- Filtros -->
        <?php if (!empty($categoriasProyectos)): ?>

            <div
                class="project-filters reveal-section"
                id="projectFilters"
                role="group"
                aria-label="Filtrar proyectos por categoría">

                <button
                    class="filter-btn active"
                    type="button"
                    data-filter="all"
                    aria-pressed="true">

                    Todos

                </button>

                <?php foreach ($categoriasProyectos as $categoria): ?>

                    <?php

                    $categoriaSlug = trim((string) (
                        $categoria['slug'] ?? ''
                    ));

                    $categoriaNombre = trim((string) (
                        $categoria['nombre'] ?? ''
                    ));

                    if (
                        $categoriaSlug === ''
                        || $categoriaNombre === ''
                    ) {
                        continue;
                    }

                    ?>

                    <button
                        class="filter-btn"
                        type="button"
                        data-filter="<?= $principalEscape(
                                            $categoriaSlug
                                        ) ?>"
                        aria-pressed="false">

                        <?= $principalEscape(
                            $categoriaNombre
                        ) ?>

                    </button>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

        <!-- Cuadrícula de proyectos -->
        <div
            class="row g-4"
            id="projectsGrid">

            <?php if (empty($proyectos)): ?>

                <div class="col-12">

                    <div class="empty-state">

                        <i
                            class="fa-solid fa-folder-open fa-2x mb-3"
                            aria-hidden="true">
                        </i>

                        <p class="mb-0">
                            No existen proyectos publicados.
                        </p>

                    </div>

                </div>

            <?php else: ?>

                <?php foreach ($proyectos as $proyecto): ?>

                    <?php

                    if (!is_array($proyecto)) {
                        continue;
                    }

                    $idProyecto = (int) (
                        $proyecto['id_proyecto'] ?? 0
                    );

                    $titulo = trim((string) (
                        $proyecto['titulo'] ?? ''
                    ));

                    $resumen = trim((string) (
                        $proyecto['resumen_corto'] ?? ''
                    ));

                    /*
                     * Evitamos producir elementos sin identidad.
                     */
                    if ($idProyecto <= 0 || $titulo === '') {
                        continue;
                    }

                    $categorias = is_array(
                        $proyecto['categorias'] ?? null
                    )
                        ? $proyecto['categorias']
                        : [];

                    $tecnologiasProyecto = is_array(
                        $proyecto['tecnologias'] ?? null
                    )
                        ? $proyecto['tecnologias']
                        : [];

                    $enlaces = is_array(
                        $proyecto['enlaces'] ?? null
                    )
                        ? $proyecto['enlaces']
                        : [];

                    $github = $findProjectLink(
                        $enlaces,
                        'github'
                    );

                    $githubEsPrivado = $github !== null
                        && (int) (
                            $github['es_privado'] ?? 0
                        ) === 1;

                    $githubUrl = $github !== null
                        ? trim((string) (
                            $github['url'] ?? ''
                        ))
                        : '';

                    /*
                     * Una URL privada debe llegar vacía desde HomeModel.
                     * Aun así, la anulamos nuevamente por seguridad.
                     */
                    if ($githubEsPrivado) {
                        $githubUrl = '';
                    }

                    $categoriaPrincipal = $categorias[0] ?? null;

                    $categoriaPrincipalNombre = is_array(
                        $categoriaPrincipal
                    )
                        ? trim((string) (
                            $categoriaPrincipal['nombre'] ?? ''
                        ))
                        : '';

                    $slugsCategorias = [];

                    foreach ($categorias as $categoriaProyecto) {
                        if (!is_array($categoriaProyecto)) {
                            continue;
                        }

                        $slugCategoria = trim((string) (
                            $categoriaProyecto['slug'] ?? ''
                        ));

                        if ($slugCategoria !== '') {
                            $slugsCategorias[] = $slugCategoria;
                        }
                    }

                    $slugsCategorias = array_values(
                        array_unique($slugsCategorias)
                    );

                    $portada = is_array(
                        $proyecto['portada'] ?? null
                    )
                        ? $proyecto['portada']
                        : null;

                    $portadaUrl = $resolveProjectMediaUrl(
                        $portada
                    );

                    $portadaAlt = trim((string) (
                        $portada['texto_alternativo']
                        ?? ''
                    ));

                    if ($portadaAlt === '') {
                        $portadaAlt = "Vista previa de {$titulo}";
                    }

                    ?>

                    <div
                        class="col-md-6 col-xl-4 project-grid-item"
                        data-categories="<?= $principalEscape(
                                                implode(' ', $slugsCategorias)
                                            ) ?>">

                        <article
                            class="project-card"
                            tabindex="0"
                            role="button"
                            data-project-modal="#projectModal-<?= $idProyecto ?>"
                            aria-label="Abrir detalles de <?= $principalEscape(
                                                                $titulo
                                                            ) ?>">

                            <!-- Multimedia -->
                            <div class="project-media">

                                <?php if ($portadaUrl !== ''): ?>

                                    <img
                                        src="<?= $principalEscape(
                                                    $portadaUrl
                                                ) ?>"
                                        alt="<?= $principalEscape(
                                                    $portadaAlt
                                                ) ?>"
                                        loading="lazy"
                                        decoding="async">

                                <?php else: ?>

                                    <div
                                        class="project-media-placeholder"
                                        aria-hidden="true">

                                        <i class="fa-solid fa-code"></i>

                                    </div>

                                <?php endif; ?>

                                <!-- Categoría principal -->
                                <?php if (
                                    $categoriaPrincipalNombre !== ''
                                ): ?>

                                    <span class="project-category">

                                        <?= $principalEscape(
                                            $categoriaPrincipalNombre
                                        ) ?>

                                    </span>

                                <?php endif; ?>

                                <!-- Repositorio GitHub -->
                                <?php if ($github !== null): ?>

                                    <?php if ($githubEsPrivado): ?>

                                        <button
                                            class="project-github is-private"
                                            type="button"
                                            data-project-action
                                            data-private-target="#privateRepoMessage-<?= $idProyecto ?>"
                                            aria-label="Repositorio privado de <?= $principalEscape(
                                                                                    $titulo
                                                                                ) ?>"
                                            title="Repositorio privado">

                                            <i
                                                class="fa-brands fa-github"
                                                aria-hidden="true">
                                            </i>

                                            <span class="visually-hidden">
                                                Repositorio privado
                                            </span>

                                        </button>

                                    <?php elseif ($githubUrl !== ''): ?>

                                        <a
                                            class="project-github"
                                            href="<?= $principalEscape(
                                                        $githubUrl
                                                    ) ?>"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            data-project-action
                                            aria-label="Abrir GitHub de <?= $principalEscape(
                                                                            $titulo
                                                                        ) ?>"
                                            title="Abrir repositorio de GitHub">

                                            <i
                                                class="fa-brands fa-github"
                                                aria-hidden="true">
                                            </i>

                                            <span class="visually-hidden">
                                                Abrir repositorio
                                            </span>

                                        </a>

                                    <?php endif; ?>

                                <?php endif; ?>

                            </div>

                            <!-- Contenido -->
                            <div class="project-body">

                                <h3 class="project-title">

                                    <?= $principalEscape(
                                        $titulo
                                    ) ?>

                                </h3>

                                <?php if ($resumen !== ''): ?>

                                    <p class="project-description">

                                        <?= $principalEscape(
                                            $resumen
                                        ) ?>

                                    </p>

                                <?php endif; ?>

                                <!-- Tecnologías -->
                                <?php if (
                                    !empty($tecnologiasProyecto)
                                ): ?>

                                    <div class="project-tech-list">

                                        <?php foreach (
                                            $tecnologiasProyecto
                                            as $tecnologia
                                        ): ?>

                                            <?php

                                            if (!is_array($tecnologia)) {
                                                continue;
                                            }

                                            $tecnologiaNombre = trim(
                                                (string) (
                                                    $tecnologia['nombre']
                                                    ?? ''
                                                )
                                            );

                                            if ($tecnologiaNombre === '') {
                                                continue;
                                            }

                                            $tecnologiaColor =
                                                $normalizeProjectColor(
                                                    $tecnologia['color_hexadecimal'] ?? null
                                                );

                                            $tipoIcono = trim((string) (
                                                $tecnologia['tipo_icono']
                                                ?? ''
                                            ));

                                            $valorIcono = trim((string) (
                                                $tecnologia['valor_icono']
                                                ?? ''
                                            ));

                                            ?>

                                            <span
                                                class="project-tech"
                                                style="--tech-color: <?= $principalEscape(
                                                                            $tecnologiaColor
                                                                        ) ?>"
                                                title="<?= $principalEscape(
                                                            $tecnologiaNombre
                                                        ) ?>">

                                                <?php if (
                                                    $tipoIcono === 'fontawesome'
                                                    && $valorIcono !== ''
                                                ): ?>

                                                    <i
                                                        class="<?= $principalEscape(
                                                                    $valorIcono
                                                                ) ?>"
                                                        aria-hidden="true">
                                                    </i>

                                                <?php endif; ?>

                                                <span>

                                                    <?= $principalEscape(
                                                        $tecnologiaNombre
                                                    ) ?>

                                                </span>

                                            </span>

                                        <?php endforeach; ?>

                                    </div>

                                <?php endif; ?>

                                <!-- Mensaje de repositorio privado -->
                                <?php if ($githubEsPrivado): ?>

                                    <div
                                        class="private-repo-message"
                                        id="privateRepoMessage-<?= $idProyecto ?>"
                                        role="status">

                                        <i
                                            class="fa-solid fa-lock me-1"
                                            aria-hidden="true">
                                        </i>

                                        Este repositorio es privado.
                                        Puedes consultar la galería y los
                                        detalles técnicos del proyecto.

                                    </div>

                                <?php endif; ?>

                            </div>

                        </article>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </div>

</section>