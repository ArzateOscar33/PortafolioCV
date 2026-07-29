<?php

$proyectos = is_array($data['proyectos'] ?? null)
    ? $data['proyectos']
    : [];

/**
 * Resuelve la URL de una imagen o video.
 *
 * Admite:
 * - URL externa HTTP/HTTPS.
 * - Ruta local relativa a BASE_URL.
 */
$resolveModalMediaUrl = static function ($archivo): string {
    if (!is_array($archivo)) {
        return '';
    }

    $urlExterna = trim((string) (
        $archivo['url_externa'] ?? ''
    ));

    if ($urlExterna !== '') {
        return preg_match('~^https?://~i', $urlExterna)
            ? $urlExterna
            : '';
    }

    $rutaArchivo = trim((string) (
        $archivo['ruta_archivo'] ?? ''
    ));

    if ($rutaArchivo === '') {
        return '';
    }

    if (preg_match('~^https?://~i', $rutaArchivo)) {
        return $rutaArchivo;
    }

    return rtrim(BASE_URL, '/')
        . '/'
        . ltrim($rutaArchivo, '/');
};

/**
 * Localiza el primer enlace coincidente.
 */
$findModalLink = static function (
    array $enlaces,
    array $tipos
): ?array {
    foreach ($enlaces as $enlace) {
        if (!is_array($enlace)) {
            continue;
        }

        $tipoSlug = trim((string) (
            $enlace['tipo_slug'] ?? ''
        ));

        if (in_array($tipoSlug, $tipos, true)) {
            return $enlace;
        }
    }

    return null;
};

/**
 * Valida un color hexadecimal antes de utilizarlo.
 */
$normalizeModalColor = static function (
    $color,
    string $fallback = '#00F6FF'
): string {
    $color = strtoupper(trim((string) $color));

    return preg_match('/^#[0-9A-F]{6}$/', $color)
        ? $color
        : $fallback;
};

?>

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

    if ($idProyecto <= 0 || $titulo === '') {
        continue;
    }

    $descripcion = trim((string) (
        $proyecto['descripcion'] ?? ''
    ));

    $retoTecnico = trim((string) (
        $proyecto['reto_tecnico'] ?? ''
    ));

    $resultado = trim((string) (
        $proyecto['resultado'] ?? ''
    ));

    $clienteNombre = trim((string) (
        $proyecto['cliente_nombre'] ?? ''
    ));

    $categorias = is_array(
        $proyecto['categorias'] ?? null
    )
        ? $proyecto['categorias']
        : [];

    $tecnologias = is_array(
        $proyecto['tecnologias'] ?? null
    )
        ? $proyecto['tecnologias']
        : [];

    $imagenes = is_array(
        $proyecto['imagenes'] ?? null
    )
        ? $proyecto['imagenes']
        : [];

    $videos = is_array(
        $proyecto['videos'] ?? null
    )
        ? $proyecto['videos']
        : [];

    $enlaces = is_array(
        $proyecto['enlaces'] ?? null
    )
        ? $proyecto['enlaces']
        : [];

    /*
     * Enlace de GitHub.
     */
    $github = $findModalLink(
        $enlaces,
        ['github']
    );

    $githubEsPrivado = $github !== null
        && (int) ($github['es_privado'] ?? 0) === 1;

    $githubUrl = $github !== null
        ? trim((string) ($github['url'] ?? ''))
        : '';

    /*
     * Aunque HomeModel ya debe ocultar esta URL,
     * la anulamos nuevamente en la vista pública.
     */
    if ($githubEsPrivado) {
        $githubUrl = '';
    }

    /*
     * Enlace público a demo, sitio o aplicación.
     */
    $sitio = $findModalLink(
        $enlaces,
        [
            'demo',
            'live',
            'sitio',
            'website',
            'web',
            'produccion',
        ]
    );

    $sitioEsPrivado = $sitio !== null
        && (int) ($sitio['es_privado'] ?? 0) === 1;

    $sitioUrl = $sitio !== null
        ? trim((string) ($sitio['url'] ?? ''))
        : '';

    if ($sitioEsPrivado) {
        $sitioUrl = '';
    }

    /*
     * Nombres de categorías.
     */
    $nombresCategorias = [];

    foreach ($categorias as $categoria) {
        if (!is_array($categoria)) {
            continue;
        }

        $nombreCategoria = trim((string) (
            $categoria['nombre'] ?? ''
        ));

        if ($nombreCategoria !== '') {
            $nombresCategorias[] = $nombreCategoria;
        }
    }

    $nombresCategorias = array_values(
        array_unique($nombresCategorias)
    );

    /*
     * Preparar las imágenes válidas y evitar duplicados.
     */
    $imagenesValidas = [];
    $urlsRegistradas = [];

    $portada = is_array(
        $proyecto['portada'] ?? null
    )
        ? $proyecto['portada']
        : null;

    if ($portada !== null) {
        $portadaUrl = $resolveModalMediaUrl($portada);

        if ($portadaUrl !== '') {
            $imagenesValidas[] = [
                'url' => $portadaUrl,
                'alt' => trim((string) (
                    $portada['texto_alternativo']
                    ?? "Galería de {$titulo}"
                )),
            ];

            $urlsRegistradas[$portadaUrl] = true;
        }
    }

    foreach ($imagenes as $imagen) {
        if (!is_array($imagen)) {
            continue;
        }

        $imagenUrl = $resolveModalMediaUrl($imagen);

        if (
            $imagenUrl === ''
            || isset($urlsRegistradas[$imagenUrl])
        ) {
            continue;
        }

        $imagenAlt = trim((string) (
            $imagen['texto_alternativo']
            ?? ''
        ));

        if ($imagenAlt === '') {
            $imagenAlt = "Imagen de {$titulo}";
        }

        $imagenesValidas[] = [
            'url' => $imagenUrl,
            'alt' => $imagenAlt,
        ];

        $urlsRegistradas[$imagenUrl] = true;
    }

    $imagenPrincipal = $imagenesValidas[0] ?? null;

    /*
     * Preparar los videos válidos una sola vez para el visor multimedia.
     * Los archivos locales se reproducen dentro del modal y los enlaces
     * externos se muestran como accesos directos.
     */
    $videosValidos = [];

    foreach ($videos as $video) {
        if (!is_array($video)) {
            continue;
        }

        $videoUrl = $resolveModalMediaUrl($video);

        if ($videoUrl === '') {
            continue;
        }

        $rutaArchivoVideo = trim((string) (
            $video['ruta_archivo'] ?? ''
        ));

        $tipoMimeVideo = trim((string) (
            $video['tipo_mime'] ?? 'video/mp4'
        ));

        if ($tipoMimeVideo === '') {
            $tipoMimeVideo = 'video/mp4';
        }

        $videosValidos[] = [
            'url' => $videoUrl,
            'mime' => $tipoMimeVideo,
            'es_reproducible' => $rutaArchivoVideo !== '',
        ];
    }

    $indiceVideoInicial = null;

    if ($imagenPrincipal === null) {
        foreach ($videosValidos as $indiceVideo => $videoValido) {
            if ($videoValido['es_reproducible']) {
                $indiceVideoInicial = $indiceVideo;
                break;
            }
        }
    }

    $mostrarGaleria = count($imagenesValidas) > 1
        || !empty($videosValidos);

    $mostrarFooter =
        (!$githubEsPrivado && $githubUrl !== '')
        || (!$sitioEsPrivado && $sitioUrl !== '');

    ?>

    <div
        class="modal fade"
        id="projectModal-<?= $idProyecto ?>"
        tabindex="-1"
        aria-labelledby="projectModalTitle-<?= $idProyecto ?>"
        aria-hidden="true">

        <div
            class="modal-dialog project-modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <!-- Encabezado -->
                <div class="modal-header">

                    <div>

                        <?php if (!empty($nombresCategorias)): ?>

                            <span class="section-code mb-1">

                                // <?= $principalEscape(
                                        implode(' · ', $nombresCategorias)
                                    ) ?>

                            </span>

                        <?php endif; ?>

                        <h2
                            class="modal-title fs-4 modal-project-title"
                            id="projectModalTitle-<?= $idProyecto ?>">

                            <?= $principalEscape($titulo) ?>

                        </h2>

                    </div>

                    <button
                        class="btn-close"
                        type="button"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar">
                    </button>

                </div>

                <!-- Cuerpo -->
                <div class="modal-body project-modal-body">

                    <div class="project-modal-layout">

                        <!-- Multimedia -->
                        <section class="project-modal-media">

                            <!-- Visor principal compartido por imágenes y videos -->
                            <div
                                class="project-media-stage"
                                id="projectMediaViewer-<?= $idProyecto ?>">

                                <?php if ($imagenPrincipal !== null): ?>

                                    <img
                                        class="modal-main-image"
                                        src="<?= $principalEscape(
                                                    $imagenPrincipal['url']
                                                ) ?>"
                                        alt="<?= $principalEscape(
                                                    $imagenPrincipal['alt']
                                                ) ?>"
                                        decoding="async">

                                <?php elseif ($indiceVideoInicial !== null): ?>

                                    <?php

                                    $videoInicial = $videosValidos[$indiceVideoInicial];

                                    ?>

                                    <video
                                        class="modal-main-video"
                                        controls
                                        preload="metadata"
                                        playsinline>

                                        <source
                                            src="<?= $principalEscape(
                                                        $videoInicial['url']
                                                    ) ?>"
                                            type="<?= $principalEscape(
                                                        $videoInicial['mime']
                                                    ) ?>">

                                        Tu navegador no admite reproducción
                                        de video.

                                    </video>

                                <?php else: ?>

                                    <div
                                        class="project-media-placeholder"
                                        aria-label="Proyecto sin vista previa multimedia">

                                        <i
                                            class="fa-solid fa-photo-film"
                                            aria-hidden="true">
                                        </i>

                                    </div>

                                <?php endif; ?>

                            </div>

                            <!-- Miniaturas de imágenes y videos -->
                            <?php if ($mostrarGaleria): ?>

                                <div
                                    class="gallery-strip"
                                    aria-label="Galería multimedia de <?= $principalEscape(
                                                                            $titulo
                                                                        ) ?>">

                                    <?php foreach (
                                        $imagenesValidas
                                        as $indice => $imagen
                                    ): ?>

                                        <button
                                            class="gallery-thumb <?= $indice === 0
                                                                        && $imagenPrincipal !== null
                                                                        ? 'active'
                                                                        : '' ?>"
                                            type="button"
                                            data-project-media
                                            data-media-type="image"
                                            data-media-url="<?= $principalEscape(
                                                                $imagen['url']
                                                            ) ?>"
                                            data-media-alt="<?= $principalEscape(
                                                                $imagen['alt']
                                                            ) ?>"
                                            data-media-target="#projectMediaViewer-<?= $idProyecto ?>"
                                            aria-label="Mostrar imagen <?= $indice + 1 ?> de <?= $principalEscape(
                                                                                                    $titulo
                                                                                                ) ?>">

                                            <img
                                                src="<?= $principalEscape(
                                                            $imagen['url']
                                                        ) ?>"
                                                alt=""
                                                loading="lazy"
                                                decoding="async">

                                        </button>

                                    <?php endforeach; ?>

                                    <?php foreach (
                                        $videosValidos
                                        as $indiceVideo => $videoValido
                                    ): ?>

                                        <?php if (
                                            $videoValido['es_reproducible']
                                        ): ?>

                                            <button
                                                class="gallery-thumb gallery-thumb-video <?= $indiceVideoInicial === $indiceVideo
                                                                                                ? 'active'
                                                                                                : '' ?>"
                                                type="button"
                                                data-project-media
                                                data-media-type="video"
                                                data-media-url="<?= $principalEscape(
                                                                    $videoValido['url']
                                                                ) ?>"
                                                data-media-mime="<?= $principalEscape(
                                                                        $videoValido['mime']
                                                                    ) ?>"
                                                data-media-target="#projectMediaViewer-<?= $idProyecto ?>"
                                                aria-label="Reproducir video <?= $indiceVideo + 1 ?> de <?= $principalEscape(
                                                                                                            $titulo
                                                                                                        ) ?>">

                                                <span class="gallery-video-icon">

                                                    <i
                                                        class="fa-solid fa-circle-play"
                                                        aria-hidden="true">
                                                    </i>

                                                    <small>Video</small>

                                                </span>

                                            </button>

                                        <?php else: ?>

                                            <a
                                                class="gallery-thumb gallery-thumb-video"
                                                href="<?= $principalEscape(
                                                            $videoValido['url']
                                                        ) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                aria-label="Abrir video externo <?= $indiceVideo + 1 ?> de <?= $principalEscape(
                                                                                                                $titulo
                                                                                                            ) ?>">

                                                <span class="gallery-video-icon">

                                                    <i
                                                        class="fa-solid fa-arrow-up-right-from-square"
                                                        aria-hidden="true">
                                                    </i>

                                                    <small>Video</small>

                                                </span>

                                            </a>

                                        <?php endif; ?>

                                    <?php endforeach; ?>

                                </div>

                            <?php endif; ?>

                        </section>

                        <!-- Información -->
                        <aside class="project-modal-info">

                            <?php if ($descripcion !== ''): ?>

                                <div class="detail-block mb-3">

                                    <div class="detail-label">
                                        Descripción
                                    </div>

                                    <p class="detail-text">
                                        <?= nl2br(
                                            $principalEscape($descripcion)
                                        ) ?>
                                    </p>

                                </div>

                            <?php endif; ?>

                            <!-- Tecnologías -->
                            <?php if (!empty($tecnologias)): ?>

                                <div class="detail-block mb-3">

                                    <div class="detail-label">
                                        Tecnologías
                                    </div>

                                    <div class="modal-tech-list">

                                        <?php foreach (
                                            $tecnologias
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
                                                $normalizeModalColor(
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

                                </div>

                            <?php endif; ?>

                            <!-- Reto técnico -->
                            <?php if ($retoTecnico !== ''): ?>

                                <div class="detail-block mb-3">

                                    <div class="detail-label">
                                        Reto técnico
                                    </div>

                                    <p class="detail-text">
                                        <?= nl2br(
                                            $principalEscape($retoTecnico)
                                        ) ?>
                                    </p>

                                </div>

                            <?php endif; ?>

                            <!-- Resultado -->
                            <?php if ($resultado !== ''): ?>

                                <div class="detail-block mb-3">

                                    <div class="detail-label">
                                        Resultado
                                    </div>

                                    <p class="detail-text">
                                        <?= nl2br(
                                            $principalEscape($resultado)
                                        ) ?>
                                    </p>

                                </div>

                            <?php endif; ?>

                            <!-- Cliente -->
                            <?php if ($clienteNombre !== ''): ?>

                                <div class="detail-block mb-3">

                                    <div class="detail-label">
                                        Cliente / propietario
                                    </div>

                                    <p class="detail-text">

                                        <?= $principalEscape(
                                            $clienteNombre
                                        ) ?>

                                    </p>

                                </div>

                            <?php endif; ?>

                            <!-- Repositorio privado -->
                            <?php if ($githubEsPrivado): ?>

                                <div
                                    class="private-repo-message show"
                                    role="status">

                                    <i
                                        class="fa-solid fa-lock me-1"
                                        aria-hidden="true">
                                    </i>

                                    Este repositorio es privado. El código
                                    fuente no se encuentra disponible
                                    públicamente.

                                </div>

                            <?php endif; ?>

                        </aside>

                    </div>

                </div>

                <!-- Botones -->
                <?php if ($mostrarFooter): ?>

                    <div class="modal-footer">

                        <?php if (
                            !$githubEsPrivado
                            && $githubUrl !== ''
                        ): ?>

                            <a
                                class="cp-btn cp-btn-secondary"
                                href="<?= $principalEscape(
                                            $githubUrl
                                        ) ?>"
                                target="_blank"
                                rel="noopener noreferrer">

                                <i
                                    class="fa-brands fa-github"
                                    aria-hidden="true">
                                </i>

                                Ver repositorio

                            </a>

                        <?php endif; ?>

                        <?php if (
                            !$sitioEsPrivado
                            && $sitioUrl !== ''
                        ): ?>

                            <a
                                class="cp-btn"
                                href="<?= $principalEscape(
                                            $sitioUrl
                                        ) ?>"
                                target="_blank"
                                rel="noopener noreferrer">

                                <i
                                    class="fa-solid fa-arrow-up-right-from-square"
                                    aria-hidden="true">
                                </i>

                                Abrir proyecto

                            </a>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>

<?php endforeach; ?>