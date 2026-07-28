<?php

$data = is_array($data ?? null)
    ? $data
    : [];

$principalEscape = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
};

$pageTitle = $data['title']
    ?? 'Cyberpunk | Portafolio de Desarrollo';

$pageDescription = $data['description']
    ?? 'Portafolio profesional de desarrollo e infraestructura.';

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title><?= $principalEscape($pageTitle) ?></title>

    <meta
        name="description"
        content="<?= $principalEscape($pageDescription) ?>">

    <meta
        name="theme-color"
        content="#070711">

    <!-- Bootstrap debe cargarse antes del CSS personalizado -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        crossorigin="anonymous">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        referrerpolicy="no-referrer">

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin>

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Orbitron:wght@500;600;700;800;900&display=swap">

    <!-- Siempre dentro de head -->
    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>Assets/css/Principal/style.css?v=1.0.1">
</head>

<body>

    <div class="cp-grid" aria-hidden="true"></div>
    <div class="cp-noise" aria-hidden="true"></div>