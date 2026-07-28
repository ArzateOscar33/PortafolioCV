<?php

/**
 * Scripts específicos del módulo.
 *
 * Ejemplo:
 * $data['scripts'] = [
 *     'Assets/js/Admin/categorias.js',
 * ];
 */

$additionalScripts = $data['scripts'] ?? [];

if (!is_array($additionalScripts)) {
    $additionalScripts = [];
}

if (!isset($adminEscape)) {
    $adminEscape = static function ($value): string {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES,
            'UTF-8'
        );
    };
}

?>

</div>
</main>
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>


<script src="<?= BASE_URL ?>Assets/js/Admin/admin.js"></script>

<?php if (($data['activeModule'] ?? '') === 'categorias'): ?>

    <script src="<?= BASE_URL ?>Assets/js/Admin/categorias.js"></script>

<?php endif; ?>
<?php if (($data['activeModule'] ?? '') === 'proyectos'): ?>

    <script src="<?= BASE_URL ?>Assets/js/Admin/proyectos.js"></script>

<?php endif; ?>
<?php if (($data['activeModule'] ?? '') === 'clientes'): ?>

    <script src="<?= BASE_URL ?>Assets/js/Admin/clientes.js"></script>

<?php endif; ?>
<?php if (($data['activeModule'] ?? '') === 'multimedia'): ?>
    <script src="<?= BASE_URL ?>Assets/js/Admin/multimedia.js"></script>
<?php endif; ?>
<?php if (($data['activeModule'] ?? '') === 'mensajes'): ?>
    <script src="<?= BASE_URL ?>Assets/js/Admin/mensajes.js"></script>
<?php endif; ?>
<?php if (($data['activeModule'] ?? '') === 'configuracion'): ?>
    <script src="<?= BASE_URL ?>Assets/js/Admin/configuracion.js"></script>
<?php endif; ?>

<?php foreach ($additionalScripts as $script): ?>

    <?php
    $script = ltrim(
        (string) $script,
        '/'
    );
    ?>

    <script src="<?= $adminEscape(
                        BASE_URL . $script
                    ) ?>"></script>

<?php endforeach; ?>

</body>

</html>