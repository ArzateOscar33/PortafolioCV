<?php

$configuracion = is_array($data['configuracion'] ?? null)
    ? $data['configuracion']
    : [];

$heroKicker = trim(
    (string) ($configuracion['hero_kicker'] ?? '')
);

$heroTituloPrincipal = trim(
    (string) ($configuracion['hero_titulo_principal'] ?? '')
);

$heroTituloDestacado = trim(
    (string) ($configuracion['hero_titulo_destacado'] ?? '')
);

$heroDescripcion = trim(
    (string) ($configuracion['hero_descripcion'] ?? '')
);

$heroCtaPrincipalTexto = trim(
    (string) ($configuracion['hero_cta_principal_texto'] ?? 'Ver proyectos')
);

$heroCtaPrincipalUrl = trim(
    (string) ($configuracion['hero_cta_principal_url'] ?? '#proyectos')
);

$heroCtaSecundarioTexto = trim(
    (string) ($configuracion['hero_cta_secundario_texto'] ?? 'Contactar')
);

$heroCtaSecundarioUrl = trim(
    (string) ($configuracion['hero_cta_secundario_url'] ?? '#contacto')
);

?>

<!-- Hero -->
<section class="hero" id="inicio">
    <div class="floating-node" aria-hidden="true"></div>
    <div class="floating-node" aria-hidden="true"></div>
    <div class="floating-node" aria-hidden="true"></div>

    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-8">
                <div class="hero-content">
                    <span class="hero-kicker">
                        <?= $principalEscape($heroKicker) ?>
                    </span>

                    <h1 class="hero-title">
                        <span
                            class="glitch"
                            data-text="<?= $principalEscape($heroTituloPrincipal) ?>">
                            <?= $principalEscape($heroTituloPrincipal) ?>
                        </span>

                        <br>

                        <span class="outline">
                            <?= $principalEscape($heroTituloDestacado) ?>
                        </span>
                    </h1>

                    <p class="hero-copy">
                        <?= nl2br($principalEscape($heroDescripcion)) ?>
                    </p>
                    <div class="hero-actions">
                        <a
                            class="cp-btn"
                            href="<?= $principalEscape($heroCtaPrincipalUrl) ?>">
                            <i class="fa-solid fa-layer-group"></i>
                            <?= $principalEscape($heroCtaPrincipalTexto) ?>
                        </a>

                        <a
                            class="cp-btn cp-btn-secondary"
                            href="<?= $principalEscape($heroCtaSecundarioUrl) ?>">
                            <i class="fa-solid fa-satellite-dish"></i>
                            <?= $principalEscape($heroCtaSecundarioTexto) ?>
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
                            <span class="terminal-prompt">root@arzateoscar:~$</span>
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
                            <span class="terminal-prompt">root@arzateoscar:~$</span>
                            <span class="cursor" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>