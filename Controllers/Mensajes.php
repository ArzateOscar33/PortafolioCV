<?php

class Mensajes extends Controller
{
    private const ESTADOS = [
        'nueva',
        'leida',
        'en_seguimiento',
        'respondida',
        'cerrada',
        'archivada',
        'spam',
    ];

    public function __construct()
    {
        SessionManager::start();
        parent::__construct();

        if (
            empty($_SESSION['autenticado'])
            || empty($_SESSION['usuario']['id_usuario'])
        ) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->json([
                    'status' => false,
                    'msg' => 'La sesión ha expirado.',
                    'icono' => 'warning',
                ], 401);
            }

            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $_SESSION['ultima_actividad'] = time();

        if (empty($_SESSION['admin_csrf_token'])) {
            $_SESSION['admin_csrf_token'] = bin2hex(
                random_bytes(32)
            );
        }
    }

    public function index()
    {
        header('Location: ' . BASE_URL . 'admin/mensajes');
        exit;
    }

    public function catalogos()
    {
        $catalogos = $this->model->catalogos();

        $this->json([
            'status' => true,
            'servicios' => is_array($catalogos['servicios'] ?? null)
                ? $catalogos['servicios']
                : [],
            'contactos' => is_array($catalogos['contactos'] ?? null)
                ? $catalogos['contactos']
                : [],
            'estados' => $this->estadosCatalogo(),
        ]);
    }

    public function listar()
    {
        $busqueda = trim($_GET['busqueda'] ?? '');
        $estado = strtolower(trim($_GET['estado'] ?? 'all'));
        $origen = strtolower(trim($_GET['origen'] ?? 'all'));
        $idServicio = max(0, (int) ($_GET['id_servicio'] ?? 0));
        $fechaDesde = trim($_GET['fecha_desde'] ?? '');
        $fechaHasta = trim($_GET['fecha_hasta'] ?? '');

        if (
            $estado !== 'all'
            && !in_array($estado, self::ESTADOS, true)
        ) {
            $estado = 'all';
        }

        if (!in_array(
            $origen,
            ['all', 'formulario_web', 'manual', 'referido', 'otro'],
            true
        )) {
            $origen = 'all';
        }

        if (!$this->fechaValida($fechaDesde)) {
            $fechaDesde = '';
        }

        if (!$this->fechaValida($fechaHasta)) {
            $fechaHasta = '';
        }

        if (
            $fechaDesde !== ''
            && $fechaHasta !== ''
            && $fechaDesde > $fechaHasta
        ) {
            [$fechaDesde, $fechaHasta] = [
                $fechaHasta,
                $fechaDesde,
            ];
        }

        $mensajes = $this->model->listar(
            $busqueda,
            $estado,
            $idServicio,
            $origen,
            $fechaDesde,
            $fechaHasta
        );

        $resumen = $this->model->estadisticas();

        $this->json([
            'status' => true,
            'mensajes' => is_array($mensajes) ? $mensajes : [],
            'resumen' => [
                'total' => (int) ($resumen['total'] ?? 0),
                'nuevas' => (int) ($resumen['nuevas'] ?? 0),
                'seguimiento' => (int) ($resumen['seguimiento'] ?? 0),
                'respondidas' => (int) ($resumen['respondidas'] ?? 0),
                'pendientes' => (int) ($resumen['pendientes'] ?? 0),
            ],
        ]);
    }

    public function obtener($idSolicitud)
    {
        $idSolicitud = (int) $idSolicitud;

        if ($idSolicitud <= 0) {
            $this->json([
                'status' => false,
                'msg' => 'El identificador no es válido.',
                'icono' => 'error',
            ], 400);
        }

        $mensaje = $this->model->obtener($idSolicitud);

        if (empty($mensaje)) {
            $this->json([
                'status' => false,
                'msg' => 'El mensaje no existe.',
                'icono' => 'error',
            ], 404);
        }

        $this->json($mensaje);
    }

    public function marcarLeida()
    {
        $entrada = $this->entrada();
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idSolicitud = (int) (
            $entrada['id_solicitud_contacto'] ?? 0
        );

        if ($idSolicitud <= 0) {
            $this->json([
                'status' => false,
                'msg' => 'El identificador del mensaje no es válido.',
                'icono' => 'warning',
            ], 422);
        }

        $mensaje = $this->model->obtener($idSolicitud);

        if (empty($mensaje)) {
            $this->json([
                'status' => false,
                'msg' => 'El mensaje no existe.',
                'icono' => 'error',
            ], 404);
        }

        if (($mensaje['estado'] ?? '') !== 'nueva') {
            $this->json([
                'status' => true,
                'msg' => 'El mensaje ya había sido revisado.',
                'icono' => 'success',
                'estado' => $mensaje['estado'] ?? '',
            ]);
        }

        $resultado = $this->model->cambiarEstado(
            $idSolicitud,
            'leida'
        );

        if (empty($resultado['status'])) {
            $respuesta = [
                'status' => false,
                'msg' => 'No fue posible marcar el mensaje como leído.',
                'icono' => 'error',
            ];

            if ($this->esEntornoLocal()) {
                $respuesta['detalle'] =
                    $resultado['error'] ?? 'Error desconocido.';
            }

            $this->json($respuesta, 500);
        }

        $this->json([
            'status' => true,
            'msg' => 'Mensaje marcado como leído.',
            'icono' => 'success',
            'estado' => 'leida',
        ]);
    }

    public function cambiarEstado()
    {
        $entrada = $this->entrada();
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idSolicitud = (int) (
            $entrada['id_solicitud_contacto'] ?? 0
        );

        if ($idSolicitud <= 0) {
            $this->json([
                'status' => false,
                'msg' => 'El identificador del mensaje no es válido.',
                'icono' => 'warning',
            ], 422);
        }

        $estado = strtolower(
            trim($entrada['estado'] ?? '')
        );

        if (!in_array($estado, self::ESTADOS, true)) {
            $this->json([
                'status' => false,
                'msg' => 'El estado seleccionado no es válido.',
                'icono' => 'warning',
            ], 422);
        }

        $mensaje = $this->model->obtener($idSolicitud);

        if (empty($mensaje)) {
            $this->json([
                'status' => false,
                'msg' => 'El mensaje no existe.',
                'icono' => 'error',
            ], 404);
        }

        /*
     * Si el estado seleccionado ya es el actual, no es necesario
     * ejecutar nuevamente el UPDATE.
     */
        if (($mensaje['estado'] ?? '') === $estado) {
            $this->json([
                'status' => true,
                'msg' => 'El mensaje ya tiene el estado seleccionado.',
                'icono' => 'success',
                'estado' => $estado,
            ]);
        }

        $resultado = $this->model->cambiarEstado(
            $idSolicitud,
            $estado
        );

        if (empty($resultado['status'])) {
            $respuesta = [
                'status' => false,
                'msg' => 'No fue posible actualizar el estado.',
                'icono' => 'error',
            ];

            if ($this->esEntornoLocal()) {
                $respuesta['detalle'] =
                    $resultado['error'] ?? 'Error desconocido.';
            }

            $this->json($respuesta, 500);
        }

        $this->json([
            'status' => true,
            'msg' => 'Estado actualizado correctamente.',
            'icono' => 'success',
            'estado' => $estado,
        ]);
    }

    public function vincularContacto()
    {
        $entrada = $this->entrada();
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idSolicitud = (int) (
            $entrada['id_solicitud_contacto'] ?? 0
        );

        $idContacto = max(
            0,
            (int) ($entrada['id_contacto_cliente'] ?? 0)
        );

        if (empty($this->model->obtener($idSolicitud))) {
            $this->json([
                'status' => false,
                'msg' => 'El mensaje no existe.',
                'icono' => 'error',
            ], 404);
        }

        if (
            $idContacto > 0
            && empty($this->model->contactoExiste($idContacto))
        ) {
            $this->json([
                'status' => false,
                'msg' => 'Selecciona un contacto activo.',
                'icono' => 'warning',
            ], 422);
        }

        if (!$this->model->vincularContacto(
            $idSolicitud,
            $idContacto > 0 ? $idContacto : null
        )) {
            $this->json([
                'status' => false,
                'msg' => 'No fue posible vincular el contacto.',
                'icono' => 'error',
            ], 500);
        }

        $this->json([
            'status' => true,
            'msg' => $idContacto > 0
                ? 'Contacto vinculado correctamente.'
                : 'Vinculación eliminada correctamente.',
            'icono' => 'success',
        ]);
    }
    private function esEntornoLocal(): bool
    {
        $host = strtolower(
            (string) ($_SERVER['HTTP_HOST'] ?? '')
        );

        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

        return str_starts_with($host, 'localhost')
            || str_starts_with($host, '127.0.0.1')
            || $ip === '127.0.0.1'
            || $ip === '::1';
    }

    private function estadosCatalogo()
    {
        return [
            [
                'slug' => 'nueva',
                'nombre' => 'Nueva',
            ],
            [
                'slug' => 'leida',
                'nombre' => 'Leída',
            ],
            [
                'slug' => 'en_seguimiento',
                'nombre' => 'En seguimiento',
            ],
            [
                'slug' => 'respondida',
                'nombre' => 'Respondida',
            ],
            [
                'slug' => 'cerrada',
                'nombre' => 'Cerrada',
            ],
            [
                'slug' => 'archivada',
                'nombre' => 'Archivada',
            ],
            [
                'slug' => 'spam',
                'nombre' => 'Spam',
            ],
        ];
    }

    private function fechaValida($fecha)
    {
        if ($fecha === '') {
            return true;
        }

        $objeto = DateTimeImmutable::createFromFormat(
            'Y-m-d',
            $fecha
        );

        return $objeto !== false
            && $objeto->format('Y-m-d') === $fecha;
    }

    private function entrada()
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $contenido = file_get_contents('php://input');

        if (
            $contenido === false
            || trim($contenido) === ''
        ) {
            return [];
        }

        $datos = json_decode($contenido, true);

        return is_array($datos) ? $datos : [];
    }

    private function validarCsrf($token)
    {
        $tokenSesion = $_SESSION['admin_csrf_token'] ?? '';

        if (
            $token === ''
            || $tokenSesion === ''
            || !hash_equals($tokenSesion, $token)
        ) {
            $this->json([
                'status' => false,
                'msg' => 'La solicitud no es válida. Actualiza la página.',
                'icono' => 'warning',
            ], 419);
        }
    }

    private function json($respuesta, $codigo = 200)
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
        );

        exit;
    }
}
