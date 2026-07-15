<?php

/**
 * Controlador de autenticación del panel administrativo.
 *
 * Rutas previstas:
 * - GET  /login
 * - POST /login/validar
 * - POST /login/salir
 *
 * El método validar() funciona tanto con un formulario HTML convencional
 * como con una solicitud AJAX que espere JSON.
 */
class Login extends Controller
{
    private const MAXIMO_INTENTOS = 5;
    private const MINUTOS_BLOQUEO = 15;
    private const DIAS_SESION_PERSISTENTE = 30;

    private const COOKIE_RECORDAR = 'CYBERPUNK_RECORDAR';
    private const RUTA_PANEL = 'admin';

    public function __construct()
    {
        SessionManager::start();
        parent::__construct();
    }

    /**
     * Muestra la vista del login.
     */
    public function index(): void
    {
        $this->aplicarCabecerasPrivadas();

        if (!$this->usuarioAutenticado()) {
            $this->intentarRestaurarSesionPersistente();
        }

        if ($this->usuarioAutenticado()) {
            $this->redirigirAlPanel();
        }

        $data = [
            'title' => 'Acceso administrativo',
            'csrf_token' => $this->obtenerTokenCsrfLogin(),
            'mensaje_error' => $_SESSION['login_error'] ?? null,
            'identificador' => $_SESSION['login_identificador'] ?? '',
        ];

        unset(
            $_SESSION['login_error'],
            $_SESSION['login_identificador']
        );

        $this->views->getView('Admin/Login', 'index', $data);
    }

    /**
     * Valida las credenciales enviadas por el formulario.
     */
    public function validar(): void
    {
        $this->aplicarCabecerasPrivadas();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderError(
                'Método de solicitud no permitido.',
                405
            );
        }

        if ($this->usuarioAutenticado()) {
            $this->responderExito(
                'La sesión ya está iniciada.'
            );
        }

        $datos = $this->obtenerDatosSolicitud();

        $identificador = trim((string) ($datos['identificador'] ?? ''));
        $contrasena = (string) ($datos['contrasena'] ?? '');
        $tokenCsrf = (string) ($datos['csrf_token'] ?? '');
        $recordarSesion = $this->valorBooleano(
            $datos['recordar_sesion'] ?? false
        );

        if (!$this->tokenCsrfValido($tokenCsrf)) {
            $this->renovarTokenCsrfLogin();

            $this->responderError(
                'La solicitud expiró. Recarga la página e inténtalo nuevamente.',
                419,
                $identificador
            );
        }

        if ($identificador === '' || $contrasena === '') {
            $this->responderError(
                'Escribe tu usuario o correo y tu contraseña.',
                422,
                $identificador
            );
        }

        $longitudIdentificador = function_exists('mb_strlen')
            ? mb_strlen($identificador, 'UTF-8')
            : strlen($identificador);

        if (
            $longitudIdentificador > 150
            || strlen($contrasena) > 255
        ) {
            $this->responderError(
                'Los datos proporcionados no son válidos.',
                422
            );
        }

        $direccionIp = $this->obtenerDireccionIp();
        $agenteUsuario = $this->obtenerAgenteUsuario();

        $usuario = $this->model->obtenerUsuarioPorIdentificador(
            $identificador
        );

        if (!$usuario) {
            $this->model->registrarAuditoria(
                null,
                'login_fallido',
                'usuarios',
                null,
                null,
                ['motivo' => 'credenciales_invalidas'],
                $direccionIp,
                $agenteUsuario
            );

            $this->responderError(
                'Usuario o contraseña incorrectos.',
                401,
                $identificador
            );
        }

        $idUsuario = (int) $usuario['id_usuario'];

        /*
         * Si el bloqueo fue temporal y ya venció, se libera y se vuelve
         * a consultar al usuario antes de continuar.
         */
        if (
            $usuario['estado'] === 'bloqueado'
            && !empty($usuario['bloqueado_hasta'])
            && $this->fechaYaVencio($usuario['bloqueado_hasta'])
        ) {
            $this->model->liberarBloqueoExpirado($idUsuario);

            $usuario = $this->model->obtenerUsuarioPorIdentificador(
                $identificador
            );

            if (!$usuario) {
                $this->responderError(
                    'No fue posible validar la cuenta.',
                    500
                );
            }
        }

        if ($usuario['estado'] === 'inactivo') {
            $this->registrarAuditoriaEstadoCuenta(
                $usuario,
                'cuenta_inactiva',
                $direccionIp,
                $agenteUsuario
            );

            $this->responderError(
                'La cuenta no se encuentra disponible.',
                403
            );
        }

        if ($usuario['estado'] === 'bloqueado') {
            $this->registrarAuditoriaEstadoCuenta(
                $usuario,
                'cuenta_bloqueada',
                $direccionIp,
                $agenteUsuario
            );

            if (!empty($usuario['bloqueado_hasta'])) {
                $minutos = $this->minutosRestantes(
                    $usuario['bloqueado_hasta']
                );

                $this->responderError(
                    'La cuenta está temporalmente bloqueada. '
                        . 'Intenta nuevamente en aproximadamente '
                        . $minutos
                        . ($minutos === 1 ? ' minuto.' : ' minutos.'),
                    423
                );
            }

            $this->responderError(
                'La cuenta está bloqueada. Contacta al administrador.',
                423
            );
        }

        if ($usuario['estado'] !== 'activo') {
            $this->responderError(
                'La cuenta no se encuentra disponible.',
                403
            );
        }

        $contrasenaCorrecta = password_verify(
            $contrasena,
            $usuario['contrasena_hash']
        );

        if (!$contrasenaCorrecta) {
            $this->model->registrarIntentoFallido(
                $idUsuario,
                self::MAXIMO_INTENTOS,
                self::MINUTOS_BLOQUEO
            );

            $usuarioActualizado = $this->model->obtenerUsuarioPorId(
                $idUsuario
            );

            $this->model->registrarAuditoria(
                $idUsuario,
                'login_fallido',
                'usuarios',
                $idUsuario,
                null,
                [
                    'motivo' => 'contrasena_incorrecta',
                    'intentos_fallidos' => $usuarioActualizado
                        ? (int) $usuarioActualizado['intentos_fallidos']
                        : null,
                ],
                $direccionIp,
                $agenteUsuario
            );

            if (
                $usuarioActualizado
                && $usuarioActualizado['estado'] === 'bloqueado'
            ) {
                $this->responderError(
                    'Se alcanzó el límite de intentos. '
                        . 'La cuenta fue bloqueada temporalmente durante '
                        . self::MINUTOS_BLOQUEO
                        . ' minutos.',
                    423
                );
            }

            $this->responderError(
                'Usuario o contraseña incorrectos.',
                401,
                $identificador
            );
        }

        /*
         * Si PHP recomienda un algoritmo o costo más reciente,
         * se actualiza el hash sin interrumpir el acceso.
         */
        if (
            password_needs_rehash(
                $usuario['contrasena_hash'],
                PASSWORD_DEFAULT
            )
        ) {
            $nuevoHash = password_hash(
                $contrasena,
                PASSWORD_DEFAULT
            );

            if ($nuevoHash !== false) {
                $this->model->actualizarHashContrasena(
                    $idUsuario,
                    $nuevoHash
                );
            }
        }

        $this->model->registrarAccesoExitoso($idUsuario);

        session_regenerate_id(true);

        $this->crearSesionPhp($usuario);

        /*
         * El token persistente es opcional. El navegador recibe el token
         * original y MySQL almacena únicamente su SHA-256.
         */
        if ($recordarSesion) {
            $this->crearSesionPersistente(
                $idUsuario,
                $direccionIp,
                $agenteUsuario
            );
        } else {
            $this->eliminarSesionPersistenteActual();
        }

        $this->model->registrarAuditoria(
            $idUsuario,
            'login_exitoso',
            'usuarios',
            $idUsuario,
            null,
            [
                'nombre_usuario' => $usuario['nombre_usuario'],
                'rol' => $usuario['nombre_rol'],
                'sesion_persistente' => $recordarSesion,
            ],
            $direccionIp,
            $agenteUsuario
        );

        $this->renovarTokenCsrfLogin();

        /*
         * Limpieza ocasional. No afecta si no existen sesiones antiguas.
         */
        try {
            if (random_int(1, 20) === 1) {
                $this->model->eliminarSesionesInactivas(30);
            }
        } catch (Throwable $e) {
            error_log(
                'No fue posible ejecutar la limpieza de sesiones: '
                    . $e->getMessage()
            );
        }

        $this->responderExito(
            'Inicio de sesión correcto.'
        );
    }

    /**
     * Cierra la sesión actual.
     *
     * Esta acción debe invocarse mediante POST y enviar
     * csrf_token_sesion.
     */
    public function salir(): void
    {
        $this->aplicarCabecerasPrivadas();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderError(
                'Método de solicitud no permitido.',
                405
            );
        }

        $datos = $this->obtenerDatosSolicitud();
        $tokenCsrf = (string) (
            $datos['csrf_token_sesion']
            ?? $datos['csrf_token']
            ?? ''
        );

        $tokenSesion = $_SESSION['csrf_token_sesion'] ?? '';

        if (
            $tokenSesion === ''
            || $tokenCsrf === ''
            || !hash_equals($tokenSesion, $tokenCsrf)
        ) {
            $this->responderError(
                'No fue posible validar la solicitud de cierre.',
                419
            );
        }

        $idUsuario = isset($_SESSION['usuario']['id_usuario'])
            ? (int) $_SESSION['usuario']['id_usuario']
            : null;

        $direccionIp = $this->obtenerDireccionIp();
        $agenteUsuario = $this->obtenerAgenteUsuario();

        $this->eliminarSesionPersistenteActual();

        if ($idUsuario !== null) {
            $this->model->registrarAuditoria(
                $idUsuario,
                'logout',
                'usuarios',
                $idUsuario,
                null,
                null,
                $direccionIp,
                $agenteUsuario
            );
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $parametros = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 3600,
                    'path' => $parametros['path'],
                    'domain' => $parametros['domain'],
                    'secure' => (bool) $parametros['secure'],
                    'httponly' => (bool) $parametros['httponly'],
                    'samesite' => 'Lax',
                ]
            );
        }

        session_destroy();

        if ($this->esSolicitudAsincrona()) {
            $this->enviarJson(
                [
                    'ok' => true,
                    'mensaje' => 'Sesión cerrada correctamente.',
                    'redirect' => BASE_URL . 'login',
                ]
            );
        }

        header('Location: ' . BASE_URL . 'login');
        exit;
    }


    /**
     * Crea los datos mínimos de la sesión PHP.
     */
    private function crearSesionPhp(array $usuario): void
    {
        $_SESSION['autenticado'] = true;

        $_SESSION['usuario'] = [
            'id_usuario' => (int) $usuario['id_usuario'],
            'id_rol' => (int) $usuario['id_rol'],
            'nombre_rol' => $usuario['nombre_rol'],
            'nombre_usuario' => $usuario['nombre_usuario'],
            'correo' => $usuario['correo'],
            'nombre_mostrar' => $usuario['nombre_mostrar'],
            'ruta_avatar' => $usuario['ruta_avatar'],
        ];

        $_SESSION['inicio_sesion'] = time();
        $_SESSION['ultima_actividad'] = time();
        $_SESSION['csrf_token_sesion'] = bin2hex(
            random_bytes(32)
        );
    }

    /**
     * Crea la sesión recordada y su cookie.
     */
    private function crearSesionPersistente(
        int $idUsuario,
        ?string $direccionIp,
        ?string $agenteUsuario
    ): void {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $expiraEn = (new DateTimeImmutable())
            ->modify('+' . self::DIAS_SESION_PERSISTENTE . ' days');

        $idSesion = $this->model->crearSesion(
            $idUsuario,
            $tokenHash,
            $direccionIp,
            $agenteUsuario,
            $expiraEn
        );

        if ($idSesion <= 0) {
            error_log(
                'No fue posible crear la sesión persistente del usuario '
                    . $idUsuario
            );
            return;
        }

        setcookie(
            self::COOKIE_RECORDAR,
            $token,
            [
                'expires' => $expiraEn->getTimestamp(),
                'path' => $this->obtenerRutaCookie(),
                'domain' => '',
                'secure' => $this->solicitudEsHttps(),
                'httponly' => true,
                'samesite' => 'Strict',
            ]
        );
    }

    /**
     * Restaura la sesión PHP cuando existe una cookie válida.
     */
    private function intentarRestaurarSesionPersistente(): void
    {
        $token = $_COOKIE[self::COOKIE_RECORDAR] ?? '';

        if (
            !is_string($token)
            || preg_match('/^[a-f0-9]{64}$/', $token) !== 1
        ) {
            return;
        }

        $tokenHash = hash('sha256', $token);
        $sesion = $this->model->obtenerSesionActiva($tokenHash);

        if (!$sesion) {
            $this->eliminarCookieRecordar();
            return;
        }

        session_regenerate_id(true);

        $this->crearSesionPhp([
            'id_usuario' => $sesion['id_usuario'],
            'id_rol' => $sesion['id_rol'],
            'nombre_rol' => $sesion['nombre_rol'],
            'nombre_usuario' => $sesion['nombre_usuario'],
            'correo' => $sesion['correo'],
            'nombre_mostrar' => $sesion['nombre_mostrar'],
            'ruta_avatar' => $sesion['ruta_avatar'],
        ]);

        $_SESSION['id_sesion_persistente'] = (int) $sesion['id_sesion'];

        $this->model->actualizarActividadSesion(
            (int) $sesion['id_sesion']
        );

        $this->model->registrarAuditoria(
            (int) $sesion['id_usuario'],
            'sesion_restaurada',
            'sesiones_usuario',
            (int) $sesion['id_sesion'],
            null,
            null,
            $this->obtenerDireccionIp(),
            $this->obtenerAgenteUsuario()
        );
    }

    /**
     * Revoca la sesión recordada y elimina la cookie.
     */
    private function eliminarSesionPersistenteActual(): void
    {
        $token = $_COOKIE[self::COOKIE_RECORDAR] ?? '';

        if (
            is_string($token)
            && preg_match('/^[a-f0-9]{64}$/', $token) === 1
        ) {
            $this->model->revocarSesion(
                hash('sha256', $token)
            );
        }

        $this->eliminarCookieRecordar();
    }

    private function eliminarCookieRecordar(): void
    {
        setcookie(
            self::COOKIE_RECORDAR,
            '',
            [
                'expires' => time() - 3600,
                'path' => $this->obtenerRutaCookie(),
                'domain' => '',
                'secure' => $this->solicitudEsHttps(),
                'httponly' => true,
                'samesite' => 'Strict',
            ]
        );

        unset($_COOKIE[self::COOKIE_RECORDAR]);
    }

    /**
     * Registra una denegación relacionada con el estado de la cuenta.
     */
    private function registrarAuditoriaEstadoCuenta(
        array $usuario,
        string $motivo,
        ?string $direccionIp,
        ?string $agenteUsuario
    ): void {
        $this->model->registrarAuditoria(
            (int) $usuario['id_usuario'],
            'login_denegado',
            'usuarios',
            (int) $usuario['id_usuario'],
            null,
            ['motivo' => $motivo],
            $direccionIp,
            $agenteUsuario
        );
    }

    /**
     * Devuelve o crea el token CSRF exclusivo del login.
     */
    private function obtenerTokenCsrfLogin(): string
    {
        if (
            empty($_SESSION['csrf_token_login'])
            || !is_string($_SESSION['csrf_token_login'])
        ) {
            $_SESSION['csrf_token_login'] = bin2hex(
                random_bytes(32)
            );
        }

        return $_SESSION['csrf_token_login'];
    }

    private function renovarTokenCsrfLogin(): void
    {
        $_SESSION['csrf_token_login'] = bin2hex(
            random_bytes(32)
        );
    }

    private function tokenCsrfValido(string $token): bool
    {
        $tokenSesion = $_SESSION['csrf_token_login'] ?? '';

        return is_string($tokenSesion)
            && $tokenSesion !== ''
            && $token !== ''
            && hash_equals($tokenSesion, $token);
    }

    /**
     * Obtiene datos desde POST o desde un cuerpo JSON.
     */
    private function obtenerDatosSolicitud(): array
    {
        $tipoContenido = strtolower(
            (string) ($_SERVER['CONTENT_TYPE'] ?? '')
        );

        if (str_contains($tipoContenido, 'application/json')) {
            $contenido = file_get_contents('php://input');
            $datos = json_decode((string) $contenido, true);

            return is_array($datos) ? $datos : [];
        }

        return $_POST;
    }

    /**
     * Respuesta exitosa para AJAX o formulario tradicional.
     */
    private function responderExito(string $mensaje): void
    {
        $redirect = BASE_URL . self::RUTA_PANEL;

        if ($this->esSolicitudAsincrona()) {
            $this->enviarJson(
                [
                    'ok' => true,
                    'mensaje' => $mensaje,
                    'redirect' => $redirect,
                ],
                200
            );
        }

        header('Location: ' . $redirect);
        exit;
    }

    /**
     * Respuesta de error para AJAX o formulario tradicional.
     */
    private function responderError(
        string $mensaje,
        int $codigoHttp = 400,
        string $identificador = ''
    ): void {
        if ($this->esSolicitudAsincrona()) {
            $this->enviarJson(
                [
                    'ok' => false,
                    'mensaje' => $mensaje,
                    'csrf_token' => $this->obtenerTokenCsrfLogin(),
                ],
                $codigoHttp
            );
        }

        $_SESSION['login_error'] = $mensaje;
        $_SESSION['login_identificador'] = $identificador;

        header('Location: ' . BASE_URL . 'login');
        exit;
    }

    private function enviarJson(
        array $respuesta,
        int $codigoHttp = 200
    ): void {
        http_response_code($codigoHttp);
        header(
            'Content-Type: application/json; charset=utf-8'
        );

        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
                | JSON_INVALID_UTF8_SUBSTITUTE
        );

        exit;
    }

    private function esSolicitudAsincrona(): bool
    {
        $requestedWith = strtolower(
            (string) (
                $_SERVER['HTTP_X_REQUESTED_WITH']
                ?? ''
            )
        );

        $accept = strtolower(
            (string) ($_SERVER['HTTP_ACCEPT'] ?? '')
        );

        $tipoContenido = strtolower(
            (string) ($_SERVER['CONTENT_TYPE'] ?? '')
        );

        return $requestedWith === 'xmlhttprequest'
            || str_contains($accept, 'application/json')
            || str_contains($tipoContenido, 'application/json');
    }

    private function usuarioAutenticado(): bool
    {
        return !empty($_SESSION['autenticado'])
            && !empty($_SESSION['usuario']['id_usuario']);
    }

    private function redirigirAlPanel(): void
    {
        header(
            'Location: '
                . BASE_URL
                . self::RUTA_PANEL
        );
        exit;
    }

    private function valorBooleano($valor): bool
    {
        if (is_bool($valor)) {
            return $valor;
        }

        return in_array(
            strtolower(trim((string) $valor)),
            ['1', 'true', 'on', 'si', 'sí'],
            true
        );
    }

    private function fechaYaVencio(string $fecha): bool
    {
        try {
            return new DateTimeImmutable($fecha)
                <= new DateTimeImmutable();
        } catch (Throwable $e) {
            error_log(
                'Fecha de bloqueo inválida: '
                    . $e->getMessage()
            );

            return false;
        }
    }

    private function minutosRestantes(string $fecha): int
    {
        try {
            $bloqueadoHasta = new DateTimeImmutable($fecha);
            $ahora = new DateTimeImmutable();

            $segundos = $bloqueadoHasta->getTimestamp()
                - $ahora->getTimestamp();

            return max(1, (int) ceil($segundos / 60));
        } catch (Throwable $e) {
            return self::MINUTOS_BLOQUEO;
        }
    }

    private function obtenerDireccionIp(): ?string
    {
        $direccion = $_SERVER['REMOTE_ADDR'] ?? null;

        if (
            !is_string($direccion)
            || filter_var($direccion, FILTER_VALIDATE_IP) === false
        ) {
            return null;
        }

        return substr($direccion, 0, 45);
    }

    private function obtenerAgenteUsuario(): ?string
    {
        $agente = trim(
            (string) ($_SERVER['HTTP_USER_AGENT'] ?? '')
        );

        if ($agente === '') {
            return null;
        }

        if (function_exists('mb_substr')) {
            return mb_substr(
                $agente,
                0,
                500,
                'UTF-8'
            );
        }

        return substr($agente, 0, 500);
    }

    private function obtenerRutaCookie(): string
    {
        $ruta = parse_url(BASE_URL, PHP_URL_PATH);

        if (!is_string($ruta) || $ruta === '') {
            return '/';
        }

        $ruta = trim($ruta, '/');

        return $ruta === ''
            ? '/'
            : '/' . $ruta . '/';
    }

    private function solicitudEsHttps(): bool
    {
        return (
            !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off'
        )
            || (
                isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
                && strtolower(
                    $_SERVER['HTTP_X_FORWARDED_PROTO']
                ) === 'https'
            )
            || (
                isset($_SERVER['SERVER_PORT'])
                && (int) $_SERVER['SERVER_PORT'] === 443
            );
    }

    private function aplicarCabecerasPrivadas(): void
    {
        header(
            'Cache-Control: no-store, no-cache, must-revalidate, max-age=0'
        );
        header('Pragma: no-cache');
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: same-origin');
    }
}
