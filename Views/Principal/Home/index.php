<?php

$principalRoot = dirname(__DIR__);

require_once $principalRoot
    . '/Template/principal-header.php';

require_once $principalRoot
    . '/Sections/navbar.php';

?>

<main>

    <?php require_once $principalRoot . '/Sections/hero.php'; ?>

    <?php require_once $principalRoot . '/Sections/metrics.php'; ?>

    <?php require_once $principalRoot . '/Sections/technologies.php'; ?>

    <?php require_once $principalRoot . '/Sections/about.php'; ?>

    <?php require_once $principalRoot . '/Sections/projects.php'; ?>

    <?php require_once $principalRoot . '/Sections/services.php'; ?>

    <?php require_once $principalRoot . '/Sections/contact.php'; ?>

</main>

<?php

require_once $principalRoot
    . '/Sections/site-footer.php';

/*
 * Los componentes deben existir en el DOM
 * antes de cargar index.js.
 */
require_once $principalRoot
    . '/Components/project-modal.php';

require_once $principalRoot
    . '/Components/contact-toast.php';

/*
 * Este archivo carga los scripts.
 * Siempre debe ser el último.
 */
require_once $principalRoot
    . '/Template/principal-footer.php';
