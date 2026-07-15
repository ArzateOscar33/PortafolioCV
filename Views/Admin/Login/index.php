<?php
$tituloPagina = $data['title'] ?? 'Acceso administrativo';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($tituloPagina, ENT_QUOTES, 'UTF-8'); ?>
        | <?= htmlspecialchars(TITLE, ENT_QUOTES, 'UTF-8'); ?>
    </title>

    <meta
        name="description"
        content="Acceso privado al panel administrativo de Cyberpunk Portfolio.">
    <meta name="theme-color" content="#070711">
    <meta name="robots" content="noindex, nofollow">

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

    <!-- Tipografías del portafolio -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Orbitron:wght@500;600;700;800;900&display=swap"
        rel="stylesheet">

    <link
        rel="stylesheet"
        href="<?= BASE_URL; ?>Assets/css/Login/style.css?v=1.0.0">
</head>

<body class="login-page">
    <div class="login-grid" aria-hidden="true"></div>
    <div class="login-noise" aria-hidden="true"></div>
    <div class="scan-line" aria-hidden="true"></div>

    <span class="floating-node floating-node-one" aria-hidden="true"></span>
    <span class="floating-node floating-node-two" aria-hidden="true"></span>
    <span class="floating-node floating-node-three" aria-hidden="true"></span>

    <main class="login-main">
        <div class="container">
            <div class="login-shell">

                <!-- Panel informativo -->
                <section class="login-visual-panel" aria-labelledby="loginVisualTitle">
                    <div>
                        <a class="cyber-brand" href="<?= BASE_URL; ?>" aria-label="Regresar al portafolio">
                            <span class="brand-mark" aria-hidden="true">
                                <i class="fa-solid fa-bolt"></i>
                            </span>

                            <span>

                                <strong>Cyberpunk</strong>
                                <small>Portfolio system</small>
                            </span>
                        </a>

                        <div class="system-badge">
                            <span class="system-dot" aria-hidden="true"></span>
                            Nodo administrativo disponible
                        </div>

                        <p class="visual-code">// acceso_restringido</p>

                        <h1 id="loginVisualTitle" class="visual-title">
                            Acceso al
                            <span>núcleo</span>
                        </h1>

                        <p class="visual-description">
                            Consola privada para administrar proyectos, tecnologías,
                            categorías, contenido multimedia, clientes y citas.


                        </p>
                    </div>

                    <div class="security-terminal" aria-label="Estado de seguridad del sistema">
                        <div class="terminal-header">
                            <div class="terminal-dots" aria-hidden="true">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>

                            <span class="terminal-name">security_gateway.sh</span>
                        </div>

                        <div class="terminal-body">
                            <p>
                                <span class="terminal-prompt">root@cyberpunk:~$</span>
                                verificar_sistema
                            </p>

                            <p>
                                <span class="terminal-ok">[OK]</span>
                                Base de datos:
                                <strong>portafolio</strong>
                            </p>

                            <p>
                                <span class="terminal-ok">[OK]</span>
                                Canal de autenticación preparado
                            </p>

                            <p>
                                <span class="terminal-ok">[OK]</span>
                                Control de sesiones disponible
                            </p>

                            <p>
                                <span class="terminal-warning">[AUTH]</span>
                                Esperando credenciales
                                <span class="terminal-cursor" aria-hidden="true"></span>
                            </p>
                        </div>
                    </div>

                    <div class="security-features">
                        <div>
                            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                            <span>Contraseñas protegidas mediante hash</span>
                        </div>

                        <div>
                            <i class="fa-solid fa-fingerprint" aria-hidden="true"></i>
                            <span>Validación de identidad y sesión</span>
                        </div>

                        <div>
                            <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>
                            <span>Registro de actividad administrativa</span>
                        </div>
                    </div>
                </section>

                <!-- Formulario de acceso -->
                <section class="login-form-panel" aria-labelledby="loginTitle">
                    <div class="login-form-content">
                        <div class="access-status">
                            <span>AUTH_GATEWAY</span>
                            <small>v1.0</small>
                        </div>

                        <div class="login-heading">
                            <span class="login-kicker">Identificación requerida</span>

                            <h2 id="loginTitle">Iniciar sesión</h2>

                            <p>
                                Ingresa tus credenciales para acceder al panel administrativo.
                            </p>
                        </div>

                        <?php if (!empty($data['mensaje_error'])): ?>
                            <div
                                id="loginMessage"
                                class="alert alert-danger"
                                role="alert"
                                aria-live="polite">

                                <i class="fa-solid fa-triangle-exclamation me-2"></i>

                                <?= htmlspecialchars(
                                    $data['mensaje_error'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </div>
                        <?php else: ?>
                            <div
                                id="loginMessage"
                                class="login-message d-none"
                                role="alert"
                                aria-live="polite">
                            </div>
                        <?php endif; ?>

                        <form
                            id="formLogin"
                            class="login-form"
                            action="<?= BASE_URL; ?>login/validar"
                            method="POST"
                            novalidate>

                            <?php if (!empty($data['csrf_token'])): ?>
                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars(
                                                $data['csrf_token'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ); ?>">
                            <?php endif; ?>

                            <div class="form-field">
                                <label for="identificador">
                                    Usuario o correo electrónico
                                    <?php
                                    // 1. La contraseña que el usuario ingresa en el formulario
                                    $passwordPlano = "Oscar1702";

                                    // 2. Crear el hash de la contraseña
                                    $passwordHash = password_hash($passwordPlano, PASSWORD_DEFAULT);

                                    echo "Hash generado: " . $passwordHash;
                                    ?>
                                </label>

                                <div class="cyber-input-group">
                                    <span class="input-icon" aria-hidden="true">
                                        <i class="fa-regular fa-user"></i>
                                    </span>

                                    <input
                                        id="identificador"
                                        name="identificador"
                                        type="text"
                                        class="form-control"
                                        placeholder="admin o correo@dominio.com"
                                        value="<?= htmlspecialchars(
                                                    $data['identificador'] ?? '',
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ); ?>"
                                        maxlength="150"
                                        autocomplete="username"
                                        spellcheck="false"
                                        required
                                        autofocus>

                                    <span class="input-corner" aria-hidden="true"></span>
                                </div>

                                <div class="invalid-feedback">
                                    Escribe tu usuario o correo electrónico.
                                </div>
                            </div>

                            <div class="form-field">
                                <div class="field-label-row">
                                    <label for="contrasena">Contraseña</label>

                                    <span class="security-label">
                                        <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                        Canal seguro
                                    </span>
                                </div>

                                <div class="cyber-input-group">
                                    <span class="input-icon" aria-hidden="true">
                                        <i class="fa-solid fa-key"></i>
                                    </span>

                                    <input
                                        id="contrasena"
                                        name="contrasena"
                                        type="password"
                                        class="form-control"
                                        placeholder="••••••••••••"
                                        minlength="8"
                                        maxlength="255"
                                        autocomplete="current-password"
                                        required>

                                    <button
                                        id="btnMostrarContrasena"
                                        class="password-toggle"
                                        type="button"
                                        aria-label="Mostrar contraseña"
                                        aria-pressed="false"
                                        disabled
                                        title="Se activará al agregar el JavaScript">
                                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                    </button>

                                    <span class="input-corner" aria-hidden="true"></span>
                                </div>

                                <div class="invalid-feedback">
                                    Escribe tu contraseña.
                                </div>
                            </div>

                            <div class="login-options">
                                <label class="cyber-check" for="recordarSesion">
                                    <input
                                        id="recordarSesion"
                                        name="recordar_sesion"
                                        type="checkbox"
                                        value="1">

                                    <span class="check-mark" aria-hidden="true"></span>
                                    <span>Mantener sesión iniciada</span>
                                </label>

                                <span class="attempt-status">
                                    <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                    Acceso exclusivo
                                </span>
                            </div>

                            <button
                                id="btnIniciarSesion"
                                class="login-submit"
                                type="submit">

                                <span class="button-text">
                                    <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i>
                                    Autenticar
                                </span>

                                <span class="button-loader d-none" aria-hidden="true">
                                    <span class="spinner-border spinner-border-sm"></span>
                                    Verificando...
                                </span>
                            </button>
                        </form>

                        <div class="login-divider">
                            <span>PORTFOLIO_ACCESS_PROTOCOL</span>
                        </div>

                        <a class="return-link" href="<?= BASE_URL; ?>">
                            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                            Regresar al portafolio
                        </a>

                        <p class="login-footer">
                            <span class="footer-pulse" aria-hidden="true"></span>
                            Sistema privado · Los accesos pueden ser auditados
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <!--
        En la siguiente etapa se agregará:
        Assets/js/Login/login.js
    -->
</body>

</html>