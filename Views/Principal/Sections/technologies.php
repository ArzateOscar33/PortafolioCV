<?php

$tecnologias = is_array($data['tecnologias'] ?? null)
    ? $data['tecnologias']
    : [];

/**
 * Resuelve rutas de imágenes guardadas en la base de datos.
 *
 * Acepta:
 * - URL absoluta.
 * - Ruta relativa al proyecto.
 */
$resolveTechnologyIconUrl = static function ($ruta): string {
    $ruta = trim((string) $ruta);

    if ($ruta === '') {
        return '';
    }

    if (preg_match('~^https?://~i', $ruta)) {
        return $ruta;
    }

    return rtrim(BASE_URL, '/')
        . '/'
        . ltrim($ruta, '/');
};

?>

<!-- Tecnologías -->
<section class="section" id="tecnologias">
    <div class="container">

        <div class="section-heading reveal-section">
            <span class="section-code">
                // stack_tecnológico
            </span>

            <h2 class="section-title">
                Tecnologías que <span>domino</span>
            </h2>

            <p class="section-copy">
                Herramientas utilizadas para desarrollar, desplegar y
                mantener productos digitales.
            </p>
        </div>

        <div
            class="row g-3"
            id="technologyGrid">

            <?php if (empty($tecnologias)): ?>

                <div class="col-12">
                    <div class="empty-state">
                        <i class="fa-solid fa-microchip fa-2x mb-3"></i>

                        <p class="mb-0">
                            No hay tecnologías públicas disponibles.
                        </p>
                    </div>
                </div>

            <?php else: ?>

                <?php foreach ($tecnologias as $tecnologia): ?>

                    <?php

                    $nombre = trim(
                        (string) ($tecnologia['nombre'] ?? '')
                    );

                    $slug = trim(
                        (string) ($tecnologia['slug'] ?? '')
                    );

                    $nivelArea = trim(
                        (string) ($tecnologia['nivel_o_area'] ?? '')
                    );

                    $tipoIcono = trim(
                        (string) ($tecnologia['tipo_icono'] ?? 'fontawesome')
                    );

                    $valorIcono = trim(
                        (string) ($tecnologia['valor_icono'] ?? '')
                    );

                    $color = strtoupper(trim(
                        (string) (
                            $tecnologia['color_hexadecimal']
                            ?? '#00F6FF'
                        )
                    ));

                    /*
                     * Aunque el administrador ya valida este campo,
                     * volvemos a validarlo antes de introducirlo en style.
                     */
                    if (!preg_match('/^#[0-9A-F]{6}$/', $color)) {
                        $color = '#00F6FF';
                    }

                    ?>

                    <div
                        class="col-12 col-sm-6 col-lg-4 col-xl-3 tech-item"
                        data-technology="<?= (int) (
                                                $tecnologia['id_tecnologia'] ?? 0
                                            ) ?>"
                        data-technology-slug="<?= $principalEscape($slug) ?>">

                        <article
                            class="tech-card"
                            style="--tech-color: <?= $principalEscape(
                                                        $color
                                                    ) ?>">

                            <div
                                class="tech-icon"
                                aria-hidden="true">

                                <?php if (
                                    $tipoIcono === 'imagen'
                                    && $valorIcono !== ''
                                ): ?>

                                    <img
                                        class="tech-icon-image"
                                        src="<?= $principalEscape(
                                                    $resolveTechnologyIconUrl(
                                                        $valorIcono
                                                    )
                                                ) ?>"
                                        alt=""
                                        loading="lazy"
                                        decoding="async">

                                <?php elseif ($valorIcono !== ''): ?>

                                    <i class="<?= $principalEscape(
                                                    $valorIcono
                                                ) ?>"></i>

                                <?php else: ?>

                                    <i class="fa-solid fa-code"></i>

                                <?php endif; ?>

                            </div>

                            <div>
                                <h3 class="tech-name">
                                    <?= $principalEscape($nombre) ?>
                                </h3>

                                <?php if ($nivelArea !== ''): ?>

                                    <span class="tech-level">
                                        <?= $principalEscape($nivelArea) ?>
                                    </span>

                                <?php endif; ?>
                            </div>

                        </article>
                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </div>
</section>